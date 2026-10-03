<?php
class Encomiendas_ReportModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            // ----------------------------
            // Comprobantes
            // ----------------------------

            if (!empty($data["tp_comprobante"]) && $data["tp_comprobante"] !== 'TODO') {
                if ($data['tp_comprobante'] === "7") {
                    $tp_comprobante_raw =  "7,8";
                } else {
                    $tp_comprobante_raw =  $data["tp_comprobante"];
                }
            } else if ($data['tp_comprobante'] === "0") {
                $tp_comprobante_raw = "1,3";
            } else {
                $tp_comprobante_raw = "1,2,3";
            }

            // Como runDataTableQuery no recibe params, incrustamos directo
            $tp_comprobante_array = array_map('intval', explode(',', $tp_comprobante_raw));
            $placeholders = implode(',', $tp_comprobante_array);

            // ----------------------------
            // Filtros extra
            // ----------------------------
            $terminal     = $data["terminal"]     ?? null;
            $forma_pago   = $data["forma_pago"]   ?? null;
            $estado_pago  = $data["estado_pago"]  ?? null;
            $fecha_inicio = $data["fecha_inicio"] ?? null;
            $fecha_fin    = $data["fecha_fin"]    ?? null;

            $filtros = "";

            if ($terminal) {
                $filtros .= " AND v.id_terminal = " . intval($terminal);
            }

            if ($forma_pago) {
                $filtros .= " AND f.id_forma_pago = " . intval($forma_pago);
            }

            if ($estado_pago) {
                $filtros .= " AND e.pago = " . $this->db->connect()->quote($estado_pago);
            }

            if ($fecha_inicio && $fecha_fin) {
                $filtros .= " AND DATE(v.fecha_emision) BETWEEN " .
                    $this->db->connect()->quote($fecha_inicio) .
                    " AND " .
                    $this->db->connect()->quote($fecha_fin);
            } elseif ($fecha_inicio) {
                $fecha_fin = date('Y-m-d');
                $filtros .= " AND DATE(v.fecha_emision) BETWEEN " .
                    $this->db->connect()->quote($fecha_inicio) .
                    " AND " .
                    $this->db->connect()->quote($fecha_fin);
            }

            // ----------------------------
            // Usuario logueado
            // ----------------------------
            $tp_usuario = $this->tp_usuario_sesion;
            $restriccionTerminal = "";
            if ($tp_usuario != 1 && $tp_usuario != 2) {
                $restriccionTerminal = " AND (e.id_terminal_origen = " . intval($this->id_terminal_sesion) .
                    " OR e.id_terminal_destino = " . intval($this->id_terminal_sesion) . ")";
            }

            // ----------------------------
            // Select + BaseQuery
            // ----------------------------
            $selectFields = "
        v.id_venta,
        v.id_terminal,
        f.descripcion AS forma_pago,
        t.nombre AS terminal,
        v.id_vendedor,
        v.id_cliente,
        c_r.nombres AS remitente_nombres,
        c_r.apellidos AS remitente_apellidos,
        c_r.num_docu AS remitente_num_docu,
        c_d.nombres AS destinatario_nombres,
        c_d.apellidos AS destinatario_apellidos,
        c_d.num_docu AS destinatario_num_docu,
        e.fecha_salida AS encomienda_fecha_salida,
        e.estado AS encomienda_estado_envio,
        t_d.nombre AS destino,
        v.id_forma_pago,
        v.id_medio_pago,
        v.id_tp_moneda,
        v.id_tp_comprobante,
        v.id_caja_chica,
        v.id_serie,
        v.serie,
        v.correlativo,
        CONCAT(v.serie, '-', v.correlativo) AS numero,
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
        e.pago AS estado_pago,
        p.fecha_salida AS programacion_fecha_salida,
        p.hora_salida AS programacion_hora_salida,
        dt_v.id_encomienda AS id_encomienda
        ";

            $baseQuery = "
        FROM venta v
        LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN forma_pago f ON f.id_forma_pago = v.id_forma_pago
        LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
        LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN terminal t ON t.id_terminal = v.id_terminal
        LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
        WHERE tp_s.descripcion = 'ENCOMIENDA'
        AND v.id_tp_comprobante IN ($placeholders)
        $restriccionTerminal
        $filtros
        ";

            // ----------------------------
            // Columnas buscables
            // ----------------------------
            $searchColumns = [
                "v.serie",
                "v.correlativo",
                "CONCAT(v.serie, '-', v.correlativo)",
                "c_r.nombres",
                "CONCAT(c_r.nombres, ' ', c_r.apellidos)",
                "CONCAT(c_r.nombres, ' ', c_r.apellidos, ' ', c_r.num_docu)",
                "c_r.apellidos",
                "c_d.nombres",
                "c_d.apellidos",
                "CONCAT(c_d.nombres, ' ', c_d.apellidos)",
                "CONCAT(c_d.nombres, ' ', c_d.apellidos, ' ', c_d.num_docu)",
                "t.nombre",
                "CASE v.envio_sunat WHEN 0 THEN 'SIN ENVIAR' WHEN 1 THEN 'ENVIADO A SUNAT' WHEN 2 THEN 'ENVIADO POR RESUMEN' ELSE 'DESCONOCIDO' END",
                "t_d.nombre",
                "e.pago",
                "v.op_gravada",
                "v.op_inafecta",
                "v.op_exonerada",
                "v.op_igv",
                "v.total",
                "f.descripcion",
            ];

            $orderBy = "v.fecha_emision DESC";

            // ----------------------------
            // Ejecutar con tu helper
            // ----------------------------
            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );
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
}
