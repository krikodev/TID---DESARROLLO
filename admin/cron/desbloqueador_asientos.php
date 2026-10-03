<?php
require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/../libs/Database.php");
require_once __DIR__ . '/../../vendor/autoload.php';
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;

date_default_timezone_set('America/Lima');

class GestorReservas
{
    public $db;
    function __construct()
    {
        $this->db = new Database();
    }

    function get_programaciones_hoy($conn)
    {
        $data = [];
        try {
            if (empty($conn)) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
                SELECT 
                * 
                FROM programacion
                WHERE fecha_salida = CURDATE() AND estado = 1
            ");
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_programaciones_hoy] Error: ' . $e->getMessage());
            return $data;
        }
    }

    function revisar_asientos()
    {
        try {
            $conn = $this->db->connect();
            $programaciones = $this->get_programaciones_hoy($conn);
            if (empty($programaciones)) {
                return;
            }

            $ventasLiberadas = [];

            foreach ($programaciones as $programacion) {
                $asientos_bloqueados = $this->get_asientos_bloqueados(
                    $conn,
                    $programacion['id_programacion']
                );

                foreach ($asientos_bloqueados as $asiento) {

                    if ($this->deberia_desbloquear($asiento)) {

                        if ($asiento['estado'] === 'RESERVADO') {

                            $ok = $this->liberar_reserva($conn, $asiento);

                            if ($ok && !empty($asiento['id_venta'])) {
                                $ventasLiberadas[] = $asiento['id_venta'];
                            }
                        } else if ($asiento['estado'] === 'PROCESO_WEB' || $asiento['estado'] === '') {
                            $this->desbloquear_asiento($conn, $asiento);
                            $ventasLiberadas[] = 1;
                        }
                    }
                }
            }

            if (!empty($ventasLiberadas)) {
                $this->notificarWebSocketMasivo($ventasLiberadas);
            }
        } catch (PDOException $e) {
            error_log('[GestorReservas][revisar_asientos] Error: ' . $e->getMessage());
        }
    }

    function notificarWebSocketMasivo($ventas)
    {
        $loop = Factory::create();

        $reactConnector = new ReactConnector($loop, [
            'timeout' => 10,
            'tls' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $connector = new Connector($loop, $reactConnector);

        $connector('wss://tid.net.pe:8085')->then(
            function ($conn) use ($ventas, $loop) {

                $msg = json_encode([
                    'ventas' => $ventas
                ]);

                $conn->send($msg);

                $loop->addTimer(1, function () use ($conn, $loop) {
                    $conn->close();
                    $loop->stop();
                });
            },
            function ($e) use ($loop) {
                error_log('[GestorReservas] Error WebSocket: ' . $e->getMessage());
                $loop->stop();
            }
        );

        $loop->run();
    }

    function get_asientos_bloqueados($conn, $programacion_id)
    {
        try {
            if (empty($conn)) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
                SELECT 
                * 
                FROM programacion_obj
                WHERE id_programacion = :programacion_id
                AND estado_proceso = 1
                AND (estado = 'RESERVADO' OR estado = 'PROCESO_WEB' OR estado = '' OR estado IS NULL)
            ");
            $query->bindParam(':programacion_id', $programacion_id);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_asientos_bloqueados] Error en get_asientos_bloqueados: ' . $e->getMessage());
            return [];
        }
    }

    function deberia_desbloquear($asiento)
    {
        $hora_actual = new DateTime();
        $hora_registro = new DateTime($asiento['fecha_registro']);
        $desbloquear = false;
        if ($asiento['estado'] === '') {
            $query = $this->db->connect()->prepare("
                SELECT tiempo_seleccion
                FROM configuracion_pasaje
                LIMIT 1
            ");
            $query->execute();
            $tiempo_seleccion = (int)$query->fetchColumn();
            $hora_registro->modify("+{$tiempo_seleccion} minutes");
            if ($hora_actual > $hora_registro) {
                $desbloquear = true;
            }
        } else if ($asiento['estado'] === 'PROCESO_WEB') {
            $segundos = $hora_actual->getTimestamp() - $hora_registro->getTimestamp();

            if ($segundos >= 600) {
                $desbloquear = true;
            }
        } else if ($asiento['estado'] === 'RESERVADO') {
            if (!empty($asiento['tiempo_reserva'])) {
                [$horas, $mins] = explode(':', $asiento['tiempo_reserva']);
                $totalMinutos = ((int)$horas * 60) + (int)$mins;
                $hora_registro->modify("+{$totalMinutos} minutes");
                if ($hora_actual > $hora_registro) {
                    $desbloquear = true;
                }
            } else {
                $desbloquear = false;
            }
        }
        return $desbloquear;
    }

    function liberar_reserva($conn, $asiento)
    {
        try {
            if (empty($conn)) {
                $conn = $this->db->connect();
            }
            $conn->beginTransaction();

            $id_venta = $asiento['id_venta'];

            // Anulando el detalle de la venta
            $query = $conn->prepare("
                    UPDATE dt_venta SET estado_asiento = 'ANULADO' 
                    WHERE id_venta = :id_venta
                ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();

            // Cambiando el estado del objeto
            $query = $conn->prepare("
                    UPDATE programacion_obj SET estado = 'ANULADO' 
                    WHERE id_venta = :id_venta
                ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();

            // Anulando la venta
            $query = $conn->prepare("
                    UPDATE venta SET estado = 'ANULADO', total = '0.00' 
                    WHERE id_venta = :id_venta
                ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();

            $conn->commit();

            return true;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('[GestorReservas][liberar_reserva] Error: ' . $e->getMessage());
            return false;
        }
    }

    function desbloquear_asiento($conn, $asiento)
    {
        try {
            if (empty($conn)) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare(
                "DELETE FROM programacion_obj 
                 WHERE id_obj_vehiculo = :id_obj_vehiculo 
                 AND id_programacion = :id_programacion 
                 AND estado_proceso = 1"
            );

            $query->bindParam(':id_obj_vehiculo', $asiento['id_obj_vehiculo'], PDO::PARAM_INT);
            $query->bindParam(':id_programacion', $asiento['id_programacion'], PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            error_log('[GestorReservas][desbloquear_asiento] Error: ' . $e->getMessage());
            return false;
        }
    }
}
