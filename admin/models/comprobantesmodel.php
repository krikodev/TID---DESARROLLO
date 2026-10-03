<?php
require_once('public/plugins/print/num_letras.php');

class ComprobantesModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_dataTable($data)
    {
        try {
            $selectFields = "v.id_venta,
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
            v.id_tp_venta,
            tv.nombre AS tipo_venta_nombre,
            CASE 
            WHEN v.id_tp_venta = 1 THEN 'PASAJE'
            WHEN v.id_tp_venta = 2 THEN 'ENCOMIENDA'
            WHEN v.id_tp_venta = 3 THEN 'FACTURADOR'
            WHEN v.id_tp_venta = 4 THEN 'PAGO DE NOTAS EN BLOQUE'
            WHEN v.id_tp_venta = 5 THEN 'COTIZACION'
            WHEN v.id_tp_venta = 6 THEN 'VALORIZACION'
            WHEN v.id_tp_venta = 7 THEN 'FLETE'
            ELSE 'OTRO'
            END AS tipo_display,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            tp_s.descripcion AS tp_servicio,
            f.descripcion AS forma_pago
            ";
            $baseQuery = " FROM venta v
            LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
            LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
            LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
            LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
            LEFT JOIN forma_pago f ON f.id_forma_pago = v.id_forma_pago
            LEFT JOIN tp_venta tv ON tv.id = v.id_tp_venta
            WHERE v.id_tp_comprobante IN (1,3)
            ";

            $params = [];

            // Aplicar filtros adicionales
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $fecha_inicio = $data['fecha_inicio'];
                $fecha_fin = $data['fecha_fin'];
                $baseQuery .= " AND DATE(v.fecha_emision) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            }

            if (!empty($data['tp_comprobante'])) {
                $tp_comprobante = intval($data['tp_comprobante']);
                $baseQuery .= " AND v.id_tp_comprobante = $tp_comprobante";
            }

            if (!empty($data['tipo_venta'])) {
                if ($data['tipo_venta'] == 'OTRO') {
                    $baseQuery .= " AND (v.id_tp_venta NOT IN (1,2,3,4) OR v.id_tp_venta IS NULL)";
                } else {
                    $tipo_venta = intval($data['tipo_venta']);
                    $baseQuery .= " AND v.id_tp_venta = $tipo_venta";
                }
            }

            $searchColumns = [
                "v.fecha_emision",
                "CONCAT(c.nombres, ' ',c.apellidos)",
                "CONCAT(c.nombres, ' ',c.apellidos, ' ', c.num_docu)",
                "c.nombres",
                "c.apellidos",
                "v.serie",
                "v.correlativo",
                "CONCAT(v.serie, '-', v.correlativo)",
                "v.estado",
                "v.op_gravada",
                "v.op_exonerada",
                "v.op_inafecta",
                "v.op_igv",
                "v.total",
                "f.descripcion",
                "CASE v.estado WHEN 0 THEN 'SIN ENVIAR' WHEN 1 THEN 'ENVIADO A SUNAT' WHEN 2 THEN 'ENVIADO POR RESUMEN' ELSE 'DESCONOCIDO' END",
                "CASE WHEN v.id_tp_venta = 1 THEN 'PASAJE' WHEN v.id_tp_venta = 2 THEN 'ENCOMIENDA' WHEN v.id_tp_venta = 3 THEN 'FACTURADOR' WHEN v.id_tp_venta = 4 THEN 'PAGO DE NOTAS EN BLOQUE' ELSE 'OTRO' END",
            ];
            $orderBy = "v.fecha_emision DESC";

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

    public function get_comprobante_nota($id_venta)
    {
        try {
            $query = $this->db->connect()->prepare("
        SELECT v.id_venta,tp_s.descripcion, 
        v.op_igv AS igv,v.serie, v.correlativo, 
        v.descuento, v.op_gravada, v.op_exonerada, 
        v.op_inafecta,v.total ,tp_c.codigo, 
        CONCAT(u.nombres,' ',u.apellidos) AS cliente, 
        tp_m.descripcion AS moneda, tp_m.codigo AS moneda_codigo
        FROM venta v 
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
        LEFT JOIN usuario u  ON u.id_usuario = v.id_cliente
        LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
        WHERE v.id_venta=:id_venta");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);

            $data['unidad_medida'] = 'ZZ';
            $data['valor_unitario'] = $data['total'];
            $data['precio_unitario'] = $data['total'];
            return ['success' => true, 'message' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function motivos_notas()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM tabla_parametrica");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public function set_data_notas($data)
    {
        try {
            $id_venta = $data['id_venta'];
            $tp_comp = substr($data['list_comp'], 1);
            $emisor = $this->consult_emisor();
            $correlativo = $this->get_correlativo_notas($data['txtserie_nota']);

            $afectacion = null;
            $query = $this->db->connect()->prepare("SELECT
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
        WHERE v.id_venta=:id_venta");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);
            $cliente['pais'] = 'PE';

            $query = $this->db->connect()->prepare("SELECT
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
         WHERE v.id_venta=:id_venta");
            $query->bindParam(":id_venta", $id_venta);
            $query->execute();
            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            $productos = $data['productos'];
            $items = null;

            date_default_timezone_set('America/Lima');
            $hora_actual = date('H:i:s');

            if ($data['tp_servicio'] == "PASAJE") {
                $afectacion = 0.00;
            } else {
                $afectacion = 18;
            }
            if ($data['tp_servicio'] != "PASAJE") {

                // Suponiendo que tienes $items definido previamente
                foreach ($productos as $i => $producto) {

                    if ($producto['op_gravada'] > 0) {
                        $codigos = ["S", "10", "1000", "IGV", "VAT"];
                    } else if ($producto['op_exonerada'] > 0) {
                        $codigos = ["E", "20", "9997", "EXO", "VAT"];
                    } else if ($producto['op_inafecta'] > 0) {
                        $codigos = ["O", "30", "9998", "INA", "FRE"];
                    }

                    $total_antes_impuestos = $producto['total'] - $producto['igv'];

                    $items[$i]["item"] = ($i + 1);
                    $items[$i]['porcentaje_igv'] = $afectacion;
                    $items[$i]['codigo'] = 0;
                    $items[$i]['igv'] = $producto['igv'];
                    $items[$i]['precio_lista'] = $producto['precio_unitario'];
                    $items[$i]['valor_total'] = $total_antes_impuestos;
                    $items[$i]['valor_unitario'] = $producto['valor_unitario'];
                    $items[$i]['total_impuestos'] = $producto['igv'];
                    $items[$i]['total'] = sprintf('%.2f', $producto['total']);
                    $items[$i]['icbper'] = 0.00;
                    $items[$i]['factor_icbper'] = 0.00;
                    $items[$i]['unidad'] = $producto['unidad_medida'];
                    $items[$i]['nombre'] = $producto['descripcion'];
                    $items[$i]['total_antes_impuestos'] = $total_antes_impuestos;
                    $items[$i]['tipo_precio'] = "01";
                    $items[$i]['codigos'] = $codigos;
                    $items[$i]['cantidad'] = $producto['cantidad'];
                }

                //Cabecera para exo 
                $cabecera['tipo_comprobante'] = $data['list_comp'];
                $cabecera['igv'] = sprintf('%.2f', $data['igv_total']);
                $cabecera['total_op_gravadas'] = $data['op_gravada'];
                $cabecera['total_op_exoneradas'] = $data['op_exonerada'];
                $cabecera['total_op_inafectas'] = $data['op_inafecta'];
                $cabecera['descuento'] = 0.00;
                $cabecera['total_a_pagar'] = sprintf('%.2f', $data['total_pagar']);
                $cabecera['serie'] = $data['txtserie_nota'];
                $cabecera['correlativo'] = $correlativo;
                $cabecera['fecha_emision'] = $data['txt_fecha'];
                $cabecera['hora_emision'] = $hora_actual;
                $cabecera['fecha_emision'] = $data['txt_fecha'];
                $cabecera['codmotivo'] = $data['list_motivo'];
                $cabecera['descripcion'] = $data['txt_descripcion'];
                $cabecera['anexo_sucursal'] = "0000";
                $cabecera['total_texto'] = numtoletras($cabecera['total_a_pagar']);
            } else {
                $codigos = ["E", "20", "9997", "EXO", "VAT"];
                foreach ($productos as $i => $producto) {
                    $items[$i]["item"] = ($i + 1);
                    $items[$i]['porcentaje_igv'] = $afectacion;
                    $items[$i]['codigo'] = 0;
                    $items[$i]['igv'] = $producto['igv'];
                    $items[$i]['precio_lista'] = $producto['total'];
                    $items[$i]['valor_total'] = $producto['total'];
                    $items[$i]['valor_unitario'] = $producto['total'];
                    $items[$i]['total'] = $producto['total'];
                    $items[$i]['icbper'] = 0.00;
                    $items[$i]['factor_icbper'] = 0.00;
                    $items[$i]['unidad'] = "ZZ";
                    $items[$i]['nombre'] = $producto['descripcion'];
                    $items[$i]['total_antes_impuestos'] = $producto['total'];
                    $items[$i]['tipo_precio'] = "01";
                    $items[$i]['codigos'] = $codigos;
                    $items[$i]['cantidad'] = 1;
                }
                //cabecera para igv
                $cabecera['tipo_comprobante'] = $data['list_comp'];
                $cabecera['igv'] = $data['productos'][0]['igv'];
                $cabecera['total_op_gravadas'] = $data['productos'][0]['op_gravada'];
                $cabecera['total_op_exoneradas'] = $data['productos'][0]['op_exonerada'];
                $cabecera['total_op_inafectas'] = $data['productos'][0]['op_inafecta'];
                $cabecera['descuento'] = $data['productos'][0]['descuento'];
                $cabecera['total_a_pagar'] = $data['productos'][0]['total'];
                $cabecera['serie'] = $data['txtserie_nota'];
                $cabecera['correlativo'] = $correlativo;
                $cabecera['fecha_emision'] = $data['txt_fecha'];
                $cabecera['hora_emision'] = $hora_actual;
                $cabecera['fecha_emision'] = $data['txt_fecha'];
                $cabecera['codmotivo'] = $data['list_motivo'];
                $cabecera['descripcion'] = $data['txt_descripcion'];
                $cabecera['anexo_sucursal'] = "0000";
                $cabecera['total_texto'] = numtoletras($data['productos'][0]['total']);
            }

            $json = [
                "ose" => $emisor['ose'],
                "emisor" => $emisor,
                "cliente" => $cliente,
                "cabecera" => $cabecera,
                "items" => $items
            ];

            $rpta_sunat = $this->enviar_json_a_api(json_encode($json));
            $resp = json_decode($rpta_sunat, true);
            if ($resp[0]["estado"] == "1") {
                $this->add_nota($json, $id_venta, $resp, $tp_comp);
                return array(
                    "success" => true,
                    "message" => array(
                        "estado_sunat" => isset($resp) ? $resp[0]["estado"] : 0,
                        "message_sunat" => isset($resp) ? $resp[0]["mensaje_sunat"] : ''
                    )
                );
            } elseif ($resp[0]["estado"] == "2") {
                return array(
                    "success" => true,
                    "message" => array(
                        "estado_sunat" => isset($resp) ? $resp[0]["estado"] : 0,
                        "message_sunat" => isset($resp) ? $resp[0]["mensaje_sunat"] : ''
                    )
                );
            } else {
                return [
                    "success" => true,
                    "message" => [
                        "estado_sunat" => isset($resp) ? $resp[0]["estado"] : 0,
                        "message_sunat" => isset($resp) ? $resp[0]["mensaje_sunat"] : ''
                    ]
                ];
            }
        } catch (PDOException $e) {
            return array(
                "success" => false,
                "message" => array(
                    "respuesta" => "Ha ocurrido un error intentalo mas tarde.." . $e
                )
            );
        }
    }

    public function get_serieForTpComprobante($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $data["tp_comprobante"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($reply as &$registro) {
                if ($registro['correlativo'] !== null && $registro['correlativo'] !== 0) {
                    $registro['correlativo']++;
                } else {
                    $registro['correlativo'] = 1;
                }
            }

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

    public function get_items_encomienda($data)
    {
        $query = $this->db->connect()->prepare("
            SELECT pe.*, pe.op_igv AS igv, 
            pe.op_total AS total,ct.afectacion AS afectacion, 
            v.descuento, v.id_venta,
            um.abreviatura AS unidad_medida,
            pe.precio AS precio_unitario
            FROM producto_encomienda pe
            LEFT JOIN ctg_encomienda ct ON pe.id_ctg_encomienda = ct.id_ctg_encomienda
            LEFT JOIN dt_venta dv ON pe.id_encomienda = dv.id_encomienda
            LEFT JOIN venta v ON dv.id_venta = v.id_venta
            LEFT JOIN unidad_medida um ON pe.id_unidad_medida = um.id
            WHERE pe.id_encomienda IN (
            SELECT id_encomienda
            FROM dt_venta
            WHERE id_venta = :id_venta)
            ");
        $query->bindParam(":id_venta", $data['id_venta']);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reply as &$item) {
            if ($item['op_gravada'] > 0) {
                $item['valor_unitario'] = $item['cantidad'] == 1 ? $item['op_gravada'] : $item['op_gravada'] / $item['cantidad'];
            } else if ($item['op_exonerada'] > 0) {
                $item['valor_unitario'] = $item['cantidad'] == 1 ? $item['op_exonerada'] : $item['op_exonerada'] / $item['cantidad'];
            } else if ($item['op_inafecta'] > 0) {
                $item['valor_unitario'] = $item['cantidad'] == 1 ? $item['op_inafecta'] : $item['op_inafecta'] / $item['cantidad'];
            }
        }
        unset($item);

        return ['success' => true, 'message' => $reply];
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


    public function get_dataTableResumen($data)
    {
        try {
            $selectFields = "*";
            $baseQuery = "FROM resumen_baja WHERE 1=1";

            $params = [];

            // Aplicar filtros de fecha si existen
            if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
                $fecha_inicio = $data['fecha_inicio'];
                $fecha_fin = $data['fecha_fin'];
                $baseQuery .= " AND DATE(fecha_envio) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            }

            $searchColumns = ["fecha_envio", "fecha_referencia", "nombre_xml", "ticket", "mensaje_sunat", "codigo_sunat"];
            $orderBy = "id_resumen DESC";

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

    public function get_dataTableReenvio($data)
    {
        try {
            if ($data['ruc'] == 1) {
                $tipo_ruc = 1;
            } else {
                $tipo_ruc = 2;
            }

            $tp_comprobante = isset($data["tp_comprobante"]) ? implode(",", $data["tp_comprobante"]) : "3";
            $fecha_inicio = isset($data["fecha_inicio"]) ? $data["fecha_inicio"] : null;
            $fecha_fin = isset($data["fecha_fin"]) ? $data["fecha_fin"] : null;

            if (!$fecha_inicio && !$fecha_fin) {
                $fecha_actual = date('Y-m-d');
                $fecha_7_dias_atras = date('Y-m-d', strtotime('-7 days')); // 2025-07-31
                $fecha_inicio = $fecha_7_dias_atras;
                $fecha_fin = $fecha_actual;
            }

            $baseQuery = "
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
            DATE(v.fecha_emision) AS fecha_emision,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') AS fecha_emision_formatted,
            DATE(v.fecha_registro) AS fecha_registro,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            tv.nombre AS tipo_venta_nombre,
            CASE 
            WHEN v.id_tp_venta = 1 THEN 'PASAJE'
            WHEN v.id_tp_venta = 2 THEN 'ENCOMIENDA'
            WHEN v.id_tp_venta = 3 THEN 'FACTURADOR'
            WHEN v.id_tp_venta = 4 THEN 'PAGO DE NOTAS EN BLOQUE'
            ELSE 'OTRO'
            END AS tipo_display,
            tp_s.descripcion AS tp_servicio,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida
        FROM venta v
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN tp_servicio tp_s ON tp_s.id_tp_servicio=dt_v.id_tp_servicio
        LEFT JOIN tp_venta tv ON tv.id = v.id_tp_venta 
        WHERE v.id_tp_comprobante IN ($tp_comprobante) AND tipo_ruc = $tipo_ruc
        AND v.envio_sunat = 0";

            $params = [];

            // SIEMPRE aplicar filtro de fecha (ya sea por defecto o proporcionado)
            if ($fecha_inicio && $fecha_fin) {
                $baseQuery .= " AND DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            } elseif ($fecha_inicio) {
                $baseQuery .= " AND DATE(v.fecha_emision) >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            } elseif ($fecha_fin) {
                $baseQuery .= " AND DATE(v.fecha_emision) <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            }

            $baseQuery .= " ORDER BY v.correlativo DESC";

            $query = $this->db->connect()->prepare($baseQuery);
            $query->execute($params);
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            return ([
                'data' => $reply,
                'recordsTotal' => count($reply),
                'recordsFiltered' => count($reply),
                'filtros_aplicados' => [
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'tp_comprobante' => $tp_comprobante,
                    'filtro_por_defecto' => (!isset($data["fecha_inicio"]) && !isset($data["fecha_fin"]))
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            return ([
                'error' => true,
                'message' => 'Error interno del servidor',
                'details' => $e->getMessage()
            ]);
        }
    }

    // Método adicional para obtener el rango de fechas permitido
    public function get_rango_fechas_permitido()
    {
        $fecha_actual = date('Y-m-d');
        $fecha_7_dias_atras = date('Y-m-d', strtotime('-7 days'));

        return ([
            'fecha_minima' => $fecha_7_dias_atras,
            'fecha_maxima' => $fecha_actual,
            'fecha_actual' => $fecha_actual,
            'dias_permitidos' => 7
        ]);
    }

    public function getDataComprobante($id_venta)
    {
        $query = $this->db->connect()->prepare("SELECT
        e.logo AS empresa_logo,
        e.envio_ose AS ose,
        e.m_terminales AS permiso_t,
        e.fr_comprobante AS frase_empresa,
        e.termscond_pasaje AS terminos_condiciones_pasaje,
        e.num_docu AS empresa_ruc,
        e.razon_social AS empresa_razon_social,
        e.nro_cuenta_bancaria AS empresa_cuenta_bancaria,
        e.telefono_empresa AS empresa_telefono,
        t.logo AS terminal_logo,
        t.nombre AS terminal_nombre,
        t.ubigeo AS terminal_ubigeo_codigo,
        ub.depa AS terminal_ubigeo_depa,
        ub.provi AS terminal_ubigeo_provi,
        ub.distri AS terminal_ubigeo_distri,
        t.direccion_fiscal AS terminal_direccion_fiscal,
        t.direccion_comercial AS terminal_direccion_comercial,
        t.cod_domicilio_fiscal AS terminal_cod_domicilio_fiscal,
        t.celular AS terminal_celular,
        t.email AS terminal_email,
        t.siteweb AS terminal_siteweb,
        e.user_sol AS empresa_usuario_sol,
        e.pass_sol AS empresa_pass_sol,
        td.id_tp_docu AS tp_doc_id,
        e.ubigeo AS ubigeo_empresa,
        ub_e.depa AS departamento,
        ub_e.provi AS provincia,
        ub_e.distri AS distrito,
        CONCAT(e.direccion_fiscal, ' ',ub_e.depa, ' - ',ub_e.provi, ' - ',ub_e.distri) AS direccion
        
         FROM terminal t
         LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
         LEFT JOIN ubigeo ub_e ON ub_e.cod_ubigeo = e.ubigeo
         LEFT JOIN ubigeo ub ON ub.cod_ubigeo=t.ubigeo
         LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
         LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
         WHERE t.id_terminal=1;");
        $query->execute();
        $emisor = $query->fetch(PDO::FETCH_ASSOC);

        if ($emisor['permiso_t'] == '1') {
            $query = $this->db->connect()->prepare("SELECT t.nombre, t.direccion_comercial AS direccion_fiscal, t.celular
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

        $query = $this->db->connect()->prepare("SELECT
        c.nombres AS cliente_nombres,
        c.apellidos AS cliente_apellidos,
        c.id_tp_docu AS cliente_id_tp_docu,
        tp_d_c.descripcion AS cliente_tp_docu,
        c.num_docu AS cliente_num_docu,
        c.direccion AS cliente_direccion,
        c.ubigeo AS cliente_ubigeo,
        c.fecha_nacimiento AS cliente_fecha_nacimiento,
        c.nacionalidad AS cliente_nacionalidad,
        c.celular AS cliente_celular,

        psj.nombres AS pasajero_nombres,
        psj.apellidos AS pasajero_apellidos,
        psj.id_tp_docu AS pasajero_id_tp_docu,
        tp_d_psj.descripcion AS pasajero_tp_docu,
        psj.num_docu AS pasajero_num_docu,
        psj.direccion AS pasajero_direccion,
        psj.ubigeo AS pasajero_ubigeo,
        psj.fecha_nacimiento AS pasajero_fecha_nacimiento,
        psj.nacionalidad AS pasajero_nacionalidad,
        psj.celular AS pasajero_celular
        
        FROM venta v
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        LEFT JOIN usuario c ON c.id_usuario=v.id_cliente
        LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
        LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
        LEFT JOIN usuario psj ON psj.id_usuario=dt_v.id_pasajero
        LEFT JOIN ubigeo ub_psj ON ub_psj.cod_ubigeo=psj.ubigeo
        LEFT JOIN tp_docu tp_d_psj ON tp_d_psj.id_tp_docu=psj.id_tp_docu
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("SELECT
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
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            vh.placa AS vehiculo_placa,
            vh.num_poliza,
            vh.soat,
            v.estado,
            v.fecha_registro,
            date_format(v.fecha_registro, '%Y-%m-%d') AS fecha_registro_format,
            date_format(v.fecha_registro, '%H:%i:%s') AS hora_registro_format,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            v.cod_qr
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
        WHERE v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $cabecera = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("SELECT
            dt_v.piso,
            dt_v.num_asiento,
            dt_v.estado_asiento,
            dt_v.precio,
            dt_v.op_igv,
            dt_v.op_gravada,
            dt_v.op_exonerada,
            dt_v.op_inafecta,
            dt_v.op_total
        FROM dt_venta dt_v
        WHERE dt_v.id_venta=:id_venta");
        $query->bindParam(":id_venta", $id_venta);
        $query->execute();
        $items = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("SELECT
        *
         FROM impresoras i
         WHERE cod_usuario=:usuario;");
        $query->bindParam(":usuario", $this->id_usuario_sesion);
        $query->execute();
        $impresora_data = $query->fetch(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($items); $i++) {
            $items[$i]["iten"] = ($i + 1);
        }
        return array(
            "TERMINALES" => $terminales,
            "EMISOR" => $emisor,
            "CABECERA" => $cabecera,
            "CLIENTE" => $cliente,
            "ITEMS" => $items,
            "IMPRESORA" => $impresora_data
        );
    }

    public function pagar_comprobante($data)
    {
        try {
            // El archivo es opcional
            $pago_file = null;

            if (isset($_FILES["pago_file"]) && $_FILES["pago_file"]["error"] === UPLOAD_ERR_OK) {
                $reply = $this->upload->upload_basic($_FILES["pago_file"], "../img/pagos/", $_FILES["pago_file"]["name"]);
                if (!$reply["success"]) {
                    return ['success' => false, 'message' => $reply["message"]];
                }
                $pago_file = $reply["message"];
            }
            // Si error !== UPLOAD_ERR_OK y también != UPLOAD_ERR_NO_FILE, podrías 
            // considerar avisar que hubo un problema al subir, en vez de ignorarlo silenciosamente.

            $conn = $this->db->connect();

            // Si no se subió archivo, no pisamos el valor existente en pago_file
            if ($pago_file !== null) {
                $query = $conn->prepare("
            UPDATE cuota
            SET 
            estado = 'P',
            id_caja_chica = :id_caja,
            id_medio_pago = :id_medio,
            fecha_pago = :fecha_pago,
            observacion = :observacion,
            pago_file = :pago_file
            WHERE comprobante_id = :id_comprobante
            ");
                $query->bindParam(":pago_file", $pago_file);
            } else {
                $query = $conn->prepare("
            UPDATE cuota
            SET 
            estado = 'P',
            id_caja_chica = :id_caja,
            id_medio_pago = :id_medio,
            fecha_pago = :fecha_pago,
            observacion = :observacion
            WHERE comprobante_id = :id_comprobante
            ");
            }
            $query->bindParam(":id_comprobante", $data['id_comprobante_pc']);
            $query->bindParam(":id_caja", $data['caja_chica_pc']);
            $query->bindParam(":id_medio", $data['medio_pago_pc']);
            $query->bindParam(":fecha_pago", $data['fecha_pc']);
            $query->bindParam(":observacion", $data['observacion_pc']);
            $query->execute();

            $query = $conn->prepare("
        UPDATE venta
        SET 
        estado = 'PAGADO'
        WHERE id_venta = :id_venta
        ");
            $query->bindParam(":id_venta", $data['id_comprobante_pc']);
            $query->execute();

            return ['success' => true, 'message' => 'Se ha pagado el comprobante con éxito'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'No se ha podido realizar el pago del comprobante'];
        }
    }

    public function detalle_pago_cuota($data)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT
        CONCAT(v.serie, '-', v.correlativo) AS serie_corr,
        c.importe,
        c.fecha_vencimiento,
        c.fecha_pago,
        m.descripcion,
        c.observacion,
        c.pago_file
        FROM cuota c
        LEFT JOIN venta v ON v.id_venta = c.comprobante_id
        LEFT JOIN medio_pago m ON m.id_medio_pago = c.id_medio_pago
        WHERE c.comprobante_id = :id_comprobante
        ");
        $query->bindParam("id_comprobante", $data['id_comprobante']);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($resultado)) {
            return ['success' => true, 'data' => $resultado];
        } else {
            return ['success' => false, 'data' => ''];
        }
    }
    // Agregar estos métodos al final de la clase ComprobantesModel
    public function getDataExportComprobantes($fecha_inicio = '', $fecha_fin = '', $tp_comprobante = '', $tipo_venta = '', $search = '')
    {
        try {
            $sql = "SELECT 
            v.id_venta,
            DATE_FORMAT(v.fecha_emision, '%d/%m/%Y') as fecha_emision,
            CONCAT(c.nombres, ' ', c.apellidos) as cliente,
            c.num_docu as documento_cliente,
            CONCAT(v.serie, '-', v.correlativo) as numero_comprobante,
            v.estado,
            CASE 
                WHEN v.id_tp_venta = 1 THEN 'PASAJE'
                WHEN v.id_tp_venta = 2 THEN 'ENCOMIENDA'
                WHEN v.id_tp_venta = 3 THEN 'FACTURADOR'
                WHEN v.id_tp_venta = 4 THEN 'NOTAS BLOQUE'
                ELSE 'OTRO'
            END as tipo_venta,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.op_igv,
            v.total,
            CASE v.envio_sunat
                WHEN 0 THEN 'SIN ENVIAR'
                WHEN 1 THEN 'ENVIADO'
                WHEN 2 THEN 'RESUMEN'
                ELSE 'DESCONOCIDO'
            END as estado_sunat,
            f.descripcion as forma_pago,
            CASE 
                WHEN v.id_tp_comprobante = 1 THEN 'FACTURA'
                WHEN v.id_tp_comprobante = 3 THEN 'BOLETA'
                ELSE 'OTRO'
            END as tipo_comprobante
            FROM venta v
            LEFT JOIN usuario c ON c.id_usuario = v.id_cliente
            LEFT JOIN forma_pago f ON f.id_forma_pago = v.id_forma_pago
            WHERE v.id_tp_comprobante IN (1,3) ";

            $conditions = [];
            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $conditions[] = "DATE(v.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            if (!empty($tp_comprobante)) {
                $conditions[] = "v.id_tp_comprobante = :tp_comprobante";
                $params[':tp_comprobante'] = $tp_comprobante;
            }

            if (!empty($tipo_venta)) {
                if ($tipo_venta == 'OTRO') {
                    $conditions[] = "(v.id_tp_venta NOT IN (1,2,3,4) OR v.id_tp_venta IS NULL)";
                } else {
                    $conditions[] = "v.id_tp_venta = :tipo_venta";
                    $params[':tipo_venta'] = $tipo_venta;
                }
            }

            if (!empty($search)) {
                $conditions[] = "(
                    CONCAT(c.nombres, ' ', c.apellidos) LIKE :search1
                    OR c.num_docu LIKE :search2
                    OR CONCAT(v.serie, '-', v.correlativo) LIKE :search3
                    OR v.estado LIKE :search4
                    OR f.descripcion LIKE :search5
                    OR CAST(v.total AS CHAR) LIKE :search6

                    OR CASE 
                        WHEN v.id_tp_venta = 1 THEN 'PASAJE'
                        WHEN v.id_tp_venta = 2 THEN 'ENCOMIENDA'
                        WHEN v.id_tp_venta = 3 THEN 'FACTURADOR'
                        WHEN v.id_tp_venta = 4 THEN 'NOTAS BLOQUE'
                        ELSE 'OTRO'
                    END LIKE :search7

                    OR CASE
                        WHEN v.id_tp_comprobante = 1 THEN 'FACTURA'
                        WHEN v.id_tp_comprobante = 3 THEN 'BOLETA'
                        ELSE 'OTRO'
                    END LIKE :search8

                    OR CASE v.envio_sunat
                        WHEN 0 THEN 'SIN ENVIAR'
                        WHEN 1 THEN 'ENVIADO'
                        WHEN 2 THEN 'RESUMEN'
                        ELSE 'DESCONOCIDO'
                    END LIKE :search9
                )";
                $searchValue = '%' . trim($search) . '%';
                $params[':search1'] = $searchValue;
                $params[':search2'] = $searchValue;
                $params[':search3'] = $searchValue;
                $params[':search4'] = $searchValue;
                $params[':search5'] = $searchValue;
                $params[':search6'] = $searchValue;
                $params[':search7'] = $searchValue;
                $params[':search8'] = $searchValue;
                $params[':search9'] = $searchValue;
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            $sql .= " ORDER BY v.fecha_emision DESC";
            $query = $this->db->connect()->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportComprobantes: " . $e->getMessage());
            return [];
        }
    }

    public function getDataExportResumenes($fecha_inicio = '', $fecha_fin = '')
    {
        try {
            $sql = "SELECT 
            DATE_FORMAT(fecha_envio, '%d/%m/%Y') as fecha_envio,
            DATE_FORMAT(fecha_referencia, '%d/%m/%Y') as fecha_referencia,
            nombre_xml as identificador,
            ticket,
            mensaje_sunat,
            codigo_sunat,
            CASE 
                WHEN codigo_sunat = '0' THEN 'ACEPTADO'
                WHEN codigo_sunat = '0127' THEN 'EN PROCESO'
                ELSE 'PENDIENTE'
            END as estado
            FROM resumen_baja
            WHERE 1=1 ";

            $params = [];

            if (!empty($fecha_inicio) && !empty($fecha_fin)) {
                $sql .= " AND DATE(fecha_envio) BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            $sql .= " ORDER BY fecha_envio DESC";

            $query = $this->db->connect()->prepare($sql);
            $query->execute($params);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportResumenes: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresa()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT e.*, 
            CONCAT('/tid-transporte/admin/public/', e.logo) as logo_absolute_path 
            FROM empresa e 
            WHERE e.id_empresa = (SELECT id_empresa FROM terminal WHERE id_terminal = :id_terminal LIMIT 1)
        ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getEmpresa: " . $e->getMessage());
            return [];
        }
    }
}
