<?php
class Listado_FacturadorModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene datos para DataTable de comprobantes
     */
    public function getDataTable($data)
    {
        try {
            $selectFields = "v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
            c.num_docu AS ruc_cliente,
            v.id_forma_pago,
            v.id_medio_pago,
            v.id_tp_moneda,
            v.id_tp_comprobante,
            tp_c.descripcion AS tipo_comprobante,
            tp_c.codigo AS tipo_comprobante_codigo,
            v.id_caja_chica,
            v.id_serie,
            v.serie,
            v.correlativo,
            v.descuento,
            v.op_igv,
            v.icbper,
            v.estado,
            v.envio_sunat,
            v.descrip_cdr_sunat,
            v.hash_cdr,
            v.file_xml,
            v.file_cdr,
            v.fecha_emision,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            v.id_tp_venta,
            f_p.descripcion AS forma_pago,
            m_p.descripcion AS medio_pago,
            tp_m.descripcion AS moneda,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y %H:%i') AS fecha_emision_format,
            CONCAT(v.serie, '-', v.correlativo) AS serie_correlativo,
            v.afecta_detraccion";

            $baseQuery = " FROM venta v
            LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
            WHERE v.id_tp_venta = 3 
            AND v.id_tp_comprobante IN (1,2,3)";

            $searchColumns = [
                "v.fecha_emision",
                "CONCAT(c.nombres, ' ', c.apellidos)",
                "c.nombres",
                "c.apellidos",
                "c.num_docu",
                "v.serie",
                "v.correlativo",
                "CONCAT(v.serie, '-', v.correlativo)",
                "v.estado",
                "tp_c.descripcion",
                "f_p.descripcion",
                "v.total",
                "CASE v.envio_sunat 
                WHEN 0 THEN 'PENDIENTE' 
                WHEN 1 THEN 'ACEPTADO' 
                WHEN 2 THEN 'RECHAZADO' 
                WHEN 3 THEN 'EXCEPCIÓN' 
                ELSE 'DESCONOCIDO' END"
            ];

            $orderBy = "v.fecha_emision DESC";

            // Luego ejecutar la consulta normal
            $result = $this->runBasicDataTableQuery($data, $baseQuery, $searchColumns, $selectFields, $orderBy);

            return $result;

        } catch (PDOException $e) {

            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    private function runBasicDataTableQuery($data, $baseQuery, $searchColumns, $selectFields, $orderBy)
    {
        $start = intval($data['start'] ?? 0);
        $length = intval($data['length'] ?? 10); // CORREGIDO: tenía un error aquí
        $searchValue = $data['search']['value'] ?? '';

        error_log("=== RUN BASIC QUERY START ===");
        error_log("Start: $start, Length: $length, Search: '$searchValue'");

        // Contar total de registros
        $countQuery = "SELECT COUNT(*) as total" . $baseQuery;
        error_log("Count query: $countQuery");

        try {
            $stmt = $this->db->connect()->prepare($countQuery);
            $stmt->execute();
            $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            error_log("Total records: $totalRecords");
        } catch (PDOException $e) {
            error_log("Error counting total: " . $e->getMessage());
            $totalRecords = 0;
        }

        // Construir WHERE para búsqueda
        $whereConditions = [];
        $params = [];

        // Filtrar por búsqueda global
        if (!empty($searchValue)) {
            $searchConditions = [];
            foreach ($searchColumns as $index => $column) {
                $searchConditions[] = "$column LIKE :search$index";
                $params[":search$index"] = "%$searchValue%";
            }
            $whereConditions[] = "(" . implode(" OR ", $searchConditions) . ")";
            error_log("Search conditions added for: '$searchValue'");
        }

        // Aplicar filtros adicionales si los hay
        if (isset($data['filtro_fecha_desde']) && !empty($data['filtro_fecha_desde'])) {
            $whereConditions[] = "DATE(v.fecha_emision) >= :fecha_desde";
            $params[':fecha_desde'] = $data['filtro_fecha_desde'];
            error_log("Filtro fecha desde: " . $data['filtro_fecha_desde']);
        }

        if (isset($data['filtro_fecha_hasta']) && !empty($data['filtro_fecha_hasta'])) {
            $whereConditions[] = "DATE(v.fecha_emision) <= :fecha_hasta";
            $params[':fecha_hasta'] = $data['filtro_fecha_hasta'];
            error_log("Filtro fecha hasta: " . $data['filtro_fecha_hasta']);
        }

        if (isset($data['filtro_tipo_comprobante']) && !empty($data['filtro_tipo_comprobante'])) {
            $whereConditions[] = "v.id_tp_comprobante = :tipo_comprobante";
            $params[':tipo_comprobante'] = $data['filtro_tipo_comprobante'];
            error_log("Filtro tipo comprobante: " . $data['filtro_tipo_comprobante']);
        }

        if (isset($data['filtro_estado']) && !empty($data['filtro_estado'])) {
            $whereConditions[] = "v.estado = :estado";
            $params[':estado'] = $data['filtro_estado'];
            error_log("Filtro estado: " . $data['filtro_estado']);
        }

        // Construir WHERE completo
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = " AND " . implode(" AND ", $whereConditions);
        }
        error_log("Where clause: $whereClause");
        error_log("Params: " . print_r($params, true));

        // Contar registros filtrados
        $countFilteredQuery = "SELECT COUNT(*) as total" . $baseQuery . $whereClause;
        error_log("Filtered count query: $countFilteredQuery");

        $filteredRecords = 0;
        try {
            $stmt = $this->db->connect()->prepare($countFilteredQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $filteredRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            error_log("Filtered records: $filteredRecords");
        } catch (PDOException $e) {
            error_log("Error counting filtered: " . $e->getMessage());
        }

        // Consulta principal con límites
        $mainQuery = "SELECT " . $selectFields . $baseQuery . $whereClause . " ORDER BY " . $orderBy . " LIMIT :start, :length";
        error_log("Main query: $mainQuery");

        $results = [];
        try {
            $stmt = $this->db->connect()->prepare($mainQuery);

            // Bind de parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Results found: " . count($results));
            error_log("Sample result: " . (count($results) > 0 ? print_r($results[0], true) : 'No results'));
        } catch (PDOException $e) {
            error_log("Error in main query: " . $e->getMessage());
        }

        // Formatear los datos según lo espera el DataTable
        $formattedData = [];
        foreach ($results as $row) {
            // Si está anulado, poner todos los montos en 0
            $esAnulado = ($row['estado'] === 'ANULADO');

            $formattedData[] = [
                'id_venta' => $row['id_venta'] ?? 0,
                'fecha_emision' => $row['fecha_emision_format'] ?? '',
                'serie' => $row['serie'] ?? '',
                'correlativo' => $row['correlativo'] ?? '',
                'cliente' => $row['cliente'] ?? 'SIN CLIENTE',
                'ruc_cliente' => $row['ruc_cliente'] ?? '',
                'tipo_comprobante' => $row['tipo_comprobante'] ?? '',
                'op_gravada' => $esAnulado ? 0 : ($row['op_gravada'] ?? 0),
                'op_exonerada' => $esAnulado ? 0 : ($row['op_exonerada'] ?? 0),
                'op_inafecta' => $esAnulado ? 0 : ($row['op_inafecta'] ?? 0),
                'op_igv' => $esAnulado ? 0 : ($row['op_igv'] ?? 0),
                'icbper' => $esAnulado ? 0 : ($row['icbper'] ?? 0),
                'total' => $esAnulado ? 0 : ($row['total'] ?? 0),
                'estado' => $row['estado'] ?? '',
                'envio_sunat' => $row['envio_sunat'] ?? 0,
                'descrip_cdr_sunat' => $row['descrip_cdr_sunat'] ?? '',
                'afecta_detraccion' => $row['afecta_detraccion'] ?? 0,
                'hash_cdr' => $row['hash_cdr'] ?? '',
                'id_tp_comprobante' => $row['id_tp_comprobante'] ?? 0,
                'file_xml' => $row['file_xml'] ?? null,
                'file_cdr' => $row['file_cdr'] ?? null
            ];
        }

        $response = [
            "draw" => intval($data['draw'] ?? 1),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $formattedData
        ];

        error_log("Response: " . print_r($response, true));
        error_log("=== RUN BASIC QUERY END ===");

        return $response;
    }

    public function get_data_comprobante($id_comprobante)
    {
        try {
            // Reutilizar la función del FacturadorModel
            // Si no puedes acceder directamente, copia la lógica necesaria
            $query = $this->db->connect()->prepare("
            SELECT e.*, 
                   t.nombre AS terminal_nombre,
                   e.fr_comprobante,
                   e.telefono_empresa,
                   e.nro_cuenta_bancaria,
                   e.termscond_facturador
            FROM empresa e
            LEFT JOIN terminal t ON t.id_empresa = e.id_empresa
            WHERE t.id_terminal = :id_terminal
            LIMIT 1
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            // Obtener terminales con su celular
            $queryTerminales = $this->db->connect()->prepare("
            SELECT id_terminal, nombre, direccion_fiscal, celular
            FROM terminal
            WHERE id_empresa = :id_empresa
            ");
            $queryTerminales->bindParam(":id_empresa", $emisor['id_empresa']);
            $queryTerminales->execute();
            $terminales = $queryTerminales->fetchAll(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
                SELECT
                    v.id_venta,
                    v.id_terminal,
                    v.serie,
                    v.correlativo,
                    v.fecha_emision,
                    DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') AS fecha_emision_format,
                    DATE_FORMAT(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
                    v.id_forma_pago,
                    f_p.descripcion AS forma_pago,
                    v.id_medio_pago,
                    m_p.descripcion AS medio_pago,
                    v.id_tp_moneda,
                    tp_m.codigo AS moneda_codigo,
                    tp_m.descripcion AS moneda_descripcion,
                    v.id_tp_comprobante,
                    tp_c.descripcion AS tipo_comprobante,
                    tp_c.codigo AS tipo_comprobante_codigo,
                    v.op_igv,
                    v.op_gravada,
                    v.op_exonerada,
                    v.op_inafecta,
                    v.total,
                    v.estado,
                    v.envio_sunat,
                    v.descrip_cdr_sunat,
                    v.hash_cdr,
                    v.afecta_detraccion,
                    v.obs,
                    v.cod_qr,
                    v.id_cliente
                FROM venta v
                LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
                WHERE v.id_venta = :id_comprobante
            ");
            $query->bindParam(":id_comprobante", $id_comprobante);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
                SELECT
                    u.nombres,
                    u.apellidos,
                    u.num_docu,
                    tp_d.descripcion AS tipo_documento,
                    u.direccion,
                    u.celular,
                    u.email
                FROM usuario u
                LEFT JOIN tp_docu tp_d ON tp_d.id_tp_docu = u.id_tp_docu
                WHERE u.id_usuario = :id_cliente
            ");
            $query->bindParam(":id_cliente", $cabecera['id_cliente']);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
                SELECT
                    dc.item,
                    dc.cantidad,
                    p.nombre AS producto_nombre,
                    p.descripcion AS producto_descripcion,
                    u.abreviatura AS unidad_medida,
                    dc.valor_unitario,
                    dc.precio_unitario,
                    dc.igv,
                    dc.icbper,
                    dc.importe_total,
                    af.nombre AS afectacion_nombre
                FROM detalle_comprobante dc
                LEFT JOIN producto p ON p.id = dc.producto_id
                LEFT JOIN unidad_medida u ON u.id = p.unidad_id
                LEFT JOIN afectaciones_igv af ON af.id = dc.afectacion_id
                WHERE dc.comprobante_id = :id_comprobante
                ORDER BY dc.item ASC
            ");
            $query->bindParam(":id_comprobante", $id_comprobante);
            $query->execute();
            $items = $query->fetchAll(PDO::FETCH_ASSOC);

            // Si hay detracción
            $detraccion = [];
            if ($cabecera['afecta_detraccion'] == 1) {
                $query = $this->db->connect()->prepare("
                    SELECT *
                    FROM detraccion_operacion
                    WHERE comprobante_id = :id_comprobante
                ");
                $query->bindParam(":id_comprobante", $id_comprobante);
                $query->execute();
                $detraccion = $query->fetch(PDO::FETCH_ASSOC);
            }

            return [
                'success' => true,
                'data' => [
                    'emisor' => $emisor,
                    'cabecera' => $cabecera,
                    'cliente' => $cliente,
                    'items' => $items,
                    'detraccion' => $detraccion,
                    'TERMINALES' => $terminales // Agregado para incluir los terminales con su celular
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener datos del comprobante: ' . $e->getMessage()
            ];
        }
    }

    // En Listado_FacturadorModel.php
    public function get_data_comprobante_facturador($id_comprobante = 0)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT *, 
               telefono_empresa,
               fr_comprobante,
               nro_cuenta_bancaria,
               termscond_facturador
               FROM empresa
            ");
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            $queryTerminales = $this->db->connect()->prepare("
             SELECT id_terminal, nombre, direccion_fiscal, celular
            FROM terminal
            WHERE id_empresa = :id_empresa
            ");
            $queryTerminales->bindParam(":id_empresa", $emisor['id_empresa']);
            $queryTerminales->execute();
            $terminales = $queryTerminales->fetchAll(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
            SELECT
            vd.nombres AS vendedor_nombres,
            vd.apellidos AS vendedor_apellidos,
            v.id_terminal,
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
            v.estado,
            v.icbper,
            v.fecha_registro,
            v.id_cliente,
            v.cod_qr,
            v.hash_cdr,
            v.afecta_detraccion,
            v.obs
            FROM venta v
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
            LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
            LEFT JOIN usuario vd ON vd.id_usuario = v.id_vendedor
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $id_comprobante);
            $query->execute();
            $data_venta = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("SELECT
            CONCAT(c.nombres,' ',c.apellidos ) AS nombres_cliente,
            c.id_tp_docu AS tipo_documento,
            c.num_docu AS num_docu,
            c.direccion AS direccion,
            -- Dirección con ubigeo SI EXISTE
            CASE 
                WHEN c.direccion IS NOT NULL AND c.direccion != '' 
                     AND ub_c.distri IS NOT NULL AND ub_c.distri != ''
                THEN CONCAT(
                    c.direccion,
                    ' - ',
                    COALESCE(ub_c.distri, ''),
                    ', ',
                    COALESCE(ub_c.provi, ''),
                    ', ',
                    COALESCE(ub_c.depa, '')
                )
                -- Si no hay ubigeo, usar solo la dirección
                WHEN c.direccion IS NOT NULL AND c.direccion != ''
                THEN c.direccion
                -- Si no hay dirección, mostrar mensaje
                ELSE 'SIN DIRECCIÓN REGISTRADA'
            END AS direccion_completa,
            c.ubigeo AS ubigeo_cliente,
            tp_d_c.descripcion AS tipo_doc_descripcion,
            COALESCE(ub_c.depa, '') AS ubigeo_depa,
            COALESCE(ub_c.provi, '') AS ubigeo_provi,
            COALESCE(ub_c.distri, '') AS ubigeo_distri
            FROM usuario c
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo = c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu = c.id_tp_docu
            WHERE c.id_usuario = :id_usuario");
            $query->bindParam(":id_usuario", $data_venta['id_cliente']);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);
            $cliente['pais'] = 'PE';

            $query = $this->db->connect()->prepare("
            SELECT
            dt_c.*,
            p.nombre,
            p.descripcion,
            p.codigo_interno,
            u_m.abreviatura AS unidad_medida,
            af.letra,
            af.cod_afectacion_sunat,
            af.codigo,
            af.nombre AS nombre_afectacion,
            af.tipo AS tipo_afectacion
            FROM detalle_comprobante dt_c
            LEFT JOIN producto p ON dt_c.producto_id = p.id
            LEFT JOIN unidad_medida u_m ON p.unidad_id = u_m.id
            LEFT JOIN afectaciones_igv af ON p.tipo_afectacion_id = af.id
            WHERE dt_c.comprobante_id=:id_comprobante
            ORDER BY dt_c.item ASC");
            $query->bindParam(":id_comprobante", $id_comprobante);
            $query->execute();
            $productos = $query->fetchAll(PDO::FETCH_ASSOC);

            $data_detraccion = [];
            if ($data_venta['afecta_detraccion'] == 1) {
                $query = $this->db->connect()->prepare("
            SELECT 
            d.ubigeo_origen,
            d.ubigeo_destino,
            d.porcentaje,
            d.porcentaje AS porcentaje_operacion,
            tp_d.porcentaje AS porcentaje_decimal,
            d.origen_detraccion,
            d.destino_detraccion,
            d.motivo AS descripcion,
            d.monto_operacion,
            d.valor_referencial,
            d.base_detraccion,
            d.v_ref_carga_efectiva,
            d.v_ref_carga_util,
            d.monto_detraccion,
            d.ruta_origen,
            d.ruta_destino,
            tp_d.codigo AS tipo_detraccion_codigo,
            tp_d.descripcion AS tipo_detraccion_descripcion,
            m.descripcion AS medio_pago_descripcion,
            m.codigo_sunat AS medio_pago_codigo
            FROM detraccion_operacion d
            LEFT JOIN tp_detraccion tp_d ON tp_d.id = d.tp_detraccion_id
            LEFT JOIN tp_medio_pago m ON m.id = d.id_medio_pago
            WHERE d.comprobante_id = :id_venta");
                $query->bindParam(":id_venta", $id_comprobante);
                $query->execute();
                $data_detraccion = $query->fetch(PDO::FETCH_ASSOC);

                if (!empty($emisor['cuenta_detraccion'])) {
                    $data_detraccion['cuenta_detraccion'] = $emisor['cuenta_detraccion'];
                }
            }

            $respuesta = [
                "emisor" => $emisor,
                "cliente" => $cliente,
                "data_venta" => $data_venta,
                "productos" => $productos,
                "data_detraccion" => $data_detraccion,
                "TERMINALES" => $terminales
            ];

            return ['success' => true, 'message' => $respuesta];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "No se ha podido obtener la data de la venta: " . $e->getMessage()];
        }
    }

    public function reenviar_sunat($data)
    {
        try {
            $id_comprobante = $data['id_comprobante'];

            // Obtener datos para reenvío
            $comprobanteData = $this->get_data_comprobante_sunat($id_comprobante);

            if (!$comprobanteData['success']) {
                return ['success' => false, 'message' => 'Error al obtener datos para reenvío'];
            }

            // Enviar a SUNAT
            $json_envio = json_encode($comprobanteData['message']);
            $api_url = API_URL; // Asegúrate de tener definida esta constante

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_envio);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_envio)
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $sunatResponse = json_decode($response, true);

            if (empty($sunatResponse) || !is_array($sunatResponse)) {
                return ['success' => false, 'message' => 'Error en la respuesta de SUNAT'];
            }

            $resp = $sunatResponse[0];

            // Actualizar en base de datos
            $query = $this->db->connect()->prepare("
                UPDATE venta SET
                    envio_sunat = :estado,
                    descrip_cdr_sunat = :mensaje,
                    hash_cdr = :hash_cpe,
                    file_xml = :xml,
                    file_cdr = :cdr
                WHERE id_venta = :id_venta
            ");

            $query->bindParam(':estado', $resp['estado']);
            $query->bindParam(':mensaje', $resp['mensaje_sunat']);
            $query->bindParam(':hash_cpe', $resp['hash_cpe']);
            $query->bindParam(':xml', $resp['xml']);
            $query->bindParam(':cdr', $resp['cdr']);
            $query->bindParam(':id_venta', $id_comprobante);
            $query->execute();

            return [
                'success' => true,
                'message' => 'Comprobante reenviado correctamente',
                'estado_sunat' => $resp['estado'],
                'mensaje_sunat' => $resp['mensaje_sunat']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al reenviar: ' . $e->getMessage()
            ];
        }
    }

    public function anular_comprobante($id_comprobante, $motivo)
    {
        try {
            // Verificar si el comprobante puede ser anulado
            $query = $this->db->connect()->prepare("
            SELECT estado, envio_sunat, id_tp_comprobante, serie, correlativo 
            FROM venta 
            WHERE id_venta = :id_venta
        ");
            $query->bindParam(':id_venta', $id_comprobante);
            $query->execute();
            $comprobante = $query->fetch(PDO::FETCH_ASSOC);

            if (!$comprobante) {
                return [
                    'success' => false,
                    'message' => 'Comprobante no encontrado'
                ];
            }

            if ($comprobante['estado'] == 'ANULADO') {
                return [
                    'success' => false,
                    'message' => 'El comprobante ya está anulado'
                ];
            }

            // Para comprobantes electrónicos (Factura=1, Boleta=3) enviados a SUNAT
            if (in_array($comprobante['id_tp_comprobante'], [1, 3]) && $comprobante['envio_sunat'] == 1) {
                // Lógica de anulación SUNAT aquí (opcional por ahora)
                // return ['success' => false, 'message' => 'Para anular comprobantes electrónicos se requiere proceso SUNAT'];
            }

            // Actualizar estado y poner total en 0
            $query = $this->db->connect()->prepare("
            UPDATE venta SET
                estado = 'ANULADO',
                total = 0,
                op_gravada = 0,
                op_exonerada = 0,
                op_inafecta = 0,
                op_igv = 0,
                icbper = 0,
                descuento = 0,
                obs = CONCAT(IFNULL(obs, ''), ' | ANULADO: ', :motivo)
            WHERE id_venta = :id_venta
        ");
            $query->bindParam(':id_venta', $id_comprobante);
            $query->bindParam(':motivo', $motivo);

            if ($query->execute()) {
                return [
                    'success' => true,
                    'message' => 'Comprobante anulado correctamente. Los importes se han establecido en 0.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar en base de datos'
                ];
            }

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al anular: ' . $e->getMessage()
            ];
        }
    }

    public function get_detalle_comprobante($data)
    {
        try {
            $id_comprobante = $data['id_comprobante'];

            $query = $this->db->connect()->prepare("
                SELECT 
                    v.*,
                    tp_c.descripcion AS tipo_comprobante,
                    f_p.descripcion AS forma_pago,
                    m_p.descripcion AS medio_pago,
                    CONCAT(u.nombres, ' ', u.apellidos) AS cliente,
                    u.num_docu AS cliente_documento,
                    DATE_FORMAT(v.fecha_emision, '%d/%m/%Y %H:%i:%s') AS fecha_emision_format
                FROM venta v
                LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN usuario u ON u.id_usuario = v.id_cliente
                WHERE v.id_venta = :id_comprobante
            ");
            $query->bindParam(':id_comprobante', $id_comprobante);
            $query->execute();

            $comprobante = $query->fetch(PDO::FETCH_ASSOC);

            if ($comprobante) {
                return [
                    'success' => true,
                    'data' => $comprobante
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Comprobante no encontrado'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener detalle: ' . $e->getMessage()
            ];
        }
    }

    // Agregar después de get_detalle_comprobante()

    public function get_comprobante_nota($id_venta)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT 
                v.id_venta,
                v.serie, 
                v.correlativo, 
                v.op_igv AS igv,
                v.op_gravada, 
                v.op_exonerada, 
                v.op_inafecta,
                v.total,
                v.descuento,
                tp_c.codigo,
                CONCAT(u.nombres, ' ', u.apellidos) AS cliente,
                tp_m.descripcion AS moneda
            FROM venta v 
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN usuario u ON u.id_usuario = v.id_cliente
            LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
            WHERE v.id_venta = :id_venta
        ");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function get_items_encomienda($id_venta)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT 
                pe.*, 
                pe.op_igv AS igv, 
                pe.op_total AS total,
                ct.afectacion AS afectacion, 
                v.descuento, 
                v.id_venta,
                'Producto' AS descripcion,
                pe.op_gravada,
                pe.op_exonerada,
                pe.op_inafecta
            FROM producto_encomienda pe
            LEFT JOIN ctg_encomienda ct ON pe.id_ctg_encomienda = ct.id_ctg_encomienda
            LEFT JOIN dt_venta dv ON pe.id_encomienda = dv.id_encomienda
            LEFT JOIN venta v ON dv.id_venta = v.id_venta
            WHERE pe.id_encomienda IN (
                SELECT id_encomienda
                FROM dt_venta
                WHERE id_venta = :id_venta
            )
        ");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function motivos_notas()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM tabla_parametrica WHERE tipo IN ('C', 'D')");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function get_serieForTpComprobante($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT * FROM serie 
            WHERE id_tp_comprobante = :id_tp_comprobante 
            AND id_terminal = :id_terminal
        ");
            $query->bindParam(":id_tp_comprobante", $data["tp_comprobante"]);
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($reply as &$registro) {
                $registro['correlativo'] = $registro['correlativo'] ? ($registro['correlativo'] + 1) : 1;
            }

            return $reply;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function set_data_notas($data)
    {
        try {
            // DEBUG: Log datos recibidos
            error_log("=== DATOS RECIBIDOS EN set_data_notas ===");
            error_log(print_r($data, true));

            // 1. Obtener datos del comprobante original (IGUAL QUE COMPROBANTESMODEL)
            $id_venta_original = $data['id_venta'];
            $tp_comp = substr($data['list_comp'], 1); // CORRECCIÓN: Igual que ComprobantesModel línea 38

            $tipo_nota = $data['list_comp']; // '07' o '08'
            $serie_nota = $data['txtserie_nota'];
            $correlativo_nota = $data['txtcorrelativo_nota'] ?? '';
            $motivo_nota = $data['list_motivo'] ?? '';
            $descripcion = $data['txt_descripcion'] ?? '';
            $fecha_emision = $data['txt_fecha'] ?? date('Y-m-d');

            // 2. Obtener emisor (IGUAL QUE COMPROBANTESMODEL)
            $emisor = $this->consult_emisor();

            // CORRECCIÓN: Obtener correlativo correctamente
            $correlativo = $correlativo_nota; // Ya viene del frontend

            // 3. Obtener datos del cliente (IGUAL QUE COMPROBANTESMODEL)
            $query = $this->db->connect()->prepare("
            SELECT
                c.id_tp_docu AS tipo_documento,
                c.num_docu AS ruc,
                CONCAT(c.nombres,' ',c.apellidos) AS razon_social,
                c.direccion AS direccion,
                c.ubigeo AS cliente_ubigeo
            FROM venta v
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
            LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
            WHERE v.id_venta=:id_venta
        ");
            $query->bindParam(":id_venta", $id_venta_original);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            // CORRECCIÓN: Agregar campo pais igual que ComprobantesModel
            $cliente['pais'] = 'PE';

            // 4. Obtener cabecera del comprobante original (IGUAL QUE COMPROBANTESMODEL)
            $query = $this->db->connect()->prepare("
            SELECT
                v.serie AS serie_ref,
                v.correlativo AS correlativo_ref,
                f_p.descripcion AS forma_pago,
                tp_m.codigo AS moneda,
                tp_c.codigo AS tipo_comprobante_ref_id
            FROM venta v
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago= v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago= v.id_medio_pago
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta= v.id_venta
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
            LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=v.id_tp_moneda
            LEFT JOIN usuario vd ON vd.id_usuario=v.id_vendedor
            LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            WHERE v.id_venta=:id_venta
        ");
            $query->bindParam(":id_venta", $id_venta_original);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            // 5. Obtener productos
            $productos = isset($data['productos']) && is_array($data['productos']) ? $data['productos'] : [];

            // Si no hay productos específicos, usar datos del comprobante original
            if (empty($productos)) {
                // Obtener datos básicos del comprobante
                $comp_data = $this->get_comprobante_nota($id_venta_original);
                if ($comp_data && !isset($comp_data['error'])) {
                    $productos[] = [
                        'descripcion' => 'Nota de ' . ($tipo_nota == '07' ? 'Crédito' : 'Débito'),
                        'op_exonerada' => $comp_data['op_exonerada'] ?? 0,
                        'op_gravada' => $comp_data['op_gravada'] ?? 0,
                        'op_inafecta' => $comp_data['op_inafecta'] ?? 0,
                        'descuento' => $comp_data['descuento'] ?? 0,
                        'igv' => $comp_data['igv'] ?? 0,
                        'total' => $comp_data['total'] ?? 0
                    ];
                }
            }

            // 6. Calcular totales
            $total_gravada = 0;
            $total_exonerada = 0;
            $total_inafecta = 0;
            $total_igv = 0;
            $total_general = 0;

            foreach ($productos as $producto) {
                $total_gravada += floatval($producto['op_gravada'] ?? 0);
                $total_exonerada += floatval($producto['op_exonerada'] ?? 0);
                $total_inafecta += floatval($producto['op_inafecta'] ?? 0);
                $total_igv += floatval($producto['igv'] ?? 0);
                $total_general += floatval($producto['total'] ?? 0);
            }

            // 7. Determinar afectación (IGUAL QUE COMPROBANTESMODEL)
            $afectacion = null;
            if ($data['list_comp'] == "08" && $total_igv == 0) {
                $afectacion = 0.00;
            } else {
                $afectacion = 18;
            }

            // 8. Construir items (IGUAL QUE COMPROBANTESMODEL)
            $items = null;

            if ($total_igv > 0) {
                $codigos = array("S", "10", "1000", "IGV", "VAT");

                for ($i = 0; $i < count($productos); $i++) {
                    $items[$i]["item"] = ($i + 1);
                    $items[$i]['porcentaje_igv'] = $afectacion;
                    $items[$i]['codigo'] = 0;
                    $items[$i]['igv'] = $productos[$i]['igv'];
                    $items[$i]['precio_lista'] = $productos[$i]['total'];
                    $items[$i]['valor_total'] = $productos[$i]['op_gravada'];
                    $items[$i]['valor_unitario'] = $productos[$i]['op_gravada'];
                    $items[$i]['total_impuestos'] = $productos[$i]['igv'];
                    $items[$i]['total'] = $productos[$i]['total'];
                    $items[$i]['icbper'] = 0.00;
                    $items[$i]['factor_icbper'] = 0.00;
                    $items[$i]['unidad'] = "ZZ";
                    $items[$i]['nombre'] = $productos[$i]['descripcion'];
                    $items[$i]['total_antes_impuestos'] = $productos[$i]['op_gravada'];
                    $items[$i]['tipo_precio'] = "01";
                    $items[$i]['codigos'] = $codigos;
                    $items[$i]['cantidad'] = 1;
                }

                $suma_igv = $total_igv;
                $suma_opigv = $total_gravada;
                $total_a_pagar = $suma_opigv + $suma_igv;

                // Cabecera para productos con IGV
                $cabecera['tipo_comprobante'] = $data['list_comp'];
                $cabecera['igv'] = number_format($suma_igv, 2);
                $cabecera['total_op_gravadas'] = number_format($suma_opigv, 2);
                $cabecera['total_op_exoneradas'] = $total_exonerada;
                $cabecera['total_op_inafectas'] = $total_inafecta;
                $cabecera['descuento'] = $productos[0]['descuento'] ?? 0;
                $cabecera['total_a_pagar'] = number_format($total_a_pagar, 2);
                $cabecera['serie'] = $serie_nota;
                $cabecera['correlativo'] = $correlativo;
                $cabecera['fecha_emision'] = $fecha_emision;
                $cabecera['hora_emision'] = date('H:i:s');
                $cabecera['codmotivo'] = $motivo_nota;
                $cabecera['descripcion'] = $descripcion;
                $cabecera['anexo_sucursal'] = "0000";
                $cabecera['total_texto'] = $this->numtoletras($cabecera['total_a_pagar']);

            } else {
                $codigos = array("E", "20", "9997", "EXO", "VAT");

                for ($i = 0; $i < count($productos); $i++) {
                    $items[$i]["item"] = ($i + 1);
                    $items[$i]['porcentaje_igv'] = $afectacion;
                    $items[$i]['codigo'] = 0;
                    $items[$i]['igv'] = $productos[$i]['igv'];
                    $items[$i]['precio_lista'] = $productos[$i]['total'];
                    $items[$i]['valor_total'] = $productos[$i]['total'];
                    $items[$i]['valor_unitario'] = $productos[$i]['total'];
                    $items[$i]['total'] = $productos[$i]['total'];
                    $items[$i]['icbper'] = 0.00;
                    $items[$i]['factor_icbper'] = 0.00;
                    $items[$i]['unidad'] = "ZZ";
                    $items[$i]['nombre'] = $productos[$i]['descripcion'];
                    $items[$i]['total_antes_impuestos'] = $productos[$i]['total'];
                    $items[$i]['tipo_precio'] = "01";
                    $items[$i]['codigos'] = $codigos;
                    $items[$i]['cantidad'] = 1;
                }

                // Cabecera para productos exonerados
                $cabecera['tipo_comprobante'] = $data['list_comp'];
                $cabecera['igv'] = $productos[0]['igv'];
                $cabecera['total_op_gravadas'] = $productos[0]['op_gravada'];
                $cabecera['total_op_exoneradas'] = $productos[0]['op_exonerada'];
                $cabecera['total_op_inafectas'] = $productos[0]['op_inafecta'];
                $cabecera['descuento'] = $productos[0]['descuento'];
                $cabecera['total_a_pagar'] = $productos[0]['total'];
                $cabecera['serie'] = $serie_nota;
                $cabecera['correlativo'] = $correlativo;
                $cabecera['fecha_emision'] = $fecha_emision;
                $cabecera['hora_emision'] = "19:43:00";
                $cabecera['codmotivo'] = $motivo_nota;
                $cabecera['descripcion'] = $descripcion;
                $cabecera['anexo_sucursal'] = "0000";
                $cabecera['total_texto'] = $this->numtoletras($productos[0]['total']);
            }

            // 9. Construir JSON (IGUAL QUE COMPROBANTESMODEL)
            $json = array(
                "ose" => $emisor['ose'] ?? '',
                "emisor" => $emisor,
                "cliente" => $cliente,
                "cabecera" => $cabecera,
                "items" => $items
            );

            // DEBUG: Log del JSON
            error_log("=== JSON ENVIADO A SUNAT ===");
            error_log(json_encode($json, JSON_PRETTY_PRINT));

            // 10. Enviar a SUNAT
            $rpta_sunat = $this->enviar_json_a_api(json_encode($json));

            // DEBUG: Log de respuesta
            error_log("=== RESPUESTA DE SUNAT ===");
            error_log($rpta_sunat);

            $resp = json_decode($rpta_sunat, true);

            if (!$resp || !is_array($resp)) {
                throw new Exception('Respuesta inválida de SUNAT: ' . substr($rpta_sunat, 0, 200));
            }

            // 11. Procesar respuesta SUNAT
            if ($resp[0]["estado"] == "1" || $resp[0]["estado"] == "2") {
                // Insertar en tabla notas
                $this->add_nota_fact($json, $id_venta_original, $resp, $tp_comp);

                return [
                    "success" => true,
                    "message" => [
                        "estado_sunat" => $resp[0]["estado"] ?? 0,
                        "message_sunat" => $resp[0]["mensaje_sunat"] ?? ''
                    ],
                    "id_nota" => null, // Se obtiene del add_nota
                    "serie" => $serie_nota,
                    "correlativo" => $correlativo_nota,
                    "total" => $cabecera['total_a_pagar']
                ];

            } else {
                throw new Exception('SUNAT rechazó la nota: ' . ($resp[0]["mensaje_sunat"] ?? 'Error desconocido'));
            }

        } catch (Exception $e) {
            error_log("Error en set_data_notas: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Error al crear la nota: ' . $e->getMessage()
            ];
        }
    }

    public function consult_emisor($conn = null)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT e.*, 
                t.nombre AS terminal_nombre,
                e.telefono_empresa,
                e.nro_cuenta_bancaria,
                e.termscond_facturador
            FROM empresa e
            LEFT JOIN terminal t ON t.id_empresa = e.id_empresa
            WHERE t.id_terminal = :id_terminal
            LIMIT 1
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al consultar emisor: " . $e->getMessage());
            return [];
        }
    }

    // Método para enviar a SUNAT (igual que ComprobantesModel)
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

    private function numtoletras($numero)
    {
        require_once('public/plugins/print/num_letras.php');
        return numtoletras($numero);
    }

    public function getDataTableExport($data)
    {
        try {
            $selectFields = "v.fecha_emision,
            CONCAT(v.serie, '-', v.correlativo) AS comprobante,
            tp_c.descripcion AS tipo_comprobante,
            CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
            c.num_docu AS ruc_cliente,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.op_igv,
            v.icbper,
            v.total,
            v.estado,
            v.envio_sunat,
            v.descrip_cdr_sunat,
            v.afecta_detraccion";

            $baseQuery = " FROM venta v
            LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            WHERE v.id_tp_venta = 3 
            AND v.id_tp_comprobante IN (1,2,3)";

            $searchValue = $data['search']['value'] ?? '';

            // Construir WHERE para filtros
            $whereConditions = [];
            $params = [];

            // Filtros de fecha
            if (!empty($data['filtro_fecha_desde'])) {
                $whereConditions[] = "DATE(v.fecha_emision) >= :fecha_desde";
                $params[':fecha_desde'] = $data['filtro_fecha_desde'];
            }

            if (!empty($data['filtro_fecha_hasta'])) {
                $whereConditions[] = "DATE(v.fecha_emision) <= :fecha_hasta";
                $params[':fecha_hasta'] = $data['filtro_fecha_hasta'];
            }

            // Filtro tipo comprobante
            if (!empty($data['filtro_tipo_comprobante'])) {
                $whereConditions[] = "v.id_tp_comprobante = :tipo_comprobante";
                $params[':tipo_comprobante'] = $data['filtro_tipo_comprobante'];
            }

            // Filtro estado
            if (!empty($data['filtro_estado'])) {
                $whereConditions[] = "v.estado = :estado";
                $params[':estado'] = $data['filtro_estado'];
            }

            // Búsqueda global
            if (!empty($searchValue)) {
                $searchConditions = [];
                $searchColumns = [
                    "v.fecha_emision",
                    "CONCAT(c.nombres, ' ', c.apellidos)",
                    "c.num_docu",
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "v.estado",
                    "tp_c.descripcion"
                ];

                foreach ($searchColumns as $index => $column) {
                    $searchConditions[] = "$column LIKE :search$index";
                    $params[":search$index"] = "%$searchValue%";
                }
                $whereConditions[] = "(" . implode(" OR ", $searchConditions) . ")";
            }

            // Construir WHERE completo
            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = " AND " . implode(" AND ", $whereConditions);
            }

            // Consulta final
            $mainQuery = "SELECT " . $selectFields . $baseQuery . $whereClause . " ORDER BY v.fecha_emision DESC";

            $stmt = $this->db->connect()->prepare($mainQuery);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear datos
            $formattedData = [];
            foreach ($results as $row) {
                $formattedData[] = [
                    'fecha_emision' => $row['fecha_emision'] ?? '',
                    'serie' => explode('-', $row['comprobante'] ?? '')[0] ?? '',
                    'correlativo' => explode('-', $row['comprobante'] ?? '')[1] ?? '',
                    'tipo_comprobante' => $row['tipo_comprobante'] ?? '',
                    'cliente' => $row['cliente'] ?? 'SIN CLIENTE',
                    'ruc_cliente' => $row['ruc_cliente'] ?? '',
                    'op_gravada' => $row['op_gravada'] ?? 0,
                    'op_exonerada' => $row['op_exonerada'] ?? 0,
                    'op_inafecta' => $row['op_inafecta'] ?? 0,
                    'op_igv' => $row['op_igv'] ?? 0,
                    'icbper' => $row['icbper'] ?? 0,
                    'total' => $row['total'] ?? 0,
                    'estado' => $row['estado'] ?? '',
                    'envio_sunat' => $row['envio_sunat'] ?? 0,
                    'descrip_cdr_sunat' => $row['descrip_cdr_sunat'] ?? '',
                    'afecta_detraccion' => $row['afecta_detraccion'] ?? 0
                ];
            }

            return $formattedData;

        } catch (PDOException $e) {
            error_log("Error en getDataTableExport: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresa()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT e.*, 
                   CONCAT('" . $_SERVER['DOCUMENT_ROOT'] . "/tid-transporte/', e.logo) AS logo_absolute_path,
                   CASE WHEN e.logo IS NOT NULL AND e.logo != '' THEN 1 ELSE 0 END AS logo_exists
            FROM empresa e
            LIMIT 1
        ");
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en getEmpresa: " . $e->getMessage());
            return [];
        }
    }

    // Método add_nota actualizado
    private function add_nota_fact($data, $id_venta, $resp, $tp_c)
    {
        try {
            $query = $this->db->connect()->prepare("
            INSERT INTO notas (
                serie, correlativo, codmotivo, descripcion, serie_ref,
                correlativo_ref, id_comprobante, xml, cdr, fecha_emision, 
                estado_sunat, mensaje_sunat, op_gravadas, op_exoneradas, 
                op_inafectas, igv, total, forma_pago, hash_cpe, cliente, id_tp_comprobante
            ) VALUES(
                :serie, :correlativo, :codmotivo, :descripcion, :serie_ref,
                :correlativo_ref, :id_venta, :xml, :cdr, :fecha_emision, 
                :estado_envio, :mensaje_sunat, :op_gravada, :op_exonerada, 
                :op_inafecta, :igv, :total, :forma_pago, :hash_cpe, :cliente, :id_tp_c
            )
        ");

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

            // También insertar en venta para consistencia
            $id_nota_insertada = $this->db->connect()->lastInsertId();

            error_log("Nota insertada con ID: " . $id_nota_insertada);

            return $id_nota_insertada;

        } catch (PDOException $e) {
            error_log("Error en add_nota: " . $e->getMessage());
            throw new Exception('Error al insertar en tabla notas: ' . $e->getMessage());
        }
    }

    // MÉTODO PARA OBTENER CAJA CHICA (Actualizado)
    private function obtener_caja_chica_activa()
    {
        try {
            // Buscar caja chica activa
            $query = $this->db->connect()->prepare("
            SELECT id_caja_chica 
            FROM caja_chica 
            WHERE estado = 'A' 
            ORDER BY id_caja_chica DESC 
            LIMIT 1
        ");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['id_caja_chica']) {
                return $result['id_caja_chica'];
            }

            // Si no hay activa, buscar la última
            $query = $this->db->connect()->prepare("
            SELECT id_caja_chica 
            FROM caja_chica 
            ORDER BY id_caja_chica DESC 
            LIMIT 1
        ");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['id_caja_chica'] : null;

        } catch (Exception $e) {
            error_log("Error al obtener caja chica: " . $e->getMessage());
            return null;
        }
    }

    // MÉTODO PARA OBTENER PRODUCTO PARA NOTA (Actualizado)
    private function obtener_producto_nota()
    {
        try {
            // Buscar producto genérico para notas
            $query = $this->db->connect()->prepare("
            SELECT id 
            FROM producto 
            WHERE (nombre LIKE '%NOTA%' OR nombre LIKE '%AJUSTE%' OR codigo LIKE 'NOTA%') 
            AND estado = 1
            LIMIT 1
        ");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['id']) {
                return $result['id'];
            }

            // Si no existe, buscar cualquier producto activo
            $query = $this->db->connect()->prepare("
            SELECT id 
            FROM producto 
            WHERE estado = 1 
            ORDER BY id ASC 
            LIMIT 1
        ");
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['id'] : 1; // Valor por defecto

        } catch (Exception $e) {
            error_log("Error al obtener producto: " . $e->getMessage());
            return 1;
        }
    }

    // Métodos auxiliares que necesitas agregar al modelo
    private function obtener_id_serie($serie, $id_tp_comprobante)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT id_serie 
            FROM serie 
            WHERE serie = :serie 
            AND id_tp_comprobante = :id_tp_comprobante
            AND id_terminal = :id_terminal
            LIMIT 1
        ");
            $query->bindParam(":serie", $serie);
            $query->bindParam(":id_tp_comprobante", $id_tp_comprobante);
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();

            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id_serie'] : 1;
        } catch (Exception $e) {
            error_log("Error al obtener ID de serie: " . $e->getMessage());
            return 1;
        }
    }

    private function actualizar_correlativo_serie($serie, $id_tp_comprobante)
    {
        try {
            $query = $this->db->connect()->prepare("
            UPDATE serie 
            SET correlativo = correlativo + 1 
            WHERE serie = :serie 
            AND id_tp_comprobante = :id_tp_comprobante
            AND id_terminal = :id_terminal
        ");
            $query->bindParam(":serie", $serie);
            $query->bindParam(":id_tp_comprobante", $id_tp_comprobante);
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar correlativo: " . $e->getMessage());
        }
    }

    private function procesarNotaConSunat($id_nota, $serie, $correlativo)
    {
        try {
            // 1. Obtener datos completos de la nota
            $query = $this->db->connect()->prepare("
            SELECT 
                n.*,
                v.id_tp_comprobante as tipo_comprobante_id,
                v.id_cliente,
                v.id_tp_moneda,
                v.fecha_emision,
                u.num_docu as cliente_documento,
                u.nombres as cliente_nombres,
                u.apellidos as cliente_apellidos,
                u.direccion as cliente_direccion
            FROM notas n
            LEFT JOIN venta v ON v.id_venta = n.id_comprobante
            LEFT JOIN usuario u ON u.id_usuario = v.id_cliente
            WHERE n.serie = :serie AND n.correlativo = :correlativo
        ");
            $query->bindParam(":serie", $serie);
            $query->bindParam(":correlativo", $correlativo);
            $query->execute();
            $notaData = $query->fetch(PDO::FETCH_ASSOC);

            if (!$notaData) {
                throw new Exception('No se encontró la nota para procesar SUNAT');
            }

            // 2. Obtener datos del emisor (empresa)
            $query = $this->db->connect()->prepare("
            SELECT e.*, t.nombre AS terminal_nombre
            FROM empresa e
            LEFT JOIN terminal t ON t.id_empresa = e.id_empresa
            WHERE t.id_terminal = :id_terminal
            LIMIT 1
        ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            if (!$emisor) {
                throw new Exception('No se encontró datos del emisor');
            }

            // 3. Obtener items de la nota
            $query = $this->db->connect()->prepare("
            SELECT 
                dc.*,
                p.nombre as producto_nombre,
                u.abreviatura as unidad_medida,
                af.codigo as afectacion_codigo
            FROM detalle_comprobante dc
            LEFT JOIN producto p ON p.id = dc.producto_id
            LEFT JOIN unidad_medida u ON u.id = p.unidad_id
            LEFT JOIN afectaciones_igv af ON af.id = dc.afectacion_id
            WHERE dc.comprobante_id = :id_nota
            ORDER BY dc.item ASC
        ");
            $query->bindParam(":id_nota", $id_nota);
            $query->execute();
            $items = $query->fetchAll(PDO::FETCH_ASSOC);

            // 4. Construir el JSON para SUNAT (similar al de comprobantes)
            $jsonSunat = [
                "operacion" => "generar_comprobante",
                "tipo_de_comprobante" => $notaData['id_tp_comprobante'] == 7 ? "07" : "08", // 07=NC, 08=ND
                "serie" => $notaData['serie'],
                "numero" => $notaData['correlativo'],
                "sunat_transaction" => "1", // 1=Boleta, 2=Factura, 3=Nota
                "cliente_tipo_de_documento" => "6", // 6=RUC, 1=DNI
                "cliente_numero_de_documento" => $notaData['cliente_documento'] ?? $emisor['num_docu'],
                "cliente_denominacion" => trim($notaData['cliente_nombres'] . ' ' . $notaData['cliente_apellidos']),
                "cliente_direccion" => $notaData['cliente_direccion'] ?? '',
                "cliente_email" => "",
                "fecha_de_emision" => date('Y-m-d', strtotime($notaData['fecha_emision'])),
                "moneda" => "1", // 1=SOLES
                "tipo_de_cambio" => "",
                "porcentaje_de_igv" => 18.00,
                "descuento_global" => "",
                "total_descuento" => "",
                "total_anticipo" => "",
                "total_gravada" => $notaData['op_gravadas'],
                "total_inafecta" => $notaData['op_inafectas'],
                "total_exonerada" => $notaData['op_exoneradas'],
                "total_igv" => $notaData['igv'],
                "total_gratuita" => "",
                "total_otros_cargos" => "",
                "total" => $notaData['total'],
                "percepcion_tipo" => "",
                "percepcion_base_imponible" => "",
                "total_percepcion" => "",
                "total_incluido_percepcion" => "",
                "detraccion" => false,
                "observaciones" => $notaData['descripcion'],
                "documento_que_se_modifica_tipo" => $notaData['serie_ref'][0] == 'F' ? "01" : "03", // 01=Factura, 03=Boleta
                "documento_que_se_modifica_serie" => $notaData['serie_ref'],
                "documento_que_se_modifica_numero" => $notaData['correlativo_ref'],
                "tipo_de_nota_de_credito" => $notaData['codmotivo'],
                "tipo_de_nota_de_debito" => $notaData['codmotivo'],
                "enviar_automaticamente_a_la_sunat" => true,
                "enviar_automaticamente_al_cliente" => false,
                "codigo_unico" => "",
                "condiciones_de_pago" => "",
                "medio_de_pago" => "",
                "plazo_credito" => "",
                "cuota" => "",
                "items" => []
            ];

            // Agregar items
            foreach ($items as $item) {
                $jsonSunat["items"][] = [
                    "unidad_de_medida" => $item['unidad_medida'] ?? "NIU",
                    "codigo" => "NOTA" . $id_nota,
                    "descripcion" => $item['producto_nombre'] ?? "Nota de crédito/débito",
                    "cantidad" => $item['cantidad'] ?? 1,
                    "valor_unitario" => $item['valor_unitario'] ?? $notaData['total'],
                    "precio_unitario" => $item['precio_unitario'] ?? $notaData['total'],
                    "descuento" => $item['descuento'] ?? 0,
                    "subtotal" => $item['importe_total'] ?? $notaData['total'],
                    "tipo_de_igv" => $item['afectacion_codigo'] ?? 1, // 1=Gravado
                    "igv" => $item['igv'] ?? 0,
                    "total" => $item['importe_total'] ?? $notaData['total'],
                    "anticipo_regularizacion" => false
                ];
            }

            // 5. Enviar a SUNAT (usa tu API actual)
            $api_url = API_URL; // Asegúrate que esta constante esté definida

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonSunat));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . ($emisor['token_sunat'] ?? '')
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('Error en conexión con SUNAT: HTTP ' . $httpCode);
            }

            $sunatResponse = json_decode($response, true);

            if (empty($sunatResponse) || !is_array($sunatResponse)) {
                throw new Exception('Respuesta inválida de SUNAT');
            }

            $resp = $sunatResponse[0] ?? $sunatResponse;

            // 6. Retornar respuesta formateada
            return [
                'success' => ($resp['estado'] ?? 0) == 1,
                'estado_sunat' => $resp['estado'] ?? 0,
                'mensaje_sunat' => $resp['mensaje_sunat'] ?? 'Sin respuesta',
                'xml' => $resp['xml'] ?? null,
                'cdr' => $resp['cdr'] ?? null,
                'hash_cpe' => $resp['hash_cpe'] ?? null,
                'raw_response' => $resp
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en procesamiento SUNAT: ' . $e->getMessage()
            ];
        }
    }

    // Función auxiliar para preparar datos para SUNAT (similar a FacturadorModel)
    private function get_data_comprobante_sunat($id_comprobante)
    {
        // Implementa esta función similar a FacturadorModel::get_data_comprobante_sunat()
        // O mejor aún, crea un servicio compartido
        return ['success' => false, 'message' => 'Función no implementada'];
    }
}