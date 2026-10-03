<?php
require_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/../libs/Database.php");
require_once(dirname(__FILE__) . "/../models/pasajemodel.php");
require_once(dirname(__FILE__) . "/../models/encomiendamodel.php");
require_once(dirname(__FILE__) . "/../models/facturadormodel.php");
require_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('America/Lima');

class GestorComprobantes
{
    public $db;
    public $pasajemodel;
    public $encomiendamodel;
    public $facturadormodel;
    function __construct()
    {
        $this->db = new Database();
        $this->pasajemodel = new PasajeModel();
        $this->encomiendamodel = new EncomiendaModel();
        $this->facturadormodel = new FacturadorModel();
    }

    //consultar comprobantes no enviados a sunat y que su fecha de emisión sea menor a hoy por tres dias y que sea solo los que no se enviaron 
    // al tener los ids de los comprpobantes hacer el reenvio correspondiente teniendo un cuenta el numero de comporbantes si es mucho repartir en bloques
    // Cuando se envien hacer la actualizacion , al hacer ele envio esperar un tiempo prudente para la respuesta de la sunat
    // Al finalizar todo el proceso enviar el detalle al correo del administrador y soporte

    function get_comprobantes_sin_enviar($conn)
    {
        $comprobantes = [];
        try {
            if (empty($conn)) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
                SELECT 
                id_venta,
                id_tp_venta
                FROM venta
                WHERE estado = 0 AND fecha_emision < DATE_SUB(CURDATE(), INTERVAL 3 DAY) AND id_tp_comprobante IN (1, 3)
            ");
            $query->execute();
            $comprobantes = $query->fetchAll(PDO::FETCH_ASSOC);

            return $comprobantes;
        } catch (PDOException $e) {
            error_log('[GestorReservas][get_comprobantes_sin_enviar] Error: ' . $e->getMessage());
            return [];
        }
    }

    function reenviar_comprobantes()
    {
        try {
            $conn = $this->db->connect();
            $comprobantes = $this->get_comprobantes_sin_enviar($conn);
            if (empty($comprobantes)) {
                return;
            }

            $comprobantes_reenviados = 0;

            foreach ($comprobantes as $comprobante) {
                // hacer el tiempo de espera
                switch ($comprobante['id_tp_venta']) {
                    case '1': // Pasaje
                        $this->reenviar_comprobante_pasaje($comprobante['id_venta']);
                        sleep(5);
                        break;
                    case '2': // Encomienda
                        $this->reenviar_comprobante_encomienda($comprobante['id_venta']);

                        sleep(3);
                        break;
                    case '3': // Facturador
                        $this->reenviar_comprobante_facturador($comprobante['id_venta']);
                        sleep(4);
                        break;
                    default:
                        sleep(5);
                }
                $this->reenviar_comprobantes($comprobante['id_venta']);
                $comprobantes_reenviados++;
            }
        } catch (PDOException $e) {
            error_log('[GestorReservas][revisar_asientos] Error: ' . $e->getMessage());
        }
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

    public function reenviar_comprobante_pasaje($id_venta)
    {
        try {

            $resp = null;
            $rpta_sunat = $this->enviar_json_a_api($this->get_data_venta_pasaje($id_venta));
            $resp = json_decode($rpta_sunat, true);
            $resp[0]["id_venta"] = $id_venta;
            $this->pasajemodel->updateVentaSetForSunat($resp[0]);

            $success = true;
            if ($resp[0]['estado'] == 2 || $resp[0]['estado'] == 3) {
                $success = false;
            }

            return [
                "success" => $success
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false];
                    break;
                default:
                    return ['success' => false];
                    break;
            }
        }
    }

