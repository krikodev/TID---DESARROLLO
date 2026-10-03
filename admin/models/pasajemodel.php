<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once('public/plugins/print/num_letras.php');

date_default_timezone_set('America/Lima');

use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;

class PasajeModel extends Model
{
    public $tp_moneda = 1;
    function __construct()
    {
        parent::__construct();
    }

    private function limpiarTerminal(string $texto): string
    {
        // Eliminar la palabra "TERMINAL"
        $limpio = str_ireplace('terminal', '', $texto);

        // Si "AGENCIA" está al inicio, eliminarla
        $limpio = preg_replace('/^\s*AGENCIA\s*-?\s*/i', '', $limpio);

        // Si "AGENCIA" aparece después, eliminarla y todo lo que venga después
        $limpio = preg_replace('/\s*-?\s*AGENCIA.*$/i', '', $limpio);

        // Eliminar espacios duplicados
        $limpio = preg_replace('/\s+/', ' ', $limpio);

        return trim($limpio);
    }

    //Pasaje 
    public function get_dataTable($data)
    {
        $id_programacion = isset($data['id_programacion']) ? intval($data['id_programacion']) : 0;

        // Si no hay id_programacion, devolver conjunto vacío
        if ($id_programacion <= 0) {
            return array('success' => true, 'data' => []);
        }

        $tp_comprobante = is_array($data["tp_comprobante"])
            ? implode(",", $data["tp_comprobante"])
            : $data["tp_comprobante"];

        $query_sql = "
        SELECT
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c.nombres AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.num_docu AS cliente_num_docu,
            v.id_forma_pago,
            v.id_medio_pago,
            v.id_tp_moneda,
            v.id_tp_comprobante,
            v.id_caja_chica,
            v.id_serie,
            v.serie,
            v.correlativo,
            v.descuento,
            v.op_igv,
            dt_v.estado_asiento AS estado,
            v.envio_sunat,
            v.descrip_cdr_sunat,
            v.hash_cdr,
            v.file_xml,
            v.file_cdr,
            v.fecha_emision,
            v.fecha_registro,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            dt_v.num_asiento AS asiento
        FROM venta v
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
        WHERE tp_s.descripcion='PASAJE' 
        AND v.id_tp_comprobante IN ($tp_comprobante) 
        AND p.id_programacion = :id_programacion
        ORDER BY v.fecha_emision DESC";

        $query = $this->db->connect()->prepare($query_sql);
        $query->bindParam("id_programacion", $id_programacion);

        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        return array('success' => true, 'data' => $reply);
    }

