<?php

class FacturadorModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $selectFields = "s.id_serie,
            s.id_terminal,
            t.nombre AS terminal_nombre,
            t.tipo AS terminal_tipo,
            t.cod_domicilio_fiscal,
            s.id_tp_comprobante,
            tp_c.descripcion AS tp_comprobante,
            s.serie,
            s.correlativo";
            $baseQuery = "
            FROM serie s
            LEFT JOIN terminal t ON t.id_terminal=s.id_terminal
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=s.id_tp_comprobante";
            $searchColumns = [
                "t.nombre",
                "t.cod_domicilio_fiscal",
                "tp_c.descripcion",
                "s.serie",
                "s.correlativo"
            ];

            $orderBy = "s.id_serie DESC";

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

    public function get_serieForTpComprobante($data)
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM serie WHERE id_tp_comprobante=:id_tp_comprobante AND id_terminal=$this->id_terminal_sesion");
            $query->bindParam(":id_tp_comprobante", $data["tp_comprobante"]);
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($reply) {
                return ['success' => true, "message" => $reply];
            } else {
                return ['success' => false, "message" => null];
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function crear_producto($data)
    {
        try {
            $tipo = '';
            $codigo_interno_temp = '';
            $factor_icbper = $data["afecto_icbper"] == 1 ? $data["factor_icbper"] : 0.00;

            $query = $this->db->connect()->prepare("
            INSERT INTO producto (
              nombre, descripcion, valor_unitario,
              tipo_afectacion_id, unidad_id, codigo_interno,
              codigo_sunat, afecto_icbper, factor_icbper, tipo, 
              estado
            ) 
            VALUES (
            :nombre, :descripcion, :valor_unitario,
            :tipo_afectacion, :unidad_id, :codigo_interno,
            :codigo_sunat, :afecto_icbper, :factor_icbper, :tipo, 
            :estado
            )
            ");

            $query->bindParam(':nombre', $data["nombre_producto"]);
            $query->bindParam(':descripcion', $data["descripcion_producto"]);
            $query->bindParam(':valor_unitario', $data["precio_unitario"]);
            $query->bindParam(':tipo_afectacion', $data["afectacion_prod"]);
            $query->bindParam(':unidad_id', $data["unidad_medida"]);
            $query->bindParam(':codigo_interno', $codigo_interno_temp);
            $query->bindParam(':codigo_sunat', $data["codigo_sunat"]);
            $query->bindParam(':afecto_icbper', $data["afecto_icbper"]);
            $query->bindParam(':factor_icbper', $factor_icbper);
            $query->bindParam(':tipo', $tipo);
            $query->bindParam(':estado', $data["estado_prod"]);
            $query->execute();

            $id_producto = $this->db->connect()->lastInsertId();

            $codigo_interno = 'P' . str_pad($id_producto, 4, '0', STR_PAD_LEFT);

            $update = $this->db->connect()->prepare("
            UPDATE producto 
            SET codigo_interno = :codigo_interno 
            WHERE id = :id_producto
            ");
            $update->bindParam(':codigo_interno', $codigo_interno);
            $update->bindParam(':id_producto', $id_producto);
            $update->execute();

            return ['success' => true, "message" => "Registro creado con éxito"];
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

    public function get_productosF()
    {
        try {
            $query = $this->db->connect()->prepare("SELECT * FROM producto  WHERE modulo = 'facturador' ORDER BY id DESC");
            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($reply) {
                return ['success' => true, "message" => $reply];
            } else {
                return ['success' => false, "message" => null];
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function generar_comprobante($data)
    {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();

            date_default_timezone_set('America/Lima');

            $tp_moneda = 1;
            $hora_actual = date('H:i:s');
            $fecha_emision = $data['fecha_emision'] . ' ' . $hora_actual;

            $total_igv = $data["total_igv"];
            $total_icbper = $data["total_icbper"];
            $total_gravada = $data["total_gravadas"];
            $total_inafecta = $data["total_inafectas"];
            $total_exonerada = $data["total_exoneradas"];

            $codigo_venta = uniqid(rand());
            $referencia = $data['observacion'];
            $importe_total = $data['importe_total'];

            $id_serie = $data["serie_venta"];
            $correlativo = $this->get_correlativo($data);

            $afecta_detraccion = $data['tp_operacion_venta'] == 2 ? 1 : 0;

            $estado_pago = $data["forma_pago"] == 2 ? 'PENDIENTE' : 'PAGADO';
            $id_medio_pago = isset($data['medio_pago']) && $data['medio_pago'] !== "" ? $data['medio_pago'] : null;

            // INSERT venta
            $query = $conn->prepare("INSERT INTO venta (
            codigo, id_terminal, id_vendedor, id_cliente, id_forma_pago, id_medio_pago,
            id_tp_moneda, id_tp_comprobante, id_caja_chica, id_serie, serie,
            correlativo, descuento, op_igv, icbper, estado, envio_sunat,
            descrip_cdr_sunat, cod_qr, hash_cdr, file_xml, file_cdr, fecha_emision, op_gravada,
            op_exonerada, op_inafecta, total, cod_operacion, fecha_vencimiento, obs, id_sesionpersonal, 
            id_tp_operacion, afecta_detraccion, id_tp_venta
            )
            VALUES (
            :codigo, :id_terminal, :id_vendedor, :id_cliente, :forma_pago, :id_medio_pago,
            :id_tp_moneda, :id_tp_comprobante, :id_caja_chica, :id_serie, (SELECT serie FROM serie WHERE id_serie=:id_serie2),
            :correlativo, '', :igv, :icbper, :estado, 0,
            '', '', '', '', '', :fecha_emision, :op_gravada,
            :op_exonerada, :op_inafecta, :total, '', :fecha_vencimiento, :referencia, :id_sesionpersonal, 
            :tp_operacion_venta, :afecta_detraccion, '3'
            )");

            $query->bindParam(':codigo', $codigo_venta);
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
            $query->bindParam(':id_cliente', $data["cliente_id"]);
            $query->bindParam(':forma_pago', $data["forma_pago"]);
            $query->bindParam(':id_medio_pago', $id_medio_pago);
            $query->bindParam(':id_tp_moneda', $tp_moneda);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':id_caja_chica', $data["id_caja"]);
            $query->bindParam(':id_serie', $id_serie);
            $query->bindParam(':id_serie2', $id_serie);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':igv', $total_igv);
            $query->bindParam(':icbper', $total_icbper);
            $query->bindParam(':estado', $estado_pago);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':op_gravada', $total_gravada);
            $query->bindParam(':op_exonerada', $total_exonerada);
            $query->bindParam(':op_inafecta', $total_inafecta);
            $query->bindParam(':total', $importe_total);
            $query->bindParam(':fecha_vencimiento', $data['fecha_vencimiento']);
            $query->bindParam(':referencia', $referencia);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':tp_operacion_venta', $data['tp_operacion_venta']);
            $query->bindParam(':afecta_detraccion', $afecta_detraccion);

            $query->execute();

            // ID generado
            $id_comprobante = $conn->lastInsertId();
            $data['id_comprobante'] = $id_comprobante;

            // Registrar detalle
            $product_insert = $this->registrar_detalle($data, $conn);

            if (!$product_insert['success']) {
                throw new Exception("Error en el detalle del comprobante");
            }

            if ($data["forma_pago"] == 2) {
                $numero_cuota = "001";
                $estado_cuota = "N"; 
                $query = $conn->prepare("INSERT INTO cuota (
                comprobante_id, numero, importe, fecha_vencimiento, estado)
                VALUES(
                :comprobante_id, :numero, :importe, :fecha_vencimiento, :estado)
                ");
                $query->bindParam(':comprobante_id', $id_comprobante);
                $query->bindParam(':numero', $numero_cuota);
                $query->bindParam(':importe', $importe_total);
                $query->bindParam(':estado', $estado_cuota);
                $query->bindParam(':fecha_vencimiento', $data['fecha_credito']);
                $query->execute();
            }

            if ($data['tp_operacion_venta'] == 2) {
                $insert_detraccion = $this->add_detraccion($data, $conn);
                if (!$insert_detraccion['success']) {
                    throw new Exception("Error al registrar la detracción");
                }
            }

            $mensaje_sunat = '';
            $estado_sunat = '';

            // Enviar a SUNAT
            if (in_array($data["tp_comprobante"], [1, 3])) {

                $data_comprobante = $this->get_data_comprobante_sunat($id_comprobante);

                if (!$data_comprobante['success']) {
                    throw new Exception("Error al generar el comprobante para SUNAT");
                }

                $json_envio = json_encode($data_comprobante['message']);
                $sunat = json_decode($this->enviar_json_a_api($json_envio), true);

                if (empty($sunat) || !is_array($sunat)) {
                    throw new Exception("Error al generar CDR");
                }

                $resp = $sunat[0];
                $resp['id_comprobante'] = $id_comprobante;

                $this->actualizar_venta_sunat($resp, $conn);

                $mensaje_sunat = $resp['mensaje_sunat'];
                $estado_sunat = $resp['estado'];
            }

            $conn->commit();
            $this->generar_codQR($id_comprobante, $conn);

            $tipo_impresion = '';
            switch ($data["tp_comprobante"]) {
                case 1: // Factura electrónica
                case 3: // Boleta electrónica
                    $tipo_impresion = 'comprobante';
                    break;
                case 2: // Nota de venta
                    $tipo_impresion = 'nota_venta';
                    break;
                default:
                    $tipo_impresion = 'comprobante';
                    break;
            }

            $link_comprobante = URL . "facturador/impresion/" . $tipo_impresion . '/' . $id_comprobante;
            return [
                'success' => true,
                'message' => 'Comprobante generado correctamente',
                'link_comprobante' => $link_comprobante,
                "message_sunat" => $data['tp_comprobante'] != 2 ? $mensaje_sunat : '',
                "estado_sunat" => $data['tp_comprobante'] != 2 ? $estado_sunat : '',
                'id_venta' => $id_comprobante,
                'tp_comprobante' => $data['tp_comprobante']  // ← AÑADIR ESTO
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

    public function add_detraccion($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
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
            $query->bindParam(":comprobante_id", $data['id_comprobante']);
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


    public function registrar_detalle($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $productos = json_decode($data['productos'], true);
            $igv_config = $this->get_igv();
            $item = 0;

            foreach ($productos as $producto) {
                $item++;

                $id_producto = $producto[0];
                $cantidad = $producto[2];
                $valor_u = $producto[3];
                $valor_u_igv = $producto[4];
                $valor_total = $producto[5];
                $precio_total = $producto[6];
                $igv_total = $producto[7];
                $afectacion_item = $producto[8];
                $icbper_total = $producto[9];
                $porcentaje_igv = $igv_config;
                $factor_icbper = $producto[10];

                $query = $this->db->connect()->prepare("
                    INSERT INTO detalle_comprobante
                    (
                      comprobante_id, item, producto_id, cantidad, valor_unitario, 
                      precio_unitario, igv, porcentaje_igv, valor_total, icbper, 
                      factor_icbper, importe_total, afectacion_id
                    )
                    VALUES (
                      :comprobante_id, :item, :producto_id, :cantidad, :valor_unitario, 
                      :precio_unitario, :igv, :porcentaje_igv, :valor_total, :icbper, 
                      :factor_icbper, :importe_total, :afectacion_id
                    )
                ");
                $query->bindParam(':comprobante_id', $data["id_comprobante"]);
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
             v.id_cliente,
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

            //==================== EN CASO HAYA UNA DETRACCION=====================
            $data_detraccion = [];
            if ($data_venta['afecta_detraccion'] == 1) {
                $query = $this->db->connect()->prepare("
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
                $query->bindParam(":id_venta", $id_comprobante);
                $query->execute();
                $data_detraccion = $query->fetch(PDO::FETCH_ASSOC);

                if (!empty($emisor['cuenta_detraccion'])) {
                    $data_detraccion['cuenta_detraccion'] = $emisor['cuenta_detraccion'];
                }
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

            $cabecera = [
                'tipo_operacion' => $data_venta['afecta_detraccion'] == 1 ? "1004" : "0101",
                'tipo_comprobante' => $data_venta['tp_comprobante_codigo'],
                'moneda' => $data_venta['tp_moneda_codigo'],
                'serie' => $data_venta['serie'],
                'correlativo' => $data_venta['correlativo'],
                'total_op_gravadas' => $data_venta['op_gravada'],
                'igv' => $data_venta['op_igv'],
                'icbper' => $data_venta['icbper'],
                'total_op_exoneradas' => $data_venta['op_exonerada'],
                'total_op_inafectas' => $data_venta['op_inafecta'],
                'total_antes_impuestos' => $total_antes_impuestos,
                'total_impuestos' => $total_impuestos,
                'total_despues_impuestos' => $total_despues_impuestos,
                'descuento_global' => 0.00,
                'suma_descuento_item' => 0.00,
                'total_a_pagar' => $data_venta['total'],
                'fecha_emision' => $data_venta['fecha_emision_format'],
                'hora_emision' => $data_venta['hora_emision_format'],
                'fecha_vencimiento' => $data_venta['fecha_vencimiento'],
                'forma_pago' => ucwords(strtolower(ucfirst($data_venta['forma_pago']))),
                'monto_credito' => 0.00,
                'anexo_sucursal' => "0000",
                'cuotas' => [],
                'observacion' => $data_venta['obs'] ?? '',
                "detraccion" => $data_venta['afecta_detraccion'] == 1 ? true : false,
                "detraccion_data" => $data_venta['afecta_detraccion'] == 1 ? [
                    "codigo_detraccion" => $data_detraccion['tipo_detraccion_codigo'],
                    "ubigeo_origen" => $data_detraccion['ubigeo_origen'],
                    "direccion_origen" => $data_detraccion['origen_detraccion'],
                    "ubigeo_destino" => $data_detraccion['ubigeo_destino'],
                    "direccion_destino" => $data_detraccion['destino_detraccion'],
                    "pocentaje_detraccion" => $data_detraccion['porcentaje'],
                    "cuenta_banco" => $data_detraccion['cuenta_detraccion'],
                    "monto_detraccion" => $data_detraccion['monto_detraccion'],
                    "codigo_medio_pago" => $data_detraccion['medio_pago_codigo'],
                    "descripcion" => $data_detraccion['descripcion'],
                    "monto_ref_servicio" => $data_detraccion['valor_referencial'],
                    "valor_carga_efectiva" => $data_detraccion['v_ref_carga_efectiva'],
                    "valor_carga_util" => $data_detraccion['v_ref_carga_util']
                ] : []
            ];

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
            p.nombre,
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
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
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

    public function get_data_comprobante($id_comprobante = 0)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT * ,
               fr_comprobante,
               telefono_empresa, 
               nro_cuenta_bancaria,
               termscond_facturador
            FROM empresa
            ");
            $query->execute();
            $emisor = $query->fetch(PDO::FETCH_ASSOC);

            // Obtener terminales con su celular
            $query = $this->db->connect()->prepare("
            SELECT id_terminal, nombre, direccion_fiscal, celular
            FROM terminal
            WHERE id_empresa = :id_empresa
            ");
            $query->bindParam(":id_empresa", $emisor['id_empresa']);
            $query->execute();
            $terminales = $query->fetchAll(PDO::FETCH_ASSOC);

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
             v.obs,
             tp_op.nombre AS tipo_operacion_sunat,
             tp_op.codigo_sunat AS codigo_tp_operacion_sunat
            FROM venta v
             LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
             LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
             LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
             LEFT JOIN dt_venta dt_v ON dt_v.id_venta = v.id_venta
             LEFT JOIN programacion p ON p.id_programacion = dt_v.id_programacion
             LEFT JOIN vehiculo vh ON vh.id_vehiculo = p.id_vehiculo
             LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
             LEFT JOIN usuario vd ON vd.id_usuario = v.id_vendedor
             LEFT JOIN tp_operacion_venta tp_op ON tp_op.id = v.id_tp_operacion
            WHERE v.id_venta = :id_venta
            ");
            $query->bindParam(":id_venta", $id_comprobante);
            $query->execute();
            $data_venta = $query->fetch(PDO::FETCH_ASSOC);

            $query = $this->db->connect()->prepare("SELECT
            CONCAT(c.nombres,' ',c.apellidos ) AS nombres_cliente,
            c.id_tp_docu AS tipo_documento,
            c.num_docu AS num_docu,
            c.direccion AS direccion,  -- <-- Ya está incluida
            CONCAT(
                COALESCE(c.direccion, ''),
                ' - ',
                COALESCE(ub_c.distri, ''),
                ', ',
                COALESCE(ub_c.provi, ''),
                ', ',
                COALESCE(ub_c.depa, '')
            ) AS direccion_completa,
            c.ubigeo AS ubigeo_cliente,
            tp_d_c.descripcion AS tipo_doc_descripcion,
            COALESCE(ub_c.depa, '') AS ubigeo_depa,
            COALESCE(ub_c.provi, '') AS ubigeo_provi,
            COALESCE(ub_c.distri, '') AS ubigeo_distri
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
            dt_c.descripcion AS descripcion_producto,
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
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "No se ha podido obetener la data de la venta"];
                    break;
            }
        }
    }

    public function reenviar_venta($data)
    {
        try {
            $id_venta = $data["id_ventaPasaje"];
            $success = true;
            $data_comprobante = $this->get_data_comprobante_sunat($id_venta);
            return $data_comprobante['message'];
            if (!$data_comprobante['success']) {
                throw new Exception("Error al generar el comprobante para SUNAT");
            }

            $json_envio = json_encode($data_comprobante['message']);
            $sunat = json_decode($this->enviar_json_a_api($json_envio), true);

            if (empty($sunat) || !is_array($sunat)) {
                throw new Exception("Error al generar CDR");
            }

            $resp = $sunat[0];
            $resp['id_comprobante'] = $id_venta;

            $this->generar_codQR($id_venta);
            $this->actualizar_venta_sunat($resp);

            $mensaje_sunat = $resp['mensaje_sunat'];
            $estado_sunat = $resp['estado'];

            if ($estado_sunat == 2 || $estado_sunat == 3) {
                $success = false;
            }

            $link_comprobante = URL . "facturador/impresion/" . ($data["tp_comprobante"] == 2 ? 'nota_venta/' : 'comprobante/') . $id_venta;

            return [
                "success" => $success,
                "message" => [
                    "message" => $mensaje_sunat,
                    "link_comprobante" => $link_comprobante,
                    "estado_sunat" => $estado_sunat,
                    "message_sunat" => $mensaje_sunat,

                ]
            ];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "No se ha podido obetener la data de la venta"];
                    break;
            }
        }
    }

    public function anular_venta($data)
    {
        try {
            if ($data["tp_comprobante"] == 1 || $data["tp_comprobante"] == 3) {
                if ($data["tp_comprobante"] == 1) {
                    $this->set_resumen_f($data["id_venta"]);
                } else {
                    $this->set_resumen($data["id_venta"]);
                }
                $ultimo = $this->Ultimo_resumen();
                $json = $this->resumen($data["id_venta"], $ultimo);
                $resp_api = json_decode($this->enviar_json_a_api(json_encode($json, true)), true);
                $this->guardar_ticket($resp_api, $ultimo);
            }
            // Anuladno la venta
            $query = $this->db->connect()->prepare("UPDATE venta SET estado='ANULADO', total='0.00', op_gravada='0.00', op_exonerada='0.00', op_igv='0.00', porcentaje_venta='0.00', factor_p_venta='0.00' WHERE id_venta=:id_venta");
            $query->bindParam(":id_venta", $data["id_venta"]);
            $query->execute();

            return ['success' => true, "message" => "Venta anulada correctamente."];
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, 'message' => "Ha sucecido un error e3n el servidor"];
                    break;
                default:
                    return ['success' => false, 'message' => "Ha sucecido un error e3n el servidor"];
                    break;
            }
        }
    }

    public function get_items_comprobante($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT 
                dt_c.igv,
                dt_c.importe_total AS total,
                dt_c.afectacion_id AS afectacion,
                v.descuento,
                v.id_venta,
                p.nombre AS descripcion,
                dt_c.valor_total,
                u_m.abreviatura AS unidad_medida,
                dt_c.cantidad,
                dt_c.precio_unitario,
                dt_c.valor_unitario
                FROM detalle_comprobante dt_c
                LEFT JOIN venta v ON dt_c.comprobante_id = v.id_venta
                LEFT JOIN producto p ON dt_c.producto_id = p.id
                LEFT JOIN unidad_medida u_m ON p.unidad_id = u_m.id
                WHERE comprobante_id = :id_venta
            ");
            $query->bindParam(":id_venta", $data['id_venta']);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$item) {
                $item['op_exonerada'] = $item['afectacion'] == 2 ? $item['valor_total'] : 0.00;
                $item['op_gravada'] = $item['afectacion'] == 1 ? $item['valor_total'] : 0.00;
                $item['op_inafecta'] = $item['afectacion'] == 3 ? $item['valor_total'] : 0.00;
            }

            return ['success' => true, 'message' => $data];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
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
