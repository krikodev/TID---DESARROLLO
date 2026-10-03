<?php

require("libs/modules/Security.php");
require("libs/modules/Upload.php");
require("libs/modules/Email.php");
//require("public/plugins/phpqrcode/qrlib.php");

class Model
{
    public $id_usuario_sesion, $id_empresa_sesion, $id_terminal_sesion, $uuid_ws_sesion, $data_usuario, $data_permisos, $data_empresa, $data_terminal, $tp_usuario, $tp_usuario_sesion, $security, $upload, $email, $db;
    function __construct()
    {
        if (Session::get(NAME_SESSION)) {
            $this->id_usuario_sesion = Session::get("data_usuario")["id_usuario"];
            $this->tp_usuario_sesion = Session::get("data_usuario")["id_tp_usuario"];
            $this->id_empresa_sesion = Session::get(("data_empresa"))["id_empresa"];
            $this->uuid_ws_sesion = Session::get(("data_empresa"))["uuid_ws"];
            $this->id_terminal_sesion = Session::get(("data_terminal"))["id_terminal"];

            $this->data_usuario = Session::get("data_usuario");
            $this->data_permisos = Session::get("data_permisos");
            $this->data_empresa = Session::get("data_empresa");
            $this->data_terminal = Session::get("data_terminal");
        }
        $this->security = new Security();
        $this->upload = new Upload();
        $this->email = new Email();
        $this->db = new Database();

        $this->tp_usuario = array(
            "ADMINISTRADOR" => 2,
            "VENDEDOR" => 3,
            "CONDUCTOR" => 4,
            "PASAJERO" => 5,
            "PROVEEDOR" => 6,
            "CLIENTE" => 7,
            "TERRAMOZA" => 8
        );
    }

    public function get_correlativo($id_serie)
    {
        $query = $this->db->connect()->prepare("SELECT COUNT(id_venta)+1 AS cantidad FROM venta WHERE id_serie=:id_serie");
        $query->bindParam(':id_serie', $id_serie);
        $query->execute();
        return $query->fetch((PDO::FETCH_ASSOC))["cantidad"];
    }

    public function get_igv()
    {
        $query = $this->db->connect()->prepare("SELECT igv FROM configuracion");
        $query->execute();
        $reply = $query->fetch((PDO::FETCH_ASSOC));
        if ($reply) {
            return $reply["igv"];
        } else {
            return null;
        }
    }

    public function generar_codQR($id_venta, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        // Obteniendo los datos para generar el código QR
        $query = $conn->prepare("SELECT
        tp_c.descripcion AS tp_comprobante,
        tp_c.codigo AS tp_comprobante_codigo,
        v.serie,
        v.correlativo,
        v.op_igv,
        v.total,
        date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision,
        c.num_docu AS cliente_num_docu,
        tp_docu_c.codigo AS cliente_tp_docu_codigo
        FROM venta v
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN tp_docu tp_docu_c ON tp_docu_c.id_tp_docu=c.id_tp_docu
        WHERE id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT
        num_docu
        FROM empresa
        WHERE id_empresa = 1
        ");
        $query->execute();
        $data_empresa = $query->fetch(PDO::FETCH_ASSOC);

        // Generando el texto del QR
        $correlativo = str_pad($data["correlativo"], 8, "0", STR_PAD_LEFT);  // Añadir ceros a la izquierda
        $serie_correlativo = $data["serie"] . " - " . $correlativo;
        $name_imgQR = 'QR_' . $data["tp_comprobante_codigo"] . '-' . $serie_correlativo . '.png';
        $text_qr = $data_empresa["num_docu"] . '|' . $data["tp_comprobante_codigo"] . '|' . $data["serie"] . '|' . $data["correlativo"] . '|' . $data["op_igv"] . '|' . $data["total"] . '|' . $data["fecha_emision"] . '|' . $data["cliente_tp_docu_codigo"] . '|' . $data["cliente_num_docu"];

        $json = [
            "ose" => 0,
            "cabecera" => [
                "tipo_comprobante" => "QR",
                "texto_qr" => $text_qr,
                "nombre_qr" => $name_imgQR
            ]
        ];
        $reply_sunat = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);

        if (!empty($reply_sunat) && is_array($reply_sunat)) {
            $resp = $reply_sunat[0];
            $file_path = $resp['file'];

            // Actualizar el campo cod_qr de la venta
            $query = $conn->prepare("UPDATE venta SET cod_qr=:cod_qr WHERE id_venta=:id_venta");
            $query->bindParam(":id_venta", $id_venta);
            $query->bindParam(":cod_qr", $file_path);
            $query->execute();
            return "QR generado y almacenado correctamente. " . $file_path;
        }
    }


