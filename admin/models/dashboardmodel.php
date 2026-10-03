<?php

use function PHPSTORM_META\map;

date_default_timezone_set("America/Lima");

class DashboardModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    private function getFiltroFechaDashboard($data, string $campoFecha): array
    {
        $periodo = $data['periodo'] ?? 'todos';
        $fecha_inicio = $data['fecha_inicio'] ?? '';
        $fecha_fin = $data['fecha_fin'] ?? '';

        $where = "";
        $params = [];

        switch ($periodo) {
            case 'ultima_semana':
                $where .= " AND DATE($campoFecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
                break;

            case 'por_mes':
                if (!empty($fecha_inicio)) {
                    $where .= " AND DATE_FORMAT($campoFecha, '%Y-%m') = :fecha_inicio ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                }
                break;

            case 'entre_meses':
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $where .= " AND DATE_FORMAT($campoFecha, '%Y-%m') BETWEEN :fecha_inicio AND :fecha_fin ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                    $params[':fecha_fin'] = $fecha_fin;
                }
                break;

            case 'por_fecha':
                if (!empty($fecha_inicio)) {
                    $where .= " AND DATE($campoFecha) = :fecha_inicio ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                }
                break;

            case 'entre_fechas':
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $where .= " AND DATE($campoFecha) BETWEEN :fecha_inicio AND :fecha_fin ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                    $params[':fecha_fin'] = $fecha_fin;
                }
                break;

            case 'todos':
            default:
                $where .= " AND $campoFecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) ";
                break;
        }

        return [
            'where' => $where,
            'params' => $params
        ];
    }

    public function dataTableProgramacion($data)
    {
        try {
            $db = $this->db->connect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 20;
            $search = trim($data['search']['value'] ?? '');

            $filtro = $this->getFiltroFechaDashboard($data, 'p.fecha_salida');

            $baseQuery = "
            FROM programacion p
            LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
            LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
            WHERE p.estado = 1
            {$filtro['where']}
        ";

            $params = $filtro['params'];

            $searchWhere = "";

            if ($search !== '') {
                $searchWhere = "
                AND (
                    t_o.nombre LIKE :search
                    OR t_d.nombre LIKE :search
                    OR vh.placa LIKE :search
                    OR p.fecha_salida LIKE :search
                    OR p.hora_salida LIKE :search
                )
            ";

                $params[':search'] = "%$search%";
            }

            $totalQuery = "SELECT COUNT(*) " . $baseQuery;

            $stmt = $db->prepare($totalQuery);

            foreach ($filtro['params'] as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            $filteredQuery = "SELECT COUNT(*) " . $baseQuery . $searchWhere;

            $stmt = $db->prepare($filteredQuery);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            $dataQuery = "
            SELECT
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                vh.placa AS vehiculo_placa,
                p.fecha_salida,
                p.hora_salida
            $baseQuery
            $searchWhere
            ORDER BY p.fecha_salida ASC, p.hora_salida ASC
            LIMIT :start, :length
        ";

            $stmt = $db->prepare($dataQuery);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':start', (int) $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int) $length, PDO::PARAM_INT);

            $stmt->execute();

            $reply = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($reply as &$r) {
                $r['hora_salida'] = formato_12_horas($r['hora_salida']);
            }

            return [
                "draw" => intval($draw),
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $reply
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

    private function getFiltrosDashboard($data): array
    {
        $terminal = $data['terminal'] ?? '';
        $periodo = $data['periodo'] ?? 'todos';
        $fecha_inicio = $data['fecha_inicio'] ?? '';
        $fecha_fin = $data['fecha_fin'] ?? '';

        $where = "";
        $params = [];

        if (!empty($terminal) && $terminal !== 'todos') {
            $where .= " AND v.id_terminal = :terminal ";
            $params[':terminal'] = $terminal;
        }

        switch ($periodo) {
            case 'ultima_semana':
                $where .= " AND DATE(v.fecha_emision) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
                break;

            case 'por_mes':
                if (!empty($fecha_inicio)) {
                    $where .= " AND DATE_FORMAT(v.fecha_emision, '%Y-%m') = :fecha_inicio ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                }
                break;

            case 'entre_meses':
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $where .= " AND DATE_FORMAT(v.fecha_emision, '%Y-%m') BETWEEN :fecha_inicio AND :fecha_fin ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                    $params[':fecha_fin'] = $fecha_fin;
                }
                break;

            case 'por_fecha':
                if (!empty($fecha_inicio)) {
                    $where .= " AND DATE(v.fecha_emision) = :fecha_inicio ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                }
                break;

            case 'entre_fechas':
                if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                    $where .= " AND DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin ";
                    $params[':fecha_inicio'] = $fecha_inicio;
                    $params[':fecha_fin'] = $fecha_fin;
                }
                break;

            case 'todos':
            default:
                $where .= " AND v.fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) ";
                break;
        }

        return [
            'where' => $where,
            'params' => $params
        ];
    }

    private function bindParamsDashboard($query, array $params): void
    {
        foreach ($params as $key => $value) {
            $query->bindValue($key, $value);
        }
    }

    public function get_info_dashboard($data)
    {
        $filtro = $this->getFiltrosDashboard($data);

        $total_comprobantes = 0;
        $total_factura = 0;
        $total_boleta = 0;
        $total_notas = 0;
        $total_venta_pasaje = 0;
        $total_venta_encomienda = 0;
        $total_programaciones = 0;
        $total_venta_facturador = 0;
        $total_personal = 0;

        $conn = $this->db->connect();
        $query = $conn->prepare("SELECT * FROM empresa");
        $query->execute();
        $empresa = $query->fetch(PDO::FETCH_ASSOC);

        switch ($this->tp_usuario_sesion) {
            case 1:
            case 2:

                $query = $conn->prepare("
                SELECT COUNT(*) AS total_comprobantes
                FROM venta v
                WHERE v.id_tp_comprobante IN (1,3)
                  AND v.envio_sunat = 1
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_comprobantes = $query->fetch(PDO::FETCH_ASSOC)["total_comprobantes"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS total_factura
                FROM venta v
                WHERE v.id_tp_comprobante = 1
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_factura = $query->fetch(PDO::FETCH_ASSOC)["total_factura"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS total_boleta
                FROM venta v
                WHERE v.id_tp_comprobante = 3
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_boleta = $query->fetch(PDO::FETCH_ASSOC)["total_boleta"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS total_notas
                FROM venta v
                WHERE v.id_tp_comprobante = 2
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_notas = $query->fetch(PDO::FETCH_ASSOC)["total_notas"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 1
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_venta_pasaje = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 2
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_venta_encomienda = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 3
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  {$filtro['where']}
                ");
                $this->bindParamsDashboard($query, $filtro['params']);
                $query->execute();
                $total_venta_facturador = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM programacion p
                WHERE p.estado = 1
                ");
                $query->execute();
                $total_programaciones = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM usuario u
                WHERE u.estado = 1
                  AND u.id_tp_usuario NOT IN (6, 7)
                ");
                $query->execute();
                $total_personal = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                break;

            case 3:

                $whereUsuario = $filtro['where'] . " AND v.id_vendedor = :id_vendedor ";
                $paramsUsuario = $filtro['params'];
                $paramsUsuario[':id_vendedor'] = $this->id_usuario_sesion;

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 1
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_pasaje = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 2
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_encomienda = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 3
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_facturador = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM programacion p
                WHERE p.estado = 1
                  AND p.id_terminal_origen = :id_terminal
                ");
                $query->bindParam(":id_terminal", $this->id_terminal_sesion);
                $query->execute();
                $total_programaciones = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM usuario u
                WHERE u.estado = 1
                  AND u.id_terminal = :id_terminal
                  AND u.id_tp_usuario NOT IN (6, 7)
                ");
                $query->bindParam(":id_terminal", $this->id_terminal_sesion);
                $query->execute();
                $total_personal = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                break;

            case 'encargado':

                $whereTerminalSesion = $filtro['where'] . " AND v.id_terminal = :id_terminal_sesion ";
                $paramsTerminalSesion = $filtro['params'];
                $paramsTerminalSesion[':id_terminal_sesion'] = $this->id_terminal_sesion;

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 1
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereTerminalSesion
                ");
                $this->bindParamsDashboard($query, $paramsTerminalSesion);
                $query->execute();
                $total_venta_pasaje = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 2
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereTerminalSesion
                ");
                $this->bindParamsDashboard($query, $paramsTerminalSesion);
                $query->execute();
                $total_venta_encomienda = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 3
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereTerminalSesion
                ");
                $this->bindParamsDashboard($query, $paramsTerminalSesion);
                $query->execute();
                $total_venta_facturador = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM programacion p
                WHERE p.estado = 1
                  AND p.id_terminal_origen = :id_terminal
                ");
                $query->bindParam(":id_terminal", $this->id_terminal_sesion);
                $query->execute();
                $total_programaciones = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COUNT(*) AS amount_register
                FROM usuario u
                WHERE u.estado = 1
                  AND u.id_terminal = :id_terminal
                  AND u.id_tp_usuario NOT IN (6, 7)
                ");
                $query->bindParam(":id_terminal", $this->id_terminal_sesion);
                $query->execute();
                $total_personal = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                break;

            case 14:
            case 15:

                $whereUsuario = $filtro['where'] . " AND v.id_vendedor = :id_vendedor ";
                $paramsUsuario = $filtro['params'];
                $paramsUsuario[':id_vendedor'] = $this->id_usuario_sesion;

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 1
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_pasaje = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 2
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_encomienda = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $query = $conn->prepare("
                SELECT COALESCE(SUM(v.total), 0.00) AS amount_register
                FROM venta v
                WHERE v.id_tp_venta = 3
                  AND v.estado NOT IN ('ANULADO', 'CANCELADO')
                  $whereUsuario
                ");
                $this->bindParamsDashboard($query, $paramsUsuario);
                $query->execute();
                $total_venta_facturador = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

                $total_programaciones = 0;
                $total_personal = 0;

                break;

            default:
                return false;
        }

        $cantidad_registro = [];

        if (in_array($this->tp_usuario_sesion, [1, 2])) {
            $cantidad_registro[] = [
                'nombre' => 'Total de comprobantes emitidos',
                'amount' => $total_comprobantes,
                'icon' => '<i class="fa-solid fa-note-sticky"></i>',
                'id' => 'consumo',
            ];

            $cantidad_registro[] = [
                'nombre' => 'Monto total de Facturas',
                'amount' => "S/ " . number_format($total_factura, 2),
                'icon' => '<i class="fas fa-dollar-sign fa-1x"></i>',
                'id' => 'consumo',
            ];

            $cantidad_registro[] = [
                'nombre' => 'Monto total de Boletas',
                'amount' => "S/ " . number_format($total_boleta, 2),
                'icon' => '<i class="fa-solid fa-dollar-sign"></i>',
                'id' => 'consumo',
            ];

            $cantidad_registro[] = [
                'nombre' => 'Monto total de Notas',
                'amount' => "S/ " . number_format($total_notas, 2),
                'icon' => '<i class="fa-solid fa-dollar-sign"></i>',
                'id' => 'consumo',
            ];
        }

        $cantidad_registro[] = [
            'nombre' => 'Venta Pasaje',
            'amount' => "S/ " . number_format($total_venta_pasaje, 2),
            'icon' => '<i class="fa-solid fa-dollar-sign"></i>',
            'id' => 'consumo',
        ];

        $cantidad_registro[] = [
            'nombre' => 'Venta Encomienda',
            'amount' => "S/ " . number_format($total_venta_encomienda, 2),
            'icon' => '<i class="fa-solid fa-truck-fast" style="color:rgb(80, 219, 75);"></i>',
            'id' => 'consumo',
        ];

        $cantidad_registro[] = [
            'nombre' => 'Venta Facturador',
            'amount' => "S/ " . number_format($total_venta_facturador, 2),
            'icon' => '<i class="fa-solid fa-truck-fast" style="color:rgb(80, 219, 75);"></i>',
            'id' => 'consumo',
        ];

        $cantidad_registro[] = [
            'nombre' => 'Programaciones',
            'amount' => $total_programaciones,
            'icon' => '<i class="fa-solid fa-calendar-days" style="color:rgb(219, 116, 75);"></i>',
            'id' => 'consumo',
        ];

        return [
            "cantidad_registro" => $cantidad_registro,
            "empresa" => $empresa
        ];
    }

    public function totales_meses($data)
    {
        try {

            if (!($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2)) {
                return [];
            }

            $filtro = $this->getFiltrosDashboard($data);

            $query = $this->db->connect()->prepare("
            SELECT
                YEAR(v.fecha_emision) AS año,
                MONTH(v.fecha_emision) AS mes,
                MONTHNAME(v.fecha_emision) AS nombre_mes,

                COALESCE(SUM(v.total), 0.00) AS total_ventas,

                COALESCE(
                    SUM(
                        CASE
                            WHEN v.id_tp_venta = 1 THEN v.total
                            ELSE 0
                        END
                    ),
                0.00) AS total_pasajes,

                COALESCE(
                    SUM(
                        CASE
                            WHEN v.id_tp_venta = 2 THEN v.total
                            ELSE 0
                        END
                    ),
                0.00) AS total_encomiendas,

                COUNT(v.id_venta) AS cantidad_ventas

            FROM venta v
            WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
            {$filtro['where']}

            GROUP BY YEAR(v.fecha_emision), MONTH(v.fecha_emision)

            ORDER BY año ASC, mes ASC
            ");

            $this->bindParamsDashboard(
                $query,
                $filtro['params']
            );

            $query->execute();

            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            $datos_grafico = [];

            foreach ($resultados as $fila) {

                $datos_grafico[] = [
                    'periodo' => traducir_mes($fila['nombre_mes']) . ' ' . $fila['año'],
                    'mes' => $fila['mes'],
                    'año' => $fila['año'],
                    'total' => (float) $fila['total_ventas'],
                    'total_pasajes' => (float) $fila['total_pasajes'],
                    'total_encomiendas' => (float) $fila['total_encomiendas'],
                    'cantidad_ventas' => (int) $fila['cantidad_ventas']
                ];
            }

            return $datos_grafico;

        } catch (PDOException $e) {

            error_log(
                "Error en totales_meses: " .
                $e->getMessage()
            );

            return [''];
        }
    }


    // Función para obtener ventas por terminales (últimos 6 meses)
    public function ventas_por_terminales($data)
    {
        try {
            $datos_grafico = [];

            if (!($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2)) {
                return $datos_grafico;
            }

            $filtro = $this->getFiltrosDashboard($data);

            $query = $this->db->connect()->prepare("
            SELECT
                v.id_terminal,
                t.nombre,
                t.cod_domicilio_fiscal AS descripcion_terminal,
                YEAR(v.fecha_emision) AS año,
                MONTH(v.fecha_emision) AS mes,
                MONTHNAME(v.fecha_emision) AS nombre_mes,
                COALESCE(SUM(v.total), 0.00) AS total_ventas,
                COUNT(v.id_venta) AS cantidad_ventas
            FROM venta v
            LEFT JOIN terminal t ON v.id_terminal = t.id_terminal
            WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
            {$filtro['where']}
            GROUP BY 
                v.id_terminal,
                t.nombre,
                t.cod_domicilio_fiscal,
                YEAR(v.fecha_emision),
                MONTH(v.fecha_emision)
            ORDER BY 
                v.id_terminal ASC,
                YEAR(v.fecha_emision) ASC,
                MONTH(v.fecha_emision) ASC
        ");

            $this->bindParamsDashboard($query, $filtro['params']);

            $query->execute();
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultados as $fila) {
                $datos_grafico[] = [
                    'id_terminal' => (int) $fila['id_terminal'],
                    'nombre_terminal' => $fila['nombre'] ?: 'Terminal ' . $fila['id_terminal'],
                    'descripcion_terminal' => $fila['descripcion_terminal'] ?: '',
                    'periodo' => traducir_mes($fila['nombre_mes']) . ' ' . $fila['año'],
                    'mes' => (int) $fila['mes'],
                    'año' => (int) $fila['año'],
                    'total' => (float) $fila['total_ventas'],
                    'cantidad_ventas' => (int) $fila['cantidad_ventas']
                ];
            }

            return $datos_grafico;

        } catch (PDOException $e) {
            error_log("Error en ventas_por_terminales: " . $e->getMessage());
            return [];
        }
    }

    // Función para obtener ventas por vendedores (últimos 6 meses)
    public function ventas_por_vendedores($data)
    {
        try {
            $datos_grafico = [];

            if (!($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2)) {
                return $datos_grafico;
            }

            $filtro = $this->getFiltrosDashboard($data);

            $query = $this->db->connect()->prepare("
            SELECT
                v.id_vendedor,
                vend.nombres,
                vend.apellidos,
                CONCAT(vend.nombres, ' ', vend.apellidos) AS nombre_completo,
                YEAR(v.fecha_emision) AS año,
                MONTH(v.fecha_emision) AS mes,
                MONTHNAME(v.fecha_emision) AS nombre_mes,
                COALESCE(SUM(v.total), 0.00) AS total_ventas,
                COUNT(v.id_venta) AS cantidad_ventas
            FROM venta v
            LEFT JOIN usuario vend ON v.id_vendedor = vend.id_usuario
            WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
            {$filtro['where']}
            GROUP BY 
                v.id_vendedor,
                vend.nombres,
                vend.apellidos,
                YEAR(v.fecha_emision),
                MONTH(v.fecha_emision)
            ORDER BY 
                v.id_vendedor ASC,
                YEAR(v.fecha_emision) ASC,
                MONTH(v.fecha_emision) ASC
        ");

            $this->bindParamsDashboard($query, $filtro['params']);

            $query->execute();
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultados as $fila) {
                $datos_grafico[] = [
                    'id_vendedor' => (int) $fila['id_vendedor'],
                    'nombre_vendedor' => $fila['nombres'] ?: '',
                    'apellido_vendedor' => $fila['apellidos'] ?: '',
                    'nombre_completo' => $fila['nombre_completo'] ?: 'Vendedor ' . $fila['id_vendedor'],
                    'periodo' => traducir_mes($fila['nombre_mes']) . ' ' . $fila['año'],
                    'mes' => (int) $fila['mes'],
                    'año' => (int) $fila['año'],
                    'total' => (float) $fila['total_ventas'],
                    'cantidad_ventas' => (int) $fila['cantidad_ventas']
                ];
            }

            return $datos_grafico;

        } catch (PDOException $e) {
            error_log("Error en ventas_por_vendedores: " . $e->getMessage());
            return [];
        }
    }


    public function obtenerResumenMensual($data)
    {
        $resultado_final = [];

        if (!($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2)) {
            return ['data' => $resultado_final];
        }

        $filtro = $this->getFiltrosDashboard($data);

        $query_ventas = $this->db->connect()->prepare("
        SELECT
            MONTH(v.fecha_emision) AS mes,
            YEAR(v.fecha_emision) AS año,
            COALESCE(SUM(CASE WHEN v.id_tp_comprobante IN (1, 3) THEN v.total ELSE 0 END), 0) AS ventas_sunat,
            COALESCE(SUM(CASE WHEN v.id_tp_comprobante = 2 THEN v.total ELSE 0 END), 0) AS ventas_internas
        FROM venta v
        WHERE v.estado NOT IN ('ANULADO', 'CANCELADO')
        {$filtro['where']}
        GROUP BY YEAR(v.fecha_emision), MONTH(v.fecha_emision)
        ORDER BY año ASC, mes ASC
    ");

        $this->bindParamsDashboard($query_ventas, $filtro['params']);
        $query_ventas->execute();
        $ventas = $query_ventas->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ventas as $venta) {
            $resultado_final[] = [
                'nombre_mes' => traducir_mes(date('F', mktime(0, 0, 0, $venta['mes'], 1))),
                'mes' => (int) $venta['mes'],
                'año' => (int) $venta['año'],
                'ventas_sunat' => (float) $venta['ventas_sunat'],
                'ventas_internas' => (float) $venta['ventas_internas'],
                'compras_gastos' => 0
            ];
        }

        return ['data' => $resultado_final];
    }
}
