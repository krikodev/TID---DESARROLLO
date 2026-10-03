<?php
date_default_timezone_set('America/Lima');
class ProgramacionModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $db = $this->db->connect();

            $draw = intval($data['draw'] ?? 1);
            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 20);

            //búsqueda global
            $search = normalizar_estado(
                trim($data['search']['value'] ?? '')
            );

            //filtros de búsqueda
            $fechaInicio = trim($data['fecha_inicio'] ?? '');
            $fechaFin = trim($data['fecha_fin'] ?? '');
            $tipoProgramacion = trim($data['tipo_programacion'] ?? '');
            $origen = trim($data['origen'] ?? '');
            $destino = trim($data['destino'] ?? '');
            $estado = trim($data['estado_programacion'] ?? '');
            $fechaLiquidacion = trim($data['fecha_liquidacion'] ?? '');

            //campos a seleccionar en la consulta
            $selectFields = "
            p.id_programacion,
            p.id_terminal_origen,
            t_ori.nombre AS terminal_origen_nombre,
            p.id_terminal_destino,
            t_desti.nombre AS terminal_destino_nombre,
            p.id_vehiculo,
            v.descripcion AS vehiculo_descripcion,
            v.num_piso AS vehiculo_piso,
            v.placa AS vehiculo_placa,
            p.id_conductor,
            CONCAT(c.nombres, ' ', c.apellidos) AS conductor_nombres,
            c.num_docu AS conductor_num_docu,
            p.fecha_salida,
            p.hora_salida,
            DATE_FORMAT(p.hora_salida, '%h:%i %p') AS hora_salida_format,
            p.precio_primer_piso,
            p.precio_segundo_piso,
            p.id_tp_servicio_pasaje,
            p.estado,
            p.liquidado,
            p.tipo_programacion,
            p.precio_minimo,
            p.fecha_liquidacion
        ";

            //fromQuery contiene la parte FROM y los JOIN necesarios para la consulta
            $fromQuery = "
            FROM programacion p
            LEFT JOIN vehiculo v
                ON v.id_vehiculo = p.id_vehiculo
            LEFT JOIN terminal t_ori
                ON t_ori.id_terminal = p.id_terminal_origen
            LEFT JOIN terminal t_desti
                ON t_desti.id_terminal = p.id_terminal_destino
            LEFT JOIN usuario c
                ON c.id_usuario = p.id_conductor
        ";

            $where = [
                "p.estado IN (0,1)"
            ];
            $params = [];

            //filtro fecha inicio
            if ($fechaInicio !== '') {
                $where[] = "p.fecha_salida >= ?";
                $params[] = $fechaInicio;
            }

            //filtro fecha fin
            if ($fechaFin !== '') {
                $where[] = "p.fecha_salida <= ?";
                $params[] = $fechaFin;
            }

            //filtro tipo de programación
            if ($tipoProgramacion !== '') {
                $where[] = "p.tipo_programacion = ?";
                $params[] = $tipoProgramacion;
            }

            //filtro origen
            if ($origen !== '') {
                $where[] = "p.id_terminal_origen = ?";
                $params[] = $origen;
            }

            //filtro destino
            if ($destino !== '') {
                $where[] = "p.id_terminal_destino = ?";
                $params[] = $destino;
            }

            //filtro estado
            if ($estado !== '') {
                $where[] = "p.estado = ?";
                $params[] = $estado;
            }

            //filtro fecha liquidación
            if ($fechaLiquidacion !== '') {
                $where[] = "p.fecha_liquidacion >= ?";
                $params[] = $fechaLiquidacion . " 00:00:00";
                $where[] = "p.fecha_liquidacion < DATE_ADD(?, INTERVAL 1 DAY)";
                $params[] = $fechaLiquidacion;
            }

            //busqueda global
            if ($search !== '') {
                $searchColumns = [
                    "p.tipo_programacion",
                    "t_ori.nombre",
                    "t_desti.nombre",
                    "c.nombres",
                    "c.apellidos",
                    "CONCAT(c.nombres, ' ', c.apellidos)",
                    "CONCAT(c.nombres, ' ', c.apellidos, ' ', c.num_docu)",
                    "v.placa",
                    "p.fecha_salida",
                    "p.hora_salida",
                    "p.precio_primer_piso",
                    "p.precio_segundo_piso",
                    "CASE
                        WHEN p.liquidado = 1
                        THEN 'Liquidado'
                        ELSE 'Sin liquidar'
                    END",
                    "p.fecha_liquidacion",
                    "DATE_FORMAT(p.fecha_liquidacion, '%d/%m/%Y')",
                    "TIME(p.fecha_liquidacion)",
                ];
                $searchConditions = [];

                foreach ($searchColumns as $column) {
                    $searchConditions[] = "$column LIKE ?";
                    $params[] = "%$search%";
                }
                $where[] = "(" . implode(" OR ", $searchConditions) . ")";
            }

            $whereSQL = " WHERE " . implode(" AND ", $where);

            //total de registros sin filtrar
            $totalQuery = "
            SELECT COUNT(*)
            $fromQuery
            WHERE p.estado IN (0,1)
        ";

            $stmtTotal = $db->prepare($totalQuery);
            $stmtTotal->execute();
            $recordsTotal = $stmtTotal->fetchColumn();

            $filteredQuery = "
            SELECT COUNT(*)
            $fromQuery
            $whereSQL
        ";

            $stmtFiltered = $db->prepare($filteredQuery);
            $stmtFiltered->execute($params);
            $recordsFiltered = $stmtFiltered->fetchColumn();

            $orderBy = "p.fecha_salida DESC";
            $dataQuery = "
            SELECT
                $selectFields
            $fromQuery
            $whereSQL
            ORDER BY $orderBy
            LIMIT ?, ?
        ";
            $stmtData = $db->prepare($dataQuery);

            //parámetros de inicio y longitud al final del array de parámetros
            $executeParams = $params;
            $executeParams[] = $start;
            $executeParams[] = $length;
            $stmtData->execute($executeParams);
            $resultData = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            //tipo de usuario de sesión para controlar botones en la vista
            foreach ($resultData as &$registro) {
                $registro['tp_usuario'] =
                    $this->tp_usuario_sesion ?? null;
            }
            return [
                "draw" => $draw,
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $resultData
            ];
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

    public function getDataExportProgramacion(
        $fechaInicio,
        $fechaFin,
        $tipoProgramacion,
        $origen,
        $destino,
        $estado,
        $fechaLiquidacion,
        $search
    ) {
        try {
            $db = $this->db->connect();
            $where = ["p.estado IN (0,1)"];
            $params = [];

            // FILTRO POR FECHA DE INICIO
            if ($fechaInicio !== '') {
                $where[] = "p.fecha_salida >= ?";
                $params[] = $fechaInicio;
            }

            // FILTRO POR FECHA DE FIN
            if ($fechaFin !== '') {
                $where[] = "p.fecha_salida <= ?";
                $params[] = $fechaFin;
            }

            // FILTRO POR TIPO DE PROGRAMACIÓN
            if ($tipoProgramacion !== '') {
                $where[] = "p.tipo_programacion = ?";
                $params[] = $tipoProgramacion;
            }

            // FILTRO POR ORIGEN
            if ($origen !== '') {
                $where[] = "p.id_terminal_origen = ?";
                $params[] = $origen;
            }

            // FILTRO POR DESTINO
            if ($destino !== '') {
                $where[] = "p.id_terminal_destino = ?";
                $params[] = $destino;
            }

            // FILTRO POR ESTADO
            if ($estado !== '') {
                $where[] = "p.estado = ?";
                $params[] = $estado;
            }

            // FILTRO POR FECHA DE LIQUIDACIÓN
            if ($fechaLiquidacion !== '') {
                $where[] = "DATE(p.fecha_liquidacion) = ?";
                $params[] = $fechaLiquidacion;
            }

            // BÚSQUEDA GENERAL
            if ($search !== '') {
                $searchColumns = [
                    "p.tipo_programacion",
                    "t_ori.nombre",
                    "t_desti.nombre",
                    "c.nombres",
                    "c.apellidos",
                    "CONCAT(c.nombres, ' ', c.apellidos)",
                    "CONCAT(c.nombres, ' ', c.apellidos, ' ', c.num_docu)",
                    "v.placa",
                    "p.fecha_salida",
                    "p.hora_salida",
                    "p.precio_primer_piso",
                    "p.precio_segundo_piso",
                    "CASE
                    WHEN p.liquidado = 1
                    THEN 'Liquidado'
                    ELSE 'Sin liquidar'
                END",
                    "p.fecha_liquidacion"
                ];

                $searchConditions = [];

                foreach ($searchColumns as $column) {
                    $searchConditions[] =
                        "$column LIKE ?";
                    $params[] = "%$search%";
                }
                $where[] = "(" . implode(" OR ", $searchConditions) . ")";
            }

            // CONSTRUIR WHERE
            $whereSQL = " WHERE " . implode(" AND ", $where);

            // CONSULTA PARA EXCEL
            $query = "
            SELECT
                p.id_programacion,
                p.tipo_programacion,
                CASE
                    WHEN p.tipo_programacion = 1
                    THEN 'PASAJES'
                    WHEN p.tipo_programacion = 2
                    THEN 'ENCOMIENDAS'
                    ELSE 'OTRO'
                END AS tipo_programacion_nombre,
                t_ori.nombre AS terminal_origen_nombre,
                t_desti.nombre AS terminal_destino_nombre,
                p.fecha_salida,
                p.hora_salida,

                DATE_FORMAT(
                    p.hora_salida,
                    '%h:%i %p'
                ) AS hora_salida_format,
                v.descripcion AS vehiculo_descripcion,
                v.placa AS vehiculo_placa,

                CONCAT(
                    c.nombres,
                    ' ',
                    c.apellidos
                ) AS conductor_nombres,
                c.num_docu AS conductor_num_docu,
                p.precio_primer_piso,
                p.precio_segundo_piso,
                p.precio_minimo,
                p.estado,

                CASE
                    WHEN p.estado = 1
                    THEN 'HABILITADO'
                    ELSE 'DESHABILITADO'
                END AS estado_nombre,
                p.liquidado,

                CASE
                    WHEN p.liquidado = 1
                    THEN 'LIQUIDADO'
                    ELSE 'SIN LIQUIDAR'
                END AS liquidado_nombre,
                DATE_FORMAT(
                    p.fecha_liquidacion,
                    '%d/%m/%Y'
                ) AS fecha_liquidacion,

                DATE_FORMAT(
                    p.fecha_liquidacion,
                    '%h:%i %p'
                ) AS hora_liquidacion

            FROM programacion p
            LEFT JOIN vehiculo v
                ON v.id_vehiculo = p.id_vehiculo

            LEFT JOIN terminal t_ori
                ON t_ori.id_terminal = p.id_terminal_origen

            LEFT JOIN terminal t_desti
                ON t_desti.id_terminal = p.id_terminal_destino

            LEFT JOIN usuario c
                ON c.id_usuario = p.id_conductor

            $whereSQL
            ORDER BY
                p.fecha_salida DESC,
                p.hora_salida DESC
        ";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportProgramacion: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresaInfo()
    {
        try {

            $query = $this->db->connect()->prepare("
            SELECT 
                e.id_empresa,
                e.razon_social AS nombre,
                e.num_docu AS ruc,
                e.direccion_fiscal AS direccion,
                e.logo
            FROM empresa e
            WHERE e.id_empresa = :id_empresa
            LIMIT 1
        ");

            $id_empresa =
                $this->id_empresa_sesion ?? 1;

            $query->bindParam(
                ':id_empresa',
                $id_empresa,
                PDO::PARAM_INT
            );

            $query->execute();

            $empresa =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$empresa) {

                // Valores por defecto si no hay empresa

                return [
                    'id_empresa' => $id_empresa,
                    'nombre' => 'EMPRESA DE TRANSPORTES',
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'logo' => '',
                    'logo_exists' => false,
                    'logo_absolute_path' => ''
                ];
            }


            // ============================================
            // CONSTRUIR RUTA ABSOLUTA DEL LOGO
            // ============================================

            $logo_absolute_path = '';

            $logo_exists = false;


            if (!empty($empresa['logo'])) {

                $logo_absolute_path =
                    $_SERVER['DOCUMENT_ROOT'] .
                    '/tid-transporte/img/admin/' .
                    $empresa['logo'];


                if (file_exists($logo_absolute_path)) {

                    $logo_exists = true;
                } else {

                    // Intentar con otra ruta posible

                    $logo_absolute_path =
                        $_SERVER['DOCUMENT_ROOT'] .
                        '/tid-transporte/img/admin' .
                        $empresa['logo'];

                    $logo_exists =
                        file_exists(
                            $logo_absolute_path
                        );
                }
            }


            // ============================================
            // CAMPOS CALCULADOS
            // ============================================

            $empresa['logo_exists'] =
                $logo_exists;

            $empresa['logo_absolute_path'] =
                $logo_exists
                ? $logo_absolute_path
                : '';


            return $empresa;
        } catch (PDOException $e) {

            error_log(
                "Error en getEmpresaInfo: " .
                    $e->getMessage()
            );


            return [

                'id_empresa' =>
                $this->id_empresa_sesion ?? 1,

                'nombre' =>
                'EMPRESA DE TRANSPORTES',

                'ruc' => '',

                'direccion' => '',

                'telefono' => '',

                'logo' => '',

                'logo_absolute_path' => '',

                'logo_exists' => false
            ];
        }
    }

    public function add_register($data)
    {
        $conn = null;

        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $tipo_servicio_pasaje = !empty($data["tp_servicio_pasaje"])
                ? $data["tp_servicio_pasaje"]
                : null;

            $precio_primer_piso = !empty($data["precio_primer_piso"])
                ? $data["precio_primer_piso"]
                : null;

            $precio_segundo_piso = !empty($data["precio_segundo_piso"])
                ? $data["precio_segundo_piso"]
                : null;

            $precio_minimo = !empty($data["precio_minimo"])
                ? $data["precio_minimo"]
                : null;



            $data["personal"] = is_array($data["personal"])
                ? $data["personal"]
                : json_decode($data["personal"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en JSON: personal");
            }


            $data["terminalRuta"] = is_array($data["terminalRuta"])
                ? $data["terminalRuta"]
                : json_decode($data["terminalRuta"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en JSON: terminalRuta");
            }


            $data["terminalDRuta"] = is_array($data["terminalDRuta"])
                ? $data["terminalDRuta"]
                : json_decode($data["terminalDRuta"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en JSON: terminalDRuta");
            }


            if (empty($data["conductor"])) {

                $data["conductor"] = "2";
            } else {

                $query = $conn->prepare("
                SELECT id_programacion 
                FROM programacion 
                WHERE id_conductor = :id_conductor 
                AND fecha_salida = :fecha_salida 
                AND estado = 1 
                AND liquidado = 0
                AND id_conductor != 2
                ");

                $query->bindParam(':id_conductor', $data["conductor"]);
                $query->bindParam(':fecha_salida', $data["fecha_salida"]);
                $query->execute();

                $conductor_programado = $query->fetchColumn();

                if ($conductor_programado) {
                    throw new Exception(
                        'El conductor seleccionado ya tiene una programacion para salida de hoy.'
                    );
                }
            }


            $query = $conn->prepare("
            SELECT id_programacion 
            FROM programacion 
            WHERE id_vehiculo = :id_vehiculo 
            AND fecha_salida = :fecha_salida 
            AND estado = 1 
            AND liquidado = 0
            ");

            $query->bindParam(
                ':id_vehiculo',
                $data["vehiculo"]
            );

            $query->bindParam(
                ':fecha_salida',
                $data["fecha_salida"]
            );

            $query->execute();

            $vehiculo_programado = $query->fetchColumn();

            if ($vehiculo_programado) {
                throw new Exception(
                    'El vehiculo seleccionado ya tiene una programacion para salida de hoy.'
                );
            }


            $config_manifiesto = null;

            if ($data["tipo_programacion"] === "1") {

                $query = $conn->prepare("
                 SELECT 
                sm.id_serie_manifiesto,
                sm.serie,
                sm.correlativo
                FROM terminal_serie_manifiesto tsm

                INNER JOIN serie_manifiesto sm
                ON sm.id_serie_manifiesto = tsm.id_serie_manifiesto

                WHERE tsm.id_terminal = :id_terminal
                AND tsm.estado = 'ACTIVO'
                AND sm.estado = 'ACTIVO'

                LIMIT 1
                FOR UPDATE
                ");

                $query->bindValue(
                    ':id_terminal',
                    $data["terminal_origen"],
                    PDO::PARAM_INT
                );

                $query->execute();

                $config_manifiesto = $query->fetch(PDO::FETCH_ASSOC);

                if (!$config_manifiesto) {
                    throw new Exception(
                        'El terminal de origen no tiene una serie de manifiesto activa configurada.'
                    );
                }

                if (empty(trim($config_manifiesto["serie"]))) {
                    throw new Exception(
                        'La serie del manifiesto no tiene un valor válido.'
                    );
                }

                if (
                    $config_manifiesto["correlativo"] === null ||
                    $config_manifiesto["correlativo"] === ''
                ) {
                    throw new Exception(
                        'La serie del manifiesto no tiene un correlativo configurado.'
                    );
                }

                if (
                    !ctype_digit(
                        (string) $config_manifiesto["correlativo"]
                    )
                ) {
                    throw new Exception(
                        'El correlativo del manifiesto no tiene un formato válido.'
                    );
                }
            }

            $query = $conn->prepare("
            INSERT INTO programacion (
                id_terminal_origen,
                id_terminal_destino,
                id_vehiculo,
                id_conductor, 
                fecha_salida,
                hora_salida,
                precio_primer_piso,
                precio_segundo_piso,
                precio_minimo, 
                id_tp_servicio_pasaje,
                estado,
                liquidacion,
                tipo_programacion
            ) VALUES (
                :id_terminal_origen,
                :id_terminal_destino,
                :id_vehiculo,
                :id_conductor, 
                :fecha_salida,
                :hora_salida,
                :precio_primer_piso,
                :precio_segundo_piso,
                :precio_minimo, 
                :id_tp_servicio_pasaje,
                :estado,
                0,
                :tipo_programacion
            )
            ");

            $query->bindParam(
                ':id_terminal_origen',
                $data["terminal_origen"]
            );

            $query->bindParam(
                ':id_terminal_destino',
                $data["terminal_destino"]
            );

            $query->bindParam(
                ':id_vehiculo',
                $data["vehiculo"]
            );

            $query->bindParam(
                ':id_conductor',
                $data["conductor"]
            );

            $query->bindParam(
                ':fecha_salida',
                $data["fecha_salida"]
            );

            $query->bindParam(
                ':hora_salida',
                $data["hora_salida"]
            );

            $query->bindParam(
                ':precio_primer_piso',
                $precio_primer_piso
            );

            $query->bindParam(
                ':precio_segundo_piso',
                $precio_segundo_piso
            );

            $query->bindParam(
                ':precio_minimo',
                $precio_minimo
            );

            $query->bindParam(
                ':id_tp_servicio_pasaje',
                $tipo_servicio_pasaje
            );

            $query->bindParam(
                ':estado',
                $data["estado"]
            );

            $query->bindParam(
                ':tipo_programacion',
                $data["tipo_programacion"]
            );

            $query->execute();

            $data["id_programacion"] = $conn->lastInsertId();


            // ============================================================
            // CREAR MANIFIESTO
            // ============================================================

            if ($data["tipo_programacion"] === "1") {

                $id_serie_manifiesto =
                    $config_manifiesto["id_serie_manifiesto"];

                $correlativo =
                    $config_manifiesto["correlativo"];

                $query = $conn->prepare("
        INSERT INTO manifiesto (
            id_programacion,
            id_serie_manifiesto,
            correlativo,
            estado
        ) VALUES (
            :id_programacion,
            :id_serie_manifiesto,
            :correlativo,
            'GENERADO'
        )
    ");

                $query->bindValue(
                    ':id_programacion',
                    $data["id_programacion"],
                    PDO::PARAM_INT
                );

                $query->bindValue(
                    ':id_serie_manifiesto',
                    $id_serie_manifiesto,
                    PDO::PARAM_INT
                );

                $query->bindValue(
                    ':correlativo',
                    $correlativo,
                    PDO::PARAM_STR
                );

                $query->execute();


                // ============================================================
                // INCREMENTAR CORRELATIVO DE LA SERIE
                // ============================================================

                $query = $conn->prepare("
                UPDATE serie_manifiesto
                SET correlativo = correlativo + 1
                WHERE id_serie_manifiesto = :id_serie_manifiesto
                ");

                $query->bindValue(
                    ':id_serie_manifiesto',
                    $id_serie_manifiesto,
                    PDO::PARAM_INT
                );

                $query->execute();
            }

            if (!empty($data["personal"]) && is_array($data["personal"])) {

                $personal_regis = $this->register_personal(
                    $data,
                    $conn
                );

                if (!$personal_regis['success']) {
                    throw new Exception(
                        $personal_regis['message']
                    );
                }
            }

            if (!empty($data["terminalRuta"]) || is_array($data["terminalRuta"])) {

                $ruta_origen = $this->register_terminalRuta(
                    $data,
                    $conn
                );
                if (!$ruta_origen['success']) {
                    throw new Exception(
                        $ruta_origen['message']
                    );
                }
            }

            if (!empty($data["terminalDRuta"]) || is_array($data["terminalDRuta"])) {

                $ruta_destino = $this->register_terminalDestinoRuta(
                    $data,
                    $conn
                );

                if (!$ruta_destino['success']) {
                    throw new Exception(
                        $ruta_destino['message']
                    );
                }
            }

            $conn->commit();

            return [
                'success' => true,
                'message' => 'Registro creado con éxito'
            ];
        } catch (Exception $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        'message' => 'Ya existe un registro con estos datos'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
            }
        }
    }
    public function edit_register($data)
    {
        try {
            if (empty($data["id_programacion"])) {
                throw new Exception("El id de programación es requerido.");
            }

            $conn = $this->db->connect();
            $conn->beginTransaction();

            $tipo_servicio_pasaje = !empty($data["tp_servicio_pasaje"]) ? $data["tp_servicio_pasaje"] : null;
            $precio_primer_piso = !empty($data["precio_primer_piso"]) ? $data["precio_primer_piso"] : null;
            $precio_segundo_piso = !empty($data["precio_segundo_piso"]) ? $data["precio_segundo_piso"] : null;
            $precio_minimo = !empty($data["precio_minimo"]) ? $data["precio_minimo"] : null;

            $data["personal"] = is_array($data["personal"])
                ? $data["personal"]
                : json_decode($data["personal"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en formato JSON de personal");
            }

            $data["terminalRuta"] = is_array($data["terminalRuta"])
                ? $data["terminalRuta"]
                : json_decode($data["terminalRuta"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en formato JSON de terminalRuta");
            }

            $data["terminalDRuta"] = is_array($data["terminalDRuta"])
                ? $data["terminalDRuta"]
                : json_decode($data["terminalDRuta"], true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error en formato JSON de terminalDRuta");
            }

            if (empty($data["conductor"])) {
                $data["conductor"] = "2";
            } else {
                $query = $conn->prepare("
                SELECT id_programacion 
                FROM programacion 
                WHERE id_programacion != :id_programacion 
                AND id_conductor = :id_conductor 
                AND fecha_salida = :fecha_salida 
                AND estado = 1 
                AND liquidado = 0
                ");
                $query->bindParam(':id_programacion', $data["id_programacion"]);
                $query->bindParam(':id_conductor', $data["conductor"]);
                $query->bindParam(':fecha_salida', $data["fecha_salida"]);
                $query->execute();

                if ($query->fetchColumn()) {
                    throw new Exception('El conductor seleccionado ya tiene una programacion para salida de hoy.');
                }
            }

            $query = $conn->prepare("
            SELECT id_programacion 
            FROM programacion 
            WHERE id_programacion != :id_programacion 
            AND id_vehiculo = :id_vehiculo 
            AND fecha_salida = :fecha_salida 
            AND estado = 1 
            AND liquidado = 0
            ");
            $query->bindParam(':id_programacion', $data["id_programacion"]);
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->execute();

            if ($query->fetchColumn()) {
                throw new Exception('El vehiculo seleccionado ya tiene una programacion para salida de hoy.');
            }

            if ($data["tipo_programacion"] === "1") {

                $query = $conn->prepare("
                   SELECT sm.id_serie_manifiesto
                  FROM terminal_serie_manifiesto tsm

                  INNER JOIN serie_manifiesto sm
                        ON sm.id_serie_manifiesto = tsm.id_serie_manifiesto

                    WHERE tsm.id_terminal = :id_terminal
                  AND tsm.estado = 'ACTIVO'
                  AND sm.estado = 'ACTIVO'

                  LIMIT 1
                  ");

                $query->bindValue(
                    ':id_terminal',
                    $data["terminal_origen"],
                    PDO::PARAM_INT
                );

                $query->execute();

                $serie_activa = $query->fetchColumn();

                if (!$serie_activa) {
                    throw new Exception(
                        'El terminal de origen seleccionado no tiene una serie de manifiesto activa configurada.'
                    );
                }
            }

            $query = $conn->prepare("
            UPDATE programacion SET 
                id_terminal_origen    = :id_terminal_origen,
                id_terminal_destino   = :id_terminal_destino,
                id_vehiculo           = :id_vehiculo,
                id_conductor          = :id_conductor,
                fecha_salida          = :fecha_salida,
                hora_salida           = :hora_salida,
                precio_primer_piso    = :precio_primer_piso,
                precio_segundo_piso   = :precio_segundo_piso,
                precio_minimo         = :precio_minimo,
                id_tp_servicio_pasaje = :id_tp_servicio_pasaje,
                estado                = :estado
            WHERE id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $data["id_programacion"]);
            $query->bindParam(':id_terminal_origen', $data["terminal_origen"]);
            $query->bindParam(':id_terminal_destino', $data["terminal_destino"]);
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':id_conductor', $data["conductor"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->bindParam(':hora_salida', $data["hora_salida"]);
            $query->bindParam(':precio_primer_piso', $precio_primer_piso);
            $query->bindParam(':precio_segundo_piso', $precio_segundo_piso);
            $query->bindParam(':precio_minimo', $precio_minimo);
            $query->bindParam(':id_tp_servicio_pasaje', $tipo_servicio_pasaje);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();

            if (!empty($data["personal"]) && is_array($data["personal"])) {
                $personal_regis = $this->register_personal($data, $conn);
                if (!$personal_regis['success']) {
                    throw new Exception($personal_regis['message']);
                }
            }

            if (!empty($data["terminalRuta"]) && is_array($data["terminalRuta"])) {
                $ruta_origen = $this->register_terminalRuta($data, $conn);
                if (!$ruta_origen['success']) {
                    throw new Exception($ruta_origen['message']);
                }
            }
            if (!empty($data["terminalDRuta"]) && is_array($data["terminalDRuta"])) {
                $ruta_destino = $this->register_terminalDestinoRuta($data, $conn);
                if (!$ruta_destino['success']) {
                    throw new Exception($ruta_destino['message']);
                }
            }

            $conn->commit();
            return ['success' => true, "message" => "Registro modificado con éxito"];
        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                default:
                    return ['success' => false, "message" => $e->getMessage()];
            }
        }
    }

    public function actualizar_program($data)
    {
        try {
            $data['id_programacion'] = $data['id_program'];
            $query = $this->db->connect()->prepare("UPDATE programacion SET 
            id_conductor=:id_conductor
            WHERE id_programacion=:id_programacion");
            $query->bindParam(':id_programacion', $data["id_program"]);
            $query->bindParam(':id_conductor', $data["conductor"]);
            $query->execute();

            $data["personal"] = json_decode($data["personal"]);
            $this->register_personal($data);
            return array('success' => true, "message" => "Registro modificado con éxito", "id_programacion" => $data['id_programacion']);
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

    // public function delete_register($data)
    // {
    //     try {
    //         $query = $this->db->connect()->prepare("DELETE FROM programacion WHERE id_programacion=:id_programacion");
    //         $query->bindParam(":id_programacion", $data["id_programacion"]);
    //         $query->execute();
    //         return array('success' => true, "message" => "Registro eliminado con éxito");
    //     } catch (PDOException $e) {
    //         switch ($e->getCode()) {
    //             case '23000':
    //                 return array('success' => false, "message" => "El registro se encuentra protegido");
    //                 break;
    //             default:
    //                 return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
    //                 break;
    //         }
    //     }
    // }

    public function get_ventas_programacion($id_programacion, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
            SELECT
             pr.id_programacion_obj,
             pr.id_programacion,
             pr.id_obj_vehiculo,
             pr.id_venta,
             obj.text_obj AS asiento,
             v.estado,
             v.id_tp_comprobante,
             CONCAT(u.nombres, ' ', u.apellidos) AS cliente,
             CASE
                 WHEN pr.id_venta IS NULL THEN 'SELECCION'
                 ELSE 'VENTA'
             END AS tipo
            FROM programacion_obj pr
            LEFT JOIN venta v ON pr.id_venta = v.id_venta
            LEFT JOIN obj_vehiculo obj ON pr.id_obj_vehiculo = obj.id_obj_vehiculo
            LEFT JOIN usuario u ON v.id_cliente = u.id_usuario
            WHERE pr.id_programacion = :id_programacion AND pr.estado != 'ANULADO'
            ");
            $query->bindParam(":id_programacion", $id_programacion);
            $query->execute();
            $data_asientos = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data_asientos];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function delete_register($data)
    {
        $conn = null;

        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $registros = $this->get_ventas_programacion(
                $data['id_programacion'],
                $conn
            );

            if (!$registros['success']) {
                throw new Exception($registros['message']);
            }

            if (!empty($registros['data'])) {

                $conn->rollBack();

                return [
                    'success' => true,
                    'action' => 'show_records',
                    'message' => 'La programación tiene ventas o asientos seleccionados.',
                    'data' => $registros['data']
                ];
            }


            $query = $conn->prepare("
            UPDATE manifiesto
            SET estado = 'ANULADO'
            WHERE id_programacion = :id_programacion
            ");

            $query->bindParam(
                ":id_programacion",
                $data["id_programacion"],
                PDO::PARAM_INT
            );

            $query->execute();


            $query = $conn->prepare("
            UPDATE programacion
            SET estado = '2'
            WHERE id_programacion = :id_programacion
            ");

            $query->bindParam(
                ":id_programacion",
                $data["id_programacion"],
                PDO::PARAM_INT
            );

            $query->execute();

            $conn->commit();

            return [
                'success' => true,
                'action' => 'deleted',
                'message' => 'Registro eliminado con éxito.'
            ];
        } catch (Exception $e) {

            if ($conn && $conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        'message' => 'El registro se encuentra protegido.'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
            }
        }
    }
    public function get_allTerminalDestino($data)
    {
        try {
            if (!$data["id_terminal_origen"]) {
                return array('success' => false, "message" => null);
            }
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
            WHERE id_terminal!=:id_terminal_origen
            ");
            $query->bindParam(":id_terminal_origen", $data["id_terminal_origen"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El registro se encuentra protegido");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function register_personal($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }

        try {
            $query = $conn->prepare("
            SELECT id_personal_programacion, id_usuario 
            FROM personal_programacion 
            WHERE id_programacion = :id_programacion
            ");
            $query->bindValue(':id_programacion', $data["id_programacion"]);
            $query->execute();
            $existingPersonal = $query->fetchAll(PDO::FETCH_ASSOC);

            $existingUserIds = array_column($existingPersonal, 'id_usuario', 'id_personal_programacion');
            $newUserIds = array_column($data["personal"], 0);

            $recordsToRemove = [];
            foreach ($existingPersonal as $record) {
                if (!in_array($record['id_usuario'], $newUserIds)) {
                    $recordsToRemove[] = $record['id_personal_programacion'];
                }
            }

            if (!empty($recordsToRemove)) {
                $placeholders = str_repeat('?,', count($recordsToRemove) - 1) . '?';
                $query = $conn->prepare("
                DELETE FROM personal_programacion 
                WHERE id_programacion = ? 
                AND id_personal_programacion IN ($placeholders)
                ");
                $params = array_merge([$data["id_programacion"]], array_values($recordsToRemove));
                $query->execute($params);
            }

            foreach ($data["personal"] as $personal) {
                if (!in_array($personal[0], $existingUserIds)) {
                    $query = $conn->prepare("
                    INSERT INTO personal_programacion (id_programacion, id_usuario, id_sesionpersonal) 
                    VALUES (:id_programacion, :id_usuario, :id_sesionpersonal)
                ");
                    $query->bindValue(':id_programacion', $data["id_programacion"]);
                    $query->bindValue(':id_usuario', $personal[0]);        // bindValue, no bindParam
                    $query->bindValue(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->execute();
                }
            }

            return ['success' => true, 'message' => 'Se hizo el registro del personal con éxito'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al momento de registrar personal: ' . $e->getMessage()];
        }
    }

    public function get_personalProgramacion($data)
    {
        $query = $this->db->connect()->prepare("
        SELECT
            pp.id_personal_programacion,
            pp.id_programacion,
            pp.id_usuario,
            u.nombres AS personal_nombres,
            u.apellidos AS personal_apellidos,
            u.num_docu AS personal_num_docu,
            tp_u.descripcion AS personal_tp_usuario
        FROM personal_programacion pp
        INNER JOIN usuario u ON u.id_usuario=pp.id_usuario
        INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
        WHERE id_programacion=:id_programacion");
        $query->bindParam(':id_programacion', $data["id_programacion"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function get_allTerminalRutaProgramacion($data)
    {
        try {
            if (!$data["id_terminal_origen"]) {
                return array('success' => false, "message" => null);
            }
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
            WHERE id_terminal NOT IN (:id_terminal_origen, :id_terminal_destino)");
            $query->bindParam(":id_terminal_origen", $data["id_terminal_origen"]);
            $query->bindParam(":id_terminal_destino", $data["id_terminal_destino"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El registro se encuentra protegido");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_allTerminalRutaDestino($data)
    {
        try {
            if (!$data["id_terminal_origen"] || !$data["id_terminal_destino"]) {
                return array('success' => false, "message" => null);
            }

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
            WHERE id_terminal NOT IN (:id_terminal_origen, :id_terminal_destino)");
            $query->bindParam(":id_terminal_origen", $data["id_terminal_origen"]);
            $query->bindParam(":id_terminal_destino", $data["id_terminal_destino"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            return array(
                'success' => false,
                "message" => "Ha ocurrido un error, intentalo mas tarde.",
                $e->getCode()
            );
        }
    }

    public function get_allTerminalesParaFiltro()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                t.id_terminal,
                t.nombre,
                t.ubigeo,
                t.direccion_comercial AS direccion
            FROM terminal t
            ORDER BY t.nombre
        ");

            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array(
                'success' => true,
                'message' => $reply
            );
        } catch (PDOException $e) {
            return array(
                'success' => false,
                'message' => 'Ha ocurrido un error, intentalo mas tarde.'
            );
        }
    }

    public function register_terminalRuta($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }

        try {
            $query = $conn->prepare("
            SELECT id_ruta, id_terminal 
            FROM ruta 
            WHERE id_programacion = :id_programacion
            ");
            $query->bindValue(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $existingRoutes = $query->fetchAll(PDO::FETCH_ASSOC);

            $newTerminalIds = array_column($data["terminalRuta"], 0);

            $routesToRemove = [];
            foreach ($existingRoutes as $route) {
                if (!in_array($route['id_terminal'], $newTerminalIds)) {
                    $routesToRemove[] = $route['id_ruta'];
                }
            }

            if (!empty($routesToRemove)) {
                $placeholders = str_repeat('?,', count($routesToRemove) - 1) . '?';

                $query = $conn->prepare("
                SELECT id_ruta 
                FROM dt_venta 
                WHERE id_ruta IN ($placeholders)
                ");
                $query->execute($routesToRemove);
                $routesWithSales = $query->fetchAll(PDO::FETCH_COLUMN);

                $routesToDelete = array_diff($routesToRemove, $routesWithSales);

                if (!empty($routesToDelete)) {
                    $deletePlaceholders = str_repeat('?,', count($routesToDelete) - 1) . '?';
                    $query = $conn->prepare("
                    DELETE FROM ruta 
                    WHERE id_programacion = ? 
                    AND id_ruta IN ($deletePlaceholders)
                    ");
                    $params = array_merge([$data["id_programacion"]], array_values($routesToDelete));
                    $query->execute($params);
                }
            }

            foreach ($data["terminalRuta"] as $terminalRuta) {
                if (!isset($terminalRuta[0], $terminalRuta[1])) {
                    throw new Exception("Estructura inválida en terminalRuta");
                }

                $query = $conn->prepare("
                SELECT id_ruta 
                FROM ruta 
                WHERE id_programacion = :id_programacion 
                AND id_terminal = :id_terminal
                ");
                $query->bindValue(':id_programacion', $data["id_programacion"]);
                $query->bindValue(':id_terminal', $terminalRuta[0]);
                $query->execute();
                $existingRoute = $query->fetch(PDO::FETCH_ASSOC);

                if ($existingRoute) {
                    $query = $conn->prepare("
                    UPDATE ruta 
                    SET hora = :hora 
                    WHERE id_ruta = :id_ruta
                ");
                    $query->bindValue(':hora', $terminalRuta[1]);
                    $query->bindValue(':id_ruta', $existingRoute['id_ruta']);
                    $query->execute();
                } else {
                    $query = $conn->prepare("
                    INSERT INTO ruta (id_programacion, id_terminal, hora, id_sesionpersonal) 
                    VALUES (:id_programacion, :id_terminal, :hora, :id_sesionpersonal)
                ");
                    $query->bindValue(':id_programacion', $data["id_programacion"]);
                    $query->bindValue(':id_terminal', $terminalRuta[0]);
                    $query->bindValue(':hora', $terminalRuta[1]);
                    $query->bindValue(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->execute();
                }
            }

            return ['success' => true, "message" => "Rutas de origen registradas con éxito"];
        } catch (Exception $e) {
            return ['success' => false, "message" => "Error al registrar las rutas: " . $e->getMessage()];
        }
    }

    public function register_terminalDestinoRuta($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }

        try {

            $query = $conn->prepare("
            SELECT id_ruta_destino, id_terminal 
            FROM rutas_destino
            WHERE id_programacion = :id_programacion
           ");
            $query->bindValue(":id_programacion", $data["id_programacion"]);
            $query->execute();
            $existingRoutes = $query->fetchAll(PDO::FETCH_ASSOC);

            $newTerminalIds = array_column($data["terminalDRuta"], 'id_terminal');

            $routesToRemove = [];
            foreach ($existingRoutes as $route) {
                if (!in_array($route['id_terminal'], $newTerminalIds)) {
                    $routesToRemove[] = $route['id_ruta_destino'];
                }
            }

            if (!empty($routesToRemove)) {
                $placeholders = str_repeat('?,', count($routesToRemove) - 1) . '?';

                $query = $conn->prepare("
                SELECT id_destino 
                FROM dt_venta 
                WHERE id_destino IN ($placeholders)
                ");
                $query->execute($routesToRemove);
                $routesWithSales = $query->fetchAll(PDO::FETCH_COLUMN);

                $routesToDelete = array_diff($routesToRemove, $routesWithSales);

                if (!empty($routesToDelete)) {
                    $deletePlaceholders = str_repeat('?,', count($routesToDelete) - 1) . '?';
                    $query = $conn->prepare("
                    DELETE FROM rutas_destino 
                    WHERE id_programacion = ? 
                    AND id_ruta_destino IN ($deletePlaceholders)
                    ");
                    $params = array_merge([$data["id_programacion"]], array_values($routesToDelete));
                    $query->execute($params);
                }
            }

            foreach ($data["terminalDRuta"] as $terminalRuta) {
                if (
                    !isset(
                        $terminalRuta['id_terminal'],
                        $terminalRuta['hora'],
                        $terminalRuta['precio_primer_piso'],
                        $terminalRuta['precio_segundo_piso']
                    )
                ) {
                    throw new Exception("Estructura inválida en terminalDRuta");
                }

                $query = $conn->prepare("
                SELECT id_ruta_destino 
                FROM rutas_destino
                WHERE id_programacion = :id_programacion 
                AND id_terminal = :id_terminal
                ");
                $query->bindValue(':id_programacion', $data["id_programacion"]);
                $query->bindValue(':id_terminal', $terminalRuta['id_terminal']);
                $query->execute();
                $existingRoute = $query->fetch(PDO::FETCH_ASSOC);

                if ($existingRoute) {
                    $query = $conn->prepare("
                    UPDATE rutas_destino
                    SET hora                = :hora,
                        precio_primer_piso  = :precio_primer_piso,
                        precio_segundo_piso = :precio_segundo_piso
                    WHERE id_ruta_destino = :id_ruta_destino
                    ");
                    $query->bindValue(':hora', $terminalRuta['hora']);
                    $query->bindValue(':precio_primer_piso', $terminalRuta['precio_primer_piso']);
                    $query->bindValue(':precio_segundo_piso', $terminalRuta['precio_segundo_piso']);
                    $query->bindValue(':id_ruta_destino', $existingRoute['id_ruta_destino']);
                    $query->execute();
                } else {
                    $query = $conn->prepare("
                    INSERT INTO rutas_destino (id_programacion, id_terminal, hora, precio_primer_piso, precio_segundo_piso, id_sesionpersonal) 
                    VALUES (:id_programacion, :id_terminal, :hora, :precio_primer_piso, :precio_segundo_piso, :id_sesionpersonal)
                    ");
                    $query->bindValue(':id_programacion', $data["id_programacion"]);
                    $query->bindValue(':id_terminal', $terminalRuta['id_terminal']);
                    $query->bindValue(':hora', $terminalRuta['hora']);
                    $query->bindValue(':precio_primer_piso', $terminalRuta['precio_primer_piso']);
                    $query->bindValue(':precio_segundo_piso', $terminalRuta['precio_segundo_piso']);
                    $query->bindValue(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->execute();
                }
            }

            return ['success' => true, "message" => "Rutas de destino registradas con éxito"];
        } catch (Exception $e) {
            return ['success' => false, "message" => "Error al registrar las rutas destino: " . $e->getMessage()];
        }
    }

    public function get_terminalRuta($data)
    {
        $query = $this->db->connect()->prepare("
        SELECT
            r.id_ruta,
            r.id_programacion,
            r.id_terminal,
            t.nombre AS terminal_nombre,
            r.hora
        FROM ruta r
        INNER JOIN terminal t ON t.id_terminal=r.id_terminal
        WHERE id_programacion=:id_programacion");
        $query->bindParam(':id_programacion', $data["id_programacion"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function get_terminalDRuta($data)
    {
        $query = $this->db->connect()->prepare("
        SELECT
            r.id_ruta_destino,
            r.id_programacion,
            r.id_terminal,
            t.nombre AS terminal_nombre,
            r.hora,
            r.precio_primer_piso,
            r.precio_segundo_piso

        FROM rutas_destino r
        INNER JOIN terminal t ON t.id_terminal=r.id_terminal
        WHERE id_programacion=:id_programacion");
        $query->bindParam(':id_programacion', $data["id_programacion"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function cambiar_carro($data)
    {
        // 1) Obtener los asientos vendidos y sus nombres en el vehículo anterior
        $asientos_vendidos = $this->obtener_asientos_vendidos($data);

        // Si no hay asientos vendidos, simplemente cambiamos el vehículo directo
        if (empty($asientos_vendidos)) {
            $query1 = $this->db->connect()->prepare("
            UPDATE programacion
            SET id_vehiculo = :id_vehiculo
            WHERE id_programacion = :id_programacion
            ");
            $query1->bindParam(':id_vehiculo', $data['vehiculo']);
            $query1->bindParam(':id_programacion', $data['id_programacion']);
            $ok = $query1->execute();

            return $ok
                ? ["success" => true, "message" => "Vehículo cambiado exitosamente (sin asientos vendidos)"]
                : ["success" => false, "message" => "No se pudo actualizar el vehículo"];
        }

        $nombres_asientos = [];
        foreach ($asientos_vendidos as $asiento) {
            $nombres_asientos[] = $asiento['nombre_asiento'];
        }

        // 2) Obtener los IDs de los asientos correspondientes en el nuevo vehículo
        $nuevos_ids_asientos = $this->obtener_nuevos_ids_asientos($data, $nombres_asientos);

        if (!$nuevos_ids_asientos) {
            return ["success" => false, "message" => "Los datos del nuevo vehiculo no coinciden con el anterior"];
        }

        // Indexar los nuevos asientos por nombre para búsqueda rápida (evita foreach anidado)
        $mapa_nuevos = [];
        foreach ($nuevos_ids_asientos as $nuevo_asiento) {
            $mapa_nuevos[$nuevo_asiento['nombre_asiento']] = $nuevo_asiento['id_asiento'];
        }

        // 3) VALIDACIÓN PREVIA: asegurarnos de que TODOS los asientos vendidos
        //    tengan un equivalente en el vehículo nuevo, antes de tocar la BD
        $sin_equivalente = [];
        foreach ($asientos_vendidos as $asiento) {
            if (!array_key_exists($asiento['nombre_asiento'], $mapa_nuevos)) {
                $sin_equivalente[] = $asiento['nombre_asiento'];
            }
        }

        if (!empty($sin_equivalente)) {
            return [
                "success" => false,
                "message" => "El nuevo vehículo no tiene asientos equivalentes para: " . implode(', ', $sin_equivalente)
            ];
        }

        // 4) Ejecutar todo dentro de una transacción
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            $query = $conn->prepare("
            UPDATE programacion_obj
            SET id_obj_vehiculo = :nuevo_id_asiento
            WHERE id_obj_vehiculo = :id_asiento_vendido AND id_programacion = :id_programacion
            ");

            foreach ($asientos_vendidos as $asiento) {
                $nombre_asiento = $asiento['nombre_asiento'];
                $id_asiento_vendido = $asiento['id_asiento'];
                $nuevo_id_asiento = $mapa_nuevos[$nombre_asiento];

                $query->bindParam(':nuevo_id_asiento', $nuevo_id_asiento);
                $query->bindParam(':id_asiento_vendido', $id_asiento_vendido);
                $query->bindParam(':id_programacion', $data['id_programacion']);
                $query->execute();

                // rowCount() = 0 significa que el WHERE no encontró esa fila -> algo no cuadra
                if ($query->rowCount() === 0) {
                    throw new Exception("No se encontró el asiento '{$nombre_asiento}' (id {$id_asiento_vendido}) para actualizar");
                }
            }

            // Solo si TODOS los asientos se actualizaron correctamente, cambiamos el vehículo
            $query1 = $conn->prepare("
            UPDATE programacion
            SET id_vehiculo = :id_vehiculo
            WHERE id_programacion = :id_programacion
            ");
            $query1->bindParam(':id_vehiculo', $data['vehiculo']);
            $query1->bindParam(':id_programacion', $data['id_programacion']);
            $query1->execute();

            if ($query1->rowCount() === 0) {
                throw new Exception("No se pudo actualizar la programación con el nuevo vehículo");
            }

            $conn->commit();

            return ["success" => true, "message" => "Asientos actualizados exitosamente y carro cambiado!"];
        } catch (Exception $e) {
            $conn->rollBack();
            return ["success" => false, "message" => "Algo ha salido mal en el proceso: " . $e->getMessage()];
        }
    }

    public function obtener_asientos_vendidos($data)
    {
        $query = $this->db->connect()->prepare("
            SELECT po.id_obj_vehiculo AS id_asiento, ov.text_obj AS nombre_asiento
            FROM programacion_obj po
            JOIN obj_vehiculo ov ON po.id_obj_vehiculo = ov.id_obj_vehiculo
            WHERE po.id_programacion = :id_programacion AND po.estado = 'VENDIDO'
        ");
        $query->bindParam(':id_programacion', $data["id_programacion"]);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

        // Limpiar los nombres de asientos
        foreach ($resultados as &$resultado) {
            $resultado['nombre_asiento'] = trim($resultado['nombre_asiento']);
        }

        return $resultados;
    }

    public function obtener_nuevos_ids_asientos($data, $nombres_asientos)
    {
        $nombres_asientos_in = implode(',', array_map(function ($nombre) {
            return trim($nombre);
        }, $nombres_asientos));

        $query = $this->db->connect()->prepare("
            SELECT id_obj_vehiculo AS id_asiento, text_obj AS nombre_asiento
            FROM obj_vehiculo
            WHERE id_vehiculo = :id_vehiculo AND text_obj IN ($nombres_asientos_in)
        ");
        $query->bindParam(':id_vehiculo', $data["vehiculo"]);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

        // Limpiar los nombres de asientos
        foreach ($resultados as &$resultado) {
            $resultado['nombre_asiento'] = trim($resultado['nombre_asiento']);
        }

        return $resultados;
    }
}
