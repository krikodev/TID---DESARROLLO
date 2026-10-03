<?php
class Comprobantes_ReportModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            // Normalizar tp_comprobante
            $data['tp_comprobante'] = isset($data['tp_comprobante']) ? $data['tp_comprobante'] : 'TODO';
            switch ($data['tp_comprobante']) {
                case 'TODO':
                    $data['tp_comprobante'] = "1,2,3,7,8";
                    break;
                case '0':
                    $data['tp_comprobante'] = "1,3,7,8";
                    break;
                case '7':
                    $data['tp_comprobante'] = "7,8";
                    break;
            }

            $tp_comprobante = $data['tp_comprobante'];
            $fecha_inicio = $data['fecha_inicio'] ?? null;
            $fecha_fin = $data['fecha_fin'] ?? null;
            $terminal = $data['terminal'] ?? null;
            $draw = intval($data['draw'] ?? 1);
            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 10);
            $searchValue = $data['search']['value'] ?? '';

            // Separar tipos de comprobante
            $tipos_array = explode(',', $tp_comprobante);
            $tipos_venta = array_filter($tipos_array, function ($tipo) {
                return in_array($tipo, ['1', '2', '3']);
            });
            $tipos_nota = array_filter($tipos_array, function ($tipo) {
                return in_array($tipo, ['7', '8']);
            });

            // Si no hay tipos válidos, retornar vacío
            if (empty($tipos_venta) && empty($tipos_nota)) {
                return [
                    "draw" => $draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ];
            }

            $db = $this->db->connect();

            // Configurar PDO para permitir named placeholders duplicados
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

            // Construir las subconsultas con parámetros únicos
            $subqueries = [];
            $param_count = 0;
            $params = [];

            // Subconsulta para VENTAS
            if (!empty($tipos_venta)) {
                $param_count++;
                $tipos_venta_str = implode(',', $tipos_venta);

                $where_conditions = [];

                if ($fecha_inicio && $fecha_fin) {
                    $where_conditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                    $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                    $params[":fecha_fin_$param_count"] = $fecha_fin;
                } elseif ($fecha_inicio) {
                    $fecha_fin_default = date('Y-m-d');
                    $where_conditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                    $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                    $params[":fecha_fin_$param_count"] = $fecha_fin_default;
                }

                if ($terminal) {
                    $where_conditions[] = "v.id_terminal = :terminal_$param_count";
                    $params[":terminal_$param_count"] = $terminal;
                }

                $where_clause = !empty($where_conditions) ? " AND " . implode(" AND ", $where_conditions) : "";

                $subqueries[] = "
                SELECT 
                    v.id_venta AS id,
                    CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                    c.num_docu AS cliente_num_docu,
                    CONCAT(v.serie, '-', v.correlativo) AS numero,
                    v.op_igv AS igv,
                    v.estado,
                    v.envio_sunat AS estado_sunat,
                    v.descrip_cdr_sunat,
                    v.hash_cdr,
                    v.fecha_registro,
                    v.op_gravada,
                    v.op_exonerada,
                    v.op_inafecta,
                    v.total,
                    tp_s.descripcion AS tp_servicio,
                    DATE_FORMAT(v.fecha_emision, '%Y-%m-%d') AS fecha_emision,
                    v.id_tp_comprobante,
                    v.serie,
                    v.correlativo,
                    td.codigo AS tipo_docu,
                    tm.codigo AS codigo_moneda,
                    NULL AS ref_fecha,
                    NULL AS ref_tipo_comprobante,
                    NULL AS ref_serie,
                    NULL AS ref_correlativo,
                    NULL AS ref_id_cliente,
                    NULL AS ref_cliente_num_docu,
                    NULL AS ref_cliente_nombre
                FROM venta v
                LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
                LEFT JOIN tp_docu td ON td.id_tp_docu = c.id_tp_docu
                LEFT JOIN tp_moneda tm ON tm.id_tp_moneda = v.id_tp_moneda
                LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
                LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
                WHERE v.id_tp_comprobante IN ($tipos_venta_str)
                $where_clause
            ";
            }

            // Subconsulta para NOTAS
            if (!empty($tipos_nota)) {
                $param_count++;
                $tipos_nota_str = implode(',', $tipos_nota);

                $where_conditions = [];

                if ($fecha_inicio && $fecha_fin) {
                    $where_conditions[] = "DATE(n.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                    $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                    $params[":fecha_fin_$param_count"] = $fecha_fin;
                } elseif ($fecha_inicio) {
                    $fecha_fin_default = date('Y-m-d');
                    $where_conditions[] = "DATE(n.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                    $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                    $params[":fecha_fin_$param_count"] = $fecha_fin_default;
                }

                if ($terminal) {
                    $where_conditions[] = "v.id_terminal = :terminal_$param_count";
                    $params[":terminal_$param_count"] = $terminal;
                }

                $where_clause = !empty($where_conditions) ? " AND " . implode(" AND ", $where_conditions) : "";

                $subqueries[] = "
                SELECT 
                    n.id_nota AS id,
                    n.cliente,
                    NULL AS cliente_num_docu,
                    CONCAT(n.serie, '-', n.correlativo) AS numero,
                    n.igv,
                    'APROBADO' AS estado,
                    n.estado_sunat,
                    n.mensaje_sunat AS descrip_cdr_sunat,
                    n.hash_cpe AS hash_cdr,
                    n.fecha_emision AS fecha_registro,
                    n.op_gravadas AS op_gravada,
                    n.op_exoneradas AS op_exonerada,
                    n.op_inafectas AS op_inafecta,
                    n.total,
                    NULL AS tp_servicio,
                    DATE_FORMAT(n.fecha_emision, '%Y-%m-%d') AS fecha_emision,
                    n.id_tp_comprobante,
                    n.serie,
                    n.correlativo,
                    NULL AS tipo_docu,
                    'PEN' AS codigo_moneda,
                    v.fecha_emision AS ref_fecha,
                    tc.codigo AS ref_tipo_comprobante,
                    n.serie_ref AS ref_serie,
                    n.correlativo_ref AS ref_correlativo,
                    v.id_cliente AS ref_id_cliente,
                    u.num_docu AS ref_cliente_num_docu,
                    CONCAT(u.nombres, ' ', u.apellidos) AS ref_cliente_nombre
                FROM notas n
                LEFT JOIN venta v ON n.id_comprobante = v.id_venta
                LEFT JOIN tp_comprobante tc ON v.id_tp_comprobante = tc.id_tp_comprobante
                LEFT JOIN usuario u ON u.id_usuario = v.id_cliente
                WHERE n.id_tp_comprobante IN ($tipos_nota_str)
                $where_clause
            ";
            }

            // Unir todas las subconsultas
            $unionSQL = implode(" UNION ALL ", $subqueries);

            // Columnas para búsqueda (usando los ALIAS)
            $searchColumns = [
                'fecha_emision',
                'cliente',
                'numero',
                'estado',
                'op_gravada',
                'op_exonerada',
                'op_inafecta',
                'igv',
                'total',
                'serie',
                'correlativo',
                'cliente_num_docu'
            ];

            // Construir WHERE para búsqueda (con parámetros únicos)
            $searchWhere = "";
            $searchParams = [];
            if ($searchValue && !empty($searchColumns)) {
                $searchWhere = " WHERE (";
                foreach ($searchColumns as $i => $col) {
                    if ($i > 0)
                        $searchWhere .= " OR ";
                    $param_name = ":search_" . $i . "_" . uniqid();
                    $searchWhere .= "`$col` LIKE $param_name";
                    $searchParams[$param_name] = '%' . $searchValue . '%';
                }
                $searchWhere .= ")";
            }

            // Combinar todos los parámetros
            $allParams = array_merge($params, $searchParams);

            // Conteo total (sin búsqueda)
            $totalQuery = "SELECT COUNT(*) AS total FROM ($unionSQL) AS combined";
            $stmtTotal = $db->prepare($totalQuery);
            foreach ($params as $key => $val) {
                $stmtTotal->bindValue($key, $val);
            }
            $stmtTotal->execute();
            $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

            // Conteo filtrado (con búsqueda)
            $filteredQuery = "SELECT COUNT(*) AS total FROM ($unionSQL) AS combined $searchWhere";
            $stmtFiltered = $db->prepare($filteredQuery);
            foreach ($allParams as $key => $val) {
                $stmtFiltered->bindValue($key, $val);
            }
            $stmtFiltered->execute();
            $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // Datos (con búsqueda, ORDER BY, LIMIT)
            $dataQuery = "SELECT * FROM ($unionSQL) AS combined $searchWhere ORDER BY fecha_emision DESC LIMIT :start, :length";
            $allParams[':start'] = (int) $start;
            $allParams[':length'] = (int) $length;

            $stmtData = $db->prepare($dataQuery);
            foreach ($allParams as $key => $val) {
                $paramType = PDO::PARAM_STR;
                if (is_int($val)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_null($val)) {
                    $paramType = PDO::PARAM_NULL;
                }
                $stmtData->bindValue($key, $val, $paramType);
            }
            $stmtData->execute();
            $dataResult = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            return [
                "draw" => $draw,
                "recordsTotal" => (int) $recordsTotal,
                "recordsFiltered" => (int) $recordsFiltered,
                "data" => $dataResult
            ];

        } catch (PDOException $e) {
            $draw = isset($data['draw']) ? intval($data['draw']) : 1;
            return [
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    public function getTerminales()
    {
        try {
            $sql = "SELECT id_terminal, nombre, cod_domicilio_fiscal AS ruc, direccion_fiscal AS razon_social FROM terminal ORDER BY nombre ASC";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                return ['success' => true, 'message' => $result];
            } else {
                return ['success' => false, 'message' => 'No se encontraron terminales.'];
            }
        } catch (PDOException $e) {
            error_log("Error en getTerminales: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al consultar terminales: ' . $e->getMessage()];
        }
    }

    public function getTerminalById($id_terminal)
    {
        try {
            $sql = "SELECT cod_domicilio_fiscal AS ruc, direccion_fiscal AS razon_social FROM terminal WHERE id_terminal = :id_terminal";
            $query = $this->db->connect()->prepare($sql);
            $query->bindValue(':id_terminal', $id_terminal);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            } else {
                return ['ruc' => '20123456789', 'razon_social' => 'TRANSPORTES XYZ S.A.C.'];
            }
        } catch (PDOException $e) {
            error_log("Error en getTerminalById: " . $e->getMessage());
            return ['ruc' => 'ERROR', 'razon_social' => 'ERROR'];
        }
    }

    public function getEmpresa()
    {
        try {
            $sql = "SELECT logo, num_docu FROM empresa LIMIT 1";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            } else {
                return ['logo' => '', 'num_docu' => ''];
            }
        } catch (PDOException $e) {
            error_log("Error en getEmpresa: " . $e->getMessage());
            return ['logo' => 'ERROR', 'num_docu' => 'ERROR'];
        }
    }

    private function getFilteredData($tp_comprobante, $fecha_inicio = null, $fecha_fin = null, $terminal = null)
    {
        // Separar tipos de comprobante
        $tipos_array = explode(',', $tp_comprobante);
        $tipos_venta = array_filter($tipos_array, function ($tipo) {
            return in_array($tipo, ['1', '2', '3']);
        });
        $tipos_nota = array_filter($tipos_array, function ($tipo) {
            return in_array($tipo, ['7', '8']);
        });

        // Si no hay tipos válidos, retornar vacío
        if (empty($tipos_venta) && empty($tipos_nota)) {
            return [];
        }

        $db = $this->db->connect();
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        // Construir las subconsultas con parámetros únicos
        $subqueries = [];
        $param_count = 0;
        $params = [];

        // Subconsulta para VENTAS
        if (!empty($tipos_venta)) {
            $param_count++;
            $tipos_venta_str = implode(',', $tipos_venta);

            $where_conditions = [];

            if ($fecha_inicio && $fecha_fin) {
                $where_conditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                $params[":fecha_fin_$param_count"] = $fecha_fin;
            } elseif ($fecha_inicio) {
                $fecha_fin_default = date('Y-m-d');
                $where_conditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                $params[":fecha_fin_$param_count"] = $fecha_fin_default;
            }

            if ($terminal) {
                $where_conditions[] = "v.id_terminal = :terminal_$param_count";
                $params[":terminal_$param_count"] = $terminal;
            }

            $where_clause = !empty($where_conditions) ? " AND " . implode(" AND ", $where_conditions) : "";

            $subqueries[] = "
            SELECT
                v.id_venta AS id_venta,
                CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                c.num_docu AS cliente_num_docu,
                CONCAT(v.serie, ' ', v.correlativo) AS numero,
                v.op_igv AS igv,
                v.estado,
                v.envio_sunat AS estado_sunat,
                v.descrip_cdr_sunat,
                v.hash_cdr,
                v.fecha_registro,
                v.op_gravada,
                v.op_exonerada,
                v.op_inafecta,
                v.total,
                tp_s.descripcion AS tp_servicio,
                DATE_FORMAT(v.fecha_emision, '%Y-%m-%d') AS fecha_emision,
                v.id_tp_comprobante,
                v.serie,
                v.correlativo,
                td.codigo AS tipo_docu,
                tm.codigo AS codigo_moneda,
                NULL AS ref_fecha,
                NULL AS ref_tipo_comprobante,
                NULL AS ref_serie,
                NULL AS ref_correlativo,
                NULL AS ref_id_cliente,
                NULL AS ref_cliente_num_docu,
                NULL AS ref_cliente_nombre
            FROM venta v
            LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
            LEFT JOIN tp_docu td ON td.id_tp_docu = c.id_tp_docu
            LEFT JOIN tp_moneda tm ON tm.id_tp_moneda = v.id_tp_moneda
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
            WHERE v.id_tp_comprobante IN ($tipos_venta_str)
            $where_clause
        ";
        }

        // Subconsulta para NOTAS
        if (!empty($tipos_nota)) {
            $param_count++;
            $tipos_nota_str = implode(',', $tipos_nota);

            $where_conditions = [];

            if ($fecha_inicio && $fecha_fin) {
                $where_conditions[] = "DATE(n.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                $params[":fecha_fin_$param_count"] = $fecha_fin;
            } elseif ($fecha_inicio) {
                $fecha_fin_default = date('Y-m-d');
                $where_conditions[] = "DATE(n.fecha_emision) BETWEEN :fecha_inicio_$param_count AND :fecha_fin_$param_count";
                $params[":fecha_inicio_$param_count"] = $fecha_inicio;
                $params[":fecha_fin_$param_count"] = $fecha_fin_default;
            }

            if ($terminal) {
                $where_conditions[] = "v.id_terminal = :terminal_$param_count";
                $params[":terminal_$param_count"] = $terminal;
            }

            $where_clause = !empty($where_conditions) ? " AND " . implode(" AND ", $where_conditions) : "";

            $subqueries[] = "
            SELECT
                n.id_nota AS id_venta,
                n.cliente,
                NULL AS cliente_num_docu,
                CONCAT(n.serie, ' ', n.correlativo) AS numero,
                n.igv,
                'APROBADO' AS estado,
                n.estado_sunat,
                n.mensaje_sunat AS descrip_cdr_sunat,
                n.hash_cpe AS hash_cdr,
                n.fecha_emision AS fecha_registro,
                n.op_gravadas AS op_gravada,
                n.op_exoneradas AS op_exonerada,
                n.op_inafectas AS op_inafecta,
                n.total,
                NULL AS tp_servicio,
                DATE_FORMAT(n.fecha_emision, '%Y-%m-%d') AS fecha_emision,
                n.id_tp_comprobante,
                n.serie,
                n.correlativo,
                NULL AS tipo_docu,
                'PEN' AS codigo_moneda,
                v.fecha_emision AS ref_fecha,
                tc.codigo AS ref_tipo_comprobante,
                n.serie_ref AS ref_serie,
                n.correlativo_ref AS ref_correlativo,
                v.id_cliente AS ref_id_cliente,
                u.num_docu AS ref_cliente_num_docu,
                CONCAT(u.nombres, ' ', u.apellidos) AS ref_cliente_nombre
            FROM notas n
            LEFT JOIN venta v ON n.id_comprobante = v.id_venta
            LEFT JOIN tp_comprobante tc ON v.id_tp_comprobante = tc.id_tp_comprobante
            LEFT JOIN usuario u ON u.id_usuario = v.id_cliente
            WHERE n.id_tp_comprobante IN ($tipos_nota_str)
            $where_clause
        ";
        }

        // Unir y ejecutar
        $unionSQL = implode(" UNION ALL ", $subqueries) . " ORDER BY fecha_emision DESC";

        try {
            $query = $db->prepare($unionSQL);
            foreach ($params as $key => $val) {
                $query->bindValue($key, $val);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getFilteredData: " . $e->getMessage());
            error_log("SQL: " . $unionSQL);
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }

    public function getDataExportacion($data)
    {
        $tp_comprobante = isset($data['tp_comprobante']) ? $data['tp_comprobante'] : 'TODO';
        switch ($tp_comprobante) {
            case 'TODO':
                $tp_comprobante = "1,2,3,7,8";
                break;
            case '0':
                $tp_comprobante = "1,3,7,8";
                break;
            case '7':
                $tp_comprobante = "7,8";
                break;
        }

        $fecha_inicio = $data['fecha_inicio'] ?? null;
        $fecha_fin = $data['fecha_fin'] ?? null;
        $terminal = $data['terminal'] ?? null;

        $filteredData = $this->getFilteredData($tp_comprobante, $fecha_inicio, $fecha_fin, $terminal);

        if (empty($filteredData)) {
            return [];
        }

        return array_map(function ($row) {
            return array_merge($row, [
                'id_tp_comprobante' => isset($row['id_tp_comprobante']) ? $row['id_tp_comprobante'] : '7',
                'serie' => isset($row['serie']) ? $row['serie'] : (isset($row['numero']) ? explode(' ', $row['numero'])[0] : ''),
                'correlativo' => isset($row['correlativo']) ? $row['correlativo'] : (isset($row['numero']) ? explode(' ', $row['numero'])[1] : '')
            ]);
        }, $filteredData);
    }
}