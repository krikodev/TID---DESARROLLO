<?php

class CotizacionModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }


    public function getDataTable($data)
    {
        try {

            $selectFields = "
            ctz.id_cotizacion,
            ctz.codigo,
            ctz.id_serie,
            ctz.serie,
            ctz.correlativo,
            ctz.id_terminal,
            ctz.id_vendedor,
            ctz.id_cliente,

            CONCAT(
                c.nombres,
                ' ',
                c.apellidos
            ) AS cliente,

            c.num_docu AS ruc_cliente,
            c.id_tp_docu AS tipo_documento_client,

            ctz.id_tp_moneda,
            tp_m.descripcion AS moneda,
            tp_m.simbolo AS simbolo_moneda,

            ctz.fecha_emision,
            ctz.dias_validez,
            ctz.fecha_vencimiento,

            ctz.condicion_pago,
            ctz.dias_credito,

            ctz.subtotal,
            ctz.descuento,
            ctz.total,

            ctz.condiciones_servicio,
            ctz.obs,

            ctz.estado,
            ctz.id_sesionpersonal,
            ctz.fecha_creacion,
            ctz.updated_at,

            DATE_FORMAT(
                ctz.fecha_emision,
                '%d/%m/%Y'
            ) AS fecha_emision_format,

            DATE_FORMAT(
                ctz.fecha_vencimiento,
                '%d/%m/%Y'
            ) AS fecha_vencimiento_format,

            CONCAT(
                ctz.serie,
                '-',
                ctz.correlativo
            ) AS serie_correlativo,

            CASE
                WHEN ctz.fecha_vencimiento IS NULL
                    THEN NULL
                ELSE DATEDIFF(
                    ctz.fecha_vencimiento,
                    CURDATE()
                )
            END AS dias_para_vencer
        ";

            $baseQuery = "
            FROM cotizacion ctz

            LEFT JOIN usuario c
                ON c.id_usuario = ctz.id_cliente

            LEFT JOIN tp_moneda tp_m
                ON tp_m.id_tp_moneda = ctz.id_tp_moneda

            WHERE ctz.id_terminal = $this->id_terminal_sesion
        ";

            // =====================================================
            // CAMPOS BUSCABLES
            // =====================================================

            $searchColumns = [

                // Número / código de cotización
                "ctz.codigo",
                "ctz.serie",
                "ctz.correlativo",
                "CONCAT(ctz.serie, '-', ctz.correlativo)",

                // Fecha
                "ctz.fecha_emision",
                "DATE_FORMAT(ctz.fecha_emision, '%d/%m/%Y')",

                // Cliente
                "c.nombres",
                "c.apellidos",
                "CONCAT(c.nombres, ' ', c.apellidos)",
                "c.num_docu",

                // Condición de pago
                "ctz.condicion_pago",
                "ctz.dias_credito",

                // Importe
                "ctz.subtotal",
                "ctz.descuento",
                "ctz.total",
                "tp_m.descripcion",
                "tp_m.simbolo",
                "CONCAT(tp_m.simbolo, ' ', ctz.total)",

                // Vencimiento
                "ctz.fecha_vencimiento",
                "DATE_FORMAT(ctz.fecha_vencimiento, '%d/%m/%Y')",
                "ctz.dias_validez",

                // Estado
                "ctz.estado"
            ];

            $orderBy = "
            ctz.fecha_emision DESC,
            ctz.correlativo DESC
        ";

            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );
        } catch (PDOException $e) {

            return [
                "draw" => intval(
                    $data["draw"] ?? 1
                ),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" =>
                "Error en la consulta: " .
                    $e->getMessage()
            ];
        }
    }

    public function get_numero_cotizacion()
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("
                SELECT * FROM serie WHERE id_tp_comprobante=4 AND id_terminal=:id_terminal
            ");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($data) {
                return ['success' => true, "message" => $data];
            } else {
                return ['success' => false, "message" => 'Ooops.... no se ha registrado ninguna seria para este terminal'];
            }
        } catch (Exception $e) {
            return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
        }
    }

    public function add_register($data)
    {
        $conn = $this->db->connect();
        try {
            $conn->beginTransaction();
            date_default_timezone_set('America/Lima');
            $tp_moneda = $data['moneda'] ?? 1;
            $hora_actual = date('H:i:s');
            $fecha_emision = $data['fecha_emision'] . ' ' . $hora_actual;
            $codigo = uniqid(rand());
            $id_serie = $data['s_cotizacion'];
            $subtotal = $data['subtotal'] ?? 0;
            $descuento = $data['descuento'] ?? 0;
            $total = $data['total'] ?? 0;
            $condicion_pago = $data['condicion_pago'] ?? 'CONTADO';
            $igv = $data['igv'] ?? 0;
            $porcentaje_igv = $data['porcentaje_igv'] ?? 18;
            $incluye_igv = isset($data['incluye_igv']) ? (int)$data['incluye_igv'] : 1;
            $dias_credito = $condicion_pago === 'CREDITO' ? ($data['dias_credito'] ?? 0) : 0;
            $dias_validez = $data['dias_validez'] ?? 15;
            $fecha_vencimiento = $data['fecha_vencimiento'] ?? null;
            $condiciones_servicio = $data['condiciones_servicio'] ?? null;
            $obs = $data['obs'] ?? null;
            $ubigeo_origen = !empty($data['ubigeo_origen']) ? $data['ubigeo_origen'] : null;
            $direccion_origen = !empty($data['direccion_origen']) ? $data['direccion_origen'] : null; /* * DESTINO */
            $ubigeo_destino = !empty($data['ubigeo_destino']) ? $data['ubigeo_destino'] : null;
            $direccion_destino = !empty($data['direccion_destino']) ? $data['direccion_destino'] : null; /* * OBTENER SERIE */
            $query = $conn->prepare(" SELECT correlativo, serie FROM serie WHERE id_serie = :id_serie ");
            $query->bindParam(':id_serie', $id_serie);
            $query->execute();
            $serieData = $query->fetch(PDO::FETCH_ASSOC);
            if (!$serieData) {
                throw new Exception("No se encontró la serie de cotización.");
            }
            $correlativoInicial = $serieData['correlativo'];
            $correlativo = !empty($correlativoInicial) ? $correlativoInicial + 1 : 1;
            $serie = $serieData['serie']; /* * REGISTRAR COTIZACIÓN */
            $query = $conn->prepare("INSERT INTO cotizacion (
    codigo,
    id_serie,
    serie,
    correlativo,
    id_terminal,
    id_vendedor,
    id_cliente,
    ubigeo_origen,
    direccion_origen,
    ubigeo_destino,
    direccion_destino,
    id_tp_moneda,
    fecha_emision,
    dias_validez,
    fecha_vencimiento,
    condicion_pago,
    dias_credito,
    incluye_igv,

    subtotal,
    descuento,
    igv,
    porcentaje_igv,
    total,

    condiciones_servicio,
    obs,
    estado,
    id_sesionpersonal
)
VALUES (
    :codigo,
    :id_serie,
    :serie,
    :correlativo,
    :id_terminal,
    :id_vendedor,
    :id_cliente,
    :ubigeo_origen,
    :direccion_origen,
    :ubigeo_destino,
    :direccion_destino,
    :id_tp_moneda,
    :fecha_emision,
    :dias_validez,
    :fecha_vencimiento,
    :condicion_pago,
    :dias_credito,
    :incluye_igv,

    :subtotal,
    :descuento,
    :igv,
    :porcentaje_igv,
    :total,

    :condiciones_servicio,
    :obs,
    'BORRADOR',
    :id_sesionpersonal
)");
            $query->bindParam(':codigo', $codigo);
            $query->bindParam(':id_serie', $id_serie);
            $query->bindParam(':serie', $serie);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->bindParam(':id_vendedor', $this->id_usuario_sesion);
            $query->bindParam(':id_cliente', $data['cliente_id']);
            $query->bindValue(':ubigeo_origen', $ubigeo_origen, $ubigeo_origen === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':direccion_origen', $direccion_origen, $direccion_origen === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':ubigeo_destino', $ubigeo_destino, $ubigeo_destino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':direccion_destino', $direccion_destino, $direccion_destino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindParam(':id_tp_moneda', $tp_moneda);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':dias_validez', $dias_validez);
            $query->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $query->bindParam(':condicion_pago', $condicion_pago);
            $query->bindParam(':dias_credito', $dias_credito);
            $query->bindParam(':incluye_igv', $incluye_igv);
            $query->bindParam(':subtotal', $subtotal);
            $query->bindParam(':descuento', $descuento);
            $query->bindParam(':igv', $igv);
            $query->bindParam(':porcentaje_igv', $porcentaje_igv);
            $query->bindParam(':total', $total);
            $query->bindParam(':condiciones_servicio', $condiciones_servicio);
            $query->bindParam(':obs', $obs);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->execute();
            $id_cotizacion = $conn->lastInsertId();
            $data['id_cotizacion'] = $id_cotizacion; /* * REGISTRAR DETALLES */
            $detalle = $this->registrar_detalle($data, $conn);
            if (!$detalle['success']) {
                throw new Exception("Error al registrar el detalle de la cotización: " . ($detalle['error'] ?? ''));
            } /* * ACTUALIZAR CORRELATIVO */
            $correlativo_update = $this->actualizar_correlativo($id_serie, $correlativo, $conn);
            if (!$correlativo_update['success']) {
                throw new Exception("Error al actualizar el correlativo.");
            }
            $conn->commit();
            return ["success" => true, "message" => "Cotización registrada correctamente.", "id_cotizacion" => $id_cotizacion, "link_comprobante" => URL . "cotizacion/impresion/cotizacion/" . $id_cotizacion, "link_comprobante_a4" => URL . "cotizacion/impresion/cotizacion_a4/" . $id_cotizacion];
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ["success" => false, "message" => $e->getMessage()];
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            if ($e->getCode() == '23000') {
                return ["success" => false, "message" => "Registro duplicado."];
            }
            return ["success" => false, "message" => "Error de base de datos: " . $e->getMessage()];
        }
    }

    public function edit_register($data)
    {
        $conn = $this->db->connect();
        try {
            $conn->beginTransaction();
            date_default_timezone_set('America/Lima');
            $tp_moneda = $data['moneda'];
            $hora_actual = date('H:i:s');
            $fecha_emision = $data['fecha_emision'] . ' ' . $hora_actual;
            $id_cotizacion = $data['id_cotizacion'];
            $subtotal = $data['subtotal'] ?? 0;
            $descuento = $data['descuento'] ?? 0;
            $total = $data['total'] ?? 0;
            $condicion_pago = $data['condicion_pago'] ?? 'CONTADO';
            $dias_credito = $condicion_pago === 'CREDITO' ? ($data['dias_credito'] ?? 0) : 0;
            $dias_validez = $data['dias_validez'] ?? 15;
            $fecha_vencimiento = $data['fecha_vencimiento'] ?? null;
            $condiciones_servicio = $data['condiciones_servicio'] ?? null;
            $obs = $data['obs'] ?? null;
            $incluye_igv = isset($data['incluye_igv']) ? (int)$data['incluye_igv'] : 1; /* * ORIGEN */
            $igv = $data['igv'] ?? 0;
            $porcentaje_igv = $data['porcentaje_igv'] ?? 18;
            $ubigeo_origen = !empty($data['ubigeo_origen']) ? $data['ubigeo_origen'] : null;
            $direccion_origen = !empty($data['direccion_origen']) ? $data['direccion_origen'] : null; /* * DESTINO */
            $ubigeo_destino = !empty($data['ubigeo_destino']) ? $data['ubigeo_destino'] : null;
            $direccion_destino = !empty($data['direccion_destino']) ? $data['direccion_destino'] : null; /* * ACTUALIZAR COTIZACIÓN */
            $query = $conn->prepare(" UPDATE cotizacion SET id_cliente = :id_cliente, ubigeo_origen = :ubigeo_origen, direccion_origen = :direccion_origen, 
            ubigeo_destino = :ubigeo_destino, direccion_destino = :direccion_destino, id_tp_moneda = :id_tp_moneda, 
            fecha_emision = :fecha_emision, dias_validez = :dias_validez, fecha_vencimiento = :fecha_vencimiento, 
            condicion_pago = :condicion_pago, dias_credito = :dias_credito, incluye_igv = :incluye_igv, subtotal = :subtotal,
            descuento = :descuento, igv = :igv, porcentaje_igv = :porcentaje_igv, total = :total, condiciones_servicio = :condiciones_servicio, obs = :obs
            WHERE id_cotizacion = :id_cotizacion AND id_terminal = :id_terminal ");
            $query->bindParam(':id_cliente', $data['cliente_id']);
            $query->bindValue(':ubigeo_origen', $ubigeo_origen, $ubigeo_origen === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':direccion_origen', $direccion_origen, $direccion_origen === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':ubigeo_destino', $ubigeo_destino, $ubigeo_destino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindValue(':direccion_destino', $direccion_destino, $direccion_destino === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $query->bindParam(':id_tp_moneda', $tp_moneda);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':dias_validez', $dias_validez);
            $query->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $query->bindParam(':condicion_pago', $condicion_pago);
            $query->bindParam(':dias_credito', $dias_credito);
            $query->bindParam(':incluye_igv', $incluye_igv);
            $query->bindParam(':subtotal', $subtotal);
            $query->bindParam(':descuento', $descuento);
            $query->bindParam(':igv', $igv);
            $query->bindParam(':porcentaje_igv', $porcentaje_igv);
            $query->bindParam(':total', $total);
            $query->bindParam(':condiciones_servicio', $condiciones_servicio);
            $query->bindParam(':obs', $obs);
            $query->bindParam(':id_cotizacion', $id_cotizacion);
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->execute(); /* * IMPORTANTE: * * rowCount() puede devolver 0 si los datos * enviados son exactamente iguales a los actuales. * * Por eso no debemos interpretar rowCount() = 0 * como que la cotización no existe. */
            $query = $conn->prepare(" SELECT id_cotizacion FROM cotizacion WHERE id_cotizacion = :id_cotizacion AND id_terminal = :id_terminal LIMIT 1 ");
            $query->bindParam(':id_cotizacion', $id_cotizacion);
            $query->bindParam(':id_terminal', $this->id_terminal_sesion);
            $query->execute();
            if (!$query->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("No se encontró la cotización a editar.");
            } /* * ELIMINAR DETALLES ANTERIORES */
            $query = $conn->prepare(" DELETE FROM detalle_cotizacion WHERE cotizacion_id = :id_cotizacion ");
            $query->bindParam(':id_cotizacion', $id_cotizacion);
            $query->execute(); /* * REGISTRAR NUEVAMENTE LOS DETALLES */
            $data['id_cotizacion'] = $id_cotizacion;
            $detalle = $this->registrar_detalle($data, $conn);
            if (!$detalle['success']) {
                throw new Exception("Error al registrar el detalle de la cotización: " . ($detalle['error'] ?? ''));
            }
            $conn->commit();
            return ["success" => true, "message" => "Cotización actualizada correctamente.", "id_cotizacion" => $id_cotizacion, "link_comprobante" => URL . "cotizacion/impresion/cotizacion/" . $id_cotizacion, "link_comprobante_a4" => URL . "cotizacion/impresion/cotizacion_a4/" . $id_cotizacion];
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ["success" => false, "message" => $e->getMessage()];
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            if ($e->getCode() == '23000') {
                return ["success" => false, "message" => "Registro duplicado."];
            }
            return ["success" => false, "message" => "Error de base de datos: " . $e->getMessage()];
        }
    }

    public function registrar_detalle($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $productos = json_decode($data['productos'], true);
            if (!is_array($productos) || empty($productos)) {
                throw new Exception("No se encontraron productos para registrar.");
            }
            $item = 0;
            foreach ($productos as $producto) {
                $item++;
                $id_producto = !empty($producto['id_producto']) ? $producto['id_producto'] : null;
                $cantidad_viajes = $producto['cantidad'] ?? 0;
                $id_tipo_unidad = !empty($producto['id_unidad']) ? $producto['id_unidad'] : null;
                $precio_unitario = $producto['precio_unitario'] ?? 0;
                $importe_total = $producto['subtotal'] ?? 0; /* * VALIDAR PRODUCTO */
                if (empty($id_producto)) {
                    throw new Exception("El producto del item {$item} no es válido.");
                } /* * VALIDAR CANTIDAD */
                if ($cantidad_viajes <= 0) {
                    throw new Exception("La cantidad de viajes del item {$item} debe ser mayor a cero.");
                } /* * REGISTRAR DETALLE * * Ya NO se registra: * * - ubigeo_origen * - direccion_origen * - ubigeo_destino * - direccion_destino * * Estos datos pertenecen a cotizacion. */
                $query = $conn->prepare(" INSERT INTO detalle_cotizacion ( cotizacion_id, item, producto_id, cantidad_viajes, id_tipo_unidad, precio_unitario, importe_total ) VALUES ( :cotizacion_id, :item, :producto_id, :cantidad_viajes, :id_tipo_unidad, :precio_unitario, :importe_total ) ");
                $query->bindValue(':cotizacion_id', $data['id_cotizacion'], PDO::PARAM_INT);
                $query->bindValue(':item', $item, PDO::PARAM_INT);
                $query->bindValue(':producto_id', $id_producto, PDO::PARAM_INT);
                $query->bindValue(':cantidad_viajes', $cantidad_viajes);
                $query->bindValue(':id_tipo_unidad', $id_tipo_unidad, $id_tipo_unidad === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $query->bindValue(':precio_unitario', $precio_unitario);
                $query->bindValue(':importe_total', $importe_total);
                $query->execute();
            }
            return ["success" => true];
        } catch (PDOException $e) {
            return ["success" => false, "error" => $e->getMessage()];
        } catch (Exception $e) {
            return ["success" => false, "error" => $e->getMessage()];
        }
    }

    public function actualizar_correlativo($id_serie, $correlativo, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            $query = $conn->prepare("UPDATE serie SET correlativo=:correlativo WHERE id_serie=:id_serie");
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':id_serie', $id_serie);
            $query->execute();
            return ['success' => true, "message" => "Correlativo actualizado correctamente."];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error al actualizar el correlativo: " . $e->getMessage()];
        }
    }

    public function getCotizacion($data)
    {
        $id_cotizacion = $data['id_cotizacion'] ?? null;
        $conn = $this->db->connect();

        try {

            /*
         * =========================================================
         * OBTENER CABECERA DE LA COTIZACIÓN
         * =========================================================
         *
         * Origen y destino pertenecen ahora a cotizacion,
         * no a detalle_cotizacion.
         */

            $query = $conn->prepare("
            SELECT
                c.id_cotizacion,
                c.codigo,
                c.id_serie,
                c.serie,
                c.correlativo,
                c.id_terminal,
                c.id_vendedor,
                c.id_cliente,
                c.id_tp_moneda,

                c.fecha_emision,
                c.dias_validez,
                c.fecha_vencimiento,

                c.condicion_pago,
                c.dias_credito,
                c.incluye_igv,

                c.subtotal,
                c.descuento,
                c.igv,
                c.porcentaje_igv,
                c.total,

                c.condiciones_servicio,
                c.obs,
                c.estado,

                c.id_sesionpersonal,
                c.fecha_creacion,
                c.updated_at,

                /*
                 * ORIGEN
                 */
                c.ubigeo_origen,
                CONCAT(
                    uo.depa,
                    ' - ',
                    uo.provi,
                    ' - ',
                    uo.distri
                ) AS nombre_origen,
                c.direccion_origen,

                /*
                 * DESTINO
                 */
                c.ubigeo_destino,
                CONCAT(
                    ud.depa,
                    ' - ',
                    ud.provi,
                    ' - ',
                    ud.distri
                ) AS nombre_destino,
                c.direccion_destino,

                /*
                 * CLIENTE
                 */
                CONCAT(
                    cli.num_docu,
                    ' - ',
                    cli.nombres,
                    ' ',
                    cli.apellidos
                ) AS nombre_cliente,

                /*
                 * NÚMERO DE COTIZACIÓN
                 */
                CONCAT(
                    c.serie,
                    '-',
                    c.correlativo
                ) AS numero

            FROM cotizacion c

            LEFT JOIN usuario cli
                ON c.id_cliente = cli.id_usuario

            LEFT JOIN ubigeo uo
                ON uo.cod_ubigeo = c.ubigeo_origen

            LEFT JOIN ubigeo ud
                ON ud.cod_ubigeo = c.ubigeo_destino

            WHERE c.id_cotizacion = :id_cotizacion
              AND c.id_terminal = :id_terminal

            LIMIT 1
        ");

            $query->bindValue(
                ':id_cotizacion',
                $id_cotizacion,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':id_terminal',
                $this->id_terminal_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $cotizacion = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cotizacion) {

                return [
                    "success" => false,
                    "message" => "No se encontró la cotización."
                ];
            }


            /*
         * =========================================================
         * OBTENER DETALLES
         * =========================================================
         *
         * Ya NO buscamos:
         *
         * d.ubigeo_origen
         * d.direccion_origen
         * d.ubigeo_destino
         * d.direccion_destino
         *
         * porque esos campos ahora pertenecen a cotizacion.
         */

            $query = $conn->prepare("
            SELECT
                d.id_detalle,
                d.cotizacion_id,
                d.item,
                d.producto_id,

                p.nombre AS descripcion,

                d.cantidad_viajes,

                d.id_tipo_unidad,

                tu.nombre AS unidad,

                d.precio_unitario,
                d.importe_total

            FROM detalle_cotizacion d

            LEFT JOIN producto p
                ON p.id = d.producto_id

            LEFT JOIN tipo_unidad_transporte tu
                ON tu.id = d.id_tipo_unidad

            WHERE d.cotizacion_id = :id_cotizacion

            ORDER BY d.item ASC
        ");

            $query->bindValue(
                ':id_cotizacion',
                $id_cotizacion,
                PDO::PARAM_INT
            );

            $query->execute();

            $detalles = $query->fetchAll(PDO::FETCH_ASSOC);


            /*
         * =========================================================
         * AGREGAR DETALLES A LA CABECERA
         * =========================================================
         */

            $cotizacion['detalles'] = $detalles;


            return [
                "success" => true,
                "data" => $cotizacion
            ];
        } catch (PDOException $e) {

            return [
                "success" => false,
                "message" =>
                "Error de base de datos: " .
                    $e->getMessage()
            ];
        }
    }

    public function get_cotizacion_data($id_cotizacion)
    {
        try {

            $conn = $this->db->connect();

            //=========================================================
            // EMPRESA
            //=========================================================

            $query = $conn->prepare("
            SELECT
                e.envio_ose AS ose,
                e.num_docu AS ruc,
                e.razon_social AS razon_social,
                e.razon_social AS nombre_comercial,
                e.ubigeo AS ubigeo,

                CONCAT(
                    ub.distri,
                    ' - ',
                    ub.provi,
                    ' - ',
                    ub.depa
                ) AS ciudad,

                e.telefono_empresa AS celular,
                e.direccion_fiscal AS direccion,
                e.user_sol AS usuario_sol,
                e.pass_sol AS clave_sol,
                td.id_tp_docu AS tipodoc,
                e.guia_id AS api_id,
                e.guia_clave AS api_clave,
                e.nro_cuenta_BN AS cuenta_detraccion,
                e.correo,
                e.logo

            FROM terminal t

            LEFT JOIN empresa e
                ON e.id_empresa = t.id_empresa

            LEFT JOIN ubigeo ub
                ON ub.cod_ubigeo = e.ubigeo

            LEFT JOIN tp_docu td
                ON e.tp_docu = td.descripcion

            WHERE t.id_terminal = :id_terminal
            ");

            $query->bindValue(
                ":id_terminal",
                $this->id_terminal_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $header_empresa = $query->fetch(PDO::FETCH_ASSOC);

            if (!$header_empresa) {

                throw new Exception(
                    "No se encontró la información de la empresa."
                );
            }

            $header_empresa["pais"] = "PE";


            //=========================================================
            // CUENTAS BANCARIAS
            //=========================================================

            $query = $conn->prepare("
            SELECT
                b.nombre AS banco,
                ec.numero_cuenta AS cuenta,
                ec.cci

            FROM empresa_cuenta_bancaria ec

            INNER JOIN banco b
                ON b.id_banco = ec.id_banco

            WHERE ec.id_empresa = :id_empresa
              AND ec.estado = 'ACTIVO'
              AND ec.mostrar_reportes = 1

            ORDER BY b.nombre
            ");

            $query->bindValue(
                ":id_empresa",
                $this->id_empresa_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $header_empresa["cuentas"] =
                $query->fetchAll(PDO::FETCH_ASSOC);


            if (!empty($header_empresa["cuenta_detraccion"])) {

                $header_empresa["cuentas"][] = [
                    "banco" => "Banco de la Nación",
                    "cuenta" =>
                    "CTA. CTL. DETRACCIÓN " .
                        $header_empresa["cuenta_detraccion"],
                    "cci" => ""
                ];
            }


            //=========================================================
            // COTIZACIÓN
            //=========================================================

            /*
         * IMPORTANTE:
         *
         * Origen y destino ahora pertenecen a la cabecera
         * de cotizacion.
         */

            $query = $conn->prepare("
            SELECT

                c.*,
                c.fecha_emision AS fecha,
                CONCAT(
                    c.serie,
                    '-',
                    c.correlativo
                ) AS numero,

                CONCAT(
                    cli.nombres,
                    ' ',
                    cli.apellidos
                ) AS nombre_cliente,

                cli.num_docu AS num_docu_cliente,

                cli.direccion AS direccion_cliente,

                CONCAT(
                    ubc.depa,
                    ' - ',
                    ubc.provi,
                    ' - ',
                    ubc.distri
                ) AS ciudad_cliente,

                cli.celular AS celular_cliente,

                cli.email AS email_cliente,

                tpd.descripcion AS tipo_documento_cliente,
                tpm.simbolo AS simbolo_moneda,
                tpm.codigo AS codigo_moneda,

                CONCAT(
                    uo.depa,
                    ' - ',
                    uo.provi,
                    ' - ',
                    uo.distri
                ) AS nombre_origen,


                CONCAT(
                    ud.depa,
                    ' - ',
                    ud.provi,
                    ' - ',
                    ud.distri
                ) AS nombre_destino

            FROM cotizacion c
            LEFT JOIN usuario cli ON cli.id_usuario = c.id_cliente
            LEFT JOIN ubigeo ubc ON ubc.cod_ubigeo = cli.ubigeo
            LEFT JOIN tp_docu tpd ON tpd.id_tp_docu = cli.id_tp_docu
            LEFT JOIN ubigeo uo ON uo.cod_ubigeo = c.ubigeo_origen
            LEFT JOIN ubigeo ud ON ud.cod_ubigeo = c.ubigeo_destino
            LEFT JOIN tp_moneda tpm ON c.id_tp_moneda = tpm.id_tp_moneda
            WHERE c.id_cotizacion = :id_cotizacion
              AND c.id_terminal = :id_terminal
            LIMIT 1
            ");

            $query->bindValue(
                ":id_cotizacion",
                $id_cotizacion,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ":id_terminal",
                $this->id_terminal_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $data_cotizacion =
                $query->fetch(PDO::FETCH_ASSOC);

            if (!$data_cotizacion) {

                throw new Exception(
                    "No se encontró la cotización."
                );
            }


            //=========================================================
            // CLIENTE
            //=========================================================

            $data_cliente = [

                "nombre_cliente" =>
                $data_cotizacion["nombre_cliente"] ?? "",

                "num_docu" =>
                $data_cotizacion["num_docu_cliente"] ?? "",

                "direccion" =>
                $data_cotizacion["direccion_cliente"] ?? "",

                "ciudad" =>
                $data_cotizacion["ciudad_cliente"] ?? "",

                "celular" =>
                $data_cotizacion["celular_cliente"] ?? "",

                "email" =>
                $data_cotizacion["email_cliente"] ?? "",

                "tipo_documento" =>
                $data_cotizacion["tipo_documento_cliente"] ?? ""
            ];


            //=========================================================
            // DETALLE
            //=========================================================

            /*
         * El detalle ya NO contiene:
         *
         * ubigeo_origen
         * direccion_origen
         * ubigeo_destino
         * direccion_destino
         *
         * Estos datos pertenecen a la cabecera.
         */

            $query = $conn->prepare("
            SELECT

                dt.item,

                dt.producto_id,

                p.nombre AS descripcion,

                dt.cantidad_viajes AS cant_vjs,

                dt.id_tipo_unidad,

                tp_u.nombre AS tip_unidad,

                dt.precio_unitario AS p_unitario,

                dt.importe_total

            FROM detalle_cotizacion dt

            LEFT JOIN producto p
                ON p.id = dt.producto_id

            LEFT JOIN tipo_unidad_transporte tp_u
                ON tp_u.id = dt.id_tipo_unidad

            WHERE dt.cotizacion_id = :id_cotizacion

            ORDER BY dt.item ASC
        ");

            $query->bindValue(
                ":id_cotizacion",
                $id_cotizacion,
                PDO::PARAM_INT
            );

            $query->execute();

            $data_productos =
                $query->fetchAll(PDO::FETCH_ASSOC);


            //=========================================================
            // RESPUESTA
            //=========================================================

            return [

                "success" => true,

                "HEADER_EMPRESA" =>
                $header_empresa,

                "COTIZACION" =>
                $data_cotizacion,

                "detraccion" =>
                [],

                "CLIENTE" =>
                $data_cliente,

                "productos" =>
                $data_productos,

                "terminos_condiciones" =>
                $data_cotizacion["condiciones_servicio"] ?? ""
            ];
        } catch (Exception $e) {

            return [

                "success" => false,

                "message" =>
                $e->getMessage()
            ];
        }
    }

    public function buscar_productos_cotizacion($data)
    {
        try {

            $conn = $this->db->connect();

            $texto = trim($data['texto'] ?? '');

            if ($texto === '') {
                return [
                    'success' => true,
                    'data' => [],
                    'hasMore' => false
                ];
            }

            $palabras = preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY);

            $condiciones = [];
            $params = [];

            foreach ($palabras as $i => $palabra) {

                $keyNombre = ":n{$i}";
                $keyCodigo = ":c{$i}";

                $condiciones[] = "(p.nombre LIKE {$keyNombre} OR p.codigo_interno LIKE {$keyCodigo})";

                $params[$keyNombre] = "%{$palabra}%";
                $params[$keyCodigo] = "%{$palabra}%";
            }

            $where = implode(' AND ', $condiciones);

            $params[':texto1'] = $texto;
            $params[':texto2'] = $texto;
            $params[':texto3'] = $texto;

            $query = $conn->prepare("
            SELECT
                p.id AS id_producto,
                p.codigo_interno,
                p.nombre,
                p.valor_unitario,
                p.tipo_afectacion_id,
                CONCAT(p.codigo_interno, ' - ', p.nombre) AS descripcion_prod
            FROM producto p
            WHERE
                p.modulo = 'cotizacion'
                AND {$where}
            ORDER BY
                (p.codigo_interno = :texto1) DESC,
                (p.codigo_interno LIKE CONCAT(:texto2, '%')) DESC,
                (p.nombre LIKE CONCAT(:texto3, '%')) DESC,
                p.nombre ASC
            LIMIT 10
            ");

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();

            return [
                'success' => true,
                'data' => $query->fetchAll(PDO::FETCH_ASSOC),
                'hasMore' => false
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    public function crear_servicio($data)
    {
        $conn = $this->db->connect();

        try {

            $data = array_merge([
                "descripcion_producto" => "",
                "precio_unitario" => 0,
                "afectacion_prod" => 2,
                "estado_prod" => 1,
                "codigo_sunat" => "",
                "afecto_icbper" => 0
            ], $data);

            $conn->beginTransaction();

            $tipo = "";
            $codigo_interno = null;
            $factor_icbper = 0.00;
            $unidad_medida = 10;
            $modulo = "cotizacion";

            $query = $conn->prepare("
            INSERT INTO producto (
                nombre,
                descripcion,
                valor_unitario,
                tipo_afectacion_id,
                unidad_id,
                codigo_interno,
                codigo_sunat,
                afecto_icbper,
                factor_icbper,
                tipo,
                estado,
                modulo
            )
            VALUES (
                :nombre,
                :descripcion,
                :valor_unitario,
                :tipo_afectacion,
                :unidad_id,
                :codigo_interno,
                :codigo_sunat,
                :afecto_icbper,
                :factor_icbper,
                :tipo,
                :estado,
                :modulo
            )
            ");

            $query->bindParam(":nombre", $data["nombre_producto"]);
            $query->bindParam(":descripcion", $data["descripcion_producto"]);
            $query->bindParam(":valor_unitario", $data["precio_unitario"]);
            $query->bindParam(":tipo_afectacion", $data["afectacion_prod"]);
            $query->bindParam(":unidad_id", $unidad_medida);
            $query->bindParam(":codigo_interno", $codigo_interno);
            $query->bindParam(":codigo_sunat", $data["codigo_sunat"]);
            $query->bindParam(":afecto_icbper", $data["afecto_icbper"]);
            $query->bindParam(":factor_icbper", $factor_icbper);
            $query->bindParam(":tipo", $tipo);
            $query->bindParam(":estado", $data["estado_prod"]);
            $query->bindParam(":modulo", $modulo);

            $query->execute();

            $id_producto = $conn->lastInsertId();

            $codigo_interno = "P" . str_pad($id_producto, 4, "0", STR_PAD_LEFT);

            $update = $conn->prepare("
            UPDATE producto
            SET codigo_interno = :codigo_interno
            WHERE id = :id_producto
            ");

            $update->bindParam(":codigo_interno", $codigo_interno);
            $update->bindParam(":id_producto", $id_producto);
            $update->execute();

            $producto = $this->getProductoById($id_producto, $conn);

            $conn->commit();

            return [
                "success" => true,
                "message" => "Registro creado con éxito",
                "producto" => $producto
            ];
        } catch (PDOException $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {

                case "23000":
                    return [
                        "success" => false,
                        "message" => "Ya existe un registro con estos datos"
                    ];

                default:
                    return [
                        "success" => false,
                        "message" => "Ha ocurrido un error, inténtelo más tarde."
                    ];
            }
        }
    }

    private function getProductoById($id_producto, $conn)
    {
        $query = $conn->prepare("
        SELECT
            id AS id_producto,
            codigo_interno,
            nombre,
            nombre AS nombre_producto,
            CONCAT(codigo_interno,' - ',nombre) AS descripcion_prod,
            valor_unitario,
            tipo_afectacion_id,
            factor_icbper
        FROM producto
        WHERE id = :id_producto
        ");

        $query->bindParam(":id_producto", $id_producto);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function get_servicios($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT
            dt.precio_unitario,
            dt.valor_unitario,
            dt.cantidad_viajes AS cant_vjs,
            p.nombre AS descripcion,
            tp_u.nombre AS tip_unidad,
            dt.porcentaje_igv,
            dt.igv,
            dt.producto_id,
            dt.afectacion_id,
            dt.id_tipo_unidad
            FROM detalle_cotizacion dt
            LEFT JOIN producto p
                ON p.id = dt.producto_id
            LEFT JOIN tipo_unidad_transporte tp_u
                ON tp_u.id = dt.id_tipo_unidad
            WHERE dt.cotizacion_id = :id_cotizacion
            ORDER BY dt.item
            ");

            $query->bindParam(":id_cotizacion", $data['id_cotizacion']);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function aceptar_cotizacion($data)
    {
        $conn = $this->db->connect();

        try {
            if (empty($data['id_cotizacion'])) {
                return ['success' => false, 'message' => 'ID de cotización no válido.'];
            }

            $conn->beginTransaction();

            $checkQuery = $conn->prepare("
            SELECT estado FROM cotizacion WHERE id_cotizacion = :id_cotizacion FOR UPDATE
           ");
            $checkQuery->bindParam(":id_cotizacion", $data['id_cotizacion']);
            $checkQuery->execute();
            $cotizacion = $checkQuery->fetch(PDO::FETCH_ASSOC);

            if (!$cotizacion) {
                $conn->rollBack();
                return ['success' => false, 'message' => 'La cotización no existe.'];
            }

            if ($cotizacion['estado'] !== 'BORRADOR') {
                $conn->rollBack();
                return ['success' => false, 'message' => 'Solo se pueden aceptar cotizaciones en estado BORRADOR.'];
            }

            $query = $conn->prepare("
            UPDATE cotizacion
            SET estado = 'ACEPTADA'
            WHERE id_cotizacion = :id_cotizacion
            ");
            $query->bindParam(":id_cotizacion", $data['id_cotizacion']);
            $query->execute();

            if ($query->rowCount() === 0) {
                $conn->rollBack();
                return ['success' => false, 'message' => 'No se pudo actualizar la cotización.'];
            }

            $conn->commit();

            return ['success' => true, 'message' => 'La cotización ha sido aceptada correctamente'];
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    // Conversion a comprobante
    public function convertir_cotizacion($data)
    {
        $conn = $this->db->connect(); // sacamos la conexión antes del try para poder usarla también en el catch

        try {
            if (empty($data['id_cotizacion'])) {
                throw new Exception('Sin registro para convertir a comprobante');
            }

            $id_cotizacion = $data['id_cotizacion'];
            $data['tp_comprobante'] = $data['tipo_comprobante'] == 'boleta' ? 3 : 1;

            $conn->beginTransaction();

            // Armado de json para envio a api
            $crear_venta = $this->crear_registro_venta($data, $conn);
            if (!$crear_venta['success']) {
                throw new Exception('No se ha podido crear la venta: ' . $crear_venta['message']);
            }
            $id_comprobante = $crear_venta['message'];

            if (in_array($data["tp_comprobante"], [1, 3])) {

                $data_comprobante = $this->get_data_comprobante_sunat($id_comprobante, $conn);
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

                $actualizar_venta = $this->actualizar_venta_sunat($resp, $conn);
                if (!$actualizar_venta['success']) {
                    throw new Exception('No se pudo actualizar la venta');
                }
                $actualizar_cotizacion = $this->actualizar_cotizacion_estado($id_cotizacion, $id_comprobante, $conn);
                if (!$actualizar_cotizacion['success']) {
                    throw new Exception('No se pudo actualizar la venta');
                }
                $mensaje_sunat = $resp['mensaje_sunat'];
                $estado_sunat = $resp['estado'];
            } else {
                $mensaje_sunat = '';
                $estado_sunat = '';
            }

            $conn->commit();

            $link_comprobante = URL . "facturador/impresion/comprobante" . $id_comprobante;

            return [
                'success' => true,
                'message' => 'Comprobante generado correctamente',
                'link_comprobante' => $link_comprobante,
                "message_sunat" => $mensaje_sunat,
                "estado_sunat" => $estado_sunat,
                'id_venta' => $id_comprobante,
                'tp_comprobante' => $data['tp_comprobante']
            ];
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }
    public function crear_registro_venta($data, $conn = null)
    {
        try {

            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $query = $conn->prepare("
            SELECT 
            *
            FROM cotizacion
            WHERE id_cotizacion = :id_cotizacion
            ");
            $query->bindParam(":id_cotizacion", $data['id_cotizacion']);
            $query->execute();
            $data_cotizacion = $query->fetch(PDO::FETCH_ASSOC);
            if (!$data_cotizacion) {
                throw new Exception("No se encontró la cotización.");
            }

            $tp_moneda = 1;
            $hora_actual = date('H:i:s');
            $fecha_emision = $data['fecha_emision'] . ' ' . $hora_actual;
            $fecha_vencimiento = date('Y-m-d');
            $correlativo = $this->get_correlativo($data);
            $estado_pago = $data["forma_pago"] == 2 ? 'PENDIENTE' : 'PAGADO';
            $data['medio_pago'] = ($data['medio_pago'] === '') ? null : $data['medio_pago'];

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
            :id_tp_operacion, :afecta_detraccion, '5'
            )");

            $query->bindParam(':codigo', $data_cotizacion['codigo']);
            $query->bindParam(':id_terminal', $data_cotizacion['id_terminal']);
            $query->bindParam(':id_vendedor', $data_cotizacion['id_vendedor']);
            $query->bindParam(':id_cliente', $data_cotizacion["id_cliente"]);
            $query->bindParam(':forma_pago', $data["forma_pago"]);
            $query->bindParam(':id_medio_pago', $data['medio_pago']);
            $query->bindParam(':id_tp_moneda', $tp_moneda);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':id_caja_chica', $data["id_caja"]);
            $query->bindParam(':id_serie', $data['serie_venta']);
            $query->bindParam(':id_serie2', $data['serie_venta']);
            $query->bindParam(':correlativo', $correlativo);
            $query->bindParam(':igv', $data_cotizacion['op_igv']);
            $query->bindParam(':icbper', $data_cotizacion['icbper']);
            $query->bindParam(':estado', $estado_pago);
            $query->bindParam(':fecha_emision', $fecha_emision);
            $query->bindParam(':op_gravada', $data_cotizacion['op_gravada']);
            $query->bindParam(':op_exonerada', $data_cotizacion['op_exonerada']);
            $query->bindParam(':op_inafecta', $data_cotizacion['op_inafecta']);
            $query->bindParam(':total', $data_cotizacion['total']);
            $query->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $query->bindParam(':referencia', $data['obs_comprobante']);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':id_tp_operacion', $data_cotizacion['id_tp_operacion']);
            $query->bindParam(':afecta_detraccion', $data_cotizacion['afecta_detraccion']);

            $query->execute();

            $id_comprobante = $conn->lastInsertId();
            $data['id_comprobante'] = $id_comprobante;


            $query = $conn->prepare("
            SELECT
            dt.precio_unitario,
            dt.valor_unitario,
            dt.cantidad_viajes AS cant_vjs,
            p.nombre AS descripcion,
            tp_u.nombre AS tip_unidad,
            dt.porcentaje_igv,
            dt.igv,
            dt.producto_id,
            dt.afectacion_id,
            dt.id_tipo_unidad,
            dt.valor_total,
            dt.importe_total
            FROM detalle_cotizacion dt
            LEFT JOIN producto p
                ON p.id = dt.producto_id
            LEFT JOIN tipo_unidad_transporte tp_u
                ON tp_u.id = dt.id_tipo_unidad
            WHERE dt.cotizacion_id = :id_cotizacion
            ORDER BY dt.item
            ");

            $query->bindParam(":id_cotizacion", $data['id_cotizacion']);
            $query->execute();
            $data_productos = $query->fetchAll(PDO::FETCH_ASSOC);


            if (!$data_productos) {
                throw new Exception("No se ha podido hallar los servicios de la cotizacion");
            }

            $data_detalle = [
                'id_comprobante' => $id_comprobante,
                'productos' => $data_productos
            ];

            $product_insert = $this->registrar_detalle_venta($data_detalle, $conn);

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
                $query->bindParam(':importe', $data['monto_credito']);
                $query->bindParam(':estado', $estado_cuota);
                $query->bindParam(':fecha_vencimiento', $data['fecha_credito']);
                $query->execute();
            }


            return ['success' => true, 'message' => $id_comprobante];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ups.... no se pudo crear la venta sunat'];
        }
    }
    public function registrar_detalle_venta($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $productos = $data['productos'];
            $item = 0;

            foreach ($productos as $producto) {
                $item++;

                $id_producto = $producto['producto_id'];
                $cantidad = $producto['cant_vjs'];
                $valor_u = $producto['valor_unitario'];
                $valor_u_igv = $producto['precio_unitario'];
                $valor_total = $producto['valor_total'];
                $precio_total = $producto['importe_total'];
                $igv_total = $producto['igv'];
                $afectacion_item = $producto['afectacion_id'];
                $icbper_total = 0.00;
                $porcentaje_igv = $producto['porcentaje_igv'];
                $factor_icbper = 0.00;
                $cantidad_viajes = $producto['cant_vjs'];
                $id_tipo_unidad = $producto['id_tipo_unidad'];

                $query = $conn->prepare("
                    INSERT INTO detalle_comprobante
                    (
                      comprobante_id, item, producto_id, cantidad, valor_unitario, 
                      precio_unitario, igv, porcentaje_igv, valor_total, icbper, 
                      factor_icbper, importe_total, afectacion_id, cantidad_viajes,
                      id_tipo_unidad
                    )
                    VALUES (
                      :comprobante_id, :item, :producto_id, :cantidad, :valor_unitario, 
                      :precio_unitario, :igv, :porcentaje_igv, :valor_total, :icbper, 
                      :factor_icbper, :importe_total, :afectacion_id, :cantidad_viajes,
                      :id_tipo_unidad
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
                $query->bindParam(':cantidad_viajes', $cantidad_viajes);
                $query->bindParam(':id_tipo_unidad', $id_tipo_unidad);
                $query->execute();
            };

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
    public function get_data_comprobante_sunat($id_comprobante, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }
            $emisor = $this->consult_emisor();

            $query = $conn->prepare("
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

            $cuotas = [];
            if ($data_venta['id_forma_pago'] == 2) {
                $query = $conn->prepare("
                  SELECT * FROM cuota
                  WHERE comprobante_id = :id_venta
                ");
                $query->bindParam(":id_venta", $id_comprobante);
                $query->execute();
                $data_cuota = $query->fetch(PDO::FETCH_ASSOC);

                if ($data_venta['id_forma_pago'] == 2) {
                    $cuotas[] = [
                        'numero' => $data_cuota['numero'],
                        'importe' => number_format($data_cuota['importe'], 2, '.', ''),
                        'vencimiento' => $data_cuota['fecha_vencimiento']
                    ];
                }
            }

            //==================== EN CASO HAYA UNA DETRACCION=====================
            $data_detraccion = [];
            if ($data_venta['afecta_detraccion'] == 1) {
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
                'monto_credito' => $data_venta['id_forma_pago'] == 2 ? number_format($data_cuota['importe'], 2, '.', '') : 0.00,
                'anexo_sucursal' => "0000",
                'cuotas' => $cuotas,
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

            $query = $conn->prepare("SELECT
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

            $query = $conn->prepare("
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
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde." . $e];
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
            $query = $conn->prepare("UPDATE venta SET
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

    public function actualizar_cotizacion_estado($id_cotizacion, $id_comprobante, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $query = $conn->prepare("UPDATE cotizacion SET
            estado='FACTURADA',
            id_venta= :id_venta
            WHERE id_cotizacion=:id_cotizacion
            ");
            $query->bindParam(':id_cotizacion', $id_cotizacion);
            $query->bindParam(':id_venta', $id_comprobante);
            $query->execute();

            return ['success' => true, "message" => "Registro actualizado correctamente."];
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

    public function enviar_json_a_api($json)
    {

        $api_url = API_URL;

        $opciones = [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            ]
        ];

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
