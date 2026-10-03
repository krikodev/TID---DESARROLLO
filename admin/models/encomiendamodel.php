<?php

date_default_timezone_set('America/Lima');

class EncomiendaModel extends Model
{
    public $tp_moneda = 1;
    function __construct()
    {
        parent::__construct();
    }

    public function get_dataTable($data)
    {
        try {
            // Validar que tp_comprobante esté definido
            if (!isset($data["tp_comprobante"]) || empty($data["tp_comprobante"])) {
                return [
                    'draw' => intval($data['draw'] ?? 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'No hay tipos de comprobante proporcionados.'
                ];
            }

            $selectFields = "
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c_r.id_tp_docu AS tp_docu_cliente,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            e.fecha_salida AS encomienda_fecha_salida,
            e.estado AS encomienda_estado_envio,
            e.id_terminal_origen,
            e.id_terminal_destino,
            e.id_remitente,
            e.id_destinatario,
            e.e_domicilio,
            e.dir_puntopartida,
            e.dir_puntollegada,
            e.ubi_partida,
            e.ubi_llegada,
            e.pagador_flete,
            e.id_almacen,
            v.obs AS referencia,
            e.pass AS encomienda_pass,
            pf.nombres AS pagante_nombres,
            pf.apellidos AS pagante_apellidos,
            pf.num_docu AS pagante,
            cj.referencia AS caja_chica,
            t_o.nombre AS origen,
            CASE 
               WHEN dt_v.salida = 1 THEN u_s.distri
               ELSE t_d.nombre
            END AS destino,
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
            v.id_pago,
            v.tipo_ruc,
            e.pago AS estado,
            CONCAT(pa.serie, '-', pa.correlativo) AS pago_e,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            dt_v.id_encomienda AS id_encomienda,
            e.tp_comprobante_ref,
            e.serie_ref,
            e.correlativo_ref,
            e.ruc_ref,
            e.guia_serie,
            e.guia_correlativo,
            e.guia_ruc,
            e.link_partida,
            e.link_llegada,
            e.codigo AS tracking,
            dt_v.salida
            ";

            $baseQuery = "
            FROM venta v
            LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
            LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            LEFT JOIN venta pa ON pa.id_venta = v.id_pago 
            LEFT JOIN usuario pf ON pf.id_usuario = e.pagador_flete
            LEFT JOIN caja_chica cj ON cj.id_caja_chica = v.id_caja_chica
            LEFT JOIN programacion_salida p_s ON p_s.id_salida = dt_v.id_salida
            LEFT JOIN ubigeo u_s ON p_s.ubigeo_destino = u_s.cod_ubigeo 
            WHERE tp_s.descripcion = 'ENCOMIENDA'
              AND v.id_tp_comprobante IN (" . implode(',', array_map('intval', $data["tp_comprobante"])) . ")
            ";

            // ===== FILTROS ADICIONALES =====
            // Filtro por fecha
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(v.fecha_emision) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            // Filtro por tipo (solo para comprobantes)
            if (!empty($data['tipo']) && in_array(1, $data["tp_comprobante"])) {
                $baseQuery .= " AND v.id_tp_comprobante = " . intval($data['tipo']);
            }

            // Filtro por origen
            if (!empty($data['origen'])) {
                $baseQuery .= " AND e.id_terminal_origen = " . intval($data['origen']);
            }

            // Filtro por estado de envío
            if (!empty($data['estado_envio'])) {

                $estadoEnvio = intval($data['estado_envio']);

                switch ($estadoEnvio) {

                    case 1:
                        $baseQuery .= " AND e.estado = 'ENTREGADO'";
                        break;

                    case 2:
                        $baseQuery .= " AND e.estado = 'EN DESTINO'";
                        break;

                    case 3:
                        $baseQuery .= " AND dt_v.salida = 1
                                        AND e.estado != 'ENTREGADO'";
                        break;

                    case 4:
                        $baseQuery .= " AND e.estado = 'EN ORIGEN'";
                        break;

                    case 5:
                        $baseQuery .= " AND e.estado = 'EN TRANSITO'
                                        AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 6:
                        $baseQuery .= " AND e.estado = 'MAL ENVIADO'";
                        break;
                }
            }

            // Filtro por destino
            if (!empty($data['destino'])) {
                $baseQuery .= " AND e.id_terminal_destino = " . intval($data['destino']);
            }

            // Filtro por estado de venta
            if (!empty($data['estado_venta'])) {
                $estadoVenta = trim($data['estado_venta']);

                $baseQuery .= " AND TRIM(e.pago) = '" . addslashes($estadoVenta) . "'";
            }

            if ($data["tp_comprobante"] == 2) {
                $searchColumns = [
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "v.fecha_emision",
                    "c_r.nombres",
                    "c_r.apellidos",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos)",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos, ' ',c_r.num_docu)",
                    "c_r.num_docu",
                    "c_d.nombres",
                    "c_d.apellidos",
                    "c_d.num_docu",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos)",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos, ' ',c_d.num_docu)",
                    "t_o.nombre",
                    "t_d.nombre",
                    "e.fecha_salida",
                    "e.estado",
                    "v.total",
                    "e.tipo_envio",
                    "e.codigo"
                ];
            } else {
                $searchColumns = [
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "v.fecha_emision",
                    "c_r.nombres",
                    "c_r.apellidos",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos)",
                    "CONCAT(c_r.nombres, ' ',c_r.num_docu)",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos, ' ',c_r.num_docu)",
                    "c_r.num_docu",
                    "c_d.nombres",
                    "c_d.apellidos",
                    "c_d.num_docu",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos)",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos, ' ', c_d.num_docu)",
                    "t_o.nombre",
                    "t_d.nombre",
                    "e.fecha_salida",
                    "e.tipo_envio",
                    "e.estado",
                    "v.total",
                    "v.op_gravada",
                    "v.op_exonerada",
                    "v.op_inafecta",
                    "v.op_igv",
                    "e.codigo"
                ];
            }


            $orderBy = "v.fecha_emision DESC";

            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $baseQuery .= " AND (e.id_terminal_origen = {$this->id_terminal_sesion} OR e.id_terminal_destino = {$this->id_terminal_sesion} OR e.id_terminal_actual = {$this->id_terminal_sesion})";
            }

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            foreach ($result["data"] as &$row) {
                $row["encomienda_pass"] = $this->security->decryption($row["encomienda_pass"]);
            }

            return $result;
        } catch (Exception $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    public function get_dataTable_grt($data)
    {
        try {
            // Validar que tp_comprobante esté definido
            if (!isset($data["tp_comprobante"]) || empty($data["tp_comprobante"])) {
                return [
                    'draw' => intval($data['draw'] ?? 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'No hay tipos de comprobante proporcionados.'
                ];
            }

            $selectFields = "
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c_r.id_tp_docu AS tp_docu_cliente,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            e.fecha_salida AS encomienda_fecha_salida,
            e.estado AS encomienda_estado_envio,
            e.id_terminal_origen,
            e.id_terminal_destino,
            e.id_remitente,
            e.id_destinatario,
            e.e_domicilio,
            e.dir_puntopartida,
            e.dir_puntollegada,
            e.ubi_partida,
            e.ubi_llegada,
            e.pagador_flete,
            e.id_almacen,
            v.obs AS referencia,
            e.pass AS encomienda_pass,
            pf.nombres AS pagante_nombres,
            pf.apellidos AS pagante_apellidos,
            pf.num_docu AS pagante,
            cj.referencia AS caja_chica,
            t_o.nombre AS origen,
            CASE 
               WHEN dt_v.salida = 1 THEN u_s.distri
               ELSE t_d.nombre
            END AS destino, 
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
            v.id_pago,
            e.pago AS estado,
            CONCAT(pa.serie, '-', pa.correlativo) AS pago_e,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            dt_v.id_encomienda AS id_encomienda,
            e.tp_comprobante_ref,
            e.serie_ref,
            e.correlativo_ref,
            e.ruc_ref,
            e.guia_serie,
            e.guia_correlativo,
            e.guia_ruc,
            e.link_partida,
            e.link_llegada,
            e.codigo AS tracking,
            gr.id AS id_guia_remision,
            gr.xml AS guia_xml,
            gr.cdr AS guia_cdr,
            gr.codigo_sunat AS codigo_sunat_guia,
            dt_v.salida
            ";

            $baseQuery = "
            FROM venta v
            LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
            LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            LEFT JOIN venta pa ON pa.id_venta = v.id_pago 
            LEFT JOIN usuario pf ON pf.id_usuario = e.pagador_flete
            LEFT JOIN caja_chica cj ON cj.id_caja_chica = v.id_caja_chica
            LEFT JOIN guia_remision gr ON gr.id_venta = v.id_venta
            LEFT JOIN programacion_salida p_s ON p_s.id_salida = dt_v.id_salida
            LEFT JOIN ubigeo u_s ON p_s.ubigeo_destino = u_s.cod_ubigeo 
            WHERE tp_s.descripcion = 'ENCOMIENDA'
              AND v.id_tp_comprobante IN (" . implode(',', array_map('intval', $data["tp_comprobante"])) . ")
            ";

            // ===== FILTROS ADICIONALES =====
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(v.fecha_emision) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            if (!empty($data['origen'])) {
                $baseQuery .= " AND e.id_terminal_origen = " . intval($data['origen']);
            }

            if (!empty($data['destino'])) {
                $baseQuery .= " AND e.id_terminal_destino = " . intval($data['destino']);
            }

            // Filtro por estado de envío
            if (!empty($data['estado_envio'])) {

                $estadoEnvio = intval($data['estado_envio']);

                switch ($estadoEnvio) {

                    case 1:
                        $baseQuery .= " AND e.estado = 'ENTREGADO'
                                        AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 2:
                        $baseQuery .= " AND e.estado = 'EN DESTINO'";
                        break;

                    case 3:
                        $baseQuery .= " AND dt_v.salida = 1
                                        AND e.estado != 'ENTREGADO'";
                        break;

                    case 4:
                        $baseQuery .= " AND e.estado = 'EN ORIGEN'";
                        break;

                    case 5:
                        $baseQuery .= " AND e.estado = 'EN TRANSITO'
                                        AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 6:
                        $baseQuery .= " AND e.estado = 'MAL ENVIADO'";
                        break;
                }
            }

            if (!empty($data['estado_venta'])) {
                $estadoVenta = trim($data['estado_venta']);

                $baseQuery .= " AND TRIM(e.pago) = '" . addslashes($estadoVenta) . "'";
            }

            if ($data["tp_comprobante"] == 2) {
                $searchColumns = [
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "v.fecha_emision",
                    "c_r.nombres",
                    "c_r.apellidos",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos)",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos, ' ',c_r.num_docu)",
                    "c_r.num_docu",
                    "c_d.nombres",
                    "c_d.apellidos",
                    "c_d.num_docu",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos)",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos, ' ',c_d.num_docu)",
                    "t_o.nombre",
                    "t_d.nombre",
                    "e.fecha_salida",
                    "CASE
                        WHEN dt_v.salida = 1 AND e.estado != 'ENTREGADO'
                            THEN 'EN RUTA'
                        ELSE e.estado
                    END",
                    "v.total",
                    "e.tipo_envio",
                    "e.codigo"
                ];
            } else {
                $searchColumns = [
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "v.fecha_emision",
                    "c_r.nombres",
                    "c_r.apellidos",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos)",
                    "CONCAT(c_r.nombres, ' ',c_r.num_docu)",
                    "CONCAT(c_r.nombres, ' ',c_r.apellidos, ' ',c_r.num_docu)",
                    "c_r.num_docu",
                    "c_d.nombres",
                    "c_d.apellidos",
                    "c_d.num_docu",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos)",
                    "CONCAT(c_d.nombres, ' ',c_d.apellidos, ' ', c_d.num_docu)",
                    "t_o.nombre",
                    "t_d.nombre",
                    "e.fecha_salida",
                    "e.tipo_envio",
                    "CASE
                        WHEN dt_v.salida = 1 AND e.estado != 'ENTREGADO'
                            THEN 'EN RUTA'
                        ELSE e.estado
                    END",
                    "v.total",
                    "v.op_gravada",
                    "v.op_exonerada",
                    "v.op_inafecta",
                    "v.op_igv",
                    "e.codigo"
                ];
            }


            $orderBy = "v.fecha_emision DESC";

            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $baseQuery .= " AND (e.id_terminal_origen = {$this->id_terminal_sesion} OR e.id_terminal_destino = {$this->id_terminal_sesion})";
            }

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            foreach ($result["data"] as &$row) {
                $row["encomienda_pass"] = $this->security->decryption($row["encomienda_pass"]);
            }

            return $result;
        } catch (Exception $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    public function get_dataTableGuias($data)
    {
        try {
            $selectFields = "*";
            $baseQuery = "FROM guia_remision WHERE id_venta IS NULL";
            // ===== FILTROS ADICIONALES =====
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(fecha_emision) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            if (!empty($data['partida'])) {
                $baseQuery .= " AND partida_ubigeo = '" . $data['partida'] . "'";
            }

            if (!empty($data['destino'])) {
                $baseQuery .= " AND destino_ubigeo = '" . $data['destino'] . "'";
            }

            if (!empty($data['vehiculo_placa'])) {
                $baseQuery .= " AND vehiculo_placa = '" . $data['vehiculo_placa'] . "'";
            }

            $searchColumns = [
                "serie",
                "correlativo",
                "CONCAT(serie, '-', correlativo)",
                "fecha_emision",
                "vehiculo_placa",
                "conductor_nombres",
                "conductor_apellidos",
                "CONCAT(conductor_nombres, ' ', conductor_apellidos)",
                "partida_direccion",
                "destino_direccion",
                "codigo_sunat",
                "mensaje_sunat",
                "conductor_licencia"
            ];
            $orderBy = "id DESC";

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

    public function get_dataTableEmbarcaciones($data)
    {
        try {
            $selectFields = "
        DATE(em.fecha_registro)  AS fecha,
        TIME(em.fecha_registro)  AS hora,
        em.id,
        em.id_programacion,
        em.estado,
        CONCAT(u.nombres, ' ', u.apellidos) AS usuario,

        CASE 
            WHEN em.tipo_programacion = 'salida' THEN o_s.distri
            ELSE t_o.nombre
        END AS origen,

        CASE 
            WHEN em.tipo_programacion = 'salida' THEN d_s.distri
            ELSE t_d.nombre
        END AS destino,

        CASE 
            WHEN em.tipo_programacion = 'salida' THEN v_s.placa
            ELSE v.placa
        END AS vehiculo_placa,

        CASE 
            WHEN em.tipo_programacion = 'salida' THEN con_s.nombres
            ELSE con.nombres
        END AS nombre_conductor,

        CASE 
            WHEN em.tipo_programacion = 'salida' THEN con_s.apellidos
            ELSE con.apellidos
        END AS apellidos_conductor,

        CASE
            WHEN em.tipo_programacion = 'salida' THEN dt_c_s.licencia
            ELSE dt_c.licencia
        END AS licencia,
        em.tipo_programacion
            ";

            $baseQuery = "
            FROM embarcaciones em
            LEFT JOIN programacion p         ON em.id_programacion         = p.id_programacion
            LEFT JOIN programacion_salida p_s ON em.id_programacion_salida = p_s.id_salida
            LEFT JOIN terminal t_o           ON em.origen                  = t_o.id_terminal
            LEFT JOIN terminal t_d           ON em.destino                 = t_d.id_terminal
            LEFT JOIN ubigeo o_s             ON o_s.cod_ubigeo             = p_s.ubigeo_origen
            LEFT JOIN ubigeo d_s             ON d_s.cod_ubigeo             = p_s.ubigeo_destino
            LEFT JOIN vehiculo v             ON p.id_vehiculo              = v.id_vehiculo
            LEFT JOIN vehiculo v_s           ON p_s.id_vehiculo            = v_s.id_vehiculo
            LEFT JOIN usuario u              ON em.id_usuario              = u.id_usuario
            LEFT JOIN usuario con            ON p.id_conductor             = con.id_usuario
            LEFT JOIN usuario con_s          ON p_s.id_conductor           = con_s.id_usuario
            LEFT JOIN dt_conductor dt_c      ON dt_c.id_usuario            = p.id_conductor
            LEFT JOIN dt_conductor dt_c_s    ON dt_c_s.id_usuario          = p_s.id_conductor
            WHERE 1=1
            ";

            // ===== FILTROS ADICIONALES =====
            // Filtro por fecha (usando la columna fecha_registro)
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(em.fecha_registro) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            // Filtro por origen
            if (!empty($data['origen'])) {
                $baseQuery .= " AND em.origen = " . intval($data['origen']);
            }

            // Filtro por destino
            if (!empty($data['destino'])) {
                $baseQuery .= " AND em.destino = " . intval($data['destino']);
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1)) {
                $Id_usuario_sesion = intval($this->id_usuario_sesion);
                $baseQuery .= " AND em.id_usuario = $Id_usuario_sesion";
            }

            // Filtro por vehículo
            if (!empty($data['id_vehiculo'])) {
                $id_vehiculo = intval($data['id_vehiculo']);
                $baseQuery .= " AND ((em.tipo_programacion = 'salida' AND p_s.id_vehiculo = $id_vehiculo)OR (em.tipo_programacion != 'salida' AND p.id_vehiculo = $id_vehiculo))";
            }

            $searchColumns = [
                "em.id",
                "em.estado",
                "t_o.nombre",
                "t_d.nombre",
                "DATE(em.fecha_registro)",
                "TIME(em.fecha_registro)",
                "CONCAT(u.nombres, ' ', u.apellidos)",
                "u.nombres",
                "u.apellidos",
                "t_o.nombre",
                "t_d.nombre",
                "v.placa",
                "con.nombres",
                "con.apellidos",
                "CONCAT(con.nombres, ' ', con.apellidos)",
                "dt_c.licencia",
                "dt_c_s.licencia",
            ];

            $orderBy = "em.id DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy,
            );

            foreach ($result['data'] as &$item) {
                $item['hora'] = date("h:i A", strtotime($item['hora']));
            }

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

    public function get_dataTableDesembarques($data)
    {
        try {
            $selectFields = "
            DATE(em.fecha_registro) AS fecha,
            TIME(em.fecha_registro) AS hora,
            em.id,
            CONCAT(u.nombres, ' ',u.apellidos) AS usuario,
            t_o.nombre AS origen,
            t_d.nombre AS destino,
            v.placa AS vehiculo_placa,
            con.nombres AS nombre_conductor,
            con.apellidos AS apellidos_conductor,
            dt_c.licencia AS licencia
            ";

            $baseQuery = "
            FROM desembarques em
            LEFT JOIN programacion p ON em.id_programacion = p.id_programacion
            LEFT JOIN terminal t_o ON p.id_terminal_origen = t_o.id_terminal
            LEFT JOIN terminal t_d ON em.destino = t_d.id_terminal
            LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
            LEFT JOIN usuario u ON em.id_usuario = u.id_usuario
            LEFT JOIN usuario con ON p.id_conductor = con.id_usuario
            LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = p.id_conductor
            WHERE 1=1
            ";

            // ===== FILTROS ADICIONALES =====
            // Filtro por fecha (usando la columna fecha_registro)
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(em.fecha_registro) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            // Filtro por origen (en desembarques, el origen está en la programación)
            if (!empty($data['origen'])) {
                $baseQuery .= " AND p.id_terminal_origen = " . intval($data['origen']);
            }

            // Filtro por destino
            if (!empty($data['destino'])) {
                $baseQuery .= " AND em.destino = " . intval($data['destino']);
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1)) {
                $Id_usuario_sesion = intval($this->id_usuario_sesion);
                $baseQuery .= " AND em.id_usuario = $Id_usuario_sesion";
            }

            // Filtro por vehículo
            if (!empty($data['id_vehiculo'])) {
                $baseQuery .= " AND p.id_vehiculo = " . intval($data['id_vehiculo']);
            }

            $searchColumns = [
                "t_o.nombre",
                "t_d.nombre",
                "DATE(em.fecha_registro)",
                "TIME(em.fecha_registro)",
                "CONCAT(u.nombres, ' ', u.apellidos)",
                "u.nombres",
                "u.apellidos",
                "v.placa",
                "con.nombres",
                "con.apellidos",
                "CONCAT(con.nombres, ' ', con.apellidos)",
                "dt_c.licencia"
            ];

            $orderBy = "em.id DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy,
            );

            foreach ($result['data'] as &$item) {
                $item['hora'] = date("h:i A", strtotime($item['hora']));
            }

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

    public function get_dataTableEmbarcacionesGrupales($data)
    {
        try {
            // Usar subconsulta para COUNT para evitar problemas con GROUP BY
            $selectFields = "
    ge.id AS id_grupo_embarcacion,
    DATE(ge.fecha) AS fecha,
    TIME(ge.fecha) AS hora,
    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN o_s.distri
        ELSE t_o.nombre
    END AS origen,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN d_s.distri
        ELSE t_d.nombre
    END AS destino,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN v_s.placa
        ELSE v.placa
    END AS vehiculo_placa,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN con_s.nombres
        ELSE con.nombres
    END AS nombre_conductor,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN con_s.apellidos
        ELSE con.apellidos
    END AS apellidos_conductor,

    CASE 
        WHEN emb.tipo_programacion = 'salida' THEN dt_c_s.licencia
        ELSE dt_c.licencia
    END AS licencia,

    p.id_programacion,
    p_s.id_salida,

    (
        SELECT COUNT(*) 
        FROM embarcaciones emb2 
        WHERE emb2.id_grupo_embarcacion = ge.id
    ) AS total_embarcaciones
            ";

            $baseQuery = "
            FROM grupo_embarcaciones ge
             LEFT JOIN usuario u             ON ge.id_usuario              = u.id_usuario
             LEFT JOIN embarcaciones emb     ON ge.id                      = emb.id_grupo_embarcacion
             LEFT JOIN programacion p        ON emb.id_programacion        = p.id_programacion
             LEFT JOIN programacion_salida p_s ON emb.id_programacion_salida = p_s.id_salida
             LEFT JOIN terminal t_o          ON p.id_terminal_origen       = t_o.id_terminal
             LEFT JOIN terminal t_d          ON p.id_terminal_destino      = t_d.id_terminal
             LEFT JOIN ubigeo o_s            ON o_s.cod_ubigeo             = p_s.ubigeo_origen
             LEFT JOIN ubigeo d_s            ON d_s.cod_ubigeo             = p_s.ubigeo_destino
             LEFT JOIN vehiculo v            ON p.id_vehiculo              = v.id_vehiculo
             LEFT JOIN vehiculo v_s          ON p_s.id_vehiculo            = v_s.id_vehiculo
             LEFT JOIN usuario con           ON p.id_conductor             = con.id_usuario
             LEFT JOIN usuario con_s         ON p_s.id_conductor           = con_s.id_usuario
             LEFT JOIN dt_conductor dt_c     ON dt_c.id_usuario            = p.id_conductor
             LEFT JOIN dt_conductor dt_c_s   ON dt_c_s.id_usuario          = p_s.id_conductor
            WHERE 1=1
               ";

            // ===== FILTROS ADICIONALES =====
            // Filtro por fecha (usando la columna fecha de grupo_embarcaciones)
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $baseQuery .= " AND DATE(ge.fecha) BETWEEN '" . $data['fecha_inicio'] . "' AND '" . $data['fecha_fin'] . "'";
            }

            // Filtro por origen
            if (!empty($data['origen'])) {
                $baseQuery .= " AND p.id_terminal_origen = " . intval($data['origen']);
            }

            // Filtro por destino
            if (!empty($data['destino'])) {
                $baseQuery .= " AND p.id_terminal_destino = " . intval($data['destino']);
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1)) {
                $baseQuery .= " AND ge.id_usuario = $this->id_usuario_sesion";
            }

            // Filtro por vehículo
            if (!empty($data['id_vehiculo'])) {
                $id_vehiculo = intval($data['id_vehiculo']);
                $baseQuery .= " AND ((emb.tipo_programacion = 'salida' AND p_s.id_vehiculo = $id_vehiculo) OR (emb.tipo_programacion != 'salida' AND p.id_vehiculo = $id_vehiculo))";
            }

            // Campos de búsqueda - Solo campos del GROUP BY o agregados
            $searchColumns = [
                "ge.id",
                "DATE(ge.fecha)",
                "TIME(ge.fecha)",
                "CONCAT(u.nombres, ' ', u.apellidos)",
                "t_o.nombre",
                "t_d.nombre",
                "v.placa",
                "CONCAT(con.nombres, ' ', con.apellidos)",
                "dt_c.licencia",
            ];

            $orderBy = "ge.id DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            // Formatear datos para respuesta
            if (isset($result['data'])) {
                foreach ($result['data'] as &$item) {
                    // Asegurar que el ID se muestre correctamente
                    $item['id'] = $item['id_grupo_embarcacion'] ?? 'N/A';

                    // Formatear hora
                    if (!empty($item['hora'])) {
                        $item['hora'] = $this->convertTo12HourFormat($item['hora']);
                    }

                    // Valores por defecto para campos NULL
                    $item['origen'] = $item['origen'] ?? '-';
                    $item['destino'] = $item['destino'] ?? '-';
                    $item['vehiculo_placa'] = $item['vehiculo_placa'] ?? '-';
                    $item['nombre_conductor'] = $item['nombre_conductor'] ?? '-';
                    $item['apellidos_conductor'] = $item['apellidos_conductor'] ?? '';
                    $item['total_embarcaciones'] = $item['total_embarcaciones'] ?? 0;
                }
            }

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

    public function get_data_guia($id)
    {
        $query = $this->db->connect()->prepare("SELECT gr.*, 
        u_p.depa AS departamento_partida, 
        u_p.provi AS provincia_partida,
        u_p.distri AS distrito_partida, 
        u_d.depa AS provincia_destino, 
        u_d.provi AS distrito_destino,
        u_d.distri AS departamento_destino, 
        u_r.num_docu AS r_num_doc, 
        u_r.id_tp_docu AS r_tp_docu, 
        CONCAT(u_r.nombres, ' ',u_r.apellidos) AS remitente
                FROM guia_remision gr
                LEFT JOIN usuario u_r ON gr.remitente_id = u_r.id_usuario
                LEFT JOIN ubigeo u_p ON gr.partida_ubigeo = u_p.cod_ubigeo
                LEFT JOIN ubigeo u_d ON gr.destino_ubigeo = u_d.cod_ubigeo
                WHERE gr.id = $id
                ORDER BY gr.id DESC;");
        $query->execute();
        $reply = $query->fetch(PDO::FETCH_ASSOC);

        $query3 = $this->db->connect()->prepare("SELECT *
        FROM doc_relacionado 
        WHERE id_guia_remision = :id_guia_remision");
        $query3->bindParam(":id_guia_remision", $id);
        $query3->execute();
        $doc_relacionados = $query3->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($doc_relacionados)) {
            foreach ($doc_relacionados as &$doc_relacionado) {
                switch ($doc_relacionado['tp_comprobante']) {
                    case '01':
                        $doc_relacionado['comprobante'] = "Factura";
                        break;
                    case '03':
                        $doc_relacionado['comprobante'] = "Boleta de venta";
                        break;
                    case '09':
                        $doc_relacionado['comprobante'] = "Guía de remisión remitente";
                        break;
                    default:
                        $doc_relacionado['comprobante'] = "Desconocido";
                }
            }
        }

        $reply['doc_relacionados'] = $doc_relacionados;

        $id_pagador = $reply['pagador_flete'] ? $reply['pagador_flete'] : 0;

        $query2 = $this->db->connect()->prepare("
        SELECT gd.*, pr.codigo
        FROM guia_remision_detalle gd
        LEFT JOIN ctg_encomienda pr ON pr.id_ctg_encomienda = gd.producto_id
        WHERE gd.guia_remision_id = $id");
        $query2->execute();
        $productos = $query2->fetchAll(PDO::FETCH_ASSOC);

        $id_empresa = $reply['id_empresa'] ?? 1;
        $query1 = $this->db->connect()->prepare("
        SELECT e.*, u.depa, u.provi, u.distri 
        FROM empresa e 
        LEFT JOIN ubigeo u ON e.ubigeo = u.cod_ubigeo
        WHERE e.id_empresa = :id_empresa
        LIMIT 1");
        $query1->bindParam(":id_empresa", $id_empresa, PDO::PARAM_INT);
        $query1->execute();
        $header_empresa = $query1->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
                SELECT CONCAT(u.nombres, ' ', u.apellidos) AS nombres,
                u.num_docu AS num_docu, 
                u.id_tp_docu AS id_tp_docu
                FROM usuario u
                WHERE u.id_usuario = $id_pagador");
        $query->execute();
        $data_pagador_flete = $query->fetch(PDO::FETCH_ASSOC);
        $pagador_fletero = [];
        if ($data_pagador_flete) {
            $pagador_fletero['tipo_documento'] = $data_pagador_flete['id_tp_docu'];
            $pagador_fletero['num_doc'] = $data_pagador_flete['num_docu'];
            $pagador_fletero['razon_social'] = $data_pagador_flete['nombres'];
        }

        $reply['empresa'] = $header_empresa;
        $reply['pagador_flete'] = $pagador_fletero;
        $reply['productos'] = $productos;

        return ['data' => $reply];
    }

    public function get_data_guia_grupal($id_grupo_embarque)
    {
        // Obtener los id relacionados al grupo de embarque 
        $query_ids = $this->db->connect()->prepare("SELECT id FROM guia_remision WHERE id_grupo_embarcacion = :id_grupo_embarque");
        $query_ids->bindParam(":id_grupo_embarque", $id_grupo_embarque);
        $query_ids->execute();
        $ids = $query_ids->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return ['data' => null];
        }

        // Obtener datos de empresa una sola vez
        $query1 = $this->db->connect()->prepare("
        SELECT e.*, u.depa, u.provi, u.distri 
        FROM empresa e 
        LEFT JOIN ubigeo u ON e.ubigeo = u.cod_ubigeo");
        $query1->execute();
        $header_empresa = $query1->fetch(PDO::FETCH_ASSOC);

        $resultados = [];

        foreach ($ids as $id) {
            $id = intval($id);

            // Consulta principal con parámetros preparados
            $query = $this->db->connect()->prepare("SELECT gr.*, 
            u_p.depa AS departamento_partida, 
            u_p.provi AS provincia_partida,
            u_p.distri AS distrito_partida, 
            u_d.depa AS departamento_destino, 
            u_d.provi AS provincia_destino,
            u_d.distri AS distrito_destino, 
            u_r.num_docu AS r_num_doc, 
            CONCAT(u_r.nombres, ' ', u_r.apellidos) AS remitente
            FROM guia_remision gr
            LEFT JOIN usuario u_r ON gr.remitente_id = u_r.id_usuario
            LEFT JOIN ubigeo u_p ON gr.partida_ubigeo = u_p.cod_ubigeo
            LEFT JOIN ubigeo u_d ON gr.destino_ubigeo = u_d.cod_ubigeo
            WHERE gr.id = :id
            ORDER BY gr.id DESC");
            $query->bindParam(":id", $id, PDO::PARAM_INT);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            if (!$reply) {
                continue;
            }

            // Documentos relacionados
            $query3 = $this->db->connect()->prepare("SELECT * FROM doc_relacionado WHERE id_guia_remision = :id_guia_remision");
            $query3->bindParam(":id_guia_remision", $id, PDO::PARAM_INT);
            $query3->execute();
            $doc_relacionados = $query3->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($doc_relacionados)) {
                foreach ($doc_relacionados as &$doc_relacionado) {
                    switch ($doc_relacionado['tp_comprobante']) {
                        case '01':
                            $doc_relacionado['comprobante'] = "Factura";
                            break;
                        case '03':
                            $doc_relacionado['comprobante'] = "Boleta de venta";
                            break;
                        case '09':
                            $doc_relacionado['comprobante'] = "Guía de remisión remitente";
                            break;
                        default:
                            $doc_relacionado['comprobante'] = "Desconocido";
                    }
                }
            }

            $reply['doc_relacionados'] = $doc_relacionados;

            // Productos
            $query2 = $this->db->connect()->prepare("
            SELECT gd.*, pr.codigo
            FROM guia_remision_detalle gd
            LEFT JOIN ctg_encomienda pr ON pr.id_ctg_encomienda = gd.producto_id
            WHERE gd.guia_remision_id = :id");
            $query2->bindParam(":id", $id, PDO::PARAM_INT);
            $query2->execute();
            $productos = $query2->fetchAll(PDO::FETCH_ASSOC);

            // Pagador de flete
            $id_pagador = $reply['pagador_flete'] ? $reply['pagador_flete'] : 0;
            $pagador_fletero = [];

            if ($id_pagador > 0) {
                $query_pagador = $this->db->connect()->prepare("
                SELECT CONCAT(u.nombres, ' ', u.apellidos) AS nombres,
                u.num_docu AS num_docu, 
                u.id_tp_docu AS id_tp_docu
                FROM usuario u
                WHERE u.id_usuario = :id_pagador");
                $query_pagador->bindParam(":id_pagador", $id_pagador, PDO::PARAM_INT);
                $query_pagador->execute();
                $data_pagador_flete = $query_pagador->fetch(PDO::FETCH_ASSOC);

                if ($data_pagador_flete) {
                    $pagador_fletero['tipo_documento'] = $data_pagador_flete['id_tp_docu'];
                    $pagador_fletero['num_doc'] = $data_pagador_flete['num_docu'];
                    $pagador_fletero['razon_social'] = $data_pagador_flete['nombres'];
                }
            }

            $reply['empresa'] = $header_empresa;
            $reply['pagador_flete'] = $pagador_fletero;
            $reply['productos'] = $productos;

            $resultados[] = $reply;
        }

        return ['data' => $resultados];
    }

    public function add_register($data)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $tipo_ruc = 2;
            } else {
                $tipo_ruc = 1;
            }

            $conn = $this->db->connect();
            $conn->beginTransaction();

            $data["estado_envio"] = 'EN ORIGEN';
            $data['remitente'] = $data['remitente_id'];
            $data['destinatario'] = $data['destinatario_id'];

            $tp_comprobante_ref = $data['tp_comprobante_e'] ?? 0;
            $serie_ref = $data['serieEP'] ?? '';
            $correlativo_ref = $data['correlativoEP'] ?? '';
            $ruc_ref = $data['rucEP'] ?? '';
            $fecha_registro = date(("Y-m-d H:i:s"));
            $id_serie = $data["serie_venta"];
            $correlativo = $data['tp_comprobante'] == "31" ? $this->get_correlativo_GRT($data) : $this->get_correlativo($data, $tipo_ruc);
            $igv = $this->get_igv($conn);
            if (!$igv || $igv == 0) {
                throw new Exception("Ingrese el IGV desde el apartado configuración.");
            }
            // $op_gravada = $igv || $igv != 0 ? ($data["precio_venta"] / (($igv / 100) + 1)) : 0.0;
            $op_gravada = $data["precio_venta"] / (1 + ($igv / 100));
            $codigo_encomienda = uniqid(rand());
            $codigo_venta = uniqid(rand());
            $pass = $this->security->encryption($data["pass"]);

            $obs_destinatario = isset($data['obs_destinatario']) ? trim($data['obs_destinatario']) : '';

            $guia_serie = $data['serieGR'] ?? '';
            $guia_correlativo = $data['correlativoGR'] ?? '';
            $guia_ruc = $data['rucGR'] ?? '';
            $porcentaje_venta = 0;
            $factor_p_venta = 0;
            $num_recibo_fisico = $data['num_recibo_fisico'] ?? null;

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
                       porcent_venta_e
                    FROM configuracion
                ");
                $query->execute();
                $porcentaje_dato = floatval($query->fetchColumn());
                $factor_p_venta = $porcentaje_dato;
                $total_venta = floatval($data['precio_venta']);

                $porcentaje_venta = $total_venta * $porcentaje_dato / 100;
            }

            $modalidad_pago = $data["forma_pago"] == 2 && $data['pago'] == 'PAGADO' ? 'CREDITO' : $data["pago"];

            // Registrando la encomienda
            if (isset($data['e_domicilio']) && $data['e_domicilio'] == 1) {
                $query = $conn->prepare("INSERT INTO encomienda (
                id_almacen, codigo, fecha_salida, estado, id_remitente,
                id_destinatario, id_terminal_origen, id_terminal_destino, id_terminal_actual, pago, pass, fecha_registro, id_sesionpersonal,
                dir_puntopartida, dir_puntollegada, ubi_partida, ubi_llegada, link_partida, link_llegada, pagador_flete, e_domicilio, tipo_envio, obs_destinatario,
                tp_comprobante_ref, serie_ref, correlativo_ref, ruc_ref, guia_serie, guia_correlativo, guia_ruc, num_recibo_fisico) VALUES(
                :id_almacen, :codigo, :fecha_salida, :estado, :id_remitente,
                :id_destinatario, :id_terminal_origen, :id_terminal_destino, :id_terminal_actual, :pago, :pass, :fecha_registro, :id_sesionpersonal,
                :dir_puntopartida, :dir_puntollegada, :ubi_partida, :ubi_llegada, :link_partida, :link_llegada, :pagador_flete, :e_domicilio, :tipo_envio, :obs_destinatario,
                :tp_comprobante_ref, :serie_ref, :correlativo_ref, :ruc_ref, :guia_serie, :guia_correlativo, :guia_ruc, :num_recibo_fisico
                )");
                $query->bindParam(":id_almacen", $data["almacen"]);
                $query->bindParam(":codigo", $codigo_encomienda);
                $query->bindParam(":fecha_salida", $data["fecha_salida"]);
                $query->bindParam(":estado", $data["estado_envio"]);
                $query->bindParam(":id_remitente", $data["remitente"]);
                $query->bindParam(":id_destinatario", $data["destinatario"]);
                $query->bindParam(":id_terminal_origen", $this->id_terminal_sesion);
                $query->bindParam(":id_terminal_destino", $data["destino_terminal"]);
                $query->bindParam(":id_terminal_actual", $this->id_terminal_sesion);
                $query->bindParam(":pago", $modalidad_pago);
                $query->bindParam(":pass", $pass);
                $query->bindParam(":fecha_registro", $fecha_registro);
                $query->bindParam(":id_sesionpersonal", $this->id_usuario_sesion);
                // Binding the new parameters
                $query->bindParam(":dir_puntopartida", $data["p_partida"]);
                $query->bindParam(":dir_puntollegada", $data["p_llegada"]);
                $query->bindParam(":ubi_partida", $data["ubigeoP"]);
                $query->bindParam(":ubi_llegada", $data["ubigeoLle"]);
                $query->bindParam(":link_partida", $data["linkP"]);
                $query->bindParam(":link_llegada", $data["linkLle"]);
                $query->bindParam(":e_domicilio", $data["e_domicilio"]);
                $query->bindParam(":pagador_flete", $data["pagante_id"]);
                $query->bindParam(":tipo_envio", $data["pago"]);
                $query->bindParam(":obs_destinatario", $obs_destinatario);
                $query->bindParam(":tp_comprobante_ref", $tp_comprobante_ref);
                $query->bindParam(":serie_ref", $serie_ref);
                $query->bindParam(":correlativo_ref", $correlativo_ref);
                $query->bindParam(":ruc_ref", $ruc_ref);
                $query->bindParam(':guia_serie', $guia_serie);
                $query->bindParam(':guia_correlativo', $guia_correlativo);
                $query->bindParam(':guia_ruc', $guia_ruc);
                $query->bindParam(':num_recibo_fisico', $num_recibo_fisico);
            } else if ($data['tp_comprobante'] == "31") {
                $query = $conn->prepare("INSERT INTO encomienda (
                id_almacen, codigo, fecha_salida, estado, id_remitente,
                id_destinatario, id_terminal_origen, id_terminal_destino, id_terminal_actual, pago, pass, fecha_registro, id_sesionpersonal, tipo_envio, obs_destinatario,
                tp_comprobante_ref, serie_ref, correlativo_ref, ruc_ref, guia_serie, guia_correlativo, guia_ruc, pagador_flete, num_recibo_fisico
                ) VALUES(
                :id_almacen, :codigo, :fecha_salida, :estado, :id_remitente,
                :id_destinatario, :id_terminal_origen, :id_terminal_destino, :id_terminal_actual, :pago, :pass, :fecha_registro, :id_sesionpersonal, :tipo_envio, :obs_destinatario,
                :tp_comprobante_ref, :serie_ref, :correlativo_ref, :ruc_ref, :guia_serie, :guia_correlativo, :guia_ruc, :pagador_flete, :num_recibo_fisico
                )");
                $query->bindParam(":id_almacen", $data["almacen"]);
                $query->bindParam(":codigo", $codigo_encomienda);
                $query->bindParam(":fecha_salida", $data["fecha_salida"]);
                $query->bindParam(":estado", $data["estado_envio"]);
                $query->bindParam(":id_remitente", $data["remitente"]);
                $query->bindParam(":id_destinatario", $data["destinatario"]);
                $query->bindParam(":id_terminal_origen", $this->id_terminal_sesion);
                $query->bindParam(":id_terminal_destino", $data["destino_terminal"]);
                $query->bindParam(":id_terminal_actual", $this->id_terminal_sesion);
                $query->bindParam(":pago", $modalidad_pago);
                $query->bindParam(":pass", $pass);
                $query->bindParam(":fecha_registro", $fecha_registro);
                $query->bindParam(":id_sesionpersonal", $this->id_usuario_sesion);
                $query->bindParam(":tipo_envio", $data['pago']);
                $query->bindParam(":obs_destinatario", $obs_destinatario);
                $query->bindParam(":tp_comprobante_ref", $tp_comprobante_ref);
                $query->bindParam(":serie_ref", $serie_ref);
                $query->bindParam(":correlativo_ref", $correlativo_ref);
                $query->bindParam(":ruc_ref", $ruc_ref);
                $query->bindParam(':guia_serie', $guia_serie);
                $query->bindParam(':guia_correlativo', $guia_correlativo);
                $query->bindParam(':guia_ruc', $guia_ruc);
                $query->bindParam(":pagador_flete", $data["pagante_id"]);
                $query->bindParam(':num_recibo_fisico', $num_recibo_fisico);
            } else {
                // Original SQL query without the new fields
                $query = $conn->prepare("INSERT INTO encomienda (
                id_almacen, codigo, fecha_salida, estado, id_remitente,
                id_destinatario, id_terminal_origen, id_terminal_destino, id_terminal_actual, pago, pass, fecha_registro, id_sesionpersonal, tipo_envio, obs_destinatario,
                tp_comprobante_ref, serie_ref, correlativo_ref, ruc_ref, guia_serie, guia_correlativo, guia_ruc, num_recibo_fisico
                ) VALUES(
                :id_almacen, :codigo, :fecha_salida, :estado, :id_remitente,
                :id_destinatario, :id_terminal_origen, :id_terminal_destino, :id_terminal_actual, :pago, :pass, :fecha_registro, :id_sesionpersonal, :tipo_envio, :obs_destinatario,
                :tp_comprobante_ref, :serie_ref, :correlativo_ref, :ruc_ref, :guia_serie, :guia_correlativo, :guia_ruc, :num_recibo_fisico
                )");
                $query->bindParam(":id_almacen", $data["almacen"]);
                $query->bindParam(":codigo", $codigo_encomienda);
                $query->bindParam(":fecha_salida", $data["fecha_salida"]);
                $query->bindParam(":estado", $data["estado_envio"]);
                $query->bindParam(":id_remitente", $data["remitente"]);
                $query->bindParam(":id_destinatario", $data["destinatario"]);
                $query->bindParam(":id_terminal_origen", $this->id_terminal_sesion);
                $query->bindParam(":id_terminal_destino", $data["destino_terminal"]);
                $query->bindParam(":id_terminal_actual", $this->id_terminal_sesion);
                $query->bindParam(":pago", $modalidad_pago);
                $query->bindParam(":pass", $pass);
                $query->bindParam(":fecha_registro", $fecha_registro);
                $query->bindParam(":id_sesionpersonal", $this->id_usuario_sesion);
                $query->bindParam(":tipo_envio", $data['pago']);
                $query->bindParam(":obs_destinatario", $obs_destinatario);
                $query->bindParam(":tp_comprobante_ref", $tp_comprobante_ref);
                $query->bindParam(":serie_ref", $serie_ref);
                $query->bindParam(":correlativo_ref", $correlativo_ref);
                $query->bindParam(":ruc_ref", $ruc_ref);
                $query->bindParam(':guia_serie', $guia_serie);
                $query->bindParam(':guia_correlativo', $guia_correlativo);
                $query->bindParam(':guia_ruc', $guia_ruc);
                $query->bindParam(':num_recibo_fisico', $num_recibo_fisico);
            }
            $query->execute();

            //Obteniendo el id_encomienda
            $query = $conn->prepare("SELECT id_encomienda FROM encomienda WHERE codigo=:codigo");
            $query->bindParam(":codigo", $codigo_encomienda);
            $query->execute();
            $data["id_encomienda"] = $query->fetch(PDO::FETCH_ASSOC)["id_encomienda"];
            $data["productos"] = json_decode($data["productos"]);

            // Registro de movimiento
            $queryMovimiento = $conn->prepare("
              INSERT INTO encomienda_movimiento (
               id_encomienda,
               tipo_movimiento,
               id_terminal_evento,
               id_terminal_destino,
               observacion,
               id_usuario,
               fecha_registro
               )
               VALUES (
               :id_encomienda,
               'REGISTRO',
               :id_terminal_evento,
               :id_terminal_destino,
               :observacion,
               :id_usuario,
               NOW()
               )
            ");

            $observacion = "Encomienda registrada en el sistema";

            $queryMovimiento->bindParam(":id_encomienda", $data["id_encomienda"], PDO::PARAM_INT);
            $queryMovimiento->bindParam(":id_terminal_evento", $this->id_terminal_sesion, PDO::PARAM_INT);
            $queryMovimiento->bindParam(":id_terminal_destino", $data['id_terminal_destino'], PDO::PARAM_INT);
            $queryMovimiento->bindParam(":observacion", $observacion, PDO::PARAM_STR);
            $queryMovimiento->bindParam(":id_usuario", $this->id_usuario_sesion, PDO::PARAM_INT);
            $queryMovimiento->execute();

            // Registrando la venta
            $estado_comp = $data["forma_pago"] == 2 || $data['pago'] == 'PAGO EN BLOQUE' || $data['pago'] == "PAGO EN DESTINO" ? 'PENDIENTE' : 'PAGADO';
            $id_medio_pago = isset($data['medio_pago']) && $data['medio_pago'] !== "" ? $data['medio_pago'] : null;

            $afecta_detraccion = $data['tp_operacion_venta'] == 2 ? 1 : 0;
            if ($data["pago"] == "PAGADO") {

                $resultados = $this->register_productos($data, $conn);
                $total_igv = $resultados["total_igv"];
                $total_gravada = $resultados["total_gravada"];
                $total_inafecta = $resultados["total_inafecta"];
                $total_exonerada = $resultados["total_exonerada"];

                $query = $conn->prepare("INSERT INTO venta (
                codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                correlativo, descuento, op_igv, estado, envio_sunat,
                descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, 
                peso_encomienda, porcentaje_venta, factor_p_venta, id_tp_operacion, afecta_detraccion, 
                id_tp_venta, tipo_ruc
                )
                VALUES(
                :codigo, :id_terminal, :id_vendedor, :id_cliente, :forma_pago, :id_medio_pago,
                :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                :correlativo, '', :igv,  :estado, 0, 
                '', '', '', '', '', :fecha_emision, :op_gravada,
                :op_exonerada, :op_inafecta, :total, '','', :referencia, :id_sesionpersonal, :peso_total,
                :porcentaje_venta, :factor_p_venta, :tp_operacion_venta, :afecta_detraccion, '2', :tipo_ruc
                )");
                $query->bindParam(':codigo', $codigo_venta);
                $query->bindParam(':id_terminal', $this->id_terminal_sesion);
                $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
                $query->bindParam(':id_cliente', $data["remitente"]);
                $query->bindParam(':forma_pago', $data["forma_pago"]);
                $query->bindParam(':id_medio_pago', $id_medio_pago);
                $query->bindParam(':id_tp_moneda', $this->tp_moneda);
                $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
                $query->bindParam(':id_caja_chica', $data["destino"]);
                $query->bindParam(':id_serie', $data["serie_venta"]);
                $query->bindParam(':correlativo', $correlativo);
                $query->bindParam(':id_serie', $id_serie);
                $query->bindParam(':igv', $total_igv);
                // $query->bindParam(':monto_antes_igv', $data["precio_venta"]);
                $query->bindParam(':estado', $estado_comp);
                $query->bindParam(':fecha_emision', $fecha_registro);
                $query->bindParam(':op_gravada', $total_gravada);
                $query->bindParam(':op_exonerada', $total_exonerada);
                $query->bindParam(':op_inafecta', $total_inafecta);
                $query->bindParam(':total', $data["precio_venta"]);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->bindParam(':referencia', $data['referencia']);
                $query->bindParam(':peso_total', $data['peso_venta']);
                $query->bindParam(':porcentaje_venta', $porcentaje_venta);
                $query->bindParam(':factor_p_venta', $factor_p_venta);
                $query->bindParam(':tp_operacion_venta', $data['tp_operacion_venta']);
                $query->bindParam(':afecta_detraccion', $afecta_detraccion);
                $query->bindParam(':tipo_ruc', $tipo_ruc);
                $query->execute();
            } else {
                $resultados = $this->register_productos($data, $conn);
                $total_igv = $resultados["total_igv"];
                $total_gravada = $resultados["total_gravada"];
                $total_inafecta = $resultados["total_inafecta"];
                $total_exonerada = $resultados["total_exonerada"];

                $query = $conn->prepare("INSERT INTO venta (
                codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, 
                id_tp_moneda, id_tp_comprobante,  id_serie, serie,
                correlativo, descuento, op_igv, estado, envio_sunat,
                descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, 
                id_sesionpersonal, peso_encomienda, porcentaje_venta, factor_p_venta, 
                id_tp_operacion, afecta_detraccion, id_tp_venta, tipo_ruc
                )
                VALUES(
                :codigo, :id_terminal, :id_vendedor, :id_cliente, :forma_pago, 
                :id_tp_moneda, :id_tp_comprobante, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                :correlativo, '', :igv,  :estado, 0, 
                '', '', '', '', '', :fecha_emision, :op_gravada,
                :op_exonerada, :op_inafecta, :total, '','', '', :id_sesionpersonal, :peso_total,
                :porcentaje_venta, :factor_p_venta, :tp_operacion_venta, :afecta_detraccion, '2', :tipo_ruc
                )");
                $query->bindParam(':codigo', $codigo_venta);
                $query->bindParam(':id_terminal', $this->id_terminal_sesion);
                $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
                $query->bindParam(':id_cliente', $data["remitente"]);
                $query->bindParam(':forma_pago', $data["forma_pago"]);

                $query->bindParam(':id_tp_moneda', $this->tp_moneda);
                $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);

                $query->bindParam(':id_serie', $data["serie_venta"]);
                $query->bindParam(':correlativo', $correlativo);
                $query->bindParam(':id_serie', $id_serie);
                $query->bindParam(':igv', $total_igv);
                // $query->bindParam(':monto_antes_igv', $data["precio_venta"]);
                $query->bindParam(':estado', $estado_comp);
                $query->bindParam(':fecha_emision', $fecha_registro);
                $query->bindParam(':op_gravada', $total_gravada);
                $query->bindParam(':op_exonerada', $total_exonerada);
                $query->bindParam(':op_inafecta', $total_inafecta);
                $query->bindParam(':total', $data["precio_venta"]);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->bindParam(':peso_total', $data['peso_venta']);
                $query->bindParam(':porcentaje_venta', $porcentaje_venta);
                $query->bindParam(':factor_p_venta', $factor_p_venta);
                $query->bindParam(':tp_operacion_venta', $data['tp_operacion_venta']);
                $query->bindParam(':afecta_detraccion', $afecta_detraccion);
                $query->bindParam(':tipo_ruc', $tipo_ruc);
                $query->execute();
            }

            // Obteniendo el id_venta
            $query = $conn->prepare("SELECT id_venta FROM venta WHERE codigo=:codigo");
            $query->bindParam(":codigo", $codigo_venta);
            $query->execute();
            $data["id_venta"] = $query->fetch(PDO::FETCH_ASSOC)["id_venta"];

            // Creando registro si en caso es credito 
            if ($data["forma_pago"] == 2) {
                $numero_cuota = "001";
                $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                $query = $conn->prepare("INSERT INTO cuota (
                comprobante_id, numero, importe, fecha_vencimiento, estado)
                VALUES(
                :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                ");
                $query->bindParam(':comprobante_id', $data["id_venta"]);
                $query->bindParam(':numero', $numero_cuota);
                $query->bindParam(':importe', $data['monto_credito']);
                $query->bindParam(':estado', $estado_cuota);
                $query->bindParam(':fecha_vencimiento', $data['fecha_credito']);
                $query->execute();
            }

            if ($data['tp_operacion_venta'] == 2) {
                $this->add_detraccion($data, $conn);
            }

            // Obteniendo el el estado
            $query = $conn->prepare("SELECT estado FROM venta WHERE codigo=:codigo");
            $query->bindParam(":codigo", $codigo_venta);
            $query->execute();
            $data["estado"] = $query->fetch(PDO::FETCH_ASSOC)["estado"];

            // Registrando detalle venta
            $salida = isset($data['e_salida']) && $data['e_salida'] == 1 ? 1 : 0;
            $id_salida = isset($data['salida']) ? $data['salida'] : null;

            $query = $conn->prepare("INSERT INTO dt_venta (
            id_venta, id_tp_servicio, precio, id_encomienda, id_sesionpersonal, salida, id_salida) 
            VALUES(
            :id_venta, 2, :precio, :id_encomienda, :id_sesionpersonal, :salida, :id_salida)");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->bindParam(':precio', $data["precio_venta"]);
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':salida', $salida);
            $query->bindParam(':id_salida', $id_salida);
            $query->execute();

            // Actualizar el comprobante 
            if (isset($data['e_salida']) && $data['e_salida'] == '1') {
                $salida_update = $this->comprobante_salida($data, $conn);
                if (!$salida_update['success']) {
                    throw new Exception($salida_update['message']);
                }
            }

            // Generando el código QR y facturación
            if (in_array($data["tp_comprobante"], [1, 3]) && $data["pago"] == "PAGADO") {
                $id_venta = $data["id_venta"];
                $this->generar_codQR($id_venta, $conn);
                $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($data["id_venta"], $conn)));
                $resp = json_decode($rpta_sunat, true);
                // Actualizando los datos de la venta
                $resp[0]["id_venta"] = $data["id_venta"];
                $this->updateVentaSetForSunat($resp[0], $conn);
            }

            $tp_comprobante = $data["tp_comprobante"]; // 1=Factura, 2=Nota Venta, 3=Boleta
            $link_comprobante = URL . "encomienda/impresion/" . ($data["tp_comprobante"] == 2 ? 'nota_venta/' : 'comprobante/') . $data["id_venta"];

            $link_embarque = [];
            $link_guia_a4 = [];
            // Logica si en caso es una guia de remision transportista
            if ($data["tp_comprobante"] == "31" || $data['e_salida'] == '1') {
                $embarcacion = $this->embarcar_encomienda($data, $conn);
                if ($embarcacion['success']) {
                    if ($data["tp_comprobante"] == "31") {
                        $link_comprobante = $embarcacion['message']['links'][1]['link'];
                    }
                    $link_embarque = $embarcacion['message']['links'][0];
                    $link_guia_a4 = $embarcacion['message']['links'][2];
                } else {
                    throw new Exception('No se pudo embarcar la encomienda');
                }
            }

            $conn->commit();

            /*================================= Notificar al socket del cambio =================================== */
            $tabla = $data["tp_comprobante"] == 2 ? "notaVenta" : "comprobante";
            switch ($data["tp_comprobante"]) {
                case 2:
                    $tabla = "notaVenta";
                    break;
                case 3:
                case 1:
                    $tabla = "comprobante";
                    break;
                case 31:
                    $tabla = "guiasTransportista";
                    break;
            }

            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                "tipo" => "encomienda_cambio",
                "tabla" => $tabla,
                "id" => $data["id_venta"],
                "accion" => "insert"
            ]);

            $links = [];

            if (!empty($link_embarque)) {
                $links[] = $link_embarque;
            }

            $links[] = ['nombre' => 'Comprobante', 'link' => $link_comprobante];

            $query = $conn->prepare("
                SELECT 
                rotulado_e
                FROM configuracion_encomienda
            ");
            $query->execute();
            $permiso_rotulado = $query->fetchColumn();
            if ($permiso_rotulado == 1) {
                $links[] = ['nombre' => 'Rotulado', 'link' => URL . "encomienda/impresion/rotulo/" . $data['id_venta']];
            }

            $links[] = ['nombre' => 'Archivo', 'link' => URL . "encomienda/impresion/archivo/" . $data["id_venta"]];

            if (!empty($link_guia_a4)) {
                $links[] = $link_guia_a4;
            }

            return [
                'success' => true,
                "message" => [
                    "message" => "Venta registrada con éxito",
                    "id_venta" => $data["id_venta"],
                    "tp_comprobante" => $tp_comprobante,
                    "links" => $links,
                    "tp_comprobante" => $data['tp_comprobante'],
                    "salida" => $data['e_salida']
                ]
            ];
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "El registro se encuentra protegido", $e];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde." . $e];
                    break;
            }
        }
    }

    public function comprobante_salida($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
            INSERT INTO grupo_embarcaciones
            (fecha, id_usuario) VALUES(:fecha, :id_usuario)
            ");
            $fecha_actual = date('Y-m-d H:i:s');
            $query->bindParam(":fecha", $fecha_actual);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->execute();
            $id_grupo_embarcacion = $conn->lastInsertId();

            $query_embarcacion = $conn->prepare("
            INSERT INTO embarcaciones 
                (id_programacion_salida, id_usuario, tipo, id_grupo_embarcacion, tipo_programacion)
            VALUES 
                (:id_programacion_salida, :id_usuario, 'INDIVIDUAL', :id_grupo_embarcacion, 'salida')
            ");
            $query_embarcacion->bindParam(":id_programacion_salida", $data["salida"]);
            $query_embarcacion->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query_embarcacion->bindParam(":id_grupo_embarcacion", $id_grupo_embarcacion);
            $query_embarcacion->execute();

            $id_embarcacion = $conn->lastInsertId();

            $query_historial = $conn->prepare("
            INSERT INTO historial_embarcaciones 
                (encomienda_id, embarcacion_id)
            VALUES 
                (:encomienda_id, :embarcacion_id)
            ");
            $query_historial->bindParam(":encomienda_id", $data['id_encomienda']);
            $query_historial->bindParam(":embarcacion_id", $id_embarcacion);
            $query_historial->execute();

            $query_update_estado = $conn->prepare("
            UPDATE encomienda SET estado='EN TRANSITO' , id_terminal_actual = 0 WHERE id_encomienda = :id_encomienda
            ");
            $query_update_estado->bindParam(":id_encomienda", $data['id_encomienda']);
            $query_update_estado->execute();

            return ['success' => true, 'message' => 'Se hizo la actualización correctamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function add_detraccion($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            $fecha_detraccion = date("Y-m-d H:i:s");

            $query = $conn->prepare("INSERT INTO
             detraccion_operacion (
             comprobante_id,
             tp_detraccion_id,
             monto_operacion,
             valor_referencial,
             base_detraccion,
             porcentaje,
             monto_detraccion,
             fecha_operacion,
             anexo_version_id,
             motivo,
             usuario_creo,
             v_ref_carga_efectiva,
             v_ref_carga_util,
             tm_detraccion,
             id_config_v,
             ubigeo_origen,
             origen_detraccion,
             ubigeo_destino,
             destino_detraccion,
             id_medio_pago,
             ruta_origen,
             ruta_destino
            ) VALUES(
             :comprobante_id,
             1,
             :monto_operacion,
             :valor_referencial,
             :base_detraccion,
             4,
             :monto_detraccion,
             :fecha_operacion,
             1,
             :motivo,
             :usuario_creo,
             :v_ref_carga_efectiva,
             :v_ref_carga_util,
             :tm_detraccion,
             :id_config_v,
             :ubigeo_origen,
             :origen_detraccion,
             :ubigeo_destino,
             :destino_detraccion,
             :id_medio_pago,
             :ruta_origen,
             :ruta_destino
            )");
            $query->bindParam(":comprobante_id", $data['id_venta']);
            $query->bindParam(":monto_operacion", $data['total_operacion']);
            $query->bindParam(":valor_referencial", $data['v_ref_servicio']);
            $query->bindParam(":base_detraccion", $data['base_detraccion']);
            $query->bindParam(":monto_detraccion", $data['monto_detraccion']);
            $query->bindParam(":fecha_operacion", $fecha_detraccion);
            $query->bindParam(":motivo", $data['detalle_detraccion']);
            $query->bindParam(":usuario_creo", $this->id_usuario_sesion);
            $query->bindParam(":v_ref_carga_efectiva", $data['v_ref_carga_efectiva']);
            $query->bindParam(":v_ref_carga_util", $data['v_ref_carga_util']);
            $query->bindParam(":tm_detraccion", $data['tm_detraccion']);
            $query->bindParam(":id_config_v", $data['config_vehiculo']);
            $query->bindParam(":ubigeo_origen", $data['ubigeo_origen_d']);
            $query->bindParam(":origen_detraccion", $data['origen_detraccion']);
            $query->bindParam(":ubigeo_destino", $data['ubigeo_destino_d']);
            $query->bindParam(":destino_detraccion", $data['destino_detraccion']);
            $query->bindParam(":id_medio_pago", $data['medio_pago_detraccion']);
            $query->bindParam(":ruta_origen", $data['ruta_origen']);
            $query->bindParam(":ruta_destino", $data['ruta_destino']);
            $query->execute();

            return ['success' => true, "message" => "Detracción registrada con éxito."];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "El registro se encuentra protegido", $e];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $data["estado_envio"] = 'EN ORIGEN';
            $data['remitente'] = $data['remitente_id'];
            $data['destinatario'] = $data['destinatario_id'];

            // Extraccion de datos de la venta
            $query = $this->db->connect()->prepare("
            SELECT
            v.id_venta,
            v.estado,
            e.id_terminal_origen,
            e.id_encomienda
            FROM venta v
            LEFT JOIN  dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();
            $venta = $query->fetch(PDO::FETCH_ASSOC);

            $id_encomienda = $venta['id_encomienda'];
            $terminal_origen = $this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2 ? $venta['id_terminal_origen'] : $this->id_terminal_sesion;

            if (!$this->get_igv() || $this->get_igv() == 0) {
                return array('success' => false, "message" => array("message" => "Ingrese el IGV desde el apartado configuración."));
            }

            // La serie y el correlativo de la nota de venta no se actulizan
            $igv = $this->get_igv();
            // $op_gravada = $igv || $igv != 0 ? ($data["precio_venta"] / (($igv / 100) + 1)) : 0.0;
            $op_gravada = $data["precio_venta"] / (1 + ($igv / 100));
            $pass = $this->security->encryption($data["pass"]);

            $tp_comprobante_ref = $data['tp_comprobante_e'] ?? 0;
            $serie_ref = $data['serieEP'] ?? '';
            $correlativo_ref = $data['correlativoEP'] ?? '';
            $ruc_ref = $data['rucEP'] ?? '';

            $guia_serie = $data['serieGR'] ?? '';
            $guia_correlativo = $data['correlativoGR'] ?? '';
            $guia_ruc = $data['rucGR'] ?? '';
            $obs_destinatario = isset($data['obs_destinatario']) ? trim($data['obs_destinatario']) : '';
            $porcentaje_venta = 0;
            $factor_p_venta = 0;

            $query = $this->db->connect()->prepare("
            SELECT 
            porcent_venta
            FROM empresa
            WHERE id_empresa = :id_empresa");
            $query->bindParam(':id_empresa', $this->id_empresa_sesion);
            $query->execute();
            $permiso_porcentaje = $query->fetchColumn();

            if ($permiso_porcentaje == 1) {
                $query = $this->db->connect()->prepare("
                    SELECT 
                       porcent_venta_e
                    FROM configuracion
                ");
                $query->execute();
                $porcentaje_dato = floatval($query->fetchColumn());
                $factor_p_venta = $porcentaje_dato;
                $total_venta = floatval($data['precio_venta']);

                $porcentaje_venta = $total_venta * $porcentaje_dato / 100;
            }

            $modalidad_pago = $data["forma_pago"] == 2 && $data['pago'] == 'PAGADO' ? 'CREDITO' : $data["pago"];

            // Registrando la encomienda
            if (isset($data['e_domicilio']) && $data['e_domicilio'] == 1) {
                $query = $this->db->connect()->prepare("
                UPDATE encomienda SET
                id_almacen = :id_almacen,
                fecha_salida = :fecha_salida,
                estado = :estado,
                id_remitente = :id_remitente,
                id_destinatario = :id_destinatario,
                id_terminal_origen = :id_terminal_origen,
                id_terminal_destino = :id_terminal_destino,
                pago = :pago,
                pass = :pass,
                id_sesionpersonal = :id_sesionpersonal,
                dir_puntopartida = :dir_puntopartida,
                dir_puntollegada = :dir_puntollegada,
                ubi_partida = :ubi_partida,
                ubi_llegada = :ubi_llegada,
                pagador_flete = :pagador_flete,
                e_domicilio = :e_domicilio,
                tipo_envio = :tipo_envio,
                obs_destinatario = :obs_destinatario,
                tp_comprobante_ref = :tp_comprobante_ref, 
                serie_ref = :serie_ref, 
                correlativo_ref = :correlativo_ref, 
                ruc_ref = :ruc_ref, 
                guia_serie = :guia_serie, 
                guia_correlativo = :guia_correlativo, 
                guia_ruc = :guia_ruc
                WHERE id_encomienda = :id_encomienda
                ");
                $query->bindParam(":id_almacen", $data["almacen"]);
                $query->bindParam(":fecha_salida", $data["fecha_salida"]);
                $query->bindParam(":estado", $data["estado_envio"]);
                $query->bindParam(":id_remitente", $data["remitente"]);
                $query->bindParam(":id_destinatario", $data["destinatario"]);
                $query->bindParam(":id_terminal_origen", $this->id_terminal_sesion);
                $query->bindParam(":id_terminal_destino", $data["destino_terminal"]);
                $query->bindParam(":pago", $modalidad_pago);
                $query->bindParam(":pass", $pass);
                $query->bindParam(":id_sesionpersonal", $this->id_usuario_sesion);
                $query->bindParam(":dir_puntopartida", $data["p_partida"]);
                $query->bindParam(":dir_puntollegada", $data["p_llegada"]);
                $query->bindParam(":ubi_partida", $data["ubigeoP"]);
                $query->bindParam(":ubi_llegada", $data["ubigeoLle"]);
                $query->bindParam(":e_domicilio", $data["e_domicilio"]);
                $query->bindParam(":pagador_flete", $data["pagante_id"]);
                $query->bindParam(":tipo_envio", $data["pago"]);
                $query->bindParam(":obs_destinatario", $obs_destinatario);
                $query->bindParam(":tp_comprobante_ref", $tp_comprobante_ref);
                $query->bindParam(":serie_ref", $serie_ref);
                $query->bindParam(":correlativo_ref", $correlativo_ref);
                $query->bindParam(":ruc_ref", $ruc_ref);
                $query->bindParam(':guia_serie', $guia_serie);
                $query->bindParam(':guia_correlativo', $guia_correlativo);
                $query->bindParam(':guia_ruc', $guia_ruc);
                $query->bindParam(":id_encomienda", $id_encomienda);
            } else {
                // Original SQL query without the new fields
                $query = $this->db->connect()->prepare("
                UPDATE encomienda SET
                id_almacen = :id_almacen,
                fecha_salida = :fecha_salida,
                estado = :estado,
                id_remitente = :id_remitente,
                id_destinatario = :id_destinatario,
                id_terminal_origen = :id_terminal_origen,
                id_terminal_destino = :id_terminal_destino,
                pago = :pago,
                pass = :pass,
                id_sesionpersonal = :id_sesionpersonal,
                tipo_envio = :tipo_envio,
                obs_destinatario = :obs_destinatario,
                tp_comprobante_ref = :tp_comprobante_ref, 
                serie_ref = :serie_ref, 
                correlativo_ref = :correlativo_ref, 
                ruc_ref = :ruc_ref, 
                guia_serie = :guia_serie, 
                guia_correlativo = :guia_correlativo, 
                guia_ruc = :guia_ruc
                WHERE id_encomienda = :id_encomienda
                ");
                $query->bindParam(":id_almacen", $data["almacen"]);
                $query->bindParam(":fecha_salida", $data["fecha_salida"]);
                $query->bindParam(":estado", $data["estado_envio"]);
                $query->bindParam(":id_remitente", $data["remitente"]);
                $query->bindParam(":id_destinatario", $data["destinatario"]);
                $query->bindParam(":id_terminal_origen", $terminal_origen);
                $query->bindParam(":id_terminal_destino", $data["destino_terminal"]);
                $query->bindParam(":pago", $modalidad_pago);
                $query->bindParam(":pass", $pass);
                $query->bindParam(":id_sesionpersonal", $this->id_usuario_sesion);
                $query->bindParam(":tipo_envio", $data['pago']);
                $query->bindParam(":obs_destinatario", $obs_destinatario);
                $query->bindParam(":tp_comprobante_ref", $tp_comprobante_ref);
                $query->bindParam(":serie_ref", $serie_ref);
                $query->bindParam(":correlativo_ref", $correlativo_ref);
                $query->bindParam(":ruc_ref", $ruc_ref);
                $query->bindParam(':guia_serie', $guia_serie);
                $query->bindParam(':guia_correlativo', $guia_correlativo);
                $query->bindParam(':guia_ruc', $guia_ruc);
                $query->bindParam(":id_encomienda", $id_encomienda);
            }
            $query->execute();

            $data["id_encomienda"] = $id_encomienda;
            $data["productos"] = json_decode($data["productos"]);

            // Un solo proceso
            $resultados = $this->register_productos($data);
            $total_igv = $resultados["total_igv"];
            $total_gravada = $resultados["total_gravada"];
            $total_inafecta = $resultados["total_inafecta"];
            $total_exonerada = $resultados["total_exonerada"];

            $estado_comp = $data["forma_pago"] == 2 || $data['pago'] == 'PAGO EN BLOQUE' ? 'PENDIENTE' : 'PAGADO';
            $id_medio_pago = isset($data['medio_pago']) && $data['medio_pago'] !== "" ? $data['medio_pago'] : null;

            // Registrando la venta
            if ($data["pago"] == "PAGADO") {

                $query = $this->db->connect()->prepare("
                UPDATE venta SET
                id_cliente = :id_cliente,
                id_forma_pago = :forma_pago,
                id_medio_pago = :id_medio_pago,
                id_tp_moneda = :id_tp_moneda,
                op_igv = :igv,
                estado = :estado,
                op_gravada = :op_gravada,
                op_exonerada = :op_exonerada,
                op_inafecta = :op_inafecta,
                total = :total,
                obs = :referencia,
                id_sesionpersonal = :id_sesionpersonal,
                peso_encomienda = :peso_total,
                porcentaje_venta = :porcentaje_venta,
                factor_p_venta = :factor_p_venta
                WHERE id_venta = :id_venta
                ");
                $query->bindParam(':id_cliente', $data["remitente"]);
                $query->bindParam(':forma_pago', $data["forma_pago"]);
                $query->bindParam(':id_medio_pago', $id_medio_pago);
                $query->bindParam(':id_tp_moneda', $this->tp_moneda);
                $query->bindParam(':igv', $total_igv);
                // $query->bindParam(':monto_antes_igv', $data["precio_venta"]);
                $query->bindParam(':estado', $estado_comp);
                $query->bindParam(':op_gravada', $total_gravada);
                $query->bindParam(':op_exonerada', $total_exonerada);
                $query->bindParam(':op_inafecta', $total_inafecta);
                $query->bindParam(':total', $data["precio_venta"]);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->bindParam(':referencia', $data['referencia']);
                $query->bindParam(':peso_total', $data['peso_venta']);
                $query->bindParam(':porcentaje_venta', $porcentaje_venta);
                $query->bindParam(':factor_p_venta', $factor_p_venta);
                $query->bindParam(':id_venta', $data["id_venta"]);
                $query->execute();
            } else {
                $query = $this->db->connect()->prepare("
                UPDATE venta SET
                id_cliente = :id_cliente,
                id_forma_pago = :forma_pago,
                id_tp_moneda = :id_tp_moneda,
                op_igv = :igv,
                estado = :estado,
                op_gravada = :op_gravada,
                op_exonerada = :op_exonerada,
                op_inafecta = :op_inafecta,
                total = :total,
                obs = :referencia,
                id_sesionpersonal = :id_sesionpersonal,
                peso_encomienda = :peso_total,
                porcentaje_venta = :porcentaje_venta,
                factor_p_venta = :factor_p_venta
                WHERE id_venta = :id_venta
                ");
                $query->bindParam(':id_cliente', $data["remitente"]);
                $query->bindParam(':forma_pago', $data["forma_pago"]);
                $query->bindParam(':id_tp_moneda', $this->tp_moneda);
                $query->bindParam(':igv', $total_igv);
                // $query->bindParam(':monto_antes_igv', $data["precio_venta"]);
                $query->bindParam(':estado', $estado_comp);
                $query->bindParam(':op_gravada', $total_gravada);
                $query->bindParam(':op_exonerada', $total_exonerada);
                $query->bindParam(':op_inafecta', $total_inafecta);
                $query->bindParam(':total', $data["precio_venta"]);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->bindParam(':peso_total', $data['peso_venta']);
                $query->bindParam(':referencia', $data['referencia']);
                $query->bindParam(':porcentaje_venta', $porcentaje_venta);
                $query->bindParam(':factor_p_venta', $factor_p_venta);
                $query->bindParam(':id_venta', $data["id_venta"]);
                $query->execute();
            }

            // Creando registro si en caso es credito 
            if ($data["forma_pago"] == 2) {
                $numero_cuota = "001";
                $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                $query = $this->db->connect()->prepare("INSERT INTO cuota (
                comprobante_id, numero, importe, fecha_vencimiento, estado)
                VALUES(
                :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                ");
                $query->bindParam(':comprobante_id', $data["id_venta"]);
                $query->bindParam(':numero', $numero_cuota);
                $query->bindParam(':importe', $data['monto_credito']);
                $query->bindParam(':estado', $estado_cuota);
                $query->bindParam(':fecha_vencimiento', $data['fecha_credito']);
                $query->execute();
            }

            $data["estado"] = $venta["estado"];

            // Registrando detalle venta
            $query = $this->db->connect()->prepare("
            UPDATE dt_venta SET
            precio = :precio,
            id_sesionpersonal = :id_sesionpersonal
            WHERE id_venta = :id_venta AND id_encomienda = :id_encomienda
            ");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->bindParam(':precio', $data["precio_venta"]);
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->execute();

            $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                "tipo" => "encomienda_cambio",
                "tabla" => "notaVenta",
                "id" => $data["id_venta"],
                "accion" => "update"
            ]);

            // Generando el código QR y facturación
            // if (in_array($data["tp_comprobante"], [1, 3]) && $data["pago"] == "PAGADO") {
            //     $id_venta = $data["id_venta"];
            //     $this->generar_codQR($id_venta);
            //     $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($data["id_venta"])));
            //     $resp = json_decode($rpta_sunat, true);
            //     // Actualizando los datos de la venta
            //     $resp[0]["id_venta"] = $data["id_venta"];
            //     $this->updateVentaSetForSunat($resp[0]);
            // }


            $link_comprobante = URL . "encomienda/impresion/" . 'nota_venta/' . $data["id_venta"];
            return array(
                'success' => true,
                "message" => array(
                    "message" => "Venta editada con éxito",
                    "links" => array(
                        array('nombre' => 'Comprobante', 'link' => $link_comprobante),
                        array('nombre' => 'Archivo', 'link' => URL . "encomienda/impresion/archivo/" . $data["id_venta"]),
                    )
                )
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El registro se encuentra protegido", $e);
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e);
                    break;
            }
        }
    }

    public function get_allTerminalDestino()
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
            WHERE id_terminal!=:id_terminal_origen
            ");
            $query->bindParam(':id_terminal_origen', $this->id_terminal_sesion);
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

    public function anularVenta($data)
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("
                SELECT 
                dt.id_encomienda,
                v.tipo_ruc
                FROM dt_venta dt
                LEFT JOIN venta v ON dt.id_venta = v.id_venta
                WHERE dt.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $data['id_venta']);
            $query->execute();
            $data_encomienda = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data_encomienda) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la venta para anular'
                ];
            }

            $id_encomienda = $data_encomienda['id_encomienda'];
            $tipo_ruc = $data_encomienda['tipo_ruc'];

            $resp = "Venta anulada correctamente";

            if (!empty($id_encomienda)) {
                $query = $conn->prepare("UPDATE encomienda SET estado='CANCELADO' WHERE id_encomienda=:id_encomienda");
                $query->bindParam(":id_encomienda", $id_encomienda);
                $query->execute();
            }

            if ($data["tp_comprobante"] == 1 || $data["tp_comprobante"] == 3) {
                if ($data["tp_comprobante"] == 1) {
                    $id_resumen = $this->set_resumen_f($data["id_venta"], $tipo_ruc);
                } else {
                    $id_resumen = $this->set_resumen($data["id_venta"], $tipo_ruc);
                }
                $json = $this->resumen($data["id_venta"], $id_resumen, $tipo_ruc);
                $resp_api = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);
                $resp = $this->guardar_ticket($resp_api, $id_resumen);
            }
            // Anuladno la venta
            $query = $conn->prepare("UPDATE venta SET estado='ANULADO', total='0.00', op_gravada='0.00',  op_exonerada='0.00', op_igv='0.00', porcentaje_venta='0.00', factor_p_venta='0.00' WHERE id_venta=:id_venta");
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();

            return ['success' => true, "message" => $resp];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    $errorMessage = "El registro se encuentra protegido. Error: " . $e->getMessage();
                    return ['success' => false, 'message' => $errorMessage];
                    break;
                default:
                    $errorMessage = "Ha ocurrido un error, intentalo más tarde. Error: " . $e->getMessage();
                    return ['success' => false, 'message' => $errorMessage];
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
            $resp = $reply_sunat[0]; // Obtiene el primer elemento de la respuesta
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
                $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($id_venta)));
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
            //$message = "Comprobante reenviado correctamente";
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
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $tipo_ruc = 2;
            } else {
                $tipo_ruc = 1;
            }
            $fechaActual = date('Y-m-d');
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

            $query = $this->db->connect()->prepare("SELECT * FROM venta ORDER BY id_venta DESC LIMIT 1");
            $query->execute();
            $ultima_Venta = $query->fetchColumn();

            // Insertar en la tabla de resumen
            $query = $this->db->connect()->prepare("INSERT INTO resumen_baja (
            id_comprobante, boleta, factura, fecha_envio, fecha_referencia, correlativo, tipo_ruc)
            VALUES(
            :id_comprobante, :boleta, :factura, :fecha_envio, :fecha_referencia, :correlativo, :tipo_ruc)");
            $query->bindValue(':id_comprobante', $ultima_Venta);
            $query->bindValue(':boleta', 1);
            $query->bindValue(':factura', 1);
            $query->bindParam(':fecha_envio', $fechaActual);
            $query->bindParam(':fecha_referencia', $fechaActual);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':tipo_ruc', $tipo_ruc);
            $query->execute();

            // Obtener el id del ultimo resumen
            $query = $this->db->connect()->prepare("SELECT * FROM resumen_baja ORDER BY id_resumen DESC LIMIT 1");
            $query->execute();
            $id_resumen = $query->fetchColumn();

            $emisor = $this->consult_emisor();

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
            $ultimo = $this->Ultimo_resumen();
            // Inicializar el array de resultados
            $items = array();
            $i = 0;
            foreach ($data as $id_venta) {
                // Hacer la iterracion para obtent datos de cada id_Venta
                $query = $this->db->connect()->prepare("SELECT
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
                    $detalle["icbper"] = 0;
                    $detalle["codigos"] = array(1000, "IGV", "VAT");
                }

                // Agregar los resultados al array $items
                $items = array_merge($items, $detalles);
            }
            //Devolviendo respuesta
            $respuesta = array(
                "ose" => $emisor['ose'],
                "emisor" => $emisor,
                "cabecera" => $cabecera,
                "items" => $items
            );
            $json = json_encode($respuesta);
            $resp_api = json_decode($this->enviar_json_a_api($json, true), true);
            $resp = $this->guardar_ticket($resp_api, $ultimo);
            if (empty($resp[0]['ticket'])) {
                $this->UpdateComprobanteResumen($data);
                return array(
                    "success" => true,
                    "message_sunat" => "Se ha creado el resumen " . $resp_api[0]['nombre_xml'],
                );
            } else {
                return array(
                    "success" => false,
                    "message_sunat" => "Ha ocurrido un error",
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

    public function conversor_encomienda($datos, $pago_destino = 0)
    {
        $cuotas = [];
        $monto_credito = 0;
        if ($datos['RUC_ENCOMIENDA'] == 1) {
            $emisor = $this->consult_emisor_encomienda();
        } else {
            $emisor = $this->consult_emisor();
        }

        $cliente = [
            "tipo_documento" => $datos['BODY']['id_re_docu'],
            "ruc" => $datos['BODY']['remitente_num_docu'],
            "razon_social" => $datos['BODY']['remitente_nombres'] . ' ' . $datos['BODY']['remitente_apellidos'],
            "direccion" => $datos['BODY']['remitente_direccion'],
            "pais" => "PE"
        ];

        if ($datos['BODY']['id_forma_pago'] == 2) {
            $cuotas = [
                [
                    "numero" => $datos['CUOTAS']['numero'],
                    "importe" => number_format($datos['CUOTAS']['importe'], 2, '.', ''),
                    "vencimiento" => $datos['CUOTAS']['fecha_vencimiento']
                ]
            ];
            $monto_credito = number_format($datos['CUOTAS']['importe'], 2, '.', '');
        }


        $cabecera = [
            "tipo_operacion" => $datos['BODY']['afecta_detraccion'] == 1 ? "1004" : "0101",
            "tipo_comprobante" => $datos['BODY']['tipo_comprobante'],
            "moneda" => $datos['BODY']['moneda'],
            "serie" => $datos['BODY']['serie'],
            "correlativo" => $datos['BODY']['correlativo'],
            "total_op_gravadas" => $datos['BODY']['op_gravada'],
            "igv" => $datos['BODY']['op_igv'],
            "icbper" => 0.00,
            "total_op_exoneradas" => $datos['BODY']['op_exonerada'],
            "total_op_inafectas" => $datos['BODY']['op_inafecta'],
            "total_antes_impuestos" => $datos['BODY']['op_gravada'] + $datos['BODY']['op_exonerada'] + $datos['BODY']['op_inafecta'],
            "total_impuestos" => $datos['BODY']['op_igv'],
            "total_despues_impuestos" => $datos['BODY']['total'],
            "descuento_global" => 0.00,
            "suma_descuento_item" => 0.00,
            "total_a_pagar" => $datos['BODY']['total'],
            "fecha_emision" => $datos['BODY']['fecha_registro_format'],
            "hora_emision" => $datos['BODY']['hora_registro_format'],
            "fecha_vencimiento" => $datos['BODY']['fecha_registro_format'],
            "forma_pago" => ucwords(strtolower(ucfirst($datos['BODY']['forma_pago']))),
            "monto_credito" => $monto_credito,
            "anexo_sucursal" => "0000",
            "cuotas" => $cuotas,
            "detraccion" => $datos['BODY']['afecta_detraccion'] == 1 ? true : false,
            "detraccion_data" => $datos['BODY']['afecta_detraccion'] == 1 ? [
                "codigo_detraccion" => $datos['DATA_DETRACCION']['tipo_detraccion_codigo'],
                "ubigeo_origen" => $datos['DATA_DETRACCION']['ubigeo_origen'],
                "direccion_origen" => $datos['DATA_DETRACCION']['origen_detraccion'],
                "ubigeo_destino" => $datos['DATA_DETRACCION']['ubigeo_destino'],
                "direccion_destino" => $datos['DATA_DETRACCION']['destino_detraccion'],
                "pocentaje_detraccion" => $datos['DATA_DETRACCION']['porcentaje'],
                "cuenta_banco" => $datos['HEADER']['HEADER_EMPRESA']['nro_cuenta_BN'],
                "monto_detraccion" => $datos['DATA_DETRACCION']['monto_detraccion'],
                "codigo_medio_pago" => $datos['DATA_DETRACCION']['medio_pago_codigo'],
                "descripcion" => $datos['DATA_DETRACCION']['descripcion'],
                "monto_ref_servicio" => $datos['DATA_DETRACCION']['valor_referencial'],
                "valor_carga_efectiva" => $datos['DATA_DETRACCION']['v_ref_carga_efectiva'],
                "valor_carga_util" => $datos['DATA_DETRACCION']['v_ref_carga_util']
            ] : []
        ];

        // Inicializa el número de item en 1 antes del bucle de iteración
        $numeroItem = 1;

        // Define el array para almacenar los ítems
        $items = [];

        foreach ($datos['DETALLE'] as $itemData) {
            // Condiconales para la afectacion
            if ($itemData['op_gravada'] > 0) {
                $codigos = ["S", "10", "1000", "IGV", "VAT"];
                $itemData['valor_unitario'] = $itemData['precio'] / 1.18;
                $itemData['valor_unitario'] = number_format($itemData['valor_unitario'], 2, '.', '');
                $itemData['valor_total'] = $itemData['op_gravada'];
            } elseif ($itemData['op_exonerada'] > 0) {
                $codigos = ["E", "20", "9997", "EXO", "VAT"];
                $itemData['valor_unitario'] = $itemData['precio'];
                $itemData['valor_total'] = $itemData['op_total'];
            } elseif ($itemData['op_inafecta'] > 0) {
                $codigos = ["O", "30", "9998", "INA", "FRE"];
                $itemData['valor_unitario'] = $itemData['precio'];
                $itemData['valor_total'] = $itemData['op_total'];
            } else {
            }

            $item = [
                "item" => $numeroItem,
                "nombre" => $itemData['descripcion'],
                "cantidad" => $itemData['cantidad'],
                "codigo" => 0,
                "valor_unitario" => $itemData['valor_unitario'],
                "precio_lista" => $itemData['precio'],
                "valor_total" => $itemData['valor_total'],
                "igv" => $itemData['op_igv'],
                "icbper" => 0.00,
                "factor_icbper" => 0.00,
                "total_antes_impuestos" => $itemData['valor_total'],
                "total_impuestos" => $itemData['op_igv'],
                "porcentaje_igv" => 18,
                "unidad" => $itemData['unidad_medida'],
                "codigos" => $codigos
            ];
            $numeroItem++;
            $items[] = $item;
        }
        $result = [
            "ose" => $emisor['ose'],
            "emisor" => $emisor,
            "cliente" => $cliente,
            "cabecera" => $cabecera,
            "items" => $items
        ];

        // Codificar el resultado en JSON
        $jsonResult = json_encode($result);

        return $jsonResult;
    }

    public function getDataComprobante($id_venta, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }

        // Configuracion
        $ruc_encomienda_separado = $this->get_ruc_encomienda_separado();

        $query = $conn->prepare("
        SELECT
            t.id_terminal,
            t.id_empresa,
            t.logo,
            t.nombre,
            t.tipo,
            t.ubigeo,
            ub.depa AS ubigeo_depa,
            ub.provi AS ubigeo_provi,
            ub.distri AS ubigeo_distri,
            t.direccion_fiscal,
            t.direccion_comercial,
            t.cod_domicilio_fiscal,
            t.celular,
            t.email,
            t.siteweb,
            td.id_tp_docu AS tp_doc_id
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=t.ubigeo
        LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
        WHERE t.id_terminal=:id_terminal");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $header_terminal = $query->fetch(PDO::FETCH_ASSOC);

        $query = $conn->prepare(" 
        SELECT
        v.id_venta,
        v.id_terminal,
        v.id_vendedor,
        v.obs,
        v.peso_encomienda,
        vd.nombres AS vendedor_nombres,
        vd.apellidos AS vendedor_apellidos,
        v.id_cliente,
        c_r.nombres AS remitente_nombres,
        c_r.apellidos AS remitente_apellidos,
        c_r.celular AS remitente_celular, -- NUEVO: Celular del cliente
        CONCAT(
            COALESCE(c_r.direccion, ''),
            ' - ',
            COALESCE(ub_r.distri, ''),
            ', ',
            COALESCE(ub_r.provi, ''),
            ', ',
            COALESCE(ub_r.depa, '')
        ) AS remitente_direccion_completa,
        c_r.direccion AS remitente_direccion,
        tp_d_cr.descripcion AS remitente_tp_docu,
        tp_d_cr.id_tp_docu AS id_re_docu,
        c_r.num_docu AS remitente_num_docu,
        c_r.ubigeo AS remitente_ubigeo,
        ub_r.depa AS remitente_ubigeo_depa,
        ub_r.provi AS remitente_ubigeo_provi,
        ub_r.distri AS remitente_ubigeo_distri,
        c_d.nombres AS destinatario_nombres,
        c_d.apellidos AS destinatario_apellidos,
        c_d.celular AS destinatario_celular,
        tp_d_cd.descripcion AS destinatario_tp_docu,
        c_d.num_docu AS destinatario_num_docu,
        c_d.direccion AS destinatario_direccion,
        c_d.ubigeo AS destinatario_ubigeo,
        v.id_forma_pago,
        f_p.descripcion AS forma_pago,
        v.id_medio_pago,
        m_p.descripcion AS medio_pago,
        v.id_tp_moneda,
        tp_m.codigo AS moneda,
        v.id_tp_comprobante,
        tp_c.codigo AS tipo_comprobante,
        tp_c.descripcion AS tp_comprobante,
        v.id_caja_chica,
        v.id_serie,
        v.serie,
        v.correlativo,
        v.descuento,
        v.op_igv,
        v.estado,
        v.envio_sunat,
        v.descrip_cdr_sunat,
        v.cod_qr,
        v.hash_cdr,
        v.fecha_emision,
        v.fecha_registro,
        date_format(v.fecha_registro, '%Y-%m-%d') AS fecha_registro_format,
        date_format(v.fecha_registro, '%H:%i:%s') AS hora_registro_format,
        v.op_gravada,
        v.op_exonerada,
        v.op_inafecta,
        v.total,
        v.tipo_ruc,
        v.afecta_detraccion,
        e.pass AS encomienda_pass,
        e.codigo AS codigo_tracking,
        e.obs_destinatario,
        e.tp_comprobante_ref,
        e.serie_ref,
        e.correlativo_ref,
        e.serie_ref,
        e.ruc_ref,
        e.guia_serie,
        e.guia_correlativo,
        e.guia_ruc,
        e.obs_destinatario,
        e.e_domicilio AS es_domicilio,  -- MODIFICACIÓN: Flag para tipo de entrega (1=domicilio, 0=terminal)
        e.dir_puntollegada AS dir_destino_custom,  -- MODIFICACIÓN: Dirección custom para domicilio si existe
        e.pago AS encomienda_pago 
        FROM venta v
        LEFT JOIN usuario c_r ON c_r.id_usuario=v.id_cliente
        LEFT JOIN tp_docu tp_d_cr ON tp_d_cr.id_tp_docu=c_r.id_tp_docu
        LEFT JOIN ubigeo ub_r ON ub_r.cod_ubigeo = c_r.ubigeo
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
        LEFT JOIN forma_pago f_p ON f_p.id_forma_pago= v.id_forma_pago
        LEFT JOIN medio_pago m_p ON m_p.id_medio_pago= v.id_medio_pago
        LEFT JOIN usuario vd ON vd.id_usuario=v.id_vendedor
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=v.id_tp_moneda
        LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
        LEFT JOIN usuario c_d ON c_d.id_usuario=e.id_destinatario
        LEFT JOIN tp_docu tp_d_cd ON tp_d_cd.id_tp_docu=c_d.id_tp_docu
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $body = $query->fetch(PDO::FETCH_ASSOC);

        if ($body['tipo_ruc'] == 2) {
            $query = $conn->prepare("
            SELECT 
                e.logo_encomienda AS logo,
                e.nro_cuenta_BN_encomienda AS nro_cuenta_BN,
                e.envio_ose,
                e.num_docu_encomienda AS num_docu,
                e.razon_social_encomienda AS razon_social,
                e.razon_social_encomienda AS nombre_comercial,
                e.ubigeo_encomienda AS ubigeo,
                m_terminales AS permiso_t,
                direccion_fiscal_encomienda AS direccion_fiscal,
                fr_comprobante AS frase_empresa,
                termscond_encomienda AS terminos_condiciones,
                nro_cuenta_BN AS cuenta_detraccion,
                nro_cuenta_bancaria,
                telefono_empresa,
                mostrar_direccion_completa,
                mostrar_tracking,
                mostrar_vendedor,
                datos_destinatario,
                origen_destino,
                impresion_ecompleta
            FROM empresa e");
            $query->execute();
            $header_empresa = $query->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = $conn->prepare("
            SELECT *,
            m_terminales AS permiso_t,
            fr_comprobante AS frase_empresa,
            termscond_encomienda AS terminos_condiciones,
            nro_cuenta_BN AS cuenta_detraccion,
            nro_cuenta_bancaria,
            telefono_empresa,
            mostrar_direccion_completa,
            mostrar_tracking,
            mostrar_vendedor,
            datos_destinatario,
            origen_destino,
            impresion_ecompleta
            FROM empresa");
            $query->execute();
            $header_empresa = $query->fetch(PDO::FETCH_ASSOC);
        }

        if ($header_empresa['permiso_t'] == '1') {
            $query = $conn->prepare("SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            WHERE e.id_empresa = (SELECT id_empresa FROM terminal WHERE id_terminal = :id_terminal);
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $terminales = [];
        }

        $data_detraccion = [];
        if ($body['afecta_detraccion'] == 1) {
            $query = $conn->prepare("
            SELECT 
                d.ubigeo_origen,
                d.ubigeo_destino,
                d.porcentaje,
                d.porcentaje AS porcentaje_operacion,  -- 4.0000
                tp_d.porcentaje AS porcentaje_decimal, -- 0.0400
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
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $data_detraccion = $query->fetch(PDO::FETCH_ASSOC);

            if (!empty($header_empresa['cuenta_detraccion'])) {
                $data_detraccion['cuenta_detraccion'] = $header_empresa['cuenta_detraccion'];
            }
        }

        $body["encomienda_pass"] = $this->security->decryption($body["encomienda_pass"]);

        $cuotas = [];
        // Logica para forma de pago
        if ($body["id_forma_pago"] == 2) {
            $query = $conn->prepare("
             SELECT * FROM cuota
             WHERE comprobante_id = :id_venta");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $cuotas = $query->fetch(PDO::FETCH_ASSOC);
        }

        $query = $conn->prepare("
        SELECT
        dt_v.id_dt_venta,
        dt_v.id_tp_servicio,
        dt_v.id_programacion,
        t_o.nombre AS terminal_origen,
        t_d.nombre AS terminal_destino,
        t_o.direccion_comercial AS terminal_origen_direccion,
        t_d.direccion_comercial AS terminal_destino_direccion,
        t_o.ubigeo AS terminal_origen_ubigeo,  -- Ubigeo del terminal origen
        t_d.ubigeo AS terminal_destino_ubigeo, -- Ubigeo del terminal destino
        dt_v.precio,
        e.e_domicilio AS es_domicilio,
        e.dir_puntollegada AS dir_destino_custom,
        e.ubi_partida AS ubi_partida,           -- Ubigeo partida (para domicilio)
        e.ubi_llegada AS ubi_llegada,           -- Ubigeo llegada (para domicilio)
        -- UBIGEO ORIGEN COMPLETO
        ub_o.depa AS ubigeo_origen_depa,
        ub_o.provi AS ubigeo_origen_provi,
        ub_o.distri AS ubigeo_origen_distri,
        -- UBIGEO DESTINO TERMINAL COMPLETO
        ub_d.depa AS ubigeo_destino_depa,
        ub_d.provi AS ubigeo_destino_provi,
        ub_d.distri AS ubigeo_destino_distri,
        -- UBIGEO LLEGADA DOMICILIO COMPLETO
        ub_llegada.depa AS ubigeo_llegada_depa,
        ub_llegada.provi AS ubigeo_llegada_provi,
        ub_llegada.distri AS ubigeo_llegada_distri
        FROM dt_venta dt_v
        LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
        LEFT JOIN terminal t_o ON t_o.id_terminal=e.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=e.id_terminal_destino
        LEFT JOIN ubigeo ub_o ON ub_o.cod_ubigeo = t_o.ubigeo  -- Para terminal origen
        LEFT JOIN ubigeo ub_d ON ub_d.cod_ubigeo = t_d.ubigeo  -- Para terminal destino
        LEFT JOIN ubigeo ub_llegada ON ub_llegada.cod_ubigeo = e.ubi_llegada  -- Para domicilio destino
        WHERE dt_v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $destino_raw = $query->fetch(PDO::FETCH_ASSOC);

        // MODIFICACIÓN: Lógica para procesar DESTINO y determinar TIPO
        $es_domicilio = isset($destino_raw['es_domicilio']) ? ($destino_raw['es_domicilio'] == 1) : empty($destino_raw['terminal_destino']);
        $tipo = $es_domicilio ? 'ENTREGA A DOMICILIO' : 'TERMINAL';

        // MODIFICACIÓN COMPLETA: Estructura el destino incluyendo TODOS los campos de ubigeo
        $destino = [
            'terminal_origen' => $destino_raw['terminal_origen'] ?? '',
            'terminal_origen_direccion' => $destino_raw['terminal_origen_direccion'] ?? '',
            'terminal_origen_ubigeo' => $destino_raw['terminal_origen_ubigeo'] ?? '',
            'ubigeo_origen_depa' => $destino_raw['ubigeo_origen_depa'] ?? '',
            'ubigeo_origen_provi' => $destino_raw['ubigeo_origen_provi'] ?? '',
            'ubigeo_origen_distri' => $destino_raw['ubigeo_origen_distri'] ?? '',

            'terminal_destino' => $es_domicilio ? '' : ($destino_raw['terminal_destino'] ?? ''),
            'terminal_destino_direccion' => $es_domicilio
                ? ($destino_raw['dir_destino_custom'] ?? $body['destinatario_direccion'] ?? '')
                : ($destino_raw['terminal_destino_direccion'] ?? ''),
            'terminal_destino_ubigeo' => $destino_raw['terminal_destino_ubigeo'] ?? '',
            'ubigeo_destino_depa' => $destino_raw['ubigeo_destino_depa'] ?? '',
            'ubigeo_destino_provi' => $destino_raw['ubigeo_destino_provi'] ?? '',
            'ubigeo_destino_distri' => $destino_raw['ubigeo_destino_distri'] ?? '',

            'dir_destino_custom' => $destino_raw['dir_destino_custom'] ?? '',
            'tipo' => $tipo,
            'precio' => $destino_raw['precio'] ?? 0,

            'ubi_partida' => $destino_raw['ubi_partida'] ?? '',
            'ubi_llegada' => $destino_raw['ubi_llegada'] ?? '',
            'ubigeo_llegada_depa' => $destino_raw['ubigeo_llegada_depa'] ?? '',
            'ubigeo_llegada_provi' => $destino_raw['ubigeo_llegada_provi'] ?? '',
            'ubigeo_llegada_distri' => $destino_raw['ubigeo_llegada_distri'] ?? '',
        ];

        $query = $conn->prepare("SELECT id_encomienda FROM dt_venta WHERE id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $id_encomienda = $query->fetch(PDO::FETCH_ASSOC)["id_encomienda"];

        $query = $conn->prepare("
        SELECT 
        pe.*,
        pe.unid_medida,
        um.abreviatura AS unidad_medida
        FROM producto_encomienda pe
        LEFT JOIN unidad_medida um ON um.id = pe.id_unidad_medida
        WHERE pe.id_encomienda = :id_encomienda;
        ");
        $query->bindParam(":id_encomienda", $id_encomienda);
        $query->execute();
        $detalle = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare(
            "
        SELECT igv FROM configuracion"
        );
        $query->execute();
        $igv = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
             SELECT 
                v_nv.id_venta,
                v_nv.serie,
                v_nv.correlativo,
                v_nv.total,
                v_nv.fecha_emision
               FROM venta v_nv
              WHERE v_nv.id_pago = :id_comprobante
            ");
        $query->bindParam(":id_comprobante", $id_venta);
        $query->execute();
        $ventas_pagadas = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $conn->prepare("
                SELECT 
                numero_GN
                FROM configuracion
            ");
        $query->execute();
        $mostrar_doc_relacionados = $query->fetchColumn();
        return [
            "HEADER" => [
                "HEADER_EMPRESA" => $header_empresa,
                "HEADER_TERMINAL" => $header_terminal,
            ],
            "RUC_ENCOMIENDA" => $ruc_encomienda_separado,
            "TERMINALES" => $terminales,
            "BODY" => $body,
            "DESTINO" => $destino,
            "DETALLE" => $detalle,
            "IGV" => $igv,
            "CUOTAS" => $cuotas,
            "DATA_DETRACCION" => $data_detraccion,
            "ventas_pagadas" => $ventas_pagadas,
            "mostrar_doc_relacionados" => $mostrar_doc_relacionados
        ];
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
        WHERE tp_s.descripcion='ENCOMIENDA' AND v.id_tp_comprobante = 3 AND v.envio_sunat = 0
        ORDER BY v.correlativo DESC");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('data' => $reply));
    }

    public function get_ctgEncomienda()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM ctg_encomienda ORDER BY id_ctg_encomienda DESC");
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

    public function register_productos($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("DELETE FROM producto_encomienda WHERE id_encomienda=:id_encomienda");
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
            $query->execute();

            $t_operacion = $data["t_operacion"];

            // Obtener IGV de la tabla configuracion
            $query = $conn->prepare("SELECT igv FROM configuracion");
            $query->execute();
            $igv_configuracion = $query->fetchColumn();

            $total_igv = 0;
            $total_gravada = 0;
            $total_inafecta = 0;
            $total_exonerada = 0;

            $register = function ($productos) use ($data, $igv_configuracion, &$total_igv, &$total_gravada, &$total_inafecta, &$total_exonerada, $t_operacion, $conn) {

                // Obtener la afectación desde ctg_encomienda
                $query = $conn->prepare("SELECT afectacion FROM ctg_encomienda WHERE id_ctg_encomienda = :id_ctg_encomienda");
                $query->bindParam(':id_ctg_encomienda', $productos[0]);
                $query->execute();
                $resultado = $query->fetch(PDO::FETCH_ASSOC);
                $afectacion = $t_operacion == "EXO" ? $t_operacion : $resultado['afectacion'];

                $precio = $productos[3];
                $cantidad = $productos[2];
                $precioCantidad = $precio * $cantidad;

                switch ($afectacion) {
                    case 'IGV':
                        $op_gravada = $precioCantidad / 1.18;
                        $igv = $op_gravada * ($igv_configuracion / 100);
                        $op_exonerada = 0;
                        $op_inafecta = 0;
                        $op_total = $precio * $cantidad;
                        break;
                    case 'EXO':
                        $igv = 0;
                        $op_gravada = 0;
                        $op_exonerada = $precio * $cantidad;
                        $op_inafecta = 0;
                        $op_total = $precio * $cantidad;
                        break;
                    case 'GRA':
                        $igv = $precio * $igv_configuracion;
                        $op_gravada = $precio;
                        $op_exonerada = 0;
                        $op_inafecta = 0;
                        $op_total = ($op_gravada + $op_exonerada + $op_inafecta) * $cantidad;
                        break;
                    case 'INA':
                        $igv = 0;
                        $op_gravada = 0;
                        $op_exonerada = 0;
                        $op_inafecta = $precio * $cantidad;
                        $op_total = $precio * $cantidad;
                        break;
                    default:
                        $igv = 0;
                        $op_gravada = 0;
                        $op_exonerada = 0;
                        $op_inafecta = 0;
                        $op_total = $precio * $cantidad;
                        break;
                }

                $total_igv += $igv;
                $total_gravada += $op_gravada;
                $total_inafecta += $op_inafecta;
                $total_exonerada += $op_exonerada;

                $query = $conn->prepare("
                    INSERT INTO producto_encomienda
                    (
                      id_encomienda, id_ctg_encomienda, descripcion, cantidad, precio, precio_kg, obs, peso, id_sesionpersonal, 
                      op_gravada, op_exonerada, op_inafecta, op_igv, op_total, unid_medida, id_unidad_medida
                    )
                    VALUES (
                      :id_encomienda, :id_ctg_encomienda, :descripcion, :cantidad, :precio, :precio_kg, :obs, :peso, :id_sesionpersonal, 
                      :op_gravada, :op_exonerada, :op_inafecta, :op_igv, :op_total, :unid_medida, :id_unidad_medida
                    )
                ");
                $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                $query->bindParam(':id_ctg_encomienda', $productos[0]);
                $query->bindParam(':descripcion', $productos[1]);
                $query->bindParam(':cantidad', $productos[2]);
                $query->bindParam(':precio', $productos[3]);
                $query->bindParam(':precio_kg', $productos[4]);
                $query->bindParam(':obs', $productos[5]);
                $query->bindParam(':unid_medida', $productos[6]);
                $query->bindParam(':peso', $productos[7]);
                $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                $query->bindParam(':op_gravada', $op_gravada);
                $query->bindParam(':op_exonerada', $op_exonerada);
                $query->bindParam(':op_inafecta', $op_inafecta);
                $query->bindParam(':op_igv', $igv);
                $query->bindParam(':op_total', $op_total);
                $query->bindParam(':id_unidad_medida', $productos[12]);

                $query->execute();
            };

            array_map($register, $data["productos"]);

            return [
                "success" => true,
                "total_igv" => $total_igv,
                "total_gravada" => $total_gravada,
                "total_inafecta" => $total_inafecta,
                "total_exonerada" => $total_exonerada
            ];
        } catch (PDOException $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
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

    public function permisos_modal()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT e.permiso_guia FROM empresa e");
            $query->execute();
            $permiso_guia = $query->fetch(PDO::FETCH_ASSOC)['permiso_guia'];

            $query = $this->db->connect()->prepare("SELECT t.c_selva FROM terminal t WHERE t.id_terminal=:id_terminal");
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->execute();
            $p_operacion = $query->fetchColumn();

            return [
                'success' => true,
                "message" => [
                    "permiso_guia" => $permiso_guia,
                    "c_selva" => $p_operacion
                ]
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "");
                    break;
                default:
                    return array('success' => false, "message" => "");
                    break;
            }
        }
    }


    // Entregar
    public function getEncomiendas($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                v.id_venta,
                v.id_terminal,
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
                e.id_encomienda,
                e.id_terminal_origen,
                e.id_terminal_destino,
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
                e.pago AS estado,
                v.envio_sunat,
                v.descrip_cdr_sunat,
                v.hash_cdr,
                v.fecha_emision,
                v.fecha_registro,
                v.op_gravada,
                v.op_exonerada,
                v.op_inafecta,
                v.total,
                p.fecha_salida AS programacion_fecha_salida,
                p.hora_salida AS programacion_hora_salida,
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                t_o.ubigeo AS ubigeo_origen,
                t_d.ubigeo AS ubigeo_destino,
                t_d.direccion_comercial AS direccion_destino,
                tp_s.descripcion AS tp_servicio,
                dt_v.salida
            FROM venta v
            LEFT JOIN usuario c_r ON c_r.id_usuario=v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
            LEFT JOIN usuario c_d ON c_d.id_usuario=e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
            LEFT JOIN terminal t_o ON t_o.id_terminal=e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=e.id_terminal_destino
            WHERE tp_s.descripcion='ENCOMIENDA' AND c_d.num_docu=:c_d_num_docu AND e.estado NOT IN ('EN ORIGEN','ENTREGADO', 'CANCELADO')
            ");
            $query->bindParam(':c_d_num_docu', $data["num_docuEntregar"]);
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

    public function getProductosEncomienda($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                p_e.id_producto_encomienda,
                p_e.id_encomienda,
                p_e.id_ctg_encomienda,
                ctg_e.descripcion AS ctg_encomienda,
                p_e.descripcion,
                p_e.cantidad,
                p_e.precio,
                p_e.rotulado,
                p_e.obs
            FROM producto_encomienda p_e
            LEFT JOIN ctg_encomienda ctg_e ON ctg_e.id_ctg_encomienda=p_e.id_ctg_encomienda
            WHERE id_encomienda=:id_encomienda
            ");
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
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

    public function entregarEncomienda($data)
    {
        try {
            $fecha_entrega = date("Y-m-d H:i:s");
            $pass = $this->security->encryption($data["codigo"]);
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $query = $conn->prepare("SELECT id_encomienda FROM encomienda WHERE id_encomienda=:id_encomienda AND pass=:pass");
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
            $query->bindParam(':pass', $pass);
            $query->execute();
            $reply = $query->fetch(PDO::FETCH_ASSOC);

            $reply_maestra = false;
            if (!$reply) {
                $query = $conn->prepare("
                SELECT id_clave_maestra FROM encomienda_clave_maestra 
                WHERE estado NOT IN('USADA', 'VENCIDA', 'ANULADA') AND clave = :pass
                ");
                $query->bindParam(':pass', $pass);
                $query->execute();
                $reply_maestra = $query->fetch(PDO::FETCH_ASSOC);
            }

            $query_movimiento = $conn->prepare("
             INSERT INTO encomienda_movimiento (
              id_encomienda,
              tipo_movimiento,
              id_terminal_evento,
              id_terminal_destino,
              observacion,
              id_usuario,
              fecha_registro
            )
            VALUES (
              :id_encomienda,
              'ENTREGA',
              :id_terminal_evento,
              NULL,
              :observacion,
              :id_usuario,
              NOW()
              )
            ");

            if ($reply || $reply_maestra) {
                $query = $conn->prepare("UPDATE encomienda SET estado ='ENTREGADO', fecha_entrega=:fecha_entrega, id_terminal_actual = 0 WHERE id_encomienda=:id_encomienda");
                $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                $query->bindParam(':fecha_entrega', $fecha_entrega);

                if ($query->execute()) {

                    $observacion = $reply_maestra
                        ? "Entrega realizada utilizando clave maestra."
                        : "Entrega realizada con código de seguridad.";

                    $query_movimiento->execute([
                        ':id_encomienda' => $data["id_encomienda"],
                        ':id_terminal_evento' => $this->id_terminal_sesion,
                        ':observacion' => $observacion,
                        ':id_usuario' => $this->id_usuario_sesion
                    ]);

                    $query = $conn->prepare("SELECT id_tp_comprobante FROM venta WHERE id_venta=:id_venta");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->execute();
                    $id_tp_comprobante = $query->fetch(PDO::FETCH_ASSOC)["id_tp_comprobante"];

                    switch ($id_tp_comprobante) {
                        case 2:
                            $tabla = "notaVenta";
                            break;
                        case 3:
                        case 1:
                            $tabla = "comprobante";
                            break;
                        case 31:
                            $tabla = "guiasTransportista";
                            break;
                        // Agregar al switch
                        default:
                            return ['success' => false, "message" => ["message" => "Tipo de comprobante no reconocido."]];
                    }

                    if ($reply_maestra) {
                        $query = $conn->prepare("
                             UPDATE encomienda_clave_maestra SET 
                             estado = 'USADA',
                             id_encomienda = :id_encomienda,
                             id_usuario_uso = :id_usuario_uso,
                             fecha_uso = :fecha_uso
                             WHERE id_clave_maestra = :id_clave_maestra
                        ");
                        $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                        $query->bindParam(':id_usuario_uso', $this->id_usuario_sesion);
                        $query->bindParam(':fecha_uso', $fecha_entrega);
                        $query->bindParam(':id_clave_maestra', $reply_maestra["id_clave_maestra"]);
                        $query->execute();
                    }
                    $conn->commit();

                    $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                        "tipo" => "encomienda_cambio",
                        "tabla" => $tabla,
                        "id" => $data["id_venta"],
                        "accion" => "update"
                    ]);

                    $link_comprobante = URL . "encomienda/impresion/" . ($id_tp_comprobante == 2 ? 'nota_venta/' : 'comprobante/') . $data["id_venta"];
                    return [
                        'success' => true,
                        "message" => [
                            "message" => "Encomienda entregado con éxito.",
                            "links" => [
                                ['nombre' => 'Imprimir', 'link' => $link_comprobante]
                            ]
                        ]
                    ];
                } else {
                    return ['success' => false, "message" => ["message" => "Error al entregar la encomienda."]];
                }
            } else {
                return ['success' => false, "message" => ["message" => "Código incorrecto."]];
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => ["message" => "Error en el servidor.", $e]];
                    break;
                default:
                    return ['success' => false, "message" => ["message" => "Error en el servidor.", $e]];
                    break;
            }
        }
    }

    public function pagarEncomienda($data)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $tipo_ruc = 2;
            } else {
                $tipo_ruc = 1;
            }
            $data_cliente_entregar = '';
            $afecta_detraccion = $data['tp_operacion_venta'] == 2 ? 1 : 0;
            $fecha_comprobante = date("Y-m-d H:i:s");
            //Verificando que el usuario asignado es apto para hacer una factura 
            if ($data["type_generarComprobante"] == "dataNueva") {
                $query = $this->db->connect()->prepare("SELECT id_tp_docu FROM usuario WHERE id_usuario = :id_usuario");
                $query->bindParam(':id_usuario', $data["remitenteEntregar"]);
                $query->execute();
                $data_cliente_entregar = $query->fetch(PDO::FETCH_ASSOC);
            } else if ($data["type_generarComprobante"] == "dataOrigen") {
                $query = $this->db->connect()->prepare("SELECT u.id_tp_docu 
                FROM encomienda e
                LEFT JOIN usuario u ON e.id_remitente = u.id_usuario
                WHERE e.id_encomienda = :id_encomienda");
                $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                $query->execute();
                $data_cliente_entregar = $query->fetch(PDO::FETCH_ASSOC);
            } else if ($data["type_generarComprobante"] == "dataDestino") {
                $query = $this->db->connect()->prepare("SELECT u.id_tp_docu, u.id_usuario
                FROM encomienda e
                LEFT JOIN usuario u ON e.id_destinatario = u.id_usuario
                WHERE e.id_encomienda = :id_encomienda");
                $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                $query->execute();
                $data_cliente_entregar = $query->fetch(PDO::FETCH_ASSOC);
            }

            if ($data['tp_comprobanteEntregar'] == '1' && $data_cliente_entregar['id_tp_docu'] != 6) {
                return [
                    'success' => false,
                    "message" => [
                        "message" => "No se puede generar una factura con un DNI, por favor corriga",
                        "links" => ''
                    ]
                ];
            }

            $data["serie_venta"] = $data["serie_ventaEntregar"];
            $fecha_entrega = date("Y-m-d H:i:s");
            $id_serie = $data["serie_ventaEntregar"];
            $correlativo = $this->get_correlativo($data);
            $messsage = "";

            // Extraccion de datos
            $query = $this->db->connect()->prepare("
                    SELECT * 
                    FROM venta 
                    WHERE id_venta = :id_venta");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->execute();
            $data_anterior = $query->fetch(PDO::FETCH_ASSOC);

            $data['medio_pagoEntregar'] = !empty($data['medio_pagoEntregar']) ? $data['medio_pagoEntregar'] : null;

            if ($data["habilitar_generarComprobante"] == "true") {
                if ($data["type_generarComprobante"] == "dataOrigen") {
                    $conn = $this->db->connect();
                    // Insercion de datos nuevos en la tabla
                    $query = $conn->prepare("INSERT INTO venta (
                    codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                    id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                    correlativo, descuento, op_igv, estado, envio_sunat,
                    descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                    op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, peso_encomienda, 
                    afecta_detraccion, id_tp_operacion, tipo_ruc
                    )
                    VALUES(
                    :codigo, :id_terminal, :id_vendedor, :id_cliente, :id_forma_pago, :id_medio_pago,
                    :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                    :correlativo, '', :igv,  'PAGADO', 0, 
                    '', '', '', '', '', :fecha_emision, :op_gravada,
                    :op_exonerada, :op_inafecta, :total, '','', '', :id_sesionpersonal, :peso_total,
                    :afecta_detraccion, :id_tp_operacion, :tipo_ruc
                    )");
                    $query->bindParam(':codigo', $data_anterior["codigo"]);
                    $query->bindParam(':id_terminal', $data_anterior["id_terminal"]);
                    $query->bindParam(':id_vendedor', $data_anterior['id_vendedor']);
                    $query->bindParam(':id_cliente', $data_anterior["id_cliente"]);
                    $query->bindParam(':id_forma_pago', $data["forma_pagoEntregar"]);
                    $query->bindParam(':id_medio_pago', $data["medio_pagoEntregar"]);
                    $query->bindParam(':id_tp_moneda', $data_anterior["id_tp_moneda"]);
                    $query->bindParam(':id_tp_comprobante', $data["tp_comprobanteEntregar"]);
                    $query->bindParam(':id_caja_chica', $data["destino_entregar"]);
                    $query->bindParam(':correlativo', $correlativo);
                    $query->bindParam(':id_serie', $data["serie_ventaEntregar"]);
                    $query->bindParam(':igv', $data_anterior['op_igv']);
                    $query->bindParam(':fecha_emision', $fecha_comprobante);
                    $query->bindParam(':op_gravada', $data_anterior['op_gravada']);
                    $query->bindParam(':op_exonerada', $data_anterior['op_exonerada']);
                    $query->bindParam(':op_inafecta', $data_anterior['op_inafecta']);
                    $query->bindParam(':total', $data_anterior["total"]);
                    $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->bindParam(':peso_total', $data_anterior['peso_total']);
                    $query->bindParam(':afecta_detraccion', $afecta_detraccion);
                    $query->bindParam(':id_tp_operacion', $data["tp_operacion_venta"]);
                    $query->bindParam(':tipo_ruc', $tipo_ruc);
                    $query->execute();

                    $id_comprobante_nuevo = $conn->lastInsertId();

                    // Actualizando la nota venta
                    $query = $this->db->connect()->prepare("
                        UPDATE venta SET
                            total = 0.00,
                            estado = 'PAGADO',
                            id_pago = :id_pago
                        WHERE id_venta=:id_venta");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_pago', $id_comprobante_nuevo);
                    $query->execute();

                    // Insertando detalle venta de la nota de venta al comprobante nuevo
                    $query = $this->db->connect()->prepare("
                        INSERT INTO dt_venta (
                            id_venta, id_tp_servicio, id_programacion, id_encomienda, precio, id_sesionpersonal
                        )
                        SELECT
                            $id_comprobante_nuevo AS id_venta,
                            id_tp_servicio,
                            id_programacion,
                            id_encomienda,
                            precio,
                            $this->id_usuario_sesion AS id_sesionpersonal
                        FROM dt_venta
                        WHERE id_venta=:id_venta AND id_encomienda=:id_encomienda");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                    $query->execute();

                    // Creando registro si en caso es credito 
                    if ($data["forma_pagoEntregar"] == 2) {
                        $numero_cuota = "001";
                        $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                        $query = $this->db->connect()->prepare("INSERT INTO cuota (
                         comprobante_id, numero, importe, fecha_vencimiento, estado)
                         VALUES(
                         :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                        ");
                        $query->bindParam(':comprobante_id', $id_comprobante_nuevo);
                        $query->bindParam(':numero', $numero_cuota);
                        $query->bindParam(':importe', $data['monto_creditoEntregar']);
                        $query->bindParam(':estado', $estado_cuota);
                        $query->bindParam(':fecha_vencimiento', $data['fecha_creditoEntregar']);
                        $query->execute();
                    }

                    if ($data['tp_operacion_venta'] == 2) {
                        $data['id_venta'] = $id_comprobante_nuevo;
                        $this->add_detraccion($data);
                    }
                    // Generando el código QR y facturación 
                    if (in_array($data["tp_comprobanteEntregar"], [1, 3])) {

                        $id_venta = $id_comprobante_nuevo;
                        $this->generar_codQR($id_venta);
                        $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($id_comprobante_nuevo)));
                        $resp = json_decode($rpta_sunat, true);
                        // Actualizando los datos de la venta
                        $resp[0]["id_venta"] = $id_comprobante_nuevo;
                        $this->updateVentaSetForSunat($resp[0]);
                    }
                    $messsage = "Encomienda pagado con éxito y comprobante nuevo.";
                } else if ($data["type_generarComprobante"] == "dataDestino") {
                    // Insercion de datos nuevos en la tabla
                    $query = $this->db->connect()->prepare("INSERT INTO venta (
                    codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                    id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                    correlativo, descuento, op_igv, estado, envio_sunat,
                    descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                    op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, peso_encomienda,
                    afecta_detraccion, id_tp_operacion, tipo_ruc
                    )
                    VALUES(
                    :codigo, :id_terminal, :id_vendedor, :id_cliente, :id_forma_pago, :id_medio_pago,
                    :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                    :correlativo, '', :igv,  'PAGADO', 0, 
                    '', '', '', '', '', :fecha_emision, :op_gravada,
                    :op_exonerada, :op_inafecta, :total, '','', '', :id_sesionpersonal, :peso_total,
                    :afecta_detraccion, :id_tp_operacion, :tipo_ruc
                    )");
                    $query->bindParam(':codigo', $data_anterior["codigo"]);
                    $query->bindParam(':id_terminal', $data_anterior["id_terminal"]);
                    $query->bindParam(':id_vendedor', $data_anterior['id_vendedor']);
                    $query->bindParam(':id_cliente', $data_cliente_entregar["id_usuario"]);
                    $query->bindParam(':id_forma_pago', $data["forma_pagoEntregar"]);
                    $query->bindParam(':id_medio_pago', $data["medio_pagoEntregar"]);
                    $query->bindParam(':id_tp_moneda', $data_anterior["id_tp_moneda"]);
                    $query->bindParam(':id_tp_comprobante', $data["tp_comprobanteEntregar"]);
                    $query->bindParam(':id_caja_chica', $data["destino_entregar"]);
                    $query->bindParam(':correlativo', $correlativo);
                    $query->bindParam(':id_serie', $data["serie_ventaEntregar"]);
                    $query->bindParam(':igv', $data_anterior['op_igv']);
                    $query->bindParam(':fecha_emision', $fecha_comprobante);
                    $query->bindParam(':op_gravada', $data_anterior['op_gravada']);
                    $query->bindParam(':op_exonerada', $data_anterior['op_exonerada']);
                    $query->bindParam(':op_inafecta', $data_anterior['op_inafecta']);
                    $query->bindParam(':total', $data_anterior["total"]);
                    $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->bindParam(':peso_total', $data_anterior['peso_total']);
                    $query->bindParam(':afecta_detraccion', $afecta_detraccion);
                    $query->bindParam(':id_tp_operacion', $data["tp_operacion_venta"]);
                    $query->bindParam(':tipo_ruc', $tipo_ruc);
                    $query->execute();

                    $id_comprobante_nuevo = $this->db->connect()->lastInsertId();

                    // Actualizando la nota venta
                    $query = $this->db->connect()->prepare("
                        UPDATE venta SET
                            total = 0.00,
                            estado = 'PAGADO',
                            id_pago = :id_pago
                        WHERE id_venta=:id_venta");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_pago', $id_comprobante_nuevo);
                    $query->execute();

                    // Insertando detalle venta de la nota de venta al comprobante nuevo
                    $query = $this->db->connect()->prepare("
                        INSERT INTO dt_venta (
                            id_venta, id_tp_servicio, id_programacion, id_encomienda, precio, id_sesionpersonal
                        )
                        SELECT
                            $id_comprobante_nuevo AS id_venta,
                            id_tp_servicio,
                            id_programacion,
                            id_encomienda,
                            precio,
                            $this->id_usuario_sesion AS id_sesionpersonal
                        FROM dt_venta
                        WHERE id_venta=:id_venta AND id_encomienda=:id_encomienda");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                    $query->execute();

                    if ($data["forma_pagoEntregar"] == 2) {
                        $numero_cuota = "001";
                        $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                        $query = $this->db->connect()->prepare("INSERT INTO cuota (
                         comprobante_id, numero, importe, fecha_vencimiento, estado)
                         VALUES(
                         :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                        ");
                        $query->bindParam(':comprobante_id', $id_comprobante_nuevo);
                        $query->bindParam(':numero', $numero_cuota);
                        $query->bindParam(':importe', $data['monto_creditoEntregar']);
                        $query->bindParam(':estado', $estado_cuota);
                        $query->bindParam(':fecha_vencimiento', $data['fecha_creditoEntregar']);
                        $query->execute();
                    }

                    if ($data['tp_operacion_venta'] == 2) {
                        $data['id_venta'] = $id_comprobante_nuevo;
                        $this->add_detraccion($data);
                    }
                    // Generando el código QR y facturación 
                    if (in_array($data["tp_comprobanteEntregar"], [1, 3])) {

                        $id_venta = $id_comprobante_nuevo;
                        $this->generar_codQR($id_venta);
                        $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($id_comprobante_nuevo)));
                        $resp = json_decode($rpta_sunat, true);
                        // Actualizando los datos de la venta
                        $resp[0]["id_venta"] = $id_comprobante_nuevo;
                        $this->updateVentaSetForSunat($resp[0]);
                    }
                    $messsage = "Encomienda pagado con éxito y comprobante nuevo.";
                } else if ($data["type_generarComprobante"] == "dataNueva") {
                    // Insercion de datos nuevos en la tabla
                    $query = $this->db->connect()->prepare("INSERT INTO venta (
                    codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                    id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                    correlativo, descuento, op_igv, estado, envio_sunat,
                    descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                    op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, peso_encomienda,
                    afecta_detraccion, id_tp_operacion, tipo_ruc
                    )
                    VALUES(
                    :codigo, :id_terminal, :id_vendedor, :id_cliente, :id_forma_pago, :id_medio_pago,
                    :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                    :correlativo, '', :igv,  'PAGADO', 0, 
                    '', '', '', '', '', :fecha_emision, :op_gravada,
                    :op_exonerada, :op_inafecta, :total, '','', '', :id_sesionpersonal, :peso_total,
                    :afecta_detraccion, :id_tp_operacion, :tipo_ruc
                    )");
                    $query->bindParam(':codigo', $data_anterior["codigo"]);
                    $query->bindParam(':id_terminal', $data_anterior["id_terminal"]);
                    $query->bindParam(':id_vendedor', $data_anterior['id_vendedor']);
                    $query->bindParam(':id_cliente', $data["remitenteEntregar"]);
                    $query->bindParam(':id_forma_pago', $data["forma_pagoEntregar"]);
                    $query->bindParam(':id_medio_pago', $data["medio_pagoEntregar"]);
                    $query->bindParam(':id_tp_moneda', $data_anterior["id_tp_moneda"]);
                    $query->bindParam(':id_tp_comprobante', $data["tp_comprobanteEntregar"]);
                    $query->bindParam(':id_caja_chica', $data["destino_entregar"]);
                    $query->bindParam(':correlativo', $correlativo);
                    $query->bindParam(':id_serie', $data["serie_ventaEntregar"]);
                    $query->bindParam(':igv', $data_anterior['op_igv']);
                    $query->bindParam(':fecha_emision', $fecha_comprobante);
                    $query->bindParam(':op_gravada', $data_anterior['op_gravada']);
                    $query->bindParam(':op_exonerada', $data_anterior['op_exonerada']);
                    $query->bindParam(':op_inafecta', $data_anterior['op_inafecta']);
                    $query->bindParam(':total', $data_anterior["total"]);
                    $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
                    $query->bindParam(':peso_total', $data_anterior['peso_total']);
                    $query->bindParam(':afecta_detraccion', $afecta_detraccion);
                    $query->bindParam(':id_tp_operacion', $data["tp_operacion_venta"]);
                    $query->bindParam(':tipo_ruc', $tipo_ruc);
                    $query->execute();

                    $id_comprobante_nuevo = $this->db->connect()->lastInsertId();

                    // Actualizando la nota venta
                    $query = $this->db->connect()->prepare("
                        UPDATE venta SET
                            total = 0.00,
                            estado = 'PAGADO',
                            id_pago = :id_pago
                        WHERE id_venta=:id_venta");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_pago', $id_comprobante_nuevo);
                    $query->execute();

                    // Insertando detalle venta de la nota de venta al comprobante nuevo
                    $query = $this->db->connect()->prepare("
                        INSERT INTO dt_venta (
                            id_venta, id_tp_servicio, id_programacion, id_encomienda, precio, id_sesionpersonal
                        )
                        SELECT
                            $id_comprobante_nuevo AS id_venta,
                            id_tp_servicio,
                            id_programacion,
                            id_encomienda,
                            precio,
                            $this->id_usuario_sesion AS id_sesionpersonal
                        FROM dt_venta
                        WHERE id_venta=:id_venta AND id_encomienda=:id_encomienda");
                    $query->bindParam(':id_venta', $data["id_venta"]);
                    $query->bindParam(':id_encomienda', $data["id_encomienda"]);
                    $query->execute();

                    if ($data["forma_pagoEntregar"] == 2) {
                        $numero_cuota = "001";
                        $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                        $query = $this->db->connect()->prepare("INSERT INTO cuota (
                         comprobante_id, numero, importe, fecha_vencimiento, estado)
                         VALUES(
                         :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                        ");
                        $query->bindParam(':comprobante_id', $id_comprobante_nuevo);
                        $query->bindParam(':numero', $numero_cuota);
                        $query->bindParam(':importe', $data['monto_creditoEntregar']);
                        $query->bindParam(':estado', $estado_cuota);
                        $query->bindParam(':fecha_vencimiento', $data['fecha_creditoEntregar']);
                        $query->execute();
                    }

                    if ($data['tp_operacion_venta'] == 2) {
                        $data['id_venta'] = $id_comprobante_nuevo;
                        $this->add_detraccion($data);
                    }

                    // Generando el código QR y facturación 
                    if (in_array($data["tp_comprobanteEntregar"], [1, 3])) {

                        $id_venta = $id_comprobante_nuevo;
                        $this->generar_codQR($id_venta);
                        $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($id_comprobante_nuevo)));
                        $resp = json_decode($rpta_sunat, true);
                        // Actualizando los datos de la venta
                        $resp[0]["id_venta"] = $id_comprobante_nuevo;
                        $this->updateVentaSetForSunat($resp[0]);
                    }
                    $messsage = "Encomienda pagado con éxito y comprobante con nuevo cliente.";
                }
            } else {
                $query = $this->db->connect()->prepare("
                UPDATE venta SET
                    estado ='PAGADO',
                    id_forma_pago=1,
                    id_medio_pago=:id_medio_pago,
                    id_caja_chica=:id_caja_chica,
                    obs=:obs
                WHERE id_venta=:id_venta");
                $query->bindParam(':id_venta', $data["id_venta"]);
                $query->bindParam(':id_medio_pago', $data["medio_pagoEntregar"]);
                $query->bindParam(':id_caja_chica', $data["destino_entregar"]);
                $query->bindParam(':obs', $data["obs_ventaEntregar"]);
                $query->execute();
                $messsage = "Encomienda pagado con éxito.";
            }
            $query = $this->db->connect()->prepare("UPDATE encomienda SET pago ='PAGADO' WHERE id_encomienda=:id_encomienda");
            $query->bindParam(':id_encomienda', $data["id_encomienda"]);
            $query->execute();

            $id_link_comprobante = $id_comprobante_nuevo ?? $data["id_venta"];

            $link_comprobante = URL . "encomienda/impresion/" . ($data["tp_comprobanteEntregar"] == 2 ? 'nota_venta/' : 'comprobante/') . $id_link_comprobante;
            return [
                'success' => true,
                "message" => [
                    "message" => $messsage,
                    "links" => [
                        ['nombre' => 'Imprimir', 'link' => $link_comprobante]
                    ]
                ]
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor.", $e));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor.", $e));
                    break;
            }
        }
    }

    // EMBARCAR
    public function get_dataTableEncomiendaEmbarcar($data)
    {
        try {
            // Obtener configuración de embarque general
            $queryConfig = $this->db->connect()->prepare("
            SELECT embarque_general 
            FROM empresa 
            WHERE id_empresa = :id_empresa
            ");
            $queryConfig->bindParam(':id_empresa', $this->id_empresa_sesion);
            $queryConfig->execute();
            $p_embarque_general = $queryConfig->fetchColumn();

            // Limpiar y preparar datos
            $estado = $data['estado_encomienda'] ?? '';
            $fecha_inicio = $data['fecha_inicio'] ?? '';
            $fecha_fin = $data['fecha_fin'] ?? date('Y-m-d');
            $terminalDestino = isset($data['id_terminalDestino']) ? (int) $data['id_terminalDestino'] : 0;

            // Procesar destinos
            $destinos_data = isset($data['destinos_programacion']) ? json_decode($data['destinos_programacion'], true) : [];
            $destinos_condicion = '';

            if (!empty($destinos_data)) {
                // Crear marcadores de posición para PDO
                $placeholders = [];
                for ($i = 0; $i < count($destinos_data); $i++) {
                    $placeholders[] = ":destino_" . $i;
                }
                $destinos_condicion = "e.id_terminal_destino IN (" . implode(',', $placeholders) . ")";
            } else if ($terminalDestino > 0) {
                $destinos_condicion = "e.id_terminal_destino = :terminalDestino";
            }

            // Construir WHERE dinámicamente
            $whereConditions = [
                "v.estado != 'ANULADO'",
                "(
                   (
                    e.estado = 'EN ORIGEN'
                    AND e.id_terminal_origen = :id_terminal_origen
                    )
                   OR
                    (
                     e.estado = 'MAL ENVIADO'
                     AND e.id_terminal_actual = :id_terminal_actual
                     )
                )"
            ];

            $bindParams = [
                ':id_terminal_origen' => $this->id_terminal_sesion,
                ':id_terminal_actual' => $this->id_terminal_sesion
            ];
            // Condición de fechas (RANGO O FECHA ÚNICA)
            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                // Si tenemos fecha inicio Y fecha fin => RANGO
                $whereConditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $bindParams[':fecha_inicio'] = $fecha_inicio;
                $bindParams[':fecha_fin'] = $fecha_fin;
            } else if (!empty($fecha_fin) && empty($fecha_inicio)) {
                // Si solo tenemos fecha fin (caso por defecto) => SOLO ESA FECHA
                $whereConditions[] = "DATE(v.fecha_emision) = :fecha_fin";
                $bindParams[':fecha_fin'] = $fecha_fin;
            }
            // Si $fecha_inicio está vacío y $fecha_fin también, no se aplica filtro de fecha

            // Condición de estado
            if (!empty($estado) && $estado != 'TODOS') {
                if ($estado == 'PAGADO') {
                    // Usar TRIM y comparación exacta
                    $whereConditions[] = "TRIM(e.pago) = 'PAGADO'";
                } elseif ($estado == 'POR_PAGAR') {
                    // Usar TRIM y UPPER para normalizar
                    $whereConditions[] = "UPPER(TRIM(e.pago)) IN ('PAGO EN DESTINO', 'PAGO EN BLOQUE', 'CREDITO')";
                } else {
                    $whereConditions[] = "TRIM(e.pago) = :estado_encomienda";
                    $bindParams[':estado_encomienda'] = $estado;
                }
            }

            // Condición de terminal destino
            if (!empty($destinos_condicion)) {
                $whereConditions[] = $destinos_condicion;

                if (!empty($destinos_data)) {
                    // Agregar destinos a los parámetros
                    foreach ($destinos_data as $index => $destino) {
                        $bindParams[":destino_" . $index] = $destino;
                    }
                } else if ($terminalDestino > 0) {
                    $bindParams[':terminalDestino'] = $terminalDestino;
                }
            }

            // Construir SQL
            $whereClause = implode(' AND ', $whereConditions);

            $sql = "
            SELECT
                e.id_encomienda,
                e.id_almacen,
                alm.descripcion AS almacen,
                CONCAT(v.serie, ' - ', v.correlativo) AS numero,
                DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') as fecha_emision_formateada,
                v.fecha_emision,
                e.codigo,
                e.fecha_salida,
                e.fecha_entrega,
                e.estado,
                e.id_remitente,
                e.id_destinatario,
                e.id_terminal_origen,
                e.id_terminal_destino,
                e.e_domicilio,
                e.pago AS estado_e,
                t_d.nombre AS terminal_destino,
                e.pago,
                v.id_venta,
                dt_v.id_dt_venta AS id_dt_venta,
                CASE 
                    WHEN v.id_tp_comprobante = 2 THEN 'notaVenta'
                    WHEN v.id_tp_comprobante IN (1, 3) THEN 'comprobante'
                    ELSE 'otro'
                END AS tabla
            FROM encomienda e
            LEFT JOIN dt_venta dt_v ON dt_v.id_encomienda = e.id_encomienda
            LEFT JOIN venta v ON dt_v.id_venta = v.id_venta
            LEFT JOIN almacen alm ON alm.id_almacen = e.id_almacen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            WHERE $whereClause
            ORDER BY v.fecha_emision DESC
            ";

            // DEBUG: Para verificar la consulta
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($bindParams, true));

            $query = $this->db->connect()->prepare($sql);

            // Bind de parámetros
            foreach ($bindParams as $key => $value) {
                $paramType = PDO::PARAM_STR;

                if (strpos($key, ':destino_') === 0 || $key === ':id_terminal_origen' || $key === ':terminalDestino') {
                    $paramType = PDO::PARAM_INT;
                }

                $query->bindValue($key, $value, $paramType);
            }

            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            // Obtener productos para cada encomienda
            if ($reply) {
                foreach ($reply as &$encomienda) {
                    $q2 = $this->db->connect()->prepare("
                    SELECT
                        p_e.descripcion AS producto,
                        p_e.precio AS producto_precio, 
                        p_e.rotulado AS producto_rotulado,
                        p_e.obs AS producto_obs
                    FROM producto_encomienda p_e 
                    WHERE p_e.id_encomienda = :id_encomienda
                ");
                    $q2->bindParam(":id_encomienda", $encomienda['id_encomienda'], PDO::PARAM_INT);
                    $q2->execute();
                    $encomienda['productos'] = $q2->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            return [
                'success' => true,
                'draw' => $data['draw'] ?? 1,
                'recordsTotal' => count($reply),
                'recordsFiltered' => count($reply),
                'data' => $reply
            ];
        } catch (PDOException $e) {
            error_log("Error en get_dataTableEncomiendaEmbarcar: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error en el servidor: " . $e->getMessage()
            ];
        }
    }

    public function get_terminalDestinoEmbarcar()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT    
                t.id_terminal,
                t.nombre,
                t.ubigeo
            FROM terminal t
            WHERE t.id_terminal!=:id_terminal");
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
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
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function get_programacionEmbarcar($data)
    {
        try {
            $fecha_actual = date("Y-m-d");

            $query = $this->db->connect()->prepare("
            SELECT DISTINCT
                p.id_programacion,
                p.id_vehiculo,
                v.placa,
                p.id_conductor,
                c.nombres AS conductor_nombres,
                c.apellidos AS conductor_apellidos,
                c.num_docu AS conductor_num_docu,
                p.fecha_salida, 
                p.hora_salida,
                p.id_tp_servicio_pasaje,
                p.id_terminal_destino,
                tps_psj.descripcion AS tp_servicio_pasaje
            FROM programacion p 
            LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
            LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
            LEFT JOIN tp_servicio_pasaje tps_psj ON tps_psj.id_tp_servicio_pasaje = p.id_tp_servicio_pasaje
            LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
            LEFT JOIN rutas_destino rd ON rd.id_programacion = p.id_programacion
            LEFT JOIN terminal t_rd ON t_rd.id_terminal = rd.id_terminal
            WHERE (
                p.id_terminal_destino = :id_terminal_destino 
                OR rd.id_terminal = :id_terminal_destino_ruta
            )
            AND p.estado = 1 
            AND p.fecha_salida >= :fecha_actual
            ORDER BY p.fecha_salida, p.hora_salida");
            $query->bindParam(':fecha_actual', $fecha_actual);
            $query->bindParam(':id_terminal_destino', $data["id_terminalDestino"]);
            $query->bindParam(':id_terminal_destino_ruta', $data["id_terminalDestino"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            $programaciones = [];

            foreach ($reply as $row) {
                $id = $row['id_programacion'];
                $row['tipo_destino'] = $row['id_terminal_destino'] == $data['id_terminalDestino'] ? "Destino Final" : "Ruta";

                if (!isset($programaciones[$id])) {
                    $programaciones[$id] = [
                        'id_programacion' => $row['id_programacion'],
                        'id_vehiculo' => $row['id_vehiculo'],
                        'placa' => $row['placa'],
                        'id_conductor' => $row['id_conductor'],
                        'conductor_nombres' => $row['conductor_nombres'],
                        'conductor_apellidos' => $row['conductor_apellidos'],
                        'conductor_num_docu' => $row['conductor_num_docu'],
                        'fecha_salida' => $row['fecha_salida'],
                        'hora_salida' => $this->convertTo12HourFormat($row['hora_salida']),
                        'id_terminal_destino' => $row['tipo_destino'] == "Ruta" ? $data["id_terminalDestino"] : $row['id_terminal_destino'],
                        'tp_servicio_pasaje' => $row['tp_servicio_pasaje'],
                        'tipo_destino' => $row['tipo_destino'],
                        'rutas' => []
                    ];
                }

                // Hacer un select adicional para obtener las rutas asociadas
                if ($row['tipo_destino'] == "Destino Final") {
                    $query = $this->db->connect()->prepare("
                    SELECT 
                        t.id_terminal AS id_terminal_destino,
                        t.nombre AS terminal_destino
                        FROM rutas_destino rd
                        LEFT JOIN terminal t ON t.id_terminal = rd.id_terminal
                        WHERE rd.id_programacion = :id_programacion");
                    $query->bindParam(':id_programacion', $id);
                    $query->execute();
                    $rutas = $query->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rutas as $ruta) {
                        $programaciones[$id]['rutas'][] = [
                            'id_terminal' => $ruta['id_terminal_destino'],
                            'nombre' => $ruta['terminal_destino']
                        ];
                    }
                }
            }

            // Convertir a array indexado
            $programaciones = array_values($programaciones);


            if ($programaciones) {
                return ['success' => true, "message" => $programaciones];
            } else {
                return ['success' => false, "message" => ''];
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en el servidor. " . ($e->getMessage() ?? '');
            return [
                'success' => false,
                'message' => ["message" => $errorMessage]
            ];
        }
    }

    private function convertTo12HourFormat($time24)
    {
        $time = strtotime($time24);
        return date("g:i A", $time);
    }

    public function crear_guia_embarque($data, $conn = null)
    {
        try {

            $id_embarque = $data["id_embarque"];

            $link_guia = '';
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            // Extraccion de datos de embarque como id_programacion y encomiendas
            $queryD = $conn->prepare("
            SELECT 
            id,
            id_programacion,
            destino AS id_destino,
            tipo
            FROM embarcaciones
            WHERE id =:id_embarque");
            $queryD->bindParam(':id_embarque', $id_embarque);
            $queryD->execute();
            $datos_g = $queryD->fetch(PDO::FETCH_ASSOC);
            // Extraccion de id_encomiendas de la tabla historial_embarcaciones
            $queryE = $conn->prepare("
            SELECT encomienda_id 
            FROM historial_embarcaciones 
            WHERE embarcacion_id = :id_embarque");
            $queryE->bindParam(':id_embarque', $id_embarque);
            $queryE->execute();
            // Extraer solo los valores de encomienda_id
            $id_encomiendas = array_column($queryE->fetchAll(PDO::FETCH_ASSOC), 'encomienda_id');

            if ($id_encomiendas == []) {
                return ['success' => false, "message" => 'El embarque esta vacio', "codigo" => 2];
            }
            $json_guia = $this->crear_guia_remision($id_encomiendas, $datos_g, $conn);
            if ($json_guia['success'] == false) {
                throw new Exception('No se ha creado correctamente el JSON a enviar (-.-) ' . $json_guia['error']);
            }
            $json_envio = json_encode($json_guia['json']);
            $resp = $this->enviar_json_a_api($json_envio);
            $r = json_decode($resp, true);
            $message = '';
            $success = false;
            $link_guia = '';

            switch ($r[0]['estado']) {
                case '1':
                    $json_guia['id_embarcacion'] = $id_embarque ?? null;
                    $insercion = $this->insert_guia($json_guia['json'], $r, $conn);
                    $generar_qr = $this->codQRguias($insercion['id'], $r[0]['link_guia'], $conn);
                    if (!$generar_qr['success']) {
                        throw new Exception('No se ha creado correctamente el qr' . $generar_qr['message']);
                    }
                    $link_guia = URL . "encomienda/impresion/guia_remision/" . $insercion['id'];
                    $this->insert_detalle_guia($json_guia['json'], $insercion, $conn);
                    $message = $r[0]['mensaje_sunat'];
                    $success = true;
                    $link_guia = [
                        ['nombre' => 'Guia de remision', 'link' => $link_guia]
                    ];
                    break;
                case '2':
                    $message = $r[0]['mensaje_sunat'];
                    $success = false;
                    $link_guia = [];
                    break;
                case '3':
                    $message = $r[0]['mensaje_sunat'];
                    $success = false;
                    $link_guia = [];
                    break;
            }

            return [
                'success' => $success,
                "message" => $message,
                "link_guia" => $link_guia
            ];
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Error en el servidor." . $e];
                    break;
                default:
                    return ['success' => false, "message" => "Error en el servidor." . $e];
                    break;
            }
        }
    }

    //Creacion de las guias de remision
    public function crear_guia_remision($id_encomiendas, $datosE, $conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        date_default_timezone_set('America/Lima');
        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i:s');
        $id_programacion = $datosE['id_programacion'];
        $ruc_encomienda = $this->get_ruc_encomienda_separado($conn);

        if ($ruc_encomienda == 1) {
            $emisor = $this->consult_emisor_encomienda($conn);
        } else {
            $emisor = $this->consult_emisor($conn);
        }
        $remitente = [];
        $destinatario = [];
        $pagante = [];
        $documentos_globales = [];

        try {
            //Obteniendo serie y correlativo 
            $tp_c = "31";
            $query = $conn->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $tp_c);
            $query->execute();
            $serie = $query->fetch(PDO::FETCH_ASSOC);

            if ($serie == null) {
                throw new Exception('No existe una serie para esta terminal para las guias de remision');
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

            // Crear un marcador de posición para cada ID de encomienda en la consulta
            $placeholders = implode(',', array_fill(0, count($id_encomiendas), '?'));

            // Primera consulta para encontrar los id_venta basados en id_encomiendas
            $query1 = $conn->prepare("SELECT id_venta FROM dt_venta WHERE id_encomienda IN ($placeholders)");
            $query1->execute($id_encomiendas);
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
            CONCAT(u_o.nombres, ' ', u_o.apellidos) AS usuario_orig,
            u_o.num_docu AS doc_orig,
            u_o.id_tp_docu AS tipodoc_orig,
            CONCAT(u_d.nombres, ' ', u_d.apellidos) AS usuario_dest,
            u_d.num_docu AS doc_dest,
            u_d.id_tp_docu AS tipodoc_dest
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

            if (!$d_programacion) {
                throw new Exception('No se encontró la programación con id: ' . $id_programacion);
            }


            if ($d_programacion['nombres'] == 'CONDUCTOR') {
                throw new Exception('Falta seleccionar conductor.');
            }
            // Datos de la persona que esta mandando
            if ($datosE['tipo'] == 'INDIVIDUAL') {
                $id_encom = $id_encomiendas[0];
                // Sacando data toda la data necesaria de encomienda
                $query = $conn->prepare("
                 SELECT * 
                 FROM encomienda 
                 WHERE id_encomienda IN ($id_encom)");
                $query->execute();
                $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

                $encomienda = $encomiendas[0];
                $id_encomienda = $encomienda['id_encomienda'];
                $id_remitente = $encomienda['id_remitente'];
                $id_destinatario = $encomienda['id_destinatario'];
                $id_pagador_flete = $encomienda['pagador_flete'];
                $origen_dir = $encomienda['dir_puntopartida'];
                $destino_dir = $encomienda['dir_puntollegada'];
                $ubi_origen = $encomienda['ubi_partida'];
                $ubi_destino = $encomienda['ubi_llegada'];

                // Agregar el unico comprobante de encomienda a docmuentos globales
                if ($encomienda['tp_comprobante_ref'] != '0') {
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

                // Extraer data de pagante a partir de la encomienda
                $query = $conn->prepare("
                SELECT 
                CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
                u.num_docu AS num_docu,
                u.id_tp_docu AS id_tp_docu
                FROM usuario u
                WHERE u.id_usuario = $id_pagador_flete");
                $query->execute();
                $data_pagante = $query->fetch(PDO::FETCH_ASSOC);

                // Armando la data de remitente
                $remitente['tipo_documento'] = $data_remitente['id_tp_docu'];
                $remitente['num_doc'] = $data_remitente['num_docu'];
                $remitente['razon_social'] = $data_remitente['nombres'];
                $remitente['usuario'] = $id_remitente;

                // Armando la data de destinatario
                $destinatario['tipo_documento'] = $data_destinatario['id_tp_docu'];
                $destinatario['num_doc'] = $data_destinatario['num_docu'];
                $destinatario['razon_social'] = $data_destinatario['nombres'];
                $cabecera['indicador_envio_SUNAT'] = "SUNAT_Envio_IndicadorPagadorFlete_Tercero";

                $pagante['tipo_documento'] = $data_pagante['id_tp_docu'];
                $pagante['numero_doc'] = $data_pagante['num_docu'];
                $pagante['razon_social'] = $data_pagante['nombres'];
                $pagante['id_pagador'] = $id_pagador_flete;

                $cabecera['observaciones'] = "";
            } else if ($datosE['tipo'] == 'GRUPAL') {

                $query = $conn->prepare("
                SELECT 
                t.ubigeo,
                t.direccion_comercial AS direccion
                FROM terminal t
                WHERE t.id_terminal = :id_terminal
                ");
                $query->bindParam(":id_terminal", $datosE['id_destino']);
                $query->execute();
                $destino = $query->fetch(PDO::FETCH_ASSOC);

                $queryG = $conn->prepare("
                SELECT
                CONCAT(u.apellidos, ' ', u.nombres) AS nombres,
                u.num_docu,
                u.id_usuario,
                u.id_tp_docu AS tipodoc
                FROM terminal t
                LEFT JOIN usuario u ON u.id_usuario = t.encargado
                WHERE t.id_terminal = :id_terminal
                ");
                $queryG->bindParam(":id_terminal", $this->id_terminal_sesion);
                $queryG->execute();
                $datos_encargado = $queryG->fetch(PDO::FETCH_ASSOC);

                //En caso se manejen solo el personal y la empresa solo transporta
                $remitente['tipo_documento'] = $datos_encargado['tipodoc'];
                $remitente['num_doc'] = $datos_encargado['num_docu'];
                $remitente['razon_social'] = $datos_encargado['nombres'];
                $remitente['usuario'] = $datos_encargado['id_usuario'];

                $destinatario['tipo_documento'] = $emisor['tipodoc'];
                $destinatario['num_doc'] = $emisor['ruc'];
                $destinatario['razon_social'] = $emisor['razon_social'];

                // Extrayendo data de encomiendas
                $ids = is_array($id_encomiendas) ? $id_encomiendas : explode(',', $id_encomiendas);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $queryE = $conn->prepare("
                SELECT guia_serie, guia_correlativo, guia_ruc, tp_comprobante_ref, serie_ref, correlativo_ref, ruc_ref
                FROM encomienda 
                WHERE id_encomienda IN ($placeholders)
                ");
                $queryE->execute($ids);
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
                $documentos_globales = [];
                $cabecera['indicador_envio_SUNAT'] = "";
            }


            $token = $this->ObtenerTokenAutenticacion($emisor, $conn);
            if (!$token['success']) {
                throw new Exception('No se pudo obtener el token -> ' . $token['message']);
            } else {
                $emisor['token'] = $token['token'];
            }

            $cabecera['tipo_comprobante'] = "31";
            $cabecera['serie'] = $serie['serie'];
            $cabecera['correlativo'] = $correlativo;
            $cabecera['fecha_emision'] = $fechaActual;
            $cabecera['hora_emision'] = $horaActual;
            $cabecera['fecha_envio'] = $fechaActual;
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
            $cabecera['partida_ubigeo'] = $datosE['tipo'] == "INDIVIDUAL" ? trim($ubi_origen) : trim($d_programacion['ubigeo_origen']);
            $cabecera['partida_direccion'] = $datosE['tipo'] == "INDIVIDUAL" ? trim($origen_dir) : trim($d_programacion['direccion_origen']);
            //Lugar de destino
            $cabecera['destino_ubigeo'] = $datosE['tipo'] == "INDIVIDUAL" ? trim($ubi_destino) : trim($destino['ubigeo']);
            $cabecera['destino_direccion'] = $datosE['tipo'] == "INDIVIDUAL" ? trim($destino_dir) : trim($destino['direccion']);

            //Apartado para conseguir productos de todas las encomiendas
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

            $placeholders_e = implode(',', array_map('intval', $id_encomiendas));
            $query4 = $conn->prepare("SELECT * 
            FROM producto_encomienda p_e
            WHERE p_e.id_encomienda IN ($placeholders_e)");
            $query4->execute();
            $items = $query4->fetchAll(PDO::FETCH_ASSOC);
            $productos = [];
            $sumas_por_di_ctg_endomienda = [];
            $i = 1;

            foreach ($items as &$item) {
                $id_ctg_encomienda = $item['id_ctg_encomienda'];

                // Agregamos los documentos adjuntos como globales
                if (!empty($item['comprobante'])) {
                    $documentos_globales[] = [
                        'tipo_documento' => $item['comprobante'],
                        'serie_numero' => $item['serie'] . '-' . $item['correlativo'],
                    ];
                }

                if (!empty($item['guia_serie'])) {
                    $documentos_globales[] = [
                        'tipo_documento' => '09',
                        'serie_numero' => $item['guia_serie'] . '-' . $item['guia_correlativo'],
                        'ruc_emisor' => $item['guia_ruc'],
                    ];
                }

                // Si es un nuevo producto, añadimos un nuevo elemento al array consolidado
                $iten = [];
                $iten['item'] = $i++;
                $iten['codigo'] = $codigos_por_id[$id_ctg_encomienda] ?? '';
                $iten['unidad'] = "NIU";
                $iten['cantidad'] = $item['cantidad'];
                $iten['id_producto'] = $id_ctg_encomienda;
                $nombre = $nombres_por_id[$id_ctg_encomienda];
                $iten['nombre'] = $nombre . ' ' . $item['obs'];
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
                "pagante" => $pagante,
                "cabecera" => $cabecera,
                "documentos_globales" => $documentos_globales,
                "items" => $productos,

            ];
            return ['success' => true, "json" => $json];
        } catch (Exception $e) {
            return ['success' => false, "error" => "Error al obtener los datos" . $e];
        }
    }

    public function embarcar_encomiendas($data)
    {
        $data['destinos'] = json_decode($data['destinos'], true);
        $id_programacion = $data['id_programacion'];
        $crear_guia = isset($data['crear_guia']) ? (int) $data['crear_guia'] : 0;
        $json_guia = '';
        $link_guia_remision = '';
        $tipo_embarque = '';
        $insercion = null;

        if (empty($data['destinos'])) {
            return ['success' => false, "message" => ["message" => "No hay destinos para embarcar."]];
        }

        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $query = $conn->prepare("
            INSERT INTO grupo_embarcaciones
            (fecha, id_usuario) VALUES(:fecha, :id_usuario)
            ");
            $fecha_actual = date('Y-m-d H:i:s');
            $query->bindParam(":fecha", $fecha_actual);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->execute();
            $id_grupo_embarcacion = $conn->lastInsertId();

            $query_embarcacion = $conn->prepare("
            INSERT INTO embarcaciones 
                (id_programacion, origen, destino, id_usuario, tipo, id_grupo_embarcacion)
            VALUES 
                (:id_programacion, :origen, :destino, :id_usuario, :tipo, :id_grupo_embarcacion)
            ");

            $query_historial = $conn->prepare("
            INSERT INTO historial_embarcaciones 
                (encomienda_id, embarcacion_id)
            VALUES 
                (:encomienda_id, :embarcacion_id)
            ");

            $query_update_venta = $conn->prepare("
            UPDATE dt_venta SET id_programacion=:id_programacion WHERE id_dt_venta = :id_dt_venta
            ");

            $query_update_estado = $conn->prepare("
            UPDATE encomienda SET estado='EN TRANSITO' , id_terminal_actual = 0 WHERE id_encomienda = :id_encomienda
            ");

            $observacion = 'Reenvío desde terminal incorrecto hacia destino correcto';

            $query_movimiento_embarque = $conn->prepare("
INSERT INTO encomienda_movimiento (
    id_encomienda,
    tipo_movimiento,
    id_terminal_evento,
    id_terminal_destino,
    observacion,
    id_usuario,
    fecha_registro
)
VALUES (
    :id_encomienda,
    'SALIDA',
    :id_terminal_evento,
    :id_terminal_destino,
    :observacion,
    :id_usuario,
    NOW()
)
");

            $query_movimiento_reenvio = $conn->prepare("
INSERT INTO encomienda_movimiento (
    id_encomienda,
    tipo_movimiento,
    id_terminal_evento,
    id_terminal_destino,
    observacion,
    id_usuario,
    fecha_registro
)
VALUES (
    :id_encomienda,
    'REENVIO',
    :id_terminal_evento,
    :id_terminal_destino,
    :observacion,
    :id_usuario,
    NOW()
)
");

            $ids_venta_afectados = [];
            foreach ($data['destinos'] as $id_destino => $encomiendas) {

                $id_encomiendas_array = array_column($encomiendas, 'id_encomienda');
                $id_encomiendas_str = implode(',', $id_encomiendas_array);
                $domicilios = array_column($encomiendas, 'e_domicilio');
                $es_individual = (
                    count($id_encomiendas_array) === 1 &&
                    $domicilios[0] == 1
                );

                $tipo_embarque = $es_individual ? 'INDIVIDUAL' : 'GRUPAL';

                $query_embarcacion->bindParam(":id_programacion", $data["id_programacion"]);
                $query_embarcacion->bindParam(":origen", $this->id_terminal_sesion);
                $query_embarcacion->bindParam(":destino", $id_destino);
                $query_embarcacion->bindParam(":id_usuario", $this->id_usuario_sesion);
                $query_embarcacion->bindParam(":tipo", $tipo_embarque);
                $query_embarcacion->bindParam(":id_grupo_embarcacion", $id_grupo_embarcacion);
                $query_embarcacion->execute();

                $id_embarcacion = $conn->lastInsertId();

                if ($crear_guia === 1) {

                    if ($es_individual) {
                        $json_guia = $this->crear_guia_individual(
                            $id_encomiendas_array[0],
                            $id_programacion,
                            $conn
                        );
                    } else {
                        $json_guia = $this->crear_guia(
                            $id_encomiendas_str,
                            $id_programacion,
                            $id_destino,
                            $conn
                        );
                    }

                    if ($json_guia['success'] === false) {
                        throw new Exception("Error al crear guia: " . $json_guia['message']);
                    }

                    $json_envio = json_encode($json_guia['json']);
                    $resp = $this->enviar_json_a_api($json_envio);
                    $r = json_decode($resp, true);

                    if (empty($r) || !isset($r[0]['link_guia'])) {
                        throw new Exception("Respuesta inválida de la API al enviar guía.");
                    }

                    $json_guia['json']['id_grupo_embarque'] = $id_grupo_embarcacion;
                    $json_guia['json']['id_embarcacion'] = $id_embarcacion;
                    $insercion = $this->insert_guia($json_guia['json'], $r, $conn);
                    if ($insercion['success'] === false) {
                        throw new Exception("Error al insertar guia en la base de datos: " . $insercion['message']);
                    }

                    $codigo_qr = $this->codQRguias($insercion['id'], $r[0]['link_guia'], $conn);
                    if ($codigo_qr['success'] === false) {
                        throw new Exception("Error al generar codigo QR: " . $codigo_qr['message']);
                    }

                    $insert_detalle_guia = $this->insert_detalle_guia($json_guia['json'], $insercion, $conn);
                    if ($insert_detalle_guia['success'] === false) {
                        throw new Exception("Error al insertar detalle de guia: " . $insert_detalle_guia['message']);
                    }
                }

                foreach ($encomiendas as $item) {
                    $query_historial->bindParam(":encomienda_id", $item['id_encomienda']);
                    $query_historial->bindParam(":embarcacion_id", $id_embarcacion);
                    $query_historial->execute();

                    $query_update_venta->bindParam(":id_programacion", $data["id_programacion"]);
                    $query_update_venta->bindParam(":id_dt_venta", $item['id_dt_venta']);
                    $query_update_venta->execute();

                    $query_update_estado->bindParam(":id_encomienda", $item['id_encomienda']);
                    $query_update_estado->execute();

                    if (isset($item['estado']) && $item['estado'] === 'MAL ENVIADO') {

                        $query_movimiento_reenvio->execute([
                            ':id_encomienda' => $item['id_encomienda'],
                            ':id_terminal_evento' => $this->id_terminal_sesion,
                            ':id_terminal_destino' => $id_destino,
                            ':observacion' => 'Reenvío desde terminal incorrecto hacia destino correcto.',
                            ':id_usuario' => $this->id_usuario_sesion
                        ]);
                    } else {

                        $query_movimiento_embarque->execute([
                            ':id_encomienda' => $item['id_encomienda'],
                            ':id_terminal_evento' => $this->id_terminal_sesion,
                            ':id_terminal_destino' => $id_destino,
                            ':observacion' => 'Encomienda embarcada.',
                            ':id_usuario' => $this->id_usuario_sesion
                        ]);
                    }

                    if (!empty($item['id_venta']) && !empty($item['tabla'])) {
                        $key = $item['tabla'] . '_' . $item['id_venta'];
                        $ids_venta_afectados[$key] = [
                            'id' => $item['id_venta'],
                            'tabla' => $item['tabla']
                        ];
                    }
                }
            }

            $conn->commit();
            foreach ($ids_venta_afectados as $item) {
                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                    "tipo" => "encomienda_cambio",
                    "tabla" => $item['tabla'],
                    "id" => $item['id'],
                    "accion" => "update"
                ]);
            }

            $link_reporte = URL . "encomienda/impresion/embarque_grupal/" . $data["id_programacion"] . "/" . $id_grupo_embarcacion;
            $link_guia_remision = [
                'nombre' => 'Guia Remision',
                'link' => isset($insercion['id']) ? URL . "encomienda/impresion/guia_grupal/" . $id_grupo_embarcacion : ''
            ];

            return [
                'success' => true,
                "message" => [
                    "message" => "Encomiendas embarcados con éxito",
                    "links" => [
                        ['nombre' => 'Embarque', 'link' => $link_reporte],
                        $link_guia_remision
                    ]
                ]
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            return [
                'success' => false,
                "message" => ["message" => "Error en el servidor: " . $e->getMessage()]
            ];
        }
    }

    public function embarcar_encomienda($data, $conn = null)
    {
        $id_programacion = $data['e_salida'] == '1' ? $data['salida'] : $data['programacion_guia_t'];
        $id_encomienda = $data['id_encomienda'];
        $id_destino = $data['destino_terminal'];
        $json_guia = '';
        $link_guia_remision = '';
        $tipo_embarque = 'INDIVIDUAL';
        $e_domicilio = $data['e_domicilio'] ?? 0;
        $e_salida = $data['e_salida'] ?? 0;

        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
                INSERT INTO grupo_embarcaciones
                (fecha, id_usuario) VALUES(:fecha, :id_usuario)
            ");
            $fecha_actual = date('Y-m-d H:i:s');
            $query->bindParam(":fecha", $fecha_actual);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->execute();
            $id_grupo_embarcacion = $conn->lastInsertId();

            if ($e_salida == 1) {
                $query_embarcacion = $conn->prepare("
                INSERT INTO embarcaciones 
                  (id_programacion_salida, id_usuario, tipo, id_grupo_embarcacion, tipo_programacion)
                VALUES 
                   (:id_programacion_salida, :id_usuario, 'INDIVIDUAL', :id_grupo_embarcacion, 'salida')
                ");
                $query_embarcacion->bindParam(":id_programacion_salida", $data["salida"]);
                $query_embarcacion->bindParam(":id_usuario", $this->id_usuario_sesion);
                $query_embarcacion->bindParam(":id_grupo_embarcacion", $id_grupo_embarcacion);
                $query_embarcacion->execute();
            } else {
                $query = $conn->prepare("
                INSERT INTO embarcaciones 
                    (id_programacion, origen, destino, id_usuario, tipo, id_grupo_embarcacion)
                VALUES 
                    (:id_programacion, :origen, :destino, :id_usuario, :tipo, :id_grupo_embarcacion)
                ");

                $query->bindParam(":id_programacion", $id_programacion);
                $query->bindParam(":origen", $this->id_terminal_sesion);
                $query->bindParam(":destino", $id_destino);
                $query->bindParam(":id_usuario", $this->id_usuario_sesion);
                $query->bindParam(":tipo", $tipo_embarque);
                $query->bindParam(":id_grupo_embarcacion", $id_grupo_embarcacion);
                $query->execute();
            }

            $id_embarcacion = $conn->lastInsertId();

            $json_guia = $this->GRT_individual(
                $id_encomienda,
                $id_programacion,
                $e_domicilio,
                $e_salida,
                $conn
            );

            if ($json_guia['success'] === false) {
                throw new Exception($json_guia['message']);
            }

            // Envío a SUNAT / API
            $json_envio = json_encode($json_guia['json']);
            $resp = $this->enviar_json_a_api($json_envio);
            $r = json_decode($resp, true);

            $json_guia['json']['id_grupo_embarque'] = $id_grupo_embarcacion;
            $json_guia['json']['id_venta_r'] = $e_salida == 1 ? null : $data['id_venta'];
            $json_guia['json']['id_embarcacion'] = $id_embarcacion;
            $insercion = $this->insert_guia($json_guia['json'], $r, $conn);
            if ($insercion['success'] === false) {
                throw new Exception($insercion['message']);
            }
            $codQR = $this->codQRguias($insercion['id'], $r[0]['link_guia'], $conn);
            if ($codQR['success'] === false) {
                throw new Exception($codQR['message']);
            }
            $inser_detalle = $this->insert_detalle_guia($json_guia['json'], $insercion, $conn);
            if ($inser_detalle['success'] === false) {
                throw new Exception($inser_detalle['message']);
            }

            $query = $conn->prepare("
                    INSERT INTO historial_embarcaciones 
                         (encomienda_id, embarcacion_id)
                    VALUES 
                       (:encomienda_id, :embarcacion_id)
                    ");

            $query->bindParam(":encomienda_id", $id_encomienda);
            $query->bindParam(":embarcacion_id", $id_embarcacion);
            $query->execute();

            // Datos necesarios para las inserciones
            $query = $conn->prepare("
                SELECT 
                dt_v.id_dt_venta,
                dt_v.id_venta,
                dt_v.id_encomienda
                FROM venta v 
                LEFT JOIN dt_venta dt_v ON v.id_venta = dt_v.id_venta 
                WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $data['id_venta']);
            $query->execute();
            $datos = $query->fetch(PDO::FETCH_ASSOC);

            if ($e_salida == '1') {
                $query = $conn->prepare("
                    UPDATE encomienda SET estado='EN TRANSITO', id_terminal_actual = 0 WHERE id_encomienda = :id_encomiendas");
                $query->bindParam(":id_encomiendas", $datos['id_encomienda']);
                $query->execute();
            } else {
                $query = $conn->prepare("
                    UPDATE dt_venta SET id_programacion=:id_programacion WHERE id_dt_venta = :id_dt_venta");
                $query->bindParam(":id_programacion", $id_programacion);
                $query->bindParam(":id_dt_venta", $datos['id_dt_venta']);
                $query->execute();

                // Actualizando el estado de las encomiendas por IDs
                $query = $conn->prepare("
                    UPDATE encomienda SET estado='EN TRANSITO', id_terminal_actual = 0 WHERE id_encomienda = :id_encomiendas");
                $query->bindParam(":id_encomiendas", $datos['id_encomienda']);
                $query->execute();
            }

            /*================================= No se activa por el momento =================================== */
            // $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
            //     "tipo" => "encomienda_cambio",
            //     "tabla" => "guiasTransportista",
            //     "id" => $data['id_venta'],
            //     "accion" => "update"
            // ]);

            $link_reporte = $e_salida == 1 ? URL . "encomienda/impresion/embarque_ruta/" . $id_embarcacion : URL . "encomienda/impresion/embarque/" . $id_programacion . "/" . $id_embarcacion;
            $link_guia_remision = URL . "encomienda/impresion/guia_remision/" . $insercion['id'];
            $link_guia_remision_a4 = URL . "encomienda/impresion/guia_remisionA4/" . $insercion['id'];
            return [
                'success' => true,
                "message" => [
                    "message" => "Productos embarcados con éxito",
                    "links" => [
                        [
                            'nombre' => 'Embarque',
                            'link' => $link_reporte
                        ],
                        [
                            'nombre' => 'Guia Remision',
                            'link' => $link_guia_remision
                        ],
                        [
                            'nombre' => 'Guia A4',
                            'link' => $link_guia_remision_a4
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => ["message" => "Error en el servidor."], $e];
                    break;
                default:
                    return ['success' => false, "message" => ["message" => "Error en el servidor."], $e];
                    break;
            }
        }
    }

    public function getDataReporteEmbarque($id_programacion, $id_terminal_destino)
    {
        try {
            // Validar parámetros de entrada
            if (empty($id_programacion) || !is_numeric($id_programacion)) {
                error_log("ID de programación inválido: $id_programacion");
                return [
                    'success' => false,
                    'message' => ['message' => 'ID de programación no válido'],
                    'HEADER' => [],
                    'TERMINALES' => [],
                    'BODY' => [],
                    'EMPRESA' => [],
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => []
                ];
            }
            if (empty($id_terminal_destino) || !is_numeric($id_terminal_destino)) {
                error_log("ID de terminal de destino inválido: $id_terminal_destino");
                return [
                    'success' => false,
                    'message' => ['message' => 'ID de terminal de destino no válido'],
                    'HEADER' => [],
                    'TERMINALES' => [],
                    'BODY' => [],
                    'EMPRESA' => [],
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => []
                ];
            }

            error_log("getDataReporteEmbarque - id_programacion: $id_programacion, id_terminal_destino: $id_terminal_destino");

            // Consulta de datos de la empresa (sin cambios)
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }
            if (empty($empresa) || !isset($empresa['id_empresa'])) {
                error_log("Datos de empresa no encontrados, usando valores por defecto");
                $empresa = [
                    'id_empresa' => $this->id_empresa_sesion ?? 0,
                    'nombre' => 'Empresa no especificada',
                    'ruc' => 'No especificado',
                    'direccion' => 'No especificada'
                ];
            }
            error_log("Empresa: " . json_encode($empresa));

            // Consulta de tipos de comprobantes (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
                FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);
            $tipo_comprobante_map = array_column($tipos_comprobante, 'descripcion', 'id_tp_comprobante');
            $tipo_comprobante_map[0] = 'NO ESPECIFICADO';
            error_log("Tipos de comprobante: " . json_encode($tipo_comprobante_map));

            // Consulta de terminales (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular, 
                       COALESCE(CONCAT(u.distri, ' - ', u.provi, ' - ', u.depa), 'No especificado') AS ubigeo
                FROM terminal t
                LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
                LEFT JOIN ubigeo u ON t.ubigeo = u.cod_ubigeo
                WHERE e.id_empresa = :id_empresa
            ");
            $id_empresa = $empresa['id_empresa'] ?? $this->id_empresa_sesion ?? 0;
            $query->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
            if (empty($terminales)) {
                error_log("No se encontraron terminales para la empresa id: $id_empresa");
                $terminales = [];
            }
            error_log("Terminales encontradas: " . json_encode($terminales));

            // Consulta de programación (MODIFICADA para incluir nombres de terminales)
            $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.id_programacion,
                    dt_v.id_encomienda,
                    p.id_terminal_origen,
                    p.id_terminal_destino,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p.id_vehiculo,
                    p.id_conductor,
                    p.fecha_salida,
                    p.hora_salida,
                    p.id_tp_servicio_pasaje,
                    p.estado,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    c.num_docu AS conductor_num_docu,
                    dt_c.licencia AS conductor_licencia,
                    v.placa AS vehiculo_placa,
                    v.num_poliza AS vehiculo_poliza
                FROM dt_venta dt_v
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
                LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = c.id_usuario
                LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
                WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$programacion) {
                error_log("No se encontró la programación con ID: $id_programacion");
                return [
                    'success' => false,
                    'message' => ['message' => "No se encontró la programación con ID: $id_programacion"],
                    'HEADER' => [],
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
                ];
            }
            error_log("Programación encontrada: " . json_encode($programacion));

            // Consulta de encomiendas (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT
                    COALESCE(p.fecha_salida, '0000-00-00') AS fecha_salida,
                    v.fecha_emision AS fecha_emision,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta AS id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p_e.descripcion AS detalle,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    COALESCE(TRIM(UPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS id_tp_comprobante,
                    COALESCE(TRIM(UPPER(tp_c.descripcion)), 'NO ESPECIFICADO') AS tipo_comprobante,
                    
                    -- NUEVAS COLUMNAS de encomienda
                    e.tp_comprobante_ref,
                    e.serie_ref,
                    e.correlativo_ref,
                    e.ruc_ref,
                    e.guia_serie,
                    e.guia_correlativo,
                    e.guia_ruc,
        
                    -- NUEVAS COLUMNAS de doc_relacionado
                    dr.tp_comprobante AS doc_rel_tp_comprobante,
                    dr.serie AS doc_rel_serie,
                    dr.correlativo AS doc_rel_correlativo,
                    dr.ruc AS doc_rel_ruc,

                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
                LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
                LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
                LEFT JOIN doc_relacionado dr ON dr.id_guia_remision = e.id_encomienda
                WHERE dt_v.id_programacion = :id_programacion 
                AND (e.id_terminal_destino = :id_terminal_destino OR e.id_terminal_destino IS NULL)
            ");
            $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
            $query->bindParam(':id_terminal_destino', $id_terminal_destino, PDO::PARAM_INT);
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);
            error_log("Encomiendas obtenidas (count: " . count($encomiendas) . "): " . json_encode($encomiendas));

            if (empty($encomiendas)) {
                error_log("No se encontraron encomiendas para id_programacion: $id_programacion, id_terminal_destino: $id_terminal_destino");
                return [
                    'success' => true,
                    'message' => ['message' => 'No se encontraron encomiendas'],
                    'HEADER' => $programacion,
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
                ];
            }

            // Consulta para el resumen de medios de pago (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COUNT(DISTINCT e.id_encomienda) AS cantidad,
                    SUM(v.total) AS total
                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                WHERE dt_v.id_programacion = :id_programacion 
                AND (e.id_terminal_destino = :id_terminal_destino OR e.id_terminal_destino IS NULL)
                GROUP BY m_p.id_medio_pago, m_p.descripcion
            ");
            $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
            $query->bindParam(':id_terminal_destino', $id_terminal_destino, PDO::PARAM_INT);
            $query->execute();
            $resumen_medios_pago = $query->fetchAll(PDO::FETCH_ASSOC);
            error_log("Resumen medios de pago (count: " . count($resumen_medios_pago) . "): " . json_encode($resumen_medios_pago));

            return [
                'success' => true,
                'message' => ['message' => 'Datos obtenidos correctamente'],
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'RESUMEN_MEDIOS_PAGO' => $resumen_medios_pago,
                'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
            ];
        } catch (PDOException $e) {
            error_log("Error en getDataReporteEmbarque (id_programacion: $id_programacion, id_terminal_destino: $id_terminal_destino): " . $e->getMessage());
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ],
                'HEADER' => [],
                'TERMINALES' => [],
                'BODY' => [],
                'EMPRESA' => [],
                'RESUMEN_MEDIOS_PAGO' => [],
                'TIPO_COMPROBANTE_MAP' => []
            ];
        }
    }

    public function getDataReporteEmbarqueH($data)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }

            // Obtener tipos de comprobantes (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
                FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);
            $tipo_comprobante_map = array_column($tipos_comprobante, 'descripcion', 'id_tp_comprobante');
            $tipo_comprobante_map[0] = 'NO ESPECIFICADO';

            // Obtener información de terminales (sin cambios)
            $terminales = $this->consult_terminales(8);
            if (empty($terminales)) {
                return ['success' => false, 'message' => ['message' => 'No se encontraron terminales para la empresa']];
            }

            // Obtener datos de la embarcación (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT e.*
                FROM embarcaciones e
                WHERE e.id = :id_embarcacion
            ");
            $query->bindParam(':id_embarcacion', $data);
            $query->execute();
            $embarcacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$embarcacion) {
                return ['success' => false, 'message' => ['message' => "No se encontró la embarcación con ID: $data"]];
            }

            // Obtener datos de la programación (MODIFICADA para incluir nombres de terminales)
            $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.id_programacion,
                    dt_v.id_encomienda,
                    p.id_terminal_origen,
                    p.id_terminal_destino,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p.id_vehiculo,
                    p.id_conductor,
                    p.fecha_salida,
                    p.hora_salida,
                    p.id_tp_servicio_pasaje,
                    p.estado,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    c.num_docu AS conductor_num_docu,
                    dt_c.licencia AS conductor_licencia,
                    v.placa AS vehiculo_placa,
                    v.num_poliza AS vehiculo_poliza
                FROM dt_venta dt_v
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
                LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = c.id_usuario
                LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
                WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $embarcacion['id_programacion']);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$programacion) {
                return ['success' => false, 'message' => ['message' => "No se encontró la programación con ID: {$embarcacion['id_programacion']}"]];
            }

            // Obtener historial de encomiendas en la embarcación (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT encomienda_id 
                FROM historial_embarcaciones
                WHERE embarcacion_id = :id_embarcacion
            ");
            $query->bindParam(':id_embarcacion', $data);
            $query->execute();
            $historial_enco = $query->fetchAll(PDO::FETCH_COLUMN);
            if (empty($historial_enco)) {
                return [
                    'success' => true,
                    'message' => ['message' => 'No se encontraron encomiendas'],
                    'HEADER' => $programacion,
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map,
                    'EMBARQUE' => $embarcacion
                ];
            }

            // Obtener encomiendas basadas en el historial de embarcaciones (sin cambios)
            $placeholders = rtrim(str_repeat('?,', count($historial_enco)), ',');

            $sql_encomiendas = "
                SELECT
                    p.fecha_salida AS fecha_salida,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta AS id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p_e.descripcion AS encomienda_producto,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    p_e.serie,
                    p_e.correlativo,
                    p_e.guia_serie,
                    p_e.guia_correlativo,
                    COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS tp_comprobante,

                    -- NUEVAS COLUMNAS de encomienda
                    e.tp_comprobante_ref,
                    e.serie_ref,
                    e.correlativo_ref,
                    e.ruc_ref,
                    e.guia_serie,
                    e.guia_correlativo,
                    e.guia_ruc,
        
                    -- NUEVAS COLUMNAS de doc_relacionado
                    dr.tp_comprobante AS doc_rel_tp_comprobante,
                    dr.serie AS doc_rel_serie,
                    dr.correlativo AS doc_rel_correlativo,
                    dr.ruc AS doc_rel_ruc

                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
                LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
                LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN doc_relacionado dr ON dr.id_guia_remision = e.id_encomienda
                WHERE dt_v.id_programacion = ? 
                AND e.id_encomienda IN ($placeholders)
            ";

            $query = $this->db->connect()->prepare($sql_encomiendas);
            $query->bindValue(1, $embarcacion['id_programacion'], PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            // Consulta para el resumen de medios de pago (sin cambios)
            $sql_resumen = "
                SELECT
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COUNT(DISTINCT e.id_encomienda) AS cantidad,
                    SUM(v.total) AS total
                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                WHERE dt_v.id_programacion = ? 
                AND e.id_encomienda IN ($placeholders)
                GROUP BY m_p.id_medio_pago, m_p.descripcion
            ";

            $query = $this->db->connect()->prepare($sql_resumen);
            $query->bindValue(1, $embarcacion['id_programacion'], PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $resumen_medios_pago = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => ['message' => 'Datos obtenidos correctamente'],
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'RESUMEN_MEDIOS_PAGO' => $resumen_medios_pago,
                'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map,
                'EMBARQUE' => $embarcacion
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ];
        }
    }

    public function getDataReporteEmbarqueRuta($id_embarcacion)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }
            $conn = $this->db->connect();

            $query = $conn->prepare('
            SELECT 
            id_programacion_salida
            FROM embarcaciones
            WHERE id = :id_embarcacion
            ');
            $query->bindParam(':id_embarcacion', $id_embarcacion, PDO::PARAM_INT);
            $query->execute();
            $id_programacion_salida = $query->fetchColumn();

            // Obtener tipos de comprobantes (sin cambios)
            $query = $conn->prepare("
                SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
                FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);
            $tipo_comprobante_map = array_column($tipos_comprobante, 'descripcion', 'id_tp_comprobante');
            $tipo_comprobante_map[0] = 'NO ESPECIFICADO';

            // Obtener información de terminales (sin cambios)
            $terminales = $this->consult_terminales(8);
            if (empty($terminales)) {
                return ['success' => false, 'message' => ['message' => 'No se encontraron terminales para la empresa']];
            }

            // Obtener datos de la embarcación (sin cambios)
            $query = $conn->prepare("
                SELECT e.*
                FROM embarcaciones e
                WHERE e.id = :id_embarcacion
            ");
            $query->bindParam(':id_embarcacion', $id_embarcacion, PDO::PARAM_INT);
            $query->execute();
            $embarcacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$embarcacion) {
                return ['success' => false, 'message' => ['message' => "No se encontró la embarcación con ID: $id_embarcacion"]];
            }

            // Obtener datos de la programación (MODIFICADA para incluir nombres de terminales)
            $query = $conn->prepare("
                SELECT
                    dt_v.id_programacion,
                    dt_v.id_encomienda,
                    u_o.distri AS terminal_origen,
                    u_d.distri AS terminal_destino,
                    p.id_vehiculo,
                    p.id_conductor,
                    p.fecha_salida,
                    p.hora_salida,
                    p.estado,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    c.num_docu AS conductor_num_docu,
                    dt_c.licencia AS conductor_licencia,
                    v.placa AS vehiculo_placa,
                    v.num_poliza AS vehiculo_poliza
                FROM dt_venta dt_v
                LEFT JOIN programacion_salida p ON p.id_salida = dt_v.id_salida
                LEFT JOIN ubigeo u_o ON p.ubigeo_origen = u_o.cod_ubigeo 
                LEFT JOIN ubigeo u_d ON p.ubigeo_destino = u_d.cod_ubigeo
                LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = c.id_usuario
                LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
                WHERE p.id_salida = :id_salida
            ");
            $query->bindParam(':id_salida', $id_programacion_salida, PDO::PARAM_INT);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$programacion) {
                return ['success' => false, 'message' => ['message' => "No se encontró la programación con ID: {$embarcacion['id_programacion']}", $programacion]];
            }

            // Obtener historial de encomiendas en la embarcación (sin cambios)
            $query = $conn->prepare("
                SELECT encomienda_id 
                FROM historial_embarcaciones
                WHERE embarcacion_id = :id_embarcacion
            ");
            $query->bindParam(':id_embarcacion', $id_embarcacion, PDO::PARAM_INT);
            $query->execute();
            $historial_enco = $query->fetchAll(PDO::FETCH_COLUMN);

            if (empty($historial_enco)) {
                return [
                    'success' => true,
                    'message' => ['message' => 'No se encontraron encomiendas'],
                    'HEADER' => $programacion,
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map,
                    'EMBARQUE' => $embarcacion
                ];
            }

            // Obtener encomiendas basadas en el historial de embarcaciones (sin cambios)
            $placeholders = rtrim(str_repeat('?,', count($historial_enco)), ',');

            $sql_encomiendas = "
                SELECT
                    p.fecha_salida AS fecha_salida,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta AS id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    u_o.distri AS terminal_origen,
                    u_d.distri AS terminal_destino,
                    p_e.descripcion AS encomienda_producto,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    p_e.serie,
                    p_e.correlativo,
                    p_e.guia_serie,
                    p_e.guia_correlativo,
                    COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS tp_comprobante,

                    -- NUEVAS COLUMNAS de encomienda
                    e.tp_comprobante_ref,
                    e.serie_ref,
                    e.correlativo_ref,
                    e.ruc_ref,
                    e.guia_serie,
                    e.guia_correlativo,
                    e.guia_ruc,
        
                    -- NUEVAS COLUMNAS de doc_relacionado
                    dr.tp_comprobante AS doc_rel_tp_comprobante,
                    dr.serie AS doc_rel_serie,
                    dr.correlativo AS doc_rel_correlativo,
                    dr.ruc AS doc_rel_ruc

                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
                LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
                LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
                LEFT JOIN programacion_salida p ON p.id_salida = dt_v.id_salida
                LEFT JOIN ubigeo u_o ON p.ubigeo_origen = u_o.cod_ubigeo 
                LEFT JOIN ubigeo u_d ON p.ubigeo_destino = u_d.cod_ubigeo
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN doc_relacionado dr ON dr.id_guia_remision = e.id_encomienda
                WHERE dt_v.id_salida = ? 
                AND e.id_encomienda IN ($placeholders)
            ";

            $query = $conn->prepare($sql_encomiendas);
            $query->bindValue(1, $id_programacion_salida, PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            // Consulta para el resumen de medios de pago (sin cambios)
            $sql_resumen = "
                SELECT
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COUNT(DISTINCT e.id_encomienda) AS cantidad,
                    SUM(v.total) AS total
                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                WHERE dt_v.id_salida = ? 
                AND e.id_encomienda IN ($placeholders)
                GROUP BY m_p.id_medio_pago, m_p.descripcion
            ";

            $query = $conn->prepare($sql_resumen);
            $query->bindValue(1, $id_programacion_salida, PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $resumen_medios_pago = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => ['message' => 'Datos obtenidos correctamente'],
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'RESUMEN_MEDIOS_PAGO' => $resumen_medios_pago,
                'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map,
                'EMBARQUE' => $embarcacion
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ];
        }
    }
    public function getDataReporteEmbarqueGrupal($id_grupo_embarcacion, $id_programacion)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }
            // Obtener tipos de comprobantes (sin cambios)
            $query = $this->db->connect()->prepare("
                SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
                FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);
            $tipo_comprobante_map = array_column($tipos_comprobante, 'descripcion', 'id_tp_comprobante');
            $tipo_comprobante_map[0] = 'No especificado';

            // Obtener información de terminales (sin cambios)
            $terminales = $this->consult_terminales(8);

            // Extrayendo los id de embarcaciones del grupo
            $query = $this->db->connect()->prepare("
                SELECT id
                FROM embarcaciones
                WHERE id_grupo_embarcacion = :id_grupo_embarcacion
            ");
            $query->bindParam(':id_grupo_embarcacion', $id_grupo_embarcacion);
            $query->execute();
            $embarcaciones = $query->fetchAll(PDO::FETCH_ASSOC);


            // Obtener datos de la programación (MODIFICADA para incluir nombres de terminales)
            $query = $this->db->connect()->prepare("
                SELECT
                    dt_v.id_programacion,
                    dt_v.id_encomienda,
                    p.id_terminal_origen,
                    p.id_terminal_destino,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p.id_vehiculo,
                    p.id_conductor,
                    p.fecha_salida,
                    p.hora_salida,
                    p.id_tp_servicio_pasaje,
                    p.estado,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    c.num_docu AS conductor_num_docu,
                    dt_c.licencia AS conductor_licencia,
                    v.placa AS vehiculo_placa
                FROM dt_venta dt_v
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
                LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = c.id_usuario
                LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
                WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $id_programacion);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);

            // Historial de encomiendas en las embarcaciones del grupo
            $historial_enco = [];

            foreach ($embarcaciones as $embarcacion) {

                $query = $this->db->connect()->prepare("
                  SELECT encomienda_id
                  FROM historial_embarcaciones
                  WHERE embarcacion_id = :id_embarcacion
                ");
                $query->execute([
                    ':id_embarcacion' => $embarcacion['id']
                ]);

                $historial_enco = array_merge(
                    $historial_enco,
                    $query->fetchAll(PDO::FETCH_COLUMN)
                );
            }


            // Obtener encomiendas basadas en el historial de embarcaciones (sin cambios)
            $placeholders = rtrim(str_repeat('?,', count($historial_enco)), ',');

            $sql_encomiendas = "
                SELECT
                    p.fecha_salida AS fecha_salida,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta AS id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p_e.descripcion AS encomienda_producto,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    p_e.serie,
                    p_e.correlativo,
                    p_e.guia_serie,
                    p_e.guia_correlativo,
                    COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS tp_comprobante,

                    -- NUEVAS COLUMNAS de encomienda
                    e.tp_comprobante_ref,
                    e.serie_ref,
                    e.correlativo_ref,
                    e.ruc_ref,
                    e.guia_serie,
                    e.guia_correlativo,
                    e.guia_ruc,
        
                    -- NUEVAS COLUMNAS de doc_relacionado
                    dr.tp_comprobante AS doc_rel_tp_comprobante,
                    dr.serie AS doc_rel_serie,
                    dr.correlativo AS doc_rel_correlativo,
                    dr.ruc AS doc_rel_ruc

                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
                LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
                LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN doc_relacionado dr ON dr.id_guia_remision = e.id_encomienda
                WHERE dt_v.id_programacion = ? 
                AND e.id_encomienda IN ($placeholders)
            ";

            $query = $this->db->connect()->prepare($sql_encomiendas);
            $query->bindValue(1, $id_programacion, PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            // Consulta para el resumen de medios de pago (sin cambios)
            $sql_resumen = "
                SELECT
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COUNT(DISTINCT e.id_encomienda) AS cantidad,
                    SUM(v.total) AS total
                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                WHERE dt_v.id_programacion = ? 
                AND e.id_encomienda IN ($placeholders)
                GROUP BY m_p.id_medio_pago, m_p.descripcion
            ";

            $query = $this->db->connect()->prepare($sql_resumen);
            $query->bindValue(1, $id_programacion, PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $resumen_medios_pago = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'RESUMEN_MEDIOS_PAGO' => $resumen_medios_pago,
                'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map,
                'EMBARQUE' => $embarcacion
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ];
        }
    }

    public function registrarEnHistorialEmbarcaciones($id_embarcacion, $id_encomienda)
    {
        try {
            $query = $this->db->connect()->prepare("
                INSERT INTO historial_embarcaciones (embarcacion_id, encomienda_id)
                VALUES (:embarcacion_id, :encomienda_id)
            ");
            $query->bindParam(':embarcacion_id', $id_embarcacion, PDO::PARAM_INT);
            $query->bindParam(':encomienda_id', $id_encomienda, PDO::PARAM_INT);
            $query->execute();
            error_log("Encomienda $id_encomienda registrada en historial_embarcaciones para embarcacion $id_embarcacion");
            return true;
        } catch (PDOException $e) {
            error_log("Error al registrar en historial_embarcaciones: " . $e->getMessage());
            return false;
        }
    }

    public function getIdEmbarcacionByProgramacion($id_programacion)
    {
        try {
            $query = $this->db->connect()->prepare("
                SELECT id
                FROM embarcaciones
                WHERE id_programacion = :id_programacion
                ORDER BY id DESC
                LIMIT 1
            ");
            $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                error_log("No se encontraron embarcaciones para id_programacion: $id_programacion");
                return null;
            }

            $id_embarcacion = $result['id'];
            error_log("ID de embarcación encontrado para id_programacion: $id_programacion - id_embarcacion: $id_embarcacion");

            // Verificar si hay múltiples embarcaciones
            $query = $this->db->connect()->prepare("
                SELECT id
                FROM embarcaciones
                WHERE id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $id_programacion, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($results) > 1) {
                error_log("Advertencia: Múltiples embarcaciones encontradas para id_programacion: $id_programacion - " . json_encode($results));
            }

            return $id_embarcacion;
        } catch (PDOException $e) {
            error_log("Error al obtener id_embarcacion para id_programacion: $id_programacion - " . $e->getMessage());
            return null;
        }
    }

    // Desembarcar
    public function get_dataTableEncomiendaDesembarcar($data)
    {
        $conn = $this->db->connect();
        $query = $conn->prepare("
        SELECT
            e.id_encomienda,
            e.id_almacen,
            p.id_programacion,
            alm.descripcion AS almacen,
            CONCAT(v.serie, ' - ',v.correlativo) AS numero,
            e.codigo,
            e.fecha_salida,
            DATE(v.fecha_emision) AS fecha_emision,
            e.fecha_entrega,
            e.estado,
            e.id_remitente,
            e.id_destinatario,
            e.id_terminal_origen,
            e.id_terminal_destino,
            t_d.nombre AS terminal_destino,
            e.pago,
            v.id_venta,
            e.pago AS estado_e,
            dt_v.id_dt_venta AS id_dt_venta,
            vh.placa,
            CASE 
                    WHEN v.id_tp_comprobante = 2 THEN 'notaVenta'
                    WHEN v.id_tp_comprobante = 31 THEN 'guiasTransportista'
                    WHEN v.id_tp_comprobante IN (1, 3) THEN 'comprobante'
                    ELSE 'otro'
            END AS tabla
        FROM
        encomienda e
        LEFT JOIN dt_venta dt_v ON dt_v.id_encomienda=e.id_encomienda
        LEFT JOIN venta v ON v.id_venta=dt_v.id_venta
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN almacen alm ON alm.id_almacen=e.id_almacen
        LEFT JOIN terminal t_d ON t_d.id_terminal=e.id_terminal_destino
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        WHERE e.estado='EN TRANSITO' AND vh.placa=:placa AND e.id_terminal_destino = :id_terminal");
        $query->bindParam(":placa", $data["placa"]);
        $query->bindParam(':id_terminal', $this->id_terminal_sesion);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($reply) {
            foreach ($reply as &$encomienda) {
                $q2 = $conn->prepare("
                    SELECT
                        p_e.descripcion AS producto,
                        p_e.precio AS producto_precio, 
                        p_e.rotulado AS producto_rotulado,
                        p_e.obs AS producto_obs
                    FROM producto_encomienda p_e 
                    WHERE p_e.id_encomienda = :id_encomienda
                ");
                $q2->bindParam(":id_encomienda", $encomienda['id_encomienda'], PDO::PARAM_INT);
                $q2->execute();
                $encomienda['productos'] = $q2->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return ['data' => $reply];
    }

    public function desembarcar_encomiendas($data)
    {
        try {
            $encomiendas = json_decode($data["encomiendas"], true);
            $id_encomiendas_array = array_map('intval', array_column($encomiendas, 'id_encomienda'));
            $placeholders = implode(',', $id_encomiendas_array); // ya sanitizado con intval

            $conn = $this->db->connect();
            $conn->beginTransaction();

            // UPDATE seguro — intval ya sanitizó los ids
            $query = $conn->prepare("
            UPDATE encomienda SET estado='EN DESTINO', id_terminal_actual = :id_terminal_actual
            WHERE id_encomienda IN ($placeholders)
            ");
            $query->bindParam(":id_terminal_actual", $this->id_terminal_sesion);
            $query->execute();

            $query = $conn->prepare("
            INSERT INTO desembarques (id_programacion, id_usuario, destino)
            VALUES (:id_programacion, :id_usuario, :destino)
            ");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->bindParam(":destino", $this->id_terminal_sesion);
            $query->execute();
            $id_desembarque = $conn->lastInsertId();

            $query_movimiento = $conn->prepare("
INSERT INTO encomienda_movimiento (
    id_encomienda,
    tipo_movimiento,
    id_terminal_evento,
    id_terminal_destino,
    observacion,
    id_usuario,
    fecha_registro
)
VALUES (
    :id_encomienda,
    'LLEGADA',
    :id_terminal_evento,
    NULL,
    :observacion,
    :id_usuario,
    NOW()
)
");

            foreach ($id_encomiendas_array as $id_encomienda) {
                $query = $conn->prepare("
                INSERT INTO historial_desembarques (encomienda_id, desembarque_id)
                VALUES (:encomienda_id, :desembarque_id)
                ");
                $query->bindParam(":encomienda_id", $id_encomienda);
                $query->bindParam(":desembarque_id", $id_desembarque);
                $query->execute();

                // Registrar movimiento
                $observacion = "Encomienda desembarcada en el terminal.";

                $query_movimiento->bindValue(
                    ":id_encomienda",
                    $id_encomienda,
                    PDO::PARAM_INT
                );

                $query_movimiento->bindValue(
                    ":id_terminal_evento",
                    $this->id_terminal_sesion,
                    PDO::PARAM_INT
                );

                $query_movimiento->bindValue(
                    ":observacion",
                    $observacion,
                    PDO::PARAM_STR
                );

                $query_movimiento->bindValue(
                    ":id_usuario",
                    $this->id_usuario_sesion,
                    PDO::PARAM_INT
                );
                $query_movimiento->execute();
            }

            $conn->commit();

            // ── Notificar socket — igual que embarcar ──────────────
            $notificados = [];
            foreach ($encomiendas as $item) {
                if (empty($item['id_venta']) || empty($item['tabla']))
                    continue;

                $key = $item['tabla'] . '_' . $item['id_venta'];
                if (isset($notificados[$key]))
                    continue; // evita duplicados

                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                    "tipo" => "encomienda_cambio",
                    "tabla" => $item['tabla'],
                    "id" => $item['id_venta'],
                    "accion" => "update"
                ]);
                $notificados[$key] = true;
            }

            $link_reporte = URL . "encomienda/impresion/desembarqueH/" . $id_desembarque;
            return [
                'success' => true,
                "message" => [
                    "message" => "Productos desembarcados con éxito",
                    "links" => [['nombre' => 'Imprimir', 'link' => $link_reporte]]
                ]
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, "message" => ["message" => "Error: " . $e->getMessage()]];
        }
    }

    public function getDataReporteDesembarque($data)
    {
        try {
            // Validar parámetros de entrada (alineado con embarque)
            if (empty($data) || !is_numeric($data)) {
                return [
                    'success' => false,
                    'message' => ['message' => 'ID de programación no válido'],
                    'HEADER' => [],
                    'TERMINALES' => [],
                    'BODY' => [],
                    'EMPRESA' => [],
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => []
                ];
            }
            if (empty($this->id_terminal_sesion) || !is_numeric($this->id_terminal_sesion)) {
                return [
                    'success' => false,
                    'message' => ['message' => 'Terminal de destino no especificado'],
                    'HEADER' => [],
                    'TERMINALES' => [],
                    'BODY' => [],
                    'EMPRESA' => [],
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => []
                ];
            }


            // Consulta de datos de la empresa (igual que embarque)
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }
            if (empty($empresa) || !isset($empresa['id_empresa'])) {
                $empresa = [
                    'id_empresa' => $this->id_empresa_sesion ?? 0,
                    'nombre' => 'Empresa no especificada',
                    'ruc' => 'No especificado',
                    'direccion' => 'No especificada'
                ];
            }

            // Consulta de tipos de comprobantes (igual que embarque)
            $query = $this->db->connect()->prepare("
            SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
            FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);
            $tipo_comprobante_map = array_column($tipos_comprobante, 'descripcion', 'id_tp_comprobante');
            $tipo_comprobante_map[0] = 'NO ESPECIFICADO';

            // Consulta de terminales (sin LIMIT 7, con COALESCE como embarque)
            $query = $this->db->connect()->prepare("
            SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular, 
                   COALESCE(CONCAT(u.distri, ' - ', u.provi, ' - ', u.depa), 'No especificado') AS ubigeo
            FROM terminal t
            LEFT JOIN empresa e ON e.id_empresa = t.id_empresa
            LEFT JOIN ubigeo u ON t.ubigeo = u.cod_ubigeo
            WHERE e.id_empresa = :id_empresa
            LIMIT 8 
            ");
            $id_empresa = $empresa['id_empresa'] ?? $this->id_empresa_sesion ?? 0;
            $query->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);
            if (empty($terminales)) {
                $terminales = [];
            }

            // Consulta de programación (AGREGADO: JOIN con terminal para nombres)
            $query = $this->db->connect()->prepare("
            SELECT
                dt_v.id_programacion,
                dt_v.id_encomienda,
                p.id_terminal_origen,
                p.id_terminal_destino,
                COALESCE(t_o.nombre, 'No especificado') AS terminal_origen,
                COALESCE(t_d.nombre, 'No especificado') AS terminal_destino,
                p.id_vehiculo,
                p.id_conductor,
                COALESCE(p.fecha_salida, '0000-00-00') AS fecha_salida,
                p.hora_salida,
                p.id_tp_servicio_pasaje,
                p.estado,
                c.nombres AS conductor_nombres,
                c.apellidos AS conductor_apellidos,
                c.num_docu AS conductor_num_docu,
                dt_c.licencia AS conductor_licencia,
                v.placa AS vehiculo_placa
            FROM dt_venta dt_v
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
            LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = c.id_usuario
            LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
            LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
            WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $data, PDO::PARAM_INT);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$programacion) {
                return [
                    'success' => false,
                    'message' => ['message' => "No se encontró la programación con ID: $data"],
                    'HEADER' => [],
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
                ];
            }

            // Consulta de encomiendas (sin cambios, ya incluye terminal_origen y terminal_destino)
            $query = $this->db->connect()->prepare("
            SELECT
                COALESCE(p.fecha_salida, '0000-00-00') AS fecha_salida,
                v.fecha_emision AS fecha_emision,
                e.id_encomienda AS id_encomienda,
                v.id_venta AS id_venta,
                e.id_remitente,
                r.nombres AS remitente_nombres,
                r.apellidos AS remitente_apellidos,
                d.nombres AS destinatario_nombres,
                d.apellidos AS destinatario_apellidos,
                v.serie AS venta_serie,
                v.correlativo AS venta_correlativo,
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                p_e.descripcion AS detalle,
                p_e.obs AS obs,
                p_e.cantidad AS encomienda_cantidad,
                p_e.op_gravada AS encomienda_precio_op_gravada,
                p_e.op_exonerada AS encomienda_precio_op_exonerada,
                p_e.op_inafecta AS encomienda_precio_op_inafecta,
                (COALESCE(p_e.op_gravada, 0) * 1.18 + COALESCE(p_e.op_exonerada, 0) + COALESCE(p_e.op_inafecta, 0)) AS total_producto,
                COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                v.id_tp_comprobante AS id_tp_comprobante,
                COALESCE(TRIM(UPPER(tp_c.descripcion)), 'NO ESPECIFICADO') AS tipo_comprobante
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
            LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
            WHERE dt_v.id_programacion = :id_programacion 
            AND e.estado = 'EN DESTINO'
            AND e.id_terminal_destino = :id_terminal_destino
            ORDER BY e.id_encomienda, p_e.id_producto_encomienda 
            ");
            $query->bindParam(':id_programacion', $data, PDO::PARAM_INT);
            $query->bindParam(':id_terminal_destino', $this->id_terminal_sesion, PDO::PARAM_INT);
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($encomiendas)) {
                return [
                    'success' => true,
                    'message' => ['message' => 'No se encontraron encomiendas en destino'],
                    'HEADER' => $programacion,
                    'TERMINALES' => $terminales,
                    'BODY' => [],
                    'EMPRESA' => $empresa,
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
                ];
            }

            // Consulta para el resumen de medios de pago
            $query = $this->db->connect()->prepare("
            SELECT
                COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                COUNT(DISTINCT e.id_encomienda) AS cantidad,
                SUM(COALESCE(p_e.op_gravada, 0) * 1.18 + COALESCE(p_e.op_exonerada, 0) + COALESCE(p_e.op_inafecta, 0)) AS total
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            WHERE dt_v.id_programacion = :id_programacion 
            AND e.estado = 'EN DESTINO'
            AND e.id_terminal_destino = :id_terminal_destino
            GROUP BY m_p.id_medio_pago, m_p.descripcion
            ");
            $query->bindParam(':id_programacion', $data, PDO::PARAM_INT);
            $query->bindParam(':id_terminal_destino', $this->id_terminal_sesion, PDO::PARAM_INT);
            $query->execute();
            $resumen_medios_pago = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => ['message' => 'Datos obtenidos correctamente'],
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'RESUMEN_MEDIOS_PAGO' => $resumen_medios_pago,
                'TIPO_COMPROBANTE_MAP' => $tipo_comprobante_map
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ],
                'HEADER' => [],
                'TERMINALES' => [],
                'BODY' => [],
                'EMPRESA' => [],
                'RESUMEN_MEDIOS_PAGO' => [],
                'TIPO_COMPROBANTE_MAP' => []
            ];
        }
    }

    public function getDataReporteDesembarqueH($data)
    {
        try {
            if (empty($data) || !is_numeric($data)) {
                return [
                    'success' => false,
                    'message' => ['message' => 'ID de programación no válido'],
                    'HEADER' => [],
                    'TERMINALES' => [],
                    'BODY' => [],
                    'EMPRESA' => [],
                    'RESUMEN_MEDIOS_PAGO' => [],
                    'TIPO_COMPROBANTE_MAP' => []
                ];
            }

            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $empresa = $this->consult_emisor_encomienda();
            } else {
                $empresa = $this->consult_emisor();
            }

            $terminales = $this->consult_terminales(8);

            $query = $this->db->connect()->prepare("
            SELECT *
            FROM desembarques
            WHERE id = :id
            ");
            $query->bindParam(':id', $data, PDO::PARAM_INT);
            $query->execute();
            $data_desembarque = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
            SELECT 
            encomienda_id 
            FROM historial_desembarques
            WHERE desembarque_id = :desembarque_id
            ");
            $query->bindParam(':desembarque_id', $data, PDO::PARAM_INT);
            $query->execute();
            $historial_des = $query->fetchAll(PDO::FETCH_COLUMN);

            $query = $this->db->connect()->prepare("
            SELECT 
                p.id_terminal_origen,
                p.id_terminal_destino,
                p.id_vehiculo,
                p.id_conductor,
                COALESCE(p.fecha_salida, '0000-00-00') AS fecha_salida,
                p.hora_salida,
                p.id_tp_servicio_pasaje,
                p.estado,
                con.nombres AS conductor_nombres,
                con.apellidos AS conductor_apellidos,
                con.num_docu AS conductor_num_docu,
                dt_c.licencia AS conductor_licencia,
                v.placa AS vehiculo_placa,
                t_o.nombre AS terminal_origen_nombre,
                t_d.nombre AS terminal_destino_nombre
            FROM programacion p
            LEFT JOIN usuario con ON con.id_usuario = p.id_conductor
            LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
            LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = p.id_conductor
            LEFT JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
            WHERE p.id_programacion = :id_programacion
            ");
            $query->bindParam(':id_programacion', $data_desembarque['id_programacion'], PDO::PARAM_INT);
            $query->execute();
            $programacion = $query->fetch(PDO::FETCH_ASSOC);

            $placeholders = rtrim(str_repeat('?,', count($historial_des)), ',');

            $sql_encomiendas = "
            SELECT 
                    p.fecha_salida AS fecha_salida,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta AS id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p_e.descripcion AS encomienda_producto,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_igv,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    p_e.serie,
                    p_e.correlativo,
                    p_e.guia_serie,
                    p_e.guia_correlativo,
                    COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS tp_comprobante
            FROM dt_venta  dt_v
            LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
            LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
            WHERE dt_v.id_programacion = ? 
            AND e.id_encomienda IN ($placeholders)
            ";
            $query = $this->db->connect()->prepare($sql_encomiendas);
            $query->bindValue(1, $data_desembarque['id_programacion'], PDO::PARAM_INT);
            foreach ($historial_des as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            // Tp_comprobantes
            $query = $this->db->connect()->prepare("
             SELECT id_tp_comprobante, TRIM(UPPER(descripcion)) AS descripcion
             FROM tp_comprobante
            ");
            $query->execute();
            $tipos_comprobante = $query->fetchAll(PDO::FETCH_ASSOC);

            $medios_pago_encomienda = [];
            $totales_tipo_comprobante = [];

            foreach ($encomiendas as $enc) {
                $medio = $enc['medio_pago'];
                $tipoComprobante = $enc['tp_comprobante'];
                $descripcionComprobante = '';

                // Buscar la descripción del tipo de comprobante
                foreach ($tipos_comprobante as $tipo) {
                    if ($tipo['id_tp_comprobante'] == $tipoComprobante) {
                        $descripcionComprobante = $tipo['descripcion'];
                        break;
                    }
                }

                $total = (
                    ($enc['encomienda_precio_op_gravada'] ?? 0) +
                    ($enc['op_igv'] ?? 0) +
                    ($enc['encomienda_precio_op_exonerada'] ?? 0) +
                    ($enc['encomienda_precio_op_inafecta'] ?? 0)
                );

                if (!isset($medios_pago_encomienda[$medio])) {
                    $medios_pago_encomienda[$medio] = [
                        'cantidad' => 0,
                        'total' => 0,
                    ];
                }
                $medios_pago_encomienda[$medio]['cantidad']++;
                $medios_pago_encomienda[$medio]['total'] += $total;

                if (!isset($totales_tipo_comprobante[$descripcionComprobante])) {
                    $totales_tipo_comprobante[$descripcionComprobante] = [
                        'cantidad' => 0,
                        'total' => 0,
                    ];
                }
                $totales_tipo_comprobante[$descripcionComprobante]['cantidad']++;
                $totales_tipo_comprobante[$descripcionComprobante]['total'] += $total;
            }

            return [
                'success' => true,
                'message' => ['message' => 'Datos obtenidos correctamente'],
                'HEADER' => $programacion,
                'TERMINALES' => $terminales,
                'BODY' => $encomiendas,
                'EMPRESA' => $empresa,
                'DESEMBARQUE' => $data_desembarque,
                'RESUMEN_MEDIOS_PAGO' => $medios_pago_encomienda,
                'TIPO_COMPROBANTE_MAP' => $totales_tipo_comprobante
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => [
                    'message' => 'Error en la base de datos: ' . $e->getMessage(),
                    'code' => $e->getCode()
                ],
                'HEADER' => [],
                'TERMINALES' => [],
                'BODY' => [],
                'EMPRESA' => [],
            ];
        }
    }

    // Reporte
    public function get_terminalDestinoReporte($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT    
                t.id_terminal,
                t.nombre,
                t.ubigeo
            FROM terminal t
            WHERE t.id_terminal!=:id_terminal");
            $query->bindParam(':id_terminal', $data["id_terminalOrigen"]);
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
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function get_programacionReporte($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                p.id_programacion,
                p.id_vehiculo,
                v.placa,
                p.id_conductor,
                c.nombres AS conductor_nombres,
                c.apellidos AS conductor_apellidos,
                c.num_docu AS conductor_num_docu,
                p.fecha_salida, 
                p.hora_salida,
                p.id_tp_servicio_pasaje,
                t_d.nombre AS terminal_destino,
                tps_psj.descripcion AS tp_servicio_pasaje
            FROM programacion p 
            LEFT JOIN vehiculo v ON v.id_vehiculo=p.id_vehiculo
            LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
            LEFT JOIN tp_servicio_pasaje tps_psj ON tps_psj.id_tp_servicio_pasaje=p.id_tp_servicio_pasaje
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            WHERE p.id_terminal_origen=:id_terminal_origen AND p.id_terminal_destino=:id_terminal_destino");
            $query->bindParam(':id_terminal_origen', $data["id_terminalOrigen"]);
            $query->bindParam(':id_terminal_destino', $data["id_terminalDestino"]);
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
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function get_programacionTerminalOrigenReporte($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT
                p.id_programacion,
                p.id_vehiculo,
                v.placa,
                p.id_conductor,
                c.nombres AS conductor_nombres,
                c.apellidos AS conductor_apellidos,
                c.num_docu AS conductor_num_docu,
                p.fecha_salida, 
                p.hora_salida,
                p.id_tp_servicio_pasaje,
                t_d.nombre AS terminal_destino,
                tps_psj.descripcion AS tp_servicio_pasaje
            FROM programacion p 
            LEFT JOIN vehiculo v ON v.id_vehiculo=p.id_vehiculo
            LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
            LEFT JOIN tp_servicio_pasaje tps_psj ON tps_psj.id_tp_servicio_pasaje=p.id_tp_servicio_pasaje
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            WHERE p.id_terminal_origen=:id_terminal_origen");
            $query->bindParam(':id_terminal_origen', $data["id_terminalOrigen"]);
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
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }


    public function getDataReporteGeneral($id_terminal_origen, $fecha_inicio, $fecha_fin)
    {
        try {
            // Obteniendo data empresa
            $query = $this->db->connect()->prepare("SELECT * FROM empresa");
            $query->execute();
            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo data terminal
            $query = $this->db->connect()->prepare("SELECT * FROM terminal WHERE id_terminal=:id_terminal");
            $query->bindParam(":id_terminal", $id_terminal_origen);
            $query->execute();
            $terminal = $query->fetch(PDO::FETCH_ASSOC);

            $terminal["fecha_inicio_reporte"] = implode("-", explode("_", $fecha_inicio));
            $terminal["fecha_fin_reporte"] = implode("-", explode("_", $fecha_fin));

            $fecha_inicio = implode("-", array_reverse(explode("_", $fecha_inicio)));
            $fecha_fin = implode("-", array_reverse(explode("_", $fecha_fin)));

            // Obteniendo las encomiendas
            $query = $this->db->connect()->prepare("
            SELECT
                p.fecha_salida AS fecha_salida,
                e.id_remitente,
                r.nombres AS remitente_nombres,
                r.apellidos AS remitente_apellidos,
                v.serie AS venta_serie,
                v.correlativo AS venta_correlativo,
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                p_e.descripcion AS encomienda_producto,
                p_e.cantidad AS encomienda_cantidad,
                p_e.precio AS encomienda_precio_unitario,
                f_p.descripcion AS forma_pago,
                m_p.descripcion AS medio_pago,
                e.estado AS encomienda_estado,
                v.op_gravada AS total
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta=dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda=e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario=e.id_remitente
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago=v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            WHERE e.id_terminal_origen=$id_terminal_origen AND v.id_tp_comprobante IN (1,3) AND (DATE_FORMAT(v.fecha_emision, '%Y-%m-%d') >= '$fecha_inicio' AND DATE_FORMAT(v.fecha_emision, '%Y-%m-%d') <= '$fecha_fin')
            ");
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            return array(
                "EMPRESA" => $empresa,
                "TERMINAL" => $terminal,
                "BODY" => $encomiendas,
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function getDataReporteOrigenDestino($id_programacion)
    {
        try {
            // Obteniendo data terminal, conductor, programación y vehiculo
            $query = $this->db->connect()->prepare("
                SELECT
                    t_o.nombre AS terminal_origen_nombre,
                    t_d.nombre AS terminal_destino_nombre,
                    p.fecha_salida AS fecha_salida,
                    p.hora_salida AS hora_salida,
                    vh.placa AS vehiculo_placa,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    dt_c.licencia AS conductor_licencia
                FROM programacion p
                LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
                LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
                LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
                WHERE p.id_programacion=:id_programacion
                ");
            $query->bindParam(":id_programacion", $id_programacion);
            $query->execute();
            $header = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo las encomiendas
            $query = $this->db->connect()->prepare("
            SELECT
                p.fecha_salida AS fecha_salida,
                e.id_remitente,
                r.nombres AS remitente_nombres,
                r.apellidos AS remitente_apellidos,
                tp_dc.descripcion AS remitente_tp_docu,
                r.num_docu AS remitente_num_docu,
                v.serie AS venta_serie,
                v.correlativo AS venta_correlativo,
                p_e.descripcion AS encomienda_producto,
                p_e.cantidad AS encomienda_cantidad,
                p_e.precio AS encomienda_precio_unitario,
                v.op_gravada AS total
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta=dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda=e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario=e.id_remitente
            LEFT JOIN tp_docu tp_dc ON tp_dc.id_tp_docu=r.id_tp_docu
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago=v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
            WHERE p.id_programacion=$id_programacion AND tp_s.descripcion='ENCOMIENDA'
            ");
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            return array(
                "HEADER" => $header,
                "BODY" => $encomiendas,
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function getDataReporteVehiculo($id_programacion)
    {
        try {

            // Obteniendo data empresa
            $query = $this->db->connect()->prepare("SELECT * FROM empresa");
            $query->execute();
            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo data terminal, conductor, programación y vehiculo
            $query = $this->db->connect()->prepare("
                SELECT
                    t.nombre AS terminal_nombre,
                    t.direccion_comercial AS terminal_direccion_comercial,
                    t.celular AS terminal_celular,
                    p.fecha_salida AS fecha_salida,
                    p.hora_salida AS hora_salida,
                    vh.placa AS vehiculo_placa,
                    c.nombres AS conductor_nombres,
                    c.apellidos AS conductor_apellidos,
                    c.celular AS conductor_celular,
                    dt_c.licencia AS conductor_licencia
                FROM programacion p
                LEFT JOIN terminal t ON t.id_terminal=p.id_terminal_origen
                LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
                LEFT JOIN usuario c ON c.id_usuario=p.id_conductor
                LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario=c.id_usuario
                wHERE p.id_programacion=:id_programacion
                ");
            $query->bindParam(":id_programacion", $id_programacion);
            $query->execute();
            $header = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo las encomiendas
            $query = $this->db->connect()->prepare("
            SELECT
                p.fecha_salida AS fecha_salida,
                e.id_remitente,
                r.nombres AS remitente_nombres,
                r.apellidos AS remitente_apellidos,
                tp_dc.descripcion AS remitente_tp_docu,
                r.num_docu AS remitente_num_docu,
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                v.serie AS venta_serie,
                v.correlativo AS venta_correlativo,
                p_e.descripcion AS encomienda_producto,
                p_e.cantidad AS encomienda_cantidad,
                p_e.precio AS encomienda_precio_unitario,
                e.estado AS encomienda_estado,
                v.op_gravada AS total
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta=dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda=e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario=e.id_remitente
            LEFT JOIN tp_docu tp_dc ON tp_dc.id_tp_docu=r.id_tp_docu
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago=v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
            WHERE p.id_programacion=$id_programacion AND tp_s.descripcion='ENCOMIENDA'
            AND p.id_terminal_origen=(SELECT id_terminal_origen FROM programacion WHERE id_programacion=$id_programacion)
            AND p.id_vehiculo=(SELECT id_vehiculo FROM programacion WHERE id_programacion=$id_programacion)
            ");
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            return array(
                "EMPRESA" => $empresa,
                "HEADER" => $header,
                "BODY" => $encomiendas,
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function getDataReporteCliente($id_terminal_origen, $id_cliente)
    {
        try {
            // Obteniendo data empresa
            $query = $this->db->connect()->prepare("SELECT * FROM empresa");
            $query->execute();
            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo data terminal, conductor, programación y vehiculo
            $query = $this->db->connect()->prepare("
            SELECT
                t.nombre AS terminal_nombre,
                t.direccion_comercial AS terminal_direccion_comercial,
                t.celular AS terminal_celular,
                c.nombres AS cliente_nombres,
                c.apellidos AS cliente_apellidos,
                c.num_docu AS cliente_num_docu,
                c.celular AS cliente_celular,
                c.direccion AS cliente_direccion
            FROM venta v
            LEFT JOIN terminal t ON t.id_terminal=v.id_terminal
            LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
            WHERE v.id_cliente=$id_cliente AND t.id_terminal=$id_terminal_origen
            ");
            $query->execute();
            $header = $query->fetch(PDO::FETCH_ASSOC);

            // Obteniendo las encomiendas
            $query = $this->db->connect()->prepare("
            SELECT
                p.fecha_salida AS fecha_salida,
                e.id_remitente,
                r.nombres AS remitente_nombres,
                r.apellidos AS remitente_apellidos,
                tp_dc.descripcion AS remitente_tp_docu,
                r.num_docu AS remitente_num_docu,
                t_o.nombre AS terminal_origen,
                t_d.nombre AS terminal_destino,
                v.serie AS venta_serie,
                v.correlativo AS venta_correlativo,
                p_e.descripcion AS encomienda_producto,
                p_e.cantidad AS encomienda_cantidad,
                p_e.precio AS encomienda_precio_unitario,
                e.estado AS encomienda_estado,
                v.op_gravada AS total
            FROM dt_venta dt_v
            LEFT JOIN venta v ON v.id_venta=dt_v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda=dt_v.id_encomienda
            LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda=e.id_encomienda
            LEFT JOIN usuario r ON r.id_usuario=e.id_remitente
            LEFT JOIN tp_docu tp_dc ON tp_dc.id_tp_docu=r.id_tp_docu
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
            LEFT JOIN forma_pago f_p ON f_p.id_forma_pago=v.id_forma_pago
            LEFT JOIN medio_pago m_p ON m_p.id_medio_pago=v.id_medio_pago
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
            WHERE v.id_cliente=$id_cliente AND tp_s.descripcion='ENCOMIENDA'
            AND p.id_terminal_origen=$id_terminal_origen
            ");
            $query->execute();
            $encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            return array(
                "EMPRESA" => $empresa,
                "HEADER" => $header,
                "BODY" => $encomiendas,
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function updateVentaSetForSunat($data, $conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $envio_sunat = isset($data["estado"]) && $data["estado"] == 1 ? 1 : 0;
        $query = $conn->prepare("UPDATE venta SET
            envio_sunat=:envio_sunat,
            descrip_cdr_sunat=:descrip_cdr_sunat,
            hash_cdr=:hash_cdr,
            file_xml=:file_xml,
            file_cdr=:file_cdr
        WHERE id_venta=:id_venta
        ");
        $query->bindParam(':envio_sunat', $envio_sunat);
        $query->bindParam(':descrip_cdr_sunat', $data["mensaje_sunat"]);
        $query->bindParam(':hash_cdr', $data["hash_cpe"]);
        $query->bindParam(':file_xml', $data["xml"]);
        $query->bindParam(':file_cdr', $data["cdr"]);
        $query->bindParam(':id_venta', $data["id_venta"]);
        $query->execute();
    }

    public function insert_guia($data, $respuesta, $conn = null)
    {
        if ($conn == null) {
            $conn = $this->db->connect();
        }
        $id_grupo_embarque = $data['id_grupo_embarque'] ?? null;
        $id_embarcacion = $data['id_embarcacion'] ?? null;
        try {
            $id_venta = $data['id_venta_r'] ?? null;
            $data['pagador_flete'] = isset($data['pagador_flete']['id_pagador_flete']) ? $data['pagador_flete']['id_pagador_flete'] : 0;
            $query = $conn->prepare("INSERT INTO guia_remision (
            remitente_id, serie, correlativo, fecha_emision, fecha_envio, peso, vehiculo_placa, conductor_tipo_doc, 
            conductor_nro_doc, conductor_nombres, conductor_apellidos, conductor_licencia, partida_ubigeo, partida_direccion, 
            destino_ubigeo, destino_direccion, nombrexml, hash, codigo_sunat, mensaje_sunat, xml, cdr, destinatario, d_num_doc, 
            d_tipo_doc, link_guia_sunat, pagador_flete, observaciones, id_grupo_embarcacion, id_embarcacion, hora_emision, tarjeta_mtc, id_venta) VALUES (
            :remitente_id, :serie, :correlativo, :fecha_emision, :fecha_envio, :peso, :vehiculo_placa, :conductor_tipo_doc, :conductor_nro_doc, 
            :conductor_nombres, :conductor_apellidos, :conductor_licencia, :partida_ubigeo, :partida_direccion,
            :destino_ubigeo, :destino_direccion, :nombrexml, :hash, :codigo_sunat, :mensaje_sunat, :xml, :cdr, :destinatario,
            :d_num_doc, :d_tipo_doc, :link_guia_sunat, :pagador_flete, :observaciones, :id_grupo_embarque, :id_embarcacion, :hora_emision, :tarjeta_mtc, :id_venta
            )");
            $query->bindParam(":remitente_id", $data['remitente']["usuario"]);
            $query->bindParam(":serie", $data['cabecera']['serie']);
            $query->bindParam(":correlativo", $data['cabecera']['correlativo']);
            $query->bindParam(":fecha_emision", $data['cabecera']['fecha_emision']);
            $query->bindParam(":fecha_envio", $data['cabecera']["fecha_envio"]);
            $query->bindParam(":peso", $data['cabecera']["peso"]);
            $query->bindParam(":vehiculo_placa", $data['cabecera']["vehiculo_placa"]);
            $query->bindParam(":conductor_tipo_doc", $data['cabecera']["conductor_tipo_doc"]);
            $query->bindParam(":conductor_nro_doc", $data['cabecera']["conductor_nro_doc"]);
            $query->bindParam(":conductor_nombres", $data['cabecera']["conductor_nombres"]);
            $query->bindParam(":conductor_apellidos", $data['cabecera']["conductor_apellidos"]);
            $query->bindParam(":conductor_licencia", $data['cabecera']["conductor_licencia"]);
            $query->bindParam(":partida_ubigeo", $data['cabecera']["partida_ubigeo"]);
            $query->bindParam(":partida_direccion", $data['cabecera']["partida_direccion"]);
            $query->bindParam(":destino_ubigeo", $data['cabecera']["destino_ubigeo"]);
            $query->bindParam(":destino_direccion", $data['cabecera']["destino_direccion"]);
            //Data de resp
            $query->bindParam(":nombrexml", $respuesta[0]["nombrexml"]);
            //$query->bindParam(":xmlbase64", $respuesta["xmlbase64"]);
            $query->bindParam(":hash", $respuesta[0]["hash_cpe"]);
            // $query->bindParam(":cdrbase64", $respuesta["cdrbase64"]);
            $query->bindParam(":codigo_sunat", $respuesta[0]["estado"]);
            $query->bindParam(":mensaje_sunat", $respuesta[0]["mensaje_sunat"]);
            $query->bindParam(":xml", $respuesta[0]["xml"]);
            $query->bindParam(":cdr", $respuesta[0]["cdr"]);
            $query->bindParam(":destinatario", $data['destinatario']["razon_social"]);
            $query->bindParam(":d_num_doc", $data['destinatario']["num_doc"]);
            $query->bindParam(":d_tipo_doc", $data['destinatario']["tipo_documento"]);
            $query->bindParam(":link_guia_sunat", $respuesta[0]["link_guia_sunat"]);
            $query->bindParam(":pagador_flete", $data["pagante"]['id_pagador']);
            $query->bindParam(":observaciones", $data["cabecera"]['observaciones']);
            $query->bindParam(":id_grupo_embarque", $id_grupo_embarque);
            $query->bindParam(":id_embarcacion", $id_embarcacion);
            $query->bindParam(":hora_emision", $data['cabecera']['hora_emision']);
            $query->bindParam(":tarjeta_mtc", $data['cabecera']['vehiculo_mtc']);
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $id_guia_remision = $conn->lastInsertId();

            if (!empty($data['documentos_globales'])) {
                foreach ($data['documentos_globales'] as $doc_relacionado) {

                    $tp_comprobante = $doc_relacionado['tipo_documento'] ?? '';
                    $serie_numero = $doc_relacionado['serie_numero'] ?? '';
                    $ruc = $doc_relacionado['ruc_emisor'] ?? '';

                    $partes = explode('-', $serie_numero);
                    $serie = $partes[0] ?? '';
                    $correlativo = $partes[1] ?? '';

                    if (empty($serie) || empty($correlativo) || empty($ruc)) {
                        continue;
                    }

                    $query = $conn->prepare("
                    INSERT INTO doc_relacionado (
                    id_guia_remision, tp_comprobante, serie, correlativo, ruc
                    ) VALUES (
                    :id_guia_remision, :tp_comprobante, :serie, :correlativo, :ruc
                     )
                    ");
                    $query->bindParam(":id_guia_remision", $id_guia_remision);
                    $query->bindParam(":tp_comprobante", $tp_comprobante);
                    $query->bindParam(":serie", $serie);
                    $query->bindParam(":correlativo", $correlativo);
                    $query->bindParam(":ruc", $ruc);
                    $query->execute();
                }
            }

            return ['success' => true, 'id' => $id_guia_remision];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El proceso no se completo");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    function insert_detalle_guia($data, $id, $conn = null)
    {
        try {
            if ($conn == null) {
                $conn = $this->db->connect();
            }
            foreach ($data['items'] as $item) {
                $query = $conn->prepare("INSERT INTO guia_remision_detalle 
                (guia_remision_id, item, nombre, producto_id, cantidad) 
                VALUES (:guia_remision_id, :item, :nombre, :producto_id, :cantidad)");
                $query->bindParam(":guia_remision_id", $id['id']);
                $query->bindParam(":item", $item['item']);
                $query->bindParam(":nombre", $item['nombre']);
                $query->bindParam(":producto_id", $item['id_producto']);
                $query->bindParam(":cantidad", $item['cantidad']);
                $query->execute();
            }
            return array('success' => true, "message" => "Inserción exitosa");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => $e->getMessage());
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, inténtalo más tarde.");
                    break;
            }
        }
    }


    public function enviar_json_a_api($json)
    {

        $api_url = API_URL;
        // Datos a enviar en la solicitud POST

        // Inicializar cURL
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

        // Verificar si hubo errores
        if (curl_errno($curl)) {
            // Manejar el error, por ejemplo, registrándolo o devolviendo un mensaje de error
            return 'Error en la solicitud: ' . curl_error($curl);
        }

        // Cerrar la conexión cURL
        curl_close($curl);

        // Devolver la respuesta de la API
        return $response;
    }

    public function get_productosEncomienda($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM producto_encomienda WHERE id_encomienda=:id_encomienda");
            $query->bindParam(':id_encomienda', $data['id_encomienda']);
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
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function buscar_ubigeo($data)
    {
        try {
            $db = $this->db->connect();

            if (!empty($data['pre'])) {
                // Buscar por código exacto
                $query = $db->prepare("
                SELECT cod_ubigeo,
                       CONCAT(distri, ' - ', provi, ' - ', depa) AS nombre
                FROM ubigeo
                WHERE cod_ubigeo = :ubigeo
                LIMIT 1
            ");
                $query->bindParam(':ubigeo', $data['pre']);
            } else {
                // Búsqueda optimizada con FULLTEXT o índices
                $q = '%' . $data['q'] . '%';
                $query = $db->prepare("
                SELECT cod_ubigeo,
                       CONCAT(distri, ' - ', provi, ' - ', depa) AS nombre
                FROM ubigeo
                WHERE CONCAT(distri, ' - ', provi, ' - ', depa) LIKE :q
                ORDER BY 
                    CASE 
                        WHEN depa LIKE :q_start1 THEN 1
                        WHEN provi LIKE :q_start2 THEN 2
                        WHEN distri LIKE :q_start3 THEN 3
                        ELSE 4
                    END
                LIMIT 15
            ");
                $q_start = $data['q'] . '%';
                $query->bindParam(':q', $q);
                $query->bindParam(':q_start1', $q_start);
                $query->bindParam(':q_start2', $q_start);
                $query->bindParam(':q_start3', $q_start);
            }

            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            return array('success' => true, "items" => $reply);
        } catch (PDOException $e) {
            return array(
                'success' => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            );
        }
    }

    public function buscar_ruta_anexo2($data)
    {
        try {
            $db = $this->db->connect();

            // Búsqueda optimizada con FULLTEXT o índices
            $q = '%' . $data['q'] . '%';
            $query = $db->prepare("
                SELECT 
                d.id,
                d.ruta_anexo2_id,
                d.dv_parcial_km,
                d.dv_acumulado_km,
                d.valor_por_tm,
                CONCAT(d.destino, ' | ', r.nombre_ruta) AS nombre
                FROM detalle_anexo2 d
                LEFT JOIN rutas_anexo2 r ON r.id = d.ruta_anexo2_id
                WHERE destino LIKE :q
                LIMIT 5
            ");
            $query->bindParam(':q', $q);

            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, "items" => $reply];
        } catch (PDOException $e) {
            return [
                'success' => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ];
        }
    }

    public function convert_NXC($data)
    {
        try {
            $ruc_encomienda = $this->get_ruc_encomienda_separado();

            if ($ruc_encomienda == 1) {
                $tipo_ruc = 2;
            } else {
                $tipo_ruc = 1;
            }
            // Paramteros necesarios para la facturacion
            $query = $this->db->connect()->prepare("
            SELECT 
            id_serie,
            serie
            FROM serie
            WHERE id_terminal=:id_terminal 
            AND id_tp_comprobante = :id_tp_comprobante
            LIMIT 1
            ");
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->bindParam(':id_tp_comprobante', $data['tp_comprobante']);
            $query->execute();
            $serie_data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$serie_data) {
                return array(
                    'success' => false,
                    "message" => "No se encontró una serie activa para el tipo de comprobante seleccionado."
                );
            }

            $serie = $serie_data['serie'];
            $id_serie = $serie_data['id_serie'];
            $serie_data['serie_venta'] = $serie_data['id_serie'];
            $correlativo = $this->get_correlativo($serie_data);

            // Exytraccion de datos de la venta para crear el comprobante de venta electronico dependiendo del tipo de comprobante
            $query = $this->db->connect()->prepare("
            SELECT 
            v.*,
            dt_v.id_encomienda
            FROM venta v
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            WHERE v.id_venta=:id_venta");
            $query->bindParam(':id_venta', $data['id_venta']);
            $query->execute();
            $data_notaV = $query->fetch(PDO::FETCH_ASSOC);

            $codigo_venta = uniqid(rand());

            // En caso se haga cambios en las operaciones
            $op_gravada = $data_notaV['op_gravada'];
            $op_inafecta = $data_notaV['op_inafecta'];
            $op_exonerada = $data_notaV['op_exonerada'];
            $igv = $data_notaV['op_igv'];
            $fecha_comprobanteV = date("Y-m-d H:i:s");

            // Insertando nuevo registro de venta con los datos de comprobante
            $query = $this->db->connect()->prepare("INSERT INTO venta (
                    codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                    id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                    correlativo, descuento, op_igv, estado, envio_sunat,
                    descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                    op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs,
                    id_sesionpersonal, peso_encomienda, tipo_ruc
                    )
                    VALUES(
                    :codigo, :id_terminal, :id_vendedor, :id_cliente, :id_forma_pago, :id_medio_pago,
                    :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                    :correlativo, '', :igv,  'PAGADO', 0, 
                    '', '', '', '', '', :fecha_emision, :op_gravada,
                    :op_exonerada, :op_inafecta, :total, '','', '', :id_sesionpersonal, :peso_total, :tipo_ruc
                    )");
            $query->bindParam(':codigo', $codigo_venta);
            $query->bindParam(':id_terminal', $data_notaV["id_terminal"]);
            $query->bindParam(':id_vendedor', $data_notaV['id_vendedor']);
            $query->bindParam(':id_cliente', $data_notaV["id_cliente"]);
            $query->bindParam(':id_forma_pago', $data_notaV["id_forma_pago"]);
            $query->bindParam(':id_medio_pago', $data_notaV["id_medio_pago"]);
            $query->bindParam(':id_tp_moneda', $data_notaV["id_tp_moneda"]);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':id_caja_chica', $data_notaV["id_caja_chica"]);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':id_serie', $serie_data["id_serie"]);
            $query->bindParam(':igv', $data_notaV['op_igv']);
            $query->bindParam(':fecha_emision', $fecha_comprobanteV);
            $query->bindParam(':op_gravada', $data_notaV['op_gravada']);
            $query->bindParam(':op_exonerada', $data_notaV['op_exonerada']);
            $query->bindParam(':op_inafecta', $data_notaV['op_inafecta']);
            $query->bindParam(':total', $data_notaV["total"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':peso_total', $data_notaV['peso_total']);
            $query->bindParam(':tipo_ruc', $tipo_ruc);
            $query->execute();

            $id_comprobanteN = $this->db->connect()->lastInsertId();

            // En caso sea un comprobante a credito y se tiene una cuota tambien actualizar
            $query = $this->db->connect()->prepare("
                UPDATE cuota SET
                    comprobante_id = :id_comprobante
                WHERE comprobante_id=:id_venta");
            $query->bindParam(':id_venta', $data_notaV['id_venta']);
            $query->bindParam(':id_comprobante', $id_comprobanteN);
            $query->execute();

            // Actualizando la nota venta
            $query = $this->db->connect()->prepare("
                        UPDATE venta SET
                            total = 0.00,
                            estado = 'PAGADO',
                            id_pago = :id_pago
                        WHERE id_venta=:id_venta");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->bindParam(':id_pago', $id_comprobanteN);
            $query->execute();

            // Insertando detalle venta de la nota de venta al comprobante nuevo
            $query = $this->db->connect()->prepare("
                        INSERT INTO dt_venta (
                            id_venta, id_tp_servicio, id_programacion, id_encomienda, precio, id_sesionpersonal
                        )
                        SELECT
                            $id_comprobanteN AS id_venta,
                            id_tp_servicio,
                            id_programacion,
                            id_encomienda,
                            precio,
                            $this->id_usuario_sesion AS id_sesionpersonal
                        FROM dt_venta
                        WHERE id_venta=:id_venta AND id_encomienda=:id_encomienda");
            $query->bindParam(':id_venta', $data["id_venta"]);
            $query->bindParam(':id_encomienda', $data_notaV["id_encomienda"]);
            $query->execute();

            // Facturacion
            $id_venta = $id_comprobanteN;
            $this->generar_codQR($id_venta);
            $rpta_sunat = $this->enviar_json_a_api($this->conversor_encomienda($this->getDataComprobante($id_comprobanteN)));
            $resp = json_decode($rpta_sunat, true);
            // Actualizando los datos de la venta
            $resp[0]["id_venta"] = $id_comprobanteN;
            $this->updateVentaSetForSunat($resp[0]);

            $id_link_comprobante = $id_comprobanteN ?? $data["id_venta"];

            $link_comprobante = URL . "encomienda/impresion/comprobante/" . $id_link_comprobante;
            return [
                'success' => true,
                "message" => [
                    "message" => "Se ha convertido la nota de venta a comprobante correctamente.",
                    "links" => [
                        ['nombre' => 'Imprimir', 'link' => $link_comprobante]
                    ]
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ];
        }
    }

    public function ExCrear_guia($data)
    {
        try {
            $id_encomiendas = $data["id_encomienda"];
            $id_programacion = $data['id_programacion'];

            $json_guia = $this->crear_guia_individual($id_encomiendas, $id_programacion);
            $tipo_embarque = 'INDIVIDUAL';

            if ($json_guia['success'] == false) {
                return array('success' => false, "message" => array("message" => $json_guia['message']['message']));
            }

            $json_envio = json_encode($json_guia['json']);
            $resp = $this->enviar_json_a_api($json_envio);
            $r = json_decode($resp, true);
            $insercion = $this->insert_guia($json_guia['json'], $r);
            $this->codQRguias($insercion['id'], $r[0]['link_guia']);
            $this->insert_detalle_guia($json_guia['json'], $insercion);
            $link_guia_remision = URL . "encomienda/impresion/guia_remision/" . $insercion['id'];

            // Actualizando el estado de las encomiendas por IDs
            $query = $this->db->connect()->prepare("
            UPDATE encomienda SET estado='EN TRANSITO', id_terminal_actual = 0 WHERE id_encomienda IN ($id_encomiendas)");
            $query->execute();

            //Creacion del registro en la tabla embarcaciones
            $query = $this->db->connect()->prepare("
                        INSERT INTO embarcaciones (id_programacion, origen, destino, id_usuario, tipo)
                        VALUES (:id_programacion, :origen, :destino, :id_usuario, :tipo)");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->bindParam(":origen", $this->id_terminal_sesion);
            $query->bindParam(":destino", $data["id_destino"]);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion);
            $query->bindParam(":tipo", $tipo_embarque);
            //Recuparar el id de la embarcacion
            $query->execute();
            $id_embarcacion = $this->db->connect()->lastInsertId();

            //Hacer iteracion para insertar los id_encomienda y id_embarcacion en la tabla historial_embarcaciones
            $id_encomiendas = explode(",", $id_encomiendas);
            foreach ($id_encomiendas as $id_encomienda) {
                $query = $this->db->connect()->prepare("
                        INSERT INTO historial_embarcaciones (encomienda_id, embarcacion_id)
                        VALUES (:encomienda_id, :embarcacion_id)");
                $query->bindParam(":encomienda_id", $id_encomienda);
                $query->bindParam(":embarcacion_id", $id_embarcacion);
                $query->execute();
            }

            // Obteniendo el id_dt_venta de cada encomienda
            $query = $this->db->connect()->prepare("
            SELECT id_dt_venta
            FROM dt_venta
            WHERE id_encomienda = :id_encomienda
            ");
            $query->bindParam(":id_encomienda", $data["id_encomienda"]);
            $query->execute();
            $id_dt_venta = $query->fetch(PDO::FETCH_ASSOC)['id_dt_venta'];

            // Actualizando el id_programación por IDs dt_venta de cada encomienda
            $query = $this->db->connect()->prepare("
            UPDATE dt_venta SET id_programacion=:id_programacion WHERE id_dt_venta IN ($id_dt_venta)");
            $query->bindParam(":id_programacion", $data["id_programacion"]);
            $query->execute();

            $link_reporte = URL . "encomienda/impresion/embarque/" . $data["id_programacion"] . "/" . $id_embarcacion;
            return [
                'success' => true,
                "message" => [
                    "message" => "Productos embarcados con éxito",
                    "links" => [
                        ['nombre' => 'Embarque', 'link' => $link_reporte],
                        ['nombre' => 'Guia Remision', 'link' => $link_guia_remision]
                    ]
                ]
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
                default:
                    return array('success' => false, "message" => array("message" => "Error en el servidor."));
                    break;
            }
        }
    }

    public function registrar_receptor($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            UPDATE encomienda SET id_destinatario=:id_receptor 
            WHERE id_encomienda = :id_encomienda");
            $query->bindParam(":id_encomienda", $data["id_encomienda"]);
            $query->bindParam(":id_receptor", $data["id_receptor"]);
            $query->execute();
            return ['success' => true, "message" => "Se registro al receptor."];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Error en el servidor."];
                    break;
                default:
                    return ['success' => false, "message" => "Error en el servidor."];
                    break;
            }
        }
    }

    public function consultar_cuentaD()
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("
            SELECT nro_cuenta_BN
            FROM empresa
            WHERE id_empresa = :id_empresa
            LIMIT 1
            ");

            $query->bindParam(':id_empresa', $this->id_empresa_sesion, PDO::PARAM_INT);
            $query->execute();

            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data || empty($data['nro_cuenta_BN'])) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la cuenta de depósito de detracciones.'
                ];
            }

            return [
                'success' => true,
                'message' => $data['nro_cuenta_BN']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en el servidor.'
            ];
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

            $id_empresa = $this->id_empresa_sesion ?? 1;
            $query->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $query->execute();

            $empresa = $query->fetch(PDO::FETCH_ASSOC);

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

            // Construir la ruta absoluta del logo
            $logo_absolute_path = '';
            $logo_exists = false;

            if (!empty($empresa['logo'])) {

                $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin/' . $empresa['logo'];

                if (file_exists($logo_absolute_path)) {
                    $logo_exists = true;
                } else {
                    // Intenta con otra ruta posible
                    $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin' . $empresa['logo'];
                    $logo_exists = file_exists($logo_absolute_path);
                }
            }

            // Agregar los campos calculados
            $empresa['logo_exists'] = $logo_exists;
            $empresa['logo_absolute_path'] = $logo_exists ? $logo_absolute_path : '';

            return $empresa;
        } catch (PDOException $e) {
            error_log("Error en getEmpresaInfo: " . $e->getMessage());
            return [
                'id_empresa' => $this->id_empresa_sesion ?? 1,
                'nombre' => 'EMPRESA DE TRANSPORTES',
                'ruc' => '',
                'direccion' => '',
                'telefono' => '',
                'logo' => '',
                'logo_absolute_path' => '',
                'logo_exists' => false
            ];
        }
    }

    public function getNombreTerminal($id_terminal)
    {
        try {
            if (empty($id_terminal)) {
                return '';
            }

            $query = $this->db->connect()->prepare("
            SELECT nombre 
            FROM terminal 
            WHERE id_terminal = :id_terminal
            LIMIT 1
        ");

            $query->bindParam(':id_terminal', $id_terminal, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['nombre'] : $id_terminal;
        } catch (PDOException $e) {
            error_log("Error en getNombreTerminal: " . $e->getMessage());
            return $id_terminal;
        }
    }

    // Métodos para exportación a Excel - Agregar al final de la clase EncomiendaModel

    public function getDataExportComprobante($fecha_inicio, $fecha_fin, $tipo, $origen, $destino, $estado_envio, $estado_venta, $search = '')
    {
        try {
            $where = ["v.id_tp_comprobante IN (1,3)", "tp_s.descripcion = 'ENCOMIENDA'"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($tipo)) {
                $where[] = "v.id_tp_comprobante = :tipo";
                $params[':tipo'] = $tipo;
            }

            if (!empty($origen)) {
                $where[] = "e.id_terminal_origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "e.id_terminal_destino = :destino";
                $params[':destino'] = $destino;
            }
            // Filtro por estado de envío
            if (!empty($estado_envio)) {

                $estadoEnvio = intval($estado_envio);

                switch ($estadoEnvio) {

                    case 1:
                        $where[] = "e.estado = 'ENTREGADO'";
                        break;

                    case 2:
                        $where[] = "e.estado = 'EN DESTINO'";
                        break;

                    case 3:
                        $where[] = "dt_v.salida = 1
                                    AND e.estado != 'ENTREGADO'";
                        break;

                    case 4:
                        $where[] = "e.estado = 'EN ORIGEN'";
                        break;

                    case 5:
                        $where[] = "e.estado = 'EN TRANSITO'
                                    AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 6:
                        $where[] = "e.estado = 'MAL ENVIADO'";
                        break;
                }
            }

            // Filtro por estado de venta
            if (!empty($estado_venta)) {
                $where[] = "TRIM(e.pago) = :estado_venta";
                $params[':estado_venta'] = trim($estado_venta);
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);
                $searchValue = '%' . $search . '%';

                $where[] = "(
                    CONCAT(v.serie, '-', v.correlativo) LIKE :search_numero
                    OR v.fecha_emision LIKE :search_fecha
                    OR CONCAT(c_r.nombres, ' ', c_r.apellidos, ' ', c_r.num_docu) LIKE :search_remitente
                    OR CONCAT(c_d.nombres, ' ', c_d.apellidos, ' ', c_d.num_docu) LIKE :search_destinatario
                    OR t_o.nombre LIKE :search_origen
                    OR t_d.nombre LIKE :search_destino
                    OR e.fecha_salida LIKE :search_salida
                    OR e.estado LIKE :search_estado_envio

                    OR CASE v.envio_sunat
                        WHEN 0 THEN 'PENDIENTE'
                        WHEN 1 THEN 'ACEPTADO'
                        WHEN 2 THEN 'RECHAZADO'
                        WHEN 3 THEN 'ANULADO'
                        ELSE 'DESCONOCIDO'
                    END LIKE :search_sunat

                    OR v.op_gravada LIKE :search_gravado
                    OR v.op_exonerada LIKE :search_exonerado
                    OR v.op_inafecta LIKE :search_inafecto
                    OR v.op_igv LIKE :search_igv
                    OR v.total LIKE :search_total
                    OR e.codigo LIKE :search_tracking
                    OR e.pago LIKE :search_estado_venta
                )";

                $params[':search_numero'] = $searchValue;
                $params[':search_fecha'] = $searchValue;
                $params[':search_remitente'] = $searchValue;
                $params[':search_destinatario'] = $searchValue;
                $params[':search_origen'] = $searchValue;
                $params[':search_destino'] = $searchValue;
                $params[':search_salida'] = $searchValue;
                $params[':search_estado_envio'] = $searchValue;
                $params[':search_sunat'] = $searchValue;
                $params[':search_gravado'] = $searchValue;
                $params[':search_exonerado'] = $searchValue;
                $params[':search_inafecto'] = $searchValue;
                $params[':search_igv'] = $searchValue;
                $params[':search_total'] = $searchValue;
                $params[':search_tracking'] = $searchValue;
                $params[':search_estado_venta'] = $searchValue;
            }

            // Filtro por terminal según permisos
            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $where[] = "(e.id_terminal_origen = {$this->id_terminal_sesion} OR e.id_terminal_destino = {$this->id_terminal_sesion})";
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            v.id_venta,
            v.serie,
            v.correlativo,
            CONCAT(v.serie, '-', v.correlativo) AS numero_comprobante,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') AS fecha_emision,
            DATE_FORMAT(v.fecha_emision, '%H:%i:%s') AS hora_emision,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            tp_dcr.descripcion AS remitente_tipo_doc,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            tp_dcd.descripcion AS destinatario_tipo_doc,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            e.fecha_salida,
            e.estado AS estado_envio,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.op_igv,
            v.total,
            CASE v.envio_sunat
                WHEN 0 THEN 'PENDIENTE'
                WHEN 1 THEN 'ACEPTADO'
                WHEN 2 THEN 'RECHAZADO'
                WHEN 3 THEN 'ANULADO'
                ELSE 'DESCONOCIDO'
            END AS estado_sunat,
            e.codigo AS tracking,
            e.pago AS estado_venta,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario_registro
        FROM venta v
        LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
        LEFT JOIN tp_docu tp_dcr ON tp_dcr.id_tp_docu = c_r.id_tp_docu
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
        LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
        LEFT JOIN tp_docu tp_dcd ON tp_dcd.id_tp_docu = c_d.id_tp_docu
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
        LEFT JOIN usuario u ON u.id_usuario = v.id_sesionpersonal
        WHERE $whereClause
        ORDER BY v.fecha_emision DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportComprobante: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportNotaVenta($fecha_inicio, $fecha_fin, $origen, $destino, $estado_envio, $estado_venta, $search = '')
    {
        try {
            $where = ["v.id_tp_comprobante = 2", "tp_s.descripcion = 'ENCOMIENDA'"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($origen)) {
                $where[] = "e.id_terminal_origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "e.id_terminal_destino = :destino";
                $params[':destino'] = $destino;
            }

            // Filtro por estado de envío
            if (!empty($estado_envio)) {

                $estadoEnvio = intval($estado_envio);

                switch ($estadoEnvio) {

                    case 1:
                        $where[] = "e.estado = 'ENTREGADO'";
                        break;

                    case 2:
                        $where[] = "e.estado = 'EN DESTINO'";
                        break;

                    case 3:
                        $where[] = "dt_v.salida = 1
                                    AND e.estado != 'ENTREGADO'";
                        break;

                    case 4:
                        $where[] = "e.estado = 'EN ORIGEN'";
                        break;

                    case 5:
                        $where[] = "e.estado = 'EN TRANSITO'
                                    AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 6:
                        $where[] = "e.estado = 'MAL ENVIADO'";
                        break;
                }
            }

            // Filtro por estado de venta
            if (!empty($estado_venta)) {
                $where[] = "TRIM(e.pago) = :estado_venta";
                $params[':estado_venta'] = trim($estado_venta);
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);

                $where[] = "CONCAT_WS(' ',
                CONCAT(v.serie, '-', v.correlativo),
                DATE_FORMAT(v.fecha_emision, '%d/%m/%Y'),
                c_r.nombres,
                c_r.apellidos,
                c_r.num_docu,
                c_d.nombres,
                c_d.apellidos,
                c_d.num_docu,
                t_o.nombre,
                t_d.nombre,
                DATE_FORMAT(e.fecha_salida, '%d/%m/%Y'),
                e.estado,
                v.total,
                e.pago,
                e.codigo,
                CONCAT(pa.serie, '-', pa.correlativo)
            ) LIKE :search";

                $params[':search'] = '%' . $search . '%';
            }

            // Filtro por terminal según permisos
            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $where[] = "(e.id_terminal_origen = {$this->id_terminal_sesion} OR e.id_terminal_destino = {$this->id_terminal_sesion})";
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            v.id_venta,
            v.serie,
            v.correlativo,
            CONCAT(v.serie, '-', v.correlativo) AS numero_comprobante,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') AS fecha_emision,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            tp_dcr.descripcion AS remitente_tipo_doc,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            tp_dcd.descripcion AS destinatario_tipo_doc,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            e.fecha_salida,
            e.estado AS estado_envio,
            v.total,
            e.pago AS estado_venta,
            e.codigo AS tracking,
            CONCAT(pa.serie, '-', pa.correlativo) AS comprobante_pago,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario_registro
        FROM venta v
        LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
        LEFT JOIN tp_docu tp_dcr ON tp_dcr.id_tp_docu = c_r.id_tp_docu
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
        LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
        LEFT JOIN tp_docu tp_dcd ON tp_dcd.id_tp_docu = c_d.id_tp_docu
        LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
        LEFT JOIN venta pa ON pa.id_venta = v.id_pago
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN usuario u ON u.id_usuario = v.id_sesionpersonal
        WHERE $whereClause
        ORDER BY v.fecha_emision DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportNotaVenta: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportGuiasT($fecha_inicio, $fecha_fin, $origen, $destino, $estado_envio, $estado_venta, $search = '')
    {
        try {
            $where = ["v.id_tp_comprobante = 31", "tp_s.descripcion = 'ENCOMIENDA'"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($origen)) {
                $where[] = "e.id_terminal_origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "e.id_terminal_destino = :destino";
                $params[':destino'] = $destino;
            }

            // Filtro por estado de envío
            if (!empty($estado_envio)) {

                $estadoEnvio = intval($estado_envio);

                switch ($estadoEnvio) {

                    case 1:
                        $where[] = "e.estado = 'ENTREGADO'";
                        break;

                    case 2:
                        $where[] = "e.estado = 'EN DESTINO'";
                        break;

                    case 3:
                        $where[] = "dt_v.salida = 1
                                    AND e.estado != 'ENTREGADO'";
                        break;

                    case 4:
                        $where[] = "e.estado = 'EN ORIGEN'";
                        break;

                    case 5:
                        $where[] = "e.estado = 'EN TRANSITO'
                                    AND (dt_v.salida IS NULL OR dt_v.salida != 1)";
                        break;

                    case 6:
                        $where[] = "e.estado = 'MAL ENVIADO'";
                        break;
                }
            }

            // Filtro por estado de venta
            if (!empty($estado_venta)) {
                $where[] = "TRIM(e.pago) = :estado_venta";
                $params[':estado_venta'] = trim($estado_venta);
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);

                $where[] = "CONCAT_WS(' ',
                    CONCAT(v.serie, '-', v.correlativo),
                    DATE_FORMAT(v.fecha_emision, '%d/%m/%Y'),
                    c_r.nombres,
                    c_r.apellidos,
                    c_r.num_docu,
                    c_d.nombres,
                    c_d.apellidos,
                    c_d.num_docu,
                    t_o.nombre,
                    t_d.nombre,
                    DATE_FORMAT(e.fecha_salida, '%d/%m/%Y'),
                    CASE
                        WHEN dt_v.salida = 1 AND e.estado != 'ENTREGADO' THEN 'EN RUTA'
                        ELSE e.estado
                    END,
                    v.total,
                    e.pago,
                    e.codigo,
                    CONCAT(pa.serie, '-', pa.correlativo),
                    gr.codigo_sunat
                ) LIKE :search";

                $params[':search'] = '%' . $search . '%';
            }

            // Filtro por terminal según permisos
            if ($this->tp_usuario_sesion != 1 && $this->tp_usuario_sesion != 2) {
                $where[] = "(e.id_terminal_origen = {$this->id_terminal_sesion} OR e.id_terminal_destino = {$this->id_terminal_sesion})";
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            v.id_venta,
            v.serie,
            v.correlativo,
            CONCAT(v.serie, '-', v.correlativo) AS numero_comprobante,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') AS fecha_emision,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            e.fecha_salida,
            CASE
                WHEN dt_v.salida = 1 AND e.estado != 'ENTREGADO' THEN 'EN RUTA'
                ELSE e.estado
            END AS estado_envio,
            v.total,
            e.pago AS estado_venta,
            e.codigo AS tracking,
            CONCAT(pa.serie, '-', pa.correlativo) AS comprobante_pago,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario_registro,
            gr.id AS id_guia_remision,
            gr.codigo_sunat AS estado_sunat
        FROM venta v
        LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
        LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
        LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
        LEFT JOIN venta pa ON pa.id_venta = v.id_pago
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN guia_remision gr ON gr.id_venta = v.id_venta
        LEFT JOIN usuario u ON u.id_usuario = v.id_sesionpersonal
        WHERE $whereClause
        ORDER BY v.fecha_emision DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportGuiasT: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportEmbarcaciones($fecha_inicio, $fecha_fin, $origen, $destino, $id_vehiculo, $search = '')
    {
        try {
            $where = ["1=1"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(em.fecha_registro) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($origen)) {
                $where[] = "em.origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "em.destino = :destino";
                $params[':destino'] = $destino;
            }

            if (!empty($id_vehiculo)) {
                $where[] = "p.id_vehiculo = :id_vehiculo";
                $params[':id_vehiculo'] = $id_vehiculo;
            }

            // Filtro de búsqueda general
            if (!empty($search)) {

                $search = trim($search);

                $searchColumns = [
                    "em.id",
                    "em.estado",
                    "t_o.nombre",
                    "t_d.nombre",
                    "DATE(em.fecha_registro)",
                    "TIME(em.fecha_registro)",
                    "CONCAT(u.nombres, ' ', u.apellidos)",
                    "u.nombres",
                    "u.apellidos",
                    "v.placa",
                    "con.nombres",
                    "con.apellidos",
                    "CONCAT(con.nombres, ' ', con.apellidos)",
                    "dt_c.licencia"
                ];

                $searchConditions = [];

                foreach ($searchColumns as $i => $column) {

                    $param = ":search_" . $i;
                    $searchConditions[] = "$column LIKE $param";
                    $params[$param] = '%' . $search . '%';
                }

                $where[] = "(" . implode(" OR ", $searchConditions) . ")";
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 1 || $this->tp_usuario_sesion == 2)) {
                $where[] = "em.id_usuario = :id_usuario";
                $params[':id_usuario'] = $this->id_usuario_sesion;
            }

            $whereClause = implode(' AND ', $where);

            // DEBUG: Ver la consulta SQL
            error_log("=== DEBUG MODELO: getDataExportEmbarcaciones ===");
            error_log("WHERE clause: " . $whereClause);
            error_log("Params: " . print_r($params, true));

            $sql = "SELECT
            em.id,
            DATE_FORMAT(em.fecha_registro, '%d/%m/%Y') AS fecha,
            DATE_FORMAT(em.fecha_registro, '%H:%i:%s') AS hora,
            t_o.nombre AS origen_nombre,
            t_d.nombre AS destino_nombre,
            v.placa AS vehiculo_placa,
            CONCAT(con.nombres, ' ', con.apellidos) AS conductor,
            con.num_docu AS conductor_dni,
            CASE 
                WHEN em.tipo_programacion = 'salida' THEN dt_c_s.licencia
                ELSE dt_c.licencia
            END AS licencia,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            (SELECT COUNT(*) FROM historial_embarcaciones he WHERE he.embarcacion_id = em.id) AS total_encomiendas
        FROM embarcaciones em
        LEFT JOIN programacion p ON em.id_programacion = p.id_programacion
        LEFT JOIN terminal t_o ON em.origen = t_o.id_terminal
        LEFT JOIN terminal t_d ON em.destino = t_d.id_terminal
        LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
        LEFT JOIN usuario u ON em.id_usuario = u.id_usuario
        LEFT JOIN usuario con ON p.id_conductor = con.id_usuario
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = p.id_conductor
        LEFT JOIN programacion_salida p_s ON em.id_programacion_salida = p_s.id_salida
        LEFT JOIN dt_conductor dt_c_s ON dt_c_s.id_usuario = p_s.id_conductor
        WHERE $whereClause
        ORDER BY em.id DESC";

            error_log("SQL: " . $sql);

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
                error_log("Binding: $key = $value");
            }

            $query->execute();
            $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

            // DEBUG: Resultados
            error_log("Total resultados: " . count($resultados));
            if (count($resultados) > 0) {
                error_log("Primer resultado: " . print_r($resultados[0], true));
            } else {
                // Consulta de prueba para ver si hay datos en la tabla
                $test_query = $this->db->connect()->query("SELECT COUNT(*) as total FROM embarcaciones");
                $test_result = $test_query->fetch(PDO::FETCH_ASSOC);
                error_log("Total en tabla embarcaciones: " . $test_result['total']);
            }

            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en getDataExportEmbarcaciones: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportEmbarcacionGrupal($fecha_inicio, $fecha_fin, $origen, $destino, $id_vehiculo, $search = '')
    {
        try {
            $where = ["1=1"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(ge.fecha) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($origen)) {
                $where[] = "p.id_terminal_origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "p.id_terminal_destino = :destino";
                $params[':destino'] = $destino;
            }

            if (!empty($id_vehiculo)) {
                $where[] = "p.id_vehiculo = :id_vehiculo";
                $params[':id_vehiculo'] = $id_vehiculo;
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);

                $where[] = "CONCAT_WS(' ',
                    ge.id,
                    DATE_FORMAT(ge.fecha, '%d/%m/%Y'),
                    DATE_FORMAT(ge.fecha, '%H:%i:%s'),
                    t_o.nombre,
                    t_d.nombre,
                    v.placa,
                    con.nombres,
                    con.apellidos,
                    con.num_docu,
                    dt_c.licencia,
                    CONCAT(u.nombres, ' ', u.apellidos),
                    (
                        SELECT COUNT(*)
                        FROM embarcaciones emb2
                        WHERE emb2.id_grupo_embarcacion = ge.id
                    )
                ) LIKE :search";

                $params[':search'] = '%' . $search . '%';
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1)) {
                $where[] = "ge.id_usuario = {$this->id_usuario_sesion}";
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            ge.id AS id_grupo,
            DATE_FORMAT(ge.fecha, '%d/%m/%Y') AS fecha,
            DATE_FORMAT(ge.fecha, '%H:%i:%s') AS hora,
            t_o.nombre AS origen_nombre,
            t_d.nombre AS destino_nombre,
            v.placa AS vehiculo_placa,
            dt_c.licencia AS licencia,
            CONCAT(con.nombres, ' ', con.apellidos) AS conductor,
            con.num_docu AS conductor_dni,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            COUNT(DISTINCT emb.id) AS total_embarcaciones,
            (
                SELECT COUNT(*) 
                FROM embarcaciones emb2 
                WHERE emb2.id_grupo_embarcacion = ge.id
            ) AS total_encomiendas
        FROM grupo_embarcaciones ge
        LEFT JOIN usuario u ON ge.id_usuario = u.id_usuario
        LEFT JOIN embarcaciones emb ON ge.id = emb.id_grupo_embarcacion
        LEFT JOIN programacion p ON emb.id_programacion = p.id_programacion
        LEFT JOIN terminal t_o ON p.id_terminal_origen = t_o.id_terminal
        LEFT JOIN terminal t_d ON p.id_terminal_destino = t_d.id_terminal
        LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
        LEFT JOIN usuario con ON p.id_conductor = con.id_usuario
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = p.id_conductor
        WHERE $whereClause
        GROUP BY ge.id
        ORDER BY ge.id DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportEmbarcacionGrupal: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportDesembarque($fecha_inicio, $fecha_fin, $origen, $destino, $id_vehiculo, $search = '')
    {
        try {
            $where = ["1=1"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(ds.fecha_registro) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($origen)) {
                $where[] = "p.id_terminal_origen = :origen";
                $params[':origen'] = $origen;
            }

            if (!empty($destino)) {
                $where[] = "ds.destino = :destino";
                $params[':destino'] = $destino;
            }

            if (!empty($id_vehiculo)) {
                $where[] = "p.id_vehiculo = :id_vehiculo";
                $params[':id_vehiculo'] = $id_vehiculo;
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);

                $where[] = "CONCAT_WS(' ',
                    ds.id,
                    DATE_FORMAT(ds.fecha_registro, '%d/%m/%Y'),
                    DATE_FORMAT(ds.fecha_registro, '%H:%i:%s'),
                    t_o.nombre,
                    t_d.nombre,
                    v.placa,
                    con.nombres,
                    con.apellidos,
                    con.num_docu,
                    dt_c.licencia,
                    CONCAT(u.nombres, ' ', u.apellidos),
                    (
                        SELECT COUNT(*)
                        FROM historial_desembarques hd2
                        WHERE hd2.desembarque_id = ds.id
                    )
                ) LIKE :search";

                $params[':search'] = '%' . $search . '%';
            }

            // Filtro según el tipo de usuario
            if (!($this->tp_usuario_sesion == 2 || $this->tp_usuario_sesion == 1)) {
                $where[] = "ds.id_usuario = {$this->id_usuario_sesion}";
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            ds.id,
            DATE_FORMAT(ds.fecha_registro, '%d/%m/%Y') AS fecha,
            DATE_FORMAT(ds.fecha_registro, '%H:%i:%s') AS hora,
            t_o.nombre AS origen_nombre,
            t_d.nombre AS destino_nombre,
            v.placa AS vehiculo_placa,
            CONCAT(con.nombres, ' ', con.apellidos) AS conductor,
            con.num_docu AS conductor_dni,
            dt_c.licencia AS licencia,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            (SELECT COUNT(*) FROM historial_desembarques hd WHERE hd.desembarque_id = ds.id) AS total_encomiendas
        FROM desembarques ds
        LEFT JOIN programacion p ON ds.id_programacion = p.id_programacion
        LEFT JOIN terminal t_o ON p.id_terminal_origen = t_o.id_terminal
        LEFT JOIN terminal t_d ON ds.destino = t_d.id_terminal
        LEFT JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
        LEFT JOIN usuario u ON ds.id_usuario = u.id_usuario
        LEFT JOIN usuario con ON p.id_conductor = con.id_usuario
        LEFT JOIN dt_conductor dt_c ON dt_c.id_usuario = p.id_conductor
        WHERE $whereClause
        ORDER BY ds.id DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportDesembarque: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportGuiasRT($fecha_inicio, $fecha_fin, $partida, $destino, $vehiculo_placa, $search = '')
    {
        try {
            $where = ["1=1"];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $where[] = "DATE(gr.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($partida)) {
                $where[] = "gr.partida_ubigeo = :partida";
                $params[':partida'] = $partida;
            }

            if (!empty($destino)) {
                $where[] = "gr.destino_ubigeo = :destino";
                $params[':destino'] = $destino;
            }

            // Filtro por vehículo
            if (!empty($vehiculo_placa)) {
                $where[] = "gr.vehiculo_placa = :vehiculo_placa";
                $params[':vehiculo_placa'] = trim($vehiculo_placa);
            }

            // Filtro de búsqueda general
            if (!empty($search)) {
                $search = trim($search);

                $where[] = "CONCAT_WS(' ',
                    CONCAT(gr.serie, '-', gr.correlativo),
                    DATE_FORMAT(gr.fecha_emision, '%d/%m/%Y'),
                    gr.vehiculo_placa,
                    gr.conductor_nombres,
                    gr.conductor_apellidos,
                    gr.conductor_nro_doc,
                    gr.conductor_licencia,
                    u_p.depa,
                    u_p.provi,
                    u_p.distri,
                    gr.partida_direccion,
                    u_d.depa,
                    u_d.provi,
                    u_d.distri,
                    gr.destino_direccion,
                    u_r.nombres,
                    u_r.apellidos,
                    u_r.num_docu,
                    gr.peso,
                    CASE gr.codigo_sunat
                        WHEN 1 THEN 'ACEPTADO'
                        WHEN 2 THEN 'RECHAZADO'
                        WHEN 3 THEN 'ANULADO'
                        ELSE 'PENDIENTE'
                    END,
                    gr.mensaje_sunat
                ) LIKE :search";

                $params[':search'] = '%' . $search . '%';
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            gr.id,
            gr.serie,
            gr.correlativo,
            CONCAT(gr.serie, '-', gr.correlativo) AS numero_guia,
            DATE_FORMAT(gr.fecha_emision, '%d/%m/%Y') AS fecha_emision,
            gr.vehiculo_placa,
            gr.conductor_nombres,
            gr.conductor_apellidos,
            gr.conductor_nro_doc AS conductor_dni,
            gr.conductor_licencia,
            u_p.depa AS partida_depa,
            u_p.provi AS partida_provi,
            u_p.distri AS partida_distri,
            CONCAT(u_p.distri, ' - ', u_p.provi, ' - ', u_p.depa) AS partida_ubigeo_completo,
            gr.partida_direccion,
            u_d.depa AS destino_depa,
            u_d.provi AS destino_provi,
            u_d.distri AS destino_distri,
            CONCAT(u_d.distri, ' - ', u_d.provi, ' - ', u_d.depa) AS destino_ubigeo_completo,
            gr.destino_direccion,
            gr.remitente_id,
            u_r.nombres AS remitente_nombres,
            u_r.apellidos AS remitente_apellidos,
            u_r.num_docu AS remitente_doc,
            tp_r.descripcion AS remitente_tipo_doc,
            gr.peso,
            CASE gr.codigo_sunat
                WHEN 1 THEN 'ACEPTADO'
                WHEN 2 THEN 'RECHAZADO'
                WHEN 3 THEN 'ANULADO'
                ELSE 'PENDIENTE'
            END AS estado_sunat,
            gr.mensaje_sunat
        FROM guia_remision gr
        LEFT JOIN ubigeo u_p ON gr.partida_ubigeo = u_p.cod_ubigeo
        LEFT JOIN ubigeo u_d ON gr.destino_ubigeo = u_d.cod_ubigeo
        LEFT JOIN usuario u_r ON gr.remitente_id = u_r.id_usuario
        LEFT JOIN tp_docu tp_r ON tp_r.id_tp_docu = u_r.id_tp_docu
        WHERE $whereClause AND gr.id_venta IS NULL
        ORDER BY gr.id DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportGuiasRT: " . $e->getMessage());
            return [];
        }
    }

    // Métodos auxiliares para obtener listas de terminales y tipos
    public function getTerminalesParaFiltro()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT id_terminal, nombre 
            FROM terminal 
            ORDER BY nombre
        ");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTiposComprobanteParaFiltro()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT id_tp_comprobante, descripcion 
            FROM tp_comprobante 
            WHERE id_tp_comprobante IN (1,2,3)
            ORDER BY id_tp_comprobante
        ");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // En encomiendaModel.php, agrega:

    public function get_allTerminales()
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
            INNER JOIN empresa e ON e.id_empresa = t.id_empresa
            ORDER BY t.nombre
        ");
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
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
            return array('success' => true, "message" => $reply);
        } catch (PDOException $e) {
            return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
        }
    }

    public function consultar_comprobanteSUNAT($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT 
                v.serie,
                v.correlativo,
                v.cod_qr,
                tp_c.codigo
                FROM venta v
                LEFT JOIN tp_comprobante tp_c ON v.id_tp_comprobante = tp_c.id_tp_comprobante
                WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(':id_venta', $data['id_venta'], PDO::PARAM_INT);
            $query->execute();
            $data_venta = $query->fetch(PDO::FETCH_ASSOC);

            $emisor = $this->consult_emisor();

            $cabecera = [
                'tipo_comprobante' => 'CC',
                'tp_comprobante' => $data_venta['codigo'],
                'serie' => $data_venta['serie'],
                'correlativo' => $data_venta['correlativo']
            ];

            $json_envio = [
                'ose' => $emisor['ose'],
                'emisor' => $emisor,
                'cabecera' => $cabecera
            ];
            $rpta_sunat = $this->enviar_json_a_api(json_encode($json_envio));
            $resp = json_decode($rpta_sunat, true);
            if ($data_venta['cod_qr'] == '' && $resp[0]["exito"] === true && $resp[0]["codigo_sunat"] == '0001' && $resp[0]['mensaje'] == 'El comprobante existe y está aceptado.') {
                $this->generar_codQR($data['id_venta']);
            }
            return ['success' => $resp[0]['exito'], "message" => $resp[0]['mensaje']];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Error en el servidor."];
                    break;
                default:
                    return ['success' => false, "message" => "Error en el servidor."];
                    break;
            }
        }
    }

    public function anular_GRT($data, $conn = null)
    {
        $es_dueno_transaccion = ($conn === null);

        try {
            if ($es_dueno_transaccion) {
                $conn = $this->db->connect();
                $conn->beginTransaction();
            }

            date_default_timezone_set('America/Lima');
            $fechaActual = date('Y-m-d');

            $query = $conn->prepare("
                SELECT 
                CONCAT(serie, '-', correlativo) AS numero
                FROM guia_remision
                WHERE id = :id
            ");
            $query->bindParam(":id", $data['id']);
            $query->execute();
            $numero_guia = $query->fetchColumn();

            if (empty($numero_guia)) {
                throw new Exception("No se encontro el numero de guia");
            }

            $texto_obs = 'Anular la guía de remision transportista "' . $numero_guia . '" en el portal SOL de SUNAT';

            $query = $conn->prepare("UPDATE guia_remision SET observacion = :observacion WHERE id = :id");
            $query->bindParam(':id', $data['id']);
            $query->bindParam(':observacion', $texto_obs);
            $query->execute();

            if ($es_dueno_transaccion) {
                $conn->commit();
            }

            return ['success' => true, "message" => "Guía de remisión anulada correctamente."];
        } catch (Exception $e) {
            if ($es_dueno_transaccion && $conn && $conn->inTransaction()) {
                $conn->rollBack();
            }
            if (!$es_dueno_transaccion) {
                throw $e;
            }
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function get_encomiendas_embarque($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT encomienda_id 
                FROM historial_embarcaciones
                WHERE embarcacion_id = :id_embarcacion
            ");
            $query->bindParam(':id_embarcacion', $data['id_embarcacion']);
            $query->execute();
            $historial_enco = $query->fetchAll(PDO::FETCH_COLUMN);

            $placeholders = rtrim(str_repeat('?,', count($historial_enco)), ',');
            $sql_encomiendas = "
                SELECT
                    p.fecha_salida AS fecha_salida,
                    e.id_encomienda AS id_encomienda,
                    v.id_venta,
                    e.id_remitente,
                    r.nombres AS remitente_nombres,
                    r.apellidos AS remitente_apellidos,
                    d.nombres AS destinatario_nombres,
                    d.apellidos AS destinatario_apellidos,
                    v.serie AS venta_serie,
                    v.correlativo AS venta_correlativo,
                    t_o.nombre AS terminal_origen,
                    t_d.nombre AS terminal_destino,
                    p_e.descripcion AS encomienda_producto,
                    p_e.obs AS obs,
                    p_e.cantidad AS encomienda_cantidad,
                    p_e.op_gravada AS encomienda_precio_op_gravada,
                    p_e.op_exonerada AS encomienda_precio_op_exonerada,
                    p_e.op_inafecta AS encomienda_precio_op_inafecta,
                    p_e.serie,
                    p_e.correlativo,
                    p_e.guia_serie,
                    p_e.guia_correlativo,
                    COALESCE(TRIM(UPPER(f_p.descripcion)), 'NO ESPECIFICADO') AS forma_pago,
                    COALESCE(TRIM(UPPER(m_p.descripcion)), 'NO ESPECIFICADO') AS medio_pago,
                    COALESCE(TRIM(UPPER(e.pago)), 'NO ESPECIFICADO') AS estado_pago,
                    v.total AS total,
                    v.id_tp_comprobante AS tp_comprobante,

                    -- NUEVAS COLUMNAS de encomienda
                    e.tp_comprobante_ref,
                    e.serie_ref,
                    e.correlativo_ref,
                    e.ruc_ref,
                    e.guia_serie,
                    e.guia_correlativo,
                    e.guia_ruc,
        
                    -- NUEVAS COLUMNAS de doc_relacionado
                    dr.tp_comprobante AS doc_rel_tp_comprobante,
                    dr.serie AS doc_rel_serie,
                    dr.correlativo AS doc_rel_correlativo,
                    dr.ruc AS doc_rel_ruc,

                    CASE 
                    WHEN v.id_tp_comprobante = 2 THEN 'notaVenta'
                    WHEN v.id_tp_comprobante = 31 THEN 'guiasTransportista'
                    WHEN v.id_tp_comprobante IN (1, 3) THEN 'comprobante'
                    ELSE 'otro'
                    END AS tabla

                FROM dt_venta dt_v
                LEFT JOIN venta v ON v.id_venta = dt_v.id_venta
                LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
                LEFT JOIN producto_encomienda p_e ON p_e.id_encomienda = e.id_encomienda
                LEFT JOIN usuario r ON r.id_usuario = e.id_remitente
                LEFT JOIN usuario d ON d.id_usuario = e.id_destinatario
                LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
                LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
                LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
                LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
                LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
                LEFT JOIN doc_relacionado dr ON dr.id_guia_remision = e.id_encomienda
                WHERE dt_v.id_programacion = ? 
                AND e.id_encomienda IN ($placeholders)
            ";
            $query = $this->db->connect()->prepare($sql_encomiendas);
            $query->bindValue(1, $data['id_programacion'], PDO::PARAM_INT);
            foreach ($historial_enco as $i => $id) {
                $query->bindValue($i + 2, $id, PDO::PARAM_INT);
            }
            $query->execute();
            $data_encomiendas = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data_encomiendas];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function quitar_encomiendas_embarque($data)
    {
        $conn = null;

        try {
            $conn = $this->db->connect();
            $conn->beginTransaction();

            $id_embarcacion = $data['id_embarcacion'];
            $encomiendas = json_decode($data['ids_encomiendas'], true);

            $query_historial = $conn->prepare("
            DELETE FROM historial_embarcaciones 
            WHERE encomienda_id = :encomienda_id 
            AND embarcacion_id  = :embarcacion_id
            ");

            $query_update_venta = $conn->prepare("
            UPDATE dt_venta SET id_programacion = NULL 
            WHERE id_dt_venta = :id_dt_venta
            ");

            $query_update_estado = $conn->prepare("
            UPDATE encomienda SET estado = 'EN ORIGEN' 
            WHERE id_encomienda = :id_encomienda
            ");

            $notificados = [];
            foreach ($encomiendas as $item) {
                $query_historial->bindParam(":encomienda_id", $item['id_encomienda']);
                $query_historial->bindParam(":embarcacion_id", $id_embarcacion);
                $query_historial->execute();

                $query_update_venta->bindParam(":id_dt_venta", $item['id_dt_venta']);
                $query_update_venta->execute();

                $query_update_estado->bindParam(":id_encomienda", $item['id_encomienda']);
                $query_update_estado->execute();
                if (empty($item['id_venta']) || empty($item['tabla']))
                    continue;

                $key = $item['tabla'] . '_' . $item['id_venta'];
                if (isset($notificados[$key]))
                    continue;

                $this->notificarWebSocket("empresa_{$this->uuid_ws_sesion}_encomienda", [
                    "tipo" => "encomienda_cambio",
                    "tabla" => $item['tabla'],
                    "id" => $item['id_venta'],
                    "accion" => "update"
                ]);
                $notificados[$key] = true;
            }

            /*====== ANULAR GUIA ======*/
            $query = $conn->prepare("
            SELECT id FROM guia_remision 
            WHERE id_embarcacion = :id_embarcacion 
            AND codigo_sunat = 1
            ");
            $query->bindParam(":id_embarcacion", $id_embarcacion);
            $query->execute();
            $id_guia = $query->fetchColumn() ?: null;

            if ($id_guia) {
                $anulacion_guia = $this->anular_GRT(['id' => $id_guia], $conn);
                if ($anulacion_guia['success'] == false) {
                    throw new Exception("Error al anular la guia: " . $anulacion_guia['message']);
                }
            }

            /*================================= INSPECCION =================================== */
            $query = $conn->prepare("
            SELECT encomienda_id 
            FROM historial_embarcaciones 
            WHERE embarcacion_id = :id_embarque
            ");
            $query->bindParam(':id_embarque', $id_embarcacion);
            $query->execute();

            $id_encomiendas = array_column($query->fetchAll(PDO::FETCH_ASSOC), 'encomienda_id');

            $anulacion_completa = 0;
            $crear_guia = ['success' => true, 'link_guia' => []];

            if (!empty($id_encomiendas) && !empty($id_guia)) {
                /*====== NUEVA GUIA ======*/
                $data_nueva_guia['id_embarque'] = $id_embarcacion;
                $crear_guia = $this->crear_guia_embarque($data_nueva_guia, $conn);

                if ($crear_guia['success'] === false) {
                    if ($crear_guia['codigo'] == 2) {
                        $query = $conn->prepare("
                        UPDATE embarcaciones
                        SET estado = 'ANULADO'
                        WHERE id = :id_embarcacion
                    ");
                        $query->bindParam(":id_embarcacion", $id_embarcacion);
                        $query->execute();
                        $anulacion_completa = 1;
                    } else {
                        throw new Exception("Error al crear guia: " . $crear_guia['message']);
                    }
                }
            } elseif (!empty($id_encomiendas) && empty($id_guia)) {
                // Caso antes no manejado — quedan encomiendas pero no había guía previa
                // No se genera nueva guía, el embarque continúa sin guía SUNAT
                // Ajusta esta lógica según las reglas de negocio que correspondan

            } else {
                $query = $conn->prepare("   
                UPDATE embarcaciones
                SET estado = 'ANULADO'
                WHERE id = :id_embarcacion
                ");
                $query->bindParam(":id_embarcacion", $id_embarcacion);
                $query->execute();
                $anulacion_completa = 1;
            }

            $conn->commit();

            $links_impresion = [
                ['nombre' => 'Embarque', 'link' => URL . "encomienda/impresion/embarqueH/" . $id_embarcacion],
                $crear_guia['link_guia'][0] ?? ''
            ];

            return [
                'success' => true,
                'message' => [
                    'message' => 'Encomiendas quitados con éxito',
                    'links' => $anulacion_completa != 1 ? $links_impresion : []
                ]
            ];
        } catch (Exception $e) {
            if (isset($conn))
                $conn->rollBack();
            return [
                'success' => false,
                'message' => ['message' => 'Error en el servidor: ' . $e->getMessage()]
            ];
        }
    }

    public function get_registro_comprobante($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT
            v.id_venta,
            v.id_terminal,
            v.id_vendedor,
            v.id_cliente,
            c_r.id_tp_docu AS tp_docu_cliente,
            c_r.nombres AS remitente_nombres,
            c_r.apellidos AS remitente_apellidos,
            c_r.num_docu AS remitente_num_docu,
            c_d.nombres AS destinatario_nombres,
            c_d.apellidos AS destinatario_apellidos,
            c_d.num_docu AS destinatario_num_docu,
            e.fecha_salida AS encomienda_fecha_salida,
            e.estado AS encomienda_estado_envio,
            e.id_terminal_origen,
            e.id_terminal_destino,
            e.id_remitente,
            e.id_destinatario,
            e.e_domicilio,
            e.dir_puntopartida,
            e.dir_puntollegada,
            e.ubi_partida,
            e.ubi_llegada,
            e.pagador_flete,
            e.id_almacen,
            v.obs AS referencia,
            e.pass AS encomienda_pass,
            pf.nombres AS pagante_nombres,
            pf.apellidos AS pagante_apellidos,
            pf.num_docu AS pagante,
            cj.referencia AS caja_chica,
            t_o.nombre AS origen,
            t_d.nombre AS destino,
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
            v.id_pago,
            v.tipo_ruc,
            e.pago AS estado,
            CONCAT(pa.serie, '-', pa.correlativo) AS pago_e,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            dt_v.id_encomienda AS id_encomienda,
            e.tp_comprobante_ref,
            e.serie_ref,
            e.correlativo_ref,
            e.ruc_ref,
            e.guia_serie,
            e.guia_correlativo,
            e.guia_ruc,
            e.link_partida,
            e.link_llegada,
            e.codigo AS tracking

            FROM venta v
            LEFT JOIN usuario c_r ON c_r.id_usuario = v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
            LEFT JOIN usuario c_d ON c_d.id_usuario = e.id_destinatario
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
            LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
            LEFT JOIN venta pa ON pa.id_venta = v.id_pago 
            LEFT JOIN usuario pf ON pf.id_usuario = e.pagador_flete
            LEFT JOIN caja_chica cj ON cj.id_caja_chica = v.id_caja_chica
            WHERE tp_s.descripcion = 'ENCOMIENDA'
              AND v.id_tp_comprobante IN (" . implode(',', array_map('intval', $data["tp_comprobante"])) . ") AND v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $data['id_venta'], PDO::PARAM_INT);
            $query->execute();
            $datos = $query->fetch(PDO::FETCH_ASSOC);
            if (!$datos) {
                return ['success' => false, 'message' => 'Registro no encontrado'];
            }
            $datos["encomienda_pass"] = $this->security->decryption($datos["encomienda_pass"]);

            return ['success' => true, 'data' => $datos];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function marcar_destino_erroneo($data)
    {
        try {

            $conn = $this->db->connect();
            $conn->beginTransaction();

            $queryValidar = $conn->prepare("
               SELECT
                estado,
                id_terminal_destino
               FROM encomienda
               WHERE id_encomienda = :id_encomienda
            ");

            $queryValidar->bindParam(
                ':id_encomienda',
                $data['id_encomienda'],
                PDO::PARAM_INT
            );

            $queryValidar->execute();

            $encomienda = $queryValidar->fetch(PDO::FETCH_ASSOC);

            if (!$encomienda) {
                throw new Exception('No se encontró la encomienda');
            }

            if ($encomienda['estado'] == 'MAL ENVIADO') {

                return [
                    'success' => false,
                    'message' => 'La encomienda ya fue marcada como destino erróneo'
                ];
            }

            // Actualizar estado de la encomienda
            $query = $conn->prepare("
            UPDATE encomienda
            SET
                estado = 'MAL ENVIADO',
                fecha_mal_enviado = NOW(),
                id_terminal_actual = :id_terminal_actual,
                id_usuario_mal_enviado = :id_usuario
            WHERE id_encomienda = :id_encomienda
            ");

            $query->bindParam(":id_encomienda", $data['id_encomienda'], PDO::PARAM_INT);
            $query->bindParam(":id_terminal_actual", $this->id_terminal_sesion, PDO::PARAM_INT);
            $query->bindParam(":id_usuario", $this->id_usuario_sesion, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() <= 0) {
                $conn->rollBack();

                return [
                    'success' => false,
                    'message' => 'No se encontró la encomienda'
                ];
            }

            $observacion = !empty($data['observacion']) ? trim($data['observacion']) : 'Marcado como destino erróneo';

            // Registrar movimiento
            $queryHistorial = $conn->prepare("
            INSERT INTO encomienda_movimiento (
                id_encomienda,
                tipo_movimiento,
                id_terminal_evento,
                id_terminal_destino,
                observacion,
                id_usuario,
                fecha_registro
            )
            VALUES (
                :id_encomienda,
                'DESTINO_ERRONEO',
                :id_terminal_evento,
                :id_terminal_destino,
                :observacion,
                :id_usuario,
                NOW()
            )
            ");

            $queryHistorial->bindParam(":id_encomienda", $data['id_encomienda'], PDO::PARAM_INT);
            $queryHistorial->bindParam(":id_terminal_evento", $this->id_terminal_sesion, PDO::PARAM_INT);
            $queryHistorial->bindParam(":id_terminal_destino", $data['id_terminal_destino'], PDO::PARAM_INT);
            $queryHistorial->bindParam(":observacion", $observacion, PDO::PARAM_STR);
            $queryHistorial->bindParam(":id_usuario", $this->id_usuario_sesion, PDO::PARAM_INT);
            $queryHistorial->execute();
            $conn->commit();

            return [
                'success' => true,
                'message' => 'Encomienda marcada como destino erróneo correctamente'
            ];
        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Ooops... algo salió mal',
                'error' => $e->getMessage()
            ];
        }
    }

    public function buscar_encomienda_d_erroneo($data)
    {
        try {

            $conn = $this->db->connect();

            switch ($data['tipo_busqueda']) {

                case 'tracking':
                    $where = "e.codigo = :tracking";
                    break;

                case 'comprobante':
                    $where = "v.serie = :serie AND v.correlativo = :correlativo";
                    break;

                default:
                    return [
                        'success' => false,
                        'message' => 'Tipo de búsqueda inválido'
                    ];
            }

            $query = $conn->prepare("
            SELECT
                e.id_encomienda,
                e.id_terminal_origen,
                e.id_terminal_destino,
                e.id_terminal_actual,
                e.codigo AS tracking,
                e.estado,

                CONCAT(v.serie, '-', v.correlativo) AS comprobante,

                t_origen.nombre AS origen,
                t_destino.nombre AS destino,

                CASE
                    WHEN t_actual.nombre IS NULL THEN 'EN TRANSITO'
                    ELSE t_actual.nombre
                END AS terminal_actual

            FROM encomienda e
            INNER JOIN dt_venta dtv ON dtv.id_encomienda = e.id_encomienda
            INNER JOIN venta v ON v.id_venta = dtv.id_venta
            LEFT JOIN terminal t_origen ON t_origen.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal t_destino ON t_destino.id_terminal = e.id_terminal_destino
            LEFT JOIN terminal t_actual ON t_actual.id_terminal = e.id_terminal_actual
            WHERE {$where}
            ORDER BY e.id_encomienda DESC
            LIMIT 1
            ");

            if ($data['tipo_busqueda'] == 'tracking') {
                $query->bindParam(':tracking', $data['tracking'], PDO::PARAM_STR);
            } else {
                $query->bindParam(':serie', $data['serie'], PDO::PARAM_STR);
                $query->bindParam(':correlativo', $data['correlativo'], PDO::PARAM_STR);
            }

            $query->execute();

            $encomienda = $query->fetch(PDO::FETCH_ASSOC);

            if (!$encomienda) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la encomienda'
                ];
            }

            // Validaciones mínimas
            if ($encomienda['estado'] == 'MAL ENVIADO') {
                return [
                    'success' => false,
                    'message' => 'La encomienda ya fue marcada como destino erróneo'
                ];
            }

            if (in_array($encomienda['estado'], ['ENTREGADO', 'CANCELADO'])) {
                return [
                    'success' => false,
                    'message' => 'La encomienda no puede ser marcada como destino erróneo'
                ];
            }

            return [
                'success' => true,
                'data' => $encomienda
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => 'Ooops... algo salió mal',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtener_timeline_encomienda($data)
    {
        try {

            $conn = $this->db->connect();

            // ==========================
            // Información general
            // ==========================
            $queryInfo = $conn->prepare("
            SELECT
                e.id_encomienda,
                e.codigo AS tracking,
                CONCAT(v.serie,'-',v.correlativo) AS comprobante,
                tor.nombre AS origen,
                tdes.nombre AS destino
            FROM encomienda e
            INNER JOIN dt_venta dt ON dt.id_encomienda = e.id_encomienda
            INNER JOIN venta v ON v.id_venta = dt.id_venta
            LEFT JOIN terminal tor ON tor.id_terminal = e.id_terminal_origen
            LEFT JOIN terminal tdes ON tdes.id_terminal = e.id_terminal_destino
            WHERE e.id_encomienda = :id_encomienda
            LIMIT 1
            ");

            $queryInfo->bindParam(":id_encomienda", $data["id_encomienda"], PDO::PARAM_INT);
            $queryInfo->execute();

            $info = $queryInfo->fetch(PDO::FETCH_ASSOC);

            if (!$info) {
                return [
                    "success" => false,
                    "message" => "No se encontró la encomienda."
                ];
            }

            // ==========================
            // Historial de movimientos
            // ==========================
            $queryMov = $conn->prepare("
            SELECT
                em.tipo_movimiento,
                te.nombre AS terminal_evento,
                td.nombre AS terminal_destino,
                em.observacion,
                DATE_FORMAT(
                    em.fecha_registro,
                    '%d/%m/%Y %H:%i'
                ) AS fecha_registro,
                CONCAT(u.nombres,' ',u.apellidos) AS usuario

            FROM encomienda_movimiento em
            LEFT JOIN terminal te ON te.id_terminal = em.id_terminal_evento
            LEFT JOIN terminal td ON td.id_terminal = em.id_terminal_destino
            LEFT JOIN usuario u ON u.id_usuario = em.id_usuario
            WHERE em.id_encomienda = :id_encomienda
            ORDER BY em.fecha_registro ASC
            ");

            $queryMov->bindParam(":id_encomienda", $data["id_encomienda"], PDO::PARAM_INT);
            $queryMov->execute();

            $movimientos = $queryMov->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "info" => $info,
                "movimientos" => $movimientos
            ];
        } catch (PDOException $e) {

            return [
                "success" => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ];
        }
    }

    public function get_data_rotulo($id_venta)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
        SELECT
            v.serie,
            v.correlativo,
            CONCAT(v.serie,'-',v.correlativo) AS numero,

            e.id_encomienda,
            e.codigo AS tracking,

            o.nombre AS origen,
            d.nombre AS destino,

            CONCAT(dest.nombres,' ',dest.apellidos) AS destinatario,
            CONCAT(rem.nombres,' ',rem.apellidos) AS remitente,
            dest.celular AS telefono,

            tp_d.descripcion AS tipo_documento_dest,
            tp_r.descripcion AS tipo_documento_rem,
            dest.num_docu AS doc_destinatario,
            rem.num_docu AS doc_remitente
        FROM venta v
        INNER JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        INNER JOIN encomienda e ON e.id_encomienda = dt_v.id_encomienda
        LEFT JOIN terminal o ON o.id_terminal = e.id_terminal_origen
        LEFT JOIN terminal d ON d.id_terminal = e.id_terminal_destino
        LEFT JOIN usuario dest ON dest.id_usuario = e.id_destinatario
        LEFT JOIN usuario rem ON rem.id_usuario = e.id_remitente
        LEFT JOIN tp_docu tp_d ON tp_d.id_tp_docu = dest.id_tp_docu
        LEFT JOIN tp_docu tp_r ON tp_r.id_tp_docu = rem.id_tp_docu
        WHERE v.id_venta = :id_venta
        LIMIT 1
        ");

            $query->bindParam(":id_venta", $id_venta, PDO::PARAM_INT);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return [
                    "success" => false,
                    "message" => "No se encontró la encomienda."
                ];
            }

            // ── Productos ──────────────────────────────────────
            $query = $conn->prepare("
        SELECT
            id_producto_encomienda,
            descripcion,
            cantidad,
            precio,
            rotulado,
            obs
        FROM producto_encomienda
        WHERE id_encomienda = :id_encomienda
        ORDER BY id_producto_encomienda ASC
        ");
            $query->bindParam(":id_encomienda", $data["id_encomienda"], PDO::PARAM_INT);
            $query->execute();
            $productos = $query->fetchAll(PDO::FETCH_ASSOC);

            $data["productos"] = $productos;

            // ── Expandir rótulos (1 por unidad de cada producto) ──
            $rotulos = [];
            foreach ($productos as $producto) {
                for ($i = 1; $i <= (int) $producto["cantidad"]; $i++) {
                    $rotulos[] = [
                        "descripcion" => $producto["descripcion"],
                        "unidad" => $i,                        // ej: 1, 2
                        "cantidad" => (int) $producto["cantidad"], // ej: 2
                    ];
                }
            }

            $data["rotulos"] = $rotulos;
            $data["total_rotulos"] = count($rotulos);

            // ── Logo empresa ───────────────────────────────────
            $query = $conn->prepare("SELECT logo FROM empresa LIMIT 1");
            $query->execute();
            $data["logo_empresa"] = $query->fetchColumn();

            return [
                "success" => true,
                "data" => $data
            ];
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ];
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
