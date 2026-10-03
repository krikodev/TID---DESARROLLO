<?php

require("libs/modules/Security.php");
require("libs/modules/Upload.php");
require("libs/modules/Email.php");
require("public/plugins/phpqrcode/qrlib.php");

class Model
{
    public $id_usuario_sesion, $id_empresa_sesion, $modo_sistema, $id_terminal_sesion, $color_terminal_sesion,
    $uuid_ws_sesion, $nombre_terminal_sesion, $data_usuario, $data_permisos, $data_empresa, $data_terminal,
    $tp_usuario, $tp_usuario_sesion, $security, $upload, $email, $db;
    function __construct()
    {
        if (Session::get(NAME_SESSION)) {
            $this->id_usuario_sesion = Session::get("data_usuario")["id_usuario"];
            $this->tp_usuario_sesion = Session::get("data_usuario")["id_tp_usuario"];
            $this->id_empresa_sesion = Session::get(("data_empresa"))["id_empresa"];
            $this->modo_sistema = Session::get(("data_empresa"))["modo_sistema"];
            $this->id_terminal_sesion = Session::get(("data_terminal"))["id_terminal"];
            $this->uuid_ws_sesion = Session::get(("data_empresa"))["uuid_ws"];
            $this->nombre_terminal_sesion = Session::get(("data_terminal"))["nombre"];
            $this->color_terminal_sesion = Session::get(("data_terminal"))["color"];

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

    // Metodo general para paginacion de Datatables
    public function runDataTableQuery(
        $data, $baseQuery, $searchableColumns, $select, $defaultOrder = "id DESC", 
        $extraWhere = "", $extraParams = [])
    {
        $db = $this->db->connect();

        // Parámetros DataTables
        $draw = $data['draw'] ?? 1;
        $start = $data['start'] ?? 0;
        $length = $data['length'] ?? 20;
        $search = trim($data['search']['value'] ?? '');

        // -------------------
        // Filtro de búsqueda
        // -------------------
        $searchWhere = "";
        $params = [];
        if ($search !== '' && !empty($searchableColumns)) {
            $prefix = stripos($baseQuery, 'where') !== false ? " AND " : " WHERE ";
            $searchWhere = $prefix . "(" . implode(" LIKE ? OR ", $searchableColumns) . " LIKE ?)";
            foreach ($searchableColumns as $col) {
                $params[] = "%$search%";
            }
        }

        // -------------------
        // Total sin filtro
        // -------------------
        $totalQuery = "SELECT COUNT(*) " . $baseQuery;
        $stmt = $db->prepare($totalQuery);
        $stmt->execute();
        $recordsTotal = $stmt->fetchColumn();

        // -------------------
        // Total con filtro
        // -------------------
        $filteredQuery = "SELECT COUNT(*) " . $baseQuery . $extraWhere . $searchWhere;
        $stmt = $db->prepare($filteredQuery);
        $filteredParams = array_merge($extraParams, $params);
        $stmt->execute($filteredParams);
        $recordsFiltered = $stmt->fetchColumn();

        // -------------------
        // Data paginada
        // -------------------
        $dataQuery = "SELECT " . $select . " " . $baseQuery . $extraWhere . $searchWhere . "
                ORDER BY $defaultOrder
                LIMIT ?, ?";
        $stmt = $db->prepare($dataQuery);
        $executeParams = array_merge($extraParams, $params);
        $executeParams[] = (int) $start;
        $executeParams[] = (int) $length;
        $stmt->execute($executeParams);

        $resultData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $resultData
        ];
    }

    public function get_correlativo($data, $tipo_ruc = 1)
    {
        $where = '';
        if ($tipo_ruc == 2) {
            $where = "AND tipo_ruc = 2";
        }
        $query = $this->db->connect()->prepare("SELECT COUNT(id_venta)+1 AS cantidad FROM venta WHERE id_serie=:id_serie $where");
        $query->bindParam(':id_serie', $data["serie_venta"]);
        $query->execute();
        return $query->fetch((PDO::FETCH_ASSOC))["cantidad"];
    }

    public function get_correlativo_GRT($data)
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("SELECT * FROM serie WHERE id_serie=:id_serie");
        $query->bindParam(":id_serie", $data['serie_venta']);
        $query->execute();
        $serie = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
        SELECT gr.serie, gr.correlativo 
        FROM guia_remision gr 
        WHERE gr.serie=:serie 
        ORDER BY gr.correlativo DESC");
        $query->bindParam(":serie", $serie['serie']);
        $query->execute();
        $serie_guia = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($serie_guia)) {
            $correlativo = 1;
        } else {
            $correlativo = $serie_guia['correlativo'] + 1;
        }
        return $correlativo;
    }

    public function get_igv($conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        $query = $conn->prepare("SELECT igv FROM configuracion");
        $query->execute();
        $reply = $query->fetch((PDO::FETCH_ASSOC));
        if ($reply) {
            return $reply["igv"];
        } else {
            return null;
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

    // Generador de codigo QR para guias unicamente con HASH que devuelve SUNAT
    public function codQRguias($id_guia, $link_guia_sunat = '', $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }

        // Obteniendo el hash de la tabla guia_remision
        $query = $conn->prepare("SELECT serie, correlativo, tipo_ruc FROM guia_remision WHERE id=:id_guia");
        $query->bindParam(":id_guia", $id_guia);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);

        $campo = ($data['tipo_ruc'] == 2) ? 'num_docu_encomienda' : 'num_docu';
        $query = $conn->prepare("SELECT $campo AS ruc FROM empresa LIMIT 1");
        $query->execute();
        $ruc_empresa = $query->fetchColumn();

        $name_imgQR = 'qr_' . $data['serie'] . ' - ' . $data['correlativo'] . '.png';

        // Generando el texto del QR (en este caso, el hash)
        $text_qr = $link_guia_sunat;

        $json = [
            "ose" => 0,
            "cabecera" => [
                "ruc" => $ruc_empresa,
                "tipo_comprobante" => "QR",
                "texto_qr" => $text_qr,
                "nombre_qr" => $name_imgQR
            ]
        ];

        $reply_sunat = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);

        if (!empty($reply_sunat) && is_array($reply_sunat)) {
            $resp = $reply_sunat[0];
            $file_path = $resp['file'];
            // Actualizar el campo cod_qr de la guía
            $query = $conn->prepare("UPDATE guia_remision SET cod_qr=:cod_qr WHERE id=:id_guia");
            $query->bindParam(":id_guia", $id_guia);
            $query->bindParam(":cod_qr", $file_path);
            $query->execute();
            return ['success' => true, 'message' => 'QR generado y almacenado correctamente.', 'file_path' => $file_path];
        } else {
            return ['success' => false, 'message' => 'Error al generar el QR. Respuesta de SUNAT'];
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
        tp_docu_c.codigo AS cliente_tp_docu_codigo,
        v.tipo_ruc
        FROM venta v
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN tp_docu tp_docu_c ON tp_docu_c.id_tp_docu=c.id_tp_docu
        WHERE id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);

        $campo = ($data['tipo_ruc'] == 2) ? 'num_docu_encomienda' : 'num_docu';
        $query = $conn->prepare("SELECT $campo AS ruc FROM empresa LIMIT 1");
        $query->execute();
        $ruc_empresa = $query->fetchColumn();

        // Generando el texto del QR
        $correlativo = str_pad($data["correlativo"], 8, "0", STR_PAD_LEFT);  // Añadir ceros a la izquierda
        $serie_correlativo = $data["serie"] . " - " . $correlativo;
        $name_imgQR = 'QR_' . $data["tp_comprobante_codigo"] . '-' . $serie_correlativo . '.png';
        $text_qr = $this->data_empresa["num_docu"] . '|' . $data["tp_comprobante_codigo"] . '|' . $data["serie"] . '|' . $data["correlativo"] . '|' . $data["op_igv"] . '|' . $data["total"] . '|' . $data["fecha_emision"] . '|' . $data["cliente_tp_docu_codigo"] . '|' . $data["cliente_num_docu"];


        $json = [
            "ose" => 0,
            "cabecera" => [
                "ruc" => $ruc_empresa,
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
            return ['success' => true, 'message' => 'QR generado y almacenado correctamente.', 'file_path' => $file_path];
        } else {
            return ['success' => false, 'message' => 'Error al generar el QR. Respuesta de SUNAT'];
        }
    }


    // Métodos para Anular comprobante electrónico

    public function resumen($id_venta, $id_resumen, $tipo_ruc = 1)
    {
        $conn = $this->db->connect();

        if ($tipo_ruc == 1) {
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
        $query = $conn->prepare("SELECT
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
        v.op_igv AS igv_total,
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
            $item["codigos"] = [1000, "IGV", "VAT"];
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

    public function set_resumen($id_venta, $tipo_ruc = 1, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        date_default_timezone_set('America/Lima');
        $fechaActual = date('Y-m-d');
        $serie = str_replace("-", "", $fechaActual);

        // Recuperando fecha de referencia
        $query = $conn->prepare("SELECT date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format FROM venta v WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $fecha_ref = $query->fetch(PDO::FETCH_ASSOC);

        $fecha_referencia = $fecha_ref['fecha_emision_format'];

        // Consulta para obtener el último correlativo con dos condiciones
        $query = $conn->prepare("SELECT correlativo FROM resumen_baja WHERE fecha_envio = :fecha_actual AND boleta = :boleta AND tipo_ruc = :tipo_ruc AND tipo = 'RC' ORDER BY correlativo DESC LIMIT 1");
        $query->bindParam(':fecha_actual', $fechaActual);
        $query->bindParam(':tipo_ruc', $tipo_ruc);
        $query->bindValue(':boleta', 1);
        $query->execute();
        $ultimo_correlativo = $query->fetchColumn();

        if ($ultimo_correlativo === false) {
            $correlativo = 1;
        } else {
            $correlativo = $ultimo_correlativo + 1;
        }

        // Insertar en la tabla de resumen
        $query = $conn->prepare("INSERT INTO resumen_baja (
                id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo, tipo_ruc, tipo)
                VALUES(
                :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo, :tipo_ruc, :tipo)");
        $query->bindParam(':id_comprobante', $id_venta);
        $query->bindValue(':boleta', 1);
        $query->bindValue(':factura', 0);
        $query->bindParam(':fecha_envio', $fechaActual);
        $query->bindParam(':fecha_referencia', $fecha_referencia);
        $query->bindParam(':correlativo', $correlativo);
        $query->bindParam(':tipo_ruc', $tipo_ruc);
        $query->bindValue(':tipo', 'RC');
        $query->execute();

        $id_resumen_creado = $conn->lastInsertId();
        return $id_resumen_creado;
    }

    //para facturas
    public function set_resumen_f($id_venta, $tipo_ruc = 1, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        date_default_timezone_set('America/Lima');
        $fechaActual = date('Y-m-d');
        $serie = str_replace("-", "", $fechaActual);

        // Recuperando fecha de referencia
        $query = $conn->prepare("SELECT date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format FROM venta v WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $fecha_ref = $query->fetch(PDO::FETCH_ASSOC);

        $fecha_referencia = $fecha_ref['fecha_emision_format'];

        $query = $conn->prepare("SELECT correlativo FROM resumen_baja WHERE fecha_envio = :fecha_actual AND factura = :factura AND tipo_ruc = :tipo_ruc AND tipo = 'RA' ORDER BY correlativo DESC LIMIT 1");
        $query->bindParam(':fecha_actual', $fechaActual);
        $query->bindParam(':tipo_ruc', $tipo_ruc);
        $query->bindValue(':factura', 1);
        $query->execute();
        $ultimo_correlativo = $query->fetchColumn();

        if ($ultimo_correlativo === false) {
            $correlativo = 1;
        } else {
            $correlativo = $ultimo_correlativo + 1;
        }

        // Insertar en la tabla de resumen
        $query = $conn->prepare("INSERT INTO resumen_baja (
                id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo, tipo_ruc, tipo)
                VALUES(
                :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo, :tipo_ruc, :tipo)");
        $query->bindParam(':id_comprobante', $id_venta);
        $query->bindValue(':boleta', 0);
        $query->bindValue(':factura', 1);
        $query->bindParam(':fecha_envio', $fechaActual);
        $query->bindParam(':fecha_referencia', $fecha_referencia);
        $query->bindParam(':correlativo', $correlativo);
        $query->bindParam(':tipo_ruc', $tipo_ruc);
        $query->bindValue(':tipo', 'RA');
        $query->execute();
        $id_resumen_creado = $conn->lastInsertId();

        return $id_resumen_creado;
    }
    public function consult_emisor($conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $query = $conn->prepare("SELECT
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

    public function get_ruc_encomienda_separado($conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $query = $this->db->connect()->prepare("SELECT ruc_encomienda_separado FROM configuracion");
        $query->execute();
        $data = $query->fetchColumn();
        return $data;
    }

    public function consult_emisor_encomienda($conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $query = $conn->prepare("SELECT
        e.envio_ose AS ose,
        e.num_docu_encomienda AS ruc,
        e.razon_social_encomienda AS razon_social,
        e.razon_social_encomienda AS nombre_comercial,
        e.ubigeo_encomienda AS ubigeo,
        ub.depa AS departamento,
        ub.provi AS provincia,
        ub.distri AS distrito,
        e.direccion_fiscal_encomienda AS direccion,    
        e.user_sol_encomienda AS usuario_sol,
        e.pass_sol_encomienda AS clave_sol,
        td.id_tp_docu AS tipodoc,
        e.guia_id_encomienda AS api_id,
        e.guia_clave_encomienda AS api_clave,
        e.nro_cuenta_BN_encomienda AS cuenta_detraccion
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo_encomienda
        LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu_encomienda
        LEFT JOIN tp_docu td ON e.tp_docu_encomienda = td.descripcion
        WHERE t.id_terminal=:id_terminal");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);
        $emisor['pais'] = "PE";
        return $emisor;
    }

    function consult_terminales($data = 0)
    {
        $limit = $data != 0 ? "LIMIT " . $data : "";
        $query = $this->db->connect()->prepare("
            SELECT 
            t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular, 
            CONCAT(u.distri, ' - ', u.provi, ' - ', u.depa) AS ubigeo
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            LEFT JOIN ubigeo u ON t.ubigeo = u.cod_ubigeo
            WHERE e.id_empresa = :id_empresa
            $limit
        ");
        $query->bindParam(':id_empresa', $this->id_empresa_sesion, PDO::PARAM_INT);
        $query->execute();
        $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
        return $terminales;
    }

    function guardar_ticket($data, $ultimo, $conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }

        $response = [];

        if (!empty($data)) {
            $query = $conn->prepare("UPDATE resumen_baja SET
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

    public function crear_guia($id_encomiendas, $id_programacion, $id_terminal_destino, $conn = null)
    {
        try {
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            //Obteniendo serie y correlativo 
            date_default_timezone_set('America/Lima');
            $fechaActual = date('Y-m-d');
            $horaActual = date('H:i:s');
            $tp_c = "31";
            $query = $conn->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $tp_c);
            $query->execute();
            $serie = $query->fetch(PDO::FETCH_ASSOC);
            $pagante = [];

            if ($serie == null) {
                return array('success' => false, "message" => array("message" => "No existe una serie para esta terminal para las guias de remision"));
            }

            $query = $conn->prepare("SELECT gr.serie, gr.correlativo FROM guia_remision gr WHERE gr.serie=:serie ORDER BY gr.correlativo DESC LIMIT 1");
            $query->bindParam(":serie", $serie['serie']);
            $query->execute();
            $serie_guia = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($serie_guia)) {
                $correlativo = 1;
            } else {
                $correlativo = $serie_guia[0]['correlativo'] + 1;
            }

            $id_encomiendas_array = explode(',', $id_encomiendas);
            $placeholders = implode(',', array_fill(0, count($id_encomiendas_array), '?'));

            // Primera consulta para encontrar los id_venta basados en id_encomiendas
            $query1 = $conn->prepare("SELECT id_venta FROM dt_venta WHERE id_encomienda IN ($placeholders)");
            $query1->execute($id_encomiendas_array);
            $id_ventas = $query1->fetchAll(PDO::FETCH_COLUMN);

            // Segunda consulta para obtener los pesos basados en los id_venta encontrados
            $pesos = [];
            foreach ($id_ventas as $id_venta) {
                $query2 = $conn->prepare("SELECT peso_encomienda FROM venta WHERE id_venta = :id_venta");
                $query2->bindParam(":id_venta", $id_venta);
                $query2->execute();
                $peso = $query2->fetch(PDO::FETCH_COLUMN);
                if ($peso !== false) {
                    $pesos[] = $peso;
                }
            }
            $suma_pesos = 0;

            foreach ($pesos as $peso) {
                $peso = floatval($peso);
                $suma_pesos += $peso;
            }

            //Apartado para conseguir datos a partir de la programacion
            $query3 = $conn->prepare("
            SELECT 
            v.placa,
            u.id_tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            dt_c.licencia,
            orig.ubigeo AS ubigeo_origen,
            orig.direccion_comercial AS direccion_origen,
            u_o.id_usuario AS usuario_origen,
            CONCAT(u_o.nombres, ' ', u_o.apellidos) AS usuario_orig,
            u_o.num_docu AS doc_orig,
            u_o.id_tp_docu AS tipodoc_orig,
            p.fecha_salida
            FROM programacion p 
            LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
            LEFT JOIN usuario u ON p.id_conductor = u.id_usuario 
            LEFT JOIN dt_conductor dt_c ON p.id_conductor = dt_c.id_usuario
            LEFT JOIN terminal orig ON p.id_terminal_origen = orig.id_terminal
            LEFT JOIN usuario u_o ON orig.encargado = u_o.id_usuario
            WHERE p.id_programacion = :id_programacion");
            $query3->bindParam("id_programacion", $id_programacion);
            $query3->execute();
            $d_programacion = $query3->fetch(PDO::FETCH_ASSOC);

            if ($d_programacion['nombres'] == 'CONDUCTOR') {
                return ['success' => false, "message" => ["message" => "La programacion no tiene un conductor designado"]];
            }

            // Obteniendo data del terminal destino
            $query4 = $conn->prepare("
            SELECT
            t.nombre,
            t.direccion_comercial AS direccion,
            CONCAT(u.distri, ' - ', u.provi, ' - ', u.depa) AS ubigeo_text,
            t.ubigeo,
            CONCAT(u_d.nombres, ' ', u_d.apellidos) AS usuario_dest,
            u_d.num_docu AS doc_dest,
            u_d.id_tp_docu AS tipodoc_dest
            FROM terminal t
            LEFT JOIN ubigeo u ON t.ubigeo = u.cod_ubigeo
            LEFT JOIN usuario u_d ON t.encargado = u_d.id_usuario
            WHERE t.id_terminal = :id_terminal_destino");
            $query4->bindParam(":id_terminal_destino", $id_terminal_destino);
            $query4->execute();
            $terminal_destino = $query4->fetch(PDO::FETCH_ASSOC);

            $emisor = $this->consult_emisor();
            // Obtener el token para el envio de la guia de remision
            $token = $this->ObtenerTokenAutenticacion($emisor);
            if (!$token['success']) {
                return ['success' => false, "message" => $token['message']];
            } else {
                $emisor['token'] = $token['token'];
            }

            //remitente
            $data_usuario = Session::get("data_usuario");

            //En caso sea la empresa
            // $cliente['tipo_documento'] = $emisor['tipodoc'];
            // $cliente['num_doc'] = $emisor['ruc'];
            // $cliente['razon_social'] = $emisor['razon_social'];
            //En caso fuera seguir el formato anterior mencionado
            // $destinatario['tipo_documento'] = $d_programacion['tipodoc_dest'];
            // $destinatario['num_doc'] = $d_programacion['doc_dest'];
            // $destinatario['razon_social'] = $d_programacion['usuario_dest'];

            //En caso se manejen solo el personal y la empresa solo transporta
            $cliente['tipo_documento'] = $d_programacion['tipodoc_orig'];
            $cliente['num_doc'] = $d_programacion['doc_orig'];
            $cliente['razon_social'] = $d_programacion['usuario_orig'];
            $cliente['usuario'] = $d_programacion['usuario_origen'];

            $destinatario['tipo_documento'] = $emisor['tipodoc'];
            $destinatario['num_doc'] = $emisor['ruc'];
            $destinatario['razon_social'] = $emisor['razon_social'];

            $cabecera['tipo_comprobante'] = "31";
            $cabecera['serie'] = $serie['serie'];
            $cabecera['correlativo'] = $correlativo;
            $cabecera['fecha_emision'] = $fechaActual;
            $cabecera['hora_emision'] = $horaActual;
            $cabecera['fecha_envio'] = $d_programacion['fecha_salida'];
            $cabecera['unidad_peso'] = "KGM";
            $cabecera['peso'] = $suma_pesos;
            //Apartado vehiculo
            $cabecera['vehiculo_placa'] = $d_programacion['placa'];
            $cabecera['vehiculo_TUC'] = "";
            $cabecera['vehiculo_mtc'] = "";
            //Apartado conductor
            $cabecera['conductor_tipo_doc'] = $d_programacion['id_tp_docu'];
            $cabecera['conductor_nro_doc'] = $d_programacion['num_docu'];
            $cabecera['conductor_nombres'] = $d_programacion['nombres'];
            $cabecera['conductor_apellidos'] = $d_programacion['apellidos'];
            $cabecera['conductor_licencia'] = $d_programacion['licencia'];
            //Lugar de partida
            $cabecera['partida_ubigeo'] = trim($d_programacion['ubigeo_origen']);
            $cabecera['partida_direccion'] = trim($d_programacion['direccion_origen']);
            //Lugar de destino
            $cabecera['destino_ubigeo'] = trim($terminal_destino['ubigeo']);
            $cabecera['destino_direccion'] = trim($terminal_destino['direccion']);
            $cabecera['observaciones'] = "";
            $cabecera['indicador_envio_SUNAT'] = "";

            //Apartado para conseguir productos de todas las encmiendas
            $query_nombres = $conn->prepare("
            SELECT p_e.id_ctg_encomienda, p_e.descripcion, ctg.codigo 
            FROM producto_encomienda p_e
            LEFT JOIN ctg_encomienda ctg ON p_e.id_ctg_encomienda = ctg.id_ctg_encomienda");
            $query_nombres->execute();
            $nombres = $query_nombres->fetchAll(PDO::FETCH_ASSOC);

            // Extrayendo data de encomiendas
            $queryE = $conn->prepare("
                SELECT guia_serie, guia_correlativo, guia_ruc, tp_comprobante_ref, serie_ref, correlativo_ref, ruc_ref
                FROM encomienda 
                WHERE id_encomienda IN ($id_encomiendas)
                ");
            $queryE->execute();
            $docs_encomiendas = $queryE->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($docs_encomiendas)) {
                $texto_guia = '';
                $texto_comp = '';

                foreach ($docs_encomiendas as $doc_e) {
                    if (!empty($doc_e['guia_serie'])) {
                        if ($texto_guia === '') {
                            $texto_guia = "GRR relacionadas ";
                        }
                        $texto_guia .= $doc_e['guia_serie'] . '-' . $doc_e['guia_correlativo'] . '-' . $doc_e['guia_ruc'] . ',';
                    }

                    if (!empty($doc_e['tp_comprobante_ref']) && $doc_e['tp_comprobante_ref'] != '0') {
                        if ($texto_comp === '') {
                            $texto_comp = " Comprobantes relacionados ";
                        }
                        $texto_comp .= $doc_e['serie_ref'] . '-' . $doc_e['correlativo_ref'] . '-' . $doc_e['ruc_ref'] . ',';
                    }
                }

                $texto = $texto_guia . $texto_comp;
                $texto = rtrim($texto, ',');
                $texto = substr($texto, 0, 250);

                $cabecera['observaciones'] = $texto;
            } else {
                $cabecera['observaciones'] = "";
            }

            // Crear un array asociativo para mapear id_ctg_encomienda con el nombre correspondiente
            $nombres_por_id = [];
            $codigos_por_id = [];

            foreach ($nombres as $nombre) {
                $nombres_por_id[$nombre['id_ctg_encomienda']] = $nombre['descripcion'];
                $codigos_por_id[$nombre['id_ctg_encomienda']] = $nombre['codigo'];
            }

            $query4 = $conn->prepare("SELECT * 
            FROM producto_encomienda p_e
            WHERE p_e.id_encomienda IN ($id_encomiendas)");
            $query4->execute();
            $items = $query4->fetchAll(PDO::FETCH_ASSOC);
            $productos = [];
            $sumas_por_di_ctg_endomienda = [];
            $i = 1;

            foreach ($items as &$item) {
                $id_ctg_encomienda = $item['id_ctg_encomienda'];

                // Si es un nuevo producto, añadimos un nuevo elemento al array consolidado
                $iten = [];
                $iten['item'] = $i++;
                $iten['codigo'] = $codigos_por_id[$id_ctg_encomienda] ?? '';
                $iten['unidad'] = "NIU";
                $iten['cantidad'] = $item['cantidad'];
                $iten['id_producto'] = $id_ctg_encomienda;
                $nombre = $nombres_por_id[$id_ctg_encomienda];
                $iten['nombre'] = $nombre;
                $productos[] = $iten;
                $sumas_por_di_ctg_endomienda[$id_ctg_encomienda] = $item['cantidad'];
            }

            // Actualizamos la cantidad de productos en el array consolidado
            foreach ($productos as &$producto) {
                $id_producto = $producto['id_producto'];
                $producto['cantidad'] = $sumas_por_di_ctg_endomienda[$id_producto];
            }

            $json = [
                "ose" => 0,
                "emisor" => $emisor,
                "remitente" => $cliente,
                "destinatario" => $destinatario,
                "pagante" => $pagante,
                "cabecera" => $cabecera,
                "documentos_globales" => [],
                "items" => $productos,

            ];
            return ['success' => true, "json" => $json];
        } catch (PDOException $e) {
            return ['success' => false, "error" => "Error al obtener los datos", $e];
        }
    }

    // Crear guias de remision individuales
    public function crear_guia_individual($id_encomiendas, $id_programacion, $conn = null)
    {
        try {
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            //Obteniendo serie y correlativo 
            date_default_timezone_set('America/Lima');
            $fechaActual = date('Y-m-d');
            $horaActual = date('H:i:s');
            $tp_c = "31";
            $documentos_globales = [];

            $query = $conn->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $tp_c);
            $query->execute();
            $serie = $query->fetch(PDO::FETCH_ASSOC);

            if ($serie == null) {
                return ['success' => false, "message" => ["message" => "No existe una serie para esta terminal para las guias de remision"]];
            }

            $query = $conn->prepare("SELECT gr.serie, gr.correlativo FROM guia_remision gr WHERE gr.serie=:serie ORDER BY gr.correlativo DESC LIMIT 1");
            $query->bindParam(":serie", $serie['serie']);
            $query->execute();
            $serie_guia = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($serie_guia)) {
                $correlativo = 1;
            } else {
                $correlativo = $serie_guia[0]['correlativo'] + 1;
            }

            //obteniendo detalle de las encomiendas peso y demas
            $id_encomiendas_array = explode(',', $id_encomiendas);

            // Crear un marcador de posición para cada ID de encomienda en la consulta
            $placeholders = implode(',', array_fill(0, count($id_encomiendas_array), '?'));

            // Primera consulta para encontrar los id_venta basados en id_encomiendas
            $query1 = $conn->prepare("SELECT id_venta FROM dt_venta WHERE id_encomienda IN ($placeholders)");
            $query1->execute($id_encomiendas_array);
            $id_ventas = $query1->fetchAll(PDO::FETCH_COLUMN);

            // Segunda consulta para obtener los pesos basados en los id_venta encontrados
            $pesos = [];
            foreach ($id_ventas as $id_venta) {
                $query2 = $conn->prepare("SELECT peso_encomienda FROM venta WHERE id_venta = :id_venta");
                $query2->bindParam(":id_venta", $id_venta);
                $query2->execute();
                $peso = $query2->fetch(PDO::FETCH_COLUMN);
                if ($peso !== false) {
                    $pesos[] = $peso;
                }
            }
            $suma_pesos = 0;

            foreach ($pesos as $peso) {
                $peso = floatval($peso);
                $suma_pesos += $peso;
            }

            //Apartado para conseguir datos a partir de la programacion
            $query3 = $conn->prepare("
            SELECT 
            v.placa,
            u.id_tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            dt_c.licencia,
            orig.ubigeo AS ubigeo_origen,
            orig.direccion_comercial AS direccion_origen,
            dest.ubigeo AS ubigeo_destino,
            dest.direccion_comercial AS direccion_destino,
            u_o.id_usuario AS usuario_origen,
            CONCAT(u_o.apellidos, ' ', u_o.nombres) AS usuario_orig,
            u_o.num_docu AS doc_orig,
            u_o.id_tp_docu AS tipodoc_orig,
            CONCAT(u_d.apellidos, ' ', u_d.nombres) AS usuario_dest,
            u_d.num_docu AS doc_dest,
            u_d.id_tp_docu AS tipodoc_dest,
            p.fecha_salida
            FROM programacion p 
            LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
            LEFT JOIN usuario u ON p.id_conductor = u.id_usuario 
            LEFT JOIN dt_conductor dt_c ON p.id_conductor = dt_c.id_usuario
            LEFT JOIN terminal orig ON p.id_terminal_origen = orig.id_terminal
            LEFT JOIN terminal dest ON p.id_terminal_destino = dest.id_terminal
            LEFT JOIN usuario u_o ON orig.encargado = u_o.id_usuario
            LEFT JOIN usuario u_d ON dest.encargado = u_d.id_usuario
            WHERE p.id_programacion = :id_programacion");
            $query3->bindParam("id_programacion", $id_programacion);
            $query3->execute();
            $d_programacion = $query3->fetch(PDO::FETCH_ASSOC);

            if ($d_programacion['nombres'] == 'CONDUCTOR') {
                return ['success' => false, "message" => ["message" => "La programacion no tiene un conductor designado"]];
            }

            // Sacando data toda la data necesaria de encomienda
            $query = $conn->prepare("
            SELECT * 
            FROM encomienda 
            WHERE id_encomienda IN ($id_encomiendas)");
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);
            $encomienda = $encomiendas[0];
            $id_encomienda = $encomienda['id_encomienda'];
            $id_remitente = $encomienda['id_remitente'];
            $id_destinatario = $encomienda['id_destinatario'];
            $id_pagador_flete = $encomienda['pagador_flete'];

            // Agregar el unico comprobante de encomienda a docmuentos globales
            if (!empty($encomienda['tp_comprobante_ref']) && $encomienda['tp_comprobante_ref'] != '0') {
                $documentos_globales[] = [
                    'tipo_documento' => $encomienda['tp_comprobante_ref'],
                    'serie_numero' => $encomienda['serie_ref'] . '-' . $encomienda['correlativo_ref'],
                    'ruc_emisor' => $encomienda['ruc_ref'],
                ];
            }

            if (!empty($encomienda['guia_serie'])) {
                $documentos_globales[] = [
                    'tipo_documento' => '09',
                    'serie_numero' => $encomienda['guia_serie'] . '-' . $encomienda['guia_correlativo'],
                    'ruc_emisor' => $encomienda['guia_ruc'],
                ];
            }

            // Extraer data de remitente a partir de la encomienda
            $query = $conn->prepare("
            SELECT 
            CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
            u.num_docu AS num_docu,
            u.id_tp_docu AS id_tp_docu
            FROM usuario u
            WHERE u.id_usuario = $id_remitente");
            $query->execute();
            $data_remitente = $query->fetch(PDO::FETCH_ASSOC);

            // Extraer data de destinatario a partir de la encomienda
            $query = $conn->prepare("
            SELECT 
            CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
            u.num_docu AS num_docu,
            u.id_tp_docu AS id_tp_docu
            FROM usuario u
            WHERE u.id_usuario = $id_destinatario");
            $query->execute();
            $data_destinatario = $query->fetch(PDO::FETCH_ASSOC);

            // Extraer data de pagador de flete a partir de la encomienda
            if ($id_pagador_flete != 0) {
                $query = $conn->prepare("
                SELECT CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
                u.num_docu AS num_docu, 
                u.id_tp_docu AS id_tp_docu
                FROM usuario u
                WHERE u.id_usuario = $id_pagador_flete");
                $query->execute();
                $data_pagador_flete = $query->fetch(PDO::FETCH_ASSOC);
            }

            // Armando la data de remitente
            $remitente = [];
            $remitente['tipo_documento'] = $data_remitente['id_tp_docu'];
            $remitente['num_doc'] = $data_remitente['num_docu'];
            $remitente['razon_social'] = $data_remitente['nombres'];
            $remitente['usuario'] = $id_remitente;

            // Armando la data de destinatario
            $destinatario = [];
            $destinatario['tipo_documento'] = $data_destinatario['id_tp_docu'];
            $destinatario['num_doc'] = $data_destinatario['num_docu'];
            $destinatario['razon_social'] = $data_destinatario['nombres'];

            // Armando la data de pagador de flete
            $pagador_fletero = [];
            if ($id_pagador_flete != 0) {
                $pagador_fletero['tipo_documento'] = $data_pagador_flete['id_tp_docu'];
                $pagador_fletero['numero_doc'] = $data_pagador_flete['num_docu'];
                $pagador_fletero['razon_social'] = $data_pagador_flete['nombres'];
                $pagador_fletero['id_pagador'] = $id_pagador_flete;
            }

            // Consultar datos de partida y llegada
            $query = $conn->prepare("
                SELECT e.dir_puntopartida, e.dir_puntollegada, e.ubi_partida, e.ubi_llegada
                FROM encomienda e
                WHERE e.id_encomienda = $id_encomienda");
            $query->execute();
            $data_puntos_dir = $query->fetch(PDO::FETCH_ASSOC);

            $emisor = $this->consult_emisor();
            // Obtener el token para el envio de la guia de remision
            $token = $this->ObtenerTokenAutenticacion($emisor, $conn);
            if (!$token['success']) {
                return ['success' => false, "message" => $token['message']];
            } else {
                $emisor['token'] = $token['token'];
            }

            $cabecera['tipo_comprobante'] = "31";
            $cabecera['serie'] = $serie['serie'];
            $cabecera['correlativo'] = $correlativo;
            $cabecera['fecha_emision'] = $fechaActual;
            $cabecera['hora_emision'] = $horaActual;
            $cabecera['fecha_envio'] = $d_programacion['fecha_salida'];
            $cabecera['unidad_peso'] = "KGM";
            $cabecera['peso'] = $suma_pesos;

            //Apartado vehiculo
            $cabecera['vehiculo_placa'] = $d_programacion['placa'];
            $cabecera['vehiculo_TUC'] = "";
            $cabecera['vehiculo_mtc'] = "";
            //Apartado conductor
            $cabecera['conductor_tipo_doc'] = $d_programacion['id_tp_docu'];
            $cabecera['conductor_nro_doc'] = $d_programacion['num_docu'];
            $cabecera['conductor_nombres'] = $d_programacion['nombres'];
            $cabecera['conductor_apellidos'] = $d_programacion['apellidos'];
            $cabecera['conductor_licencia'] = $d_programacion['licencia'];
            //Lugar de partida
            $cabecera['partida_ubigeo'] = trim($data_puntos_dir['ubi_partida']);
            $cabecera['partida_direccion'] = trim($data_puntos_dir['dir_puntopartida']);
            //Lugar de destino
            $cabecera['destino_ubigeo'] = trim($data_puntos_dir['ubi_llegada']);
            $cabecera['destino_direccion'] = trim($data_puntos_dir['dir_puntollegada']);
            $cabecera['observaciones'] = "";
            $cabecera['indicador_envio_SUNAT'] = "SUNAT_Envio_IndicadorPagadorFlete_Tercero";

            //Apartado para conseguir productos de todas las encmiendas
            $query_nombres = $conn->prepare("
            SELECT p_e.id_ctg_encomienda, p_e.descripcion, ctg.codigo 
            FROM producto_encomienda p_e
            LEFT JOIN ctg_encomienda ctg ON p_e.id_ctg_encomienda = ctg.id_ctg_encomienda");
            $query_nombres->execute();
            $nombres = $query_nombres->fetchAll(PDO::FETCH_ASSOC);

            // Crear un array asociativo para mapear id_ctg_encomienda con el nombre correspondiente
            $nombres_por_id = [];
            $codigos_por_id = [];

            foreach ($nombres as $nombre) {
                $nombres_por_id[$nombre['id_ctg_encomienda']] = $nombre['descripcion'];
                $codigos_por_id[$nombre['id_ctg_encomienda']] = $nombre['codigo'];
            }

            $query4 = $conn->prepare("SELECT * 
            FROM producto_encomienda p_e
            WHERE p_e.id_encomienda IN ($id_encomiendas)");
            $query4->execute();
            $items = $query4->fetchAll(PDO::FETCH_ASSOC);
            $productos = [];
            $sumas_por_di_ctg_endomienda = [];
            $i = 1;

            foreach ($items as &$item) {
                $id_ctg_encomienda = $item['id_ctg_encomienda'];

                // Si el producto ya existe en el array consolidado, sumamos la cantidad
                // Si es un nuevo producto, añadimos un nuevo elemento al array consolidado
                $iten = [];
                $iten['item'] = $i++;
                $iten['codigo'] = $codigos_por_id[$id_ctg_encomienda] ?? '';
                $iten['unidad'] = "NIU";
                $iten['cantidad'] = $item['cantidad'];
                $iten['id_producto'] = $id_ctg_encomienda;
                $nombre = $nombres_por_id[$id_ctg_encomienda];
                $iten['nombre'] = $nombre;
                $productos[] = $iten;
                $sumas_por_di_ctg_endomienda[$id_ctg_encomienda] = $item['cantidad'];
            }

            // Actualizamos la cantidad de productos en el array consolidado
            foreach ($productos as &$producto) {
                $id_producto = $producto['id_producto'];
                $producto['cantidad'] = $sumas_por_di_ctg_endomienda[$id_producto];
            }

            $json = [
                "ose" => 0,
                "emisor" => $emisor,
                "remitente" => $remitente,
                "destinatario" => $destinatario,
                "pagante" => $pagador_fletero,
                "cabecera" => $cabecera,
                "documentos_globales" => $documentos_globales,
                "items" => $productos,

            ];
            return ['success' => true, "json" => $json];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error al obtener los datos"];
        }
    }

    // Guias de remision netamente individuales
    public function GRT_individual($id_encomienda, $id_programacion, $e_domicilio, $e_salida, $conn = null)
    {
        try {
            //Obteniendo serie y correlativo 
            date_default_timezone_set('America/Lima');
            $fechaActual = date('Y-m-d');
            $horaActual = date('H:i:s');
            $tp_c = "31";
            $documentos_globales = [];

            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $tp_c);
            $query->execute();
            $serie = $query->fetch(PDO::FETCH_ASSOC);

            if ($serie == null) {
                throw new Exception("No existe una serie para esta terminal para las guias de remision");
            }

            $query = $conn->prepare("SELECT gr.serie, gr.correlativo FROM guia_remision gr WHERE gr.serie=:serie ORDER BY gr.correlativo DESC LIMIT 1");
            $query->bindParam(":serie", $serie['serie']);
            $query->execute();
            $serie_guia = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($serie_guia)) {
                $correlativo = 1;
            } else {
                $correlativo = $serie_guia[0]['correlativo'] + 1;
            }

            // Primera consulta para encontrar los id_venta basados en id_encomiendas
            $query1 = $conn->prepare("SELECT id_venta FROM dt_venta WHERE id_encomienda = :id_encomienda");
            $query1->bindParam(":id_encomienda", $id_encomienda);
            $query1->execute();
            $id_venta = $query1->fetch(PDO::FETCH_COLUMN);

            // Segunda consulta para obtener los pesos basados en los id_venta encontrados
            $pesos = [];
            $query2 = $conn->prepare("SELECT peso_encomienda FROM venta WHERE id_venta = :id_venta");
            $query2->bindParam(":id_venta", $id_venta);
            $query2->execute();
            $peso = $query2->fetch(PDO::FETCH_COLUMN);
            if ($peso !== false) {
                $pesos[] = $peso;
            }

            $suma_pesos = 0;

            foreach ($pesos as $peso) {
                $peso = floatval($peso);
                $suma_pesos += $peso;
            }

            //Apartado para conseguir datos a partir de la programacion
            if ($e_salida == 1) {
                $query3 = $conn->prepare("
            SELECT 
            v.placa,
            u.id_tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            dt_c.licencia,
            p.ubigeo_origen,
            p.direccion_origen,
            p.ubigeo_destino,
            p.direccion_destino,
            p.fecha_salida
            FROM programacion_salida p 
            LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
            LEFT JOIN usuario u ON p.id_conductor = u.id_usuario 
            LEFT JOIN dt_conductor dt_c ON p.id_conductor = dt_c.id_usuario
            LEFT JOIN ubigeo ub_o ON p.ubigeo_origen = ub_o.cod_ubigeo
            LEFT JOIN ubigeo ub_d ON p.ubigeo_destino = ub_d.cod_ubigeo
            WHERE p.id_salida = :id_salida");
                $query3->bindParam("id_salida", $id_programacion);
                $query3->execute();
                $d_programacion = $query3->fetch(PDO::FETCH_ASSOC);
            } else {
                $query3 = $conn->prepare("
            SELECT 
            v.placa,
            u.id_tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            dt_c.licencia,
            orig.ubigeo AS ubigeo_origen,
            orig.direccion_comercial AS direccion_origen,
            dest.ubigeo AS ubigeo_destino,
            dest.direccion_comercial AS direccion_destino,
            u_o.id_usuario AS usuario_origen,
            CONCAT(u_o.apellidos, ' ', u_o.nombres) AS usuario_orig,
            u_o.num_docu AS doc_orig,
            u_o.id_tp_docu AS tipodoc_orig,
            CONCAT(u_d.apellidos, ' ', u_d.nombres) AS usuario_dest,
            u_d.num_docu AS doc_dest,
            u_d.id_tp_docu AS tipodoc_dest,
            p.fecha_salida
            FROM programacion p 
            LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
            LEFT JOIN usuario u ON p.id_conductor = u.id_usuario 
            LEFT JOIN dt_conductor dt_c ON p.id_conductor = dt_c.id_usuario
            LEFT JOIN terminal orig ON p.id_terminal_origen = orig.id_terminal
            LEFT JOIN terminal dest ON p.id_terminal_destino = dest.id_terminal
            LEFT JOIN usuario u_o ON orig.encargado = u_o.id_usuario
            LEFT JOIN usuario u_d ON dest.encargado = u_d.id_usuario
            WHERE p.id_programacion = :id_programacion");
                $query3->bindParam("id_programacion", $id_programacion);
                $query3->execute();
                $d_programacion = $query3->fetch(PDO::FETCH_ASSOC);
            }

            if ($d_programacion['nombres'] == 'CONDUCTOR') {
                throw new Exception("La programacion no tiene un conductor designado");
            }

            // Sacando data toda la data necesaria de encomienda
            $query = $conn->prepare("
            SELECT * 
            FROM encomienda 
            WHERE id_encomienda = :id_encomienda");
            $query->bindParam("id_encomienda", $id_encomienda);
            $query->execute();
            $encomienda = $query->fetch(PDO::FETCH_ASSOC);

            $id_encomienda = $encomienda['id_encomienda'];
            $id_remitente = $encomienda['id_remitente'];
            $id_destinatario = $encomienda['id_destinatario'];
            $id_pagador_flete = $encomienda['pagador_flete'];

            // Agregar el unico comprobante de encomienda a docmuentos globales
            if (!empty($encomienda['tp_comprobante_ref']) && $encomienda['tp_comprobante_ref'] != '0') {
                $documentos_globales[] = [
                    'tipo_documento' => $encomienda['tp_comprobante_ref'],
                    'serie_numero' => $encomienda['serie_ref'] . '-' . $encomienda['correlativo_ref'],
                    'ruc_emisor' => $encomienda['ruc_ref'],
                ];
            }

            if (!empty($encomienda['guia_serie'])) {
                $documentos_globales[] = [
                    'tipo_documento' => '09',
                    'serie_numero' => $encomienda['guia_serie'] . '-' . $encomienda['guia_correlativo'],
                    'ruc_emisor' => $encomienda['guia_ruc'],
                ];
            }

            // Extraer data de remitente a partir de la encomienda
            $query = $conn->prepare("
            SELECT 
            CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
            u.num_docu AS num_docu,
            u.id_tp_docu AS id_tp_docu
            FROM usuario u
            WHERE u.id_usuario = $id_remitente");
            $query->execute();
            $data_remitente = $query->fetch(PDO::FETCH_ASSOC);

            // Extraer data de destinatario a partir de la encomienda
            $query = $conn->prepare("
            SELECT 
            CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
            u.num_docu AS num_docu,
            u.id_tp_docu AS id_tp_docu
            FROM usuario u
            WHERE u.id_usuario = $id_destinatario");
            $query->execute();
            $data_destinatario = $query->fetch(PDO::FETCH_ASSOC);

            // Extraer data de pagador de flete a partir de la encomienda
            if ($id_pagador_flete != 0) {
                $query = $conn->prepare("
                SELECT CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
                u.num_docu AS num_docu, 
                u.id_tp_docu AS id_tp_docu
                FROM usuario u
                WHERE u.id_usuario = $id_pagador_flete");
                $query->execute();
                $data_pagador_flete = $query->fetch(PDO::FETCH_ASSOC);
            }

            // Armando la data de remitente
            $remitente = [];
            $remitente['tipo_documento'] = $data_remitente['id_tp_docu'];
            $remitente['num_doc'] = $data_remitente['num_docu'];
            $remitente['razon_social'] = $data_remitente['nombres'];
            $remitente['usuario'] = $id_remitente;

            // Armando la data de destinatario
            $destinatario = [];
            $destinatario['tipo_documento'] = $data_destinatario['id_tp_docu'];
            $destinatario['num_doc'] = $data_destinatario['num_docu'];
            $destinatario['razon_social'] = $data_destinatario['nombres'];

            // Armando la data de pagador de flete
            $pagador_fletero = [];
            if ($id_pagador_flete != 0) {
                $pagador_fletero['tipo_documento'] = $data_pagador_flete['id_tp_docu'];
                $pagador_fletero['numero_doc'] = $data_pagador_flete['num_docu'];
                $pagador_fletero['razon_social'] = $data_pagador_flete['nombres'];
                $pagador_fletero['id_pagador'] = $id_pagador_flete;
            }

            // Consultar datos de partida y llegada
            $query = $conn->prepare("
                SELECT e.dir_puntopartida, e.dir_puntollegada, e.ubi_partida, e.ubi_llegada
                FROM encomienda e
                WHERE e.id_encomienda = $id_encomienda");
            $query->execute();
            $data_puntos_dir = $query->fetch(PDO::FETCH_ASSOC);

            $emisor = $this->consult_emisor($conn);
            // Obtener el token para el envio de la guia de remision
            $token = $this->ObtenerTokenAutenticacion($emisor, $conn);
            if (!$token['success']) {
                throw new Exception($token['message']);
            } else {
                $emisor['token'] = $token['token'];
            }

            $cabecera['tipo_comprobante'] = "31";
            $cabecera['serie'] = $serie['serie'];
            $cabecera['correlativo'] = $correlativo;
            $cabecera['fecha_emision'] = $fechaActual;
            $cabecera['hora_emision'] = $horaActual;
            $cabecera['fecha_envio'] = $d_programacion['fecha_salida'];
            $cabecera['unidad_peso'] = "KGM";
            $cabecera['peso'] = $suma_pesos;

            //Apartado vehiculo
            $cabecera['vehiculo_placa'] = $d_programacion['placa'];
            $cabecera['vehiculo_TUC'] = "";
            $cabecera['vehiculo_mtc'] = "";
            //Apartado conductor
            $cabecera['conductor_tipo_doc'] = $d_programacion['id_tp_docu'];
            $cabecera['conductor_nro_doc'] = $d_programacion['num_docu'];
            $cabecera['conductor_nombres'] = $d_programacion['nombres'];
            $cabecera['conductor_apellidos'] = $d_programacion['apellidos'];
            $cabecera['conductor_licencia'] = $d_programacion['licencia'];
            //Lugar de partida
            $cabecera['partida_ubigeo'] = $e_domicilio == 1 ? trim($data_puntos_dir['ubi_partida']) : trim($d_programacion['ubigeo_origen']);
            $cabecera['partida_direccion'] = $e_domicilio == 1 ? trim($data_puntos_dir['dir_puntopartida']) : trim($d_programacion['direccion_origen']);
            //Lugar de destino
            $cabecera['destino_ubigeo'] = $e_domicilio == 1 ? trim($data_puntos_dir['ubi_llegada']) : trim($d_programacion['ubigeo_destino']);
            $cabecera['destino_direccion'] = $e_domicilio == 1 ? trim($data_puntos_dir['dir_puntollegada']) : trim($d_programacion['direccion_destino']);
            $cabecera['observaciones'] = "";
            $cabecera['indicador_envio_SUNAT'] = "SUNAT_Envio_IndicadorPagadorFlete_Tercero";

            //Apartado para conseguir productos de todas las encmiendas
            $query_nombres = $conn->prepare("
            SELECT p_e.id_ctg_encomienda, p_e.descripcion, ctg.codigo 
            FROM producto_encomienda p_e
            LEFT JOIN ctg_encomienda ctg ON p_e.id_ctg_encomienda = ctg.id_ctg_encomienda");
            $query_nombres->execute();
            $nombres = $query_nombres->fetchAll(PDO::FETCH_ASSOC);

            // Crear un array asociativo para mapear id_ctg_encomienda con el nombre correspondiente
            $nombres_por_id = [];
            $codigos_por_id = [];

            foreach ($nombres as $nombre) {
                $nombres_por_id[$nombre['id_ctg_encomienda']] = $nombre['descripcion'];
                $codigos_por_id[$nombre['id_ctg_encomienda']] = $nombre['codigo'];
            }

            $query4 = $conn->prepare("SELECT * 
            FROM producto_encomienda p_e
            WHERE p_e.id_encomienda = :id_encomienda");
            $query4->bindParam("id_encomienda", $id_encomienda);
            $query4->execute();
            $items = $query4->fetchAll(PDO::FETCH_ASSOC);
            $productos = [];
            $sumas_por_di_ctg_endomienda = [];
            $i = 1;

            foreach ($items as &$item) {
                $id_ctg_encomienda = $item['id_ctg_encomienda'];

                // Si es un nuevo producto, añadimos un nuevo elemento al array consolidado
                $iten = [];
                $iten['item'] = $i++;
                $iten['codigo'] = $codigos_por_id[$id_ctg_encomienda] ?? '';
                $iten['unidad'] = "NIU";
                $iten['cantidad'] = $item['cantidad'];
                $iten['id_producto'] = $id_ctg_encomienda;
                $nombre = $nombres_por_id[$id_ctg_encomienda];
                $iten['nombre'] = $nombre;
                $productos[] = $iten;
                $sumas_por_di_ctg_endomienda[$id_ctg_encomienda] = $item['cantidad'];
            }

            // Actualizamos la cantidad de productos en el array consolidado
            foreach ($productos as &$producto) {
                $id_producto = $producto['id_producto'];
                $producto['cantidad'] = $sumas_por_di_ctg_endomienda[$id_producto];
            }

            $json = [
                "ose" => 0,
                "emisor" => $emisor,
                "remitente" => $remitente,
                "destinatario" => $destinatario,
                "pagante" => $pagador_fletero,
                "cabecera" => $cabecera,
                "documentos_globales" => $documentos_globales,
                "items" => $productos,

            ];
            return ['success' => true, "json" => $json];
        } catch (Exception $e) {
            return ['success' => false, "message" => "Error al obtener los datos", $e->getMessage()];
        }
    }
    // funcion para obtener el token para el envio  de la guia de remision
    public function ObtenerTokenAutenticacion($emisor, $conn = null)
    {
        try {
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            // Verificar si ya hay un token vigente
            $query = $conn->prepare("SELECT * FROM sunat_tokens WHERE emisor_ruc = :ruc ORDER BY updated_at DESC LIMIT 1");
            $query->bindParam(':ruc', $emisor['ruc']);
            $query->execute();
            $tokenData = $query->fetch(PDO::FETCH_ASSOC);

            if ($tokenData) {
                $fechaCreacion = strtotime($tokenData['created_at']);
                $expiraEn = $tokenData['expires_in'];
                $ahora = time();

                if (($fechaCreacion + $expiraEn) > $ahora) {
                    // Token sigue vigente
                    return [
                        "success" => true,
                        "token" => $tokenData['access_token']
                    ];
                }
            }

            $nubefact_ws = "https://gre-test.nubefact.com/v1/clientessol/" . $emisor['api_id'] . "/oauth2/token/";
            $sunat_ws = "https://api-seguridad.sunat.gob.pe/v1/clientessol/" . $emisor['api_id'] . "/oauth2/token/";
            // No hay token vigente, solicitar uno nuevo

            $ws = $this->modo_sistema == 1 ? $sunat_ws : $nubefact_ws;

            $header = array(
                "Content-type: application/x-www-form-urlencoded"
            );

            $datos_envio = array(
                "grant_type" => "password",
                "scope" => "https://api-cpe.sunat.gob.pe",
                "client_id" => $emisor['api_id'],
                "client_secret" => $emisor['api_clave'],
                "username" => $emisor['ruc'] . $emisor['usuario_sol'],
                "password" => $emisor['clave_sol']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $ws);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos_envio));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpcode == 200) {
                $token = json_decode($response, true);
                // Guardar o actualizar el token en la base de datos
                if ($tokenData) {
                    $query = $conn->prepare("UPDATE sunat_tokens
                        SET access_token = :access_token, token_type = :token_type, expires_in = :expires_in, updated_at = NOW()
                        WHERE id = :id");
                    $query->bindParam(':access_token', $token['access_token']);
                    $query->bindParam(':token_type', $token['token_type']);
                    $query->bindParam(':expires_in', $token['expires_in']);
                    $query->bindParam(':id', $tokenData['id']);
                    $query->execute();
                } else {
                    $query = $conn->prepare("INSERT INTO sunat_tokens (emisor_ruc, access_token, token_type, expires_in, created_at, updated_at)
                        VALUES (:ruc, :access_token, :token_type, :expires_in, NOW(), NOW())");
                    $query->bindParam(':ruc', $emisor['ruc']);
                    $query->bindParam(':access_token', $token['access_token']);
                    $query->bindParam(':token_type', $token['token_type']);
                    $query->bindParam(':expires_in', $token['expires_in']);
                    $query->execute();
                }

                return [
                    "success" => true,
                    "token" => $token['access_token']
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Error al obtener el token de autenticación. Código HTTP: " . $httpcode
                ];
            }
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error en la base de datos: " . $e->getMessage()
            ];
        }
    }

    public function get_correlativo_notas($data)
    {
        $query = $this->db->connect()->prepare("SELECT COUNT(id_nota)+1 AS cantidad FROM notas WHERE serie=:serie");
        $query->bindParam(':serie', $data);
        $query->execute();
        return $query->fetch((PDO::FETCH_ASSOC))["cantidad"];
    }

    public function add_nota($data, $id_venta, $resp, $tp_c, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            $query = $conn->prepare("
            INSERT INTO notas (
            serie, correlativo, codmotivo, descripcion, serie_ref,
            correlativo_ref, id_comprobante, xml, cdr, fecha_emision, estado_sunat, mensaje_sunat, op_gravadas, op_exoneradas, op_inafectas, igv, total, forma_pago, hash_cpe, cliente, id_tp_comprobante) VALUES(
            :serie, :correlativo, :codmotivo, :descripcion, :serie_ref,
            :correlativo_ref, :id_venta, :xml,:cdr, :fecha_emision, :estado_envio, :mensaje_sunat, :op_gravada, :op_exonerada, :op_inafecta, :igv, :total, :forma_pago, :hash_cpe, :cliente, :id_tp_c
            )");
            $query->bindParam(":serie", $data['cabecera']['serie']);
            $query->bindParam(":correlativo", $data['cabecera']['correlativo']);
            $query->bindParam(":codmotivo", $data['cabecera']['codmotivo']);
            $query->bindParam(":descripcion", $data['cabecera']['descripcion']);
            $query->bindParam(":serie_ref", $data['cabecera']['serie_ref']);
            $query->bindParam(":correlativo_ref", $data['cabecera']['correlativo_ref']);
            $query->bindParam(":id_venta", $id_venta);
            $query->bindParam(":xml", $resp[0]["xml"]);
            $query->bindParam(":cdr", $resp[0]["cdr"]);
            $query->bindParam(":fecha_emision", $data['cabecera']['fecha_emision']);
            $query->bindParam(":estado_envio", $resp[0]["estado"]);
            $query->bindparam(":mensaje_sunat", $resp[0]["mensaje_sunat"]);
            $query->bindparam(":hash_cpe", $resp[0]["hash_cpe"]);
            $query->bindParam(":op_exonerada", $data["cabecera"]["total_op_exoneradas"]);
            $query->bindParam(":op_gravada", $data["cabecera"]["total_op_gravadas"]);
            $query->bindParam(":op_inafecta", $data["cabecera"]["total_op_inafectas"]);
            $query->bindParam(":igv", $data["cabecera"]["igv"]);
            $query->bindParam(":forma_pago", $data["cabecera"]["forma_pago"]);
            $query->bindParam(":total", $data["cabecera"]["total_a_pagar"]);
            $query->bindParam(":cliente", $data["cliente"]["razon_social"]);
            $query->bindParam(":id_tp_c", $tp_c);
            $query->execute();
            $id_nota = $conn->lastInsertId();
            return ['success' => true, 'message' => 'Nota registrada correctamente', 'id_nota' => $id_nota];
        } catch (PDOException $e) {
            return ["Error: " . $e->getMessage()];
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

}