    public function get_dataTableResumen()
    {
        $query = $this->db->connect()->prepare("SELECT rc.*
        FROM resumen_baja rc
        JOIN dt_venta ON rc.id_comprobante = dt_venta.id_venta
        WHERE dt_venta.id_tp_servicio = 1 
        ORDER BY rc.id_resumen DESC;");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('data' => $reply));
    }

    public function get_dataTableReenvio($data)
    {
        $tp_comprobante = implode(",", $data["tp_comprobante"]);
        $query = $this->db->connect()->prepare("
        SELECT
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c.nombres AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.num_docu AS cliente_num_docu,
            v.id_forma_pago,
            v.id_medio_pago,
            v.id_tp_moneda,
            v.id_tp_comprobante,
            v.id_caja_chica,
            v.id_serie,
            v.serie,
            v.correlativo,
            v.descuento,
            v.op_igv,
            v.estado,
            v.envio_sunat,
            v.descrip_cdr_sunat,
            v.hash_cdr,
            v.file_xml,
            v.file_cdr,
            v.fecha_emision,
            v.fecha_registro,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida
        FROM venta v
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
        WHERE tp_s.descripcion='PASAJE' AND v.id_tp_comprobante = 3 AND v.envio_sunat = 0
        ORDER BY v.fecha_emision DESC");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('data' => $reply));
    }

    public function impresion_rapida()
    {
        $success = false;

        // Preparar y ejecutar la consulta
        $query = $this->db->connect()->prepare("
            SELECT estado
            FROM impresoras
            WHERE cod_usuario = :cod_usuario
        ");
        $query->bindParam(':cod_usuario', $this->id_usuario_sesion);
        $query->execute();

        // Obtener el resultado de la consulta
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Verificar si se obtuvieron resultados y si el estado es 1
        if ($result !== false && isset($result['estado']) && $result['estado'] == 1) {
            $success = true;
        }

        return array(
            "estado" => $success
        );
    }

    public function set_ventaPasaje($data)
    {
        try {
            if (isset($data['aplicar_pospuesto']) && $data['aplicar_pospuesto'] == '1') {
                return $this->aplicar_venta_pospuesta($data);
            } else {
                $correlativo = $this->get_correlativo($data);

                $conn = $this->db->connect();
                $conn->beginTransaction();
                $id_serie = isset($data["serie_venta"]) ? $data["serie_venta"] : '';
                $fecha_emision = date("Y-m-d H:i:s");
                $fecha_vencimiento = date("Y-m-d");
                $v = '';
                $porcentaje_venta = 0;
                $factor_p_venta = 0;
                $estado_pago = 'PAGADO';
                $descuento = isset($data['monto_descuento']) ? $data['monto_descuento'] : 0.00;
                $egresos_venta = isset($data['egresos']) ? json_decode($data['egresos'], true) : [];

                $query = $conn->prepare("
                 SELECT 
                 porcent_venta
                 FROM empresa
                 WHERE id_empresa = :id_empresa");
                $query->bindParam(':id_empresa', $this->id_empresa_sesion);
                $query->execute();
                $permiso_porcentaje = $query->fetchColumn();

                if ($permiso_porcentaje == 1) {
                    $query = $conn->prepare("
                    SELECT 
                       porcent_venta
                    FROM configuracion  
                    ");
                    $query->execute();
                    $porcentaje_dato = floatval($query->fetchColumn());
                    $factor_p_venta = $porcentaje_dato;
                    $total_venta = floatval($data['precio_venta']);

                    $porcentaje_venta = $total_venta * $porcentaje_dato / 100;
                }

                // Consulta para verificar que el id_programacion_obj existe
                $query = $conn->prepare("
                  SELECT id_programacion_obj 
                  FROM programacion_obj 
                  WHERE id_obj_vehiculo = :id_obj_vehiculo 
                  AND id_programacion = :id_programacion
                  AND estado_proceso = 1 
                  FOR UPDATE
                ");
                $query->bindParam(':id_obj_vehiculo', $data["id_asiento_selected"]);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->execute();
                $existe_asiento = $query->fetch(PDO::FETCH_ASSOC);

                // Validar existencia
                if (!$existe_asiento) {
                    return [
                        "success" => false,
                        "message" => "Ocurrió un error con el asiento seleccionado. Por favor, intente nuevamente.",
                        "hidden_modal" => true
                    ];
                }

                switch ($data["estado_venta"]) {
                    case 'RESERVADO':
                        $query = $conn->prepare("INSERT INTO venta (
                        id_terminal, id_vendedor, codigo, fecha_emision, id_cliente, estado, id_sesionpersonal, id_tp_venta)
                        VALUES(
                        :id_terminal, :id_vendedor, :codigo, :fecha_emision, :id_cliente, :estado, :id_sesionpersonal, '1')");
                        $query->bindParam(':id_terminal', $this->id_terminal_sesion);
                        $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
                        $query->bindParam(':codigo', $v);
                        $query->bindParam(':fecha_emision', $fecha_emision);
                        $query->bindParam(':id_cliente', $data["cliente_id"]);
                        $query->bindParam(':estado', $estado_pago);
                        $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                        $query->execute();
                        break;
                    case 'VENDIDO':
                        $query = $conn->prepare("INSERT INTO venta (
                        id_terminal, id_vendedor, codigo, id_cliente, id_forma_pago, id_medio_pago, id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie,
                        serie, correlativo, descuento, op_igv, estado, envio_sunat, descrip_cdr_sunat, hash_cdr, fecha_emision,
                        op_gravada, op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, porcentaje_venta, factor_p_venta, id_tp_venta) 
                        VALUES(
                        :id_terminal, :id_vendedor, :codigo, :id_cliente, 1, :id_medio_pago, :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie,
                        (SELECT serie FROM serie WHERE id_serie=:id_serie_sub), :correlativo, :descuento, 0.00, :estado, 0, '', '', :fecha_emision,
                        0.00, :op_exonerada, 0.00, :total, :cod_operacion, :fecha_vencimiento, :obs, :id_sesionpersonal, :porcentaje_venta, :factor_p_venta, '1')");
                        $query->bindParam(':id_terminal', $this->id_terminal_sesion);
                        $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
                        $query->bindParam(':codigo', $v);
                        $query->bindParam(':id_cliente', $data["cliente_id"]);
                        $query->bindParam(':id_medio_pago', $data["medio_pago"]);
                        $query->bindParam(':id_tp_moneda', $this->tp_moneda);
                        $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
                        $query->bindParam(':id_caja_chica', $data["destino"]);
                        $query->bindParam(':id_serie', $data["serie_venta"]);
                        $query->bindParam(':id_serie_sub', $data["serie_venta"]);
                        $query->bindParam(':correlativo', $correlativo);
                        $query->bindParam(':descuento', $descuento);
                        $query->bindParam(':estado', $estado_pago);
                        $query->bindParam(':fecha_emision', $fecha_emision);
                        $query->bindParam(':op_exonerada', $data["precio_venta"]);
                        $query->bindParam(':total', $data["precio_venta"]);
                        $query->bindParam(':cod_operacion', $data["cod_operacion"]);
                        $query->bindParam(':fecha_vencimiento', $fecha_vencimiento);
                        $query->bindParam(':obs', $data["obs"]);
                        $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                        $query->bindParam(':porcentaje_venta', $porcentaje_venta);
                        $query->bindParam(':factor_p_venta', $factor_p_venta);
                        $query->execute();
                        break;
                    default:
                        throw new Exception("Estado de venta no válido");
                }
                $id_venta = $conn->lastInsertId();

                $data['id_venta_insert'] = $id_venta;
                if (isset($data['monto_descuento']) && $data['monto_descuento'] > 0) {
                    $actualizar_cupon = $this->insertar_uso_cupon($data, $conn);
                    if (!$actualizar_cupon['success']) {
                        throw new Exception('No se pudo actualizar el cupón usado');
                    }
                }

                //Particionando el tema de los clientes
                $client = !empty($data['pasajero_id']) ? $data['pasajero_id'] : ($data['cliente_id'] ?? null);
                // Consultar el origen principal
                $query = $conn->prepare("
                SELECT id_terminal_origen, id_terminal_destino
                FROM programacion
                WHERE id_programacion = :id_programacion
                ");
                $query->bindParam(':id_programacion', $data['id_programacion']);
                $query->execute();
                $result = $query->fetch(PDO::FETCH_ASSOC);

                // Inicializar variables
                $id_ruta = null;
                $tp_origen = 0;

                // Verificar si el origen es diferente y buscar la ruta correspondiente
                if ($result && $data['origen'] != $result['id_terminal_origen']) {
                    $query = $conn->prepare("
                  SELECT id_ruta
                  FROM ruta
                  WHERE id_programacion = :id_programacion 
                  AND id_terminal = :id_terminal
                 ");
                    $query->bindParam(':id_programacion', $data['id_programacion']);
                    $query->bindParam(':id_terminal', $data['origen']);
                    $query->execute();

                    $routeResult = $query->fetch(PDO::FETCH_ASSOC);
                    if ($routeResult) {
                        $id_ruta = $routeResult['id_ruta'];
                        $tp_origen = 1;
                    }
                }

                //Comparación de los destinos y tipo de destino
                $id_destino = null;
                $tp_destino = 0;

                // Verificar si el origen es diferente y buscar la ruta correspondiente
                if ($data['destino_pasajero'] != "null") {
                    $id_destino = $data['destino_pasajero'];
                    $tp_destino = 1;
                }

                // Preparar la consulta de inserción
                $query = $conn->prepare("
               INSERT INTO dt_venta (
                 id_venta, 
                 id_tp_servicio, 
                 id_programacion, 
                 piso, 
                 num_asiento, 
                 estado_asiento, 
                 precio, 
                 id_pasajero, 
                 obs_pasajero,
                 op_igv, 
                 op_gravada, 
                 op_exonerada, 
                 op_inafecta, 
                 op_total, 
                 id_sesionpersonal,
                 tp_origen,
                 tp_destino,
                 id_ruta,
                 id_destino,
                 c_nino,
                 id_nino,
                 motivo
                 ) VALUES (
                 :id_venta, 
                 1, 
                 :id_programacion, 
                 :piso, 
                 :num_asiento, 
                 :estado_asiento, 
                 :precio, 
                 :id_pasajero, 
                 :obs_pasajero,
                 0.00, 
                 0.00, 
                 :op_exonerada, 
                 0.00, 
                 :op_total, 
                 :id_sesionpersonal,
                 :tp_origen,
                 :tp_destino,
                 :id_ruta,
                 :id_destino,
                  :c_nino,
                  :id_nino,
                  :motivo
                   )
                ");
                // Parámetros base
                $query->bindParam(':id_venta', $id_venta);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->bindParam(':piso', $data["num_piso"]);
                $query->bindParam(':num_asiento', $data["num_asiento"]);
                $query->bindParam(':estado_asiento', $data["estado_venta"]);
                $query->bindParam(':precio', $data["precio_venta"]);
                $query->bindParam(':id_pasajero', $client);
                $query->bindParam(':obs_pasajero', $data['obs_pasajero']);
                $query->bindParam(':op_exonerada', $data["precio_venta"]);
                $query->bindParam(':op_total', $data["precio_venta"]);
                $query->bindParam(':tp_origen', $tp_origen);
                $query->bindParam(':tp_destino', $tp_destino);
                $query->bindParam(':id_ruta', $id_ruta, PDO::PARAM_INT);
                $query->bindParam(':id_destino', $id_destino, PDO::PARAM_INT);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);

                // Parámetros específicos para niños (NULL si no aplica)
                $cNino = ($data['c_nino'] == 1) ? $data['c_nino'] : 0;
                $idNino = ($data['c_nino'] == 1) ? $data['nino_id'] : null;
                $motivo = ($data['c_nino'] == 1) ? $data['desc_motivo'] : null;

                $query->bindParam(':c_nino', $cNino, PDO::PARAM_INT);
                $query->bindParam(':id_nino', $idNino, PDO::PARAM_INT);
                $query->bindParam(':motivo', $motivo);

                $query->execute();

                // Registrando programacion objeto
                $query = $conn->prepare("UPDATE programacion_obj SET 
                estado=:estado,
                id_venta=:id_venta,
                estado_proceso=0,
                fecha_registro=:fecha_registro
                WHERE id_obj_vehiculo=:id_obj_vehiculo 
                AND id_programacion=:id_programacion
                AND estado_proceso=1
                ");
                $query->bindParam(':id_obj_vehiculo', $data["id_asiento_selected"]);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->bindParam(':estado', $data["estado_venta"]);
                $query->bindParam(':id_venta', $id_venta);
                $query->bindParam(':fecha_registro', $fecha_emision);
                $query->execute();
                if ($query->rowCount() === 0) {
                    throw new Exception("El asiento ya fue vendido/reservado por otra operación.");
                }

                // Guardar los egresos de la venta
                if (!empty($egresos_venta)) {
                    $query = $conn->prepare("
                     INSERT INTO egresos_usuario
                     (id_usuario, id_programacion, id_venta, tp_comprobante, serie, correlativo, monto, concepto) 
                      VALUES 
                     (:id_usuario, :id_programacion, :id_venta, :tp_comprobante, :serie, :correlativo, :monto, :concepto)
                    ");

                    foreach ($egresos_venta as $egreso) {
                        $query->bindValue(":id_usuario", $this->id_usuario_sesion);
                        $query->bindValue(":id_programacion", $data['id_programacion']);
                        $query->bindValue(":id_venta", $id_venta);
                        $query->bindValue(":tp_comprobante", $egreso[0]);
                        $query->bindValue(":serie", $egreso[1]);
                        $query->bindValue(":correlativo", $egreso[2]);
                        $query->bindValue(":monto", $egreso[3]);
                        $query->bindValue(":concepto", $egreso[4]);
                        $query->execute();
                    }
                }

                // Generando el código QR y facturación
                if (in_array($data["tp_comprobante"], [1, 3]) && $data["estado_venta"] == "VENDIDO") {
                    $cod_qr = $this->generar_codQR($id_venta, $conn);
                    if (!$cod_qr['success']) {
                        throw new Exception("Error al generar el código QR: " . $cod_qr['message']);
                    }
                    // api facturación
                    $rpta_sunat = $this->enviar_json_a_api($this->conversor_data($this->getDataComprobante($id_venta, $conn)));
                    $resp = json_decode($rpta_sunat, true);
                    // Actualizando los datos de la venta
                    $resp[0]["id_venta"] = $id_venta;
                    $actualizar_venta_sunat = $this->updateVentaSetForSunat($resp[0], $conn);
                    if (!$actualizar_venta_sunat["success"]) {
                        throw new Exception("Error al actualizar la venta con datos de SUNAT: " . $actualizar_venta_sunat['message']);
                    }
                }
                $conn->commit();
                // Ejemplo de uso
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "venta_pasaje",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => [          // ← array que lee procesarEventoAsientos
                        [
                            "id_obj_vehiculo" => $data["id_asiento_selected"], // el id del div en el DOM
                            "estado" => $data["estado_venta"],        // "VENDIDO" o "RESERVADO"
                            "id_venta" => $id_venta,
                            "terminal" => $this->nombre_terminal_sesion,
                            "terminal_color" => $this->color_terminal_sesion,
                            "id_usuario" => $this->id_usuario_sesion
                        ]
                    ],
                ]);
                // formulación de la respuesta
                $link_comprobante = URL . "pasaje/impresion/" . ($data["tp_comprobante"] == 2 ? 'nota_venta/' : 'comprobante/') . $id_venta;
                $show_link = $data["estado_venta"] == "VENDIDO" ? true : false;
                $message = $data["estado_venta"] == "VENDIDO" ? "Venta registrada con éxito" : "Asiento reservado con éxito";
                return [
                    "success" => true,
                    "message" => [
                        "message" => $message,
                        "link_comprobante" => $link_comprobante,
                        "show_link" => $show_link,
                        "estado_sunat" => isset($resp) ? $resp[0]["estado"] : 0,
                        "message_sunat" => isset($resp) ? $resp[0]["mensaje_sunat"] : ''
                    ]
                ];
            }
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => ["message" => $e->getMessage()]];
                    break;
                default:
                    return ['success' => false, "message" => $e->getMessage()];
                    break;
            }
        }
    }

    public function insertar_uso_cupon($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
            INSERT INTO cupon_uso (
                id_cupon,
                id_venta,
                monto_descuento
            ) VALUES (
                :id_cupon,
                :id_venta,
                :monto_descuento
            )
           ");

            $query->bindParam(':id_cupon', $data['id_cupon'], PDO::PARAM_INT);
            $query->bindParam(':id_venta', $data['id_venta_insert'], PDO::PARAM_INT);
            $query->bindParam(':monto_descuento', $data['monto_descuento']);

            $query->execute();

            $update = $conn->prepare("
            UPDATE cupon 
            SET uso_actual = uso_actual + 1
            WHERE id_cupon = :id_cupon
            ");
            $update->bindParam(':id_cupon', $data['id_cupon'], PDO::PARAM_INT);
            $update->execute();


            return [
                'success' => true,
                'message' => 'Uso de cupón registrado correctamente'
            ];
        } catch (Exception $e) {

            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'message' => 'Este cupón ya fue utilizado'
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al registrar uso del cupón'
            ];
        }
    }

    public function reserva_grupal($data)
    {
        try {
            $conn = $this->db->connect();
            $id_serie = isset($data["serie_venta"]) ? $data["serie_venta"] : '';
            $fecha_emision = date("Y-m-d H:i:s");
            $fecha_vencimiento = date("Y-m-d");
            $as_estado = 'RESERVADO';

            // Limpiando y procesando los números de asiento
            $asientos_numeros = rtrim($data['numeros_asientos'], ',');
            $asientos_numeros = explode(',', $asientos_numeros);
            $asientos_numeros = array_map('intval', $asientos_numeros);

            // Generando la lista de placeholders para la cláusula IN
            $placeholders = implode(',', array_fill(0, count($asientos_numeros), '?'));

            // Consulta para obtener los id_obj_vehiculo basados en los asientos
            $query = $conn->prepare("
                SELECT id_obj_vehiculo, piso, TRIM(text_obj) AS num_asiento
                FROM obj_vehiculo 
                WHERE TRIM(text_obj) IN ($placeholders) 
                AND id_vehiculo = ?
            ");

            // Ejecutando la consulta con los asientos y el id del vehículo
            $query->execute(array_merge($asientos_numeros, [$data["id_vehiculo"]]));
            $asientos = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($asientos)) {
                return array('success' => false, 'message' => 'No se encontraron asientos correspondientes.');
            }

            // Extrayendo precios de los pisos según el id_programacion
            $query = $conn->prepare("
                SELECT precio_primer_piso, precio_segundo_piso
                FROM programacion
                WHERE id_programacion = ?
            ");
            $query->execute([$data["id_programacion"]]);
            $precios = $query->fetch(PDO::FETCH_ASSOC);
            $precio_asiento = 0;

            // Iterando sobre cada asiento para hacer las consultas de validación
            foreach ($asientos as $asiento) {
                $id_obj_vehiculo = $asiento['id_obj_vehiculo'];
                $precio_asiento = ($asiento['piso'] == 1) ? $precios['precio_primer_piso'] : $precios['precio_segundo_piso'];

                // Consulta única para validar estado del asiento
                $query_estado = $conn->prepare("
                    SELECT * 
                    FROM programacion_obj 
                    WHERE id_obj_vehiculo = :id_obj_vehiculo 
                    AND id_programacion = :id_programacion 
                    AND (estado_proceso = 1 OR estado IN ('VENDIDO', 'RESERVADO'))
                ");
                $query_estado->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query_estado->bindParam(':id_programacion', $data["id_programacion"]);
                $query_estado->execute();
                $estado_asiento = $query_estado->fetchAll(PDO::FETCH_ASSOC);

                // Si el asiento ya está en proceso o reservado
                if (!empty($estado_asiento)) {
                    continue;
                }

                // Proceso de reserva grupal
                $vacio = '';
                $query = $conn->prepare("INSERT INTO venta (
                    id_terminal, codigo, id_vendedor, id_cliente, serie, fecha_emision, estado, id_sesionpersonal, id_tp_venta)
                    VALUES(
                    :id_terminal, :codigo, :id_vendedor, :id_cliente, :serie, :fecha_emision, :estado, :id_sesionpersonal, '1')");
                $query->bindParam(':id_terminal', $this->id_terminal_sesion);
                $query->bindParam(':codigo', $vacio);
                $query->bindParam(':serie', $vacio);
                $query->bindParam(':fecha_emision', $fecha_emision);
                $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
                $query->bindParam(':id_cliente', $this->id_usuario_sesion);
                $query->bindParam(':estado', $as_estado);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->execute();

                $id_venta = $conn->lastInsertId();

                // Registrando el detalle venta
                $query = $conn->prepare("INSERT INTO dt_venta (
                    id_venta, id_tp_servicio, id_programacion, piso, num_asiento, estado_asiento, precio, id_pasajero, op_igv, op_gravada, op_exonerada, op_inafecta, op_total, id_sesionpersonal) 
                    VALUES(
                    :id_venta, 1, :id_programacion, :piso, :num_asiento, :estado_asiento, :precio, :id_pasajero, 0.00, 0.00, :op_exonerada, 0.00, :op_total, :id_sesionpersonal)");
                $query->bindParam(':id_venta', $id_venta);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->bindParam(':piso', $asiento["piso"]);
                $query->bindParam(':num_asiento', $asiento["num_asiento"]);
                $query->bindParam(':estado_asiento', $as_estado);
                $query->bindParam(':precio', $precio_asiento);
                $query->bindParam(':id_pasajero', $this->id_usuario_sesion);
                $query->bindParam(':op_exonerada', $precio_asiento);
                $query->bindParam(':op_total', $precio_asiento);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->execute();

                // Actualizando programacion_obj con los datos de la reserva
                $query = $conn->prepare("
                INSERT INTO programacion_obj (
                    id_obj_vehiculo, id_programacion, estado, id_venta, estado_proceso, fecha_registro, cod_usuario)
                    VALUES (
                    :id_obj_vehiculo, :id_programacion, :estado, :id_venta, 0, :fecha_registro, :cod_usuario)");
                $query->bindParam(':id_obj_vehiculo', $asiento["id_obj_vehiculo"]);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->bindParam(':estado', $as_estado);
                $query->bindParam(':id_venta', $id_venta);
                $query->bindParam(':fecha_registro', $fecha_emision);
                $query->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $query->execute();

                // Si todo fue bien, confirmar la transacción
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "reserva_grupal",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => [
                        [
                            "id_obj_vehiculo" => $asiento["id_obj_vehiculo"],
                            "estado" => "RESERVADO",
                            "id_venta" => $id_venta,
                            "terminal" => $this->nombre_terminal_sesion,
                            "terminal_color" => $this->color_terminal_sesion,
                            "id_usuario" => $this->id_usuario_sesion
                        ]
                    ],
                ]);
            }

            // Formulación de la respuesta
            $link_comprobante = '';
            $show_link = false;
            $message = "Asientos reservados con éxito";
            return [
                "success" => true,
                "message" => [
                    "message" => $message,
                    "link_comprobante" => $link_comprobante,
                    "show_link" => $show_link,
                    "estado_sunat" => 0,
                    "message_sunat" => ''
                ]
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos"));
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
            }
        }
    }

    public function reservar_todos($data)
    {
        $conn = $this->db->connect();
        try {
            $conn->beginTransaction();

            $fecha_emision = date("Y-m-d H:i:s");
            $as_estado = 'RESERVADO';
            $asientos = isset($data['numeros_asientos'])
                ? json_decode($data['numeros_asientos'], true)
                : [];
            $data['tiempo_reserva'] = isset($data['tiempo_reserva']) ? $data['tiempo_reserva'] : null;

            // Extrayendo precios según id_programacion
            $query = $conn->prepare("
            SELECT precio_primer_piso, precio_segundo_piso
            FROM programacion
            WHERE id_programacion = ?
            ");
            $query->execute([$data["id_programacion"]]);
            $precios = $query->fetch(PDO::FETCH_ASSOC);

            if (!$precios) {
                $conn->rollBack();
                return ['success' => false, 'message' => ['message' => 'Programación no encontrada']];
            }

            $asientos_reservados = 0;

            foreach ($asientos as $asiento) {
                $id_obj_vehiculo = $asiento['id_obj_vehiculo'];
                $precio_asiento = ($asiento['piso'] == 1)
                    ? $precios['precio_primer_piso']
                    : $precios['precio_segundo_piso'];

                // Validar estado del asiento (con FOR UPDATE para bloquear la fila en la transacción)
                $query_estado = $conn->prepare("
                SELECT id_programacion_obj
                FROM programacion_obj 
                WHERE id_obj_vehiculo = :id_obj_vehiculo 
                AND id_programacion = :id_programacion 
                AND estado IN ('VENDIDO', 'RESERVADO')
                FOR UPDATE
               ");
                $query_estado->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query_estado->bindParam(':id_programacion', $data["id_programacion"]);
                $query_estado->execute();

                // Si el asiento ya está ocupado, saltar al siguiente
                if ($query_estado->fetch()) {
                    continue;
                }

                // Insertar venta
                $vacio = '';
                $stmt_venta = $conn->prepare("
                INSERT INTO venta (
                    id_terminal, codigo, id_vendedor, id_cliente, serie,
                    fecha_emision, estado, id_sesionpersonal, id_tp_venta)
                VALUES (
                    :id_terminal, :codigo, :id_vendedor, :id_cliente, :serie,
                    :fecha_emision, :estado, :id_sesionpersonal, '1')
                ");
                $stmt_venta->bindParam(':id_terminal', $this->id_terminal_sesion);
                $stmt_venta->bindParam(':codigo', $vacio);
                $stmt_venta->bindParam(':serie', $vacio);
                $stmt_venta->bindParam(':fecha_emision', $fecha_emision);
                $stmt_venta->bindParam(':id_vendedor', $this->id_usuario_sesion);
                $stmt_venta->bindParam(':id_cliente', $this->id_usuario_sesion);
                $stmt_venta->bindParam(':estado', $as_estado);
                $stmt_venta->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $stmt_venta->execute();

                // Obtener el ID real de la venta recién insertada
                $id_venta = $conn->lastInsertId();

                // Insertar detalle de venta
                $stmt_dt = $conn->prepare("
                INSERT INTO dt_venta (
                    id_venta, id_tp_servicio, id_programacion, piso, num_asiento,
                    estado_asiento, precio, id_pasajero, op_igv, op_gravada,
                    op_exonerada, op_inafecta, op_total, id_sesionpersonal)
                VALUES (
                    :id_venta, 1, :id_programacion, :piso, :num_asiento,
                    :estado_asiento, :precio, :id_pasajero, 0.00, 0.00,
                    :op_exonerada, 0.00, :op_total, :id_sesionpersonal)
                ");
                $stmt_dt->bindParam(':id_venta', $id_venta);
                $stmt_dt->bindParam(':id_programacion', $data["id_programacion"]);
                $stmt_dt->bindParam(':piso', $asiento["piso"]);
                $stmt_dt->bindParam(':num_asiento', $asiento["num_asiento"]);
                $stmt_dt->bindParam(':estado_asiento', $as_estado);
                $stmt_dt->bindParam(':precio', $precio_asiento);
                $stmt_dt->bindParam(':id_pasajero', $this->id_usuario_sesion);
                $stmt_dt->bindParam(':op_exonerada', $precio_asiento);
                $stmt_dt->bindParam(':op_total', $precio_asiento);
                $stmt_dt->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $stmt_dt->execute();

                // Eliminar registro previo de programacion_obj para evitar duplicidad
                $stmt_delete = $conn->prepare("
                    DELETE FROM programacion_obj 
                     WHERE id_obj_vehiculo = :id_obj_vehiculo 
                     AND id_programacion = :id_programacion
                     AND estado_proceso = 1
                ");
                $stmt_delete->bindParam(':id_obj_vehiculo', $asiento["id_obj_vehiculo"]);
                $stmt_delete->bindParam(':id_programacion', $data["id_programacion"]);
                $stmt_delete->execute();

                // Insertar en programacion_obj
                $stmt_obj = $conn->prepare("
                INSERT INTO programacion_obj (
                    id_obj_vehiculo, id_programacion, estado, id_venta,
                    estado_proceso, fecha_registro, cod_usuario, tiempo_reserva)
                VALUES (
                    :id_obj_vehiculo, :id_programacion, :estado, :id_venta,
                    0, :fecha_registro, :cod_usuario, :tiempo_reserva)
                ");
                $stmt_obj->bindParam(':id_obj_vehiculo', $asiento["id_obj_vehiculo"]);
                $stmt_obj->bindParam(':id_programacion', $data["id_programacion"]);
                $stmt_obj->bindParam(':estado', $as_estado);
                $stmt_obj->bindParam(':id_venta', $id_venta);
                $stmt_obj->bindParam(':fecha_registro', $fecha_emision);
                $stmt_obj->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $stmt_obj->bindParam(':tiempo_reserva', $data['tiempo_reserva']);
                $stmt_obj->execute();

                $asientos_reservados++;

                $asientos_payload[] = [
                    "id_obj_vehiculo" => $id_obj_vehiculo,
                    "estado" => "RESERVADO",
                    "id_venta" => $id_venta,
                    "terminal" => $this->nombre_terminal_sesion,
                    "terminal_color" => $this->color_terminal_sesion,
                    "id_usuario" => $this->id_usuario_sesion
                ];
            }
            // Confirmar toda la transacción
            $conn->commit();

            if ($asientos_reservados > 0) {
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "reservar_todos",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => $asientos_payload
                ]);
            }

            return [
                "success" => true,
                "message" => [
                    "message" => "Se reservaron {$asientos_reservados} asiento(s) con éxito",
                    "link_comprobante" => '',
                    "show_link" => false,
                    "estado_sunat" => 0,
                    "message_sunat" => ''
                ]
            ];
        } catch (PDOException $e) {
            $conn->rollBack();

            return [
                'success' => false,
                'message' => [
                    'message' => $e->getCode() === '23000'
                        ? 'Ya existe un registro con estos datos'
                        : $e->getMessage()
                ]
            ];
        }
    }

    public function liberar_reservas($data)
    {
        try {
            $conn = $this->db->connect();
            $id_serie = isset($data["serie_venta"]) ? $data["serie_venta"] : '';
            $fecha_emision = date("Y-m-d H:i:s");
            $fecha_vencimiento = date("Y-m-d");
            $asientos_estado = 'RESERVADO';

            // Consultando permiso para desbloquear reservas
            $query_permiso = $conn->prepare("
                SELECT p_desbloquear_reservado
                FROM permiso
                WHERE id_usuario = :id_usuario
            ");
            $query_permiso->bindParam(':id_usuario', $this->id_usuario_sesion);
            $query_permiso->execute();

            $permiso = $query_permiso->fetch(PDO::FETCH_ASSOC);
            $tienePermiso = (int)($permiso['p_desbloquear_reservado'] ?? 0) === 1;

            // Limpiando y procesando los números de asiento
            $asientos_numeros = rtrim($data['numeros_asientos'], ',');
            $asientos_numeros = explode(',', $asientos_numeros);
            $asientos_numeros = array_map('intval', $asientos_numeros);

            if (empty($asientos_numeros)) {
                return array(
                    'success' => false,
                    'message' => 'No se proporcionaron números de asiento.'
                );
            }

            // Generando la lista de placeholders para la cláusula IN
            $placeholders = implode(',', array_fill(0, count($asientos_numeros), '?'));

            // Iniciando la transacción
            $conn->beginTransaction();

            // Consulta para obtener los id_obj_vehiculo basados en los asientos
            $query = $conn->prepare("
                SELECT id_obj_vehiculo, piso, TRIM(text_obj) AS num_asiento
                FROM obj_vehiculo 
                WHERE TRIM(text_obj) IN ($placeholders) 
                AND id_vehiculo = ?
            ");

            $query->execute(array_merge($asientos_numeros, [$data["id_vehiculo"]]));
            $asientos = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($asientos)) {
                $conn->rollBack();

                return array(
                    'success' => false,
                    'message' => 'No se encontraron asientos correspondientes.'
                );
            }

            $liberados = [];
            $no_liberados = [];

            foreach ($asientos as $asiento) {

                $id_obj_vehiculo = $asiento['id_obj_vehiculo'];

                $query_estado = $conn->prepare("
                    SELECT
                        p.id_venta,
                        p.cod_usuario AS id_usuario_reserva
                    FROM programacion_obj p
                    WHERE p.id_obj_vehiculo = :id_obj_vehiculo
                    AND p.id_programacion = :id_programacion
                    AND p.estado = :estado
                ");

                $query_estado->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query_estado->bindParam(':id_programacion', $data["id_programacion"]);
                $query_estado->bindParam(':estado', $asientos_estado);
                $query_estado->execute();

                $estado_asiento = $query_estado->fetch(PDO::FETCH_ASSOC);

                if (empty($estado_asiento)) {
                    $no_liberados[] = [
                        'asiento' => $asiento['num_asiento'],
                        'motivo' => 'El asiento no está reservado.'
                    ];

                    continue;
                }

                // Validando si la reserva pertenece al usuario
                $esMiReserva = (int)$estado_asiento['id_usuario_reserva'] === (int)$this->id_usuario_sesion;

                // Si no es su reserva y no tiene permiso, no puede liberarla
                if (!$esMiReserva && !$tienePermiso) {

                    $no_liberados[] = [
                        'asiento' => $asiento['num_asiento'],
                        'motivo' => 'No tiene permisos de desbloqueo para esta reserva.'
                    ];

                    continue;
                }

                $id_venta = $estado_asiento['id_venta'];

                // Anulando el detalle de la venta
                $query = $conn->prepare("
                    UPDATE dt_venta
                    SET estado_asiento = 'ANULADO'
                    WHERE id_venta = :id_venta
                ");
                $query->bindParam(':id_venta', $id_venta);
                $query->execute();

                // Cambiando el estado del objeto
                $query = $conn->prepare("
                    UPDATE programacion_obj
                    SET estado = 'ANULADO'
                    WHERE id_venta = :id_venta
                ");
                $query->bindParam(':id_venta', $id_venta);
                $query->execute();

                // Anulando la venta
                $query = $conn->prepare("
                    UPDATE venta
                    SET estado = 'ANULADO',
                        total = '0.00'
                    WHERE id_venta = :id_venta
                ");
                $query->bindParam(':id_venta', $id_venta);
                $query->execute();

                $liberados[] = $asiento['num_asiento'];

                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "liberar_reserva",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => [
                        [
                            "id_obj_vehiculo" => $asiento["id_obj_vehiculo"],
                            "estado" => null,
                            "id_venta" => null,
                        ]
                    ],
                ]);
            }

            // Confirmando la transacción
            $conn->commit();

            return array(
                'success' => true,
                'message' => 'Proceso de liberación completado.',
                'liberados' => $liberados,
                'no_liberados' => $no_liberados
            );
        } catch (PDOException $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {
                case '23000':
                    return array(
                        'success' => false,
                        'message' => 'Ya existe un registro con estos datos: ' . $e->getMessage()
                    );

                default:
                    return array(
                        'success' => false,
                        'message' => 'Ha ocurrido un error, intenta más tarde.',
                        $e
                    );
            }
        }
    }

    //Proceso para cambiar numero de asiento
    public function cambiar_asiento($data)
    {
        try {
            $fecha_emision = date("Y-m-d H:i:s");
            $estado_venta = "VENDIDO";
            // Conexión a la base de datos
            $dbConnection = $this->db->connect();

            $n_asiento = rtrim($data['asien'], ',');
            $n_asiento = explode(',', $n_asiento);
            $n_asiento = array_map('intval', $n_asiento);

            $placeholders = str_repeat('?,', count($n_asiento) - 1) . '?';

            $query = $this->db->connect()->prepare("
                SELECT id_obj_vehiculo, piso, TRIM(text_obj) AS num_asiento
                FROM obj_vehiculo 
                WHERE TRIM(text_obj) IN ($placeholders) 
                AND id_vehiculo = ?
                LIMIT 1
            ");

            $values = array_merge($n_asiento, [$data['id_vehi_a']]);
            $query->execute($values);
            $id_obj_asiento = $query->fetch(PDO::FETCH_ASSOC);

            if (!isset($id_obj_asiento['id_obj_vehiculo'])) {
                return array("success" => false, "message" => "El numero de asiento ingresado no existe.");
            }

            // Primera consulta
            $query1 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado_proceso=1");
            $query1->bindParam(':id_obj_vehiculo', $id_obj_asiento["id_obj_vehiculo"]);
            $query1->bindParam(':id_programacion', $data["id_pro_a"]);
            $query1->execute();
            $reply1 = $query1->fetchAll(PDO::FETCH_ASSOC);

            // Segunda consulta
            $query2 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado IN ('VENDIDO', 'RESERVADO')");
            $query2->bindParam(':id_obj_vehiculo', $id_obj_asiento["id_obj_vehiculo"]);
            $query2->bindParam(':id_programacion', $data["id_pro_a"]);
            $query2->execute();
            $reply2 = $query2->fetchAll(PDO::FETCH_ASSOC);

            // Verificación de resultados y respuesta
            if ($reply2) {
                return array("success" => false, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta.");
            } else if ($reply1) {
                return array("success" => false, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta.");
            } else {
                // Eliminar el registro de programacion objeto
                $query3 = $dbConnection->prepare("DELETE FROM programacion_obj WHERE id_venta = :id_venta AND id_programacion = :id_programacion");
                $query3->bindParam(':id_venta', $data["id_venta_a"]);
                $query3->bindParam(':id_programacion', $data["id_pro_a"]);
                $query3->execute();

                // Insertar nuevo registro
                $query4 = $dbConnection->prepare("INSERT INTO programacion_obj (
                    id_obj_vehiculo, id_venta, id_programacion, estado, estado_proceso, fecha_registro, cod_usuario) 
                VALUES (
                    :id_obj_vehiculo, :id_venta, :id_programacion, :estado, 0, :fecha_registro, :cod_usuario)");
                $query4->bindParam(':id_obj_vehiculo', $id_obj_asiento["id_obj_vehiculo"]);
                $query4->bindParam(':id_venta', $data["id_venta_a"]);
                $query4->bindParam(':id_programacion', $data["id_pro_a"]);
                $query4->bindParam(':estado', $estado_venta);
                $query4->bindParam(':fecha_registro', $fecha_emision);
                $query4->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $query4->execute();

                //Actualizar el numero de asiento del detalle de venta
                $query5 = $dbConnection->prepare("UPDATE dt_venta SET num_asiento = :num_asiento
                WHERE id_venta = :id_venta");
                $query5->bindParam(':num_asiento', $id_obj_asiento["num_asiento"]);
                $query5->bindParam(':id_venta', $data["id_venta_a"]);
                $query5->execute();

                //Consultar que tipo de comprobante es la venta que se hizo para el formato 
                $query6 = $dbConnection->prepare("SELECT id_tp_comprobante FROM venta
                WHERE id_venta = :id_venta");
                $query6->bindParam(':id_venta', $data["id_venta_a"]);
                $query6->execute();
                $tp_comp = $query6->fetch(PDO::FETCH_ASSOC)['id_tp_comprobante'];

                $link_comprobante = URL . "pasaje/impresion/" . ($tp_comp == 2 ? 'nota_venta/' : 'comprobante/') . $data["id_venta_a"];

                return array("success" => true, "message" => "Se ha realizado el cambio de asiento correctamente", "comprobante" => $link_comprobante);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos", $e));
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
            }
        }
    }

    public function set_estadoProcesoAsiento($data)
    {
        $fecha_emision = date("Y-m-d H:i:s");
        try {
            // Conexión a la base de datos
            $dbConnection = $this->db->connect();

            // Primera consulta
            $query1 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado_proceso=1");
            $query1->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
            $query1->bindParam(':id_programacion', $data["id_programacion"]);
            $query1->execute();
            $reply1 = $query1->fetchAll(PDO::FETCH_ASSOC);

            // Segunda consulta
            $query2 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado IN ('VENDIDO', 'RESERVADO')");
            $query2->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
            $query2->bindParam(':id_programacion', $data["id_programacion"]);
            $query2->execute();
            $reply2 = $query2->fetchAll(PDO::FETCH_ASSOC);

            $cod_usuario = isset($reply1[0]['cod_usuario']) ? $reply1[0]['cod_usuario'] : 0;
            $desbloquear = false;
            $vendedor = null; // Inicialización de $vendedor para evitar errores

            // Consultando quien es el vendedor del asiento
            if ($cod_usuario) {
                $query3 = $dbConnection->prepare("SELECT id_usuario, CONCAT(nombres, ' ', apellidos) AS vendedor FROM usuario WHERE id_usuario=:id_usuario");
                $query3->bindParam(':id_usuario', $cod_usuario);
                $query3->execute();
                $vendedor = $query3->fetch(PDO::FETCH_ASSOC);

                $cod_usuario_int = intval($cod_usuario);
                $usuario_actual = $this->id_usuario_sesion;

                $permisoDesbloquear = $this->tienePermisoDesbloquearReservado();

                if ($cod_usuario_int === $usuario_actual || $permisoDesbloquear) {
                    $desbloquear = true;
                }
            }

            // Verificación de resultados y respuesta
            if ($reply2) {
                return array("success" => true, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta.");
            } else if ($reply1) {
                return array("success" => true, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta", "vendedor" => $vendedor['vendedor'], "desbloquear" => $desbloquear);
            } else {
                $query4 = $dbConnection->prepare("INSERT INTO programacion_obj (
                    id_obj_vehiculo, id_programacion, estado, estado_proceso, fecha_registro, cod_usuario) 
                    VALUES (
                    :id_obj_vehiculo, :id_programacion, :estado, 1, :fecha_registro, :cod_usuario)");
                $query4->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
                $query4->bindParam(':id_programacion', $data["id_programacion"]);
                $query4->bindParam(':estado', $data["estado_venta"]);
                $query4->bindParam(':fecha_registro', $fecha_emision);
                $query4->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $query4->execute();
                return array("success" => false, "message" => "");
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos"));
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
            }
        }
    }

    public function verificador_asientos($data)
    {
        try {
            // Conexión a la base de datos
            $dbConnection = $this->db->connect();
            $fecha_emision = date("Y-m-d H:i:s");
            $asientos_vendidos = [];

            // Convertir los ids_obj_vehiculo de cadena a array
            $ids_obj_vehiculo = explode(',', $data['ids_obj_vehiculo']);

            foreach ($ids_obj_vehiculo as $id_obj_vehiculo) {
                // Primera consulta
                $query1 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado_proceso=1");
                $query1->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query1->bindParam(':id_programacion', $data["id_programacion"]);
                $query1->execute();
                $reply1 = $query1->fetchAll(PDO::FETCH_ASSOC);

                // Segunda consulta
                $query2 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado IN ('VENDIDO', 'RESERVADO')");
                $query2->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query2->bindParam(':id_programacion', $data["id_programacion"]);
                $query2->execute();
                $reply2 = $query2->fetchAll(PDO::FETCH_ASSOC);

                // Verificación de resultados
                if (!empty($reply2)) {
                    $asientos_vendidos[] = [
                        "id_obj_vehiculo" => $id_obj_vehiculo,
                        "estado" => "VENDIDO o RESERVADO"
                    ];
                    continue;
                }

                if (!empty($reply1)) {
                    $asientos_vendidos[] = [
                        "id_obj_vehiculo" => $id_obj_vehiculo,
                        "estado" => "EN PROCESO DE VENTA"
                    ];
                    continue;
                }

                // Si no está vendido ni reservado, insertar en la base de datos
                $query4 = $dbConnection->prepare(
                    "INSERT INTO programacion_obj (
                        id_obj_vehiculo, id_programacion, estado, estado_proceso, fecha_registro, cod_usuario
                    ) VALUES (
                        :id_obj_vehiculo, :id_programacion, :estado, 1, :fecha_registro, :cod_usuario
                    )"
                );
                $query4->bindParam(':id_obj_vehiculo', $id_obj_vehiculo);
                $query4->bindParam(':id_programacion', $data["id_programacion"]);
                $query4->bindParam(':estado', $data["estado_venta"]);
                $query4->bindParam(':fecha_registro', $fecha_emision);
                $query4->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $query4->execute();
            }

            return [
                "success" => empty($asientos_vendidos),
                "message" => empty($asientos_vendidos) ? "Los asientos fueron procesados correctamente." : "Algunos asientos no se pudieron procesar.",
                "asientos_vendidos" => $asientos_vendidos
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return [
                        'success' => false,
                        "message" => "Ya existe un registro con estos datos."
                    ];
                default:
                    return [
                        'success' => false,
                        "message" => $e->getMessage()
                    ];
            }
        }
    }

    //Funcina para verfificar un asiento desde la venta web 
    public function verificar_asiento($data)
    {
        try {
            $fecha_emision = date("Y-m-d H:i:s");
            $data['estado_venta'] = 'PROCESO_WEB';
            // Conexión a la base de datos
            $dbConnection = $this->db->connect();

            // Primera consulta
            $query1 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado_proceso=1");
            $query1->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
            $query1->bindParam(':id_programacion', $data["id_programacion"]);
            $query1->execute();
            $reply1 = $query1->fetchAll(PDO::FETCH_ASSOC);

            // Segunda consulta
            $query2 = $dbConnection->prepare("SELECT * FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado IN ('VENDIDO', 'RESERVADO')");
            $query2->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
            $query2->bindParam(':id_programacion', $data["id_programacion"]);
            $query2->execute();
            $reply2 = $query2->fetchAll(PDO::FETCH_ASSOC);


            // Verificación de resultados y respuesta
            if ($reply2) {
                return array("success" => false, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta.");
            } else if ($reply1) {
                return array("success" => false, "message" => "El asiento seleccionado ya se vendió o se encuentra en proceso de venta.");
            } else {
                $query4 = $dbConnection->prepare("INSERT INTO programacion_obj (
                    id_obj_vehiculo, id_programacion, estado, estado_proceso, fecha_registro, cod_usuario) 
                    VALUES (
                    :id_obj_vehiculo, :id_programacion, :estado, 1, :fecha_registro, :cod_usuario)");
                $query4->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
                $query4->bindParam(':id_programacion', $data["id_programacion"]);
                $query4->bindParam(':estado', $data["estado_venta"]);
                $query4->bindParam(':fecha_registro', $fecha_emision);
                $query4->bindParam(':cod_usuario', $this->id_usuario_sesion);
                $query4->execute();

                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "asiento_en_proceso",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => [
                        [
                            "id_obj_vehiculo" => $data["id_obj_vehiculo"],
                            "estado" => "PROCESO_WEB",
                            "id_venta" => null,
                        ]
                    ],
                ]);
                return array("success" => true, "message" => "Asiento disponible :]");
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos"), $e);
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
            }
        }
    }

    public function delete_estadoProcesoAsiento($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM programacion_obj WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_programacion=:id_programacion AND estado_proceso=1");
            $query->bindParam(':id_obj_vehiculo', $data["id_obj_vehiculo"]);
            $query->bindParam(':id_programacion', $data["id_programacion"]);
            $query->execute();

            return ['success' => true, 'message' => 'Se ha liberado el asiento'];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos"));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
                    break;
            }
        }
    }

    public function liberar_asientos($data)
    {
        $asientos = json_decode($data['asientos'], true);

        if (empty($asientos)) {
            return ['success' => false, 'message' => 'No se enviaron asientos para liberar'];
        }

        try {
            $conn = $this->db->connect();

            foreach ($asientos as $asiento) {
                $query = $conn->prepare("
                DELETE FROM programacion_obj 
                WHERE id_obj_vehiculo = :id_obj_vehiculo 
                  AND id_programacion = :id_programacion 
                  AND estado_proceso = 1
                ");

                $query->bindParam(':id_obj_vehiculo', $asiento["idAsiento"]);
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->execute();
            }

            $asientos_payload = array_map(fn($a) => [
                "id_obj_vehiculo" => $a["idAsiento"],
                "estado" => null,
                "id_venta" => null,
            ], $asientos);

            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                "tipo" => "asientos_liberados",
                "id_programacion" => $data["id_programacion"],
                "asientos" => $asientos_payload,
            ]);
            return ['success' => true, 'message' => 'Asientos liberados correctamente'];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al liberar asientos: ' . $e->getMessage()
            ];
        }
    }

    public function liberar_asiento(array $data)
    {
        // Input validation
        if (!isset($data['id_obj_vehiculo']) || !isset($data['id_programacion'])) {
            return [
                'success' => false,
                'message' => 'Datos de entrada incompletos'
            ];
        }

        try {
            // Prepare the database connection
            $conexion = $this->db->connect();

            // Prepare the delete statement
            $query = $conexion->prepare(
                "DELETE FROM programacion_obj 
                 WHERE id_obj_vehiculo = :id_obj_vehiculo 
                 AND id_programacion = :id_programacion 
                 AND estado_proceso = 1"
            );

            // Bind parameters with type casting
            $query->bindParam(':id_obj_vehiculo', $data['id_obj_vehiculo'], PDO::PARAM_INT);
            $query->bindParam(':id_programacion', $data['id_programacion'], PDO::PARAM_INT);

            // Execute the query
            $resultado = $query->execute();
            if ($resultado && $query->rowCount() > 0) {
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "asiento_liberado",
                    "id_programacion" => $data["id_programacion"],
                    "asientos" => [
                        [
                            "id_obj_vehiculo" => $data["id_obj_vehiculo"],
                            "estado" => null,   // null = libre, sin color de estado
                            "id_venta" => null,
                        ]
                    ],
                ]);
            }

            if ($resultado && $query->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Asiento liberado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se encontró el asiento para liberar'
                ];
            }
        } catch (PDOException $e) {
            // Specific error handling
            switch ($e->getCode()) {
                case '23000':
                    return [
                        'success' => false,
                        'message' => 'Restricción de integridad: No se puede liberar el asiento'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => 'Error en la base de datos: ' . $e->getMessage()
                    ];
            }
        }
    }
    public function set_postponerPasaje($data)
    {
        try {
            $conn = $this->db->connect();
            $fecha_actual = date('Y-m-d');
            $estado_postpuesto = 'pendiente';
            $obs = '';

            $query = $conn->prepare("
        SELECT 
            v.id_cliente,
            v.total AS monto_original,
            dt.id_programacion AS id_programacion_original
        FROM venta v
        INNER JOIN dt_venta dt ON dt.id_venta = v.id_venta
        WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();
            $data_venta = $query->fetch(PDO::FETCH_ASSOC);

            $query = $conn->prepare("
            INSERT INTO pasajes_pospuestos (
              id_venta_original,
              id_programacion_original,
              id_cliente,
              monto_original,
              saldo_a_favor,
              estado,
              fecha_postergacion,
              observacion
            )
            VALUES
            (
              :id_venta_original,
              :id_programacion_original,
              :id_cliente,
              :monto_original,
              :saldo_a_favor,
              :estado,
              :fecha_postergacion,
              :observacion
            )
            ");
            $query->bindParam(":id_venta_original", $data["id_venta"]);
            $query->bindParam(
                ":id_programacion_original",
                $data_venta["id_programacion_original"]
            );
            $query->bindParam(":id_cliente", $data_venta["id_cliente"]);
            $query->bindParam(":monto_original", $data_venta["monto_original"]);
            $query->bindParam(":saldo_a_favor", $data_venta["monto_original"]);
            $query->bindParam(":estado", $estado_postpuesto);
            $query->bindParam(":fecha_postergacion", $fecha_actual);
            $query->bindParam(":observacion", $obs);
            $query->execute();

            /*================================= Actualizar o en este caso  anular el obj_programcion =================================== */

            $query = $conn->prepare("UPDATE programacion_obj SET estado='ANULADO' WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->execute();

            $query = $conn->prepare("UPDATE dt_venta SET estado_asiento='POSPUESTO' WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->execute();

            $query = $this->db->connect()->prepare("UPDATE venta SET estado='POSPUESTO' WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->execute();
            $q = $conn->prepare("
             SELECT po.id_obj_vehiculo, dt.id_programacion 
             FROM programacion_obj po
             JOIN dt_venta dt ON po.id_venta = dt.id_venta
             WHERE po.id_venta = :id_venta 
             LIMIT 1
            ");
            $q->execute([':id_venta' => $data["id_venta"]]);
            $info_asiento = $q->fetch(PDO::FETCH_ASSOC);

            $id_obj_vehiculo_de_la_venta = $info_asiento["id_obj_vehiculo"];
            $id_programacion_de_la_venta = $info_asiento["id_programacion"];
            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                "tipo" => "pasaje_postergado",
                "id_programacion" => $id_programacion_de_la_venta,
                "asientos" => [
                    [
                        "id_obj_vehiculo" => $id_obj_vehiculo_de_la_venta,
                        "estado" => null,
                        "id_venta" => $data["id_venta"],
                    ]
                ],
            ]);
            return ['success' => true, 'message' => 'Se ha postergado el pasaje correctamente'];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Ooops... error en el servidor: " . $e->getMessage()];
        }
    }

    /*================================= Metodo para hacer la actualizacion de la venta cuando se aplique el postpuesto =================================== */
    public function update_postponerPasaje($data)
    {
        try {
            $fecha_registro = date("Y-m-d H:i:s");
            // Obteniendo el tp_comprobante para el link_impresion
            $query = $this->db->connect()->prepare("SELECT id_tp_comprobante FROM venta WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPostponer"]);
            $query->execute();
            $id_tp_comprobante = $query->fetch(PDO::FETCH_ASSOC)["id_tp_comprobante"];

            // Actualizando la nueva detalle venta
            $query = $this->db->connect()->prepare("UPDATE dt_venta SET
                id_programacion=:id_programacion,
                piso=:piso,
                num_asiento=:num_asiento
                WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPostponer"]);
            $query->bindParam(':id_programacion', $data["id_nuevaProgramacion"]);
            $query->bindParam(':piso', $data["num_nuevoPiso"]);
            $query->bindParam(':num_asiento', $data["num_nuevoAsiento"]);
            $query->execute();

            // Registrando nueva programacion objeto
            $query = $this->db->connect()->prepare("UPDATE programacion_obj SET
                id_obj_vehiculo=:id_obj_vehiculo,
                id_programacion=:id_programacion,
                fecha_registro=:fecha_registro
                WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPostponer"]);
            $query->bindParam(':id_obj_vehiculo', $data["id_asiento_selected"]);
            $query->bindParam(':id_programacion', $data["id_nuevaProgramacion"]);
            $query->bindParam(':fecha_registro', $fecha_registro);
            $query->execute();

            $link_comprobante = URL . "pasaje/impresion/" . ($id_tp_comprobante == 2 ? 'nota_venta/' : 'comprobante/') . $data["id_ventaPostponer"];
            return array('success' => true, "message" => array("message" => "Venta pospuesta con éxito", "link_comprobante" => $link_comprobante));
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function pagar_reservaPasaje($data)
    {
        try {
            $id_serie = $data["serie_venta"];
            $fecha_emision = date("Y-m-d H:i:s");
            $fecha_vencimiento = date("Y-m-d");
            $correlativo = $this->get_correlativo($data);
            $estado_pago = 'PAGADO';

            // Obteniendo el tp_comprobante para el link_impresion
            $query = $this->db->connect()->prepare("SELECT id_tp_comprobante FROM venta WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->execute();
            $id_tp_comprobante = $query->fetch(PDO::FETCH_ASSOC)["id_tp_comprobante"];
            $id_cliente  = !empty($data['cliente_id']) ? $data['cliente_id'] : null;
            $id_pasajero = !empty($data['pasajero_id']) ? $data['pasajero_id'] : $id_cliente;

            $query = $this->db->connect()->prepare("UPDATE venta SET
                id_cliente = :id_cliente,
                id_forma_pago=1,
                id_medio_pago=:id_medio_pago,
                id_tp_moneda=:id_tp_moneda,
                id_tp_comprobante=:id_tp_comprobante,
                id_caja_chica=:id_caja_chica,
                id_serie=:id_serie,
                serie=(SELECT serie FROM serie WHERE id_serie=$id_serie),
                correlativo=:correlativo,
                descuento=0.00,
                op_igv='0.00',
                estado=:estado,
                envio_sunat=0,
                descrip_cdr_sunat='',
                hash_cdr='',
                fecha_emision=:fecha_emision,
                op_gravada=0.00,
                op_exonerada=:op_exonerada,
                op_inafecta=0.00,
                total=:total,
                fecha_vencimiento=:fecha_vencimiento,
                obs=:obs,
                id_sesionpersonal=:id_sesionpersonal
            WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->bindValue(':id_cliente', $id_cliente);
            $query->bindParam(':id_medio_pago', $data["medio_pago"]);
            $query->bindParam(':id_tp_moneda', $this->tp_moneda);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':id_caja_chica', $data["destino"]);
            $query->bindParam(':id_serie', $data["serie_venta"]);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':estado', $estado_pago);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':op_exonerada', $data["precio_venta"]);
            $query->bindParam(':total', $data["precio_venta"]);
            $query->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $query->bindParam(':obs', $data["referencia"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->execute();

            // Registrando el detalle venta
            $query = $this->db->connect()->prepare("UPDATE dt_venta SET
            estado_asiento=:estado_asiento,
            id_pasajero=:id_pasajero,
            op_igv=0.00,
            op_gravada=0.00,
            op_exonerada=:op_exonerada,
            op_inafecta=0.00,
            op_total=:op_total,
            id_sesionpersonal=:id_sesionpersonal
            WHERE id_venta=:id_venta");

            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->bindParam(':estado_asiento', $data["estado_venta"]);
            $query->bindValue(':id_pasajero', $id_pasajero);
            $query->bindParam(':op_exonerada', $data["precio_venta"]);  // Corregido aquí
            $query->bindParam(':op_total', $data["precio_venta"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->execute();

            // Registrando programacion objeto
            $query = $this->db->connect()->prepare("UPDATE programacion_obj SET estado=:estado WHERE id_obj_vehiculo=:id_obj_vehiculo AND id_venta=:id_venta");
            $query->bindParam(':id_obj_vehiculo', $data["id_asiento_selected"]);
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->bindParam(':estado', $data["estado_venta"]);
            $query->execute();

            // Generando el código QR y facturación
            if (in_array($data["tp_comprobante"], [1, 3])) {
                $this->generar_codQR($data["id_ventaPasaje"]);
                // api facturación
                $rpta_sunat = $this->enviar_json_a_api($this->conversor_data($this->getDataComprobante($data["id_ventaPasaje"])));
                //decodificamos el json
                $resp = json_decode($rpta_sunat, true);
                // Actualizando los datos de la venta
                $resp[0]["id_venta"] = $data["id_ventaPasaje"];
                $this->updateVentaSetForSunat($resp[0]);
            }

            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                "tipo" => "venta_pasaje",
                "id_programacion" => $data["id_programacion"],
                "asientos" => [
                    [
                        "id_obj_vehiculo" => $data["id_asiento_selected"],
                        "estado" => $data["estado_venta"], // "VENDIDO"
                        "id_venta" => $data["id_ventaPasaje"] ?? null,
                        "terminal" => $this->nombre_terminal_sesion,
                        "terminal_color" => $this->color_terminal_sesion,
                        "id_usuario" => $this->id_usuario_sesion
                    ]
                ],
            ]);
            // formulación de la respuesta
            $link_comprobante = URL . "pasaje/impresion/" . ($data["tp_comprobante"] == 2 ? 'nota_venta/' : 'comprobante/') . $data["id_ventaPasaje"];
            $show_link = $data["estado_venta"] == "VENDIDO" ? true : false;
            return array(
                'success' => true,
                "message" => array(
                    "message" => "Venta registrada con éxito",
                    "link_comprobante" => $link_comprobante,
                    "show_link" => $show_link,
                    "estado_sunat" => isset($resp) ? $resp[0]["estado"] : 0,
                    "message_sunat" => isset($resp) ? $resp[0]["mensaje_sunat"] : ''
                )
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function anular_venta($data)
    {
        try {
            //Consultar si el terminal es el correcto para ver si el proceso realiza o no 
            $conn = $this->db->connect();
            $query = $conn->prepare("
            SELECT t.id_terminal 
            FROM programacion_obj p
            LEFT JOIN usuario u ON p.cod_usuario = u.id_usuario
            LEFT JOIN terminal t ON u.id_terminal = t.id_terminal
            WHERE p.id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->execute();
            $id_terminal_venta = $query->fetch(PDO::FETCH_ASSOC);

            $query = $conn->prepare("
            SELECT 
            id_tp_comprobante 
            FROM venta v
            WHERE v.id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->execute();
            $tp_comprobante = $query->fetchColumn();

            $data['tp_comprobante'] = $tp_comprobante;

            if ($id_terminal_venta['id_terminal'] == $this->id_terminal_sesion || $this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1) {
                // Anulando el detalle de la venta
                $query = $this->db->connect()->prepare("UPDATE dt_venta SET estado_asiento='ANULADO' WHERE id_venta=:id_venta");
                $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
                $query->execute();

                // Cambiando el estado del objeto
                $query = $this->db->connect()->prepare("UPDATE programacion_obj SET estado='ANULADO' WHERE id_venta=:id_venta");
                $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
                $query->execute();

                // Anular Comprobane Electrónico
                if ($data["tp_comprobante"] == 1 || $data["tp_comprobante"] == 3) {
                    if ($data["tp_comprobante"] == 1) {
                        $id_resumen = $this->set_resumen_f($data["id_ventaPasaje"]);
                    } else {
                        $id_resumen = $this->set_resumen($data["id_ventaPasaje"]);
                    }
                    $json = $this->resumen($data["id_ventaPasaje"], $id_resumen);
                    $resp_api = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);
                    //Guardamos el ticket para luego usarlo en la obtencion de cdr
                    $resp = $this->guardar_ticket($resp_api, $id_resumen);
                } else {
                    $resp = "Venta anulada correctamente";
                }
                // Anulando la venta
                $conn = $this->db->connect();
                $query = $conn->prepare("UPDATE venta SET estado='ANULADO', total='0.00', op_exonerada='0.00', porcentaje_venta='0.00', factor_p_venta='0.00' WHERE id_venta=:id_venta");
                $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
                $query->execute();

                $q = $conn->prepare("
                    SELECT po.id_obj_vehiculo, dt.id_programacion 
                    FROM programacion_obj po
                    JOIN dt_venta dt ON po.id_venta = dt.id_venta
                    WHERE po.id_venta = :id_venta 
                    LIMIT 1
                ");
                $q->execute([':id_venta' => $data["id_ventaPasaje"]]);
                $info_asiento = $q->fetch(PDO::FETCH_ASSOC);

                $id_programacion_de_la_venta = $info_asiento["id_programacion"];
                $id_obj_vehiculo_de_la_venta = $info_asiento["id_obj_vehiculo"];
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "venta_anulada",
                    "id_programacion" => $id_programacion_de_la_venta, // consultar antes
                    "asientos" => [
                        [
                            "id_obj_vehiculo" => $id_obj_vehiculo_de_la_venta, // consultar antes
                            "estado" => null,   // anulado = libre visualmente
                            "id_venta" => null,
                        ]
                    ],
                ]);
                return [
                    'success' => true,
                    "message" => $resp,
                ];
            } else {
                $resp = "Venta anulada correctamente";
            }

            $query = $conn->prepare("
            UPDATE venta
            SET estado = 'ANULADO',
                total = '0.00',
                op_exonerada = '0.00',
                porcentaje_venta = '0.00',
                factor_p_venta = '0.00'
            WHERE id_venta = :id_venta
           ");
            $query->bindParam(':id_venta', $data["id_ventaPasaje"]);
            $query->execute();

            $q = $conn->prepare("
            SELECT po.id_obj_vehiculo, dt.id_programacion
            FROM programacion_obj po
            JOIN dt_venta dt ON po.id_venta = dt.id_venta
            WHERE po.id_venta = :id_venta
            LIMIT 1
            ");
            $q->execute([':id_venta' => $data["id_ventaPasaje"]]);
            $info_asiento = $q->fetch(PDO::FETCH_ASSOC);

            if (!$info_asiento) {
                throw new Exception("No se pudo obtener la informacion del asiento para notificar.");
            }

            $id_programacion_de_la_venta = $info_asiento["id_programacion"];
            $id_obj_vehiculo_de_la_venta = $info_asiento["id_obj_vehiculo"];

            $conn->commit();

            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                "tipo" => "venta_anulada",
                "id_programacion" => $id_programacion_de_la_venta,
                "asientos" => [
                    [
                        "id_obj_vehiculo" => $id_obj_vehiculo_de_la_venta,
                        "estado" => null,
                        "id_venta" => null,
                    ]
                ],
            ]);

            return [
                'success' => true,
                'message' => $resp,
            ];
        } catch (\Throwable $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return [
                    'success' => false,
                    "message" => "Esta venta o reserva no se puede eliminar, por que otro terminal hizo el proceso",
                ];
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos" . $e->getMessage());
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function consultar_cdr($data)
    {
        $emisor = $this->consult_emisor();
        $consulta = $this->db->connect()->prepare("SELECT ticket, nombre_xml FROM `resumen_baja` WHERE id_resumen=:id_resumen");
        $consulta->bindParam(':id_resumen', $data["id_resumen"]);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        $ticket = $resultado['ticket'];
        $nombre = $resultado['nombre_xml'];


        $json = array(
            "ose" => $emisor['ose'],
            "emisor" => $emisor,
            "cabecera" => array(
                "tipo_comprobante" => "CS",
                "ticket" => $ticket,
                "nombre_xml" => $nombre
            )
        );
        $reply_sunat = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);

        if (!empty($reply_sunat) && is_array($reply_sunat)) {
            $resp = $reply_sunat[0];
            $resp["id_resumen"] = $data['id_resumen'];
            $this->updateResumen($resp);
            return array(
                'success' => true,
                "message" => "CDR consultado correctamente",
            );
        } else {
            return array(
                'success' => false,
                "message" => "Error al consultar CDR",
            );
        }
    }

    public function reenviar_venta($data)
    {
        try {

            $id_venta = $data["id_ventaPasaje"];
            $resp = null;

            if (in_array($data["tp_comprobante"], [1, 3])) {
                $rpta_sunat = $this->enviar_json_a_api($this->conversor_data($this->getDataComprobante($id_venta)));
                $resp = json_decode($rpta_sunat, true);
                $resp[0]["id_venta"] = $id_venta;
                $this->updateVentaSetForSunat($resp[0]);

                if (!isset($resp)) {
                    die("Error: No se obtuvo una respuesta del api");
                }
                $success = true;
                if ($resp[0]['estado'] == 2 || $resp[0]['estado'] == 3) {
                    $success = false;
                }
            }

            $link_comprobante = URL . "pasaje/impresion/" . ($data["tp_comprobante"] == 2 ? 'nota_venta/' : 'comprobante/') . $id_venta;

            return array(
                "success" => $success,
                "message" => array(
                    "message" => $resp[0]["mensaje_sunat"],
                    "link_comprobante" => $link_comprobante,
                    "estado_sunat" => $resp[0]["estado"],
                    "message_sunat" => $resp[0]["mensaje_sunat"],

                )
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':

                    return array('success' => false, "message" => array("message" => "Ya existe un registro con estos datos"));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => $e->getMessage()));
                    break;
            }
        }
    }

    public function reenvio_porResumen($data)
    {
        try {
            date_default_timezone_set('America/Lima');
            $fechaActual = date('Y-m-d');
            $conn = $this->db->connect();
            // Consulta para obtener el último correlativo con dos condiciones
            $query = $conn->prepare("SELECT correlativo FROM resumen_baja WHERE fecha_envio = :fecha_actual AND boleta = :boleta AND tipo_ruc = :tipo_ruc ORDER BY correlativo DESC LIMIT 1");
            $query->bindParam(':fecha_actual', $fechaActual);
            $query->bindValue(':boleta', 1);
            $query->bindValue(':tipo_ruc', $data['tipo_ruc']);
            $query->execute();
            $ultimo_correlativo = $query->fetchColumn();

            if ($ultimo_correlativo === false) {
                $correlativo = 1;
            } else {
                $correlativo = $ultimo_correlativo + 1;
            }

            // Obtener la ultima venta para el resumen
            $query = $conn->prepare("SELECT * FROM venta ORDER BY id_venta DESC LIMIT 1");
            $query->execute();
            $ultima_Venta = $query->fetchColumn();

            // Insertar en la tabla de resumen
            $query = $conn->prepare("INSERT INTO resumen_baja (
            id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo, tipo_ruc)
            VALUES(
            :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo, :tipo_ruc)");
            $query->bindValue(':id_comprobante', $ultima_Venta);
            $query->bindValue(':boleta', 1);
            $query->bindValue(':factura', 1);
            $query->bindParam(':fecha_envio', $fechaActual);
            $query->bindParam(':fecha_referencia', $fechaActual);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':tipo_ruc', $data['tipo_ruc']);
            $query->execute();

            $id_resumen = $conn->lastInsertId();

            if ($data['tipo_ruc'] == 1) {
                $emisor = $this->consult_emisor();
            } else {
                $emisor = $this->consult_emisor_encomienda();
            }

            //datos cabecera resumen
            $query = $conn->prepare("SELECT
                r.fecha_referencia AS fecha_emision,
                r.correlativo,
                r.fecha_envio,
                r.boleta 
            FROM resumen_baja r
            WHERE r.id_resumen =:id_resumen");
            $query->bindParam(":id_resumen", $id_resumen);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            $fecha = date('Y-m-d');
            $serie = str_replace("-", "", $fecha);
            $cabecera["correlativo"];
            $cabecera["fecha_emision"];
            $cabecera["fecha_envio"];
            $cabecera["serie"] = $serie;
            //Obteniendo tipo de dato para cabecera

            if ($cabecera["boleta"] == 1) {
                $cabecera["tipo_comprobante"] = "RC";
                $cabecera["tipodoc"] = "RC";
            } else {
                $cabecera["tipo_comprobante"] = "RA";
                $cabecera["tipodoc"] = "RA";
            }

            $items = [];
            $i = 0;

            $ids = array_filter(array_map('intval', explode(',', $data['ids'])));
            foreach ($ids as $id_venta) {
                $query = $conn->prepare("SELECT
                    c.id_tp_docu AS tipodoc_ad,
                    c.num_docu AS numdoc_ad,
                    tp_c.codigo AS tipodoc,
                    v.serie AS serie,
                    v.correlativo AS correlativo,
                    tp_m.codigo AS moneda,
                    v.total AS importe_total,
                    v.op_gravada AS op_gravadas,
                    v.op_exonerada AS op_exoneradas,
                    v.op_inafecta AS op_inafectas,
                    v.op_igv AS igv_total,
                    v.op_igv AS total_impuestos
                FROM venta v
                LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta= v.id_venta
                LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=v.id_tp_moneda
                LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
                WHERE v.id_venta=:id_venta");
                $query->bindParam(":id_venta", $id_venta);
                $query->execute();
                $detalles = $query->fetchAll(PDO::FETCH_ASSOC);

                // Iterar sobre los resultados y modificar cada ítem
                foreach ($detalles as &$detalle) {
                    $detalle["item"] = ++$i;
                    $detalle["tipodoc"];
                    $detalle["serie"];
                    $detalle["correlativo"];
                    $detalle["condicion"] = "1";
                    $detalle["moneda"];
                    $detalle["importe_total"];
                    $detalle["op_gravadas"];
                    $detalle["op_exoneradas"];
                    $detalle["op_inafectas"];
                    $detalle["igv_total"];
                    $detalle["icbper"] = "0.00";
                    $detalle["codigos"] = [1000, "IGV", "VAT"];
                }

                $items = array_merge($items, $detalles);
            }
            //Devolviendo respuesta
            $respuesta = [
                "ose" => $emisor["ose"],
                "emisor" => $emisor,
                "cabecera" => $cabecera,
                "items" => $items
            ];
            $json = json_encode($respuesta);
            $resp_api = json_decode($this->enviar_json_a_api($json, true), true);
            if (!empty($resp_api[0]['ticket'])) {
                $this->guardar_ticket($resp_api, $id_resumen);

                $stmtUpdate = $conn->prepare("UPDATE venta SET envio_sunat = :estado WHERE id_venta = :id_venta");
                foreach ($ids as $id_venta) {
                    $stmtUpdate->execute([
                        ':estado'   => 2,
                        ':id_venta' => (int)$id_venta
                    ]);
                }

                return array(
                    "success" => true,
                    "message_sunat" => "Se ha creado el resumen " . $resp_api[0]['nombre_xml']
                );
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function resumen_reenvio($id_venta, $id_resumen)
    {
        //Datos del emisor necesario para el resumen
        $query = $this->db->connect()->prepare("SELECT
                e.num_docu AS ruc,
                e.razon_social AS razon_social,
                t.ubigeo AS ubigeo,
                ub.depa AS departamento,
                ub.provi AS provincia,
                ub.distri AS distrito,
                t.direccion_fiscal AS direccion,
                e.user_sol AS usuario_sol,
                e.pass_sol AS clave_sol,
                td.id_tp_docu AS tipodoc
                FROM terminal t
                LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
                LEFT JOIN ubigeo ub ON ub.cod_ubigeo=t.ubigeo
                LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
                LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
                WHERE t.id_terminal=:id_terminal");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);

        //datos cabecera resumen
        $query = $this->db->connect()->prepare("SELECT
                    r.fecha_referencia AS fecha_emision,
                    r.correlativo,
                    r.fecha_envio,
                    r.boleta
                FROM resumen_baja r
                WHERE r.id_resumen =:id_resumen");
        $query->bindParam(":id_resumen", $id_resumen);
        $query->execute();
        $cabecera = $query->fetch(PDO::FETCH_ASSOC);

        //Obteniendo serie

        $fecha = date('Y-m-d');
        $serie = str_replace("-", "", $fecha);
        $cabecera["serie"] = $serie;
        //Obteniendo tipo de dato para cabecera

        if ($cabecera["boleta"] == 1) {
            $cabecera["tipo_comprobante"] = "RC";
            $cabecera["tipodoc"] = "RC";
        } else {
            $cabecera["tipo_comprobante"] = "RA";
            $cabecera["tipodoc"] = "RA";
        }


        //Datos de items resumen
        $query = $this->db->connect()->prepare("SELECT
                    tp_c.codigo AS tipodoc,
                    v.serie,
                    v.correlativo,
                    tp_m.codigo AS moneda,
                    v.total AS importe_total,
                    v.op_gravada AS op_gravadas,
                    v.op_exonerada AS op_exoneradas,
                    v.op_inafecta AS op_inafectas,
                    v.fecha_emision,
                    date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision,
                    v.op_igv AS igv_total,
                    v.op_igv AS total_impuestos
                FROM venta v
                LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta= v.id_venta
                LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=v.id_tp_moneda
                WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $items = $query->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        foreach ($items as &$item) {
            $item["item"] = ++$i;
            $item["condicion"] = "3";
            $item["motivo"] = "Error en Documento";
            $item["codigos"] = array(1000, "IGV", "VAT"); // Agrega el campo "item" con el número de ítem
        }

        //Devolviendo respuesta
        return array(
            "emisor" => $emisor,
            "cabecera" => $cabecera,
            "items" => $items
        );
    }

    public function get_allTerminalDestinoForOrigen($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                t.id_terminal,
                t.id_empresa,
                t.logo,
                t.nombre,
                t.ubigeo,
                t.direccion_fiscal,
                t.direccion_comercial,
                t.cod_domicilio_fiscal,
                t.celular,
                t.email,
                t.siteweb,
                t.fecha_registro,
                e.razon_social AS empresa
            FROM terminal t
            INNER JOIN empresa e ON e.id_empresa=t.id_empresa
            WHERE t.id_terminal!=:id_terminal");
            $query->bindParam(':id_terminal', $data["terminal_origen"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_programacionesForIdTerminal($data)
    {
        try {
            $conn = $this->db->connect();
            $id_terminal_origen = $data["id_terminal_origen"] ?: '%';
            $id_terminal_destino = $data["id_terminal_destino"] ?: '%';
            $fecha_inicio = !empty($data["fecha_inicio"]) ? $data["fecha_inicio"] : date('Y-m-d');
            $fecha_fin = !empty($data["fecha_fin"]) ? $data["fecha_fin"] : $fecha_inicio;

            $per_page = 2;
            $page = isset($data['page']) && $data['page'] > 0 ? (int) $data['page'] : 1;
            $offset = ($page - 1) * $per_page;

            // ── Filtros comunes reutilizables ──────────────────────────────────────
            $where = "
            WHERE (
                p.id_terminal_origen LIKE :id_terminal_origen_1
                OR r.id_terminal LIKE :id_terminal_origen_2
            )
            AND (
                p.id_terminal_destino LIKE :id_terminal_destino_1
                OR rd.id_terminal    LIKE :id_terminal_destino_2
            )
            AND p.fecha_salida BETWEEN :fecha_inicio AND :fecha_fin
            AND p.estado = :estado
            AND p.tipo_programacion != 2
            AND NOT EXISTS (
                SELECT 1 FROM p_liquidacion pl
                WHERE pl.id_terminal    = :id_terminal_sesion
                  AND pl.id_programacion = p.id_programacion
            )
            AND NOT EXISTS (
                SELECT 1 FROM liquidacion_usuario lu
                WHERE lu.id_usuario     = :id_usuario_sesion
                  AND lu.id_programacion = p.id_programacion
            )
            ";

            $baseFrom = "
            FROM programacion p
            LEFT JOIN vehiculo      v    ON v.id_vehiculo  = p.id_vehiculo
            LEFT JOIN terminal      t_ori   ON t_ori.id_terminal  = p.id_terminal_origen
            LEFT JOIN terminal      t_desti ON t_desti.id_terminal = p.id_terminal_destino
            LEFT JOIN usuario       c    ON c.id_usuario   = p.id_conductor
            LEFT JOIN ruta          r    ON r.id_programacion  = p.id_programacion
            LEFT JOIN rutas_destino rd   ON rd.id_programacion = p.id_programacion
            ";

            $countQuery = $conn->prepare("SELECT COUNT(DISTINCT p.id_programacion) AS total $baseFrom $where");
            $this->bindCommonParams($countQuery, $id_terminal_origen, $id_terminal_destino, $fecha_inicio, $fecha_fin, $data['estado']);
            $countQuery->execute();
            $total = (int) $countQuery->fetchColumn();
            $pages = (int) ceil($total / $per_page);

            $query = $conn->prepare("
            SELECT DISTINCT
                p.id_programacion,
                p.id_terminal_origen,
                t_ori.nombre   AS terminal_origen_nombre,
                p.id_terminal_destino,
                t_desti.nombre AS terminal_destino_nombre,
                p.id_vehiculo,
                v.descripcion  AS vehiculo_descripcion,
                v.placa        AS vehiculo_placa,
                p.id_conductor,
                CONCAT(c.nombres, ' ', c.apellidos) AS conductor_nombres,
                c.num_docu     AS conductor_num_docu,
                p.fecha_salida,
                p.hora_salida  AS hora_salida_original,
                CASE 
                   WHEN r.id_terminal IS NOT NULL 
                   AND r.id_terminal LIKE :id_terminal_origen_case
                   THEN TIME_FORMAT(r.hora, '%h:%i %p')
                   ELSE TIME_FORMAT(p.hora_salida, '%h:%i %p')
                END AS hora_salida,
                p.precio_primer_piso,
                p.precio_segundo_piso,
                p.precio_minimo,
                p.id_tp_servicio_pasaje,
                p.estado
            $baseFrom
            $where
            ORDER BY p.fecha_salida ASC, p.hora_salida ASC
            LIMIT :limit OFFSET :offset
            ");

            $this->bindCommonParams($query, $id_terminal_origen, $id_terminal_destino, $fecha_inicio, $fecha_fin, $data['estado']);
            $query->bindValue(':limit', $per_page, PDO::PARAM_INT);
            $query->bindValue(':offset', $offset, PDO::PARAM_INT);
            $query->bindValue(':id_terminal_origen_case', $id_terminal_origen);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => $reply,
                'pagination' => [
                    'total' => $total,
                    'pages' => $pages,
                    'page' => $page,
                    'per_page' => $per_page,
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()];
        }
    }

    private function bindCommonParams($stmt, $ori, $dest, $fi, $ff, $estado)
    {
        $stmt->bindValue(':id_terminal_origen_1', $ori, PDO::PARAM_STR);
        $stmt->bindValue(':id_terminal_origen_2', $ori, PDO::PARAM_STR);
        $stmt->bindValue(':id_terminal_destino_1', $dest, PDO::PARAM_STR);
        $stmt->bindValue(':id_terminal_destino_2', $dest, PDO::PARAM_STR);
        $stmt->bindValue(':fecha_inicio', $fi, PDO::PARAM_STR);
        $stmt->bindValue(':fecha_fin', $ff, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':id_terminal_sesion', $this->id_terminal_sesion, PDO::PARAM_STR);
        $stmt->bindValue(':id_usuario_sesion', $this->id_usuario_sesion, PDO::PARAM_STR);
    }

    public function get_vehiculoForID($data)
    {
        try {
            $db = $this->db->connect();

            $query = $db->prepare("SELECT * FROM vehiculo WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
            $query->execute();
            $vehiculo = $query->fetch(PDO::FETCH_ASSOC);

            $query = $db->prepare("SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
            $query->execute();
            $obj_vehiculo = $query->fetchAll(PDO::FETCH_ASSOC);

            $query = $db->prepare("SELECT
                prg_obj.id_programacion_obj,
                prg_obj.id_obj_vehiculo,
                prg_obj.id_programacion,
                prg_obj.estado,
                prg_obj.id_venta,
                prg_obj.estado_proceso,
                prg_obj.fecha_registro,
                prg_obj.cod_usuario,
                m_p.descripcion AS medio_pago,
                t.nombre AS terminal,
                t.color AS terminal_color
            FROM programacion_obj prg_obj
            LEFT JOIN venta v ON v.id_venta=prg_obj.id_venta
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            LEFT JOIN terminal t ON t.id_terminal=v.id_terminal
            WHERE prg_obj.id_programacion=:id_programacion");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $obj_programacion = $query->fetchAll(PDO::FETCH_ASSOC);

            $estados_validos = ['RESERVADO', 'VENTA_WEB', 'PROCESO_WEB', 'VENDIDO'];
            $programacion_map = [];
            foreach ($obj_programacion as $prog) {
                if (in_array($prog["estado"], $estados_validos)) {
                    $programacion_map[$prog["id_obj_vehiculo"]] = $prog;
                }
            }

            foreach ($obj_vehiculo as &$asiento) {
                $id = $asiento["id_obj_vehiculo"];
                if (isset($programacion_map[$id])) {
                    $prog = $programacion_map[$id];
                    $asiento["estado_asiento"] = $prog["estado"];
                    $asiento["id_venta"] = $prog["id_venta"];
                    $asiento["medio_pago"] = $prog["medio_pago"];
                    $asiento["terminal"] = $prog["terminal"];
                    $asiento["terminal_color"] = $prog["terminal_color"];
                    if ($prog["estado"] === "RESERVADO") {
                        $asiento["seleccion"] = ($prog["cod_usuario"] == $this->id_usuario_sesion) ? 'true' : '';
                    }
                }
            }
            unset($asiento);

            if ($vehiculo || $obj_vehiculo) {
                return ['success' => true, "message" => [$vehiculo, $obj_vehiculo]];
            }

            return ['success' => false, "message" => null];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, "message" => "Ya existe un registro con estos datos"];
            }
            return ['success' => false, "message" => $e->getMessage()];
        }
    }

    public function get_asientosForID($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM vehiculo WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
            $query->execute();
            $vehiculo = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
            $query->execute();
            $obj_vehiculo = $query->fetchAll(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("SELECT
                prg_obj.id_programacion_obj,
                prg_obj.id_obj_vehiculo,
                ob_v.text_obj AS numero_asientos,
                prg_obj.id_programacion,
                prg_obj.estado,
                prg_obj.id_venta,
                prg_obj.estado_proceso,
                prg_obj.fecha_registro,
                m_p.descripcion AS medio_pago,
                t.nombre AS terminal,
                t.color AS terminal_color
            FROM programacion_obj prg_obj
            LEFT JOIN venta v ON v.id_venta=prg_obj.id_venta
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            LEFT JOIN terminal t ON t.id_terminal=v.id_terminal
            LEFT JOIN obj_vehiculo ob_v ON prg_obj.id_obj_vehiculo = ob_v.id_obj_vehiculo
            WHERE prg_obj.id_programacion = :id_programacion AND prg_obj.estado = '' AND prg_obj.id_venta IS NULL AND estado_proceso = 1");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $obj_programacion = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($vehiculo || $obj_programacion) {
                return array('success' => true, "message" => [$vehiculo, $obj_programacion]);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_dataAsiento($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM obj_vehiculo WHERE id_obj_vehiculo=:id_obj_vehiculo");
            $query->bindParam(":id_obj_vehiculo", $data["id_obj_vehiculo"]);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);
            if ($reply) {
                return array('success' => true, "message" => $reply);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_DestinoRutas($data)
    {
        try {
            $data['id_programacion'] = intval($data['id_programacion']);
            $query = $this->db->connect()->prepare("
                SELECT rd.id_ruta_destino, t.*
                FROM rutas_destino rd
                LEFT JOIN terminal t ON rd.id_terminal = t.id_terminal
                WHERE rd.id_programacion = :id_programacion
            ");

            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $rutas_destino = $query->fetchAll(PDO::FETCH_ASSOC);

            //Agregando destino general en los registros
            $query = $this->db->connect()->prepare("
            SELECT t.*
            FROM programacion p
            LEFT JOIN terminal t ON p.id_terminal_destino = t.id_terminal
            WHERE p.id_programacion = :id_programacion
            ");

            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $destino_general = $query->fetch(PDO::FETCH_ASSOC);

            // Si hay resultados, los retornamos
            if (!empty($rutas_destino)) {
                $rutas_destino[] = $destino_general;
                return array('success' => true, "message" => $rutas_destino);
            }
            // Si no hay resultados, ejecutamos la segunda consulta
            $query = $this->db->connect()->prepare("
                SELECT t.*
                FROM programacion p
                LEFT JOIN terminal t ON p.id_terminal_destino = t.id_terminal
                WHERE p.id_programacion = :id_programacion
            ");

            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $destino_general = $query->fetchAll(PDO::FETCH_ASSOC);

            return array('success' => true, "message" => $destino_general);
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, inténtalo más tarde.");
            }
        }
    }

    private function tienePermisoDesbloquearReservado()
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT p_desbloquear_reservado
                FROM permiso
                WHERE id_usuario = :id_usuario
                LIMIT 1
            ");

            $query->bindValue(
                ':id_usuario',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            return (int)$query->fetchColumn() === 1;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function desbloquear_asientos($data)
    {
        try {
            $ids = $data['ids_obj_vehiculo'] ?? [];
            $id_programacion = $data['id_programacion'] ?? null;

            if (empty($ids) || !$id_programacion) {
                return ['success' => false, "message" => "No hay asientos para desbloquear"];
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM programacion_obj 
                WHERE id_programacion = ? 
                AND estado_proceso = 1 
                AND id_obj_vehiculo IN ($placeholders)";

            $query = $this->db->connect()->prepare($sql);
            $query->execute(array_merge([$id_programacion], $ids));

            return ['success' => true, "message" => "Asientos desbloqueados con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e];
            }
        }
    }

    public function desbloquear_asiento($data)
    {
        try {
            $conn = $this->db->connect();

            // Buscar quién tiene reservado/procesando el asiento
            $query = $conn->prepare("
                SELECT cod_usuario
                FROM programacion_obj
                WHERE id_obj_vehiculo = :id_obj_vehiculo
                AND id_programacion = :id_programacion
                AND estado_proceso = 1
                LIMIT 1
            ");

            $query->bindValue(':id_obj_vehiculo', $data["id_obj_vehiculo"], PDO::PARAM_INT);

            $query->bindValue(':id_programacion', $data["id_programacion"], PDO::PARAM_INT);

            $query->execute();

            $reserva = $query->fetch(PDO::FETCH_ASSOC);

            if (!$reserva) {
                return [
                    'success' => false,
                    'code' => 'RESERVA_NO_EXISTE',
                    'message' => 'El asiento ya no se encuentra bloqueado.'
                ];
            }

            $esMiReserva = (int)$reserva['cod_usuario'] === (int)$this->id_usuario_sesion;
            $tienePermiso = $this->tienePermisoDesbloquearReservado();

            if (!$esMiReserva && !$tienePermiso) {
                return [
                    'success' => false,
                    'code' => 'SIN_AUTORIZACION',
                    'message' => 'No puede desbloquear este asiento porque fue reservado por otro usuario y usted no cuenta con el permiso para desbloquear reservas.'
                ];
            }

            $query = $conn->prepare("
                DELETE FROM programacion_obj
                WHERE id_obj_vehiculo = :id_obj_vehiculo
                AND id_programacion = :id_programacion
                AND estado_proceso = 1
            ");

            $query->bindValue(':id_obj_vehiculo', $data["id_obj_vehiculo"], PDO::PARAM_INT);

            $query->bindValue(':id_programacion', $data["id_programacion"], PDO::PARAM_INT);

            $query->execute();

            return [
                'success' => true,
                'message' => 'Asiento desbloqueado con éxito'
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Ha ocurrido un error, inténtalo más tarde.'
            ];
        }
    }

    public function get_clientes()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM usuario WHERE id_tp_usuario IN (5) ORDER BY id_usuario DESC");
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($reply) {
                return array('success' => true, "message" => $reply);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function cliente_get($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT u.id_usuario, CONCAT(u.nombres,' ',u.apellidos) AS nombres_cliente,
                   u.num_docu, u.id_tp_docu, u.fecha_nacimiento, u.celular
            FROM usuario u
            WHERE u.num_docu = :num_docu
           ");
            $query->bindParam(":num_docu", $data['cliente']);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            if (!$reply) {
                return ['success' => false, 'message' => null];
            }

            $query = $conn->prepare("
            SELECT 
            id,
            id_venta_original AS id_venta,
            COUNT(id) AS cantidad,
            SUM(pp.saldo_a_favor) AS saldo_total
            FROM pasajes_pospuestos pp
            WHERE pp.id_cliente = :id_usuario
              AND (pp.estado = 'pendiente' OR pp.estado = 'parcial')
            ");
            $query->bindParam(":id_usuario", $reply["id_usuario"]);
            $query->execute();
            $pasajes_pospuestos = $query->fetch(PDO::FETCH_ASSOC);

            $tiene_pospuestos = !empty($pasajes_pospuestos) && $pasajes_pospuestos["cantidad"] > 0;

            return [
                'success' => true,
                'message' => $reply,
                'mensaje_pospuestos' => [
                    'success' => $tiene_pospuestos,
                    'mensaje' => $tiene_pospuestos
                        ? 'El usuario tiene ' . $pasajes_pospuestos["cantidad"] . ' pasajes pospuestos, con saldo total de ' . number_format($pasajes_pospuestos["saldo_total"], 2) . '.'
                        : 'El usuario no tiene pasajes pospuestos.',
                    'saldo' => $pasajes_pospuestos["saldo_total"],
                    'id_venta' => $pasajes_pospuestos["id_venta"],
                    'id' => $pasajes_pospuestos["id"]
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ha ocurrido un error, intentalo mas tarde.'];
        }
    }

    public function mostrar_clientes($tp_doc)
    {
        $consul = "";
        if (!empty($tp_doc)) {
            $consul = "AND id_tp_docu = " . $tp_doc;
        }
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM usuario WHERE id_tp_usuario IN (5) " . $consul . " ORDER BY id_usuario DESC");
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($reply) {
                return array('success' => true, "message" => $reply);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_serieForTpComprobante($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $data["tp_comprobante"]);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);
            if ($reply) {
                return array('success' => true, "message" => $reply);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function getDataVenta($data)
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare(
                "SELECT  u.nombres AS nombresU, 
                t.nombre AS terminalN, 
                c.email AS email_cliente, 
                CONCAT(c.nombres,' ',c.apellidos)AS nombres_cliente, 
                c.num_docu, 
                c.celular AS celular_cliente, 
                m.descripcion AS medio_pago,
                f.descripcion AS forma_pago,
                cj.referencia AS caja_chica,
                tc.descripcion AS tp_comprobante,
                cu.codigo AS cupon,
                cp.monto_descuento AS monto_cupon,
                v.* 
                FROM venta v
                INNER JOIN usuario u ON u.id_usuario = v.id_vendedor
                LEFT JOIN medio_pago m ON m.id_medio_pago = v.id_medio_pago
                LEFT JOIN forma_pago f ON f.id_forma_pago = v.id_forma_pago
                LEFT JOIN caja_chica cj ON cj.id_caja_chica = v.id_caja_chica
                INNER JOIN terminal t ON t.id_terminal = u.id_terminal 
                INNER JOIN usuario c ON c.id_usuario = v.id_cliente 
                LEFT JOIN tp_comprobante tc ON tc.id_tp_comprobante = v.id_tp_comprobante
                LEFT JOIN cupon_uso cp ON cp.id_venta = v.id_venta
                LEFT JOIN cupon cu ON cu.id_cupon = cp.id_cupon
                WHERE v.id_venta=:id_venta"
            );
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();
            $venta = $query->fetch(PDO::FETCH_ASSOC);

            $query = $conn->prepare("
            SELECT dt.*,
            CONCAT(p.nombres,' ',p.apellidos)AS nombres_pasajero, 
            p.num_docu AS pasajero_num,
            t_o.nombre AS origen,
            t_d.nombre AS destino,
            CONCAT(n.nombres,' ',n.apellidos)AS nombres_nino, 
            n.num_docu AS nino_num
            FROM dt_venta dt
            LEFT JOIN usuario p ON p.id_usuario = dt.id_pasajero
            LEFT JOIN usuario n ON n.id_usuario = dt.id_nino
            LEFT JOIN programacion pr ON pr.id_programacion = dt.id_programacion
            LEFT JOIN rutas_destino r_d ON r_d.id_ruta_destino = dt.id_destino
            LEFT JOIN ruta r_o ON r_o.id_ruta = dt.id_ruta
            LEFT JOIN terminal t_o ON t_o.id_terminal = IF(dt.tp_origen = 1, r_o.id_ruta, pr.id_terminal_origen)
            LEFT JOIN terminal t_d ON t_d.id_terminal = IF(dt.tp_destino = 1, r_d.id_terminal, pr.id_terminal_destino)
            WHERE id_venta=:id_venta");
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();
            $dt_venta = $query->fetch(PDO::FETCH_ASSOC);

            // Egresos venta
            $query = $conn->prepare("
            SELECT 
            *
            FROM egresos_usuario
            WHERE id_venta = :id_venta 
            ");
            $query->bindParam(":id_venta", $data['id_venta']);
            $query->execute();
            $egresos = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($venta) {
                return array('success' => true, "message" => ["venta" => $venta, "dt_venta" => $dt_venta, "egresos" => $egresos]);
            } else {
                return array('success' => false, "message" => null);
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos", $e);
                    break;
                default:
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function getDataComprobante($id_venta, $conn = null)
    {
        $id_terminal = $this->id_terminal_sesion ? $this->id_terminal_sesion : 1;
        if ($conn == null) {
            $conn = $this->db->connect();
        }

        $query = $conn->prepare("SELECT
        e.logo AS empresa_logo,
        e.envio_ose AS ose,
        e.m_terminales AS permiso_t,
        e.fr_comprobante AS frase_empresa,
        e.termscond_pasaje AS terminos_condiciones_pasaje,
        e.num_docu AS empresa_ruc,
        e.razon_social AS empresa_razon_social,
        e.nro_cuenta_bancaria AS empresa_cuenta_bancaria, 
        e.telefono_empresa AS empresa_telefono,  
        t.logo AS terminal_logo,
        t.nombre AS terminal_nombre,
        t.ubigeo AS terminal_ubigeo_codigo,
        ub.depa AS terminal_ubigeo_depa,
        ub.provi AS terminal_ubigeo_provi,
        ub.distri AS terminal_ubigeo_distri,
        t.direccion_fiscal AS terminal_direccion_fiscal,
        t.direccion_comercial AS terminal_direccion_comercial,
        t.cod_domicilio_fiscal AS terminal_cod_domicilio_fiscal,
        t.celular AS terminal_celular,
        t.email AS terminal_email,
        t.siteweb AS terminal_siteweb,
        e.user_sol AS empresa_usuario_sol,
        e.pass_sol AS empresa_pass_sol,
        td.id_tp_docu AS tp_doc_id,
        e.ubigeo AS ubigeo_empresa,
        ub_e.depa AS departamento,
        ub_e.provi AS provincia,
        ub_e.distri AS distrito,
        CONCAT(e.direccion_fiscal, ' ',ub_e.depa, ' - ',ub_e.provi, ' - ',ub_e.distri) AS direccion

         FROM terminal t
         LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
         LEFT JOIN ubigeo ub_e ON ub_e.cod_ubigeo = e.ubigeo
         LEFT JOIN ubigeo ub ON ub.cod_ubigeo=t.ubigeo
         LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
         LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
         WHERE t.id_terminal=:id_terminal;");
        $query->bindParam(":id_terminal", $id_terminal);
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);

        if ($emisor['permiso_t'] == '1') {
            $query = $conn->prepare("SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            WHERE e.id_empresa = (SELECT id_empresa FROM terminal WHERE id_terminal = :id_terminal);
            ");
            $query->bindParam(":id_terminal", $id_terminal);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $terminales = [];
        }

        $query = $conn->prepare("SELECT
        c.nombres AS cliente_nombres,
        c.apellidos AS cliente_apellidos,
        c.id_tp_docu AS cliente_id_tp_docu,
        tp_d_c.descripcion AS cliente_tp_docu,
        c.num_docu AS cliente_num_docu,
        c.direccion AS cliente_direccion,
        c.ubigeo AS cliente_ubigeo,
        c.fecha_nacimiento AS cliente_fecha_nacimiento,
        c.nacionalidad AS cliente_nacionalidad,
        c.celular AS cliente_celular,
        
        psj.nombres AS pasajero_nombres,
        psj.apellidos AS pasajero_apellidos,
        psj.id_tp_docu AS pasajero_id_tp_docu,
        tp_d_psj.descripcion AS pasajero_tp_docu,
        psj.num_docu AS pasajero_num_docu,
        psj.direccion AS pasajero_direccion,
        psj.ubigeo AS pasajero_ubigeo,
        psj.fecha_nacimiento AS pasajero_fecha_nacimiento,
        psj.nacionalidad AS pasajero_nacionalidad,
        psj.celular AS pasajero_celular
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
        LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
        LEFT JOIN usuario psj ON psj.id_usuario=dt_v.id_pasajero
        LEFT JOIN ubigeo ub_psj ON ub_psj.cod_ubigeo=psj.ubigeo
        LEFT JOIN tp_docu tp_d_psj ON tp_d_psj.id_tp_docu=psj.id_tp_docu
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
        vd.nombres AS vendedor_nombres,
        vd.apellidos AS vendedor_apellidos,
        v.serie,
        v.correlativo,
        v.fecha_emision,
         date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format,
        date_format(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
        v.id_forma_pago,
        f_p.descripcion AS forma_pago,
        v.id_medio_pago,
        m_p.descripcion AS medio_pago,
        v.fecha_vencimiento,
        v.id_tp_moneda,
        tp_m.codigo AS tp_moneda_codigo,
        tp_m.descripcion AS tp_moneda_descripcion,
        v.id_tp_comprobante,
        v.op_igv,
        v.op_gravada,
        v.op_exonerada,
        v.op_inafecta,
         v.total,
         v.descuento,
         tp_c.descripcion AS tp_comprobante,
         tp_c.codigo AS tp_comprobante_codigo,
          p.fecha_salida AS programacion_fecha_salida,
         CASE 
           WHEN dt_v.tp_origen = 1 THEN r.hora
           ELSE p.hora_salida 
         END AS programacion_hora_salida,
         vh.placa AS vehiculo_placa,
         vh.num_poliza,
         vh.soat,
         v.estado,
         v.fecha_registro,
          date_format(v.fecha_registro, '%Y-%m-%d') AS fecha_registro_format,
          date_format(v.fecha_registro, '%H:%i:%s') AS hora_registro_format,
         CASE 
           WHEN dt_v.tp_origen = 1 THEN t_r.nombre
           ELSE t_o.nombre 
         END AS terminal_origen,
         CASE 
           WHEN dt_v.tp_destino = 1 THEN r_d.nombre
           ELSE t_d.nombre 
         END AS terminal_destino,
        v.cod_qr
          FROM venta v
         LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
         LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
         LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
         LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
         LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
         LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
         LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
         LEFT JOIN usuario vd ON vd.id_usuario = v.id_vendedor
         LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
         LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
         LEFT JOIN ruta r ON r.id_ruta = dt_v.id_ruta
         LEFT JOIN terminal t_r ON t_r.id_terminal = r.id_terminal
         LEFT JOIN rutas_destino rd ON rd.id_ruta_destino = dt_v.id_destino
         LEFT JOIN terminal r_d ON r_d.id_terminal = rd.id_terminal
         WHERE v.id_venta = :id_venta
        ");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $cabecera = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("SELECT
            dt_v.piso,
            dt_v.num_asiento,
            dt_v.estado_asiento,
            dt_v.precio,
            dt_v.op_igv,
            dt_v.op_gravada,
            dt_v.op_exonerada,
            dt_v.op_inafecta,
            dt_v.op_total
        FROM dt_venta dt_v
        WHERE dt_v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $items = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT *
        FROM impresoras i
        WHERE cod_usuario=:usuario;");
        $query->bindParam(":usuario", $this->id_usuario_sesion);
        $query->execute();
        $impresora_data = $query->fetch(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($items); $i++) {
            $items[$i]["iten"] = ($i + 1);
        }
        return array(
            "TERMINALES" => $terminales,
            "EMISOR" => $emisor,
            "CABECERA" => $cabecera,
            "CLIENTE" => $cliente,
            "ITEMS" => $items,
            "IMPRESORA" => $impresora_data
        );
    }

    public function getDataReporteManifiesto($id_programacion, $condicion)
    {
        //Validando el where para los tipos de comprobantes
        switch ($condicion) {
            case "0":
                $where = "(1,3)";
                break;
            case "1":
                $where = "(1,3,2)";
                break;
            case "2":
                $where = "(2)";
                break;
            default:
                $where = "(1,3)";
                break;
        }

        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT e.*, CONCAT(u.depa, ' -',u.provi, ' -',u.distri) AS ubigeo
        FROM empresa e
        LEFT JOIN ubigeo u ON e.ubigeo = u.cod_ubigeo");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        if ($header_empresa['m_terminalesManifi'] == '1') {
            $query = $conn->prepare("SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            WHERE e.id_empresa = (SELECT id_empresa FROM terminal WHERE id_terminal = :id_terminal) LIMIT 5;
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $terminales = [];
        }

        $query = $conn->prepare("
        SELECT
            p.id_programacion AS correlativo,
            c.nombres AS conductor_nombres,
            c.apellidos AS conductor_apellidos,
            dt_c.licencia AS conductor_licencia,
            vh.placa AS vehiculo_placa,
            vh.marca AS vehiculo_marca,
            vh.num_asiento AS vehiculo_cantidad_asiento,
            vh.tuc AS vehiculo_tuc,
            vh.num_poliza AS vehiculo_num_poliza,
            t_o.nombre AS terminal_origen,
            ub_o.distri AS t_o_distrito,
            ub_d.distri AS t_d_distrito,
            t_d.nombre AS terminal_destino,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida

        FROM programacion p
        LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        LEFT JOIN ubigeo ub_o ON t_o.ubigeo = ub_o.cod_ubigeo
        LEFT JOIN ubigeo ub_d ON t_d.ubigeo = ub_d.cod_ubigeo
        WHERE p.id_programacion=:id_programacion
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        $estado = 0;
        if ($header_detalle['conductor_nombres'] == 'CONDUCTOR') {
            $estado = 1;
        }

        $query = $conn->prepare("
        SELECT
            cp.nombres AS copiloto_nombres,
            cp.apellidos AS copiloto_apellidos,
            dt_c.licencia AS copiloto_licencia
        FROM programacion p
        LEFT JOIN personal_programacion p_p ON p_p.id_programacion=p.id_programacion
        LEFT JOIN usuario cp ON cp.id_usuario=p_p.id_usuario
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=cp.id_usuario
        LEFT JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=cp.id_tp_usuario
        WHERE p.id_programacion=:id_programacion AND tp_u.descripcion IN ('COPILOTO','CONDUCTOR')
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $copilotos = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
            ayud.nombres AS ayudante_nombres,
            ayud.apellidos AS ayudante_apellidos,
            ayud.num_docu AS ayudante_doc
        FROM programacion p
        LEFT JOIN personal_programacion p_p ON p_p.id_programacion=p.id_programacion
        LEFT JOIN usuario ayud ON ayud.id_usuario=p_p.id_usuario
        LEFT JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=ayud.id_tp_usuario
        WHERE p.id_programacion=:id_programacion AND tp_u.descripcion IN ('TERRAMOZA','PERSONAL','AYUDANTE')
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $ayudantes = $query->fetchAll(PDO::FETCH_ASSOC);


        $query = $conn->prepare("
        SELECT
          dt_v.num_asiento,
          dt_v.c_nino,
          dt_v.motivo,
          dt_v.obs_pasajero,
          nin.nombres AS nino_nombres,
          nin.apellidos AS nino_apellidos,
          nin.num_docu AS nino_num_docu,
          nin.fecha_nacimiento AS nino_fecha_nacimiento,
          psj.nombres AS cliente_nombres,
          psj.apellidos AS cliente_apellidos,
          psj.fecha_nacimiento AS cliente_fecha_nacimiento,
          psj.num_docu AS cliente_num_docu,
          tp_d.abrev AS cliente_tp_docu,
          psj.celular AS cliente_celular,
          psj.nacionalidad AS cliente_nacionalidad,
         CASE 
          WHEN dt_v.tp_origen = 1 THEN t_r.nombre
          ELSE t_o.nombre 
         END AS terminal_origen,
         CASE 
          WHEN dt_v.tp_destino = 1 THEN r_d.nombre
          ELSE t_d.nombre 
         END AS terminal_destino,
         CASE 
          WHEN dt_v.tp_origen = 1 THEN (
            SELECT ub.distri 
            FROM ubigeo ub 
            WHERE ub.cod_ubigeo = t_r.ubigeo
            )
            ELSE ub_o.distri 
         END AS distrito_origen,
         CASE 
           WHEN dt_v.tp_destino = 1 THEN (
            SELECT ub.distri 
            FROM ubigeo ub 
            WHERE ub.cod_ubigeo = r_d.ubigeo
           )
          ELSE ub_d.distri 
         END AS distrito_destino,
          v.serie AS venta_serie,
          v.correlativo AS venta_correlativo,
          v.op_exonerada AS importe,
          v.obs AS venta_obs,
            (
                SELECT pp.estado
                FROM pasajes_pospuestos pp
                WHERE pp.id_venta_original = v.id_venta
                ORDER BY pp.id DESC
                LIMIT 1
            ) AS estado_pospuesto
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN usuario psj ON psj.id_usuario = dt_v.id_pasajero
        LEFT JOIN tp_docu tp_d ON psj.id_tp_docu  = tp_d.id_tp_docu 
        LEFT JOIN usuario nin ON nin.id_usuario = dt_v.id_nino
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN ubigeo ub_o ON t_o.ubigeo = ub_o.cod_ubigeo
        LEFT JOIN ubigeo ub_d ON t_d.ubigeo = ub_d.cod_ubigeo
        LEFT JOIN rutas_destino rd ON dt_v.id_destino = rd.id_ruta_destino
        LEFT JOIN terminal r_d ON r_d.id_terminal = rd.id_terminal
        LEFT JOIN ruta r ON r.id_ruta = dt_v.id_ruta
        LEFT JOIN terminal t_r ON t_r.id_terminal = r.id_terminal
        WHERE dt_v.id_programacion = :id_programacion 
        AND v.estado NOT IN ('ANULADO', 'CANCELADO', 'RESERVADO', 'POSPUESTO') 
        AND dt_v.estado_asiento = 'VENDIDO'
        AND tp_s.descripcion = 'PASAJE'  
        ORDER BY CAST(dt_v.num_asiento AS UNSIGNED) ASC
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $detalle = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
         SELECT 
        m.id_manifiesto,
        m.id_programacion,
        m.id_serie_manifiesto,
        sm.serie,
        m.correlativo,
        sm.numero_autorizacion,
        m.estado,
        m.fecha_registro
        FROM manifiesto m
        INNER JOIN serie_manifiesto sm
        ON sm.id_serie_manifiesto = m.id_serie_manifiesto
        WHERE m.id_programacion = :id_programacion
         LIMIT 1
        ");

        $query->bindParam(
            ":id_programacion",
            $id_programacion,
            PDO::PARAM_INT
        );

        $query->execute();

        $data_numero = $query->fetch(PDO::FETCH_ASSOC);

        return [
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "TERMINALES" => $terminales,
                "HEADER_DETALLE" => $header_detalle,
                "COPILOTOS" => $copilotos,
                "AYUDANTES" => $ayudantes,
                "MANIFIESTO" => $data_numero
            ],
            // "BODY" => $body,
            "DETALLE" => $detalle,
            "estado" => $estado
        ];
    }

    public function getDataReporteManifiesto_sunat($id_programacion)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT e.*, CONCAT(u.depa, ' -',u.provi, ' -',u.distri) AS ubigeo
        FROM empresa e
        LEFT JOIN ubigeo u ON e.ubigeo = u.cod_ubigeo");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        if ($header_empresa['m_terminalesManifi'] == '1') {
            $query = $conn->prepare("SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            WHERE e.id_empresa = (SELECT id_empresa FROM terminal WHERE id_terminal = :id_terminal) LIMIT 5;
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $terminales = [];
        }

        $query = $conn->prepare("
        SELECT
            p.id_programacion AS correlativo,
            c.nombres AS conductor_nombres,
            c.apellidos AS conductor_apellidos,
            dt_c.licencia AS conductor_licencia,
            vh.placa AS vehiculo_placa,
            vh.marca AS vehiculo_marca,
            vh.num_asiento AS vehiculo_cantidad_asiento,
            vh.tuc AS vehiculo_tuc,
            vh.num_poliza AS vehiculo_num_poliza,
            t_o.nombre AS terminal_origen,
            ub_o.distri AS t_o_distrito,
            ub_d.distri AS t_d_distrito,
            t_d.nombre AS terminal_destino,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida

        FROM programacion p
        LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        LEFT JOIN ubigeo ub_o ON t_o.ubigeo = ub_o.cod_ubigeo
        LEFT JOIN ubigeo ub_d ON t_d.ubigeo = ub_d.cod_ubigeo
        WHERE p.id_programacion=:id_programacion
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        $estado = 0;
        if ($header_detalle['conductor_nombres'] == 'CONDUCTOR') {
            $estado = 1;
        }

        $query = $conn->prepare("
        SELECT
            cp.nombres AS copiloto_nombres,
            cp.apellidos AS copiloto_apellidos,
            dt_c.licencia AS copiloto_licencia
        FROM programacion p
        LEFT JOIN personal_programacion p_p ON p_p.id_programacion=p.id_programacion
        LEFT JOIN usuario cp ON cp.id_usuario=p_p.id_usuario
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=cp.id_usuario
        LEFT JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=cp.id_tp_usuario
        WHERE p.id_programacion=:id_programacion AND tp_u.descripcion IN ('COPILOTO','CONDUCTOR')
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $copilotos = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
            ayud.nombres AS ayudante_nombres,
            ayud.apellidos AS ayudante_apellidos,
            ayud.num_docu AS ayudante_doc
        FROM programacion p
        LEFT JOIN personal_programacion p_p ON p_p.id_programacion=p.id_programacion
        LEFT JOIN usuario ayud ON ayud.id_usuario=p_p.id_usuario
        LEFT JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=ayud.id_tp_usuario
        WHERE p.id_programacion=:id_programacion AND tp_u.descripcion IN ('TERRAMOZA','PERSONAL','AYUDANTE')
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $ayudantes = $query->fetchAll(PDO::FETCH_ASSOC);


        $query = $conn->prepare("
        SELECT
          dt_v.num_asiento,
          dt_v.c_nino,
          dt_v.motivo,
          dt_v.obs_pasajero,
          nin.nombres AS nino_nombres,
          nin.apellidos AS nino_apellidos,
          nin.num_docu AS nino_num_docu,
          nin.fecha_nacimiento AS nino_fecha_nacimiento,
          psj.nombres AS cliente_nombres,
          psj.apellidos AS cliente_apellidos,
          psj.fecha_nacimiento AS cliente_fecha_nacimiento,
          psj.num_docu AS cliente_num_docu,
          tp_d.abrev AS cliente_tp_docu,
          psj.celular AS cliente_celular,
          psj.nacionalidad AS cliente_nacionalidad,
         CASE 
          WHEN dt_v.tp_origen = 1 THEN t_r.nombre
          ELSE t_o.nombre 
         END AS terminal_origen,
         CASE 
          WHEN dt_v.tp_destino = 1 THEN r_d.nombre
          ELSE t_d.nombre 
         END AS terminal_destino,
         CASE 
          WHEN dt_v.tp_origen = 1 THEN (
            SELECT ub.distri 
            FROM ubigeo ub 
            WHERE ub.cod_ubigeo = t_r.ubigeo
            )
            ELSE ub_o.distri 
         END AS distrito_origen,
         CASE 
           WHEN dt_v.tp_destino = 1 THEN (
            SELECT ub.distri 
            FROM ubigeo ub 
            WHERE ub.cod_ubigeo = r_d.ubigeo
           )
          ELSE ub_d.distri 
         END AS distrito_destino,
          v.serie AS venta_serie,
          v.correlativo AS venta_correlativo,
          v.op_exonerada AS importe,
          v.obs AS venta_obs,
            (
                SELECT pp.estado
                FROM pasajes_pospuestos pp
                WHERE pp.id_venta_original = v.id_venta
                ORDER BY pp.id DESC
                LIMIT 1
            ) AS estado_pospuesto
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN usuario psj ON psj.id_usuario = dt_v.id_pasajero
        LEFT JOIN usuario nin ON nin.id_usuario = dt_v.id_nino
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN tp_docu tp_d ON tp_d.id_tp_docu = psj.id_tp_docu
        LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN ubigeo ub_o ON t_o.ubigeo = ub_o.cod_ubigeo
        LEFT JOIN ubigeo ub_d ON t_d.ubigeo = ub_d.cod_ubigeo
        LEFT JOIN rutas_destino rd ON dt_v.id_destino = rd.id_ruta_destino
        LEFT JOIN terminal r_d ON r_d.id_terminal = rd.id_terminal
        LEFT JOIN ruta r ON r.id_ruta = dt_v.id_ruta
        LEFT JOIN terminal t_r ON t_r.id_terminal = r.id_terminal
        WHERE dt_v.id_programacion = :id_programacion 
        AND v.estado NOT IN ('ANULADO', 'CANCELADO', 'RESERVADO', 'POSPUESTO') 
        AND dt_v.estado_asiento = 'VENDIDO'
        AND v.id_tp_comprobante NOT IN (2)
        AND tp_s.descripcion = 'PASAJE'  
        ORDER BY CAST(dt_v.num_asiento AS UNSIGNED) ASC
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $detalle = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT 
        m.id_manifiesto,
        m.id_programacion,
        m.id_serie_manifiesto,
        sm.serie,
        m.correlativo,
        sm.numero_autorizacion,
        m.estado,
        m.fecha_registro
        FROM manifiesto m
        INNER JOIN serie_manifiesto sm
        ON sm.id_serie_manifiesto = m.id_serie_manifiesto
        WHERE m.id_programacion = :id_programacion
         LIMIT 1
        ");

        $query->bindParam(
            ":id_programacion",
            $id_programacion,
            PDO::PARAM_INT
        );

        $query->execute();
        $data_numero = $query->fetch(PDO::FETCH_ASSOC);

        return [
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "TERMINALES" => $terminales,
                "HEADER_DETALLE" => $header_detalle,
                "COPILOTOS" => $copilotos,
                "AYUDANTES" => $ayudantes,
                "MANIFIESTO" => $data_numero
            ],
            // "BODY" => $body,
            "DETALLE" => $detalle,
            "estado" => $estado
        ];
    }

    private function calcularComisionVendedor($id_vendedor, $monto_venta)
    {
        $query = $this->db->connect()->prepare("
        SELECT id_tp_usuario
        FROM usuario
        WHERE id_usuario = :id_vendedor
        ");

        $query->bindParam(':id_vendedor', $id_vendedor, PDO::PARAM_INT);
        $query->execute();

        $tipo_usuario = $query->fetch(PDO::FETCH_ASSOC);

        if (!$tipo_usuario) {
            return 0.00;
        }

        $id_tp_usuario = (int) $tipo_usuario['id_tp_usuario'];

        $query = $this->db->connect()->prepare("
        SELECT
            comision_nivel1,
            tp_comision_nivel1,
            comision_nivel2,
            tp_comision_nivel2
        FROM configuracion
        LIMIT 1
        ");

        $query->execute();

        $config = $query->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            return 0.00;
        }

        $valor_comision = 0;
        $tipo_comision = 'PORCENTAJE';

        if ($id_tp_usuario === 14) {

            $valor_comision = (float) $config['comision_nivel1'];
            $tipo_comision = $config['tp_comision_nivel1'];
        } elseif ($id_tp_usuario === 15) {

            $valor_comision = (float) $config['comision_nivel2'];
            $tipo_comision = $config['tp_comision_nivel2'];
        } else {

            return 0.00;
        }

        if ($valor_comision <= 0) {
            return 0.00;
        }

        if ($tipo_comision === 'MONTO') {

            return $valor_comision;
        }

        return ($monto_venta * $valor_comision) / 100;
    }

    public function getDataReporteLiquidacionVehiculo($id_programacion)
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("SELECT * FROM empresa");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            vh.placa AS vehiculo_placa,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            dt_c.licencia AS conductor_licencia,
            c.nombres AS nombres_conductor,
            c.apellidos AS apellidos_conductor,
            comision_v AS comision
        FROM programacion p
        LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        WHERE p.id_programacion=:id_programacion
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        // Obteniendo la cantidad de pagos que se realizó
        $query = $conn->prepare("
            SELECT 
                m_p.descripcion AS medio_pago,
                COUNT(m_p.descripcion) AS amount,
                CASE
                    WHEN pp.id_programacion_original = :id_prog_original_piso
                        THEN vh_original.num_piso
                    ELSE vh_actual.num_piso
                END AS num_piso
            FROM venta v
            INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            INNER JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            LEFT JOIN pasajes_pospuestos pp ON pp.id_venta_original = v.id_venta
            LEFT JOIN programacion p_actual ON p_actual.id_programacion = dt_v.id_programacion
            LEFT JOIN vehiculo vh_actual ON vh_actual.id_vehiculo = p_actual.id_vehiculo
            LEFT JOIN programacion p_original ON p_original.id_programacion = pp.id_programacion_original
            LEFT JOIN vehiculo vh_original ON vh_original.id_vehiculo = p_original.id_vehiculo
            WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
            AND ((dt_v.id_programacion = :id_prog_actual
                    AND NOT EXISTS (
                        SELECT 1
                        FROM pasajes_pospuestos pp2
                        WHERE pp2.id_venta_original = v.id_venta
                        AND pp2.estado = 'usado'
                    )
                )
                OR pp.id_programacion_original = :id_prog_original
            )
            GROUP BY
                m_p.descripcion,
                CASE
                    WHEN pp.id_programacion_original = :id_prog_group
                        THEN vh_original.num_piso
                    ELSE vh_actual.num_piso
                END
        ");
        $query->bindParam(":id_prog_original_piso", $id_programacion);
        $query->bindParam(":id_prog_actual", $id_programacion);
        $query->bindParam(":id_prog_original", $id_programacion);
        $query->bindParam(":id_prog_group", $id_programacion);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_general = [];
        for ($i = 0; $i < count($medio_pagos); $i++) {
            $pisos = array();
            for ($x = 0; $x < $medio_pagos[$i]["num_piso"]; $x++) {
                $piso = $x + 1;
                $query = $conn->prepare("
                    SELECT
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_asiento
                            THEN TRIM(ov_original.text_obj)
                            ELSE dt_v.num_asiento
                        END AS num_asiento,
                        c.nombres AS cliente_nombres,
                        c.apellidos AS cliente_apellidos,
                        c.num_docu AS cliente_num_docu,
                        v.serie AS venta_serie,
                        v.porcentaje_venta,
                        v.correlativo AS venta_correlativo,
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_destino
                            THEN t_d_original.nombre
                            ELSE t_d.nombre
                        END AS terminal_destino,
                        v.op_exonerada AS importe,
                        CONCAT(ven.nombres) AS vendedor,
                        v.id_vendedor AS vendedor_id,
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_piso
                            THEN ov_original.piso
                            ELSE dt_v.piso
                        END AS num_piso,
                        CASE
                        WHEN pp.estado = 'usado' THEN 'POSPUESTO US.'
                        ELSE dt_v.estado_asiento
                        
                        END AS estado_asiento
                    FROM venta v
                    LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                    LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
                    LEFT JOIN programacion p_actual ON p_actual.id_programacion = dt_v.id_programacion
                    LEFT JOIN terminal t_d ON t_d.id_terminal = p_actual.id_terminal_destino
                    LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
                    LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                    LEFT JOIN usuario ven ON ven.id_usuario = v.id_vendedor
                    LEFT JOIN pasajes_pospuestos pp ON pp.id_venta_original = v.id_venta
                    LEFT JOIN programacion_obj po_original ON po_original.id_venta = v.id_venta 
                        AND po_original.id_programacion = pp.id_programacion_original
                    LEFT JOIN obj_vehiculo ov_original ON ov_original.id_obj_vehiculo = po_original.id_obj_vehiculo
                    LEFT JOIN programacion p_original ON p_original.id_programacion = pp.id_programacion_original
                    LEFT JOIN terminal t_d_original ON t_d_original.id_terminal = p_original.id_terminal_destino
                    WHERE
                    ((dt_v.id_programacion = :id_prog_actual
                            AND NOT EXISTS (
                                SELECT 1
                                FROM pasajes_pospuestos pp2
                                WHERE pp2.id_venta_original = v.id_venta
                                AND pp2.estado = 'usado'
                            )
                        )
                        OR pp.id_programacion_original = :id_prog_original
                    )
                    AND v.estado NOT IN ('ANULADO', 'CANCELADO', 'RESERVADO', 'VENTA_WEB')
                    AND tp_s.descripcion = 'PASAJE'
                    AND ((pp.id_programacion_original = :id_prog_piso_condicion
                            AND ov_original.piso = :piso_original
                        ) OR ((pp.id_programacion_original IS NULL
                                OR pp.id_programacion_original <> :id_prog_comparacion
                            )
                            AND dt_v.piso = :piso_actual
                        )
                    )
                    AND m_p.descripcion = :medio_pago
                    ORDER BY CAST(
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_orden
                            THEN TRIM(ov_original.text_obj)
                            ELSE dt_v.num_asiento
                        END AS UNSIGNED
                    ) ASC
                ");
                $query->bindParam(":id_prog_asiento", $id_programacion);
                $query->bindParam(":id_prog_destino", $id_programacion);
                $query->bindParam(":id_prog_piso", $id_programacion);
                $query->bindParam(":id_prog_actual", $id_programacion);
                $query->bindParam(":id_prog_original", $id_programacion);
                $query->bindParam(":id_prog_piso_condicion", $id_programacion);
                $query->bindParam(":piso_original", $piso);
                $query->bindParam(":id_prog_comparacion", $id_programacion);
                $query->bindParam(":piso_actual", $piso);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->bindParam(":id_prog_orden", $id_programacion);

                $query->execute();

                $ventas_porPiso = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($ventas_porPiso) {
                    foreach ($ventas_porPiso as &$venta) {

                        // Limpiar TERMINAL y AGENCIA del destino
                        if (isset($venta['terminal_destino'])) {
                            $venta['terminal_destino'] = $this->limpiarTerminal(
                                $venta['terminal_destino']
                            );
                        }
                        // Asegurarse de que el ID del vendedor y el monto estén presentes
                        if (isset($venta['vendedor_id']) && isset($venta['importe'])) {
                            $comision = $this->calcularComisionVendedor($venta['vendedor_id'], (float) $venta['importe']);
                            $venta['comision_vendedor'] = $comision; // Añadir un nuevo campo
                        } else {
                            $venta['comision_vendedor'] = 0.00;
                        }
                    }
                    unset($venta); // Romper la referencia
                    array_push($pisos, $ventas_porPiso);
                }
                if ($piso == $medio_pagos[$i]["num_piso"] && $pisos) {
                    array_push(
                        $detalle_general,
                        [
                            "medio_pago" => $medio_pagos[$i]["medio_pago"],
                            "pisos" => $pisos
                        ]
                    );
                }
            }
        }

        // ============== EXTRACCIÓN DE ENCOMIENDAS ==============
        // Obteniendo la cantidad de pagos que se realizó para ENCOMIENDAS
        $query = $conn->prepare("
        SELECT 
        m_p.descripcion AS medio_pago,
        COUNT(m_p.descripcion) AS amount
        FROM venta v
         INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
          INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
         WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion AND dt_v.id_tp_servicio=2 
         GROUP BY 
          m_p.descripcion;
         ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $medio_pagos_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);
        // return $medio_pagos_encomiendas;

        $detalle_encomiendas = [];
        foreach ($medio_pagos_encomiendas as $medio_pago_item) {
            $encomiendas_agrupadas = [];

            $query = $conn->prepare("
            SELECT 
              e.*,
              CONCAT(remi.apellidos, ' ',remi.nombres) AS remitente,
              remi.num_docu AS num_docu_remitente,
              CONCAT(desti.apellidos, ' ',desti.nombres) AS destinatario,
              desti.num_docu AS num_docu_destinatario,
              CONCAT(ven.apellidos, ' ',ven.nombres) AS vendedor,
              ven.num_docu AS num_docu_vendedor,
              m_p.descripcion AS medio_pago,
              CONCAT(v.serie, '-', v.correlativo) AS comprobante,
              v.total AS monto,
              v.porcentaje_venta,
              pr.descripcion AS producto
              FROM encomienda e
              LEFT JOIN dt_venta dt ON e.id_encomienda = dt.id_encomienda
              LEFT JOIN venta v ON dt.id_venta = v.id_venta 
              LEFT JOIN producto_encomienda pr ON e.id_encomienda = pr.id_encomienda 
              LEFT JOIN medio_pago m_p ON v.id_medio_pago = m_p.id_medio_pago 
              LEFT JOIN usuario remi ON e.id_remitente = remi.id_usuario 
              LEFT JOIN usuario desti ON e.id_destinatario = desti.id_usuario 
              LEFT JOIN usuario ven ON v.id_vendedor = ven.id_usuario 
              WHERE dt.id_programacion = :id_programacion 
              AND dt.id_tp_servicio = 2
              AND m_p.descripcion = :medio_pago
            ");

            $query->bindParam(":id_programacion", $id_programacion);
            $query->bindParam(":medio_pago", $medio_pago_item["medio_pago"]);
            $query->execute();
            $encomiendas_porMedioPago = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($encomiendas_porMedioPago as $enc) {
                $clave = $enc['comprobante'];

                if (!isset($encomiendas_agrupadas[$clave])) {
                    $encomiendas_agrupadas[$clave] = $enc;
                } else {
                    $encomiendas_agrupadas[$clave]['producto'] .= ', ' . $enc['producto'];
                }
            }

            if (!empty($encomiendas_agrupadas)) {
                $detalle_encomiendas[] = [
                    "medio_pago" => $medio_pago_item["medio_pago"],
                    "encomiendas" => array_values($encomiendas_agrupadas)
                ];
            }
        }

        //Egresos
        $total_egresos = [];

        // Primera consulta de egresos
        $query = $conn->prepare("
         SELECT
         *
         FROM egreso e
        WHERE e.cod_programacion=:cod_programacion
        ");
        $query->bindParam(":cod_programacion", $id_programacion);
        $query->execute();
        $egresos = $query->fetchAll(PDO::FETCH_ASSOC);

        // Guardar los egresos de la primera consulta
        $total_egresos = $egresos;

        //Extraer la data de los egresos por terminales
        $query_l = $conn->prepare("
         SELECT
         id_liquidacion,
         id_programacion
        FROM p_liquidacion p
        WHERE p.id_programacion=:id_programacion
        ");
        $query_l->bindParam(":id_programacion", $id_programacion);
        $query_l->execute();
        $liquidaciones = $query_l->fetchAll(PDO::FETCH_ASSOC);

        //Por cada uno de las liquidaciones jalar el egreso de la tabla egresos_terminal
        if (!empty($liquidaciones)) {
            foreach ($liquidaciones as $liquidacion) {
                $query_1 = $conn->prepare("
        SELECT
            *
        FROM egresos_terminal e
        WHERE e.id_liquidacion=:id_liquidacion
        ");
                $query_1->bindParam(":id_liquidacion", $liquidacion["id_liquidacion"]);
                $query_1->execute();
                $egresos_terminal = $query_1->fetchAll(PDO::FETCH_ASSOC);

                // Agregar los egresos_terminal al array total_egresos
                if (!empty($egresos_terminal)) {
                    $total_egresos = array_merge($total_egresos, $egresos_terminal);
                }
            }
        }

        $query_u = $conn->prepare("
            SELECT
            id,
            id_programacion,
            comision_empresa
            FROM liquidacion_usuario l
            WHERE l.id_programacion=:id_programacion
            ");

        $query_u->bindParam(":id_programacion", $id_programacion);
        $query_u->execute();

        $liquidaciones_usuarios = $query_u->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($liquidaciones_usuarios)) {

            $query_2 = $conn->prepare("
            SELECT *
            FROM egresos_usuario e
            WHERE e.id_programacion=:id_programacion
            ");

            $query_2->bindParam(":id_programacion", $id_programacion);
            $query_2->execute();

            $egresos_usuarios = $query_2->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($egresos_usuarios)) {
                foreach ($egresos_usuarios as $egreso_usuario) {
                    $total_egresos[] = $egreso_usuario;
                }
            }
        }
        return [
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "HEADER_DETALLE" => $header_detalle,
            ],
            "DETALLE" => $detalle_general,
            "ENCOMIENDAS" => $detalle_encomiendas,
            "EGRESOS" => $total_egresos,
            "LIQUIDACIONES" => $liquidaciones_usuarios
        ];
    }

    public function getDataReporteLiquidacionPUsuario($id_programacion)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
        SELECT
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            vh.placa AS vehiculo_placa,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            dt_c.licencia AS conductor_licencia,
            c.nombres AS nombres_conductor,
            c.apellidos AS apellidos_conductor,
            comision_v AS comision
        FROM programacion p
        LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        WHERE p.id_programacion=:id_programacion
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);

        // Obteniendo la cantidad de pagos que se realizó
        $query = $this->db->connect()->prepare("SELECT 
            m_p.descripcion AS medio_pago,
            COUNT(m_p.descripcion) AS amount,
            vh.num_piso AS num_piso
        FROM venta v
        INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        INNER JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo 
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion GROUP BY m_p.descripcion");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_general = array();
        for ($i = 0; $i < count($medio_pagos); $i++) {
            $pisos = array();
            for ($x = 0; $x < $medio_pagos[$i]["num_piso"]; $x++) {
                $piso = $x + 1;
                $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.num_asiento AS num_asiento,
                    c.nombres AS cliente_nombres,
                    c.apellidos AS cliente_apellidos,
                    c.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.op_exonerada AS importe,
                    CONCAT(ven.nombres) AS vendedor,
                    dt_v.piso AS num_piso,
                    dt_v.estado_asiento,
                    v.porcentaje_venta
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                LEFT JOIN usuario ven ON ven.id_usuario=v.id_vendedor
                WHERE dt_v.id_programacion=:id_programacion AND v.estado NOT IN ('ANULADO', 'CANCELADO','RESERVADO','VENTA_WEB') AND tp_s.descripcion='PASAJE' AND dt_v.piso=:piso AND m_p.descripcion=:medio_pago
                ORDER BY CAST(dt_v.num_asiento AS UNSIGNED) ASC
                ");
                $query->bindParam(":id_programacion", $id_programacion);
                $query->bindParam(":piso", $piso);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->execute();
                $ventas_porPiso = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($ventas_porPiso) {
                    array_push($pisos, $ventas_porPiso);
                }
                if ($piso == $medio_pagos[$i]["num_piso"] && $pisos) {
                    array_push(
                        $detalle_general,
                        [
                            "medio_pago" => $medio_pagos[$i]["medio_pago"],
                            "pisos" => $pisos
                        ]
                    );
                }
            }
        }

        // ============== EXTRACCIÓN DE ENCOMIENDAS ==============
        // Obteniendo la cantidad de pagos que se realizó para ENCOMIENDAS
        $query = $this->db->connect()->prepare("
        SELECT 
        m_p.descripcion AS medio_pago,
        COUNT(m_p.descripcion) AS amount
        FROM venta v
         INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
          INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
         WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') 
         AND dt_v.id_programacion=:id_programacion 
         AND dt_v.id_tp_servicio=2 
         GROUP BY 
          m_p.descripcion;
         ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $medio_pagos_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_encomiendas = [];
        foreach ($medio_pagos_encomiendas as $medio_pago_item) {
            $encomiendas_agrupadas = [];

            $query = $this->db->connect()->prepare("
            SELECT 
              e.*,
              CONCAT(remi.apellidos, ' ',remi.nombres) AS remitente,
              remi.num_docu AS num_docu_remitente,
              CONCAT(desti.apellidos, ' ',desti.nombres) AS destinatario,
              desti.num_docu AS num_docu_destinatario,
              CONCAT(ven.apellidos, ' ',ven.nombres) AS vendedor,
              ven.num_docu AS num_docu_vendedor,
              m_p.descripcion AS medio_pago,
              CONCAT(v.serie, '-', v.correlativo) AS comprobante,
              v.total AS monto,
              v.porcentaje_venta,
              pr.descripcion AS producto
              FROM encomienda e
              LEFT JOIN dt_venta dt ON e.id_encomienda = dt.id_encomienda
              LEFT JOIN venta v ON dt.id_venta = v.id_venta 
              LEFT JOIN producto_encomienda pr ON e.id_encomienda = pr.id_encomienda 
              LEFT JOIN medio_pago m_p ON v.id_medio_pago = m_p.id_medio_pago 
              LEFT JOIN usuario remi ON e.id_remitente = remi.id_usuario 
              LEFT JOIN usuario desti ON e.id_destinatario = desti.id_usuario 
              LEFT JOIN usuario ven ON v.id_vendedor = ven.id_usuario 
              WHERE dt.id_programacion = :id_programacion 
              AND dt.id_tp_servicio = 2
              AND m_p.descripcion = :medio_pago
            ");

            $query->bindParam(":id_programacion", $id_programacion);
            $query->bindParam(":medio_pago", $medio_pago_item["medio_pago"]);
            $query->execute();
            $encomiendas_porMedioPago = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($encomiendas_porMedioPago as $enc) {
                $clave = $enc['comprobante'];

                if (!isset($encomiendas_agrupadas[$clave])) {
                    $encomiendas_agrupadas[$clave] = $enc;
                } else {
                    $encomiendas_agrupadas[$clave]['producto'] .= ', ' . $enc['producto'];
                }
            }

            if (!empty($encomiendas_agrupadas)) {
                $detalle_encomiendas[] = [
                    "medio_pago" => $medio_pago_item["medio_pago"],
                    "encomiendas" => array_values($encomiendas_agrupadas)
                ];
            }
        }

        //Egresos
        $query = $this->db->connect()->prepare("
        SELECT
            *
        FROM egreso e
        WHERE e.cod_programacion=:cod_programacion
        ");
        $query->bindParam(":cod_programacion", $id_programacion);
        $query->execute();
        $egresos = $query->fetchAll(PDO::FETCH_ASSOC);
        return array(
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "HEADER_DETALLE" => $header_detalle,
            ],
            "DETALLE" => $detalle_general,
            "EGRESOS" => $egresos,
            "ENCOMIENDAS" => $detalle_encomiendas
        );
    }

    public function getDataReporteLiquidacionUsuario($id_programacion, $id_vendedor)
    {
        // Empresa
        $conn = $this->db->connect();
        $query = $conn->prepare("SELECT * FROM empresa");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        // Configuración de comisiones
        $query = $conn->prepare("
        SELECT 
        comision_nivel1, 
        tp_comision_nivel1,
        comision_nivel2, 
        tp_comision_nivel2,
        porcentaje_empresa
        FROM configuracion
        LIMIT 1
        ");
        $query->execute();
        $configuracion = $query->fetch(PDO::FETCH_ASSOC) ?: [
            'comision_nivel1' => 0,
            'tp_comision_nivel1' => 'PORCENTAJE',
            'comision_nivel2' => 0,
            'tp_comision_nivel2' => 'PORCENTAJE',
            'porcentaje_empresa' => 0
        ];

        $porcentaje_nivel1 = (float) ($configuracion['comision_nivel1'] ?? 0);
        $porcentaje_nivel2 = (float) ($configuracion['comision_nivel2'] ?? 0);
        $tp_comision_nivel1 = $configuracion['tp_comision_nivel1'] ?? 'PORCENTAJE';
        $tp_comision_nivel2 = $configuracion['tp_comision_nivel2'] ?? 'PORCENTAJE';
        $porcentaje_empresa = (float) ($configuracion['porcentaje_empresa'] ?? 0);

        // Header de la programación
        $query = $conn->prepare("
        SELECT
            p.fecha_salida                          AS programacion_fecha_salida,
            p.hora_salida                           AS programacion_hora_salida,
            vh.placa                                AS vehiculo_placa,
            t_o.nombre                              AS terminal_origen,
            t_d.nombre                              AS terminal_destino,
            dt_c.licencia                           AS conductor_licencia,
            c.nombres                               AS nombres_conductor,
            c.apellidos                             AS apellidos_conductor,
            t_o.nombre                              AS terminal_liquidacion,
            t_o.direccion_comercial                 AS terminal_direccion,
            t_o.celular                             AS terminal_contacto
        FROM programacion p
        LEFT JOIN usuario      c    ON c.id_usuario      = p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario   = c.id_usuario
        LEFT JOIN terminal     t_o  ON t_o.id_terminal   = p.id_terminal_origen
        LEFT JOIN terminal     t_d  ON t_d.id_terminal   = p.id_terminal_destino
        LEFT JOIN vehiculo     vh   ON vh.id_vehiculo    = p.id_vehiculo
        WHERE p.id_programacion = :id_programacion
        ");
        $query->bindParam(':id_programacion', $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);
        $header_detalle['vendedor_nombres'] = $this->data_usuario['nombres'];
        $header_detalle['vendedor_apellidos'] = $this->data_usuario['apellidos'];
        $header_detalle['vendedor_num_docu'] = $this->data_usuario['num_docu'];

        // Medios de pago usados en pasajes
        $query = $conn->prepare("
            SELECT m_p.descripcion AS medio_pago,
                CASE
                    WHEN pp.id_programacion_original = :id_prog_piso
                        THEN vh_original.num_piso
                    ELSE vh_actual.num_piso
                END AS num_piso
            FROM venta v
            INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            INNER JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            LEFT JOIN pasajes_pospuestos pp ON pp.id_venta_original = v.id_venta
            LEFT JOIN programacion p_actual ON p_actual.id_programacion = dt_v.id_programacion
            LEFT JOIN vehiculo vh_actual ON vh_actual.id_vehiculo = p_actual.id_vehiculo
            LEFT JOIN programacion p_original ON p_original.id_programacion = pp.id_programacion_original
            LEFT JOIN vehiculo vh_original ON vh_original.id_vehiculo = p_original.id_vehiculo
            WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
            AND (
                    (
                        dt_v.id_programacion = :id_programacion
                        AND NOT EXISTS (
                            SELECT 1
                            FROM pasajes_pospuestos pp2
                            WHERE pp2.id_venta_original = v.id_venta
                            AND pp2.estado = 'usado'
                        )
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM pasajes_pospuestos pp3
                        WHERE pp3.id_venta_original = v.id_venta
                        AND pp3.id_programacion_original = :id_prog_original
                    )
            )
            AND v.id_vendedor = :id_vendedor

            GROUP BY 
                m_p.descripcion,
                CASE
                    WHEN pp.id_programacion_original = :id_prog_group
                        THEN vh_original.num_piso
                    ELSE vh_actual.num_piso
                END
        ");
        $query->bindParam(':id_programacion', $id_programacion);
        $query->bindParam(':id_prog_original', $id_programacion);
        $query->bindParam(':id_prog_piso', $id_programacion);
        $query->bindParam(':id_prog_group', $id_programacion);
        $query->bindParam(':id_vendedor', $id_vendedor);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        // Todos los egresos individuales de esta programación/vendedor de una sola vez
        // y los indexamos por id_venta para evitar N+1 dentro del loop de ventas
        $query = $conn->prepare("
        SELECT id_venta, monto, concepto
        FROM egresos_usuario
        WHERE id_programacion = :id_programacion
          AND id_usuario       = :id_vendedor
        ");
        $query->bindParam(':id_programacion', $id_programacion);
        $query->bindParam(':id_vendedor', $id_vendedor);
        $query->execute();
        $egresos_por_venta = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $eg) {
            $egresos_por_venta[$eg['id_venta']][] = $eg;
        }

        $detalle_general = [];
        $comisiones_por_vendedor = [];
        $total_comisiones = 0;
        $total_importe_pasajes = 0;
        $total_egresos_pasajes = 0;
        $total_importe_neto_pasajes = 0;
        $total_precio_pasajes = 0;

        foreach ($medio_pagos as $medio_pago) {
            $pisos = [];

            for ($piso = 1; $piso <= $medio_pago['num_piso']; $piso++) {
                $query = $conn->prepare("
                    SELECT
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_asiento
                                THEN TRIM(ov_original.text_obj)
                            ELSE dt_v.num_asiento
                        END AS num_asiento,
                        c.nombres AS cliente_nombres,
                        c.apellidos AS cliente_apellidos,
                        c.num_docu AS cliente_num_docu,
                        v.serie AS venta_serie,
                        v.correlativo AS venta_correlativo,
                        v.fecha_emision,
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_destino
                                THEN t_d_original.nombre
                            ELSE
                                CASE
                                    WHEN dt_v.tp_destino = 1
                                        THEN r_d.nombre
                                    ELSE t_d.nombre
                                END
                        END AS terminal_destino,
                        dt_v.precio AS precio_pasaje,
                        v.op_exonerada AS importe_total,
                        CONCAT(ven.nombres, ' ', ven.apellidos) AS vendedor,
                        ven.id_tp_usuario AS vendedor_tipo,
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_piso
                                THEN ov_original.piso
                            ELSE dt_v.piso
                        END AS num_piso,
                        CASE
                            WHEN pp.estado = 'usado'
                                THEN 'POSPUESTO US.'
                            ELSE dt_v.estado_asiento
                        END AS estado_asiento,
                        v.porcentaje_venta,
                        v.id_vendedor,
                        v.id_venta
                    FROM venta v
                    LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                    LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
                    LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                    LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
                    LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
                    LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                    LEFT JOIN usuario ven ON ven.id_usuario = v.id_vendedor
                    LEFT JOIN rutas_destino rd ON rd.id_ruta_destino = dt_v.id_destino
                    LEFT JOIN terminal r_d ON r_d.id_terminal = rd.id_terminal

                    /* Información de la programación original */
                    LEFT JOIN pasajes_pospuestos pp ON pp.id_venta_original = v.id_venta
                    LEFT JOIN programacion_obj po_original ON po_original.id_venta = v.id_venta
                        AND po_original.id_programacion = pp.id_programacion_original
                    LEFT JOIN obj_vehiculo ov_original ON ov_original.id_obj_vehiculo = po_original.id_obj_vehiculo
                    LEFT JOIN programacion p_original ON p_original.id_programacion = pp.id_programacion_original
                    LEFT JOIN terminal t_d_original ON t_d_original.id_terminal = p_original.id_terminal_destino
                    WHERE ((dt_v.id_programacion = :id_programacion
                        AND NOT EXISTS (
                            SELECT 1
                            FROM pasajes_pospuestos pp2
                            WHERE pp2.id_venta_original = v.id_venta
                            AND pp2.estado = 'usado'
                            )
                        )
                        OR pp.id_programacion_original = :id_prog_original
                        )
                    AND v.estado NOT IN ('ANULADO', 'CANCELADO', 'RESERVADO')
                    AND tp_s.descripcion = 'PASAJE'
                    AND ((pp.id_programacion_original = :id_prog_piso_condicion
                            AND ov_original.piso = :piso_original
                        )
                        OR ((pp.id_programacion_original IS NULL
                            OR pp.id_programacion_original <> :id_prog_comparacion
                        )
                        AND dt_v.piso = :piso_actual
                        )
                    )
                    AND m_p.descripcion = :medio_pago
                    AND v.id_vendedor = :id_vendedor
                    ORDER BY CAST(
                        CASE
                            WHEN pp.id_programacion_original = :id_prog_orden
                                THEN TRIM(ov_original.text_obj)
                            ELSE dt_v.num_asiento
                        END AS UNSIGNED
                    ) ASC
                ");
                $query->bindParam(':id_programacion', $id_programacion);
                $query->bindParam(':id_prog_original', $id_programacion);
                $query->bindParam(':id_prog_asiento', $id_programacion);
                $query->bindParam(':id_prog_destino', $id_programacion);
                $query->bindParam(':id_prog_piso', $id_programacion);
                $query->bindParam(':id_prog_piso_condicion', $id_programacion);
                $query->bindParam(':id_prog_comparacion', $id_programacion);
                $query->bindParam(':id_prog_orden', $id_programacion);
                $query->bindParam(':piso_original', $piso);
                $query->bindParam(':piso_actual', $piso);
                $query->bindParam(':medio_pago', $medio_pago['medio_pago']);
                $query->bindParam(':id_vendedor', $id_vendedor);
                $query->execute();

                $ventas_piso = $query->fetchAll(PDO::FETCH_ASSOC);
                if (!$ventas_piso)
                    continue;
                foreach ($ventas_piso as &$venta) {

                    // Limpiar "TERMINAL" y "AGENCIA" del destino
                    if (isset($venta['terminal_destino'])) {
                        $venta['terminal_destino'] = $this->limpiarTerminal(
                            $venta['terminal_destino']
                        );
                    }

                    // Usar el mapa pre-cargado en lugar de query por venta
                    $egresos_venta = $egresos_por_venta[$venta['id_venta']] ?? [];
                    $monto_egreso = array_sum(array_column($egresos_venta, 'monto'));
                    $conceptos_egreso = array_column($egresos_venta, 'concepto');

                    $importe_total = (float) $venta['importe_total'];
                    $precio_neto = $importe_total - $monto_egreso;

                    $venta['monto_egreso'] = $monto_egreso;
                    $venta['conceptos_egreso'] = implode(', ', $conceptos_egreso);
                    $venta['precio_neto'] = $precio_neto;

                    $total_importe_pasajes += $importe_total;
                    $total_egresos_pasajes += $monto_egreso;
                    $total_importe_neto_pasajes += $precio_neto;

                    $tipo_vendedor = (int) ($venta['vendedor_tipo'] ?? 0);
                    $porcentaje_aplicado = match ($tipo_vendedor) {
                        14 => $porcentaje_nivel1,
                        15 => $porcentaje_nivel2,
                        default => 0,
                    };
                    $tipo_comision_aplicado = match ($tipo_vendedor) {
                        14 => $tp_comision_nivel1,
                        15 => $tp_comision_nivel2,
                        default => 'PORCENTAJE',
                    };

                    $comision = $tipo_comision_aplicado === 'MONTO'
                        ? $porcentaje_aplicado
                        : ($precio_neto * $porcentaje_aplicado) / 100;

                    $venta['comision_vendedor'] = $comision;
                    $venta['porcentaje_comision'] = $porcentaje_aplicado;
                    $venta['tipo_comision'] = $tipo_comision_aplicado;

                    if ($precio_neto > 0) {
                        $total_comisiones += $comision;
                        $total_precio_pasajes += $precio_neto;

                        $vendedor_nombre = $venta['vendedor'] ?? trim($header_detalle['vendedor_nombres'] . ' ' . $header_detalle['vendedor_apellidos']);
                        if (!isset($comisiones_por_vendedor[$vendedor_nombre])) {
                            $comisiones_por_vendedor[$vendedor_nombre] = [
                                'pasajes' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0],
                                'encomiendas' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0],
                            ];
                        }
                        $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['cantidad']++;
                        $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['base'] += $precio_neto;
                        $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['comision'] += $comision;
                        $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['porcentaje'] = $porcentaje_aplicado;
                    }
                }
                unset($venta);

                $pisos[] = $ventas_piso;
            }

            if ($pisos) {
                $detalle_general[] = ['medio_pago' => $medio_pago['medio_pago'], 'pisos' => $pisos];
            }
        }

        // Medios de pago usados en encomiendas
        $query = $conn->prepare("
        SELECT m_p.descripcion AS medio_pago
        FROM venta v
        INNER JOIN dt_venta   dt_v ON dt_v.id_venta     = v.id_venta
        INNER JOIN medio_pago m_p  ON m_p.id_medio_pago = v.id_medio_pago
        INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
          AND dt_v.id_programacion = :id_programacion
          AND dt_v.id_tp_servicio  = 2
          AND v.id_vendedor        = :id_vendedor
        GROUP BY m_p.descripcion
        ");
        $query->bindParam(':id_programacion', $id_programacion);
        $query->bindParam(':id_vendedor', $id_vendedor);
        $query->execute();
        $medio_pagos_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_encomiendas = [];
        $total_encomiendas = 0;
        $total_comisiones_encomiendas = 0;

        foreach ($medio_pagos_encomiendas as $medio_pago_item) {
            // id_tp_usuario añadido al JOIN para eliminar la query extra por encomienda
            $query = $conn->prepare("
            SELECT
                e.*,
                CONCAT(remi.apellidos,  ' ', remi.nombres)  AS remitente,
                remi.num_docu                               AS num_docu_remitente,
                CONCAT(desti.apellidos, ' ', desti.nombres) AS destinatario,
                desti.num_docu                              AS num_docu_destinatario,
                CONCAT(ven.apellidos,   ' ', ven.nombres)   AS vendedor,
                ven.num_docu                                AS num_docu_vendedor,
                ven.id_tp_usuario                           AS vendedor_tipo,
                m_p.descripcion                             AS medio_pago,
                CONCAT(v.serie, '-', v.correlativo)         AS comprobante,
                v.total                                     AS monto_total,
                v.porcentaje_venta,
                pr.descripcion                              AS producto,
                v.id_vendedor
            FROM encomienda e
            LEFT JOIN dt_venta           dt  ON e.id_encomienda     = dt.id_encomienda
            LEFT JOIN venta              v   ON dt.id_venta         = v.id_venta
            LEFT JOIN producto_encomienda pr  ON e.id_encomienda    = pr.id_encomienda
            LEFT JOIN medio_pago         m_p ON v.id_medio_pago     = m_p.id_medio_pago
            LEFT JOIN usuario            remi ON e.id_remitente     = remi.id_usuario
            LEFT JOIN usuario            desti ON e.id_destinatario = desti.id_usuario
            LEFT JOIN usuario            ven  ON v.id_vendedor      = ven.id_usuario
            WHERE dt.id_programacion = :id_programacion
              AND dt.id_tp_servicio  = 2
              AND m_p.descripcion    = :medio_pago
              AND v.id_vendedor      = :id_vendedor
            ");
            $query->bindParam(':id_programacion', $id_programacion);
            $query->bindParam(':medio_pago', $medio_pago_item['medio_pago']);
            $query->bindParam(':id_vendedor', $id_vendedor);
            $query->execute();
            $encomiendas_raw = $query->fetchAll(PDO::FETCH_ASSOC);

            $encomiendas_agrupadas = [];
            foreach ($encomiendas_raw as &$enc) {
                $monto_total = (float) $enc['monto_total'];
                $tipo_vendedor = (int) ($enc['vendedor_tipo'] ?? 0);
                $porcentaje_aplicado = match ($tipo_vendedor) {
                    14 => $porcentaje_nivel1,
                    15 => $porcentaje_nivel2,
                    default => 0,
                };
                $tipo_comision_aplicado = match ($tipo_vendedor) {
                    14 => $tp_comision_nivel1,
                    15 => $tp_comision_nivel2,
                    default => 'PORCENTAJE',
                };

                $comision = $tipo_comision_aplicado === 'MONTO'
                    ? $porcentaje_aplicado
                    : ($monto_total * $porcentaje_aplicado) / 100;

                $enc['comision_vendedor'] = $comision;
                $enc['porcentaje_comision'] = $porcentaje_aplicado;
                $enc['tipo_comision'] = $tipo_comision_aplicado;

                $total_encomiendas += $monto_total;
                $total_comisiones_encomiendas += $comision;

                $vendedor_nombre = $enc['vendedor'] ?? trim($header_detalle['vendedor_nombres'] . ' ' . $header_detalle['vendedor_apellidos']);
                if (!isset($comisiones_por_vendedor[$vendedor_nombre])) {
                    $comisiones_por_vendedor[$vendedor_nombre] = [
                        'pasajes' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0],
                        'encomiendas' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0],
                    ];
                }
                $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['cantidad']++;
                $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['base'] += $monto_total;
                $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['comision'] += $comision;
                $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['porcentaje'] = $porcentaje_aplicado;

                // Agrupar por comprobante acumulando productos
                $clave = $enc['comprobante'];
                if (!isset($encomiendas_agrupadas[$clave])) {
                    $encomiendas_agrupadas[$clave] = $enc;
                } else {
                    $encomiendas_agrupadas[$clave]['producto'] .= ', ' . $enc['producto'];
                }
            }
            unset($enc);

            if ($encomiendas_agrupadas) {
                $detalle_encomiendas[] = [
                    'medio_pago' => $medio_pago_item['medio_pago'],
                    'encomiendas' => array_values($encomiendas_agrupadas),
                ];
            }
        }

        // Egresos directos (tabla egreso)
        $query = $conn->prepare("
        SELECT
            e.id, e.tp_comprobante, e.cod_programacion, e.serie, e.correlativo,
            e.monto, e.concepto, NULL AS id_liquidacion,
            tc.descripcion AS tipo_comprobante_desc,
            'EGRESO_DIRECTO' AS origen
        FROM egreso e
        LEFT JOIN tp_comprobante tc ON tc.codigo = e.tp_comprobante
        WHERE e.cod_programacion = :cod_programacion
        ORDER BY e.id DESC
        ");
        $query->bindParam(':cod_programacion', $id_programacion);
        $query->execute();
        $egresos_generales = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_egresos_generales = array_sum(array_column($egresos_generales, 'monto'));

        // Egresos de liquidación de terminal
        $query = $conn->prepare("
        SELECT id_liquidacion, id_programacion, id_terminal, estado, fecha_liquidacion, comision_v, observaciones
        FROM p_liquidacion
        WHERE id_programacion = :id_programacion
        LIMIT 1
        ");
        $query->bindParam(':id_programacion', $id_programacion);
        $query->execute();
        $liquidacion_data = $query->fetch(PDO::FETCH_ASSOC);

        if ($liquidacion_data) {
            $query = $conn->prepare("
            SELECT
                et.id, et.id_liquidacion, et.tp_comprobante, et.cod_programacion,
                et.serie, et.correlativo, et.monto, et.concepto, et.id_usuario,
                tc.descripcion AS tipo_comprobante_desc,
                'EGRESO_TERMINAL' AS origen
            FROM egresos_terminal et
            LEFT JOIN tp_comprobante tc ON tc.codigo = et.tp_comprobante
            WHERE et.id_liquidacion = :id_liquidacion
            ORDER BY et.id DESC
            ");
            $query->bindParam(':id_liquidacion', $liquidacion_data['id_liquidacion']);
            $query->execute();
            $egresos_terminal = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($egresos_terminal as $eg) {
                $egresos_generales[] = $eg;
                $total_egresos_generales += floatval($eg['monto'] ?? 0);
            }
        }

        // Egresos terminal sin liquidación asociada
        $query = $conn->prepare("
        SELECT
            et.id, et.id_liquidacion, et.tp_comprobante, et.cod_programacion,
            et.serie, et.correlativo, et.monto, et.concepto, et.id_usuario,
            tc.descripcion AS tipo_comprobante_desc,
            'EGRESO_SUELTO' AS origen
        FROM egresos_terminal et
        LEFT JOIN tp_comprobante tc ON tc.codigo = et.tp_comprobante
        WHERE et.cod_programacion = :cod_programacion
          AND (et.id_liquidacion IS NULL OR et.id_liquidacion = 0)
        ORDER BY et.id DESC
        ");
        $query->bindParam(':cod_programacion', $id_programacion);
        $query->execute();
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $eg) {
            $egresos_generales[] = $eg;
            $total_egresos_generales += floatval($eg['monto'] ?? 0);
        }

        // Datos de liquidación del usuario
        $query = $conn->prepare("
        SELECT * FROM liquidacion_usuario
        WHERE id_usuario       = :id_usuario
          AND id_programacion  = :id_programacion
         ");
        $query->bindParam(':id_usuario', $id_vendedor);
        $query->bindParam(':id_programacion', $id_programacion);
        $query->execute();
        $data_liquidacion = $query->fetch(PDO::FETCH_ASSOC);

        // Totales finales
        $total_comisiones_general = $total_comisiones + $total_comisiones_encomiendas;
        $total_ventas_neto = $total_importe_neto_pasajes + $total_encomiendas;
        $total_ventas_bruto = $total_importe_pasajes + $total_encomiendas;
        $comision_empresa = ($total_ventas_neto * $porcentaje_empresa) / 100;
        $total_egresos_total = $total_egresos_pasajes + $total_egresos_generales;
        $total_a_pagar = $total_ventas_neto - $total_egresos_generales - $total_comisiones_general - $comision_empresa;

        return [
            'HEADER' => ['HEADER_EMPRESA' => $header_empresa, 'HEADER_DETALLE' => $header_detalle],
            'CONFIGURACION' => $configuracion,
            'DETALLE' => $detalle_general,
            'LIQUIDACION' => $data_liquidacion,
            'LIQUIDACION_VEHICULO' => $liquidacion_data,
            'ENCOMIENDAS' => $detalle_encomiendas,
            'EGRESOS_GENERALES' => $egresos_generales,
            'COMISIONES_POR_VENDEDOR' => $comisiones_por_vendedor,
            'TOTALES' => [
                'precio_pasajes' => $total_precio_pasajes,
                'importe_pasajes' => $total_importe_pasajes,
                'importe_neto_pasajes' => $total_importe_neto_pasajes,
                'egresos_pasajes' => $total_egresos_pasajes,
                'total_encomiendas' => $total_encomiendas,
                'total_ventas_bruto' => $total_ventas_bruto,
                'total_ventas_neto' => $total_ventas_neto,
                'total_comisiones' => $total_comisiones_general,
                'total_egresos_generales' => $total_egresos_generales,
                'total_egresos' => $total_egresos_total,
                'comision_empresa' => $comision_empresa,
                'porcentaje_empresa' => $porcentaje_empresa,
                'total_a_pagar' => $total_a_pagar,
            ],
        ];
    }

    public function getDataReporteLiquidacionTerminal($id_programacion, $id_terminal)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
        SELECT
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            vh.placa AS vehiculo_placa,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            dt_c.licencia AS conductor_licencia,
            c.nombres AS nombres_conductor,
            c.apellidos AS apellidos_conductor
        FROM programacion p
        LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        WHERE p.id_programacion=:id_programacion
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $header_detalle = $query->fetch(PDO::FETCH_ASSOC);
        $header_detalle["terminal_nombre"] = $this->data_terminal["nombre"];

        // Obteniendo la cantidad de pagos que se realizó
        $query = $this->db->connect()->prepare("SELECT 
            m_p.descripcion AS medio_pago,
            COUNT(m_p.descripcion) AS amount,
            vh.num_piso AS num_piso
        FROM venta v
        INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        INNER JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo 
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion AND v.id_terminal=:id_terminal GROUP BY m_p.descripcion");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->bindParam(":id_terminal", $id_terminal);
        $query->execute();
        $medio_pagos = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_general = array();
        for ($i = 0; $i < count($medio_pagos); $i++) {
            $pisos = array();
            for ($x = 0; $x < $medio_pagos[$i]["num_piso"]; $x++) {
                $piso = $x + 1;
                $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.num_asiento AS num_asiento,
                    c.nombres AS cliente_nombres,
                    c.apellidos AS cliente_apellidos,
                    c.num_docu AS cliente_num_docu,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_d.nombre AS terminal_destino,
                    v.op_exonerada AS importe,
                    CONCAT(ven.nombres) AS vendedor, 
                    dt_v.piso AS num_piso,
                    dt_v.estado_asiento,
                    v.porcentaje_venta
                FROM venta v
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
                LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
                LEFT JOIN usuario ven ON ven.id_usuario=v.id_vendedor
                WHERE dt_v.id_programacion=:id_programacion AND v.estado NOT IN ('ANULADO', 'CANCELADO','RESERVADO','VENTA_WEB') AND tp_s.descripcion='PASAJE' AND dt_v.piso=:piso AND m_p.descripcion=:medio_pago AND v.id_terminal=:id_terminal
                ");
                $query->bindParam(":id_programacion", $id_programacion);
                $query->bindParam(":piso", $piso);
                $query->bindParam(":medio_pago", $medio_pagos[$i]["medio_pago"]);
                $query->bindParam(":id_terminal", $id_terminal);
                $query->execute();
                $ventas_porPiso = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($ventas_porPiso) {
                    array_push($pisos, $ventas_porPiso);
                }
                if ($piso == $medio_pagos[$i]["num_piso"] && $pisos) {
                    array_push(
                        $detalle_general,
                        [
                            "medio_pago" => $medio_pagos[$i]["medio_pago"],
                            "pisos" => $pisos
                        ]
                    );
                }
            }
        }

        // ============== EXTRACCIÓN DE ENCOMIENDAS ==============
        // Obteniendo la cantidad de pagos que se realizó para ENCOMIENDAS
        $query = $this->db->connect()->prepare("
        SELECT 
        m_p.descripcion AS medio_pago,
        COUNT(m_p.descripcion) AS amount
        FROM venta v
         INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
        INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
          INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
         WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') 
         AND dt_v.id_programacion=:id_programacion 
         AND dt_v.id_tp_servicio=2 
         GROUP BY 
          m_p.descripcion;
         ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->execute();
        $medio_pagos_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

        $detalle_encomiendas = [];
        foreach ($medio_pagos_encomiendas as $medio_pago_item) {
            $encomiendas_agrupadas = [];

            $query = $this->db->connect()->prepare("
            SELECT 
              e.*,
              CONCAT(remi.apellidos, ' ',remi.nombres) AS remitente,
              remi.num_docu AS num_docu_remitente,
              CONCAT(desti.apellidos, ' ',desti.nombres) AS destinatario,
              desti.num_docu AS num_docu_destinatario,
              CONCAT(ven.apellidos, ' ',ven.nombres) AS vendedor,
              ven.num_docu AS num_docu_vendedor,
              m_p.descripcion AS medio_pago,
              CONCAT(v.serie, '-', v.correlativo) AS comprobante,
              v.total AS monto,
              v.porcentaje_venta,
              pr.descripcion AS producto
              FROM encomienda e
              LEFT JOIN dt_venta dt ON e.id_encomienda = dt.id_encomienda
              LEFT JOIN venta v ON dt.id_venta = v.id_venta 
              LEFT JOIN producto_encomienda pr ON e.id_encomienda = pr.id_encomienda 
              LEFT JOIN medio_pago m_p ON v.id_medio_pago = m_p.id_medio_pago 
              LEFT JOIN usuario remi ON e.id_remitente = remi.id_usuario 
              LEFT JOIN usuario desti ON e.id_destinatario = desti.id_usuario 
              LEFT JOIN usuario ven ON v.id_vendedor = ven.id_usuario 
              WHERE dt.id_programacion = :id_programacion 
              AND dt.id_tp_servicio = 2
              AND m_p.descripcion = :medio_pago
            ");

            $query->bindParam(":id_programacion", $id_programacion);
            $query->bindParam(":medio_pago", $medio_pago_item["medio_pago"]);
            $query->execute();
            $encomiendas_porMedioPago = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($encomiendas_porMedioPago as $enc) {
                $clave = $enc['comprobante'];

                if (!isset($encomiendas_agrupadas[$clave])) {
                    $encomiendas_agrupadas[$clave] = $enc;
                } else {
                    $encomiendas_agrupadas[$clave]['producto'] .= ', ' . $enc['producto'];
                }
            }

            if (!empty($encomiendas_agrupadas)) {
                $detalle_encomiendas[] = [
                    "medio_pago" => $medio_pago_item["medio_pago"],
                    "encomiendas" => array_values($encomiendas_agrupadas)
                ];
            }
        }

        //Obteniendo a comision
        $query = $this->db->connect()->prepare("
        SELECT
        comision_v
        FROM p_liquidacion
        WHERE id_programacion=:id_programacion AND id_terminal=:id_terminal
        ");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->bindParam(":id_terminal", $id_terminal);
        $query->execute();
        $comision = $query->fetch(PDO::FETCH_ASSOC);
        $comision_ve = isset($comision['comision_v']) ? $comision['comision_v'] : 0;

        // Obteniendo los egresos de la terminal
        $query = $this->db->connect()->prepare("SELECT 
               *
           FROM egresos_terminal e
           INNER JOIN p_liquidacion l ON e.id_liquidacion=l.id_liquidacion
           WHERE l.id_programacion=:id_programacion AND l.id_terminal=:id_terminal");
        $query->bindParam(":id_programacion", $id_programacion);
        $query->bindParam(":id_terminal", $id_terminal);
        $query->execute();
        $detalle_egresos = $query->fetchAll(PDO::FETCH_ASSOC);

        return array(
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "HEADER_DETALLE" => $header_detalle,
            ],
            "DETALLE" => $detalle_general,
            "COMISION" => $comision_ve,
            "EGRESOS" => $detalle_egresos,
            "ENCOMIENDAS" => $detalle_encomiendas
        );
    }

    public function getDataControlPasajeros($id_programacion)
    {
        try {
            // 1. Empresa
            $query = $this->db->connect()->prepare("
            SELECT e.*, CONCAT(u.depa, ' -', u.provi, ' -', u.distri) AS ubigeo
            FROM empresa e
            LEFT JOIN ubigeo u ON e.ubigeo = u.cod_ubigeo
            ");
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            // 2. Programación
            $query = $this->db->connect()->prepare("
            SELECT
                p.id_programacion,
                p.fecha_salida,
                p.hora_salida,
                t_o.nombre                           AS terminal_origen,
                t_d.nombre                           AS terminal_destino,
                vh.id_vehiculo,
                vh.placa                             AS vehiculo_placa,
                vh.marca                             AS vehiculo_marca,
                vh.num_asiento                       AS vehiculo_cantidad_asientos,
                CONCAT(c.nombres, ' ', c.apellidos)  AS conductor_nombre,
                c.num_docu                           AS conductor_dni,
                dt_c.licencia                        AS conductor_licencia
            FROM programacion p
            LEFT JOIN terminal     t_o  ON t_o.id_terminal   = p.id_terminal_origen
            LEFT JOIN terminal     t_d  ON t_d.id_terminal   = p.id_terminal_destino
            LEFT JOIN vehiculo     vh   ON vh.id_vehiculo    = p.id_vehiculo
            LEFT JOIN usuario      c    ON c.id_usuario      = p.id_conductor
            LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario   = c.id_usuario
            WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $id_programacion);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);

            // 3. Asientos del vehículo
            $query = $this->db->connect()->prepare("
            SELECT
                MIN(ov.id_obj_vehiculo) AS id_obj_vehiculo,
                ov.piso,
                ov.tp_asiento,
                TRIM(ov.text_obj)       AS numero_asiento
            FROM obj_vehiculo ov
            WHERE ov.id_vehiculo      = :id_vehiculo
              AND ov.tp_obj           = 'ASIENTO'
              AND TRIM(ov.text_obj)  != ''
              AND TRIM(ov.text_obj)  IS NOT NULL
            GROUP BY TRIM(ov.text_obj), ov.piso, ov.tp_asiento
            ORDER BY CAST(TRIM(ov.text_obj) AS UNSIGNED) ASC
            ");
            $query->bindParam(':id_vehiculo', $programacion['id_vehiculo']);
            $query->execute();
            $asientos = $query->fetchAll(PDO::FETCH_ASSOC);

            // 4. Ventas de la programación
            $query = $this->db->connect()->prepare("
            SELECT
                TRIM(dt_v.num_asiento)  AS num_asiento,
                dt_v.estado_asiento,
                dt_v.c_nino,
                dt_v.motivo,
                dt_v.id_nino,
                p.id_usuario            AS id_pasajero,
                CASE WHEN v.estado IN ('RESERVADO','RESERVADO_WEB','PROCESO_WEB') THEN NULL ELSE p.nombres          END AS pasajero_nombres,
                CASE WHEN v.estado IN ('RESERVADO','RESERVADO_WEB','PROCESO_WEB') THEN NULL ELSE p.apellidos        END AS pasajero_apellidos,
                CASE WHEN v.estado IN ('RESERVADO','RESERVADO_WEB','PROCESO_WEB') THEN NULL ELSE p.num_docu         END AS pasajero_dni,
                CASE WHEN v.estado IN ('RESERVADO','RESERVADO_WEB','PROCESO_WEB') THEN NULL ELSE p.fecha_nacimiento END AS pasajero_fecha_nacimiento,
                CASE WHEN v.estado IN ('RESERVADO','RESERVADO_WEB','PROCESO_WEB') THEN NULL ELSE p.celular          END AS pasajero_celular,
                n.nombres               AS nino_nombres,
                n.apellidos             AS nino_apellidos,
                n.num_docu              AS nino_dni,
                n.fecha_nacimiento      AS nino_fecha_nacimiento,
                v.id_venta,
                v.id_vendedor,
                CONCAT(u_v.nombres, ' ', u_v.apellidos) AS nombre_vendedor,
                v.estado                AS estado_venta,
                v.fecha_emision
            FROM dt_venta dt_v
            INNER JOIN venta   v   ON v.id_venta     = dt_v.id_venta
            LEFT JOIN  usuario p   ON p.id_usuario   = dt_v.id_pasajero
            LEFT JOIN  usuario n   ON n.id_usuario   = dt_v.id_nino
            LEFT JOIN  usuario u_v ON u_v.id_usuario = v.id_vendedor
            WHERE dt_v.id_programacion = :id_programacion
              AND dt_v.id_tp_servicio  = 1 AND v.estado NOT IN ('POSPUESTO')
            ");
            $query->bindParam(':id_programacion', $id_programacion);
            $query->execute();
            $ventas = $query->fetchAll(PDO::FETCH_ASSOC);

            // 5. Reservas activas (sin venta asociada)
            $query = $this->db->connect()->prepare("
            SELECT
                po.id_programacion_obj,
                po.id_obj_vehiculo,
                po.estado                            AS estado_reserva,
                po.estado_proceso,
                po.tiempo_reserva,
                po.cod_usuario                       AS id_vendedor_reserva,
                CONCAT(u.nombres, ' ', u.apellidos)  AS vendedor_reserva,
                TRIM(ov.text_obj)                    AS numero_asiento,
                po.fecha_registro                    AS fecha_reserva,
                po.id_venta
            FROM programacion_obj po
            INNER JOIN obj_vehiculo ov ON ov.id_obj_vehiculo = po.id_obj_vehiculo
            LEFT JOIN  usuario      u  ON u.id_usuario       = po.cod_usuario
            WHERE po.id_programacion = :id_programacion
              AND po.estado_proceso  = 'RESERVADO'
              AND (po.estado = 'RESERVADO' OR po.estado = 1)
              AND (po.id_venta IS NULL OR po.id_venta = 0 OR po.id_venta = '')
            ");
            $query->bindParam(':id_programacion', $id_programacion);
            $query->execute();
            $reservas = $query->fetchAll(PDO::FETCH_ASSOC);

            // 6. Armar mapa de asientos: base DISPONIBLE → aplicar reservas → aplicar ventas
            $mapa = [];
            foreach ($asientos as $asiento) {
                $num = trim($asiento['numero_asiento']);
                $mapa[$num] = [
                    'num_asiento' => $num,
                    'piso' => $asiento['piso'],
                    'tp_asiento' => $asiento['tp_asiento'],
                    'id_obj_vehiculo' => $asiento['id_obj_vehiculo'],
                    'estado' => 'DISPONIBLE',
                    'tiene_venta' => false,
                    'tiene_reserva' => false,
                    'venta' => null,
                    'reserva' => null,
                ];
            }

            foreach ($reservas as $reserva) {
                $num = trim($reserva['numero_asiento']);
                if (isset($mapa[$num]) && !$mapa[$num]['tiene_venta']) {
                    $mapa[$num]['tiene_reserva'] = true;
                    $mapa[$num]['estado'] = 'RESERVADO';
                    $mapa[$num]['reserva'] = $reserva;
                }
            }

            foreach ($ventas as $venta) {
                $num = trim($venta['num_asiento']);
                $estadoVenta = $this->determinarEstadoAsiento($venta['estado_venta']);

                if (!isset($mapa[$num]))
                    continue;

                // Reserva de programacion_obj ya cubre este asiento, ignorar
                if ($mapa[$num]['tiene_reserva'] && $estadoVenta === 'RESERVADO')
                    continue;

                if ($estadoVenta === 'RESERVADO') {
                    $mapa[$num]['tiene_reserva'] = true;
                    $mapa[$num]['estado'] = 'RESERVADO';
                    $mapa[$num]['reserva'] = ['vendedor_reserva' => $venta['nombre_vendedor'] ?? 'S/D'];
                } else {
                    $venta['pasajero_edad'] = !empty($venta['pasajero_fecha_nacimiento']) ? $this->calcularEdad($venta['pasajero_fecha_nacimiento']) : null;
                    $venta['nino_edad'] = !empty($venta['nino_fecha_nacimiento']) ? $this->calcularEdad($venta['nino_fecha_nacimiento']) : null;

                    $mapa[$num]['tiene_venta'] = true;
                    $mapa[$num]['tiene_reserva'] = false;
                    $mapa[$num]['estado'] = $estadoVenta;
                    $mapa[$num]['venta'] = $venta;
                    $mapa[$num]['reserva'] = null;
                }
            }

            uksort($mapa, fn($a, $b) => intval($a) - intval($b));

            // 7. Estadísticas
            $cantidadVentas = $cantidadReservas = $cantidadAnulados = 0;
            foreach ($mapa as $asiento) {
                if ($asiento['tiene_venta']) {
                    $asiento['estado'] === 'ANULADO' ? $cantidadAnulados++ : $cantidadVentas++;
                } elseif ($asiento['tiene_reserva']) {
                    $cantidadReservas++;
                }
            }

            return [
                'success' => true,
                'emisor' => $emisor,
                'programacion' => $programacion,
                'asientos' => array_values($mapa),
                'total_asientos' => count($mapa),
                'cantidad_ventas' => $cantidadVentas,
                'cantidad_reservas' => $cantidadReservas,
                'cantidad_anulados' => $cantidadAnulados,
                'cantidad_disponibles' => count($mapa) - $cantidadVentas - $cantidadReservas - $cantidadAnulados,
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcula la edad a partir de la fecha de nacimiento
     */
    private function calcularEdad($fecha_nacimiento)
    {
        if (empty($fecha_nacimiento)) {
            return null;
        }

        try {
            $fechaNac = new DateTime($fecha_nacimiento);
            $hoy = new DateTime();
            $edad = $hoy->diff($fechaNac)->y;
            return $edad;
        } catch (Exception $e) {
            error_log("Error calculando edad: " . $e->getMessage());
            return null;
        }
    }

    /**
     *  Determina el estado del asiento basado en el estado de la venta
     */
    private function determinarEstadoAsiento($estadoVenta)
    {
        $estadosAnulados = ['ANULADO', 'CANCELADO', 'ANULADA'];
        $estadosVendidos = ['CONFIRMADO', 'COMPLETADO', 'PAGADO', 'ENTREGADO', 'VENDIDO'];
        $estadosReservados = ['RESERVADO', 'RESERVADO_WEB', 'PROCESO_WEB'];

        if (in_array($estadoVenta, $estadosAnulados)) {
            return 'ANULADO';
        } elseif (in_array($estadoVenta, $estadosVendidos)) {
            return 'VENDIDO';
        } elseif (in_array($estadoVenta, $estadosReservados)) {
            return 'RESERVADO';
        } else {
            return 'DISPONIBLE';
        }
    }

    public function liquidar_vehiculo($data, $tp_liquidacion = 'programacion')
    {
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $comision_pasajes = 0;
            $fecha_liquidacion = date("Y-m-d H:i:s");

            // Actualizar estado de programación
            $query = $conn->prepare("UPDATE programacion SET estado=0, liquidado=1, fecha_liquidacion=:fecha_liquidacion WHERE id_programacion=:id_programacion");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->bindParam(":fecha_liquidacion", $fecha_liquidacion);
            $query->execute();

            if ($tp_liquidacion == 'terminal') {
                // Para liquidación por terminal, sumar las comisiones existentes
                $query = $conn->prepare("SELECT SUM(comision_v) AS comision_total FROM p_liquidacion WHERE id_programacion=:id_programacion ");
                $query->bindParam(":id_programacion", $data["id_programacion"]);
                $query->execute();
                $monto_pasajes = $query->fetch(PDO::FETCH_ASSOC);
                $comision_pasajes = $monto_pasajes['comision_total'] ?? 0;
            } else {
                // Calcular comisión de PASAJES
                if (isset($data['porcen']) && ($data['porcen'] != 0 || $data['monto'] != '')) {
                    if ($data['tp_porcen'] == 1) {
                        $query4 = $conn->prepare("SELECT SUM(v.total) AS total FROM venta v INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion");
                        $query4->bindParam(":id_programacion", $data["id_programacion"]);
                        $query4->execute();
                        $total = $query4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                        $comision_pasajes = ($total * $data['porcen']) / 100;
                    } else {
                        $comision_pasajes = floatval($data['monto']);
                    }
                }

                // Insertar detalles de PASAJES si existen
                if (isset($data['detalles']) && $data['detalles'] != '[]' && !empty($data['detalles'])) {
                    $detalles = json_decode($data['detalles']);
                    foreach ($detalles as $detalle) {
                        $query2 = $conn->prepare("INSERT INTO egreso (cod_programacion, tp_comprobante, serie, correlativo, monto, concepto) VALUES (:cod_programacion, :tp_comprobante, :serie, :correlativo, :monto, :concepto)");
                        $query2->bindParam(":cod_programacion", $data["id_programacion"]);
                        $query2->bindParam(":tp_comprobante", $detalle[0]);
                        $query2->bindParam(":serie", $detalle[1]);
                        $query2->bindParam(":correlativo", $detalle[2]);
                        $query2->bindParam(":monto", $detalle[3]);
                        $query2->bindParam(":concepto", $detalle[4]);
                        $query2->execute();
                    }
                }
            }

            // Crear registros en p_liquidacion para PASAJES
            if ($comision_pasajes > 0 || (isset($data['observaciones']) && !empty($data['observaciones']))) {
                $query_p = $conn->prepare("INSERT INTO p_liquidacion (id_programacion, comision_v, observaciones, fecha_liquidacion, estado) VALUES (:id_programacion, :comision, :observaciones, :fecha_liquidacion, 'liquidado')");
                $query_p->bindParam(":id_programacion", $data["id_programacion"]);
                $query_p->bindParam(":comision", $comision_pasajes);
                $query_p->bindParam(":observaciones", $data['observaciones']);
                $query_p->bindParam(":fecha_liquidacion", $fecha_liquidacion);
                $query_p->execute();
            }

            // Actualizar la comisión total en programacion (suma de ambas)
            $comision_total = $comision_pasajes;
            $query1 = $conn->prepare("UPDATE programacion SET comision_v=:comision WHERE id_programacion=:id_programacion");
            $query1->bindParam(":id_programacion", $data["id_programacion"]);
            $query1->bindParam(":comision", $comision_total);
            $query1->execute();

            $conn->commit();
            return array("success" => true, "message" => "Vehículo liquidado con éxito");
        } catch (Exception $e) {
            $conn->rollBack();
            return array("success" => false, "message" => "Error al liquidar el vehículo: " . $e->getMessage());
        }
    }

    public function liquidar_terminal_v($data)
    {
        try {
            // Guardar la conexión en una variable
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Insertar en la tabla liquidacion
            $query = $conn->prepare("
                INSERT INTO p_liquidacion
                (id_programacion, id_terminal, estado, fecha_liquidacion, observaciones)
                VALUES (:id_programacion, :id_terminal, 'liquidado', NOW(), :observaciones)
            ");

            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->bindParam(":observaciones", $data['observaciones']);
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);

            if (!$query->execute()) {
                throw new Exception("Error al crear la liquidación");
            }

            // Obtener el id_liquidacion generado
            $id_liquidacion = $conn->lastInsertId();

            if ($data['porcen'] != 0 || $data['monto'] != '') {
                $comision = 0;
                if ($data['tp_porcen'] == '1') {
                    $query4 = $conn->prepare("SELECT 
                    SUM(v.total) AS total
                    FROM venta v
                    INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                    INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                    WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion
                    AND v.id_terminal =:id_terminal");
                    $query4->bindParam(":id_programacion", $data["id_programacion"]);
                    $query4->bindParam(":id_terminal", $this->id_terminal_sesion);
                    $query4->execute();
                    $total = $query4->fetch(PDO::FETCH_ASSOC)['total'];

                    $comision = ($total * $data['porcen']) / 100;
                } else {
                    $comision = $data['monto'];
                }

                //Actualizar
                $query1 = $conn->prepare("UPDATE p_liquidacion SET comision_v=:comision WHERE id_liquidacion=:id_liquidacion");
                $query1->bindParam(":id_liquidacion", $id_liquidacion);
                $query1->bindParam(":comision", $comision);
                $query1->execute();
            }

            // Si hay detalles, insertarlos
            if (!empty($data['detalles']) && $data['detalles'] !== '[]') {
                $detalles = json_decode($data['detalles'], true);

                foreach ($detalles as $detalle) {
                    $query2 = $conn->prepare("
                        INSERT INTO egresos_terminal
                        (id_liquidacion, tp_comprobante, serie, correlativo, monto, concepto) 
                        VALUES 
                        (:id_liquidacion, :tp_comprobante, :serie, :correlativo, :monto, :concepto)
                    ");

                    $query2->bindParam(":id_liquidacion", $id_liquidacion);
                    $query2->bindParam(":tp_comprobante", $detalle[0]);
                    $query2->bindParam(":serie", $detalle[1]);
                    $query2->bindParam(":correlativo", $detalle[2]);
                    $query2->bindParam(":monto", $detalle[3]);
                    $query2->bindParam(":concepto", $detalle[4]);

                    if (!$query2->execute()) {
                        throw new Exception("Error al insertar detalle de egreso");
                    }
                }
            }

            $conn->commit();

            // Consultar si es la última terminal en liquidar - fuera de la transacción
            // Consultar si es la última terminal en liquidar - fuera de la transacción
            $query = $this->db->connect()->prepare("
            SELECT 
              COUNT(*) as total
            FROM (
            -- Terminal origen de programación
            SELECT p.id_terminal_origen as id_terminal
            FROM programacion p
            WHERE p.id_programacion = :id_prog1
        
            UNION
        
             -- Terminales de la tabla ruta
             SELECT r.id_terminal
             FROM ruta r
               WHERE r.id_programacion = :id_prog2
             ) as terminales
            LEFT JOIN p_liquidacion pl ON 
                terminales.id_terminal = pl.id_terminal AND 
                pl.id_programacion = :id_prog3
              WHERE pl.estado IS NULL OR pl.estado != 'liquidado'
            ");

            // Vincular el mismo valor a diferentes parámetros
            $query->bindParam(":id_prog1", $data["id_programacion"]);
            $query->bindParam(":id_prog2", $data["id_programacion"]);
            $query->bindParam(":id_prog3", $data["id_programacion"]);

            $query->execute();
            $total = $query->fetch(PDO::FETCH_ASSOC)['total'];

            // Si total es 0, significa que es la última terminal en liquidar
            if ($total == 0) {
                $tp_l = 'terminal';
                $this->liquidar_vehiculo($data, $tp_l);
            }

            return array(
                "success" => true,
                "message" => "Terminal liquidado con éxito"
            );
        } catch (Exception $e) {
            // Verificar si hay una transacción activa antes de hacer rollback
            try {
                $conn = $this->db->connect();
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
            } catch (Exception $rollbackError) {
                // Ignorar errores en el rollback
            }

            return array(
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            );
        }
    }

    public function liquidar_usuario_p($data)
    {
        try {
            // Guardar la conexión en una variable
            $conn = $this->db->connect();
            $conn->beginTransaction();

            // Insertar en la tabla liquidacion
            $query = $conn->prepare("
                INSERT INTO liquidacion_usuario
                (id_programacion, id_terminal, id_usuario, estado, fecha_liquidacion, observaciones)
                VALUES (:id_programacion, :id_terminal, :id_usuario, 'liquidado', NOW(), :observaciones)
            ");

            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->bindParam(":observaciones", $data['observaciones_LU']);
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);

            if (!$query->execute()) {
                throw new Exception("Error al crear la liquidación");
            }

            // Obtener el id_liquidacion generado
            $id_liquidacion = $conn->lastInsertId();

            $comision_empresa = 0;
            $comision_usuario = 0;

            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $query = $conn->prepare("
                    SELECT porcentaje_empresa
                    FROM configuracion
                ");
                $query->execute();
                $porcent_empresa = $query->fetchColumn();

                $data['porcentaje_empresa'] = $porcent_empresa;
            }

            $porcentaje_empresa = $data['porcentaje_empresa'] ?? 0;
            if ($porcentaje_empresa > 0) {
                $query4 = $conn->prepare("SELECT 
                    SUM(v.total) AS total,
                    SUM(v.porcentaje_venta) AS total_comision
                    FROM venta v
                    INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
                    INNER JOIN programacion p ON p.id_programacion=dt_v.id_programacion
                    WHERE v.estado NOT IN ('ANULADO', 'CANCELADO') AND dt_v.id_programacion=:id_programacion
                    AND v.id_terminal =:id_terminal");
                $query4->bindParam(":id_programacion", $data["id_programacion"]);
                $query4->bindParam(":id_terminal", $this->id_terminal_sesion);
                $query4->execute();
                $totales = $query4->fetch(PDO::FETCH_ASSOC);

                $totales['total'] ? $totales['total'] : 0;
                $comision_empresa = ($totales['total'] * $data['porcentaje_empresa']) / 100;
                $comision_usuario = $totales['total_comision'] ?? 0;
            }
            //Actualizar
            $query1 = $conn->prepare("UPDATE liquidacion_usuario 
            SET comision_empresa=:comision_empresa, comision_usuario=:comision_usuario
             WHERE id=:id");
            $query1->bindParam(":id", $id_liquidacion);
            $query1->bindParam(":comision_empresa", $comision_empresa);
            $query1->bindParam(":comision_usuario", $comision_usuario);
            $query1->execute();

            // Si hay detalles, insertarlos
            if (!empty($data['detalles']) && $data['detalles'] !== '[]') {
                $detalles = json_decode($data['detalles'], true);

                $query2 = $conn->prepare("
                  INSERT INTO egresos_usuario
                  (id_usuario, id_programacion, tp_comprobante, serie, correlativo, monto, concepto) 
                  VALUES 
                  (:id_usuario, :id_programacion, :tp_comprobante, :serie, :correlativo, :monto, :concepto)
                ");

                foreach ($detalles as $detalle) {

                    if (
                        !$query2->execute([
                            ":id_usuario" => $this->id_usuario_sesion,
                            ":id_programacion" => $data['id_programacion'],
                            ":tp_comprobante" => $detalle[0],
                            ":serie" => $detalle[1],
                            ":correlativo" => $detalle[2],
                            ":monto" => $detalle[3],
                            ":concepto" => $detalle[4]
                        ])
                    ) {
                        throw new Exception("Error al insertar detalle de egreso");
                    }
                }
            }

            // Desahbilitar estado de programación
            if ($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2) {
                $fecha_liquidacion = date("Y-m-d H:i:s");

                $query = $conn->prepare("UPDATE programacion SET estado=0, liquidado=1, fecha_liquidacion=:fecha_liquidacion WHERE id_programacion=:id_programacion");
                $query->bindParam(":id_programacion", $data["id_programacion"]);
                $query->bindParam(":fecha_liquidacion", $fecha_liquidacion);
                $query->execute();
            }

            $conn->commit();

            $link_general = $this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2 ? URL . '/pasaje/impresion/liquidacion_vehiculo/' . $data['id_programacion'] : '';
            $link_liquidacion = URL . 'pasaje/impresion/liquidacion_usuario/' . $data['id_programacion'] . '/' . $this->id_usuario_sesion;
            return [
                "success" => true,
                "links" => [
                    'Usuario' => $link_liquidacion,
                    'Programacion' => $link_general,
                    'Ticket Usuario' => URL . 'pasaje/impresion/liquidacion_usuarioticket/' . $data['id_programacion'] . '/' . $this->id_usuario_sesion  // <-- AGREGAR ESTA LÍNEA
                ],
                "message" => "Liquidado con éxito"
            ];
        } catch (Exception $e) {
            // Verificar si hay una transacción activa antes de hacer rollback
            try {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
            } catch (Exception $rollbackError) {
                // Ignorar errores en el rollback
            }

            return [
                "success" => false,
                "message" => "Error: " . $e
            ];
        }
    }

    public function updateVentaSetForSunat($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $envio_sunat = $data["estado"] == 1 ? 1 : 0;

            $query = $conn->prepare("UPDATE venta SET
            envio_sunat = :envio_sunat,
            descrip_cdr_sunat = :descrip_cdr_sunat,
            hash_cdr = :hash_cdr,
            file_xml = :file_xml,
            file_cdr = :file_cdr
            WHERE id_venta = :id_venta
            ");

            $query->bindParam(':envio_sunat', $envio_sunat);
            $query->bindParam(':descrip_cdr_sunat', $data["mensaje_sunat"]);
            $query->bindParam(':hash_cdr', $data["hash_cpe"]);
            $query->bindParam(':file_xml', $data["xml"]);
            $query->bindParam(':file_cdr', $data["cdr"]);
            $query->bindParam(':id_venta', $data["id_venta"]);

            if ($query->execute()) {
                return ["success" => true];
            } else {
                return ["success" => false, "message" => "Error al actualizar la venta para SUNAT"];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    // API SUNAT
    public function conversor_data($datos)
    {

        $emisor = array(
            "tipodoc" => $datos['EMISOR']['tp_doc_id'],
            "ruc" => $datos['EMISOR']['empresa_ruc'],
            "razon_social" => $datos['EMISOR']['empresa_razon_social'],
            "nombre_comercial" => $datos['EMISOR']['empresa_razon_social'],
            "departamento" => $datos['EMISOR']['departamento'],
            "provincia" => $datos['EMISOR']['provincia'],
            "distrito" => $datos['EMISOR']['distrito'],
            "direccion" => $datos['EMISOR']['direccion'],
            "ubigeo" => $datos['EMISOR']['ubigeo_empresa'],
            "usuario_sol" => $datos['EMISOR']['empresa_usuario_sol'],
            "clave_sol" => $datos['EMISOR']['empresa_pass_sol']
        );
        $cliente = array(
            "tipo_documento" => $datos['CLIENTE']['cliente_id_tp_docu'],
            "ruc" => $datos['CLIENTE']['cliente_num_docu'],
            "razon_social" => $datos['CLIENTE']['cliente_nombres'] . ' ' . $datos['CLIENTE']['cliente_apellidos'],
            "direccion" => $datos['CLIENTE']['cliente_direccion'],
            "pais" => "PE"
        );

        $cabecera = array(
            "tipo_operacion" => "0101",
            "tipo_comprobante" => $datos['CABECERA']['tp_comprobante_codigo'],
            "moneda" => $datos['CABECERA']['tp_moneda_codigo'],
            "serie" => $datos['CABECERA']['serie'],
            "correlativo" => $datos['CABECERA']['correlativo'],
            "total_op_gravadas" => $datos['CABECERA']['op_gravada'],
            "igv" => $datos['CABECERA']['op_igv'],
            "icbper" => 0.00,
            "total_op_exoneradas" => $datos['CABECERA']['op_exonerada'],
            "total_op_inafectas" => $datos['CABECERA']['op_inafecta'],
            "total_antes_impuestos" => $datos['CABECERA']['op_exonerada'],
            "total_impuestos" => 0.00,
            "total_despues_impuestos" => $datos['CABECERA']['op_exonerada'],
            "descuento_global" => 0.00,
            "suma_descuento_item" => 0.00,
            "total_a_pagar" => $datos['CABECERA']['op_exonerada'],
            "fecha_emision" => $datos['CABECERA']['fecha_registro_format'],
            "hora_emision" => $datos['CABECERA']['hora_registro_format'],
            "fecha_vencimiento" => $datos['CABECERA']['fecha_registro_format'],
            "forma_pago" => ucwords(strtolower(ucfirst($datos['CABECERA']['forma_pago']))),
            "monto_credito" => 0.00,
            "anexo_sucursal" => "0000",
            "cuotas" => array()
        );

        foreach ($datos['ITEMS'] as $itemData) {
            $item = array(
                "item" => $itemData['iten'],
                "nombre" => "PASAJE",
                "cantidad" => 1,
                "codigo" => 0,
                "valor_unitario" => $itemData['precio'],
                "precio_lista" => $itemData['precio'],
                "valor_total" => $itemData['precio'],
                "igv" => $itemData['op_igv'],
                "icbper" => 0.00,
                "factor_icbper" => 0.00,
                "total_antes_impuestos" => $itemData['op_exonerada'],
                "total_impuestos" => $itemData['op_igv'],
                "porcentaje_igv" => 18,
                "unidad" => "ZZ",
                "codigos" => array("E", "20", "9997", "EXO", "VAT")
            );
            $items[] = $item;
        }
        $result = array(
            "ose" => $datos['EMISOR']['ose'],
            "emisor" => $emisor,
            "cliente" => $cliente,
            "cabecera" => $cabecera,
            "items" => $items
        );

        $jsonResult = json_encode($result);

        return $jsonResult;
    }

    public function enviar_json_a_api($json)
    {

        $api_url = API_URL;

        $opciones = array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            )
        );

        $curl = curl_init();
        curl_setopt_array($curl, $opciones);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return 'Error en la solicitud: ' . curl_error($curl);
        }

        curl_close($curl);

        return $response;
    }

    public function consultar_impresora()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM impresoras WHERE estado=1 AND cod_usuario = :cod_usuario");
        $query->bindParam(':cod_usuario', $this->id_usuario_sesion);
        $query->execute();
        $impresora = $query->fetch(PDO::FETCH_ASSOC);

        if ($impresora) {
            return array(
                "success" => true,
                "data" => $impresora
            );
        } else {
            return array(
                "success" => false,
                "message" => "No hay impresoras activas"
            );
        }
    }

    public function get_resumenVendedores($id_programacion)
    {
        try {
            $conn = $this->db->connect();

            // Obtener el tipo de usuario de la sesión actual
            $tp_usuario_actual = $this->tp_usuario_sesion;
            $id_usuario_actual = $this->id_usuario_sesion;

            // Obtener los datos completos del usuario actual si es necesario
            $datos_usuario_actual = [];
            if (!in_array($tp_usuario_actual, [1, 2])) {
                $query_usuario = $conn->prepare("
                SELECT u.id_usuario, u.nombres, u.apellidos, u.num_docu, 
                       tp_u.descripcion AS tipo_usuario, tp_u.id_tp_usuario,
                       t.nombre AS terminal_nombre
                FROM usuario u
                INNER JOIN tp_usuario tp_u ON u.id_tp_usuario = tp_u.id_tp_usuario
                LEFT JOIN terminal t ON u.id_terminal = t.id_terminal
                WHERE u.id_usuario = :id_usuario
            ");
                $query_usuario->bindParam(":id_usuario", $id_usuario_actual, PDO::PARAM_INT);
                $query_usuario->execute();
                $datos_usuario_actual = $query_usuario->fetch(PDO::FETCH_ASSOC);
            }

            // Definir qué tipos de usuario pueden ver a todos los vendedores
            // SOPORTE(1) o ADMINISTRADOR(2)
            $es_admin_soporte = in_array($tp_usuario_actual, [1, 2]);

            // Variable para almacenar los vendedores a mostrar
            $todos_vendedores = [];
            $vistos = [];

            // PASO 1: Si es ADMIN o SOPORTE, obtener TODOS los vendedores que han vendido en esta programación
            if ($es_admin_soporte) {
                $query_vendedores_con_ventas = $conn->prepare("
                SELECT DISTINCT
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.num_docu,
                    tp_u.descripcion AS tipo_usuario,
                    tp_u.id_tp_usuario,
                    t.nombre AS terminal_nombre
                FROM venta v
                INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                INNER JOIN usuario u ON v.id_vendedor = u.id_usuario
                INNER JOIN tp_usuario tp_u ON u.id_tp_usuario = tp_u.id_tp_usuario
                LEFT JOIN terminal t ON u.id_terminal = t.id_terminal
                WHERE dt_v.id_programacion = :id_programacion1
                    AND dt_v.id_tp_servicio = 1
                    AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                ORDER BY u.nombres ASC
            ");

                $query_vendedores_con_ventas->bindParam(":id_programacion1", $id_programacion, PDO::PARAM_INT);
                $query_vendedores_con_ventas->execute();
                $vendedores_con_ventas = $query_vendedores_con_ventas->fetchAll(PDO::FETCH_ASSOC);

                // PASO 2: Obtener TODAS las terminales involucradas en la programación
                $query_terminales = $conn->prepare("
                SELECT DISTINCT id_terminal_origen AS id_terminal
                FROM programacion 
                WHERE id_programacion = :id_prog1
                
                UNION
                
                SELECT DISTINCT id_terminal
                FROM ruta
                WHERE id_programacion = :id_prog2
                
                UNION
                
                SELECT DISTINCT id_terminal
                FROM rutas_destino
                WHERE id_programacion = :id_prog3
                ");

                $query_terminales->bindParam(":id_prog1", $id_programacion, PDO::PARAM_INT);
                $query_terminales->bindParam(":id_prog2", $id_programacion, PDO::PARAM_INT);
                $query_terminales->bindParam(":id_prog3", $id_programacion, PDO::PARAM_INT);
                $query_terminales->execute();
                $terminales = $query_terminales->fetchAll(PDO::FETCH_COLUMN);

                // PASO 3: Obtener vendedores de esas terminales (incluyendo los que no han vendido)
                $vendedores_por_terminal = [];
                if (!empty($terminales)) {
                    $placeholders = implode(',', array_fill(0, count($terminales), '?'));

                    $query_vendedores_terminal = $conn->prepare("
                    SELECT DISTINCT
                        u.id_usuario,
                        u.nombres,
                        u.apellidos,
                        u.num_docu, 
                        tp_u.descripcion AS tipo_usuario,
                        tp_u.id_tp_usuario,
                        t.nombre AS terminal_nombre
                    FROM usuario u
                    INNER JOIN tp_usuario tp_u ON u.id_tp_usuario = tp_u.id_tp_usuario
                    LEFT JOIN terminal t ON u.id_terminal = t.id_terminal
                    WHERE u.id_terminal IN ($placeholders)
                        AND u.estado = 1
                        AND u.id_tp_usuario IN (2, 3, 14, 15)
                    ORDER BY u.nombres ASC
                ");

                    $query_vendedores_terminal->execute($terminales);
                    $vendedores_por_terminal = $query_vendedores_terminal->fetchAll(PDO::FETCH_ASSOC);
                }

                // PASO 4: COMBINAR todos los vendedores (sin duplicados)
                // Primero, agregar vendedores con ventas (prioridad)
                foreach ($vendedores_con_ventas as $vendedor) {
                    $id = $vendedor['id_usuario'];
                    if (!isset($vistos[$id])) {
                        $todos_vendedores[] = $vendedor;
                        $vistos[$id] = true;
                    }
                }

                // Luego, agregar vendedores de terminales que no estén ya incluidos
                foreach ($vendedores_por_terminal as $vendedor) {
                    $id = $vendedor['id_usuario'];
                    if (!isset($vistos[$id])) {
                        $todos_vendedores[] = $vendedor;
                        $vistos[$id] = true;
                    }
                }

                // Si no hay vendedores de terminales, al menos mostrar los de la terminal origen
                if (empty($todos_vendedores)) {
                    $query_origen = $conn->prepare("
                    SELECT DISTINCT
                        u.id_usuario,
                        u.nombres,
                        u.apellidos,
                        u.num_docu,
                        tp_u.descripcion AS tipo_usuario,
                        tp_u.id_tp_usuario,
                        t.nombre AS terminal_nombre
                    FROM usuario u
                    INNER JOIN tp_usuario tp_u ON u.id_tp_usuario = tp_u.id_tp_usuario
                    LEFT JOIN terminal t ON u.id_terminal = t.id_terminal
                    WHERE u.id_terminal = (
                        SELECT id_terminal_origen 
                        FROM programacion 
                        WHERE id_programacion = :id_programacion2
                    )
                    AND u.estado = 1
                    AND u.id_tp_usuario IN (2, 3, 14, 15)
                    ORDER BY u.nombres ASC
                ");

                    $query_origen->bindParam(":id_programacion2", $id_programacion, PDO::PARAM_INT);
                    $query_origen->execute();
                    $todos_vendedores = $query_origen->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                // Si es VENDEDOR(3) o COMISIONISTA 1(14) o COMISIONISTA 2(15), mostrar solo su propio usuario
                if ($datos_usuario_actual) {
                    $todos_vendedores[] = [
                        'id_usuario' => $datos_usuario_actual['id_usuario'],
                        'nombres' => $datos_usuario_actual['nombres'],
                        'apellidos' => $datos_usuario_actual['apellidos'],
                        'num_docu' => $datos_usuario_actual['num_docu'],
                        'tipo_usuario' => $datos_usuario_actual['tipo_usuario'],
                        'id_tp_usuario' => $datos_usuario_actual['id_tp_usuario'],
                        'terminal_nombre' => $datos_usuario_actual['terminal_nombre'] ?? 'S/T'
                    ];
                } else {
                    // Fallback: usar datos de la sesión
                    $todos_vendedores[] = [
                        'id_usuario' => $id_usuario_actual,
                        'nombres' => $this->data_usuario["nombres"] ?? 'Usuario',
                        'apellidos' => $this->data_usuario["apellidos"] ?? '',
                        'num_docu' => $this->data_usuario["num_docu"] ?? '',
                        'tipo_usuario' => $this->data_usuario["tipo_usuario"] ?? 'VENDEDOR',
                        'id_tp_usuario' => $tp_usuario_actual,
                        'terminal_nombre' => $this->data_terminal["nombre"] ?? 'S/T'
                    ];
                }
            }

            // PASO 5: Obtener las estadísticas de ventas para CADA vendedor
            // IMPORTANTE: Siempre usar GROUP BY para obtener estadísticas por vendedor
            $stats_query = $conn->prepare("
            SELECT 
                v.id_vendedor,
                COUNT(CASE 
                    WHEN dt_v.estado_asiento = 'VENDIDO' 
                    AND v.estado = 'PAGADO'
                    AND dt_v.id_tp_servicio = 1 
                    THEN 1 
                END) AS total_vendidos,
                COUNT(CASE 
                    WHEN (dt_v.estado_asiento IN ('ANULADO', 'CANCELADO') 
                    OR v.estado IN ('ANULADO', 'CANCELADO'))
                    AND dt_v.id_tp_servicio = 1 
                    THEN 1 
                END) AS total_anulados,
                COUNT(CASE 
                    WHEN dt_v.estado_asiento IN ('RESERVADO', 'RESERVADO_WEB', 'VENTA_WEB')
                    AND v.estado IN ('RESERVADO', 'PAGADO')
                    AND dt_v.id_tp_servicio = 1 
                    THEN 1 
                END) AS total_reservados,
                COUNT(CASE 
                    WHEN v.id_tp_comprobante = 2 
                    AND dt_v.estado_asiento = 'VENDIDO'
                    AND v.estado = 'PAGADO'
                    AND dt_v.id_tp_servicio = 1
                    THEN 1 
                END) AS total_nota_venta,
                COUNT(CASE 
                    WHEN v.id_tp_comprobante = 3 
                    AND dt_v.estado_asiento = 'VENDIDO'
                    AND v.estado = 'PAGADO'
                    AND dt_v.id_tp_servicio = 1
                    THEN 1 
                END) AS total_boleta,
                COUNT(CASE 
                    WHEN v.id_tp_comprobante = 1 
                    AND dt_v.estado_asiento = 'VENDIDO'
                    AND v.estado = 'PAGADO'
                    AND dt_v.id_tp_servicio = 1
                    THEN 1 
                END) AS total_factura,
                COALESCE(SUM(CASE 
                    WHEN dt_v.estado_asiento = 'VENDIDO'
                    AND v.estado = 'PAGADO'
                    AND dt_v.id_tp_servicio = 1 
                    THEN dt_v.precio 
                    ELSE 0 
                END), 0) AS monto_total
            FROM venta v
            INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            WHERE dt_v.id_programacion = :id_programacion3
            AND dt_v.id_tp_servicio = 1
        ");

            // Si es admin/soporte, agrupar por vendedor para obtener estadísticas individuales
            if ($es_admin_soporte) {
                $stats_query = $conn->prepare("
                SELECT 
                    v.id_vendedor,
                    COUNT(CASE 
                        WHEN dt_v.estado_asiento = 'VENDIDO' 
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_vendidos,
                    COUNT(CASE 
                        WHEN (dt_v.estado_asiento IN ('ANULADO', 'CANCELADO') 
                        OR v.estado IN ('ANULADO', 'CANCELADO'))
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_anulados,
                    COUNT(CASE 
                        WHEN (dt_v.estado_asiento IN ('POSPUESTO') 
                        OR v.estado IN ('POSPUESTO'))
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_pospuestos,
                    COUNT(CASE 
                        WHEN dt_v.estado_asiento IN ('RESERVADO', 'RESERVADO_WEB', 'VENTA_WEB')
                        AND v.estado IN ('RESERVADO', 'PAGADO')
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_reservados,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 2 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_nota_venta,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 3 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_boleta,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 1 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_factura,
                    COALESCE(SUM(CASE 
                        WHEN dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1 
                        THEN dt_v.precio 
                        ELSE 0 
                    END), 0) AS monto_total
                    FROM venta v
                    INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                    WHERE dt_v.id_programacion = :id_programacion3
                    AND dt_v.id_tp_servicio = 1
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM pasajes_pospuestos pp 
                        WHERE pp.id_venta_original = v.id_venta 
                        AND pp.estado = 'usado'
                    )
                GROUP BY v.id_vendedor
            ");
            } else {
                // Si no es admin/soporte, filtrar por el vendedor actual Y agrupar
                $stats_query = $conn->prepare("
                SELECT 
                    v.id_vendedor,
                    COUNT(CASE 
                        WHEN dt_v.estado_asiento = 'VENDIDO' 
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_vendidos,
                    COUNT(CASE 
                        WHEN (dt_v.estado_asiento IN ('ANULADO', 'CANCELADO') 
                        OR v.estado IN ('ANULADO', 'CANCELADO'))
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_anulados,
                    COUNT(CASE 
                        WHEN (dt_v.estado_asiento IN ('POSPUESTO') 
                        OR v.estado IN ('POSPUESTO'))
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_pospuestos,
                    COUNT(CASE 
                        WHEN dt_v.estado_asiento IN ('RESERVADO', 'RESERVADO_WEB', 'VENTA_WEB')
                        AND v.estado IN ('RESERVADO', 'PAGADO')
                        AND dt_v.id_tp_servicio = 1 
                        THEN 1 
                    END) AS total_reservados,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 2 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_nota_venta,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 3 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_boleta,
                    COUNT(CASE 
                        WHEN v.id_tp_comprobante = 1 
                        AND dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1
                        THEN 1 
                    END) AS total_factura,
                    COALESCE(SUM(CASE 
                        WHEN dt_v.estado_asiento = 'VENDIDO'
                        AND v.estado = 'PAGADO'
                        AND dt_v.id_tp_servicio = 1 
                        THEN dt_v.precio 
                        ELSE 0 
                    END), 0) AS monto_total
                FROM venta v
                INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                WHERE dt_v.id_programacion = :id_programacion3
                AND dt_v.id_tp_servicio = 1
                AND NOT EXISTS (
                SELECT 1
                FROM pasajes_pospuestos pp
                WHERE pp.id_venta_original = v.id_venta
                AND pp.estado = 'usado'
            )
                AND v.id_vendedor = :id_vendedor
                GROUP BY v.id_vendedor
            ");
                $stats_query->bindParam(":id_vendedor", $id_usuario_actual, PDO::PARAM_INT);
            }

            $stats_query->bindParam(":id_programacion3", $id_programacion, PDO::PARAM_INT);
            $stats_query->execute();
            $stats = $stats_query->fetchAll(PDO::FETCH_ASSOC);

            // Indexar las estadísticas por id_vendedor
            $stats_by_vendedor = [];
            foreach ($stats as $stat) {
                $stats_by_vendedor[$stat['id_vendedor']] = $stat;
            }

            // PASO 6: Combinar todos los vendedores con sus estadísticas
            $resultado = [];
            foreach ($todos_vendedores as $vendedor) {
                $id = $vendedor['id_usuario'];

                if (isset($stats_by_vendedor[$id])) {
                    // Vendedor tiene ventas
                    $stats_vendedor = $stats_by_vendedor[$id];
                    $resultado[] = [
                        'id_usuario' => $id,
                        'nombres' => $vendedor['nombres'],
                        'apellidos' => $vendedor['apellidos'],
                        'num_docu' => $vendedor['num_docu'],
                        'tipo_usuario' => $vendedor['tipo_usuario'],
                        'id_tp_usuario' => $vendedor['id_tp_usuario'],
                        'terminal_nombre' => $vendedor['terminal_nombre'] ?? 'S/T',
                        'total_vendidos' => (int) $stats_vendedor['total_vendidos'],
                        'total_anulados' => (int) $stats_vendedor['total_anulados'],
                        'total_pospuestos' => (int) $stats_vendedor['total_pospuestos'],
                        'total_reservados' => (int) $stats_vendedor['total_reservados'],
                        'total_nota_venta' => (int) $stats_vendedor['total_nota_venta'],
                        'total_boleta' => (int) $stats_vendedor['total_boleta'],
                        'total_factura' => (int) $stats_vendedor['total_factura'],
                        'monto_total' => (float) $stats_vendedor['monto_total'],
                        'link' => URL . 'pasaje/impresion/liquidacion_usuarioticket/' . $id_programacion . '/' . $id
                    ];
                } else {
                    // Vendedor sin ventas
                    $resultado[] = [
                        'id_usuario' => $id,
                        'nombres' => $vendedor['nombres'],
                        'apellidos' => $vendedor['apellidos'],
                        'num_docu' => $vendedor['num_docu'],
                        'tipo_usuario' => $vendedor['tipo_usuario'],
                        'id_tp_usuario' => $vendedor['id_tp_usuario'],
                        'terminal_nombre' => $vendedor['terminal_nombre'] ?? 'S/T',
                        'total_vendidos' => 0,
                        'total_anulados' => 0,
                        'total_pospuestos' => 0,
                        'total_reservados' => 0,
                        'total_nota_venta' => 0,
                        'total_boleta' => 0,
                        'total_factura' => 0,
                        'monto_total' => 0,
                        'link' => URL . 'pasaje/impresion/liquidacion_usuarioticket/' . $id_programacion . '/' . $id
                    ];
                }
            }

            // PASO 7: Ordenar: primero los que tienen ventas (por monto descendente)
            usort($resultado, function ($a, $b) {
                if ($a['monto_total'] > 0 && $b['monto_total'] == 0)
                    return -1;
                if ($a['monto_total'] == 0 && $b['monto_total'] > 0)
                    return 1;
                if ($a['monto_total'] > 0 && $b['monto_total'] > 0) {
                    if ($a['monto_total'] != $b['monto_total']) {
                        return $b['monto_total'] <=> $a['monto_total'];
                    }
                }
                return strcmp($a['nombres'], $b['nombres']);
            });

            return [
                'success' => true,
                'data' => $resultado,
                'es_admin_soporte' => $es_admin_soporte
            ];
        } catch (PDOException $e) {
            error_log("Error en get_resumenVendedores: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener resumen de vendedores: ' . $e->getMessage()
            ];
        }
    }

    public function get_resumenDestinos($id_programacion)
    {
        try {
            // Consulta para obtener el destino principal de la programación y todos los destinos de rutas
            $query = $this->db->connect()->prepare("
            -- 1. Obtener todos los destinos posibles (principal + rutas)
            WITH destinos_programacion AS (
                -- Destino principal de la programación
                SELECT 
                    p.id_terminal_destino AS id_terminal,
                    t.nombre AS nombre_terminal,
                    'PRINCIPAL' AS tipo_destino,
                    NULL AS id_ruta_destino  -- Agregamos para poder diferenciar
                FROM programacion p
                INNER JOIN terminal t ON t.id_terminal = p.id_terminal_destino
                WHERE p.id_programacion = :id_programacion1
                
                UNION
                
                -- Destinos de rutas (rutas_destino)
                SELECT 
                    rd.id_terminal AS id_terminal,
                    t.nombre AS nombre_terminal,
                    'RUTA' AS tipo_destino,
                    rd.id_ruta_destino AS id_ruta_destino
                FROM rutas_destino rd
                INNER JOIN terminal t ON t.id_terminal = rd.id_terminal
                WHERE rd.id_programacion = :id_programacion2
            )
            
            -- 2. Contar los pasajes vendidos para cada destino
            SELECT 
                dp.id_terminal,
                UPPER(dp.nombre_terminal) AS nombre_destino,
                dp.tipo_destino,
                COUNT(DISTINCT v.id_venta) AS total_vendidos
            FROM destinos_programacion dp
            LEFT JOIN dt_venta dt_v ON dt_v.id_programacion = :id_programacion3
                AND dt_v.id_tp_servicio = 1
            LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                AND v.estado NOT IN ('ANULADO', 'CANCELADO', 'RESERVADO', 'VENTA_WEB')
            LEFT JOIN rutas_destino rd ON rd.id_ruta_destino = dt_v.id_destino
            WHERE (
                -- Para destinos principales: contar solo ventas que NO tienen destino de ruta
                (dp.tipo_destino = 'PRINCIPAL' AND dt_v.id_destino IS NULL)
                OR
                -- Para rutas: contar ventas que tienen ese destino específico
                (dp.tipo_destino = 'RUTA' AND dt_v.id_destino = dp.id_ruta_destino)
            )
            GROUP BY dp.id_terminal, dp.nombre_terminal, dp.tipo_destino, dp.id_ruta_destino
            ORDER BY 
                dp.tipo_destino DESC, -- Primero el destino principal
                total_vendidos DESC, 
                dp.nombre_terminal ASC
        ");

            // Vincular los parámetros
            $query->bindParam(":id_programacion1", $id_programacion, PDO::PARAM_INT);
            $query->bindParam(":id_programacion2", $id_programacion, PDO::PARAM_INT);
            $query->bindParam(":id_programacion3", $id_programacion, PDO::PARAM_INT);

            $query->execute();
            $destinos = $query->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay resultados, mostrar todos los destinos con 0 ventas
            if (empty($destinos)) {
                // Destino principal
                $query_default = $this->db->connect()->prepare("
                SELECT 
                    p.id_terminal_destino AS id_terminal,
                    UPPER(t.nombre) AS nombre_destino,
                    'PRINCIPAL' AS tipo_destino,
                    0 AS total_vendidos
                FROM programacion p
                INNER JOIN terminal t ON t.id_terminal = p.id_terminal_destino
                WHERE p.id_programacion = :id_programacion
            ");

                $query_default->bindParam(":id_programacion", $id_programacion, PDO::PARAM_INT);
                $query_default->execute();
                $destinos_principales = $query_default->fetchAll(PDO::FETCH_ASSOC);

                // Rutas
                $query_rutas = $this->db->connect()->prepare("
                SELECT 
                    rd.id_terminal AS id_terminal,
                    UPPER(t.nombre) AS nombre_destino,
                    'RUTA' AS tipo_destino,
                    0 AS total_vendidos
                FROM rutas_destino rd
                INNER JOIN terminal t ON t.id_terminal = rd.id_terminal
                WHERE rd.id_programacion = :id_programacion
                ORDER BY rd.orden ASC
            ");

                $query_rutas->bindParam(":id_programacion", $id_programacion, PDO::PARAM_INT);
                $query_rutas->execute();
                $rutas = $query_rutas->fetchAll(PDO::FETCH_ASSOC);

                // Combinar resultados
                $destinos = array_merge($destinos_principales, $rutas);
            }

            // Calcular el total general de pasajes vendidos
            $total_general = 0;
            foreach ($destinos as &$destino) {
                $total_general += intval($destino['total_vendidos']);
            }

            return [
                'success' => true,
                'data' => $destinos,
                'total_general' => $total_general
            ];
        } catch (PDOException $e) {
            error_log("Error en get_resumenDestinos: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener resumen de destinos: ' . $e->getMessage()
            ];
        }
    }

    public function marcarAbordaje($id_dt_venta, $id_venta)
    {
        try {
            // Verificar que la venta no esté anulada
            $conn = $this->db->connect();
            $query = $conn->prepare("
            SELECT estado FROM venta WHERE id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $estado = $query->fetchColumn();

            if ($estado === 'ANULADO') {
                return ['success' => false, 'message' => 'No se puede marcar abordaje de una venta anulada'];
            }

            // Actualizar el timestamp de abordaje
            $query = $conn->prepare("
            UPDATE dt_venta 
            SET abordaje = NOW() 
            WHERE id_dt_venta = :id_dt_venta
            ");
            $query->bindParam(':id_dt_venta', $id_dt_venta);
            $query->execute();

            if ($query->rowCount() > 0) {
                // Notificar por WebSocket para actualizar en tiempo real
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_pasaje", [
                    "tipo" => "abordaje_marcado",
                    "id_programacion" => $id_programacion ?? 0,
                    "asientos" => [], // sin asientos = procesarEventoAsientos hará fallback si lo necesita
                ]);

                return [
                    'success' => true,
                    'message' => 'Abordaje registrado correctamente',
                    'abordaje' => date('Y-m-d H:i:s')
                ];
            } else {
                return ['success' => false, 'message' => 'No se encontró el registro'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function validarEdadCliente($id_cliente)
    {
        if (empty($id_cliente)) {
            return ['success' => false, 'message' => 'No se ha seleccionado un cliente'];
        }

        $query = $this->db->connect()->prepare("
            SELECT 
                id_usuario,
                fecha_nacimiento,
                nombres,
                apellidos
            FROM usuario 
            WHERE id_usuario = :id_usuario
        ");
        $query->bindParam(':id_usuario', $id_cliente);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }

        // Verificar si la fecha de nacimiento es válida
        $fecha_nacimiento = $cliente['fecha_nacimiento'];
        $fecha_invalida = false;

        // Casos de fecha inválida
        if (
            empty($fecha_nacimiento) ||
            $fecha_nacimiento === '0000-00-00' ||
            $fecha_nacimiento === '0000-00-00 00:00:00' ||
            $fecha_nacimiento === null ||
            $fecha_nacimiento === 'NULL'
        ) {
            $fecha_invalida = true;
        } else {
            // Verificar que sea una fecha real (no 0000-00-00)
            try {
                $date = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
                if (!$date || $date->format('Y-m-d') !== $fecha_nacimiento) {
                    $fecha_invalida = true;
                }
            } catch (Exception $e) {
                $fecha_invalida = true;
            }
        }

        if ($fecha_invalida) {
            return [
                'success' => false,
                'message' => 'El cliente no tiene registrada su fecha de nacimiento. Por favor, complete sus datos.',
                'cliente_id' => $id_cliente,
                'cliente_nombre' => trim($cliente['nombres'] . ' ' . $cliente['apellidos'])
            ];
        }

        return ['success' => true, 'message' => 'Cliente válido'];
    }
    public function actualizarFechaNacimientoCliente($id_cliente, $anios)
    {
        try {
            // Validar que los años sean válidos
            $anios = intval($anios);
            if ($anios < 1 || $anios > 120) {
                return ['success' => false, 'message' => 'Edad inválida (1-120 años)'];
            }

            $anio_nacimiento = date('Y') - $anios;
            $fecha_nacimiento = $anio_nacimiento . '-01-01';

            $query = $this->db->connect()->prepare("
            UPDATE usuario 
            SET fecha_nacimiento = :fecha_nacimiento 
            WHERE id_usuario = :id_usuario
        ");
            $query->bindParam(':fecha_nacimiento', $fecha_nacimiento);
            $query->bindParam(':id_usuario', $id_cliente);

            if ($query->execute()) {
                return [
                    'success' => true,
                    'message' => 'Edad registrada correctamente',
                    'fecha_nacimiento' => $fecha_nacimiento
                ];
            } else {
                return ['success' => false, 'message' => 'No se pudo actualizar la edad'];
            }
        } catch (PDOException $e) {
            error_log("Error actualizando edad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la edad'];
        }
    }

    public function aplicar_venta_pospuesta($data)
    {
        $conn = null;
        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $data['precio_original'] = floatval($data['precio_original']);
            $data['pospuesto_saldo'] = floatval($data['pospuesto_saldo']);
            $link_nota = [];

            if ($data['precio_original'] == $data['pospuesto_saldo']) {

                $actualizar = $this->actualizar_venta_pospuesta($data, $conn);
                if (!$actualizar['success']) {
                    throw new Exception($actualizar['message']);
                }
                $saldo = $this->actualizar_saldo_pospuesto($data, $conn);
                if (isset($saldo['success']) && !$saldo['success']) {
                    throw new Exception($saldo['message'] ?? 'Error al actualizar saldo pospuesto');
                }
            } elseif ($data['precio_original'] > $data['pospuesto_saldo']) {

                $actualizar = $this->actualizar_venta_pospuesta($data, $conn);
                if (!$actualizar['success']) {
                    throw new Exception($actualizar['message']);
                }

                $nota_debito = $this->generar_nota_debito_pospuesto($data, $conn);
                if (!$nota_debito['success']) {
                    throw new Exception($nota_debito['message']);
                }

                $saldo = $this->actualizar_saldo_pospuesto($data, $conn);
                if (isset($saldo['success']) && !$saldo['success']) {
                    throw new Exception($saldo['message'] ?? 'Error al actualizar saldo pospuesto');
                }
                $link_nota = [
                    'Nota de Débito' => URL . 'notas/impresion/nota_credito/' . $nota_debito['id_nota']
                ];
            } elseif ($data['precio_original'] < $data['pospuesto_saldo']) {

                $actualizar = $this->actualizar_venta_pospuesta($data, $conn);
                if (!$actualizar['success']) {
                    throw new Exception($actualizar['message']);
                }

                $data['saldo_restante'] = $data['pospuesto_saldo'] - $data['precio_original'];
                date_default_timezone_set('America/Lima');
                $fecha_uso = date('Y-m-d H:i:s');

                if (isset($data['devolucion_restante']) && $data['devolucion_restante'] == '1') {

                    $nota_credito = $this->generar_nota_credito_pospuesto($data, $conn);
                    if (!$nota_credito['success']) {
                        throw new Exception($nota_credito['message']);
                    }

                    $query = $conn->prepare("
                    UPDATE pasajes_pospuestos
                    SET estado = 'usado',
                        fecha_uso = :fecha_uso,
                        saldo_a_favor = '0.00'
                    WHERE id = :id
                    ");
                    $query->bindParam(':id', $data['pospuesto_id']);
                    $query->bindParam(':fecha_uso', $fecha_uso);
                    if (!$query->execute()) {
                        throw new Exception('Error al marcar pospuesto como usado');
                    }
                    $link_nota = [
                        'Nota de Crédito' => URL . 'notas/impresion/nota_credito/' . $nota_credito['id_nota']
                    ];
                } else {

                    $query = $conn->prepare("
                    UPDATE pasajes_pospuestos
                    SET estado = 'parcial',
                        fecha_uso = :fecha_uso,
                        saldo_a_favor = :saldo_a_favor
                    WHERE id = :id
                    ");
                    $query->bindParam(':id', $data['pospuesto_id']);
                    $query->bindParam(':fecha_uso', $fecha_uso);
                    $query->bindParam(':saldo_a_favor', $data['saldo_restante']);
                    if (!$query->execute()) {
                        throw new Exception('Error al actualizar pospuesto a parcial');
                    }
                }
            }

            $conn->commit();
            return [
                'success' => true,
                'message' => 'Venta pospuesta aplicada correctamente',
                'links' => array_merge(
                    ['comprobante' => URL . 'pasaje/impresion/comprobante/' . $data['pospuesto_id_venta']],
                    $link_nota
                )
            ];
        } catch (Exception $e) {
            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    public function actualizar_venta_pospuesta($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $estado_prog_obj = 'VENDIDO';
            $fecha_emision = date('Y-m-d');
            $client = $data['pasajero_id'] ?? $data['cliente_id'];

            $query = $conn->prepare("
            SELECT id_terminal_origen, id_terminal_destino
            FROM programacion
            WHERE id_programacion = :id_programacion
           ");
            $query->bindParam(':id_programacion', $data['id_programacion']);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("No se encontró la programación ID: {$data['id_programacion']}");
            }

            $id_ruta = null;
            $tp_origen = 0;

            if ($data['origen'] != $result['id_terminal_origen']) {
                $query = $conn->prepare("
                SELECT id_ruta
                FROM ruta
                WHERE id_programacion = :id_programacion
                  AND id_terminal = :id_terminal
                ");
                $query->bindParam(':id_programacion', $data['id_programacion']);
                $query->bindParam(':id_terminal', $data['origen']);
                $query->execute();
                $routeResult = $query->fetch(PDO::FETCH_ASSOC);

                if ($routeResult) {
                    $id_ruta = $routeResult['id_ruta'];
                    $tp_origen = 1;
                }
            }

            $id_destino = null;
            $tp_destino = 0;

            if (!empty($data['destino_pasajero']) && $data['destino_pasajero'] !== 'null') {
                $id_destino = $data['destino_pasajero'];
                $tp_destino = 1;
            }

            $query = $conn->prepare("
            UPDATE programacion_obj SET
                estado         = :estado,
                id_venta       = :id_venta,
                estado_proceso = 0,
                fecha_registro = :fecha_registro
            WHERE id_obj_vehiculo = :id_obj_vehiculo
              AND id_programacion = :id_programacion
            ");
            $query->bindParam(':id_obj_vehiculo', $data['id_asiento_selected']);
            $query->bindParam(':id_programacion', $data['id_programacion']);
            $query->bindParam(':estado', $estado_prog_obj);
            $query->bindParam(':id_venta', $data['pospuesto_id_venta']);
            $query->bindParam(':fecha_registro', $fecha_emision);
            if (!$query->execute()) {
                throw new Exception('Error al actualizar programacion_obj');
            }

            $cNino = ($data['c_nino'] == 1) ? $data['c_nino'] : 0;
            $idNino = ($data['c_nino'] == 1) ? $data['nino_id'] : null;
            $motivo = ($data['c_nino'] == 1) ? $data['desc_motivo'] : null;

            $query = $conn->prepare("
            UPDATE dt_venta SET
                estado_asiento   = 'VENDIDO',
                id_programacion  = :id_programacion,
                piso             = :piso,
                num_asiento      = :num_asiento,
                id_pasajero      = :id_pasajero,
                tp_origen        = :tp_origen,
                tp_destino       = :tp_destino,
                id_ruta          = :id_ruta,
                id_destino       = :id_destino,
                id_sesionpersonal = :id_sesionpersonal,
                c_nino           = :c_nino,
                id_nino          = :id_nino,
                motivo           = :motivo
            WHERE id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $data['pospuesto_id_venta']);
            $query->bindParam(':id_programacion', $data['id_programacion']);
            $query->bindParam(':piso', $data['num_piso']);
            $query->bindParam(':num_asiento', $data['num_asiento']);
            $query->bindParam(':id_pasajero', $client);
            $query->bindParam(':tp_origen', $tp_origen);
            $query->bindParam(':tp_destino', $tp_destino);
            $query->bindParam(':id_ruta', $id_ruta, PDO::PARAM_INT);
            $query->bindParam(':id_destino', $id_destino, PDO::PARAM_INT);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':c_nino', $cNino, PDO::PARAM_INT);
            $query->bindParam(':id_nino', $idNino, PDO::PARAM_INT);
            $query->bindParam(':motivo', $motivo);
            if (!$query->execute()) {
                throw new Exception('Error al actualizar dt_venta');
            }

            $query = $conn->prepare("
            UPDATE venta SET
                estado           = 'PAGADO',
                id_terminal      = :id_terminal,
                id_caja_chica    = :id_caja_chica,
                obs              = :obs,
                id_sesionpersonal = :id_sesionpersonal
            WHERE id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $data['pospuesto_id_venta']);
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->bindParam(':id_caja_chica', $data['destino']);
            $query->bindParam(':obs', $data['obs']);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            if (!$query->execute()) {
                throw new Exception('Error al actualizar venta');
            }

            return ['success' => true, 'message' => 'Se ha actualizado la venta pospuesta'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    public function generar_nota_debito_pospuesto($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            date_default_timezone_set('America/Lima');

            $id_venta = $data['pospuesto_id_venta'];
            $tp_comp = 8;
            $emisor = $this->consult_emisor($conn);

            if (empty($emisor)) {
                throw new Exception('No se pudo obtener datos del emisor');
            }

            $query = $conn->prepare("
            SELECT id_tp_comprobante
            FROM venta
            WHERE id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $tp_comprobante_pos = $query->fetchColumn();

            if ($tp_comprobante_pos === false) {
                throw new Exception("No se encontró comprobante para la venta ID: {$id_venta}");
            }

            $where_serie = ($tp_comprobante_pos == 1) ? "AND serie LIKE 'FD%'" : "AND serie LIKE 'BD%'";

            $query = $conn->prepare("
            SELECT *
            FROM serie
            WHERE id_tp_comprobante = 8
              $where_serie
              AND id_terminal = $this->id_terminal_sesion
            ");
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            if (!$reply) {
                throw new Exception('No se encontró serie configurada para nota de débito en esta terminal');
            }

            $correlativo = $this->get_correlativo_notas($reply['serie']);
            if (!$correlativo) {
                throw new Exception('No se pudo obtener correlativo para la nota de débito');
            }

            $items = [];
            $fecha_actual = date('Y-m-d');
            $hora_actual = date('H:i:s');
            $descripcion = 'SERVICIO DE TRANSPORTE DE PASAJEROS';

            $query = $conn->prepare("
            SELECT
                c.id_tp_docu                          AS tipo_documento,
                c.num_docu                            AS ruc,
                CONCAT(c.nombres,' ',c.apellidos)     AS razon_social,
                c.direccion                           AS direccion,
                c.ubigeo                              AS cliente_ubigeo
            FROM venta v
            LEFT JOIN dt_venta  dt_v  ON dt_v.id_venta   = v.id_venta
            LEFT JOIN usuario   c     ON c.id_usuario     = v.id_cliente
            LEFT JOIN ubigeo    ub_c  ON ub_c.cod_ubigeo  = c.ubigeo
            LEFT JOIN tp_docu   tp_d_c ON tp_d_c.id_tp_docu = c.id_tp_docu
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                throw new Exception("No se encontraron datos del cliente para la venta ID: {$id_venta}");
            }
            $cliente['pais'] = 'PE';

            $query = $conn->prepare("
            SELECT
                v.serie                       AS serie_ref,
                v.correlativo                 AS correlativo_ref,
                f_p.descripcion               AS forma_pago,
                tp_m.codigo                   AS moneda,
                tp_c.codigo                   AS tipo_comprobante_ref_id
            FROM venta v
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN forma_pago     f_p  ON f_p.id_forma_pago      = v.id_forma_pago
            LEFT JOIN medio_pago     m_p  ON m_p.id_medio_pago      = v.id_medio_pago
            LEFT JOIN dt_venta       dt_v ON dt_v.id_venta          = v.id_venta
            LEFT JOIN programacion   p    ON p.id_programacion       = dt_v.id_programacion
            LEFT JOIN vehiculo       vh   ON vh.id_vehiculo          = p.id_vehiculo
            LEFT JOIN tp_moneda      tp_m ON tp_m.id_tp_moneda       = v.id_tp_moneda
            LEFT JOIN usuario        vd   ON vd.id_usuario           = v.id_vendedor
            LEFT JOIN terminal       t_o  ON t_o.id_terminal         = p.id_terminal_origen
            LEFT JOIN terminal       t_d  ON t_d.id_terminal         = p.id_terminal_destino
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("No se encontró cabecera de venta para ID: {$id_venta}");
            }

            $items[0] = [
                'item' => 1,
                'porcentaje_igv' => 0.00,
                'codigo' => 0,
                'igv' => 0.00,
                'precio_lista' => $data['precio_venta'],
                'valor_total' => $data['precio_venta'],
                'valor_unitario' => $data['precio_venta'],
                'total' => $data['precio_venta'],
                'icbper' => 0.00,
                'factor_icbper' => 0.00,
                'unidad' => 'ZZ',
                'nombre' => $descripcion,
                'total_antes_impuestos' => $data['precio_venta'],
                'tipo_precio' => '01',
                'codigos' => ['E', '20', '9997', 'EXO', 'VAT'],
                'cantidad' => 1,
            ];

            $cabecera = array_merge($cabecera, [
                'tipo_comprobante' => '08',
                'igv' => 0.00,
                'total_op_gravadas' => 0.00,
                'total_op_exoneradas' => $data['precio_venta'],
                'total_op_inafectas' => 0.00,
                'descuento' => 0.00,
                'total_a_pagar' => $data['precio_venta'],
                'serie' => $reply['serie'],
                'correlativo' => $correlativo,
                'hora_emision' => $hora_actual,
                'fecha_emision' => $fecha_actual,
                'codmotivo' => '02',
                'descripcion' => 'Aumento de valor por servicio de transporte de pasajeros',
                'anexo_sucursal' => '0000',
                'total_texto' => numtoletras($data['precio_venta']),
            ]);

            $json = [
                'ose' => $emisor['ose'],
                'emisor' => $emisor,
                'cliente' => $cliente,
                'cabecera' => $cabecera,
                'items' => $items,
            ];

            $rpta_sunat = $this->enviar_json_a_api(json_encode($json));
            $resp = json_decode($rpta_sunat, true);

            if (!is_array($resp) || empty($resp[0])) {
                throw new Exception('Respuesta inválida o vacía de la API SUNAT');
            }

            $estado_sunat = $resp[0]['estado'] ?? 0;
            $mensaje_sunat = $resp[0]['mensaje_sunat'] ?? '';

            switch ($estado_sunat) {
                case '1':
                    $insercion_nota = $this->add_nota($json, $id_venta, $resp, $tp_comp, $conn);
                    if (!$insercion_nota['success']) {
                        throw new Exception($insercion_nota['message']);
                    }
                    return [
                        'success' => true,
                        'message' => [
                            'estado_sunat' => $estado_sunat,
                            'message_sunat' => $mensaje_sunat,
                        ],
                        'id_nota' => $insercion_nota['id_nota']
                    ];

                case '2':
                    return [
                        'success' => true,
                        'message' => [
                            'estado_sunat' => $estado_sunat,
                            'message_sunat' => $mensaje_sunat,
                        ],
                    ];

                default:
                    throw new Exception("SUNAT retornó estado desconocido ({$estado_sunat}): {$mensaje_sunat}");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function generar_nota_credito_pospuesto($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            date_default_timezone_set('America/Lima');

            $id_venta = $data['pospuesto_id_venta'];
            $tp_comp = 7;
            $emisor = $this->consult_emisor($conn);

            if (empty($emisor)) {
                throw new Exception('No se pudo obtener datos del emisor');
            }

            $query = $conn->prepare("
            SELECT id_tp_comprobante
            FROM venta
            WHERE id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $tp_comprobante_pos = $query->fetchColumn();

            if ($tp_comprobante_pos === false) {
                throw new Exception("No se encontró comprobante para la venta ID: {$id_venta}");
            }

            $where_serie = ($tp_comprobante_pos == 1) ? "AND serie LIKE 'FC%'" : "AND serie LIKE 'BC%'";

            $query = $conn->prepare("
            SELECT *
            FROM serie
            WHERE id_tp_comprobante = 7
              $where_serie
              AND id_terminal = $this->id_terminal_sesion
            ");
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            if (!$reply) {
                throw new Exception('No hay series disponibles para generar la nota de crédito, revise la configuración de series para la terminal activa');
            }

            $correlativo = $this->get_correlativo_notas($reply['serie']);

            if (!$correlativo) {
                throw new Exception('No se pudo obtener correlativo para la nota de crédito');
            }

            $items = [];
            $fecha_actual = date('Y-m-d');
            $hora_actual = date('H:i:s');
            $descripcion = 'SERVICIO DE TRANSPORTE DE PASAJEROS';

            $query = $conn->prepare("
            SELECT
                c.id_tp_docu                        AS tipo_documento,
                c.num_docu                          AS ruc,
                CONCAT(c.nombres,' ',c.apellidos)   AS razon_social,
                c.direccion                         AS direccion,
                c.ubigeo                            AS cliente_ubigeo
            FROM venta v
            LEFT JOIN dt_venta  dt_v   ON dt_v.id_venta      = v.id_venta
            LEFT JOIN usuario   c      ON c.id_usuario        = v.id_cliente
            LEFT JOIN ubigeo    ub_c   ON ub_c.cod_ubigeo     = c.ubigeo
            LEFT JOIN tp_docu   tp_d_c ON tp_d_c.id_tp_docu  = c.id_tp_docu
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                throw new Exception("No se encontraron datos del cliente para la venta ID: {$id_venta}");
            }
            $cliente['pais'] = 'PE';

            $query = $conn->prepare("
            SELECT
                v.serie                     AS serie_ref,
                v.correlativo               AS correlativo_ref,
                f_p.descripcion             AS forma_pago,
                tp_m.codigo                 AS moneda,
                tp_c.codigo                 AS tipo_comprobante_ref_id
            FROM venta v
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN forma_pago     f_p  ON f_p.id_forma_pago      = v.id_forma_pago
            LEFT JOIN medio_pago     m_p  ON m_p.id_medio_pago      = v.id_medio_pago
            LEFT JOIN dt_venta       dt_v ON dt_v.id_venta          = v.id_venta
            LEFT JOIN programacion   p    ON p.id_programacion       = dt_v.id_programacion
            LEFT JOIN vehiculo       vh   ON vh.id_vehiculo          = p.id_vehiculo
            LEFT JOIN tp_moneda      tp_m ON tp_m.id_tp_moneda       = v.id_tp_moneda
            LEFT JOIN usuario        vd   ON vd.id_usuario           = v.id_vendedor
            LEFT JOIN terminal       t_o  ON t_o.id_terminal         = p.id_terminal_origen
            LEFT JOIN terminal       t_d  ON t_d.id_terminal         = p.id_terminal_destino
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("No se encontró cabecera de venta para ID: {$id_venta}");
            }

            $items[0] = [
                'item' => 1,
                'porcentaje_igv' => 0.00,
                'codigo' => 0,
                'igv' => 0.00,
                'precio_lista' => $data['saldo_restante'],
                'valor_total' => $data['saldo_restante'],
                'valor_unitario' => $data['saldo_restante'],
                'total' => $data['saldo_restante'],
                'icbper' => 0.00,
                'factor_icbper' => 0.00,
                'unidad' => 'ZZ',
                'nombre' => $descripcion,
                'total_antes_impuestos' => $data['saldo_restante'],
                'tipo_precio' => '01',
                'codigos' => ['E', '20', '9997', 'EXO', 'VAT'],
                'cantidad' => 1,
            ];

            $cabecera = array_merge($cabecera, [
                'tipo_comprobante' => '07',
                'igv' => 0.00,
                'total_op_gravadas' => 0.00,
                'total_op_exoneradas' => $data['saldo_restante'],
                'total_op_inafectas' => 0.00,
                'descuento' => 0.00,
                'total_a_pagar' => $data['saldo_restante'],
                'serie' => $reply['serie'],
                'correlativo' => $correlativo,
                'hora_emision' => $hora_actual,
                'fecha_emision' => $fecha_actual,
                'codmotivo' => '09',
                'descripcion' => 'Disminucion en el valor por diferencia de tarifa - Devolucion de saldo',
                'anexo_sucursal' => '0000',
                'total_texto' => numtoletras($data['saldo_restante']),
            ]);

            $json = [
                'ose' => $emisor['ose'],
                'emisor' => $emisor,
                'cliente' => $cliente,
                'cabecera' => $cabecera,
                'items' => $items,
            ];

            $rpta_sunat = $this->enviar_json_a_api(json_encode($json));
            $resp = json_decode($rpta_sunat, true);

            if (!is_array($resp) || empty($resp[0])) {
                throw new Exception('Respuesta inválida o vacía de la API SUNAT');
            }

            $estado_sunat = $resp[0]['estado'] ?? 0;
            $mensaje_sunat = $resp[0]['mensaje_sunat'] ?? '';

            switch ($estado_sunat) {
                case '1':
                    $insercion_nota = $this->add_nota($json, $id_venta, $resp, $tp_comp, $conn);
                    if (!$insercion_nota['success']) {
                        throw new Exception($insercion_nota['message']);
                    }
                    return [
                        'success' => true,
                        'message' => [
                            'estado_sunat' => $estado_sunat,
                            'message_sunat' => $mensaje_sunat,
                        ],
                        'id_nota' => $insercion_nota['id_nota']
                    ];

                case '2':
                    return [
                        'success' => true,
                        'message' => [
                            'estado_sunat' => $estado_sunat,
                            'message_sunat' => $mensaje_sunat,
                        ],
                    ];

                default:
                    throw new Exception("SUNAT retornó estado desconocido ({$estado_sunat}): {$mensaje_sunat}");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function actualizar_saldo_pospuesto($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            date_default_timezone_set('America/Lima');
            $fecha_uso = date('Y-m-d H:i:s');

            $query = $conn->prepare("
            UPDATE pasajes_pospuestos
            SET estado        = 'usado',
                fecha_uso     = :fecha_uso,
                saldo_a_favor = '0.00'
            WHERE id = :id
            ");
            $query->bindParam(':id', $data['pospuesto_id']);
            $query->bindParam(':fecha_uso', $fecha_uso);

            if (!$query->execute()) {
                throw new Exception('Error al ejecutar la actualización del saldo pospuesto');
            }

            if ($query->rowCount() === 0) {
                throw new Exception("No se encontró el registro pospuesto con ID: {$data['pospuesto_id']}");
            }

            return ['success' => true, 'message' => 'Saldo pospuesto actualizado correctamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function buscar_pasajeros()
    {
        try {

            $conn = $this->db->connect();

            $texto = $_POST['texto'] ?? '';
            $texto = trim($texto);

            if ($texto === '') {
                return [
                    'success' => true,
                    'data' => [],
                    'hasMore' => false
                ];
            }

            // Separamos el texto en palabras (por espacios) para poder
            // buscar "rusbel palacios matos" sin importar en qué orden
            // ni en qué columna (nombres/apellidos) caiga cada palabra.
            $palabras = preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY);

            $condiciones = [];
            $params = [];

            foreach ($palabras as $i => $palabra) {

                $keyNombre = ":p{$i}";
                $keyDocu = ":d{$i}";

                $condiciones[] = "(CONCAT(u.nombres, ' ', u.apellidos) LIKE {$keyNombre} OR u.num_docu LIKE {$keyDocu})";

                $params[$keyNombre] = "%{$palabra}%";
                $params[$keyDocu] = "%{$palabra}%";
            }

            $where = implode(' AND ', $condiciones);

            $params[':texto_completo1'] = $texto;
            $params[':texto_completo2'] = $texto;
            $params[':texto_completo3'] = $texto;

            $query = $conn->prepare("
        SELECT 
            MIN(u.id_usuario) AS id_usuario,
            CONCAT(u.num_docu, ' - ',u.nombres, ' ', u.apellidos) AS nombres_cliente,
            u.num_docu,
            MAX(u.celular) AS celular
        FROM usuario u
        WHERE {$where}
        GROUP BY u.num_docu, u.nombres, u.apellidos
        ORDER BY 
            (u.num_docu = :texto_completo1) DESC,
            (u.num_docu LIKE CONCAT(:texto_completo2, '%')) DESC,
            ABS(LENGTH(u.num_docu) - LENGTH(:texto_completo3)) ASC,
            u.num_docu ASC
        LIMIT 10
        ");

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();

            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $data,
                'hasMore' => false
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    public function revisar_pasajero($data)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
                SELECT 
                num_asiento,
                id_venta
                FROM dt_venta
                WHERE id_programacion = :id_programacion
                AND id_pasajero = :id_pasajero
            ");

        $query->bindParam(':id_programacion', $data['id_programacion']);
        $query->bindParam(':id_pasajero', $data['id_pasajero']);
        $query->execute();
        $existe = $query->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            return ['success' => true, 'message' => 'El pasajero ya tiene un asiento comprado en esta programacion el asiento ' . $existe['num_asiento']];
        } else {
            return ['success' => false, 'message' => 'Todo bien, continue'];
        }
    }

    public function buscar_nacionalidad($q)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT 
            cod_pais, 
            nombre_pais, 
            abrev,
            gentilicio
        FROM paises
        WHERE nombre_pais LIKE ?
        LIMIT 5
        ");

        $query->execute(["%$q%"]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
