<?php
class Viaje_Report_Model extends Model
{
    private $logDir = '';

    function __construct()
    {
        parent::__construct();

        // Verificar y crear directorio de logs si no existe
        $this->logDir = __DIR__ . '/../../../logs/';
        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0755, true);
        }
    }

    private function logError($message)
    {
        if (is_dir($this->logDir) && is_writable($this->logDir)) {
            @file_put_contents($this->logDir . 'db.log', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
        }
        error_log($message);
    }

    public function getDataTable($data)
    {
        try {
            $fecha_inicio = $data['fecha_inicio'] ?? '';
            $fecha_fin = $data['fecha_fin'] ?? '';
            $terminal_origen = $data['terminal_origen'] ?? '';
            $terminal_destino = $data['terminal_destino'] ?? '';
            $tipo_servicio = $data['tipo_servicio'] ?? '';
            $draw = intval($data['draw'] ?? 1);
            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 10);
            $searchValue = $data['search']['value'] ?? '';

            // CONSULTA BASE CON FILTROS
            $baseQuery = "
            FROM dt_venta dv
            LEFT JOIN venta v ON dv.id_venta = v.id_venta
            LEFT JOIN programacion p ON dv.id_programacion = p.id_programacion
            LEFT JOIN terminal t_o ON p.id_terminal_origen = t_o.id_terminal
            LEFT JOIN terminal t_d ON p.id_terminal_destino = t_d.id_terminal
            LEFT JOIN usuario cli ON v.id_cliente = cli.id_usuario
            LEFT JOIN usuario psj ON dv.id_pasajero = psj.id_usuario
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            LEFT JOIN vehiculo veh ON p.id_vehiculo = veh.id_vehiculo
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dv.id_tp_servicio  
            WHERE 1=1
            AND tp_s.descripcion = 'PASAJE'  
            ";

            $params = [];

            // FILTRO POR TERMINAL ORIGEN
            if (!empty($terminal_origen)) {
                $baseQuery .= " AND p.id_terminal_origen = :terminal_origen";
                $params[':terminal_origen'] = $terminal_origen;
            }

            // FILTRO POR TERMINAL DESTINO (NUEVO)
            if (!empty($terminal_destino)) {
                $baseQuery .= " AND p.id_terminal_destino = :terminal_destino";
                $params[':terminal_destino'] = $terminal_destino;
            }

            // FILTRO POR TIPO DE SERVICIO
            if (!empty($tipo_servicio)) {
                $baseQuery .= " AND p.id_tp_servicio_pasaje = :tipo_servicio";
                $params[':tipo_servicio'] = $tipo_servicio;
            }

            // FILTRO POR FECHAS (usando fecha_emision de venta)
            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $baseQuery .= " AND v.fecha_emision BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
            } elseif (!empty($fecha_inicio)) {
                $baseQuery .= " AND v.fecha_emision >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            } elseif (!empty($fecha_fin)) {
                $baseQuery .= " AND v.fecha_emision <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
            }

            // COLUMNAS PARA BÚSQUEDA
            $searchColumns = [
                "dv.piso",
                "dv.num_asiento",
                "CONCAT(dv.piso, '-', dv.num_asiento)",
                "cli.nombres",
                "cli.apellidos",
                "CONCAT(cli.nombres, ' ', cli.apellidos)",
                "cli.num_docu",
                "psj.nombres",
                "psj.apellidos",
                "CONCAT(psj.nombres, ' ', psj.apellidos)",
                "psj.num_docu",
                "CONCAT(v.serie, '-', v.correlativo)",
                "t_o.nombre",
                "t_d.nombre",
                "veh.placa"
            ];

            // CONSTRUIR WHERE PARA BÚSQUEDA
            $searchWhere = "";
            if (!empty($searchValue)) {
                $searchWhere = " AND (";
                foreach ($searchColumns as $i => $col) {
                    if ($i > 0) {
                        $searchWhere .= " OR ";
                    }
                    $searchWhere .= $col . " LIKE :search" . $i;
                    $params[':search' . $i] = '%' . $searchValue . '%';
                }
                $searchWhere .= ")";
            }

            $selectFields = "
            dv.id_dt_venta,
            dv.piso,
            dv.num_asiento,
            CONCAT(COALESCE(dv.piso, ''), '-', dv.num_asiento) as asiento,
            v.serie,
            v.correlativo,
            CONCAT(v.serie, '-', v.correlativo) as numero_documento,
            v.fecha_emision,
    
            -- CLIENTE (quien paga)
            CONCAT(COALESCE(cli.nombres, ''), ' ', COALESCE(cli.apellidos, '')) as cliente,
            COALESCE(cli.num_docu, '') as documento_cliente,
    
            -- PASAJERO (quien viaja)
            CASE 
            WHEN dv.id_pasajero IS NOT NULL AND dv.id_pasajero != 0 
            THEN CONCAT(COALESCE(psj.nombres, ''), ' ', COALESCE(psj.apellidos, ''))
            ELSE CONCAT(COALESCE(cli.nombres, ''), ' ', COALESCE(cli.apellidos, ''))
            END as pasajero,
    
            CASE 
            WHEN dv.id_pasajero IS NOT NULL AND dv.id_pasajero != 0 
            THEN COALESCE(psj.num_docu, '')
            ELSE COALESCE(cli.num_docu, '')
            END as documento_pasajero,
    
            -- ORIGEN Y DESTINO SIMPLIFICADOS
            COALESCE(t_o.nombre, '') as origen,
            COALESCE(t_d.nombre, '') as destino,
    
            -- DATOS FINANCIEROS
            COALESCE(dv.op_gravada, 0) as op_gravada,
            COALESCE(dv.op_igv, 0) as op_igv,
            COALESCE(dv.op_total, 0) as op_total,
    
            -- PLACA DEL VEHÍCULO (NUEVO)
            COALESCE(veh.placa, '') as placa,
    
            -- ESTADOS
            COALESCE(dv.estado_asiento, 1) as estado_asiento,
            COALESCE(v.envio_sunat, 0) as estado_sunat,
            COALESCE(v.id_tp_comprobante, 0) as id_tp_comprobante,
            COALESCE(tp_c.codigo, '') as tipo_comprobante_codigo
            ";

            $db = $this->db->connect();

            // CONTEO TOTAL SIN FILTROS (para recordsTotal)
            $totalQueryAll = "SELECT COUNT(*) AS total FROM dt_venta dv INNER JOIN venta v ON dv.id_venta = v.id_venta AND v.id_tp_venta = 1";
            $stmtTotalAll = $db->prepare($totalQueryAll);
            $stmtTotalAll->execute();
            $recordsTotalAll = $stmtTotalAll->fetch(PDO::FETCH_ASSOC)['total'];

            // CONTEO CON FILTROS PERO SIN BÚSQUEDA (para recordsFiltered)
            $filteredQuery = "SELECT COUNT(*) AS total " . $baseQuery . $searchWhere;
            $stmtFiltered = $db->prepare($filteredQuery);

            // Vincular solo los parámetros que existen en la consulta
            foreach ($params as $key => $value) {
                if (strpos($filteredQuery, $key) !== false) {
                    $stmtFiltered->bindValue($key, $value);
                }
            }

            $stmtFiltered->execute();
            $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // OBTENER DATOS
            $dataQuery = "
            SELECT " . $selectFields . " 
            " . $baseQuery . " 
            " . $searchWhere . " 
            ORDER BY v.fecha_emision DESC 
            LIMIT :start, :length
            ";

            // Agregar parámetros de paginación
            $params[':start'] = $start;
            $params[':length'] = $length;

            $stmtData = $db->prepare($dataQuery);

            // Vincular todos los parámetros para la consulta de datos
            foreach ($params as $key => $value) {
                $stmtData->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmtData->execute();
            $dataResult = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            $this->logError("Búsqueda: '$searchValue' - Total: $recordsTotalAll, Filtrados: $recordsFiltered, Datos: " . count($dataResult));

            return [
                "draw" => $draw,
                "recordsTotal" => $recordsTotalAll,
                "recordsFiltered" => $recordsFiltered,
                "data" => $dataResult
            ];

        } catch (PDOException $e) {
            $this->logError("Error en getDataTable: " . $e->getMessage());
            $this->logError("Consulta: " . $dataQuery);
            $this->logError("Parámetros: " . json_encode($params));
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
            // VERIFICAR qué columnas existen realmente en tu tabla terminal
            $sql = "SHOW COLUMNS FROM terminal";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $columns = $query->fetchAll(PDO::FETCH_COLUMN);

            $this->logError("Columnas en tabla terminal: " . json_encode($columns));

            // Usar las columnas que realmente existen
            if (in_array('id_terminal', $columns)) {
                $sql = "SELECT id_terminal, nombre FROM terminal ORDER BY nombre ASC";
            } else if (in_array('id', $columns)) {
                $sql = "SELECT id as id_terminal, nombre FROM terminal ORDER BY nombre ASC";
            } else {
                // Usar la primera columna como ID
                $sql = "SELECT * FROM terminal LIMIT 1";
            }

            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                return ['success' => true, 'message' => $result];
            } else {
                return ['success' => false, 'message' => 'No se encontraron terminales.'];
            }
        } catch (PDOException $e) {
            $errorMsg = "Error en getTerminales: " . $e->getMessage();
            $this->logError($errorMsg);
            return ['success' => false, 'message' => 'Error al consultar terminales: ' . $e->getMessage()];
        }
    }

    public function getTiposServicioPasaje()
    {
        try {
            $query = $this->db->connect()->prepare(
                "SELECT id_tp_servicio_pasaje, descripcion 
                 FROM tp_servicio_pasaje 
                 WHERE estado = 1 
                 ORDER BY descripcion"
            );
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMsg = "Error en getTiposServicioPasaje: " . $e->getMessage();
            $this->logError($errorMsg);
            return [];
        }
    }

    public function getEmpresa()
    {
        try {
            $sql = "SELECT logo, num_docu, nombre, direccion, telefono, email FROM empresa LIMIT 1";
            $query = $this->db->connect()->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Procesar la ruta del logo de manera más robusta
                if (!empty($result['logo'])) {
                    $logoPath = $this->resolveLogoPath($result['logo']);
                    $result['logo_absolute_path'] = $logoPath;
                    $result['logo_exists'] = file_exists($logoPath);
                } else {
                    $result['logo_absolute_path'] = '';
                    $result['logo_exists'] = false;
                }
                return $result;
            }

            return $this->getDefaultEmpresaData();

        } catch (PDOException $e) {
            $errorMsg = "Error en getEmpresa: " . $e->getMessage();
            $this->logError($errorMsg);
            return $this->getDefaultEmpresaData();
        }
    }

    private function resolveLogoPath($logoPath)
    {
        // Si ya es una ruta absoluta y existe
        if (file_exists($logoPath)) {
            return $logoPath;
        }

        // Posibles ubicaciones del logo
        $possiblePaths = [
            __DIR__ . '/../../../' . $logoPath,
            __DIR__ . '/../../../public/' . $logoPath,
            __DIR__ . '/../../../uploads/' . $logoPath,
            __DIR__ . '/../../../assets/img/' . $logoPath,
            __DIR__ . '/../../../img/' . $logoPath,
            __DIR__ . '/../../../images/' . $logoPath,
            // Ruta relativa desde el directorio raíz
            $_SERVER['DOCUMENT_ROOT'] . '/' . $logoPath,
            $_SERVER['DOCUMENT_ROOT'] . '/admin/' . $logoPath,
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Si no se encuentra, devolver la ruta original
        return $logoPath;
    }

    private function getDefaultEmpresaData()
    {
        return [
            'logo' => '',
            'logo_absolute_path' => '',
            'logo_exists' => false,
            'num_docu' => '',
            'nombre' => 'EMPRESA',
            'direccion' => '',
            'telefono' => '',
            'email' => ''
        ];
    }

    public function getDatosExportacion($fecha_inicio, $fecha_fin, $id_terminal_origen = '', $id_terminal_destino = '', $tipo_servicio = '')
    {
        try {
            $params = [];
            $filtros = "";

            // USAR MISMO FORMATO QUE GETDATATABLE PARA FECHAS
            if ($fecha_inicio && $fecha_fin) {
                $filtros .= " AND v.fecha_emision BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
            } elseif (!empty($fecha_inicio)) {
                $filtros .= " AND v.fecha_emision >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            } elseif (!empty($fecha_fin)) {
                $filtros .= " AND v.fecha_emision <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
            }

            // FILTRO TERMINAL ORIGEN
            if (!empty($id_terminal_origen)) {
                $filtros .= " AND p.id_terminal_origen = :id_terminal_origen";
                $params[':id_terminal_origen'] = $id_terminal_origen;
            }

            // FILTRO TERMINAL DESTINO
            if (!empty($id_terminal_destino)) {
                $filtros .= " AND p.id_terminal_destino = :id_terminal_destino";
                $params[':id_terminal_destino'] = $id_terminal_destino;
            }

            //  FILTRO POR TIPO DE SERVICIO PASAJE (agregar esto)
            if (!empty($tipo_servicio)) {
                $filtros .= " AND p.id_tp_servicio_pasaje = :tipo_servicio";
                $params[':tipo_servicio'] = $tipo_servicio;
            }

            $sql = "SELECT 
            CONCAT(COALESCE(dv.piso, ''), '-', dv.num_asiento) as asiento,
            CONCAT(v.serie, '-', v.correlativo) as numero_documento,
            v.fecha_emision,
            CONCAT(COALESCE(p.fecha_salida, ''), ' ', COALESCE(p.hora_salida, '')) as fecha_hora_salida,
            
            -- CLIENTE (quien paga)
            CONCAT(COALESCE(cli.nombres, ''), ' ', COALESCE(cli.apellidos, '')) as cliente,
            COALESCE(cli.num_docu, '') as documento_cliente,
            
            -- PASAJERO (quien viaja)
            CASE 
                WHEN dv.id_pasajero IS NOT NULL AND dv.id_pasajero != 0 
                THEN CONCAT(COALESCE(psj.nombres, ''), ' ', COALESCE(psj.apellidos, ''))
                ELSE CONCAT(COALESCE(cli.nombres, ''), ' ', COALESCE(cli.apellidos, ''))
            END as pasajero,
            
            CASE 
                WHEN dv.id_pasajero IS NOT NULL AND dv.id_pasajero != 0 
                THEN COALESCE(psj.num_docu, '')
                ELSE COALESCE(cli.num_docu, '')
            END as documento_pasajero,
            
            COALESCE(td.descripcion, '') as tipo_documento,
            COALESCE(dv.op_gravada, 0) as op_gravada,
            COALESCE(dv.op_exonerada, 0) as op_exonerada,
            COALESCE(dv.op_inafecta, 0) as op_inafecta,
            COALESCE(dv.op_igv, 0) as op_igv,
            COALESCE(dv.op_total, 0) as op_total,
            
            CASE 
                WHEN dv.estado_asiento = 1 THEN 'PAGADO'
                WHEN dv.estado_asiento = 2 THEN 'PAGO EN DESTINO'
                ELSE 'PENDIENTE'
            END as estado,
            
            CASE COALESCE(v.envio_sunat, 0)
                WHEN 0 THEN 'SIN ENVIAR'
                WHEN 1 THEN 'ENVIADO A SUNAT' 
                WHEN 2 THEN 'ENVIADO POR RESUMEN'
                ELSE 'DESCONOCIDO'
            END as estado_sunat,
            
            CONCAT(COALESCE(t_origen.nombre, ''), ' - ', COALESCE(t_destino.nombre, '')) as ruta,
            CONCAT(COALESCE(veh.marca, ''), ' ', COALESCE(veh.modelo, ''), ' - ', COALESCE(veh.placa, '')) as vehiculo,
            COALESCE(tsp.descripcion, '') as tipo_servicio,
            veh.placa as placa  
            
            FROM dt_venta dv
            INNER JOIN venta v ON dv.id_venta = v.id_venta
            LEFT JOIN programacion p ON dv.id_programacion = p.id_programacion
            LEFT JOIN terminal t_origen ON p.id_terminal_origen = t_origen.id_terminal
            LEFT JOIN terminal t_destino ON p.id_terminal_destino = t_destino.id_terminal
            LEFT JOIN usuario cli ON v.id_cliente = cli.id_usuario
            LEFT JOIN usuario psj ON dv.id_pasajero = psj.id_usuario
            LEFT JOIN tp_docu td ON cli.id_tp_docu = td.id_tp_docu
            LEFT JOIN vehiculo veh ON p.id_vehiculo = veh.id_vehiculo
            LEFT JOIN tp_servicio_pasaje tsp ON p.id_tp_servicio_pasaje = tsp.id_tp_servicio_pasaje
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dv.id_tp_servicio
            WHERE 1=1
            AND (tp_s.descripcion = 'PASAJE' OR v.id_tp_venta = 1)  
            " . $filtros . "
            ORDER BY v.fecha_emision DESC, p.fecha_salida DESC";

            $query = $this->db->connect()->prepare($sql);

            // Vincular parámetros
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            error_log("Registros encontrados para Excel: " . count($resultados));
            return $resultados;

        } catch (PDOException $e) {
            error_log("Error en getDatosExportacion: " . $e->getMessage());
            return [];
        }
    }
}