    public function get_data_venta_pasaje($id_venta)
    {
        $conn = $this->db->connect();
        $query =  $conn->prepare("SELECT
        e.envio_ose AS ose,
        e.num_docu AS ruc,
        e.razon_social AS razon_social,
        e.razon_social AS nombre_comercial,
        e.ubigeo AS ubigeo,
        ub.depa AS departamento,
        ub.provi AS provincia,
        ub.distri AS distrito,
        e.direccion_fiscal AS direccion,    
        e.user_sol AS usuario_sol,
        e.pass_sol AS clave_sol,
        td.id_tp_docu AS tipodoc,
        e.guia_id AS api_id,
        e.guia_clave AS api_clave,
        e.nro_cuenta_BN AS cuenta_detraccion
        FROM empresa e
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo
        LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
        WHERE e.id_empresa=1");
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);
        $emisor['pais'] = "PE";

        $query = $conn->prepare("SELECT
        CONCAT(c.nombres, ' ', c.apellidos) AS razon_social,
        c.id_tp_docu AS tipo_documento,
        tp_d_c.descripcion AS cliente_tp_docu,
        c.num_docu AS ruc,
        c.direccion,
        c.ubigeo AS cliente_ubigeo,
        c.fecha_nacimiento AS cliente_fecha_nacimiento,
        c.nacionalidad AS cliente_nacionalidad,
        
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
        LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
        v.serie,
        v.correlativo,
        v.fecha_emision,
        date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format,
        date_format(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
        f_p.descripcion AS forma_pago,
        m_p.descripcion AS medio_pago,
        v.fecha_vencimiento,
        tp_m.codigo AS moneda,
        v.op_igv,
        v.op_gravada AS total_op_gravadas,
        v.op_exonerada AS total_op_exoneradas,
        v.op_exonerada AS total_antes_impuestos,
        v.op_exonerada AS total_despues_impuestos,
        v.op_inafecta AS total_op_inafectas,
        v.total AS total_a_pagar,
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
        $cabecera['tipo_operacion'] = "0101";
        $cabecera['icbper'] = 0.00;
        $cabecera['total_impuestos'] = 0.00;
        $cabecera['descuento_global'] = 0.00;
        $cabecera['suma_descuento_item'] = 0.00;
        $cabecera['monto_credito'] = 0.00;
        $cabecera['forma_pago'] = ucwords(strtolower(ucfirst($cabecera['CABECERA']['forma_pago'])));
        $cabecera['anexo_sucursal'] = "0000";
        $cabecera["cuotas"] = [];

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

        foreach ($items as $itemData) {
            $item = [
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
                "codigos" => ["E", "20", "9997", "EXO", "VAT"]
            ];
            $items[] = $item;
        }

        return [
            "ose" => $emisor['ose'],
            "emisor" => $emisor,
            "cabecera" => $cabecera,
            "cliente" => $cliente,
            "items" => $items,
        ];
    }

    public function reenviar_comprobante_encomienda($id_venta)
    {
        try {
            $resp = null;
            $rpta_sunat = $this->enviar_json_a_api($this->encomiendamodel->conversor_encomienda($this->encomiendamodel->getDataComprobante($id_venta)));
            $resp = json_decode($rpta_sunat, true);
            $resp[0]["id_venta"] = $id_venta;
            $this->encomiendamodel->updateVentaSetForSunat($resp[0]);
            if (!isset($resp)) {
                die("Error: No se obtuvo una respuesta del api");
            }
            $success = true;
            if ($resp[0]['estado'] == 2 || $resp[0]['estado'] == 3) {
                $success = false;
            }
            return [
                "success" => $success
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false];
                    break;
                default:
                    return ['success' => false];
                    break;
            }
        }
    }
    public function reenviar_comprobante_facturador($id_venta)
    {
        try {
            $success = true;
            $data_comprobante = $this->facturadormodel->get_data_comprobante_sunat($id_venta);
            return $data_comprobante['message'];
            if (!$data_comprobante['success']) {
                throw new Exception("Error al generar el comprobante para SUNAT");
            }

            $json_envio = json_encode($data_comprobante['message']);
            $sunat = json_decode($this->enviar_json_a_api($json_envio), true);

            if (empty($sunat) || !is_array($sunat)) {
                throw new Exception("Error al generar CDR");
            }

            $resp = $sunat[0];
            $resp['id_comprobante'] = $id_venta;

            $this->generar_codQR($id_venta);
            $this->actualizar_venta_sunat($resp);

            $mensaje_sunat = $resp['mensaje_sunat'];
            $estado_sunat = $resp['estado'];

            if ($estado_sunat == 2 || $estado_sunat == 3) {
                $success = false;
            }

            return [
                "success" => $success,
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false];
                    break;
                default:
                    return ['success' => false];
                    break;
            }
        }
    }
}