    // Métodos para Anular comprobante electrónico

    public function resumen($id_venta, $id_resumen)
    {
        $query = $this->db->connect()->prepare("SELECT
        e.envio_ose AS ose,
        e.num_docu AS ruc,
        e.razon_social AS razon_social,
        e.ubigeo AS ubigeo,
        ub.depa AS departamento,
        ub.provi AS provincia,
        ub.distri AS distrito,
        e.direccion_fiscal AS direccion,    
        e.user_sol AS usuario_sol,
        e.pass_sol AS clave_sol,
        td.id_tp_docu AS tipodoc
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo
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

        if ($cabecera["boleta"] == 1) {
            $cabecera["tipo_comprobante"] = "RC";
            $cabecera["tipodoc"] = "RC";
        } else {
            $cabecera["tipo_comprobante"] = "RA";
            $cabecera["tipodoc"] = "RA";
        }


        //Datos de items resumen
        $query = $this->db->connect()->prepare("SELECT
        c.id_tp_docu AS tipodoc_ad,
        c.num_docu AS numdoc_ad,
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
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $items = $query->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        foreach ($items as &$item) {
            $item["item"] = ++$i;
            $item["condicion"] = "3";
            $item["motivo"] = "Error en Documento";
            $item["icbper"] = 0;
            $item["codigos"] = array(1000, "IGV", "VAT");
        }

        return array(
            "op" => 1,
            "ose" => $emisor['ose'],
            "emisor" => $emisor,
            "cabecera" => $cabecera,
            "items" => $items,
        );
    }

    public function Ultimo_resumen()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM resumen_baja ORDER BY id_resumen DESC LIMIT 1");
        $query->execute();
        $id_res = $query->fetch(PDO::FETCH_ASSOC);
        return $id_res["id_resumen"];
    }

    public function set_resumen($id_venta)
    {
        date_default_timezone_set('America/Lima');
        $fechaActual = date('Y-m-d');
        $serie = str_replace("-", "", $fechaActual);

        // Recuperando fecha de referencia
        $query = $this->db->connect()->prepare("SELECT date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format FROM venta v WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $fecha_ref = $query->fetch(PDO::FETCH_ASSOC);

        $fecha_referencia = $fecha_ref['fecha_emision_format'];

        // Consulta para obtener el último correlativo con dos condiciones
        $query = $this->db->connect()->prepare("SELECT correlativo FROM resumen_baja WHERE fecha_envio = :fecha_actual AND boleta = :boleta ORDER BY correlativo DESC LIMIT 1");
        $query->bindParam(':fecha_actual', $fechaActual);
        $query->bindValue(':boleta', 1);
        $query->execute();
        $ultimo_correlativo = $query->fetchColumn();

        if ($ultimo_correlativo === false) {
            $correlativo = 1;
        } else {
            $correlativo = $ultimo_correlativo + 1;
        }

        // Insertar en la tabla de resumen
        $query = $this->db->connect()->prepare("INSERT INTO resumen_baja (
                id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo)
                VALUES(
                :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo)");
        $query->bindParam(':id_comprobante', $id_venta);
        $query->bindValue(':boleta', 1);
        $query->bindValue(':factura', 0);
        $query->bindParam(':fecha_envio', $fechaActual);
        $query->bindParam(':fecha_referencia', $fecha_referencia);
        $query->bindParam(':correlativo', $correlativo);
        $query->execute();
    }

    //para facturas
    public function set_resumen_f($id_venta)
    {
        date_default_timezone_set('America/Lima');
        $fechaActual = date('Y-m-d');
        $serie = str_replace("-", "", $fechaActual);

        // Recuperando fecha de referencia
        $query = $this->db->connect()->prepare("SELECT date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format FROM venta v WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $fecha_ref = $query->fetch(PDO::FETCH_ASSOC);

        $fecha_referencia = $fecha_ref['fecha_emision_format'];

        $query = $this->db->connect()->prepare("SELECT correlativo FROM resumen_baja WHERE fecha_envio = :fecha_actual AND factura = :factura ORDER BY correlativo DESC LIMIT 1");
        $query->bindParam(':fecha_actual', $fechaActual);
        $query->bindValue(':factura', 1);
        $query->execute();
        $ultimo_correlativo = $query->fetchColumn();

        if ($ultimo_correlativo === false) {
            $correlativo = 1;
        } else {
            $correlativo = $ultimo_correlativo + 1;
        }

        // Insertar en la tabla de resumen
        $query = $this->db->connect()->prepare("INSERT INTO resumen_baja (
                id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo)
                VALUES(
                :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo)");
        $query->bindParam(':id_comprobante', $id_venta);
        $query->bindValue(':boleta', 0);
        $query->bindValue(':factura', 1);
        $query->bindParam(':fecha_envio', $fechaActual);
        $query->bindParam(':fecha_referencia', $fecha_referencia);
        $query->bindParam(':correlativo', $correlativo);
        $query->execute();
    }
    public function consult_emisor()
    {
        $query = $this->db->connect()->prepare("SELECT
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
        e.guia_clave AS api_clave
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo
        LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
        LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
        WHERE t.id_terminal=:id_terminal");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);
        $emisor['pais'] = "PE";
        return $emisor;
    }
    function guardar_ticket($data, $ultimo)
    {
        $response = array();

        if (!empty($data)) {
            $query = $this->db->connect()->prepare("UPDATE resumen_baja SET
            ticket=:ticket, nombre_xml=:nombre_xml
            WHERE id_resumen=:id_resumen    
        ");
            $query->bindParam(':ticket', $data[0]['ticket']);
            $query->bindParam(':nombre_xml', $data[0]['nombre_xml']);
            $query->bindParam(':id_resumen', $ultimo);
            $query->execute();
            $response = array("Se ha enviado correctamente el resumen de anulacion a la SUNAT");
        } else {
            $response = array("Ocurrio un error al enviar el XML a la SUNAT");
        }
        return $response;
    }

    //Actualizar tabla resumen_baja             
    public function updateResumen($data)
    {
        $envio_sunat = isset($data["estado"]) && $data["estado"] == 1 ? 1 : 0;

        $query = $this->db->connect()->prepare("UPDATE resumen_baja SET
            estado=:estado,
            mensaje_sunat=:mensaje_sunat,
            file_cdr=:file_cdr,
            file_xml=:file_xml,
            codigo_sunat=:codigo_sunat
            WHERE id_resumen=:id_resumen    
        ");
        $query->bindParam(':estado', $envio_sunat);
        $query->bindParam(':mensaje_sunat', $data["mensaje_sunat"]);
        $query->bindParam(':file_cdr', $data["cdr"]);
        $query->bindParam(':file_xml', $data["xml"]);
        $query->bindParam(':codigo_sunat', $data["codigo_sunat"]);
        $query->bindParam(':id_resumen', $data["id_resumen"]);
        $query->execute();
    }

    function UpdateComprobanteResumen($data)
    {
        $estado = 2;
        foreach ($data as $id_venta) {
            $query = $this->db->connect()->prepare("UPDATE venta SET
                envio_sunat=:estado
                WHERE id_venta=:id_venta
            ");
            $query->bindParam(':estado', $estado);
            $query->bindParam(':id_venta', $id_venta);
            $query->execute();
        }
    }
}
