<?php
require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/../libs/Database.php");

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

            // Usamos la fecha calculada en PHP (America/Lima) en vez de CURDATE() de MySQL,
            // porque el servidor de BD puede tener otra zona horaria y desfasar el "hoy".
            $fecha_hoy_lima = (new DateTime())->format('Y-m-d');

            $query = $conn->prepare("
                SELECT 
                * 
                FROM programacion
                WHERE fecha_salida = :fecha_hoy AND estado = 1
            ");
            $query->bindParam(':fecha_hoy', $fecha_hoy_lima);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            error_log("[GestorReservas][get_programaciones_hoy] Fecha usada (Lima): {$fecha_hoy_lima} | Programaciones encontradas: " . count($data));

            return $data;
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_programaciones_hoy] Error: ' . $e->getMessage());
            return $data;
        }
    }

    function get_tiempo_seleccion($conn)
    {
        try {
            $query = $conn->prepare("
                SELECT tiempo_seleccion 
                FROM configuracion_pasaje 
                LIMIT 1
            ");
            $query->execute();
            $raw = $query->fetchColumn();

            error_log('[GestorReservas][get_tiempo_seleccion] Valor crudo desde BD: ' . var_export($raw, true));

            if ($raw === false || $raw === null || $raw === '') {
                error_log('[GestorReservas][get_tiempo_seleccion] ADVERTENCIA: tiempo_seleccion vacío/NULL en configuracion_pasaje. No se podrá liberar ningún asiento.');
                return null;
            }

            // tiempo_seleccion viene como texto (ej. "2"), lo casteamos a minutos (int)
            $minutos = (int) trim($raw);

            if ($minutos <= 0) {
                error_log("[GestorReservas][get_tiempo_seleccion] ADVERTENCIA: valor casteado a minutos es <= 0 (raw='{$raw}', minutos={$minutos}).");
                return null;
            }

            error_log("[GestorReservas][get_tiempo_seleccion] Minutos de selección interpretados: {$minutos}");

            return $minutos;
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_tiempo_seleccion] Error: ' . $e->getMessage());
            return null;
        }
    }

    function get_uuid_ws_sesion($conn)
    {
        try {
            $query = $conn->prepare("
                SELECT uuid_ws
                FROM empresa 
                LIMIT 1
            ");
            $query->execute();
            return $query->fetchColumn();
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_uuid_ws] Error: ' . $e->getMessage());
            return null;
        }
    }

    function revisar_asientos()
    {
        try {
            $conn = $this->db->connect();
            $programaciones = $this->get_programaciones_hoy($conn);
            if (empty($programaciones)) {
                error_log('[GestorReservas][revisar_asientos] No hay programaciones hoy con estado=1. Fin de la corrida.');
                return;
            }

            $tiempo_seleccion_minutos = $this->get_tiempo_seleccion($conn);
            $uuid_ws_sesion = $this->get_uuid_ws_sesion($conn);

            if (empty($uuid_ws_sesion)) {
                error_log('[GestorReservas][revisar_asientos] No se encontró uuid_ws_sesion en empresa, no se puede notificar por WS.');
            }

            if (empty($tiempo_seleccion_minutos)) {
                error_log('[GestorReservas][revisar_asientos] ADVERTENCIA: tiempo_seleccion_minutos es vacío/nulo. Ningún asiento en selección se liberará esta corrida.');
            }

            foreach ($programaciones as $programacion) {

                error_log("[GestorReservas][revisar_asientos] Revisando programacion_id={$programacion['id_programacion']}");

                $asientos_bloqueados = $this->get_asientos_bloqueados(
                    $conn,
                    $programacion['id_programacion']
                );

                error_log("[GestorReservas][revisar_asientos] Programacion {$programacion['id_programacion']}: asientos en selección encontrados = " . count($asientos_bloqueados));

                $asientos_liberados_esta_programacion = [];

                foreach ($asientos_bloqueados as $asiento) {

                    $resultado = $this->deberia_desbloquear($asiento, $tiempo_seleccion_minutos);

                    if ($resultado) {

                        $ok = $this->desbloquear_asiento($conn, $asiento);

                        if ($ok) {
                            error_log("[GestorReservas][revisar_asientos] Asiento liberado OK: id_obj_vehiculo={$asiento['id_obj_vehiculo']}, id_programacion={$asiento['id_programacion']}");

                            $asientos_liberados_esta_programacion[] = [
                                "id_obj_vehiculo" => $asiento['id_obj_vehiculo'],
                                "estado" => null,
                                "id_venta" => null,
                            ];
                        } else {
                            error_log("[GestorReservas][revisar_asientos] ERROR: desbloquear_asiento devolvió false para id_obj_vehiculo={$asiento['id_obj_vehiculo']}");
                        }
                    }
                }

                if (!empty($asientos_liberados_esta_programacion) && !empty($uuid_ws_sesion)) {
                    $this->notificarWebSocket("empresa_{$uuid_ws_sesion}_pasaje", [
                        "tipo" => "asiento_liberado",
                        "motivo" => "tiempo_agotado",
                        "id_programacion" => $programacion['id_programacion'],
                        "asientos" => $asientos_liberados_esta_programacion,
                    ]);
                }
            }
        } catch (PDOException $e) {
            error_log('[GestorReservas][revisar_asientos] Error: ' . $e->getMessage());
        }
    }

    public function notificarWebSocket(string $room, array $data): void
    {
        $url = "http://127.0.0.1:3000/evento";
        $json = json_encode(array_merge(["room" => $room], $data));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "x-secret-key: " . getenv('WEBSOCKET_SECRET'),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            error_log("cURL ERROR: " . $error);
        } else {
            error_log("WS RESPONSE: $response | HTTP: $httpCode");
        }
    }

    // Solo trae asientos en proceso de SELECCIÓN (estado_proceso = 1, sin estado definido)
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
                AND (estado IS NULL OR estado = '')
            ");
            $query->bindParam(':programacion_id', $programacion_id);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            error_log("[GestorReservas][get_asientos_bloqueados] programacion_id={$programacion_id} -> " . count($data) . ' asiento(s) en selección');

            return $data;
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_asientos_bloqueados] Error: ' . $e->getMessage());
            return [];
        }
    }

    // Solo evalúa el caso de selección (estado_proceso = 1, sin estado definido)
    function deberia_desbloquear($asiento, $tiempo_seleccion_minutos)
    {
        $id = $asiento['id_obj_vehiculo'] ?? 'desconocido';

        if (empty($tiempo_seleccion_minutos)) {
            error_log("[GestorReservas][deberia_desbloquear] id_obj_vehiculo={$id}: SKIP, tiempo_seleccion_minutos vacío.");
            return false;
        }

        if (empty($asiento['fecha_registro'])) {
            error_log("[GestorReservas][deberia_desbloquear] id_obj_vehiculo={$id}: SKIP, fecha_registro vacía.");
            return false;
        }

        $hora_actual = new DateTime();
        $hora_limite = new DateTime($asiento['fecha_registro']);
        $hora_limite->modify("+{$tiempo_seleccion_minutos} minutes");

        $segundos_restantes = $hora_limite->getTimestamp() - $hora_actual->getTimestamp();

        $desbloquear = $hora_actual > $hora_limite;

        error_log(sprintf(
            "[GestorReservas][deberia_desbloquear] id_obj_vehiculo=%s | fecha_registro=%s | limite=%s | ahora=%s | segundos_restantes=%d | desbloquear=%s",
            $id,
            $asiento['fecha_registro'],
            $hora_limite->format('Y-m-d H:i:s'),
            $hora_actual->format('Y-m-d H:i:s'),
            $segundos_restantes,
            $desbloquear ? 'SI' : 'NO'
        ));

        return $desbloquear;
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

            $filas_afectadas = $query->rowCount();

            error_log("[GestorReservas][desbloquear_asiento] DELETE id_obj_vehiculo={$asiento['id_obj_vehiculo']} id_programacion={$asiento['id_programacion']} -> filas afectadas: {$filas_afectadas}");

            if ($filas_afectadas === 0) {
                error_log("[GestorReservas][desbloquear_asiento] ADVERTENCIA: el DELETE no afectó ninguna fila. Puede que el registro ya no cumpla estado_proceso=1 (cambiado por otro proceso).");
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log('[GestorReservas][desbloquear_asiento] Error: ' . $e->getMessage());
            return false;
        }
    }
}

// ══════════════════════════════════════════════════════════
// EJECUCIÓN — solo corre si este archivo se invoca directamente
// (por el cron), no si alguien más lo incluye vía require/include
// ══════════════════════════════════════════════════════════
if (php_sapi_name() === 'cli' && realpath($argv[0]) === __FILE__) {

    $lockFile = __DIR__ . '/gestor_reservas.lock';
    $fp = fopen($lockFile, 'c');

    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        error_log('[cron][GestorReservas] Ejecución anterior aún en curso, se omite esta corrida.');
        exit;
    }

    error_log('[cron][GestorReservas] Inicio de corrida: ' . date('Y-m-d H:i:s'));

    $gestor = new GestorReservas();
    $gestor->revisar_asientos();

    error_log('[cron][GestorReservas] Fin de corrida: ' . date('Y-m-d H:i:s'));

    flock($fp, LOCK_UN);
    fclose($fp);
}
