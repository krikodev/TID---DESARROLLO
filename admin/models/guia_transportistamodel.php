<?php

class Guia_transportistaModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_dataTable($data)
    {
        try {
            // Obtener conexión
            $conn = $this->db->connect();

            // Parámetros para la consulta
            $params = [];

            // Construir la consulta base
            $baseQuery = "FROM venta v
            LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
            LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
            LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
            LEFT JOIN guia_remision gr ON gr.id_venta = v.id_venta
            LEFT JOIN venta pa ON pa.id_venta = v.id_pago 
            WHERE v.id_tp_comprobante IN (31)";

            // APLICAR FILTROS ADICIONALES
            $filtros = [];

            // Filtro por fecha inicio
            if (!empty($data['filtro_fecha_inicio'])) {
                $baseQuery .= " AND DATE(v.fecha_emision) >= :fecha_inicio";
                $filtros[':fecha_inicio'] = $data['filtro_fecha_inicio'];
            }

            // Filtro por fecha fin
            if (!empty($data['filtro_fecha_fin'])) {
                $baseQuery .= " AND DATE(v.fecha_emision) <= :fecha_fin";
                $filtros[':fecha_fin'] = $data['filtro_fecha_fin'];
            }

            // Filtro por estado
            if (!empty($data['filtro_estado'])) {
                $baseQuery .= " AND v.estado = :estado";
                $filtros[':estado'] = $data['filtro_estado'];
            }

            // Obtener el total de registros sin filtros de búsqueda
            $sqlTotal = "SELECT COUNT(*) as total " . $baseQuery;
            $stmtTotal = $conn->prepare($sqlTotal);

            // Aplicar los filtros de fecha y estado al total
            foreach ($filtros as $key => $value) {
                $stmtTotal->bindValue($key, $value);
            }

            $stmtTotal->execute();
            $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

            // Construir la parte de búsqueda
            $searchValue = $data['search']['value'] ?? '';
            $searchQuery = "";

            if (!empty($searchValue)) {
                $searchColumns = [
                    "v.fecha_emision",
                    "CONCAT(c.nombres, ' ',c.apellidos)",
                    "CONCAT(c.nombres, ' ', c.apellidos, ' ', c.num_docu)",
                    "c.nombres",
                    "c.apellidos",
                    "v.serie",
                    "v.correlativo",
                    "CONCAT(v.serie, '-', v.correlativo)",
                    "tp_m.codigo",
                    "v.total",
                    "v.estado"
                ];

                $searchQuery = " AND (";
                foreach ($searchColumns as $index => $column) {
                    if ($index > 0)
                        $searchQuery .= " OR ";
                    $searchQuery .= "$column LIKE :search";
                }
                $searchQuery .= ")";
                $params[':search'] = "%$searchValue%";
            }

            // Consulta para registros filtrados por búsqueda
            $sqlFiltered = "SELECT COUNT(*) as total " . $baseQuery . $searchQuery;
            $stmtFiltered = $conn->prepare($sqlFiltered);

            // Aplicar los filtros de fecha y estado
            foreach ($filtros as $key => $value) {
                $stmtFiltered->bindValue($key, $value);
            }

            // Aplicar el parámetro de búsqueda si existe
            if (!empty($searchValue)) {
                $stmtFiltered->bindValue(':search', "%$searchValue%");
            }

            $stmtFiltered->execute();
            $filteredRecords = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // Campos a seleccionar
            $selectFields = "
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
            tp_m.codigo AS tp_moneda_codigo,
            tp_m.descripcion AS tp_moneda,
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
            v.fecha_emision,
            v.fecha_registro,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            v.id_pago,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            tp_s.descripcion AS tp_servicio,
            gr.id AS id_guia_remision,
            gr.xml AS guia_xml,
            gr.cdr AS guia_cdr,
            gr.codigo_sunat AS codigo_sunat_guia,
            CONCAT(pa.serie, '-', pa.correlativo) AS pago_e
        ";

            // Paginación
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;

            // Consulta principal con paginación
            $sqlData = "SELECT $selectFields " . $baseQuery . $searchQuery . " ORDER BY v.fecha_emision DESC LIMIT :start, :length";
            $stmtData = $conn->prepare($sqlData);

            // Aplicar los filtros de fecha y estado
            foreach ($filtros as $key => $value) {
                $stmtData->bindValue($key, $value);
            }

            // Aplicar el parámetro de búsqueda si existe
            if (!empty($searchValue)) {
                $stmtData->bindValue(':search', "%$searchValue%");
            }

            // Aplicar parámetros de paginación (como enteros)
            $stmtData->bindValue(':start', (int) $start, PDO::PARAM_INT);
            $stmtData->bindValue(':length', (int) $length, PDO::PARAM_INT);

            $stmtData->execute();
            $results = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => intval($totalRecords),
                "recordsFiltered" => intval($filteredRecords),
                "data" => $results
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

    public function deudasxpagar($data)
    {
        $conn = $this->db->connect();
        $id_cliente = $data['id_cliente'] ?? 0;

        $query = $conn->prepare("
        SELECT 
        CONCAT(v.serie, '-', v.correlativo) AS numero_guia,
        DATE(v.fecha_emision) AS fecha_emision,
        v.total AS monto,
        dtv.id_encomienda
        FROM venta v
        LEFT JOIN dt_venta dtv ON dtv.id_venta = v.id_venta
        LEFT JOIN encomienda e ON e.id_encomienda = dtv.id_encomienda
        WHERE v.id_cliente = :id_cliente AND id_tp_comprobante IN (2,31)  
        AND e.pago = 'PAGO EN BLOQUE' AND v.estado != 'PAGADO'
        ");
        $query->bindParam(":id_cliente", $id_cliente);
        $query->execute();
        $notas = $query->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($notas)) {
            foreach ($notas as &$nota) {

                $q2 = $this->db->connect()->prepare("
                    SELECT
                        p_e.descripcion AS producto,
                        p_e.precio AS producto_precio, 
                        p_e.rotulado AS producto_rotulado,
                        p_e.obs AS producto_obs
                    FROM producto_encomienda p_e 
                    WHERE p_e.id_encomienda = :id_encomienda
                ");
                $q2->bindParam(":id_encomienda", $nota['id_encomienda'], PDO::PARAM_INT);
                $q2->execute();
                $nota['detalle'] = $q2->fetchAll(PDO::FETCH_ASSOC);
            }
            return ['success' => true, 'data' => $notas];
        } else {
            return ['success' => false, 'data' => []];
        }
    }

    public function pagar_guias_bloque($data)
    {
        try {

            $id_serie = $data['serie'];
            $data['serie_venta'] = $data['serie'];
            $correlativo = $this->get_correlativo($data);
            $data_ids_e = json_decode($data['ids_encomiendas']);
            $ids_encomiendas = implode(',', $data_ids_e);
            $op_gravadas = 0.00;
            $op_exoneradas = 0.00;
            $op_inafectas = 0.00;
            $op_igv = 0.00;
            $op_total = 0.00;
            $productos_comprobante = [];
            $ids_notas = '';
            $fecha_emision = $data['fecha_emision'] . ' ' . date('H:i:s');
            $tp_venta = 4;

            $conn = $this->db->connect();

            $conn->beginTransaction();

            /*================================= Extraccion de data necesario para calcular totales de la venta =================================== */
            $query1 = $conn->prepare("
                SELECT 
                v.total,
                v.op_exonerada,
                v.op_inafecta,
                v.op_gravada,
                v.op_igv,
                v.id_venta,
                e.id_encomienda
                FROM encomienda e
                LEFT JOIN dt_venta dtv ON dtv.id_encomienda = e.id_encomienda
                LEFT JOIN venta v ON v.id_venta = dtv.id_venta
                WHERE e.id_encomienda IN ($ids_encomiendas)
            ");
            $query1->execute();
            $data_encomiendas = $query1->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data_encomiendas as &$e) {

                // Sumar los totales que se usaran en la cabecera
                $op_gravadas += $e['op_gravada'];
                $op_exoneradas += $e['op_exonerada'];
                $op_inafectas += $e['op_inafecta'];
                $op_igv += $e['op_igv'];
                $op_total += $e['total'];

                $ids_notas .= $e['id_venta'] . ',';

                //Poductos de cada nota
                $query = $conn->prepare("
                SELECT 
                  pe.*,
                  um.abreviatura AS unidad_medida
                 FROM producto_encomienda pe
                 LEFT JOIN unidad_medida um ON um.id = pe.id_unidad_medida
                WHERE pe.id_encomienda = :id_encomienda
                ");

                $query->bindParam(":id_encomienda", $e['id_encomienda']);
                $query->execute();

                $productos_comprobante = array_merge(
                    $productos_comprobante,
                    $query->fetchAll(PDO::FETCH_ASSOC)
                );
            }

            $ids_notas = rtrim($ids_notas, ',');

            /*========================== Insertar venta (Comprobante general) ============================= */
            $forma_pago = $data['forma_pago']; // Indica contado
            $tp_moneda = 1; // Indica soles
            $estado_comp = $data['forma_pago'] == '2' ? 'PENDIENTE' : 'PAGADO';
            $query = $conn->prepare("INSERT INTO venta (
                codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
                id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
                correlativo, descuento, op_igv, estado, envio_sunat,
                descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
                op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal,
                id_tp_venta
                )
                VALUES(
                '', :id_terminal, :id_vendedor, :id_cliente, :forma_pago, :id_medio_pago,
                :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=$id_serie),
                :correlativo, '', :igv,  :estado, 0, 
                '', '', '', '', '', :fecha_emision, :op_gravada,
                :op_exonerada, :op_inafecta, :total, '', :fecha_vencimiento, :referencia, :id_sesionpersonal,
                :id_tp_venta
                )");
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
            $query->bindParam(':id_cliente', $data["id_cliente"]);
            $query->bindParam(':forma_pago', $forma_pago);
            $query->bindParam(':id_medio_pago', $data["medio_pago"]);
            $query->bindParam(':id_tp_moneda', $tp_moneda);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':id_caja_chica', $data["caja_chica"]);
            $query->bindParam(':id_serie', $data["serie"]);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':id_serie', $id_serie);
            $query->bindParam(':igv', $op_igv);
            $query->bindParam(':estado', $estado_comp);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':op_gravada', $op_gravadas);
            $query->bindParam(':op_exonerada', $op_exoneradas);
            $query->bindParam(':op_inafecta', $op_inafectas);
            $query->bindParam(':total', $op_total);
            $query->bindParam(':fecha_vencimiento', $fecha_emision);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':referencia', $data['observacion']);
            $query->bindParam(':id_tp_venta', $tp_venta);
            $query->execute();

            $id_comprobante = $conn->lastInsertId();

            if (!$id_comprobante || $id_comprobante == "0") {
                throw new Exception("Error al guardar el comprobante");
            }

            if ($data['forma_pago'] == 2) {
                $numero_cuota = "001";
                $estado_cuota = "N"; // En caso sea pagada se actualizara con P
                $query = $this->db->connect()->prepare("INSERT INTO cuota (
                comprobante_id, numero, importe, fecha_vencimiento, estado)
                VALUES(
                :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                ");
                $query->bindParam(':comprobante_id', $id_comprobante);
                $query->bindParam(':numero', $numero_cuota);
                $query->bindParam(':importe', $data['monto_credito']);
                $query->bindParam(':estado', $estado_cuota);
                $query->bindParam(':fecha_vencimiento', $data['fecha_credito']);
                $query->execute();
            }

            $productos_insert = $this->registrar_detalle($productos_comprobante, $conn, $id_comprobante);
            if (!$productos_insert['success']) {
                throw new Exception("Error en el detalle del comprobante");
            }
            $conn->commit();

            /*================================= SECCION DE ENVIO A API =================================== */

            $data_comprobante = $this->get_data_comprobante_sunat($id_comprobante);
            if (!$data_comprobante['success']) {
                throw new Exception("Error al generar el comprobante para SUNAT");
            }

            $json_envio = json_encode($data_comprobante['message']);
            $sunat = json_decode($this->enviar_json_a_api($json_envio), true);

            if (empty($sunat) || !is_array($sunat)) {
                throw new Exception("Error al generar XML");
            }

            $resp = $sunat[0];
            $resp['id_comprobante'] = $id_comprobante;

            $this->actualizar_venta_sunat($resp, $conn);

            $mensaje_sunat = $resp['mensaje_sunat'];
            $estado_sunat = $resp['estado'];

            $this->generar_codQR($id_comprobante, $conn);

            $this->actualizar_notas_venta($ids_notas, $id_comprobante);

            $link_comprobante = URL . "guia_transportista/impresion/" . 'comprobante/' . $id_comprobante;

            return [
                'success' => true,
                'message' => 'Comprobante generado correctamente',
                'link_comprobante' => $link_comprobante,
                "message_sunat" => $mensaje_sunat,
                "estado_sunat" => $estado_sunat
            ];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (PDOException $e) {
            $conn->rollback();

            if ($e->getCode() == '23000') {
                return ['success' => false, 'message' => 'Registro duplicado'];
            }

            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    public function registrar_detalle($productos, $conn = null, $id_comprobante)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $igv_config = $this->get_igv();
            $item = 0;

            foreach ($productos as $producto) {
                $igv_prod = 0;
                if ($producto['op_gravada'] > 0) {
                    $afectacion = 1;
                    $producto['valor_total'] = $producto['op_gravada'];
                    $producto['precio_u'] = $producto['precio'] / (1 + $igv_config / 100);
                } elseif ($producto['op_exonerada'] > 0) {
                    $afectacion = 2;
                    $producto['precio_u'] = $producto['precio'];
                    $producto['valor_total'] = $producto['op_total'];
                } elseif ($producto['op_inafecta'] > 0) {
                    $afectacion = 3;
                    $producto['precio_u'] = $producto['precio'];
                    $producto['valor_total'] = $producto['op_total'];
                }

                $item++;

                $id_producto = $producto['id_ctg_encomienda'];
                $cantidad = $producto['cantidad'];
                $valor_u = $producto['precio_u'];
                $valor_u_igv = $producto['precio'];
                $valor_total = $producto['valor_total'];
                $precio_total = $producto['valor_total'];
                $igv_total = $producto['op_igv'];
                $afectacion_item = $afectacion;
                $icbper_total = 0.00;
                $porcentaje_igv = $igv_config;
                $factor_icbper = 0.00;
                $tp_producto = 2;
                $id_unidad_medida = $producto['id_unidad_medida'];

                $query = $conn->prepare("
                    INSERT INTO detalle_comprobante
                    (
                      comprobante_id, item, producto_id, cantidad, valor_unitario, 
                      precio_unitario, igv, porcentaje_igv, valor_total, icbper, 
                      factor_icbper, importe_total, afectacion_id, tp_producto, 
                      id_unidad_medida
                    )
                    VALUES (
                      :comprobante_id, :item, :producto_id, :cantidad, :valor_unitario, 
                      :precio_unitario, :igv, :porcentaje_igv, :valor_total, :icbper, 
                      :factor_icbper, :importe_total, :afectacion_id, :tp_producto,
                      :id_unidad_medida
                    )
                ");
                $query->bindParam(':comprobante_id', $id_comprobante);
                $query->bindParam(':item', $item);
                $query->bindParam(':producto_id', $id_producto);
                $query->bindParam(':cantidad', $cantidad);
                $query->bindParam(':valor_unitario', $valor_u);
                $query->bindParam(':precio_unitario', $valor_u_igv);
                $query->bindParam(':igv', $igv_total);
                $query->bindParam(':porcentaje_igv', $porcentaje_igv);
                $query->bindParam(':valor_total', $valor_total);
                $query->bindParam(':icbper', $icbper_total);
                $query->bindParam(':factor_icbper', $factor_icbper);
                $query->bindParam(':importe_total', $precio_total);
                $query->bindParam(':afectacion_id', $afectacion_item);
                $query->bindParam(':tp_producto', $tp_producto);
                $query->bindParam(':id_unidad_medida', $id_unidad_medida);
                $query->execute();
            }
            ;

            return [
                "success" => true
            ];
        } catch (PDOException $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function get_data_comprobante_sunat($id_comprobante = 0)
    {
        try {
            $emisor = $this->consult_emisor();

            $query = $this->db->connect()->prepare("
            SELECT
             vd.nombres AS vendedor_nombres,
             vd.apellidos AS vendedor_apellidos,
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
             v.id_cliente
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

            $cuotas = [];
            $monto_credito = 0;
            if ($data_venta['id_forma_pago'] == 2) {
                $query = $this->db->connect()->prepare("
                 SELECT * FROM cuota
                 WHERE comprobante_id = :id_venta");
                $query->bindParam(":id_venta", $id_comprobante);
                $query->execute();
                $data_cuotas = $query->fetch(PDO::FETCH_ASSOC);

                $cuotas = [
                    [
                        "numero" => $data_cuotas['numero'],
                        "importe" => number_format($data_cuotas['importe'], 2, '.', ''),
                        "vencimiento" => $data_cuotas['fecha_vencimiento']
                    ]
                ];
                $monto_credito = number_format($data_cuotas['importe'], 2, '.', '');
            }


            //=============CALCULOS=========================

            $ops_gravadas = $data_venta['op_gravada'] ?? 0.00;
            $ops_exoneradas = $data_venta['op_exonerada'] ?? 0.00;
            $ops_inafectas = $data_venta['op_inafecta'] ?? 0.00;
            $igv_ops = $data_venta['op_igv'] ?? 0.00;
            $icbper_ops = $data_venta['icbper'] ?? 0.00;

            $total_antes_impuestos = $ops_gravadas + $ops_exoneradas + $ops_inafectas;
            $total_impuestos = $igv_ops + $icbper_ops;
            $total_despues_impuestos = $total_antes_impuestos + $total_impuestos;

            $cabecera['tipo_operacion'] = "0101";
            $cabecera['tipo_comprobante'] = $data_venta['tp_comprobante_codigo'];
            $cabecera['moneda'] = $data_venta['tp_moneda_codigo'];
            $cabecera['serie'] = $data_venta['serie'];
            $cabecera['correlativo'] = $data_venta['correlativo'];
            $cabecera['total_op_gravadas'] = $data_venta['op_gravada'];
            $cabecera['igv'] = $data_venta['op_igv'];
            $cabecera['icbper'] = $data_venta['icbper'];
            $cabecera['total_op_exoneradas'] = $data_venta['op_exonerada'];
            $cabecera['total_op_inafectas'] = $data_venta['op_inafecta'];
            $cabecera['total_antes_impuestos'] = $total_antes_impuestos;
            $cabecera['total_impuestos'] = $total_impuestos;
            $cabecera['total_despues_impuestos'] = $total_despues_impuestos;
            $cabecera['descuento_global'] = 0.00;
            $cabecera['suma_descuento_item'] = 0.00;
            $cabecera['total_a_pagar'] = $data_venta['total'];
            $cabecera['fecha_emision'] = $data_venta['fecha_emision_format'];
            $cabecera['hora_emision'] = $data_venta['hora_emision_format'];
            $cabecera['fecha_vencimiento'] = $data_venta['fecha_vencimiento'];
            $cabecera['forma_pago'] = ucwords(strtolower(ucfirst($data_venta['forma_pago'])));
            $cabecera['monto_credito'] = $monto_credito;
            $cabecera['anexo_sucursal'] = "0000";
            $cabecera['cuotas'] = $cuotas;

            $query = $this->db->connect()->prepare("SELECT
            CONCAT(c.nombres,' ',c.apellidos ) AS razon_social,
            c.id_tp_docu AS tipo_documento,
            c.num_docu AS ruc,
            c.direccion AS direccion
            FROM usuario c
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
            WHERE c.id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data_venta['id_cliente']);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);
            $cliente['pais'] = 'PE';

            $query = $this->db->connect()->prepare("
            SELECT
            dt_c.*,
            p.descripcion AS nombre,
            p.codigo AS codigo_interno,
            u_m.abreviatura AS unidad_medida,
            af.letra,
            af.cod_afectacion_sunat,
            af.codigo,
            af.nombre AS nombre_afectacion,
            af.tipo AS tipo_afectacion
            FROM detalle_comprobante dt_c
            LEFT JOIN ctg_encomienda p ON dt_c.producto_id = p.id_ctg_encomienda
            LEFT JOIN unidad_medida u_m ON dt_c.id_unidad_medida = u_m.id
            LEFT JOIN afectaciones_igv af ON dt_c.afectacion_id = af.id
            WHERE dt_c.comprobante_id=:id_comprobante");
            $query->bindParam(":id_comprobante", $id_comprobante);
            $query->execute();
            $productos = $query->fetchAll(PDO::FETCH_ASSOC);

            $items = [];

            //==========CREACION DEL JSON PARA EL API ============
            foreach ($productos as $producto) {
                $p_igv = round($producto['porcentaje_igv']);
                $total_impuestos = $producto['igv'] + $producto['icbper'];
                $item = [
                    "item" => $producto['item'],
                    "nombre" => $producto['nombre'],
                    "cantidad" => $producto['cantidad'],
                    "codigo" => $producto['codigo_interno'],
                    "valor_unitario" => $producto['valor_unitario'],
                    "precio_lista" => $producto['precio_unitario'],
                    "valor_total" => $producto['valor_total'],
                    "igv" => $producto['igv'],
                    "icbper" => $producto['icbper'],
                    "factor_icbper" => $producto['factor_icbper'],
                    "total_antes_impuestos" => $producto['valor_total'],
                    "total_impuestos" => $total_impuestos,
                    "porcentaje_igv" => $p_igv,
                    "unidad" => $producto['unidad_medida'],
                    "codigos" => [
                        $producto["letra"],
                        $producto["cod_afectacion_sunat"],
                        $producto["codigo"],
                        $producto["nombre_afectacion"],
                        $producto["tipo_afectacion"]
                    ]
                ];
                $items[] = $item;
            }

            $result = [
                "ose" => $emisor['ose'],
                "emisor" => $emisor,
                "cliente" => $cliente,
                "cabecera" => $cabecera,
                "items" => $items
            ];

            return ['success' => true, 'message' => $result];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e];
                    break;
            }
        }
    }

    public function actualizar_venta_sunat($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $query = $this->db->connect()->prepare("UPDATE venta SET
            envio_sunat=:envio_sunat,
            descrip_cdr_sunat=:descrip_cdr_sunat,
            hash_cdr=:hash_cdr,
            file_xml=:file_xml,
            file_cdr=:file_cdr
            WHERE id_venta=:id_venta
            ");
            $query->bindParam(':envio_sunat', $data['estado']);
            $query->bindParam(':descrip_cdr_sunat', $data["mensaje_sunat"]);
            $query->bindParam(':hash_cdr', $data["hash_cpe"]);
            $query->bindParam(':file_xml', $data["xml"]);
            $query->bindParam(':file_cdr', $data["cdr"]);
            $query->bindParam(':id_venta', $data["id_comprobante"]);
            $query->execute();

            return ['success' => true, "message" => "Registro actualziado correctamente."];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e];
                    break;
            }
        }
    }

    public function actualizar_notas_venta($id_notas, $id_comprobante, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $estado_comp = 'PAGADO';
            $monto_cero = 0.00;

            $query = $conn->prepare("
                UPDATE venta 
                SET 
                estado = :estado_comp,
                total = :total,
                id_pago = :id_pago
                WHERE id_venta IN ($id_notas)
            ");
            $query->bindParam(":estado_comp", $estado_comp);
            $query->bindParam(":id_pago", $id_comprobante);
            $query->bindParam(":total", $monto_cero);
            $query->execute();
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e];
                    break;
            }
        }
    }

    public function get_data_comprobante($id_comprobante = 0)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT * 
            FROM empresa
            ");
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("
            SELECT
             vd.nombres AS vendedor_nombres,
             vd.apellidos AS vendedor_apellidos,
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
             v.hash_cdr
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
            tp_d_c.descripcion AS tipo_doc_descripcion
            FROM usuario c
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
            WHERE c.id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data_venta['id_cliente']);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);
            $cliente['pais'] = 'PE';

            $query = $this->db->connect()->prepare("
            SELECT
            dt_c.*,
            p.descripcion AS nombre,
            p.codigo AS codigo_interno,
            u_m.abreviatura AS unidad_medida,
            af.letra,
            af.cod_afectacion_sunat,
            af.codigo,
            af.nombre AS nombre_afectacion,
            af.tipo AS tipo_afectacion
            FROM detalle_comprobante dt_c
            LEFT JOIN ctg_encomienda p ON dt_c.producto_id = p.id_ctg_encomienda
            LEFT JOIN unidad_medida u_m ON dt_c.id_unidad_medida = u_m.id
            LEFT JOIN afectaciones_igv af ON dt_c.afectacion_id = af.id
            WHERE dt_c.comprobante_id=:id_comprobante
            ORDER BY dt_c.item ASC");
            $query->bindParam(":id_comprobante", $id_comprobante);
            $query->execute();
            $productos = $query->fetchAll(PDO::FETCH_ASSOC);

            $respuesta = [
                "emisor" => $emisor,
                "cliente" => $cliente,
                "data_venta" => $data_venta,
                "productos" => $productos
            ];

            return ['success' => true, 'message' => $respuesta];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "No se ha podido obetener la data de la venta", $e];
                    break;
            }
        }
    }

    public function getDatosExportacion($fecha_inicio = '', $fecha_fin = '', $estado = '')
    {
        try {
            $conn = $this->db->connect();

            // PRIMERO: Verificar si existen ventas con tp_comprobante = 31
            $sql_count = "SELECT COUNT(*) as total FROM venta WHERE id_tp_comprobante = 31";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->execute();
            $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

            error_log("=== DEPURACIÓN EXPORTACIÓN EXCEL ===");
            error_log("Total ventas con tp_comprobante=31: " . $total);
            error_log("Fecha Inicio recibida: '" . $fecha_inicio . "'");
            error_log("Fecha Fin recibida: '" . $fecha_fin . "'");
            error_log("Estado recibido: '" . $estado . "'");

            // Si no hay ventas, retornar vacío pero con log
            if ($total == 0) {
                error_log("NO HAY VENTAS con tp_comprobante=31 en la base de datos");
                return [];
            }

            // Tu consulta original
            $sql = "SELECT 
            v.id_venta,
            v.fecha_emision,
            v.serie,
            v.correlativo,
            v.total,
            v.estado,
            v.id_pago,
            c.nombres AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.num_docu AS cliente_num_docu,
            tp_m.codigo AS tp_moneda_codigo,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            tp_s.descripcion AS tp_servicio,
            CONCAT(pa.serie, '-', pa.correlativo) AS pago_e,
            vh.marca,
            vh.modelo,
            vh.placa
        FROM venta v
        LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
        LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
        LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio = dt_v.id_tp_servicio
        LEFT JOIN venta pa ON pa.id_venta = v.id_pago
        LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
        WHERE v.id_tp_comprobante IN (31)";

            $params = [];

            if (!empty($fecha_inicio)) {
                $sql .= " AND DATE(v.fecha_emision) >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            }

            if (!empty($fecha_fin)) {
                $sql .= " AND DATE(v.fecha_emision) <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($estado)) {
                $sql .= " AND v.estado = :estado";
                $params[':estado'] = $estado;
            }

            $sql .= " ORDER BY v.fecha_emision DESC";

            error_log("SQL a ejecutar: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $conn->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Registros encontrados: " . count($resultados));

            // Mostrar primer registro como ejemplo (si existe)
            if (count($resultados) > 0) {
                error_log("Primer registro: " . json_encode($resultados[0]));
            }

            return $resultados;

        } catch (PDOException $e) {
            error_log("ERROR en getDatosExportacion: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresa()
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("SELECT * FROM empresa LIMIT 1");
            $query->execute();
            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            if ($empresa) {
                // Construir ruta absoluta del logo si existe
                if (!empty($empresa['logo'])) {
                    $logo_relative_path = $empresa['logo'];
                    // Asumiendo que los logos están en una carpeta como uploads/logos/
                    $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/' . $logo_relative_path;

                    $empresa['logo_absolute_path'] = $logo_absolute_path;
                    $empresa['logo_exists'] = file_exists($logo_absolute_path);
                } else {
                    $empresa['logo_exists'] = false;
                    $empresa['logo_absolute_path'] = '';
                }
            }

            return $empresa ?: [];
        } catch (PDOException $e) {
            error_log("Error en getEmpresa: " . $e->getMessage());
            return [];
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
