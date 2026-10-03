<?php
date_default_timezone_set('America/Lima');
class FleteModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {

            $search = normalizar_estado(
                $data['search']['value'] ?? ''
            );

            $data['search']['value'] = $search;

            // =========================================================
            // COLUMNAS DE BÚSQUEDA
            // =========================================================

            $searchColumns = [

                // ORIGEN / DESTINO
                "u_ori.distri",
                "u_desti.distri",

                "f.direccion_origen",
                "f.direccion_destino",

                // CLIENTE
                "cli.nombres",
                "cli.apellidos",
                "cli.num_docu",

                "CONCAT(cli.nombres, ' ', cli.apellidos)",

                "CONCAT(cli.nombres,' ',cli.apellidos,' ',cli.num_docu)",

                // CONDUCTOR PROPIO
                "c.nombres",
                "c.apellidos",
                "c.num_docu",

                "CONCAT(c.nombres, ' ', c.apellidos)",

                // VEHÍCULO PROPIO
                "v.placa",
                "v.descripcion",

                // PROVEEDOR TERCERIZADO
                "prov.nombres",
                "prov.apellidos",
                "prov.num_docu",

                "CONCAT(prov.nombres,' ',prov.apellidos)",

                // CONDUCTOR TERCERIZADO
                "ct.nombres",
                "ct.apellidos",
                "ct.num_docu",

                "CONCAT(ct.nombres,' ', ct.apellidos)",

                // VEHÍCULO TERCERIZADO
                "vt.placa",
                "vt.descripcion",

                // FLETE
                "f.fecha_salida",
                "f.hora_salida",

                "f.tipo_operacion",
                "f.condicion_pago",
                "f.estado",
                "f.estado_pago",

                // TERCERIZACIÓN
                "ft.estado",
                "CONCAT(f.serie, '-', f.id_flete)"
            ];


            // =========================================================
            // SELECT
            // =========================================================

            $selectFields = "

            /* =====================================================
             * FLETE
             * ===================================================== */

            f.id_flete,
            f.id_cotizacion,
            CONCAT(f.serie, '-', f.id_flete) AS numero_flete,
            f.id_cliente,

            f.ubigeo_origen,
            u_ori.distri AS origen,
            f.direccion_origen,

            f.ubigeo_destino,
            u_desti.distri AS destino,
            f.direccion_destino,

            f.tipo_operacion,

            f.fecha_salida,
            f.hora_salida,

            f.condicion_pago,
            f.dias_credito,
            f.fecha_vencimiento,

            f.subtotal,
            f.incluye_igv,
            f.igv,
            f.total,
            f.id_tp_moneda,

            f.observacion,

            f.estado,
            f.estado_pago,

            f.id_sesionpersonal,


            /* =====================================================
             * CLIENTE
             * ===================================================== */

            CONCAT(cli.nombres,' ',cli.apellidos) AS cliente_nombres,
            cli.num_docu AS cliente_num_docu,


            /* =====================================================
             * VEHÍCULO PROPIO
             * ===================================================== */

            f.id_vehiculo,
            v.descripcion AS vehiculo_descripcion,
            v.num_piso AS vehiculo_piso,
            v.placa AS vehiculo_placa,


            /* =====================================================
             * CONDUCTOR PROPIO
             * ===================================================== */

            f.id_conductor,
            CONCAT(c.nombres,' ',c.apellidos) AS conductor_nombres,
            c.num_docu AS conductor_num_docu,


            /* =====================================================
             * TERCERIZACIÓN
             * ===================================================== */

            ft.id_flete_tercerizado,
            ft.id_proveedor,
            ft.id_vehiculo AS id_vehiculo_tercerizado,
            ft.id_conductor AS id_conductor_tercerizado,
            ft.costo AS costo_tercerizado,
            ft.estado AS estado_tercerizado,
            ft.observacion AS observacion_tercerizado,


            /* =====================================================
             * PROVEEDOR
             * ===================================================== */

            CONCAT(prov.nombres,' ',prov.apellidos) AS proveedor_nombres,
            prov.num_docu AS proveedor_num_docu,


            /* =====================================================
             * VEHÍCULO TERCERIZADO
             * ===================================================== */

            vt.descripcion AS vehiculo_tercerizado_descripcion,
            vt.placa AS vehiculo_tercerizado_placa,
            tpm.simbolo AS simbolo_moneda,

            /* =====================================================
             * CONDUCTOR TERCERIZADO
             * ===================================================== */

            CONCAT(
                ct.nombres,
                ' ',
                ct.apellidos
            ) AS conductor_tercerizado_nombres,

            ct.num_docu AS conductor_tercerizado_num_docu,
            fv.id_flete_venta,
            fv.id_venta,
            fv.origen_facturacion,
            fv.id_valorizacion,

            CONCAT(ve.serie, '-', ve.correlativo) AS numero_comprobante,
            ve.envio_sunat
            ";


            // =========================================================
            // FROM / JOINS
            // =========================================================

            $baseQuery = "

            FROM flete f
            /* =====================================================
             * UBIGEOS
             * ===================================================== */

            LEFT JOIN ubigeo u_ori
                ON u_ori.cod_ubigeo =
                   f.ubigeo_origen

            LEFT JOIN ubigeo u_desti
                ON u_desti.cod_ubigeo =
                   f.ubigeo_destino


            /* =====================================================
             * CLIENTE
             * ===================================================== */

            LEFT JOIN usuario cli
                ON cli.id_usuario =
                   f.id_cliente


            /* =====================================================
             * PROPIO
             * ===================================================== */

            LEFT JOIN vehiculo v
                ON v.id_vehiculo =
                   f.id_vehiculo

            LEFT JOIN usuario c
                ON c.id_usuario =
                   f.id_conductor


            /* =====================================================
             * TERCERIZACIÓN
             * ===================================================== */

            LEFT JOIN flete_tercerizado ft
                ON ft.id_flete =
                   f.id_flete


            /* =====================================================
             * PROVEEDOR
             * ===================================================== */

            LEFT JOIN usuario prov
                ON prov.id_usuario =
                   ft.id_proveedor


            /* =====================================================
             * VEHÍCULO TERCERIZADO
             * ===================================================== */

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo =
                   ft.id_vehiculo


            /* =====================================================
             * CONDUCTOR TERCERIZADO
             * ===================================================== */

            LEFT JOIN usuario ct
                ON ct.id_usuario =
                   ft.id_conductor

            LEFT JOIN flete_venta fv
             ON fv.id_flete = f.id_flete

            LEFT JOIN venta ve ON fv.id_venta = ve.id_venta
            LEFT JOIN tp_moneda tpm ON f.id_tp_moneda = tpm.id_tp_moneda
        ";


            // =========================================================
            // ORDER
            // =========================================================

            $orderBy = "
            f.id_flete DESC
        ";


            // =========================================================
            // DATATABLE
            // =========================================================

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );


            // =========================================================
            // FORMATEAR RESULTADO
            // =========================================================

            foreach ($result['data'] as &$registro) {

                $registro['tp_usuario'] =
                    $this->tp_usuario_sesion ?? null;


                // =====================================================
                // HORA
                // =====================================================

                $registro['hora_salida_format'] =
                    !empty($registro['hora_salida'])
                    ? formato_12_horas(
                        $registro['hora_salida']
                    )
                    : '';


                // =====================================================
                // IMPORTES
                // =====================================================

                $registro['subtotal'] =
                    $registro['subtotal']
                    ?? '0.00';

                $registro['igv'] =
                    $registro['igv']
                    ?? '0.00';

                $registro['total'] =
                    $registro['total']
                    ?? '0.00';


                // =====================================================
                // PAGO
                // =====================================================

                $registro['dias_credito'] =
                    $registro['dias_credito']
                    ?? 0;

                $registro['condicion_pago'] =
                    $registro['condicion_pago']
                    ?? 'CONTADO';


                // =====================================================
                // TIPO OPERACIÓN
                // =====================================================

                $registro['tipo_operacion'] =
                    $registro['tipo_operacion']
                    ?? 'PROPIO';


                // =====================================================
                // ESTADOS
                // =====================================================

                $registro['estado'] =
                    $registro['estado']
                    ?? 'PENDIENTE';

                $registro['estado_pago'] =
                    $registro['estado_pago']
                    ?? 'PENDIENTE';


                // =====================================================
                // TERCERIZADO
                // =====================================================

                $registro['id_proveedor'] =
                    $registro['id_proveedor']
                    ?? null;

                $registro['id_vehiculo_tercerizado'] =
                    $registro['id_vehiculo_tercerizado']
                    ?? null;

                $registro['id_conductor_tercerizado'] =
                    $registro['id_conductor_tercerizado']
                    ?? null;

                $registro['costo_tercerizado'] =
                    $registro['costo_tercerizado']
                    ?? '0.00';

                $registro['estado_tercerizado'] =
                    $registro['estado_tercerizado']
                    ?? 'PENDIENTE';

                $registro['observacion_tercerizado'] =
                    $registro['observacion_tercerizado']
                    ?? '';
            }

            unset($registro);

            return $result;
        } catch (PDOException $e) {

            return [

                "draw" =>
                intval(
                    $data['draw'] ?? 1
                ),

                "recordsTotal" =>
                0,

                "recordsFiltered" =>
                0,

                "data" =>
                [],

                "error" =>
                "Error en la consulta: " .
                    $e->getMessage()
            ];
        }
    }
    public function add_register($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();
            $conn->beginTransaction();

            // =========================================================
            // DATOS GENERALES
            // =========================================================

            $tipoOperacion = strtoupper(trim($data["tipo_operacion"] ?? "PROPIO"));

            if (!in_array($tipoOperacion, ["PROPIO", "TERCERIZADO", "MIXTO"], true)) {
                throw new Exception("Tipo de operación inválido.");
            }

            if (empty($data["cliente_id"])) {
                throw new Exception("El cliente es requerido.");
            }

            if (empty($data["ubigeo_origen"])) {
                throw new Exception("El origen es requerido.");
            }

            if (empty($data["ubigeo_destino"])) {
                throw new Exception("El destino es requerido.");
            }

            if (empty($data["fecha_salida"])) {
                throw new Exception("La fecha de salida es requerida.");
            }

            if (empty($data["hora_salida"])) {
                throw new Exception("La hora de salida es requerida.");
            }

            $condicionPago = strtoupper(
                trim($data["condicion_pago"] ?? "CONTADO")
            );

            if (!in_array($condicionPago, ["CONTADO", "CREDITO"], true)) {
                throw new Exception("Condición de pago inválida.");
            }

            $id_tp_moneda = $data['moneda']  ?? null;
            $diasCredito = 0;
            $fechaVencimiento = null;

            if ($condicionPago === "CREDITO") {

                $diasCredito = (int) ($data["dias_credito"] ?? 0);

                if ($diasCredito <= 0) {
                    throw new Exception("Los días de crédito deben ser mayores a cero.");
                }

                $fechaVencimiento = !empty($data["fecha_vencimiento"])
                    ? $data["fecha_vencimiento"]
                    : null;

                if (empty($fechaVencimiento)) {
                    throw new Exception("La fecha de vencimiento es requerida.");
                }
            }

            // =========================================================
            // COTIZACIÓN
            // =========================================================

            $idCotizacion = !empty($data["cotizacion"]) ? (int) $data["cotizacion"] : null;
            if (!empty($idCotizacion)) {

                $query = $conn->prepare("
                    SELECT id_cotizacion
                  FROM cotizacion
                  WHERE id_cotizacion = :id_cotizacion
                   LIMIT 1
                   ");

                $query->execute([
                    ":id_cotizacion" =>
                    $idCotizacion
                ]);


                if (!$query->fetchColumn()) {

                    throw new Exception(
                        "La cotización seleccionada no existe."
                    );
                }
            }
            // =========================================================
            // VEHÍCULO / CONDUCTOR PROPIO
            // =========================================================

            $idVehiculo = null;
            $idConductor = null;

            if ($tipoOperacion === "PROPIO" || $tipoOperacion === "MIXTO") {

                if (empty($data["vehiculo"])) {
                    throw new Exception("El vehículo es requerido.");
                }

                if (empty($data["conductor"])) {
                    throw new Exception("El conductor es requerido.");
                }

                $idVehiculo = (int) $data["vehiculo"];
                $idConductor = (int) $data["conductor"];

                // =====================================================
                // VALIDAR CONDUCTOR
                // =====================================================

                $query = $conn->prepare("
                SELECT id_flete
                FROM flete
                WHERE id_conductor = :id_conductor
                  AND fecha_salida = :fecha_salida
                  AND estado NOT IN ('FINALIZADO', 'CANCELADO')
                LIMIT 1
                ");

                $query->execute([
                    ":id_conductor" => $idConductor,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El conductor seleccionado ya tiene un flete programado para esta fecha."
                    );
                }

                // =====================================================
                // VALIDAR VEHÍCULO
                // =====================================================

                $query = $conn->prepare("
                SELECT id_flete
                FROM flete
                WHERE id_vehiculo = :id_vehiculo
                  AND fecha_salida = :fecha_salida
                  AND estado NOT IN ('FINALIZADO', 'CANCELADO')
                LIMIT 1
            ");

                $query->execute([
                    ":id_vehiculo" => $idVehiculo,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo seleccionado ya tiene un flete programado para esta fecha."
                    );
                }
            }

            // =========================================================
            // TERCERIZADO
            // =========================================================

            $idProveedor = null;
            $idVehiculoTercerizado = null;
            $idConductorTercerizado = null;
            $costoTercerizado = 0;
            $estadoTercerizado = "PENDIENTE";
            $observacionTercerizado = null;

            if ($tipoOperacion === "TERCERIZADO") {

                $idProveedor = (int) ($data["id_proveedor"] ?? 0);
                $idVehiculoTercerizado = (int) ($data["id_vehiculo_tercerizado"] ?? 0);
                $idConductorTercerizado = (int) ($data["id_conductor_tercerizado"] ?? 0);

                $costoTercerizado = (float) ($data["costo_tercerizado"] ?? 0);

                $estadoTercerizado = strtoupper(
                    trim($data["estado_tercerizado"] ?? "PENDIENTE")
                );

                $observacionTercerizado =
                    trim($data["observacion_tercerizado"] ?? "");

                if ($idProveedor <= 0) {
                    throw new Exception("El proveedor es requerido.");
                }

                if ($idVehiculoTercerizado <= 0) {
                    throw new Exception("El vehículo tercerizado es requerido.");
                }

                if ($idConductorTercerizado <= 0) {
                    throw new Exception("El conductor tercerizado es requerido.");
                }

                if ($costoTercerizado <= 0) {
                    throw new Exception(
                        "El costo de tercerización debe ser mayor a cero."
                    );
                }

                if (
                    !in_array(
                        $estadoTercerizado,
                        ["PENDIENTE", "ASIGNADO", "FINALIZADO", "CANCELADO"],
                        true
                    )
                ) {
                    throw new Exception("Estado de tercerización inválido.");
                }

                // =====================================================
                // VALIDAR QUE SEA PROVEEDOR
                // id_tp_usuario = 6
                // =====================================================

                $query = $conn->prepare("
                SELECT id_usuario
                FROM usuario
                WHERE id_usuario = :id_usuario
                  AND id_tp_usuario = 6
                  AND estado = 1
                LIMIT 1
            ");

                $query->execute([
                    ":id_usuario" => $idProveedor
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El proveedor seleccionado no es válido."
                    );
                }

                // =====================================================
                // VALIDAR QUE SEA CONDUCTOR
                // id_tp_usuario = 4
                // =====================================================

                $query = $conn->prepare("
                SELECT u.id_usuario
                FROM usuario u
                INNER JOIN dt_conductor dc
                    ON dc.id_usuario = u.id_usuario
                WHERE u.id_usuario = :id_usuario
                  AND u.id_tp_usuario = 4
                  AND u.estado = 1
                LIMIT 1
            ");

                $query->execute([
                    ":id_usuario" => $idConductorTercerizado
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El conductor tercerizado seleccionado no es válido."
                    );
                }

                // =====================================================
                // VALIDAR VEHÍCULO EXISTENTE
                // =====================================================

                $query = $conn->prepare("
                SELECT id_vehiculo
                FROM vehiculo
                WHERE id_vehiculo = :id_vehiculo
                  AND estado = 1
                LIMIT 1
            ");

                $query->execute([
                    ":id_vehiculo" => $idVehiculoTercerizado
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo tercerizado seleccionado no es válido."
                    );
                }

                // =====================================================
                // VALIDAR CONDUCTOR TERCERIZADO OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT ft.id_flete_tercerizado
                FROM flete_tercerizado ft
                INNER JOIN flete f
                    ON f.id_flete = ft.id_flete
                WHERE ft.id_conductor = :id_conductor
                  AND f.fecha_salida = :fecha_salida
                  AND f.estado NOT IN ('FINALIZADO', 'CANCELADO')
                  AND ft.estado <> 'CANCELADO'
                LIMIT 1
            ");

                $query->execute([
                    ":id_conductor" => $idConductorTercerizado,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El conductor tercerizado ya tiene un flete programado para esta fecha."
                    );
                }

                // =====================================================
                // VALIDAR VEHÍCULO TERCERIZADO OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT ft.id_flete_tercerizado
                FROM flete_tercerizado ft
                INNER JOIN flete f
                    ON f.id_flete = ft.id_flete
                WHERE ft.id_vehiculo = :id_vehiculo
                  AND f.fecha_salida = :fecha_salida
                  AND f.estado NOT IN ('FINALIZADO', 'CANCELADO')
                  AND ft.estado <> 'CANCELADO'
                LIMIT 1
            ");

                $query->execute([
                    ":id_vehiculo" => $idVehiculoTercerizado,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo tercerizado ya tiene un flete programado para esta fecha."
                    );
                }
            }

            // =========================================================
            // IGV
            // =========================================================

            $igvConfig = (float) $this->get_igv();

            $precio = (float) ($data["precio"] ?? 0);

            if ($precio <= 0) {
                throw new Exception("El importe debe ser mayor a cero.");
            }

            $porcentajeIgv = $igvConfig / 100;

            $incluyeIgv = (int) ($data["incluye_igv"] ?? 0);

            if ($incluyeIgv === 1) {

                $total = round($precio, 2);

                $subtotal = round(
                    $precio / (1 + $porcentajeIgv),
                    2
                );

                $igv = round(
                    $total - $subtotal,
                    2
                );
            } else {

                $subtotal = round(
                    $precio,
                    2
                );

                $igv = round(
                    $subtotal * $porcentajeIgv,
                    2
                );

                $total = round(
                    $subtotal + $igv,
                    2
                );
            }

            // =========================================================
            // INSERT FLETE
            // =========================================================
            $serie = date('Y');

            $query = $conn->prepare("
            INSERT INTO flete (
                id_cotizacion,
                serie,
                id_cliente,

                ubigeo_origen,
                direccion_origen,

                ubigeo_destino,
                direccion_destino,

                tipo_operacion,

                id_vehiculo,
                id_conductor,

                fecha_salida,
                hora_salida,

                subtotal,
                incluye_igv,
                igv,
                total,
                id_tp_moneda,

                condicion_pago,
                dias_credito,
                fecha_vencimiento,

                estado,
                estado_pago,

                observacion,

                id_sesionpersonal
            )
            VALUES (
                :id_cotizacion,
                :serie,
                :id_cliente,

                :ubigeo_origen,
                :direccion_origen,

                :ubigeo_destino,
                :direccion_destino,

                :tipo_operacion,

                :id_vehiculo,
                :id_conductor,

                :fecha_salida,
                :hora_salida,

                :subtotal,
                :incluye_igv,
                :igv,
                :total,
                :id_tp_moneda,

                :condicion_pago,
                :dias_credito,
                :fecha_vencimiento,

                :estado,
                :estado_pago,

                :observacion,

                :id_sesionpersonal
            )
        ");

            $query->execute([

                ":id_cotizacion" => $idCotizacion,
                ':serie' => date('Y'),
                ":id_cliente" => $data["cliente_id"],

                ":ubigeo_origen" => $data["ubigeo_origen"],

                ":direccion_origen" =>
                trim($data["direccion_origen"] ?? ""),

                ":ubigeo_destino" =>
                $data["ubigeo_destino"],

                ":direccion_destino" =>
                trim($data["direccion_destino"] ?? ""),

                ":tipo_operacion" =>
                $tipoOperacion,

                ":id_vehiculo" =>
                $idVehiculo,

                ":id_conductor" =>
                $idConductor,

                ":fecha_salida" =>
                $data["fecha_salida"],

                ":hora_salida" =>
                $data["hora_salida"],

                ":subtotal" =>
                $subtotal,

                ":incluye_igv" =>
                $incluyeIgv,

                ":igv" =>
                $igv,

                ":total" => $total,

                ":id_tp_moneda" => $id_tp_moneda,

                ":condicion_pago" =>
                $condicionPago,

                ":dias_credito" =>
                $diasCredito,

                ":fecha_vencimiento" =>
                $fechaVencimiento,

                ":estado" =>
                "PENDIENTE",

                ":estado_pago" =>
                "PENDIENTE",

                ":observacion" =>
                trim($data["observacion"] ?? ""),

                ":id_sesionpersonal" =>
                $this->id_usuario_sesion
            ]);

            $idFlete = (int) $conn->lastInsertId();

            // =========================================================
            // REGISTRAR DETALLE DEL FLETE
            // =========================================================

            $detalleFlete =
                $this->registrar_detalle_flete(
                    $idFlete,
                    $idCotizacion,
                    $subtotal,
                    $igv,
                    $total,
                    $incluyeIgv,
                    $conn
                );


            if (!$detalleFlete["success"]) {

                throw new Exception(
                    $detalleFlete["message"]
                        ?? "No se pudo registrar el detalle del flete."
                );
            }

            // =========================================================
            // INSERT TERCERIZADO
            // =========================================================

            if ($tipoOperacion === "TERCERIZADO") {

                $query = $conn->prepare("
                INSERT INTO flete_tercerizado (
                    id_flete,
                    id_proveedor,
                    id_vehiculo,
                    id_conductor,
                    costo,
                    estado,
                    observacion,
                    id_sesionpersonal
                )
                VALUES (
                    :id_flete,
                    :id_proveedor,
                    :id_vehiculo,
                    :id_conductor,
                    :costo,
                    :estado,
                    :observacion,
                    :id_sesionpersonal
                )
            ");

                $query->execute([

                    ":id_flete" =>
                    $idFlete,

                    ":id_proveedor" =>
                    $idProveedor,

                    ":id_vehiculo" =>
                    $idVehiculoTercerizado,

                    ":id_conductor" =>
                    $idConductorTercerizado,

                    ":costo" =>
                    $costoTercerizado,

                    ":estado" =>
                    $estadoTercerizado,

                    ":observacion" =>
                    $observacionTercerizado,

                    ":id_sesionpersonal" =>
                    $this->id_usuario_sesion
                ]);
            }

            $conn->commit();

            return [
                "success" => true,
                "message" => "Registro creado con éxito",
                "id_flete" => $idFlete
            ];
        } catch (Exception $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }

            if ($e->getCode() == "23000") {

                return [
                    "success" => false,
                    "message" => "Ya existe un registro con estos datos."
                ];
            }

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function edit_register($data)
    {
        $conn = null;

        try {

            // =========================================================
            // ID
            // =========================================================

            $idFlete = (int) ($data["id_flete"] ?? 0);

            if ($idFlete <= 0) {
                throw new Exception(
                    "El id del flete es requerido."
                );
            }

            $conn = $this->db->connect();
            $conn->beginTransaction();

            // =========================================================
            // VERIFICAR FLETE
            // =========================================================

            $query = $conn->prepare("
            SELECT
                f.id_flete,
                f.tipo_operacion,
                f.estado,

                EXISTS (
                    SELECT 1
                    FROM flete_venta fv
                    WHERE fv.id_flete = f.id_flete
                ) AS facturado

            FROM flete f

            WHERE f.id_flete = :id_flete

            LIMIT 1

            FOR UPDATE
        ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $fleteActual = $query->fetch(PDO::FETCH_ASSOC);

            if (!$fleteActual) {
                throw new Exception(
                    "El flete seleccionado no existe."
                );
            }

            if ((int) $fleteActual["facturado"] === 1) {
                throw new Exception(
                    "No se puede modificar un flete que ya fue facturado."
                );
            }

            if (
                !in_array(
                    $fleteActual["estado"],
                    ["PENDIENTE", "PROGRAMADO"],
                    true
                )
            ) {
                throw new Exception(
                    "Solo se pueden modificar fletes pendientes o programados."
                );
            }

            // =========================================================
            // DATOS GENERALES
            // =========================================================

            $tipoOperacion = strtoupper(
                trim(
                    $data["tipo_operacion"] ?? "PROPIO"
                )
            );

            if (
                !in_array(
                    $tipoOperacion,
                    ["PROPIO", "TERCERIZADO", "MIXTO"],
                    true
                )
            ) {
                throw new Exception(
                    "Tipo de operación inválido."
                );
            }

            if (empty($data["cliente_id"])) {
                throw new Exception(
                    "El cliente es requerido."
                );
            }

            if (empty($data["ubigeo_origen"])) {
                throw new Exception(
                    "El origen es requerido."
                );
            }

            if (empty($data["ubigeo_destino"])) {
                throw new Exception(
                    "El destino es requerido."
                );
            }

            if (empty($data["fecha_salida"])) {
                throw new Exception(
                    "La fecha de salida es requerida."
                );
            }

            if (empty($data["hora_salida"])) {
                throw new Exception(
                    "La hora de salida es requerida."
                );
            }

            $idTpMoneda = !empty($data["moneda"])
                ? (int) $data["moneda"]
                : null;

            if (empty($idTpMoneda)) {
                throw new Exception(
                    "La moneda es requerida."
                );
            }

            // =========================================================
            // CONDICIÓN DE PAGO
            // =========================================================

            $condicionPago = strtoupper(
                trim(
                    $data["condicion_pago"] ?? "CONTADO"
                )
            );

            if (
                !in_array(
                    $condicionPago,
                    ["CONTADO", "CREDITO"],
                    true
                )
            ) {
                throw new Exception(
                    "Condición de pago inválida."
                );
            }

            $diasCredito = 0;
            $fechaVencimiento = null;

            if ($condicionPago === "CREDITO") {

                $diasCredito = (int) (
                    $data["dias_credito"] ?? 0
                );

                if ($diasCredito <= 0) {
                    throw new Exception(
                        "Los días de crédito deben ser mayores a cero."
                    );
                }

                $fechaVencimiento =
                    !empty($data["fecha_vencimiento"])
                    ? $data["fecha_vencimiento"]
                    : null;

                if (!$fechaVencimiento) {
                    throw new Exception(
                        "La fecha de vencimiento es requerida."
                    );
                }
            }

            // =========================================================
            // COTIZACIÓN
            // =========================================================

            $idCotizacion =
                !empty($data["cotizacion"])
                ? (int) $data["cotizacion"]
                : null;

            if (!empty($idCotizacion)) {

                $query = $conn->prepare("
                SELECT id_cotizacion
                FROM cotizacion
                WHERE id_cotizacion = :id_cotizacion
                LIMIT 1
            ");

                $query->execute([
                    ":id_cotizacion" => $idCotizacion
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "La cotización seleccionada no existe."
                    );
                }
            }

            // =========================================================
            // VEHÍCULO / CONDUCTOR PROPIO
            // =========================================================

            $idVehiculo = null;
            $idConductor = null;

            if (
                $tipoOperacion === "PROPIO" ||
                $tipoOperacion === "MIXTO"
            ) {

                $idVehiculo =
                    (int) ($data["vehiculo"] ?? 0);

                $idConductor =
                    (int) ($data["conductor"] ?? 0);

                if ($idVehiculo <= 0) {
                    throw new Exception(
                        "El vehículo es requerido."
                    );
                }

                if ($idConductor <= 0) {
                    throw new Exception(
                        "El conductor es requerido."
                    );
                }

                // =====================================================
                // VALIDAR CONDUCTOR OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT id_flete
                FROM flete

                WHERE id_flete <> :id_flete
                  AND id_conductor = :id_conductor
                  AND fecha_salida = :fecha_salida
                  AND estado NOT IN ('FINALIZADO', 'CANCELADO')

                LIMIT 1
            ");

                $query->execute([
                    ":id_flete" => $idFlete,
                    ":id_conductor" => $idConductor,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El conductor seleccionado ya tiene un flete programado para esta fecha."
                    );
                }

                // =====================================================
                // VALIDAR VEHÍCULO OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT id_flete
                FROM flete

                WHERE id_flete <> :id_flete
                  AND id_vehiculo = :id_vehiculo
                  AND fecha_salida = :fecha_salida
                  AND estado NOT IN ('FINALIZADO', 'CANCELADO')

                LIMIT 1
            ");

                $query->execute([
                    ":id_flete" => $idFlete,
                    ":id_vehiculo" => $idVehiculo,
                    ":fecha_salida" => $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo seleccionado ya tiene un flete programado para esta fecha."
                    );
                }
            }

            // =========================================================
            // DATOS TERCERIZADOS
            // =========================================================

            $idProveedor = null;
            $idVehiculoTercerizado = null;
            $idConductorTercerizado = null;

            $costoTercerizado = 0;

            $estadoTercerizado = "PENDIENTE";
            $observacionTercerizado = null;

            if ($tipoOperacion === "TERCERIZADO") {

                $idProveedor =
                    (int) ($data["id_proveedor"] ?? 0);

                $idVehiculoTercerizado =
                    (int) ($data["id_vehiculo_tercerizado"] ?? 0);

                $idConductorTercerizado =
                    (int) ($data["id_conductor_tercerizado"] ?? 0);

                $costoTercerizado =
                    (float) ($data["costo_tercerizado"] ?? 0);

                $estadoTercerizado = strtoupper(
                    trim(
                        $data["estado_tercerizado"] ??
                            "PENDIENTE"
                    )
                );

                $observacionTercerizado =
                    trim(
                        $data["observacion_tercerizado"] ?? ""
                    );

                if ($idProveedor <= 0) {
                    throw new Exception(
                        "El proveedor es requerido."
                    );
                }

                if ($idVehiculoTercerizado <= 0) {
                    throw new Exception(
                        "El vehículo tercerizado es requerido."
                    );
                }

                if ($idConductorTercerizado <= 0) {
                    throw new Exception(
                        "El conductor tercerizado es requerido."
                    );
                }

                if ($costoTercerizado <= 0) {
                    throw new Exception(
                        "El costo de tercerización debe ser mayor a cero."
                    );
                }

                if (
                    !in_array(
                        $estadoTercerizado,
                        [
                            "PENDIENTE",
                            "ASIGNADO",
                            "FINALIZADO",
                            "CANCELADO"
                        ],
                        true
                    )
                ) {
                    throw new Exception(
                        "Estado de tercerización inválido."
                    );
                }

                // =====================================================
                // VALIDAR PROVEEDOR
                // =====================================================

                $query = $conn->prepare("
                SELECT id_usuario
                FROM usuario

                WHERE id_usuario = :id_usuario
                  AND id_tp_usuario = 6
                  AND estado = 1

                LIMIT 1
            ");

                $query->execute([
                    ":id_usuario" => $idProveedor
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El proveedor seleccionado no es válido."
                    );
                }

                // =====================================================
                // VALIDAR CONDUCTOR TERCERIZADO
                // =====================================================

                $query = $conn->prepare("
                SELECT u.id_usuario

                FROM usuario u

                INNER JOIN dt_conductor dc
                    ON dc.id_usuario = u.id_usuario

                WHERE u.id_usuario = :id_usuario
                  AND u.id_tp_usuario = 4
                  AND u.estado = 1

                LIMIT 1
            ");

                $query->execute([
                    ":id_usuario" =>
                    $idConductorTercerizado
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El conductor tercerizado seleccionado no es válido."
                    );
                }

                // =====================================================
                // VALIDAR VEHÍCULO TERCERIZADO
                // =====================================================

                $query = $conn->prepare("
                SELECT id_vehiculo
                FROM vehiculo

                WHERE id_vehiculo = :id_vehiculo
                  AND estado = 1

                LIMIT 1
            ");

                $query->execute([
                    ":id_vehiculo" =>
                    $idVehiculoTercerizado
                ]);

                if (!$query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo tercerizado seleccionado no es válido."
                    );
                }

                // =====================================================
                // CONDUCTOR TERCERIZADO OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT ft.id_flete_tercerizado

                FROM flete_tercerizado ft

                INNER JOIN flete f
                    ON f.id_flete = ft.id_flete

                WHERE ft.id_conductor = :id_conductor

                  AND f.id_flete <> :id_flete

                  AND f.fecha_salida = :fecha_salida

                  AND f.estado NOT IN (
                      'FINALIZADO',
                      'CANCELADO'
                  )

                  AND ft.estado <> 'CANCELADO'

                LIMIT 1
            ");

                $query->execute([
                    ":id_conductor" =>
                    $idConductorTercerizado,

                    ":id_flete" =>
                    $idFlete,

                    ":fecha_salida" =>
                    $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El conductor tercerizado ya tiene un flete programado para esta fecha."
                    );
                }

                // =====================================================
                // VEHÍCULO TERCERIZADO OCUPADO
                // =====================================================

                $query = $conn->prepare("
                SELECT ft.id_flete_tercerizado

                FROM flete_tercerizado ft

                INNER JOIN flete f
                    ON f.id_flete = ft.id_flete

                WHERE ft.id_vehiculo = :id_vehiculo

                  AND f.id_flete <> :id_flete

                  AND f.fecha_salida = :fecha_salida

                  AND f.estado NOT IN (
                      'FINALIZADO',
                      'CANCELADO'
                  )

                  AND ft.estado <> 'CANCELADO'

                LIMIT 1
            ");

                $query->execute([
                    ":id_vehiculo" =>
                    $idVehiculoTercerizado,

                    ":id_flete" =>
                    $idFlete,

                    ":fecha_salida" =>
                    $data["fecha_salida"]
                ]);

                if ($query->fetchColumn()) {
                    throw new Exception(
                        "El vehículo tercerizado ya tiene un flete programado para esta fecha."
                    );
                }
            }

            // =========================================================
            // CALCULAR IMPORTES / IGV
            // =========================================================

            $igvConfig =
                (float) $this->get_igv();

            $precio =
                (float) ($data["precio"] ?? 0);

            if ($precio <= 0) {
                throw new Exception(
                    "El importe debe ser mayor a cero."
                );
            }

            $porcentajeIgv =
                $igvConfig / 100;

            $incluyeIgv =
                (int) ($data["incluye_igv"] ?? 0);

            if ($incluyeIgv === 1) {

                // El precio ingresado YA contiene IGV.

                $total =
                    round($precio, 2);

                $subtotal =
                    round(
                        $precio / (1 + $porcentajeIgv),
                        2
                    );

                $igv =
                    round(
                        $total - $subtotal,
                        2
                    );
            } else {

                // El precio ingresado NO contiene IGV.

                $subtotal =
                    round($precio, 2);

                $igv =
                    round(
                        $subtotal * $porcentajeIgv,
                        2
                    );

                $total =
                    round(
                        $subtotal + $igv,
                        2
                    );
            }

            // =========================================================
            // UPDATE FLETE
            // =========================================================

            $query = $conn->prepare("
            UPDATE flete SET

                id_cotizacion = :id_cotizacion,
                id_cliente = :id_cliente,

                ubigeo_origen = :ubigeo_origen,
                direccion_origen = :direccion_origen,

                ubigeo_destino = :ubigeo_destino,
                direccion_destino = :direccion_destino,

                tipo_operacion = :tipo_operacion,

                id_vehiculo = :id_vehiculo,
                id_conductor = :id_conductor,

                fecha_salida = :fecha_salida,
                hora_salida = :hora_salida,

                subtotal = :subtotal,
                incluye_igv = :incluye_igv,
                igv = :igv,
                total = :total,

                id_tp_moneda = :id_tp_moneda,

                condicion_pago = :condicion_pago,
                dias_credito = :dias_credito,
                fecha_vencimiento = :fecha_vencimiento,

                observacion = :observacion

            WHERE id_flete = :id_flete
        ");

            $query->execute([

                ":id_flete" =>
                $idFlete,

                ":id_cotizacion" =>
                $idCotizacion,

                ":id_cliente" =>
                $data["cliente_id"],

                ":ubigeo_origen" =>
                $data["ubigeo_origen"],

                ":direccion_origen" =>
                trim(
                    $data["direccion_origen"] ?? ""
                ),

                ":ubigeo_destino" =>
                $data["ubigeo_destino"],

                ":direccion_destino" =>
                trim(
                    $data["direccion_destino"] ?? ""
                ),

                ":tipo_operacion" =>
                $tipoOperacion,

                ":id_vehiculo" =>
                $idVehiculo,

                ":id_conductor" =>
                $idConductor,

                ":fecha_salida" =>
                $data["fecha_salida"],

                ":hora_salida" =>
                $data["hora_salida"],

                ":subtotal" =>
                $subtotal,

                ":incluye_igv" =>
                $incluyeIgv,

                ":igv" =>
                $igv,

                ":total" =>
                $total,

                ":id_tp_moneda" =>
                $idTpMoneda,

                ":condicion_pago" =>
                $condicionPago,

                ":dias_credito" =>
                $diasCredito,

                ":fecha_vencimiento" =>
                $fechaVencimiento,

                ":observacion" =>
                trim(
                    $data["observacion"] ?? ""
                )
            ]);


            $query = $conn->prepare("
            DELETE FROM flete_detalle
            WHERE id_flete = :id_flete
        ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $detalleFlete =
                $this->registrar_detalle_flete(
                    $idFlete,
                    $idCotizacion,
                    $subtotal,
                    $igv,
                    $total,
                    $incluyeIgv,
                    $conn
                );

            if (!$detalleFlete["success"]) {
                throw new Exception(
                    $detalleFlete["message"]
                        ?? "No se pudo actualizar el detalle del flete."
                );
            }

            // =========================================================
            // TERCERIZADO
            // =========================================================

            if ($tipoOperacion === "TERCERIZADO") {

                // =====================================================
                // VERIFICAR SI YA EXISTE
                // =====================================================

                $query = $conn->prepare("
                SELECT id_flete_tercerizado

                FROM flete_tercerizado

                WHERE id_flete = :id_flete

                LIMIT 1
            ");

                $query->execute([
                    ":id_flete" => $idFlete
                ]);

                $idFleteTercerizado =
                    $query->fetchColumn();

                // =====================================================
                // ACTUALIZAR
                // =====================================================

                if ($idFleteTercerizado) {

                    $query = $conn->prepare("
                    UPDATE flete_tercerizado SET

                        id_proveedor = :id_proveedor,
                        id_vehiculo = :id_vehiculo,
                        id_conductor = :id_conductor,

                        costo = :costo,

                        estado = :estado,

                        observacion = :observacion,

                        id_sesionpersonal = :id_sesionpersonal

                    WHERE id_flete_tercerizado =
                          :id_flete_tercerizado
                ");

                    $query->execute([

                        ":id_proveedor" =>
                        $idProveedor,

                        ":id_vehiculo" =>
                        $idVehiculoTercerizado,

                        ":id_conductor" =>
                        $idConductorTercerizado,

                        ":costo" =>
                        $costoTercerizado,

                        ":estado" =>
                        $estadoTercerizado,

                        ":observacion" =>
                        $observacionTercerizado,

                        ":id_sesionpersonal" =>
                        $this->id_usuario_sesion,

                        ":id_flete_tercerizado" =>
                        $idFleteTercerizado
                    ]);
                } else {

                    // =================================================
                    // INSERTAR
                    // =================================================

                    $query = $conn->prepare("
                    INSERT INTO flete_tercerizado (
                        id_flete,
                        id_proveedor,
                        id_vehiculo,
                        id_conductor,
                        costo,
                        estado,
                        observacion,
                        id_sesionpersonal
                    )
                    VALUES (
                        :id_flete,
                        :id_proveedor,
                        :id_vehiculo,
                        :id_conductor,
                        :costo,
                        :estado,
                        :observacion,
                        :id_sesionpersonal
                    )
                ");

                    $query->execute([

                        ":id_flete" =>
                        $idFlete,

                        ":id_proveedor" =>
                        $idProveedor,

                        ":id_vehiculo" =>
                        $idVehiculoTercerizado,

                        ":id_conductor" =>
                        $idConductorTercerizado,

                        ":costo" =>
                        $costoTercerizado,

                        ":estado" =>
                        $estadoTercerizado,

                        ":observacion" =>
                        $observacionTercerizado,

                        ":id_sesionpersonal" =>
                        $this->id_usuario_sesion
                    ]);
                }
            } else {

                // =====================================================
                // SI DEJÓ DE SER TERCERIZADO
                // =====================================================

                $query = $conn->prepare("
                DELETE FROM flete_tercerizado
                WHERE id_flete = :id_flete
            ");

                $query->execute([
                    ":id_flete" => $idFlete
                ]);
            }

            // =========================================================
            // COMMIT
            // =========================================================

            $conn->commit();

            return [
                "success" => true,
                "message" => "Registro modificado con éxito"
            ];
        } catch (Exception $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }

            if ($e->getCode() == "23000") {
                return [
                    "success" => false,
                    "message" =>
                    "Ya existe un registro con estos datos."
                ];
            }

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM flete WHERE id_flete=:id_flete");
            $query->bindParam(":id_flete", $data["id_flete"]);
            $query->execute();
            return array('success' => true, "message" => "Registro eliminado con éxito");
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

    public function get_salidas($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT 
                f.*,
                u_o.distri AS origen,
                u_d.distri AS destino,
                v.placa,
                CONCAT(c.nombres,' ',c.apellidos) AS conductor
                FROM flete f
                LEFT JOIN vehiculo v ON v.id_vehiculo = f.id_vehiculo
                LEFT JOIN ubigeo u_o ON u_o.cod_ubigeo = f.ubigeo_origen
                LEFT JOIN ubigeo u_d ON u_d.cod_ubigeo = f.ubigeo_destino
                LEFT JOIN usuario c ON c.id_usuario = f.id_conductor
                WHERE  f.ubigeo_destino = :ubigeo_destino
                AND f.fecha_salida >= CURDATE()
                ORDER BY f.fecha_salida ASC, f.hora_salida ASC
            ");

            $query->bindParam(':ubigeo_destino', $data["ubigeo_destino"]);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function get_cotizacion_datos($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT c.*, 
                u.num_docu AS cliente_num_docu,
                CONCAT(u.nombres, ' ', u.apellidos) AS cliente_nombres
                FROM cotizacion c
                INNER JOIN usuario u ON u.id_usuario = c.id_cliente
                WHERE id_cotizacion = :id_cotizacion
            ");

            $query->bindParam(':id_cotizacion', $data['id_cotizacion']);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);

            return ['success' => true, 'message' => $data];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }

    public function get_flete_data($id_flete)
    {
        try {

            $conn = $this->db->connect();

            // =========================================================
            // VALIDAR ID
            // =========================================================

            $id_flete = (int) $id_flete;

            if ($id_flete <= 0) {
                throw new Exception("El id del flete es requerido.");
            }


            // =========================================================
            // DATOS DEL FLETE
            // =========================================================

            $query = $conn->prepare("
            SELECT

                /* =================================================
                 * FLETE
                 * ================================================= */

                f.id_flete,
                f.id_cotizacion,
                f.id_cliente,

                f.tipo_operacion,

                f.fecha_salida,
                f.hora_salida,

                f.subtotal,
                f.incluye_igv,
                f.igv,
                f.total,
                tpm.simbolo AS simbolo_moneda,
                tpm.codigo AS codigo_moneda,

                f.condicion_pago,
                f.dias_credito,
                f.fecha_vencimiento,

                f.estado,
                f.estado_pago,

                f.observacion,


                /* =================================================
                 * CLIENTE
                 * ================================================= */

                cli.id_usuario AS cliente_id,

                cli.num_docu AS cliente_num_docu,

                CONCAT(
                    COALESCE(cli.nombres, ''),
                    ' ',
                    COALESCE(cli.apellidos, '')
                ) AS cliente_nombres,


                /* =================================================
                 * ORIGEN
                 * ================================================= */

                f.ubigeo_origen,

                u_ori.distri AS origen,

                f.direccion_origen,


                /* =================================================
                 * DESTINO
                 * ================================================= */

                f.ubigeo_destino,

                u_desti.distri AS destino,

                f.direccion_destino,


                /* =================================================
                 * VEHÍCULO PROPIO
                 * ================================================= */

                f.id_vehiculo,

                v.placa AS vehiculo_placa,

                v.descripcion AS vehiculo_descripcion,


                /* =================================================
                 * CONDUCTOR PROPIO
                 * ================================================= */

                f.id_conductor,

                CONCAT(
                    COALESCE(c.nombres, ''),
                    ' ',
                    COALESCE(c.apellidos, '')
                ) AS conductor_nombres,

                c.num_docu AS conductor_num_docu,

                dc.licencia AS conductor_licencia,

                dc.categoria AS conductor_categoria,


                /* =================================================
                 * TERCERIZACIÓN
                 * ================================================= */

                ft.id_flete_tercerizado,

                ft.id_proveedor,

                ft.id_vehiculo
                    AS id_vehiculo_tercerizado,

                ft.id_conductor
                    AS id_conductor_tercerizado,

                ft.costo
                    AS costo_tercerizado,

                ft.estado
                    AS estado_tercerizado,

                ft.observacion
                    AS observacion_tercerizado,


                /* =================================================
                 * PROVEEDOR
                 * ================================================= */

                prov.num_docu
                    AS proveedor_num_docu,

                CONCAT(
                    COALESCE(prov.nombres, ''),
                    ' ',
                    COALESCE(prov.apellidos, '')
                ) AS proveedor_nombres,


                /* =================================================
                 * VEHÍCULO TERCERIZADO
                 * ================================================= */

                vt.placa
                    AS vehiculo_tercerizado_placa,

                vt.descripcion
                    AS vehiculo_tercerizado_descripcion,


                /* =================================================
                 * CONDUCTOR TERCERIZADO
                 * ================================================= */

                ct.num_docu
                    AS conductor_tercerizado_num_docu,

                CONCAT(
                    COALESCE(ct.nombres, ''),
                    ' ',
                    COALESCE(ct.apellidos, '')
                ) AS conductor_tercerizado_nombres,

                dct.licencia
                    AS conductor_tercerizado_licencia,

                dct.categoria
                    AS conductor_tercerizado_categoria


            FROM flete f


            /* =====================================================
             * CLIENTE
             * ===================================================== */

            LEFT JOIN usuario cli
                ON cli.id_usuario = f.id_cliente


            /* =====================================================
             * UBIGEOS
             * ===================================================== */

            LEFT JOIN ubigeo u_ori
                ON u_ori.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo u_desti
                ON u_desti.cod_ubigeo = f.ubigeo_destino


            /* =====================================================
             * VEHÍCULO PROPIO
             * ===================================================== */

            LEFT JOIN vehiculo v
                ON v.id_vehiculo = f.id_vehiculo


            /* =====================================================
             * CONDUCTOR PROPIO
             * ===================================================== */

            LEFT JOIN usuario c
                ON c.id_usuario = f.id_conductor

            LEFT JOIN dt_conductor dc
                ON dc.id_usuario = c.id_usuario


            /* =====================================================
             * TERCERIZACIÓN
             * ===================================================== */

            LEFT JOIN flete_tercerizado ft
                ON ft.id_flete = f.id_flete


            /* =====================================================
             * PROVEEDOR
             * ===================================================== */

            LEFT JOIN usuario prov
                ON prov.id_usuario = ft.id_proveedor


            /* =====================================================
             * VEHÍCULO TERCERIZADO
             * ===================================================== */

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo = ft.id_vehiculo


            /* =====================================================
             * CONDUCTOR TERCERIZADO
             * ===================================================== */

            LEFT JOIN usuario ct
                ON ct.id_usuario = ft.id_conductor

            LEFT JOIN dt_conductor dct
                ON dct.id_usuario = ct.id_usuario

            LEFT JOIN tp_moneda tpm ON f.id_tp_moneda = tpm.id_tp_moneda

            WHERE f.id_flete = :id_flete

            LIMIT 1
        ");

            $query->execute([
                ':id_flete' => $id_flete
            ]);

            $flete = $query->fetch(PDO::FETCH_ASSOC);


            // =========================================================
            // VALIDAR RESULTADO
            // =========================================================

            if (!$flete) {

                return [
                    'success' => false,
                    'message' => 'No se encontró el flete solicitado.'
                ];
            }
            $flete['numero_flete'] = sprintf(
                '%s-%08d',
                $flete['serie'] ?? date('Y'),
                (int) $flete['id_flete']
            );

            $flete['numero_orden_servicio'] = !empty($flete['serie'])
                ? sprintf(
                    'OS-%s-%08d',
                    $flete['serie'],
                    (int) $flete['id_flete']
                )
                : sprintf(
                    'OS-%08d',
                    (int) $flete['id_flete']
                );

            // =========================================================
            // NORMALIZAR DATOS
            // =========================================================

            $flete['tipo_operacion'] =
                $flete['tipo_operacion'] ?? 'PROPIO';

            $flete['condicion_pago'] =
                $flete['condicion_pago'] ?? 'CONTADO';

            $flete['dias_credito'] =
                (int) ($flete['dias_credito'] ?? 0);

            $flete['subtotal'] =
                number_format(
                    (float) ($flete['subtotal'] ?? 0),
                    2,
                    '.',
                    ''
                );

            $flete['igv'] =
                number_format(
                    (float) ($flete['igv'] ?? 0),
                    2,
                    '.',
                    ''
                );

            $flete['total'] =
                number_format(
                    (float) ($flete['total'] ?? 0),
                    2,
                    '.',
                    ''
                );

            $flete['costo_tercerizado'] =
                number_format(
                    (float) ($flete['costo_tercerizado'] ?? 0),
                    2,
                    '.',
                    ''
                );


            // =========================================================
            // FORMATO FECHA
            // =========================================================

            $flete['fecha_salida_format'] = '';

            if (!empty($flete['fecha_salida'])) {

                $fecha = new DateTime(
                    $flete['fecha_salida']
                );

                $flete['fecha_salida_format'] =
                    $fecha->format('d/m/Y');
            }


            // =========================================================
            // FECHA VENCIMIENTO
            // =========================================================

            $flete['fecha_vencimiento_format'] = '';

            if (!empty($flete['fecha_vencimiento'])) {

                $fecha = new DateTime(
                    $flete['fecha_vencimiento']
                );

                $flete['fecha_vencimiento_format'] =
                    $fecha->format('d/m/Y');
            }


            // =========================================================
            // HORA
            // =========================================================

            $flete['hora_salida_format'] =
                !empty($flete['hora_salida'])
                ? formato_12_horas(
                    $flete['hora_salida']
                )
                : '';


            // =========================================================
            // EMPRESA
            // =========================================================

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
            $empresa = $query->fetch(PDO::FETCH_ASSOC);


            // =========================================================
            // RESPUESTA
            // =========================================================

            return [
                'success' => true,
                'data' => [
                    'empresa' => $empresa,
                    'flete' => $flete
                ]
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'message' =>
                'Error en el servidor: ' .
                    $e->getMessage()
            ];
        }
    }

    public function cambiar_estado_flete($data)
    {
        try {

            $conn = $this->db->connect();

            $id_flete = (int) ($data['id_flete'] ?? 0);
            $nuevo_estado = strtoupper(trim($data['estado'] ?? ''));

            if ($id_flete <= 0) {
                return [
                    'success' => false,
                    'message' => 'El flete no es válido.'
                ];
            }

            if (empty($nuevo_estado)) {
                return [
                    'success' => false,
                    'message' => 'Debe indicar el nuevo estado.'
                ];
            }


            // =========================================================
            // BUSCAR FLETE ACTUAL
            // =========================================================

            $query = $conn->prepare("
            SELECT
                id_flete,
                tipo_operacion,
                id_vehiculo,
                id_conductor,
                fecha_salida,
                hora_salida,
                estado
            FROM flete
            WHERE id_flete = :id_flete
            LIMIT 1
        ");

            $query->execute([
                ':id_flete' => $id_flete
            ]);

            $flete = $query->fetch(PDO::FETCH_ASSOC);

            if (!$flete) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el flete.'
                ];
            }


            $estado_actual = $flete['estado'];


            // =========================================================
            // TRANSICIONES PERMITIDAS
            // =========================================================

            $transiciones = [

                'PENDIENTE' => [
                    'PROGRAMADO',
                    'CANCELADO'
                ],

                'PROGRAMADO' => [
                    'EN_CURSO',
                    'CANCELADO'
                ],

                'EN_CURSO' => [
                    'FINALIZADO'
                ],

                'FINALIZADO' => [],

                'CANCELADO' => []
            ];


            if (!isset($transiciones[$estado_actual])) {
                return [
                    'success' => false,
                    'message' => 'El estado actual del flete no es válido.'
                ];
            }


            if (!in_array(
                $nuevo_estado,
                $transiciones[$estado_actual],
                true
            )) {

                return [
                    'success' => false,
                    'message' =>
                    "No se puede cambiar el flete de {$estado_actual} a {$nuevo_estado}."
                ];
            }


            // =========================================================
            // VALIDACIONES ANTES DE PROGRAMAR
            // =========================================================

            if ($nuevo_estado === 'PROGRAMADO') {

                if (empty($flete['fecha_salida'])) {
                    return [
                        'success' => false,
                        'message' => 'El flete no tiene fecha de salida.'
                    ];
                }

                if (empty($flete['hora_salida'])) {
                    return [
                        'success' => false,
                        'message' => 'El flete no tiene hora de salida.'
                    ];
                }


                // -----------------------------------------------------
                // PROPIO / MIXTO
                // -----------------------------------------------------

                if (
                    $flete['tipo_operacion'] === 'PROPIO' ||
                    $flete['tipo_operacion'] === 'MIXTO'
                ) {

                    if (empty($flete['id_vehiculo'])) {
                        return [
                            'success' => false,
                            'message' =>
                            'Debe asignar un vehículo antes de programar el flete.'
                        ];
                    }

                    if (empty($flete['id_conductor'])) {
                        return [
                            'success' => false,
                            'message' =>
                            'Debe asignar un conductor antes de programar el flete.'
                        ];
                    }
                }


                // -----------------------------------------------------
                // TERCERIZADO
                // -----------------------------------------------------

                if ($flete['tipo_operacion'] === 'TERCERIZADO') {

                    $queryTercerizado = $conn->prepare("
                    SELECT
                        id_flete_tercerizado,
                        id_proveedor,
                        id_vehiculo,
                        id_conductor,
                        costo,
                        estado
                    FROM flete_tercerizado
                    WHERE id_flete = :id_flete
                    LIMIT 1
                ");

                    $queryTercerizado->execute([
                        ':id_flete' => $id_flete
                    ]);

                    $tercerizado =
                        $queryTercerizado->fetch(PDO::FETCH_ASSOC);

                    if (!$tercerizado) {
                        return [
                            'success' => false,
                            'message' =>
                            'El flete tercerizado no tiene información de tercerización.'
                        ];
                    }

                    if (empty($tercerizado['id_proveedor'])) {
                        return [
                            'success' => false,
                            'message' =>
                            'Debe seleccionar un proveedor antes de programar.'
                        ];
                    }

                    if (empty($tercerizado['id_vehiculo'])) {
                        return [
                            'success' => false,
                            'message' =>
                            'Debe asignar un vehículo tercerizado antes de programar.'
                        ];
                    }

                    if (empty($tercerizado['id_conductor'])) {
                        return [
                            'success' => false,
                            'message' =>
                            'Debe asignar un conductor tercerizado antes de programar.'
                        ];
                    }
                }
            }


            // =========================================================
            // VALIDACIÓN ANTES DE INICIAR
            // =========================================================

            if ($nuevo_estado === 'EN_CURSO') {

                if ($estado_actual !== 'PROGRAMADO') {
                    return [
                        'success' => false,
                        'message' =>
                        'Solo se puede iniciar un flete programado.'
                    ];
                }
            }


            // =========================================================
            // VALIDACIÓN ANTES DE FINALIZAR
            // =========================================================

            if ($nuevo_estado === 'FINALIZADO') {

                if ($estado_actual !== 'EN_CURSO') {
                    return [
                        'success' => false,
                        'message' =>
                        'Solo se puede finalizar un flete que está en curso.'
                    ];
                }
            }


            // =========================================================
            // ACTUALIZAR
            // =========================================================

            $queryUpdate = $conn->prepare("
            UPDATE flete
            SET estado = :estado
            WHERE id_flete = :id_flete
        ");

            $queryUpdate->execute([
                ':estado' => $nuevo_estado,
                ':id_flete' => $id_flete
            ]);


            // =========================================================
            // SI ES TERCERIZADO, SINCRONIZAR ESTADO OPERATIVO
            // =========================================================

            if ($flete['tipo_operacion'] === 'TERCERIZADO') {

                $estadoTercerizado = null;

                switch ($nuevo_estado) {

                    case 'PROGRAMADO':
                        $estadoTercerizado = 'ASIGNADO';
                        break;

                    case 'FINALIZADO':
                        $estadoTercerizado = 'FINALIZADO';
                        break;

                    case 'CANCELADO':
                        $estadoTercerizado = 'CANCELADO';
                        break;
                }


                if ($estadoTercerizado !== null) {

                    $queryTercerizado = $conn->prepare("
                    UPDATE flete_tercerizado
                    SET estado = :estado
                    WHERE id_flete = :id_flete
                ");

                    $queryTercerizado->execute([
                        ':estado' => $estadoTercerizado,
                        ':id_flete' => $id_flete
                    ]);
                }
            }


            return [
                'success' => true,
                'message' =>
                "El flete cambió de {$estado_actual} a {$nuevo_estado}.",
                'estado' => $nuevo_estado
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' =>
                'Error en el servidor: ' .
                    $e->getMessage()
            ];
        }
    }

    public function validar_flete_facturacion($id_flete)
    {
        try {

            $conn = $this->db->connect();
            // =========================================================
            // BUSCAR FLETE
            // =========================================================

            $query = $conn->prepare("
            SELECT

                f.id_flete,
                f.serie,
                f.id_cliente,

                f.ubigeo_origen,
                f.direccion_origen,

                f.ubigeo_destino,
                f.direccion_destino,

                f.tipo_operacion,

                f.fecha_salida,
                f.hora_salida,

                f.subtotal,
                f.incluye_igv,
                f.igv,
                f.total,

                f.condicion_pago,
                f.dias_credito,
                f.fecha_vencimiento,

                f.estado,
                f.estado_pago,

                cli.num_docu AS cliente_num_docu,

                CONCAT(
                    COALESCE(cli.nombres, ''),
                    ' ',
                    COALESCE(cli.apellidos, '')
                ) AS cliente_nombres,

                uo.distri AS origen,
                ud.distri AS destino,

                fv.id_flete_venta,
                fv.id_venta,
                fv.origen_facturacion,
                fv.id_valorizacion,
                tm.simbolo AS simbolo_moneda

            FROM flete f

            LEFT JOIN usuario cli
                ON cli.id_usuario = f.id_cliente

            LEFT JOIN ubigeo uo
                ON uo.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo ud
                ON ud.cod_ubigeo = f.ubigeo_destino

            LEFT JOIN flete_venta fv
                ON fv.id_flete = f.id_flete
                            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = f.id_tp_moneda

            WHERE f.id_flete = :id_flete

            LIMIT 1
        ");


            $query->execute([
                ':id_flete' => $id_flete
            ]);


            $flete =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$flete) {

                return [
                    'success' => false,
                    'message' =>
                    'No se encontró el flete.'
                ];
            }


            // =========================================================
            // DEBE ESTAR FINALIZADO
            // =========================================================

            if ($flete['estado'] !== 'FINALIZADO') {

                return [
                    'success' => false,
                    'message' =>
                    'Solo se pueden facturar fletes finalizados.'
                ];
            }


            // =========================================================
            // YA FACTURADO
            // =========================================================

            if (!empty($flete['id_venta'])) {

                $mensaje =
                    'Este flete ya fue facturado.';

                if (
                    $flete['origen_facturacion'] ===
                    'VALORIZACION'
                ) {

                    $mensaje =
                        'Este flete ya fue facturado mediante una valorización.';
                }


                return [
                    'success' => false,
                    'message' => $mensaje,
                    'id_venta' => $flete['id_venta']
                ];
            }


            // =========================================================
            // VALIDAR CLIENTE
            // =========================================================

            if (empty($flete['id_cliente'])) {

                return [
                    'success' => false,
                    'message' =>
                    'El flete no tiene un cliente asignado.'
                ];
            }


            // =========================================================
            // VALIDAR TOTAL
            // =========================================================

            if (
                (float) $flete['total'] <= 0
            ) {

                return [
                    'success' => false,
                    'message' =>
                    'El flete no tiene un importe válido para facturar.'
                ];
            }


            // =========================================================
            // NÚMERO FLETE
            // =========================================================

            $flete['numero_flete'] = sprintf(
                '%s-%08d',
                $flete['serie'],
                (int) $flete['id_flete']
            );

            $flete['numero_orden_servicio'] = !empty($flete['serie'])
                ? sprintf(
                    'OS-%s-%08d',
                    $flete['serie'],
                    (int) $flete['id_flete']
                )
                : sprintf(
                    'OS-%08d',
                    (int) $flete['id_flete']
                );


            // =========================================================
            // DESCRIPCIÓN
            // =========================================================

            $flete['descripcion_facturacion'] =
                'SERVICIO DE TRANSPORTE DE CARGA ' .
                trim(
                    ($flete['origen'] ?? '') .
                        ' - ' .
                        ($flete['destino'] ?? '')
                ) .
                ' / FLETE ' .
                $flete['numero_flete'];


            return [
                'success' => true,
                'message' =>
                'El flete está listo para facturar.',
                'data' => $flete
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' =>
                'Error al validar el flete: ' .
                    $e->getMessage()
            ];
        }
    }

    public function get_pago_tercerizado($data)
    {
        try {
            $id_flete = (int) ($data['id_flete'] ?? 0);
            $conn = $this->db->connect();
            $query = $conn->prepare("
            SELECT
                ft.id_flete_tercerizado,
                ft.id_flete,
                ft.id_proveedor,
                ft.id_vehiculo,
                ft.id_conductor,

                ft.costo,
                ft.estado,
                ft.estado_pago,
                ft.observacion,

                CONCAT(
                    COALESCE(p.nombres, ''),
                    ' ',
                    COALESCE(p.apellidos, '')
                ) AS proveedor_nombres,

                p.num_docu AS proveedor_num_docu,

                COALESCE(
                    SUM(fp.monto),
                    0
                ) AS total_pagado,

                GREATEST(
                    ft.costo - COALESCE(SUM(fp.monto), 0),
                    0
                ) AS saldo

            FROM flete_tercerizado ft

            LEFT JOIN usuario p
                ON p.id_usuario = ft.id_proveedor

            LEFT JOIN flete_tercerizado_pago fp
                ON fp.id_flete_tercerizado =
                   ft.id_flete_tercerizado

            WHERE ft.id_flete = :id_flete

            GROUP BY
                ft.id_flete_tercerizado,
                ft.id_flete,
                ft.id_proveedor,
                ft.id_vehiculo,
                ft.id_conductor,
                ft.costo,
                ft.estado,
                ft.estado_pago,
                ft.observacion,
                p.nombres,
                p.apellidos,
                p.num_docu

            LIMIT 1
        ");

            $query->execute([
                ':id_flete' => $id_flete
            ]);

            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) {

                return [
                    'success' => false,
                    'message' =>
                    'No se encontró información de tercerización.'
                ];
            }

            $data['costo'] = (float) $data['costo'];
            $data['total_pagado'] = (float) $data['total_pagado'];
            $data['saldo'] = (float) $data['saldo'];

            return [
                'success' => true,
                'message' => $data
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' =>
                'Error al obtener los pagos del tercerizado: ' .
                    $e->getMessage()
            ];
        }
    }

    public function get_historial_pago_tercerizado($data)
    {
        try {
            $id_flete_tercerizado = (int) $data['id_flete_tercerizado'];
            $conn = $this->db->connect();
            $query = $conn->prepare("
            SELECT
                fp.id_pago,
                fp.id_flete_tercerizado,
                fp.monto,
                fp.fecha_pago,
                fp.id_medio_pago,
                fp.numero_operacion,
                fp.observacion,
                fp.fecha_creacion,

                mp.descripcion AS medio_pago

            FROM flete_tercerizado_pago fp

            LEFT JOIN medio_pago mp
                ON mp.id_medio_pago = fp.id_medio_pago

            WHERE fp.id_flete_tercerizado =
                  :id_flete_tercerizado

            ORDER BY
                fp.fecha_pago DESC,
                fp.id_pago DESC
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $id_flete_tercerizado
            ]);

            return [
                'success' => true,
                'message' =>
                $query->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function registrar_pago_tercerizado($data)
    {
        try {
            $conn = $this->db->connect();

            $idFleteTercerizado = (int) (
                $data['id_flete_tercerizado'] ?? 0
            );

            $monto = round(
                (float) ($data['monto'] ?? 0),
                2
            );

            $fechaPago =
                trim($data['fecha_pago'] ?? '');

            $idMedioPago =
                !empty($data['id_medio_pago'])
                ? (int) $data['id_medio_pago']
                : null;

            $numeroOperacion =
                trim(
                    $data['numero_operacion'] ?? ''
                );

            $observacion =
                trim(
                    $data['observacion'] ?? ''
                );


            // =====================================================
            // VALIDACIONES
            // =====================================================

            if ($idFleteTercerizado <= 0) {

                return [
                    'success' => false,
                    'message' =>
                    'El flete tercerizado no es válido.'
                ];
            }


            if ($monto <= 0) {

                return [
                    'success' => false,
                    'message' =>
                    'El monto del pago debe ser mayor a cero.'
                ];
            }


            if (empty($fechaPago)) {

                return [
                    'success' => false,
                    'message' =>
                    'Debe ingresar la fecha del pago.'
                ];
            }


            // =====================================================
            // TRANSACCIÓN
            // =====================================================

            $conn->beginTransaction();


            // =====================================================
            // BLOQUEAR Y OBTENER COSTO
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id_flete_tercerizado,
                costo,
                estado_pago

            FROM flete_tercerizado

            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado

            FOR UPDATE
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $tercerizado =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$tercerizado) {

                throw new Exception(
                    'No se encontró el flete tercerizado.'
                );
            }


            $costo = round(
                (float) $tercerizado['costo'],
                2
            );


            // =====================================================
            // TOTAL PAGADO ACTUAL
            // =====================================================

            $query = $conn->prepare("
            SELECT
                COALESCE(
                    SUM(monto),
                    0
                ) AS total_pagado

            FROM flete_tercerizado_pago

            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $totalPagado =
                round(
                    (float) $query->fetchColumn(),
                    2
                );


            $saldo = round(
                $costo - $totalPagado,
                2
            );


            // =====================================================
            // VALIDAR SALDO
            // =====================================================

            if ($saldo <= 0) {

                throw new Exception(
                    'El proveedor ya se encuentra completamente pagado.'
                );
            }


            if ($monto > $saldo) {

                throw new Exception(
                    'El pago no puede ser mayor al saldo pendiente de S/ ' .
                        number_format(
                            $saldo,
                            2
                        )
                );
            }


            // =====================================================
            // INSERTAR PAGO
            // =====================================================

            $query = $conn->prepare("
            INSERT INTO flete_tercerizado_pago
            (
                id_flete_tercerizado,
                monto,
                fecha_pago,
                id_medio_pago,
                numero_operacion,
                observacion,
                id_sesionpersonal
            )
            VALUES
            (
                :id_flete_tercerizado,
                :monto,
                :fecha_pago,
                :id_medio_pago,
                :numero_operacion,
                :observacion,
                :id_sesionpersonal
            )
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado,

                ':monto' =>
                $monto,

                ':fecha_pago' =>
                $fechaPago,

                ':id_medio_pago' =>
                $idMedioPago,

                ':numero_operacion' =>
                $numeroOperacion ?: null,

                ':observacion' =>
                $observacion ?: null,

                ':id_sesionpersonal' =>
                $data['id_sesionpersonal'] ?? null
            ]);


            // =====================================================
            // NUEVO TOTAL
            // =====================================================

            $nuevoTotalPagado = round(
                $totalPagado + $monto,
                2
            );


            $nuevoSaldo = round(
                $costo - $nuevoTotalPagado,
                2
            );


            // =====================================================
            // NUEVO ESTADO
            // =====================================================

            if ($nuevoTotalPagado <= 0) {

                $estadoPago = 'PENDIENTE';
            } elseif ($nuevoSaldo > 0) {

                $estadoPago = 'PARCIAL';
            } else {

                $estadoPago = 'PAGADO';
            }


            // =====================================================
            // ACTUALIZAR TERCERIZADO
            // =====================================================

            $query = $conn->prepare("
            UPDATE flete_tercerizado

            SET estado_pago = :estado_pago

            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([
                ':estado_pago' =>
                $estadoPago,

                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);


            $conn->commit();


            return [
                'success' => true,

                'message' =>
                'Pago registrado correctamente.',

                'data' => [
                    'costo' =>
                    $costo,

                    'total_pagado' =>
                    $nuevoTotalPagado,

                    'saldo' =>
                    max(
                        $nuevoSaldo,
                        0
                    ),

                    'estado_pago' =>
                    $estadoPago
                ]
            ];
        } catch (Throwable $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }


            return [
                'success' => false,
                'message' =>
                $e->getMessage()
            ];
        }
    }



    public function facturar_flete($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();

            $conn->beginTransaction();


            // =========================================================
            // ID FLETE
            // =========================================================

            $idFlete =
                (int) (
                    $data["id_flete"]
                    ?? $data["id_flete_venta"]
                    ?? 0
                );


            if ($idFlete <= 0) {
                throw new Exception(
                    "El flete seleccionado no es válido."
                );
            }


            // =========================================================
            // BLOQUEAR / OBTENER FLETE
            // =========================================================

            $query = $conn->prepare("
            SELECT
                f.id_flete,
                f.id_cotizacion,
                f.id_cliente,
                f.id_tp_moneda,
                f.serie,

                f.subtotal,
                f.igv,
                f.total,

                f.condicion_pago,
                f.dias_credito,
                f.fecha_vencimiento,

                f.estado,
                f.estado_pago,

                f.ubigeo_origen,
                f.direccion_origen,
                f.ubigeo_destino,
                f.direccion_destino,

                f.observacion

            FROM flete f

            WHERE f.id_flete = :id_flete

            LIMIT 1

            FOR UPDATE
            ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $flete =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$flete) {
                throw new Exception(
                    "El flete seleccionado no existe."
                );
            }


            // =========================================================
            // ESTADO OPERATIVO
            // =========================================================

            if ($flete["estado"] !== "FINALIZADO") {

                throw new Exception(
                    "Solo se pueden facturar fletes finalizados."
                );
            }


            // =========================================================
            // CLIENTE
            // =========================================================

            if (
                empty($flete["id_cliente"]) ||
                (int) $flete["id_cliente"] <= 0
            ) {

                throw new Exception(
                    "El flete no tiene un cliente válido."
                );
            }


            // =========================================================
            // TOTAL
            // =========================================================

            if ((float) $flete["total"] <= 0) {

                throw new Exception(
                    "El total del flete debe ser mayor a cero."
                );
            }


            // =========================================================
            // EVITAR DOBLE FACTURACIÓN
            // =========================================================

            $query = $conn->prepare("
            SELECT
                id_flete_venta,
                id_venta

            FROM flete_venta

            WHERE id_flete = :id_flete

            LIMIT 1
        ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $facturacionExistente =
                $query->fetch(PDO::FETCH_ASSOC);


            if ($facturacionExistente) {

                throw new Exception(
                    "Este flete ya se encuentra facturado."
                );
            }


            // =========================================================
            // VALIDAR DETALLE
            // =========================================================

            $query = $conn->prepare("
            SELECT
                COUNT(*) AS cantidad,
                ROUND(SUM(importe_total), 2) AS total_detalle

            FROM flete_detalle

            WHERE id_flete = :id_flete
        ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $resumenDetalle =
                $query->fetch(PDO::FETCH_ASSOC);


            if (
                !$resumenDetalle ||
                (int) $resumenDetalle["cantidad"] <= 0
            ) {

                throw new Exception(
                    "El flete no tiene detalle comercial registrado."
                );
            }


            // =========================================================
            // COMPROBAR TOTAL
            // =========================================================

            $totalDetalle =
                round(
                    (float) $resumenDetalle["total_detalle"],
                    2
                );

            $totalFlete =
                round(
                    (float) $flete["total"],
                    2
                );


            if (
                abs(
                    $totalDetalle -
                        $totalFlete
                ) > 0.01
            ) {

                throw new Exception(
                    "El total del detalle del flete no coincide con el total del flete."
                );
            }


            // =========================================================
            // CREAR VENTA
            // =========================================================

            $venta =
                $this->crear_registro_venta_flete(
                    $flete,
                    $data,
                    $conn
                );


            if (!$venta["success"]) {

                throw new Exception(
                    $venta["message"]
                        ?? "No se pudo crear la venta."
                );
            }


            $idVenta =
                (int) $venta["id_venta"];


            // =========================================================
            // CREAR DETALLE COMPROBANTE
            // =========================================================

            $detalle = $this->registrar_detalle_comprobante_flete($idFlete, $idVenta, $conn);


            if (!$detalle["success"]) {
                throw new Exception($detalle["message"] ?? "No se pudo registrar el detalle del comprobante.");
            }


            // =========================================================
            // CUOTA
            // =========================================================
            $formaPago = (int) ($data["forma_pago"] ?? 0);


            if ($formaPago === 2) {
                $cuota = $this->registrar_cuota_flete($idVenta, $totalFlete, $data, $conn);
                if (!$cuota["success"]) {
                    throw new Exception($cuota["message"]  ?? "No se pudo registrar la cuota.");
                }
            }


            // =========================================================
            // RELACIÓN FLETE -> VENTA
            // =========================================================
            $relacion = $this->registrar_flete_venta($idFlete, $idVenta, $conn);

            if (!$relacion["success"]) {

                throw new Exception($relacion["message"]   ?? "No se pudo asociar el flete al comprobante.");
            }

            // =========================================================
            // ACTUALIZAR ESTADO DE PAGO DEL FLETE
            // =========================================================

            if ($formaPago === 1) {

                // CONTADO:
                // La venta se está registrando como pagada.
                $nuevoEstadoPago = "PAGADO";
            } else {

                // CRÉDITO:
                // Facturar no significa que el cliente ya pagó.
                $nuevoEstadoPago = "PENDIENTE";
            }

            $query = $conn->prepare("
    UPDATE flete
    SET estado_pago = :estado_pago
    WHERE id_flete = :id_flete
");

            $query->execute([
                ":estado_pago" => $nuevoEstadoPago,
                ":id_flete"    => $idFlete
            ]);

            if ($query->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo actualizar el estado de pago del flete."
                );
            }
            $conn->commit();

            return [
                "success" => true,

                "message" =>
                "Comprobante generado correctamente. Pendiente de envío a SUNAT.",

                "id_flete" =>
                $idFlete,

                "id_venta" =>
                $idVenta,

                "estado_sunat" =>
                0,

                "message_sunat" =>
                "Pendiente de envío a SUNAT.",

                "link_comprobante" =>
                null
            ];
        } catch (Exception $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {

                $conn->rollBack();
            }


            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function crear_registro_venta_flete(array $flete, array $data, PDO $conn)
    {
        try {

            // =====================================================
            // DATOS DEL COMPROBANTE
            // =====================================================

            $idTpComprobante =
                (int) (
                    $data["tp_comprobante"]
                    ?? $data["tipo_comprobante_venta"]
                    ?? 0
                );


            if (
                !in_array(
                    $idTpComprobante,
                    [1, 3],
                    true
                )
            ) {

                throw new Exception(
                    "Tipo de comprobante inválido."
                );
            }


            // =====================================================
            // SERIE
            // =====================================================

            $idSerie =
                (int) (
                    $data["serie_venta"]
                    ?? 0
                );


            if ($idSerie <= 0) {

                throw new Exception(
                    "Debe seleccionar una serie."
                );
            }


            // =====================================================
            // VALIDAR SERIE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id_serie,
                id_terminal,
                id_tp_comprobante,
                serie

            FROM serie

            WHERE id_serie = :id_serie
              AND id_tp_comprobante = :id_tp_comprobante

            LIMIT 1
        ");

            $query->execute([
                ":id_serie" =>
                $idSerie,

                ":id_tp_comprobante" =>
                $idTpComprobante
            ]);


            $serieData =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$serieData) {

                throw new Exception(
                    "La serie seleccionada no corresponde al tipo de comprobante."
                );
            }


            // =====================================================
            // CLIENTE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id_usuario,
                id_tp_docu,
                num_docu,
                nombres,
                apellidos

            FROM usuario

            WHERE id_usuario = :id_cliente

            LIMIT 1
        ");

            $query->execute([
                ":id_cliente" =>
                $flete["id_cliente"]
            ]);


            $cliente =
                $query->fetch(PDO::FETCH_ASSOC);


            if (!$cliente) {

                throw new Exception(
                    "El cliente del flete no existe."
                );
            }


            /*
         * FACTURA
         *
         * En tu catálogo tp_comprobante:
         * 1 = FACTURA
         *
         * Aquí conviene validar RUC.
         *
         * Si tu id_tp_docu del RUC no es 6,
         * cambia solamente este valor.
         */

            if ($idTpComprobante === 1) {

                if (
                    strlen(
                        trim(
                            $cliente["num_docu"]
                        )
                    ) !== 11
                ) {

                    throw new Exception(
                        "Para emitir una factura el cliente debe tener un RUC válido."
                    );
                }
            }


            // =====================================================
            // FECHA EMISIÓN
            // =====================================================

            $fechaEmision =
                trim(
                    $data["fecha_emision"]
                        ?? $data["fecha_emision_venta"]
                        ?? date("Y-m-d")
                );


            if (empty($fechaEmision)) {

                throw new Exception(
                    "La fecha de emisión es requerida."
                );
            }


            $fechaEmisionCompleta =
                $fechaEmision .
                " " .
                date("H:i:s");


            // =====================================================
            // FORMA DE PAGO
            // =====================================================

            $formaPago =
                (int) ($data["forma_pago"] ?? 0);

            $idTpMoneda = $flete['id_tp_moneda'] ?? 1;

            if (
                !in_array(
                    $formaPago,
                    [1, 2],
                    true
                )
            ) {

                throw new Exception(
                    "Forma de pago inválida."
                );
            }


            // =====================================================
            // MEDIO DE PAGO
            // =====================================================

            $idMedioPago = null;


            if ($formaPago === 1) {

                $idMedioPago =
                    (int) (
                        $data["medio_pago"]
                        ?? 0
                    );


                if ($idMedioPago <= 0) {

                    throw new Exception(
                        "Debe seleccionar el medio de pago."
                    );
                }
            }


            // =====================================================
            // FECHA VENCIMIENTO
            // =====================================================

            if ($formaPago === 2) {

                $fechaVencimiento =
                    trim(
                        $data["fecha_credito"]
                            ?? ""
                    );


                if (empty($fechaVencimiento)) {

                    throw new Exception(
                        "La fecha de vencimiento del crédito es requerida."
                    );
                }
            } else {

                $fechaVencimiento =
                    $fechaEmision;
            }


            // =====================================================
            // CORRELATIVO
            // =====================================================

            /*
         * Reutilizamos tu método actual.
         *
         * Este método ya forma parte de tu facturador.
         */

            $dataCorrelativo = $data;

            $dataCorrelativo["id_serie"] =
                $idSerie;

            $dataCorrelativo["serie_venta"] =
                $idSerie;

            $dataCorrelativo["tp_comprobante"] =
                $idTpComprobante;


            $correlativo =
                $this->get_correlativo(
                    $dataCorrelativo
                );


            if (
                empty($correlativo) ||
                (int) $correlativo <= 0
            ) {

                throw new Exception(
                    "No se pudo obtener el correlativo."
                );
            }


            // =====================================================
            // TOTALES
            // =====================================================

            $subtotal =
                round(
                    (float) $flete["subtotal"],
                    2
                );

            $igv =
                round(
                    (float) $flete["igv"],
                    2
                );

            $total =
                round(
                    (float) $flete["total"],
                    2
                );


            // =====================================================
            // CONSTANTES FLETE
            // =====================================================

            $idTpOperacion = 1;

            $idTpVenta = 7;

            $afectaDetraccion = 0;


            // =====================================================
            // ESTADO DE PAGO DE LA VENTA
            // =====================================================

            $estadoVenta =
                $formaPago === 2
                ? "PENDIENTE"
                : "PAGADO";


            // =====================================================
            // INSERT VENTA
            // =====================================================

            $query = $conn->prepare("
            INSERT INTO venta (
                codigo,

                id_terminal,
                id_vendedor,

                id_cliente,

                id_forma_pago,
                id_medio_pago,

                id_tp_moneda,
                id_tp_comprobante,

                id_caja_chica,

                id_serie,
                serie,
                correlativo,

                descuento,

                op_igv,
                icbper,

                estado,
                envio_sunat,

                descrip_cdr_sunat,
                cod_qr,
                hash_cdr,

                file_xml,
                file_cdr,

                fecha_emision,

                op_gravada,
                op_exonerada,
                op_inafecta,

                total,

                cod_operacion,

                fecha_vencimiento,

                obs,

                id_sesionpersonal,

                id_tp_operacion,

                afecta_detraccion,

                id_tp_venta
            )
            VALUES (
                :codigo,

                :id_terminal,
                :id_vendedor,

                :id_cliente,

                :id_forma_pago,
                :id_medio_pago,

                :id_tp_moneda,
                :id_tp_comprobante,

                :id_caja_chica,

                :id_serie,
                :serie,
                :correlativo,

                :descuento,

                :op_igv,
                :icbper,

                :estado,
                :envio_sunat,

                :descrip_cdr_sunat,
                :cod_qr,
                :hash_cdr,

                :file_xml,
                :file_cdr,

                :fecha_emision,

                :op_gravada,
                :op_exonerada,
                :op_inafecta,

                :total,

                :cod_operacion,

                :fecha_vencimiento,

                :obs,

                :id_sesionpersonal,

                :id_tp_operacion,

                :afecta_detraccion,

                :id_tp_venta
            )
        ");


            $query->execute([

                ":codigo" =>
                '',

                ":id_terminal" =>
                $serieData["id_terminal"],

                /*
             * Flete no tiene vendedor propio.
             * Usamos el usuario que está facturando.
             */
                ":id_vendedor" =>
                $this->id_usuario_sesion,

                ":id_cliente" =>
                $flete["id_cliente"],

                ":id_forma_pago" =>
                $formaPago,

                ":id_medio_pago" =>
                $idMedioPago,

                // Soles
                ":id_tp_moneda" =>
                $idTpMoneda,

                ":id_tp_comprobante" =>
                $idTpComprobante,

                ":id_caja_chica" =>
                !empty($data["id_caja"])
                    ? (int) $data["id_caja"]
                    : null,

                ":id_serie" =>
                $idSerie,

                ":serie" =>
                $serieData["serie"],

                ":correlativo" =>
                $correlativo,

                ":descuento" =>
                0,

                ":op_igv" =>
                $igv,

                ":icbper" =>
                0,

                ":estado" =>
                $estadoVenta,

                ":envio_sunat" =>
                0,

                ":descrip_cdr_sunat" =>
                null,

                ":cod_qr" =>
                null,

                ":hash_cdr" =>
                null,

                ":file_xml" =>
                null,

                ":file_cdr" =>
                null,

                ":fecha_emision" =>
                $fechaEmisionCompleta,

                ":op_gravada" =>
                $subtotal,

                ":op_exonerada" =>
                0,

                ":op_inafecta" =>
                0,

                ":total" =>
                $total,

                ":cod_operacion" =>
                null,

                ":fecha_vencimiento" =>
                $fechaVencimiento,

                ":obs" =>
                trim(
                    $flete["observacion"]
                        ?? ""
                ),

                ":id_sesionpersonal" =>
                $this->id_usuario_sesion,

                ":id_tp_operacion" =>
                $idTpOperacion,

                ":afecta_detraccion" =>
                $afectaDetraccion,

                ":id_tp_venta" =>
                $idTpVenta
            ]);


            $idVenta =
                (int) $conn->lastInsertId();


            if ($idVenta <= 0) {

                throw new Exception(
                    "No se pudo obtener el ID de la venta."
                );
            }


            return [
                "success" => true,
                "id_venta" => $idVenta,
                "serie" => $serieData["serie"],
                "correlativo" => $correlativo
            ];
        } catch (Exception $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function registrar_detalle_comprobante_flete(int $idFlete, int $idVenta, PDO $conn): array
    {
        try {

            // =====================================================
            // OBTENER DETALLE COMERCIAL DEL FLETE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                fd.id_flete_detalle,
                fd.id_producto,
                fd.item,
                fd.cantidad,
                fd.id_tipo_unidad,
                fd.valor_unitario,
                fd.precio_unitario,
                fd.porcentaje_igv,
                fd.igv,
                fd.valor_total,
                fd.importe_total,
                fd.afectacion_id,

                p.nombre AS producto_nombre,
                p.descripcion AS producto_descripcion,
                p.afecto_icbper,
                p.factor_icbper

            FROM flete_detalle fd

            INNER JOIN producto p
                ON p.id = fd.id_producto

            WHERE fd.id_flete = :id_flete

            ORDER BY fd.item ASC
        ");

            $query->execute([
                ":id_flete" => $idFlete
            ]);

            $detalles = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception(
                    "El flete no tiene detalle comercial."
                );
            }


            // =====================================================
            // INSERT DETALLE COMPROBANTE
            // =====================================================

            $insert = $conn->prepare("
            INSERT INTO detalle_comprobante
            (
                comprobante_id,
                item,
                producto_id,
                descripcion,
                cantidad,
                valor_unitario,
                precio_unitario,
                igv,
                porcentaje_igv,
                valor_total,
                icbper,
                factor_icbper,
                importe_total,
                afectacion_id,
                cantidad_viajes,
                id_tipo_unidad
            )
            VALUES
            (
                :comprobante_id,
                :item,
                :producto_id,
                :descripcion,
                :cantidad,
                :valor_unitario,
                :precio_unitario,
                :igv,
                :porcentaje_igv,
                :valor_total,
                :icbper,
                :factor_icbper,
                :importe_total,
                :afectacion_id,
                :cantidad_viajes,
                :id_tipo_unidad
            )
        ");


            foreach ($detalles as $detalle) {

                /*
             * Snapshot inicial.
             *
             * Después el usuario podrá modificar esta descripción
             * desde la vista previa sin modificar producto ni flete.
             */
                $descripcion = trim(
                    (string) ($detalle["producto_nombre"] ?? "")
                );

                if ($descripcion === "") {
                    $descripcion = "SERVICIO DE FLETE";
                }


                $cantidad = (float) $detalle["cantidad"];

                $insert->execute([
                    ":comprobante_id" => $idVenta,
                    ":item" => (int) $detalle["item"],
                    ":producto_id" => (int) $detalle["id_producto"],

                    ":descripcion" => $descripcion,

                    ":cantidad" => $cantidad,

                    ":valor_unitario" =>
                    (float) $detalle["valor_unitario"],

                    ":precio_unitario" =>
                    (float) $detalle["precio_unitario"],

                    ":igv" =>
                    (float) $detalle["igv"],

                    ":porcentaje_igv" =>
                    (float) $detalle["porcentaje_igv"],

                    ":valor_total" =>
                    (float) $detalle["valor_total"],

                    ":icbper" =>
                    0,

                    ":factor_icbper" =>
                    (float) ($detalle["factor_icbper"] ?? 0),

                    ":importe_total" =>
                    (float) $detalle["importe_total"],

                    ":afectacion_id" =>
                    (int) $detalle["afectacion_id"],

                    ":cantidad_viajes" =>
                    $cantidad,

                    ":id_tipo_unidad" =>
                    (int) $detalle["id_tipo_unidad"]
                ]);
            }


            return [
                "success" => true,
                "message" =>
                "Detalle del comprobante registrado correctamente."
            ];
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                "Error al registrar detalle del comprobante: "
                    . $e->getMessage()
            ];
        }
    }

    private function registrar_cuota_flete(int $idVenta, float $total, array $data, PDO $conn)
    {
        try {

            $fechaCredito = trim($data["fecha_credito"] ?? "");


            if (empty($fechaCredito)) {

                throw new Exception(
                    "Debe ingresar la fecha de vencimiento."
                );
            }


            if ($total <= 0) {

                throw new Exception(
                    "El monto del crédito no es válido."
                );
            }


            /*
         * IMPORTANTE:
         *
         * No confiamos en:
         *
         * $data["monto_credito"]
         *
         * El monto viene directamente del total
         * calculado en la BD.
         */

            $query = $conn->prepare("
            INSERT INTO cuota (
                comprobante_id,
                numero,
                importe,
                fecha_vencimiento,
                estado
            )
            VALUES (
                :comprobante_id,
                :numero,
                :importe,
                :fecha_vencimiento,
                :estado
            )
           ");


            $query->execute([
                ":comprobante_id" =>  $idVenta,
                ":numero" => "001",
                ":importe" => round($total, 2),
                ":fecha_vencimiento" =>   $fechaCredito,
                ":estado" => "N"
            ]);


            return [
                "success" => true
            ];
        } catch (Exception $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function registrar_flete_venta(int $idFlete, int $idVenta, PDO $conn)
    {
        try {

            $query = $conn->prepare("
            INSERT INTO flete_venta (
                id_flete,
                id_venta,
                origen_facturacion,
                id_valorizacion
            )
            VALUES (
                :id_flete,
                :id_venta,
                'DIRECTO',
                NULL
            )
        ");


            $query->execute([
                ":id_flete" =>
                $idFlete,

                ":id_venta" =>
                $idVenta
            ]);


            return [
                "success" => true
            ];
        } catch (PDOException $e) {

            /*
         * UNIQUE id_flete evita que dos procesos
         * facturen el mismo Flete simultáneamente.
         */

            if ($e->getCode() === "23000") {

                return [
                    "success" => false,
                    "message" =>
                    "El flete ya se encuentra asociado a un comprobante."
                ];
            }


            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }


    private function registrar_detalle_flete(int $idFlete, ?int $idCotizacion, float $subtotal, float $igv, float $total, int $incluyeIgv, PDO $conn)
    {
        try {

            if ($idFlete <= 0) {
                throw new Exception(
                    "El flete no es válido para registrar su detalle."
                );
            }

            // =====================================================
            // SI PROVIENE DE COTIZACIÓN
            // =====================================================

            if (!empty($idCotizacion)) {

                return $this->registrar_detalle_flete_cotizacion(
                    $idFlete,
                    $idCotizacion,
                    $subtotal,
                    $igv,
                    $total,
                    $incluyeIgv,
                    $conn
                );
            }

            // =====================================================
            // FLETE DIRECTO
            // =====================================================

            return $this->registrar_detalle_flete_directo(
                $idFlete,
                $subtotal,
                $igv,
                $total,
                $conn
            );
        } catch (Exception $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function registrar_detalle_flete_directo(int $idFlete,        float $subtotal, float $igv,    float $total, PDO $conn)
    {
        try {

            // =====================================================
            // PRODUCTO ESTÁNDAR DE FLETE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id,
                nombre,
                tipo_afectacion_id,
                unidad_id
            FROM producto
            WHERE codigo_interno = 'SRV-FLETE'
              AND estado = 1
            LIMIT 1
        ");

            $query->execute();

            $producto = $query->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception(
                    "No se encontró el producto SRV-FLETE."
                );
            }


            // =====================================================
            // PORCENTAJE IGV
            // =====================================================

            $porcentajeIgv = 0;

            if ($igv > 0) {
                $porcentajeIgv = (float) $this->get_igv();
            }

            // =====================================================
            // DETALLE
            // =====================================================

            $query = $conn->prepare("
            INSERT INTO flete_detalle (
                id_flete,
                id_producto,
                item,
                cantidad,
                id_tipo_unidad,
                valor_unitario,
                precio_unitario,
                porcentaje_igv,
                igv,
                valor_total,
                importe_total,
                afectacion_id,
                id_detalle_cotizacion
            )
            VALUES (
                :id_flete,
                :id_producto,
                1,
                1,
                :id_tipo_unidad,
                :valor_unitario,
                :precio_unitario,
                :porcentaje_igv,
                :igv,
                :valor_total,
                :importe_total,
                :afectacion_id,
                NULL
            )
        ");

            $query->execute([
                ":id_flete" =>
                $idFlete,

                ":id_producto" =>
                (int) $producto["id"],

                ":id_tipo_unidad" =>
                (int) $producto["unidad_id"],

                ":valor_unitario" =>
                round($subtotal, 2),

                ":precio_unitario" =>
                round($total, 2),

                ":porcentaje_igv" =>
                $porcentajeIgv,

                ":igv" =>
                round($igv, 2),

                ":valor_total" =>
                round($subtotal, 2),

                ":importe_total" =>
                round($total, 2),

                ":afectacion_id" =>
                (int) $producto["tipo_afectacion_id"]
            ]);


            return [
                "success" => true
            ];
        } catch (Exception $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function registrar_detalle_flete_cotizacion(int $idFlete, int $idCotizacion, float $subtotalFlete, float $igvFlete, float $totalFlete, int $incluyeIgv, PDO $conn)
    {
        try {

            // =====================================================
            // DETALLES DE COTIZACIÓN
            // =====================================================

            $query = $conn->prepare("
            SELECT
                dc.id_detalle,
                dc.item,
                dc.producto_id,
                dc.cantidad_viajes,
                dc.id_tipo_unidad,
                dc.precio_unitario,
                dc.importe_total,

                p.tipo_afectacion_id,
                p.unidad_id

            FROM detalle_cotizacion dc

            INNER JOIN producto p
                ON p.id = dc.producto_id

            WHERE dc.cotizacion_id = :id_cotizacion

            ORDER BY dc.item ASC
        ");

            $query->execute([
                ":id_cotizacion" => $idCotizacion
            ]);

            $detalles = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception(
                    "La cotización seleccionada no contiene productos o servicios."
                );
            }

            // =====================================================
            // IGV CONFIGURADO
            // =====================================================

            $igvConfig = (float) $this->get_igv();
            $factorIgv = $igvConfig / 100;

            // =====================================================
            // TOTAL ORIGINAL DE LA COTIZACIÓN
            // =====================================================

            $totalOriginalCotizacion = 0;

            foreach ($detalles as $detalle) {
                $totalOriginalCotizacion +=
                    (float) $detalle["importe_total"];
            }

            $totalOriginalCotizacion =
                round($totalOriginalCotizacion, 2);

            if ($totalOriginalCotizacion <= 0) {
                throw new Exception(
                    "El importe total de la cotización no es válido."
                );
            }

            // =====================================================
            // IMPORTE BASE DEL NUEVO FLETE
            // =====================================================
            //
            // Si el precio ingresado incluye IGV:
            // usamos el TOTAL.
            //
            // Si el precio NO incluye IGV:
            // usamos el SUBTOTAL.
            // =====================================================

            $nuevoImporteBase = $incluyeIgv === 1
                ? round($totalFlete, 2)
                : round($subtotalFlete, 2);

            if ($nuevoImporteBase <= 0) {
                throw new Exception(
                    "El importe del flete no es válido."
                );
            }

            // =====================================================
            // PREPARAR INSERT
            // =====================================================

            $insert = $conn->prepare("
            INSERT INTO flete_detalle (
                id_flete,
                id_producto,
                item,
                cantidad,
                id_tipo_unidad,
                valor_unitario,
                precio_unitario,
                porcentaje_igv,
                igv,
                valor_total,
                importe_total,
                afectacion_id,
                id_detalle_cotizacion
            )
            VALUES (
                :id_flete,
                :id_producto,
                :item,
                :cantidad,
                :id_tipo_unidad,
                :valor_unitario,
                :precio_unitario,
                :porcentaje_igv,
                :igv,
                :valor_total,
                :importe_total,
                :afectacion_id,
                :id_detalle_cotizacion
            )
        ");

            // =====================================================
            // ACUMULADORES
            // =====================================================

            $cantidadDetalles = count($detalles);

            $acumuladoValorTotal = 0;
            $acumuladoIgv = 0;
            $acumuladoImporteTotal = 0;

            // =====================================================
            // RECORRER DETALLES
            // =====================================================

            foreach ($detalles as $index => $detalle) {

                $cantidad = (float) $detalle["cantidad_viajes"];

                if ($cantidad <= 0) {
                    throw new Exception(
                        "La cantidad del item {$detalle['item']} no es válida."
                    );
                }

                $importeOriginal =
                    round((float) $detalle["importe_total"], 2);

                // =================================================
                // ÚLTIMO DETALLE
                // =================================================
                //
                // El último absorbe cualquier diferencia producida
                // por redondeos.
                // =================================================

                $esUltimo =
                    $index === ($cantidadDetalles - 1);

                if ($esUltimo) {

                    $valorTotal =
                        round(
                            $subtotalFlete - $acumuladoValorTotal,
                            2
                        );

                    $igv =
                        round(
                            $igvFlete - $acumuladoIgv,
                            2
                        );

                    $importeTotal =
                        round(
                            $totalFlete - $acumuladoImporteTotal,
                            2
                        );
                } else {

                    // =============================================
                    // PROPORCIÓN DEL DETALLE ORIGINAL
                    // =============================================

                    $proporcion =
                        $importeOriginal / $totalOriginalCotizacion;

                    // =============================================
                    // DISTRIBUIR NUEVOS IMPORTES
                    // =============================================

                    $valorTotal =
                        round(
                            $subtotalFlete * $proporcion,
                            2
                        );

                    $igv =
                        round(
                            $igvFlete * $proporcion,
                            2
                        );

                    $importeTotal =
                        round(
                            $totalFlete * $proporcion,
                            2
                        );
                }

                // =================================================
                // PORCENTAJE IGV
                // =================================================

                $esGravado =
                    (int) $detalle["tipo_afectacion_id"] === 1;

                $porcentajeIgv =
                    $esGravado && $igv > 0
                    ? $igvConfig
                    : 0;

                // =================================================
                // VALORES UNITARIOS
                // =================================================

                $valorUnitario =
                    round(
                        $valorTotal / $cantidad,
                        2
                    );

                $precioUnitario =
                    round(
                        $importeTotal / $cantidad,
                        2
                    );

                // =================================================
                // REGISTRAR
                // =================================================

                $insert->execute([

                    ":id_flete" =>
                    $idFlete,

                    ":id_producto" =>
                    (int) $detalle["producto_id"],

                    ":item" =>
                    (int) $detalle["item"],

                    ":cantidad" =>
                    $cantidad,

                    ":id_tipo_unidad" =>
                    !empty($detalle["id_tipo_unidad"])
                        ? (int) $detalle["id_tipo_unidad"]
                        : (int) $detalle["unidad_id"],

                    ":valor_unitario" =>
                    $valorUnitario,

                    ":precio_unitario" =>
                    $precioUnitario,

                    ":porcentaje_igv" =>
                    $porcentajeIgv,

                    ":igv" =>
                    $igv,

                    ":valor_total" =>
                    $valorTotal,

                    ":importe_total" =>
                    $importeTotal,

                    ":afectacion_id" =>
                    (int) $detalle["tipo_afectacion_id"],

                    ":id_detalle_cotizacion" =>
                    (int) $detalle["id_detalle"]
                ]);

                // =================================================
                // ACUMULAR
                // =================================================

                $acumuladoValorTotal += $valorTotal;
                $acumuladoIgv += $igv;
                $acumuladoImporteTotal += $importeTotal;
            }

            return [
                "success" => true
            ];
        } catch (Exception $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }
    private function get_data_comprobante_sunat(int $idVenta, PDO $conn)
    {
        try {

            if ($idVenta <= 0) {
                throw new Exception(
                    "El comprobante no es válido."
                );
            }

            $query = $conn->prepare("
            SELECT
                v.id_venta,
                v.id_cliente,
                v.id_tp_comprobante,
                v.id_tp_moneda,
                v.id_forma_pago,
                v.id_medio_pago,

                v.serie,
                v.correlativo,

                v.fecha_emision,
                v.fecha_vencimiento,

                v.op_gravada,
                v.op_exonerada,
                v.op_inafecta,
                v.op_igv,
                v.icbper,
                v.descuento,
                v.total,

                v.obs,

                v.id_tp_operacion,
                v.afecta_detraccion,
                v.id_tp_venta,

                u.id_tp_docu,
                u.num_docu,
                u.nombres,
                u.apellidos,

                date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format,
                date_format(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
                tp_c.codigo AS tp_comprobante_codigo,
                f_p.descripcion AS forma_pago,
                m_p.descripcion AS medio_pago,
                tp_m.codigo AS tp_moneda_codigo

            FROM venta v
             LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante = v.id_tp_comprobante
             LEFT JOIN forma_pago f_p ON f_p.id_forma_pago = v.id_forma_pago
             LEFT JOIN medio_pago m_p ON m_p.id_medio_pago = v.id_medio_pago
             LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda = v.id_tp_moneda
            INNER JOIN usuario u ON u.id_usuario = v.id_cliente
            WHERE v.id_venta = :id_venta

            LIMIT 1
            ");

            $query->execute([":id_venta" => $idVenta]);
            $venta = $query->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                throw new Exception(
                    "No se encontró el comprobante."
                );
            }


            $empresa = $this->consult_emisor();
            if (empty($empresa) || !is_array($empresa)) {
                throw new Exception("No se pudo obtener la información de la empresa.");
            }

            $query = $conn->prepare("
            SELECT
            dt_c.*,
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
            $query->bindParam(":id_comprobante", $idVenta);
            $query->execute();
            $productos = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($productos)) {
                throw new Exception(
                    "El comprobante no tiene productos."
                );
            }

            $query = $conn->prepare("SELECT
            CONCAT(c.nombres,' ',c.apellidos ) AS razon_social,
            c.id_tp_docu AS tipo_documento,
            c.num_docu AS ruc,
            c.direccion AS direccion
            FROM usuario c
            LEFT JOIN ubigeo ub_c ON ub_c.cod_ubigeo=c.ubigeo
            LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=c.id_tp_docu
            WHERE c.id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $venta['id_cliente']);
            $query->execute();
            $cliente = $query->fetch(PDO::FETCH_ASSOC);
            $cliente['pais'] = 'PE';



            $items = [];

            //==========CREACION DEL JSON PARA EL API ============
            foreach ($productos as $producto) {
                $p_igv = round($producto['porcentaje_igv']);
                $total_impuestos = $producto['igv'] + $producto['icbper'];
                $item = [
                    "item" => $producto['item'],
                    "nombre" => trim((string) ($producto["descripcion"] ?? "")),
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

            $cuotas = [];
            if ($venta['id_forma_pago'] == 2) {
                $query = $conn->prepare("
                  SELECT * FROM cuota
                  WHERE comprobante_id = :id_venta
                ");
                $query->bindParam(":id_venta", $idVenta);
                $query->execute();
                $data_cuota = $query->fetch(PDO::FETCH_ASSOC);

                if ($venta['id_forma_pago'] == 2) {
                    $cuotas[] = [
                        'numero' => $data_cuota['numero'],
                        'importe' => number_format($data_cuota['importe'], 2, '.', ''),
                        'vencimiento' => $data_cuota['fecha_vencimiento']
                    ];
                }
            }


            $data_detraccion = [];
            if ($venta['afecta_detraccion'] == 1) {
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
                $query->bindParam(":id_venta", $idVenta);
                $query->execute();
                $data_detraccion = $query->fetch(PDO::FETCH_ASSOC);

                if (!empty($empresa['cuenta_detraccion'])) {
                    $data_detraccion['cuenta_detraccion'] = $empresa['cuenta_detraccion'];
                }
            }

            // CALCULOS
            $ops_gravadas = $venta['op_gravada'] ?? 0.00;
            $ops_exoneradas = $venta['op_exonerada'] ?? 0.00;
            $ops_inafectas = $venta['op_inafecta'] ?? 0.00;
            $igv_ops = $venta['op_igv'] ?? 0.00;
            $icbper_ops = $venta['icbper'] ?? 0.00;

            $total_antes_impuestos = $ops_gravadas + $ops_exoneradas + $ops_inafectas;
            $total_impuestos = $igv_ops + $icbper_ops;
            $total_despues_impuestos = $total_antes_impuestos + $total_impuestos;

            $json = [
                "ose" => $empresa['ose'],
                "emisor" => $empresa,
                "cliente" => $cliente,
                "cabecera" => [
                    'tipo_operacion' => $venta['afecta_detraccion'] == 1 ? "1004" : "0101",
                    'tipo_comprobante' => $venta['tp_comprobante_codigo'],
                    'moneda' => $venta['tp_moneda_codigo'],
                    'serie' => $venta['serie'],
                    'correlativo' => $venta['correlativo'],
                    'total_op_gravadas' => $venta['op_gravada'],
                    'igv' => $venta['op_igv'],
                    'icbper' => $venta['icbper'],
                    'total_op_exoneradas' => $venta['op_exonerada'],
                    'total_op_inafectas' => $venta['op_inafecta'],
                    'total_antes_impuestos' => $total_antes_impuestos,
                    'total_impuestos' => $total_impuestos,
                    'total_despues_impuestos' => $total_despues_impuestos,
                    'descuento_global' => 0.00,
                    'suma_descuento_item' => 0.00,
                    'total_a_pagar' => $venta['total'],
                    'fecha_emision' => $venta['fecha_emision_format'],
                    'hora_emision' => $venta['hora_emision_format'],
                    'fecha_vencimiento' => $venta['fecha_vencimiento'],
                    'forma_pago' => ucwords(strtolower(ucfirst($venta['forma_pago']))),
                    'monto_credito' => $venta['id_forma_pago'] == 2 ? number_format($data_cuota['importe'], 2, '.', '') : 0.00,
                    'anexo_sucursal' => "0000",
                    'cuotas' => $cuotas,
                    'observacion' => $venta['obs'] ?? '',
                    "detraccion" => $venta['afecta_detraccion'] == 1 ? true : false,
                    "detraccion_data" => $venta['afecta_detraccion'] == 1 ? [
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
                ],
                "items" => $items
            ];

            return $json;
        } catch (Throwable $e) {

            throw new Exception(
                "Error al generar datos SUNAT: " .
                    $e->getMessage()
            );
        }
    }

    public function get_vista_previa_flete($idVenta)
    {
        try {

            $conn = $this->db->connect();

            $idVenta = (int) $idVenta;

            if ($idVenta <= 0) {
                throw new Exception(
                    "El comprobante seleccionado no es válido."
                );
            }


            // =====================================================
            // VENTA
            // =====================================================

            $query = $conn->prepare("
            SELECT
                v.*,

                CONCAT(
                    COALESCE(u.nombres, ''),
                    ' ',
                    COALESCE(u.apellidos, '')
                ) AS cliente,

                u.num_docu

            FROM venta v

            INNER JOIN usuario u
                ON u.id_usuario = v.id_cliente

            WHERE v.id_venta = :id_venta

            LIMIT 1
        ");

            $query->execute([
                ":id_venta" => $idVenta
            ]);

            $venta = $query->fetch(PDO::FETCH_ASSOC);


            if (!$venta) {
                throw new Exception(
                    "El comprobante no existe."
                );
            }


            // =====================================================
            // DETALLE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                dc.*,
                p.codigo_interno,
                p.nombre AS producto

            FROM detalle_comprobante dc

            INNER JOIN producto p
                ON p.id = dc.producto_id

            WHERE dc.comprobante_id = :id_venta

            ORDER BY dc.item ASC
        ");

            $query->execute([
                ":id_venta" => $idVenta
            ]);

            $detalle = $query->fetchAll(PDO::FETCH_ASSOC);


            return [
                "success" => true,
                "venta" => $venta,
                "detalle" => $detalle
            ];
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function actualizar_descripcion_comprobante($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();

            $conn->beginTransaction();


            $idVenta =
                (int) ($data["id_venta"] ?? 0);

            $idDetalle =
                (int) ($data["id_detalle"] ?? 0);

            $descripcion =
                trim(
                    (string) ($data["descripcion"] ?? "")
                );


            if ($idVenta <= 0 || $idDetalle <= 0) {
                throw new Exception(
                    "El detalle seleccionado no es válido."
                );
            }


            if ($descripcion === "") {
                throw new Exception(
                    "La descripción no puede estar vacía."
                );
            }


            // =====================================================
            // BLOQUEAR VENTA
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id_venta,
                envio_sunat

            FROM venta

            WHERE id_venta = :id_venta

            LIMIT 1

            FOR UPDATE
        ");

            $query->execute([
                ":id_venta" => $idVenta
            ]);

            $venta = $query->fetch(PDO::FETCH_ASSOC);


            if (!$venta) {
                throw new Exception(
                    "El comprobante no existe."
                );
            }


            // =====================================================
            // YA FUE ENVIADO
            // =====================================================

            if ((int) $venta["envio_sunat"] !== 0) {

                throw new Exception(
                    "El comprobante ya fue enviado a SUNAT y no puede modificarse."
                );
            }


            // =====================================================
            // ACTUALIZAR
            // =====================================================

            $query = $conn->prepare("
            UPDATE detalle_comprobante

            SET descripcion = :descripcion

            WHERE id = :id_detalle
              AND comprobante_id = :id_venta
        ");

            $query->execute([
                ":descripcion" => $descripcion,
                ":id_detalle" => $idDetalle,
                ":id_venta" => $idVenta
            ]);


            if ($query->rowCount() <= 0) {

                /*
             * rowCount puede ser 0 si se envía exactamente
             * la misma descripción.
             *
             * Por eso no necesariamente es error.
             */
            }


            $conn->commit();


            return [
                "success" => true,
                "message" =>
                "Descripción actualizada correctamente."
            ];
        } catch (Throwable $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }


            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function enviar_comprobante_sunat($data)
    {
        $conn = $this->db->connect();

        try {

            // =====================================================
            // VALIDAR ID VENTA
            // =====================================================

            $idVenta = isset($data['id_venta'])
                ? (int) $data['id_venta']
                : 0;

            if ($idVenta <= 0) {

                throw new Exception(
                    "El comprobante no es válido."
                );
            }


            // =====================================================
            // INICIAR TRANSACCIÓN
            // =====================================================

            $conn->beginTransaction();


            // =====================================================
            // VALIDAR COMPROBANTE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                id_venta,
                serie,
                correlativo,
                envio_sunat
            FROM venta
            WHERE id_venta = :id_venta
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_venta' => $idVenta
            ]);

            $venta = $query->fetch(PDO::FETCH_ASSOC);


            if (!$venta) {

                throw new Exception(
                    "No se encontró el comprobante."
                );
            }


            // =====================================================
            // EVITAR DOBLE ENVÍO
            // =====================================================

            if ((int) $venta['envio_sunat'] === 1) {

                throw new Exception(
                    "El comprobante ya fue enviado correctamente a SUNAT."
                );
            }


            // =====================================================
            // GENERAR JSON DESDE LA BD
            // =====================================================

            /*
         * En este punto ya se guardó la descripción
         * editada en detalle_comprobante.
         *
         * Por lo tanto get_data_comprobante_sunat()
         * obtendrá la versión definitiva.
         */

            $dataSunat =
                $this->get_data_comprobante_sunat(
                    $idVenta,
                    $conn
                );


            if (
                empty($dataSunat) ||
                !is_array($dataSunat)
            ) {

                throw new Exception(
                    "No se pudieron generar los datos del comprobante."
                );
            }


            // =====================================================
            // CONVERTIR A JSON
            // =====================================================

            $json = json_encode(
                $dataSunat,
                JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );


            if ($json === false) {

                throw new Exception(
                    "Error al generar el JSON: " .
                        json_last_error_msg()
                );
            }

            // =====================================================
            // ENVIAR A API
            // =====================================================

            $respuestaApi =
                $this->enviar_json_a_api(
                    $json
                );

            // =====================================================
            // VALIDAR RESPUESTA VACÍA
            // =====================================================

            if (
                $respuestaApi === false ||
                $respuestaApi === null ||
                trim((string) $respuestaApi) === ''
            ) {

                throw new Exception(
                    "La API no devolvió una respuesta."
                );
            }


            // =====================================================
            // DECODIFICAR RESPUESTA
            // =====================================================

            $respuestaApiDecode = json_decode(
                $respuestaApi,
                true
            );


            if (
                json_last_error() !== JSON_ERROR_NONE
            ) {

                throw new Exception(
                    "La API devolvió una respuesta inválida: " .
                        $respuestaApi
                );
            }


            // =====================================================
            // LA API DEVUELVE UN ARRAY
            // [
            //     {
            //         "estado": "1",
            //         ...
            //     }
            // ]
            // =====================================================

            if (
                !is_array($respuestaApiDecode) ||
                empty($respuestaApiDecode)
            ) {

                throw new Exception(
                    "La API no devolvió información del comprobante."
                );
            }


            $respuestaSunat = $respuestaApiDecode[0] ?? null;


            if (
                !is_array($respuestaSunat)
            ) {

                throw new Exception(
                    "La respuesta de SUNAT no tiene un formato válido."
                );
            }


            // =====================================================
            // VALIDAR ESTADO
            // =====================================================

            if (!array_key_exists(
                'estado',
                $respuestaSunat
            )) {

                throw new Exception(
                    $respuestaSunat['mensaje_sunat']
                        ?? "La API no devolvió el estado del comprobante."
                );
            }


            // =====================================================
            // PREPARAR DATOS
            // =====================================================

            $respuestaSunat['id_comprobante'] =
                $idVenta;


            $respuestaSunat['mensaje_sunat'] =
                $respuestaSunat['mensaje_sunat']
                ?? '';


            $respuestaSunat['hash_cpe'] =
                $respuestaSunat['hash_cpe']
                ?? '';


            $respuestaSunat['xml'] =
                $respuestaSunat['xml']
                ?? '';


            $respuestaSunat['cdr'] =
                $respuestaSunat['cdr']
                ?? '';


            // =====================================================
            // ACTUALIZAR VENTA
            // =====================================================

            $actualizacion =
                $this->actualizar_venta_sunat(
                    $respuestaSunat,
                    $conn
                );


            if (empty($actualizacion['success'])) {

                throw new Exception(
                    $actualizacion['message']
                        ?? "No se pudo actualizar el comprobante."
                );
            }

            // =====================================================
            // COMMIT
            // =====================================================

            $conn->commit();


            // =====================================================
            // RESPUESTA
            // =====================================================


            return [

                'success' => true,

                'message' =>
                $respuestaSunat['mensaje_sunat']
                    ?: 'Comprobante procesado correctamente.',

                'id_venta' =>
                $idVenta,

                'serie' =>
                $venta['serie'],

                'correlativo' =>
                $venta['correlativo'],

                'estado_sunat' =>
                (int) $respuestaSunat['estado'],

                'nombre_xml' =>
                $respuestaSunat['nombre_xml'] ?? null,

                'xml' =>
                $respuestaSunat['xml'] ?? null,

                'cdr' =>
                $respuestaSunat['cdr'] ?? null,

                'hash_cpe' =>
                $respuestaSunat['hash_cpe'] ?? null

            ];
        } catch (Throwable $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {

                $conn->rollBack();
            }


            return [

                'success' => false,

                'message' =>
                $e->getMessage()

            ];
        }
    }



    public function get_comprobante_pendiente($idVenta)
    {
        try {

            $conn = $this->db->connect();

            $idVenta = (int) $idVenta;

            if ($idVenta <= 0) {
                throw new Exception("El comprobante seleccionado no es válido.");
            }

            // =====================================================
            // COMPROBANTE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                v.*,

                CONCAT(
                    COALESCE(u.nombres, ''),
                    ' ',
                    COALESCE(u.apellidos, '')
                ) AS cliente,

                u.num_docu,

                fv.id_flete,
                tm.simbolo AS simbolo_moneda

            FROM venta v

            INNER JOIN usuario u
                ON u.id_usuario = v.id_cliente

            LEFT JOIN flete_venta fv
                ON fv.id_venta = v.id_venta

            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = v.id_tp_moneda
            WHERE v.id_venta = :id_venta

            LIMIT 1
        ");

            $query->execute([
                ":id_venta" => $idVenta
            ]);

            $venta = $query->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                throw new Exception("El comprobante no existe.");
            }


            // =====================================================
            // DETALLE
            // =====================================================

            $query = $conn->prepare("
            SELECT
                dc.*,
                p.codigo_interno,
                p.codigo_sunat,
                p.nombre AS producto

            FROM detalle_comprobante dc

            INNER JOIN producto p
                ON p.id = dc.producto_id

            WHERE dc.comprobante_id = :id_venta

            ORDER BY dc.item ASC
        ");

            $query->execute([
                ":id_venta" => $idVenta
            ]);

            $detalle = $query->fetchAll(PDO::FETCH_ASSOC);


            return [
                "success" => true,
                "venta" => $venta,
                "detalle" => $detalle
            ];
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function guardar_vista_previa($data)
    {
        $conn = $this->db->connect();

        try {

            $idVenta = isset($data['id_venta'])
                ? (int) $data['id_venta']
                : 0;


            if ($idVenta <= 0) {

                throw new Exception(
                    "El comprobante no es válido."
                );
            }


            // =====================================================
            // DETALLES
            // =====================================================

            $detalles = $data['detalles'] ?? null;


            if (is_string($detalles)) {

                $detalles = json_decode(
                    $detalles,
                    true
                );
            }


            if (
                !is_array($detalles) ||
                empty($detalles)
            ) {

                throw new Exception(
                    "No se recibieron detalles para guardar."
                );
            }


            // =====================================================
            // TRANSACCIÓN
            // =====================================================

            $conn->beginTransaction();


            // =====================================================
            // BLOQUEAR COMPROBANTE
            // =====================================================

            $stmtVenta = $conn->prepare("
            SELECT
                id_venta,
                envio_sunat
            FROM venta
            WHERE id_venta = :id_venta
            LIMIT 1
            FOR UPDATE
        ");


            $stmtVenta->execute([
                ':id_venta' => $idVenta
            ]);


            $venta = $stmtVenta->fetch(
                PDO::FETCH_ASSOC
            );


            if (!$venta) {

                throw new Exception(
                    "El comprobante no existe."
                );
            }


            // =====================================================
            // VALIDAR SUNAT
            // =====================================================

            if (
                (int) $venta['envio_sunat'] !== 0
            ) {

                throw new Exception(
                    "El comprobante ya fue procesado por SUNAT y no puede modificarse."
                );
            }


            // =====================================================
            // PREPARAR UPDATE
            // =====================================================

            /*
         * IMPORTANTE:
         *
         * Aquí estoy utilizando "id" como PK de
         * detalle_comprobante.
         *
         * Cámbialo por tu PK real si tiene otro nombre.
         */

            $stmtUpdate = $conn->prepare("
            UPDATE detalle_comprobante
            SET descripcion = :descripcion
            WHERE id = :id_detalle
              AND comprobante_id = :id_venta
        ");


            // =====================================================
            // ACTUALIZAR DETALLES
            // =====================================================

            foreach ($detalles as $detalle) {

                $idDetalle =
                    isset($detalle['id_detalle'])
                    ? (int) $detalle['id_detalle']
                    : 0;


                $descripcion =
                    trim(
                        $detalle['descripcion'] ?? ''
                    );


                if ($idDetalle <= 0) {

                    throw new Exception(
                        "Se encontró un detalle inválido."
                    );
                }


                if ($descripcion === '') {

                    throw new Exception(
                        "La descripción no puede estar vacía."
                    );
                }


                // Máximo de nuestra columna VARCHAR(1000)

                if (
                    mb_strlen(
                        $descripcion,
                        'UTF-8'
                    ) > 1000
                ) {

                    throw new Exception(
                        "La descripción no puede superar los 1000 caracteres."
                    );
                }


                $stmtUpdate->execute([

                    ':descripcion' =>
                    $descripcion,

                    ':id_detalle' =>
                    $idDetalle,

                    ':id_venta' =>
                    $idVenta

                ]);


                if (
                    $stmtUpdate->rowCount() === 0
                ) {

                    /*
                 * OJO:
                 * rowCount() también puede devolver 0
                 * cuando el texto enviado es exactamente
                 * igual al existente.
                 *
                 * Por eso no lanzaremos error aquí.
                 */
                }
            }


            // =====================================================
            // COMMIT
            // =====================================================

            $conn->commit();


            return [

                'success' => true,

                'message' =>
                'Cambios guardados correctamente.'

            ];
        } catch (Throwable $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {

                $conn->rollBack();
            }


            return [

                'success' => false,

                'message' =>
                $e->getMessage()

            ];
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

    public function get_data_orden_servicio($idFlete)
    {
        $conn = $this->db->connect();

        try {

            $idFlete = (int) $idFlete;

            if ($idFlete <= 0) {
                throw new Exception(
                    "El flete no es válido."
                );
            }


            // =====================================================
            // FLETE + TERCERIZACIÓN + PROVEEDOR
            // =====================================================

            $query = $conn->prepare("
            SELECT

                /* =============================================
                 * FLETE
                 * ============================================= */

                f.id_flete,
                f.serie,
                f.tipo_operacion,

                f.ubigeo_origen,
                f.direccion_origen,

                f.ubigeo_destino,
                f.direccion_destino,

                f.fecha_salida,
                f.hora_salida,

                f.observacion,

                /* =============================================
                 * RUTA
                 * ============================================= */

                uo.distri AS origen,
                ud.distri AS destino,

                /* =============================================
                 * TERCERIZACIÓN
                 * ============================================= */

                ft.id_flete_tercerizado,
                ft.id_proveedor,

                ft.id_vehiculo AS id_vehiculo_tercerizado,
                ft.id_conductor AS id_conductor_tercerizado,

                ft.costo AS costo_tercerizado,

                ft.estado AS estado_tercerizado,
                ft.observacion AS observacion_tercerizado,

                /* =============================================
                 * PROVEEDOR
                 * ============================================= */

                p.nombres AS proveedor_nombres,
                p.apellidos AS proveedor_apellidos,
                p.num_docu AS proveedor_documento,
                p.direccion AS proveedor_direccion,

                /* =============================================
                 * VEHÍCULO TERCERIZADO
                 * ============================================= */

                vt.placa AS vehiculo_placa,
                vt.descripcion AS vehiculo_descripcion,

                /* =============================================
                 * CONDUCTOR TERCERIZADO
                 * ============================================= */

                CONCAT(
                    COALESCE(ct.nombres, ''),
                    ' ',
                    COALESCE(ct.apellidos, '')
                ) AS conductor_nombres,

                ct.num_docu AS conductor_documento,

                /* =============================================
                 * RESPONSABLE / NEGOCIADOR
                 * ============================================= */

                CONCAT(
                    COALESCE(us.nombres, ''),
                    ' ',
                    COALESCE(us.apellidos, '')
                ) AS negociador

            FROM flete f

            INNER JOIN flete_tercerizado ft
                ON ft.id_flete = f.id_flete

            LEFT JOIN usuario p
                ON p.id_usuario = ft.id_proveedor

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo = ft.id_vehiculo

            LEFT JOIN usuario ct
                ON ct.id_usuario = ft.id_conductor

            LEFT JOIN usuario us
                ON us.id_usuario = f.id_sesionpersonal

            LEFT JOIN ubigeo uo
                ON uo.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo ud
                ON ud.cod_ubigeo = f.ubigeo_destino

            WHERE f.id_flete = :id_flete

            LIMIT 1
        ");


            $query->execute([
                ':id_flete' => $idFlete
            ]);


            $flete = $query->fetch(
                PDO::FETCH_ASSOC
            );


            // =====================================================
            // VALIDACIONES
            // =====================================================

            if (!$flete) {

                throw new Exception(
                    "No se encontró información de tercerización para este flete."
                );
            }


            if (
                $flete['tipo_operacion'] !== 'TERCERIZADO'
            ) {

                throw new Exception(
                    "La Orden de Servicio solo está disponible para fletes tercerizados."
                );
            }


            if (
                empty($flete['id_proveedor'])
            ) {

                throw new Exception(
                    "El flete no tiene un proveedor asignado."
                );
            }


            // =====================================================
            // NUMERACIÓN
            // =====================================================

            $serie = !empty($flete['serie'])
                ? $flete['serie']
                : null;


            $numeroOrden = $serie
                ? sprintf(
                    '%s-%08d',
                    $serie,
                    $idFlete
                )
                : sprintf(
                    '%08d',
                    $idFlete
                );


            // =====================================================
            // RUTA
            // =====================================================

            $origen = trim(
                (string) ($flete['origen'] ?? '')
            );

            $destino = trim(
                (string) ($flete['destino'] ?? '')
            );


            $ruta = trim(
                $origen . ' - ' . $destino,
                ' -'
            );


            // =====================================================
            // COSTO DE TERCERIZACIÓN
            // =====================================================

            /*
         * IMPORTANTE:
         *
         * Este monto NO es flete.total.
         *
         * Es el costo acordado con el proveedor.
         */

            $total = round(
                (float) $flete['costo_tercerizado'],
                2
            );


            if ($total <= 0) {

                throw new Exception(
                    "El costo de tercerización debe ser mayor a cero."
                );
            }

            // =====================================================
            // PAGOS REALIZADOS AL PROVEEDOR
            // =====================================================

            $query = $conn->prepare("
    SELECT
        fp.id_pago,
        fp.monto,
        fp.fecha_pago,
        fp.id_medio_pago,
        fp.numero_operacion,
        fp.observacion,
        fp.fecha_creacion,

        mp.descripcion AS medio_pago

    FROM flete_tercerizado_pago fp

    LEFT JOIN medio_pago mp
        ON mp.id_medio_pago = fp.id_medio_pago

    WHERE fp.id_flete_tercerizado =
          :id_flete_tercerizado

    ORDER BY
        fp.fecha_pago ASC,
        fp.id_pago ASC
");

            $query->execute([
                ':id_flete_tercerizado' =>
                $flete['id_flete_tercerizado']
            ]);

            $historialPagos =
                $query->fetchAll(PDO::FETCH_ASSOC);


            $totalPagado = 0;

            foreach ($historialPagos as &$pago) {

                $pago['monto'] =
                    round(
                        (float) $pago['monto'],
                        2
                    );

                $totalPagado += $pago['monto'];
            }

            unset($pago);

            $totalPagado =
                round($totalPagado, 2);

            $saldoPendiente =
                round(
                    max(0, $total - $totalPagado),
                    2
                );



            if ($totalPagado <= 0) {

                $estadoPago = 'PENDIENTE';
            } elseif ($saldoPendiente > 0) {

                $estadoPago = 'PARCIAL';
            } else {

                $estadoPago = 'PAGADO';
            }

            /*
         * Aquí estoy considerando que ft.costo representa
         * el TOTAL acordado con el proveedor, incluido IGV.
         *
         * Si en tu sistema costo significa valor SIN IGV,
         * esta parte cambia.
         */

            $valorVenta = round(
                $total / 1.18,
                2
            );


            $igv = round(
                $total - $valorVenta,
                2
            );


            // =====================================================
            // DESCRIPCIÓN
            // =====================================================

            $descripcion =
                'SERVICIO DE TRANSPORTE DE CARGA';


            // =====================================================
            // PROVEEDOR
            // =====================================================

            $razonSocialProveedor = trim(
                (
                    $flete['proveedor_nombres'] ?? ''
                ) .
                    ' ' .
                    (
                        $flete['proveedor_apellidos'] ?? ''
                    )
            );


            // =====================================================
            // EMPRESA EMISORA
            // =====================================================
            $query = $conn->prepare("SELECT
        e.envio_ose AS ose,
        e.num_docu AS ruc,
        e.razon_social AS razon_social,
        e.razon_social AS nombre_comercial,
        e.ubigeo AS ubigeo,
        ub.depa AS departamento,
        ub.provi AS provincia,
        ub.distri AS distrito,
        e.direccion_fiscal AS direccion,    
        e.user_sol AS usuario_sol,
        e.pass_sol AS clave_sol,
        td.id_tp_docu AS tipodoc,
        e.guia_id AS api_id,
        e.logo,
        e.guia_clave AS api_clave,
        e.nro_cuenta_BN AS cuenta_detraccion
        FROM terminal t
        LEFT JOIN empresa e ON e.id_empresa=t.id_empresa
        LEFT JOIN ubigeo ub ON ub.cod_ubigeo=e.ubigeo
        LEFT JOIN tp_docu AS tp_d ON tp_d.descripcion=e.tp_docu
        LEFT JOIN tp_docu td ON e.tp_docu = td.descripcion
        WHERE t.id_terminal=:id_terminal");
            $query->bindParam(":id_terminal", $this->id_terminal_sesion);
            $query->execute();
            $empresa = $query->fetch(PDO::FETCH_ASSOC);
            $empresa['pais'] = "PE";


            if (
                empty($empresa) ||
                !is_array($empresa)
            ) {

                throw new Exception(
                    "No se pudo obtener la información de la empresa."
                );
            }


            // =====================================================
            // ESTRUCTURA FINAL
            // =====================================================

            return [

                'success' => true,

                'orden_servicio' => [

                    // ---------------------------------------------
                    // CABECERA
                    // ---------------------------------------------

                    'id_flete' =>
                    $idFlete,

                    'numero' =>
                    $numeroOrden,

                    'fecha_emision' =>
                    date('Y-m-d'),

                    'fecha_servicio' =>
                    $flete['fecha_salida'],

                    'hora_servicio' =>
                    $flete['hora_salida'],


                    // ---------------------------------------------
                    // EMISOR
                    // ---------------------------------------------

                    'emisor' =>
                    $empresa,


                    // ---------------------------------------------
                    // PROVEEDOR
                    // ---------------------------------------------

                    'proveedor' => [

                        'id_proveedor' =>
                        $flete['id_proveedor'],

                        'razon_social' =>
                        $razonSocialProveedor,

                        'ruc' =>
                        $flete['proveedor_documento'],

                        'direccion' =>
                        $flete['proveedor_direccion'] ?? '',

                        /*
                     * Los dejo preparados.
                     * No los inventamos porque debemos usar
                     * los nombres reales de tus columnas.
                     */

                        'nombre_contacto' =>
                        '',

                        'telefono' =>
                        '',

                        'email' =>
                        ''

                    ],


                    // ---------------------------------------------
                    // SERVICIO
                    // ---------------------------------------------

                    'servicio' => [

                        'origen' =>
                        $origen,

                        'destino' =>
                        $destino,

                        'ruta' =>
                        $ruta,

                        'direccion_origen' =>
                        $flete['direccion_origen'],

                        'direccion_destino' =>
                        $flete['direccion_destino'],

                        'fecha' =>
                        $flete['fecha_salida'],

                        'hora' =>
                        $flete['hora_salida'],

                        /*
                     * El documento del cliente utiliza
                     * TONELADAS, pero actualmente no tenemos
                     * confirmado de dónde sale en tu BD.
                     */
                        'medida' =>
                        '',

                    ],


                    // ---------------------------------------------
                    // TRANSPORTE TERCERIZADO
                    // ---------------------------------------------

                    'transporte' => [

                        'vehiculo' =>
                        $flete['vehiculo_descripcion'] ?? '',

                        'placa' =>
                        $flete['vehiculo_placa'] ?? '',

                        'conductor' =>
                        trim(
                            $flete['conductor_nombres'] ?? ''
                        ),

                        'documento_conductor' =>
                        $flete['conductor_documento'] ?? ''

                    ],


                    // ---------------------------------------------
                    // DETALLE
                    // ---------------------------------------------

                    'detalle' => [

                        [
                            'cantidad' =>
                            1,

                            'descripcion' =>
                            $descripcion,

                            'valor_unitario' =>
                            $valorVenta,

                            'importe' =>
                            $valorVenta
                        ]

                    ],


                    // ---------------------------------------------
                    // TOTALES
                    // ---------------------------------------------

                    'totales' => [

                        'valor_venta' =>
                        $valorVenta,

                        'igv' =>
                        $igv,

                        'total' =>
                        $total

                    ],


                    // ---------------------------------------------
                    // PIE
                    // ---------------------------------------------

                    'negociador' =>
                    trim(
                        $flete['negociador'] ?? ''
                    ),

                    'plazo_pago' =>
                    '',

                    'observacion' =>
                    $flete['observacion_tercerizado']
                        ?? '',

                    'pagos' => [

                        'estado' =>
                        $estadoPago,

                        'total_servicio' =>
                        $total,

                        'total_pagado' =>
                        $totalPagado,

                        'saldo' =>
                        $saldoPendiente,

                        'cantidad_pagos' =>
                        count($historialPagos),

                        'historial' =>
                        $historialPagos
                    ],

                ]

            ];
        } catch (Throwable $e) {

            return [

                'success' => false,

                'message' =>
                $e->getMessage()

            ];
        }
    }

    public function editar_pago_tercerizado($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();

            $idPago =
                (int) ($data['id_pago'] ?? 0);

            $idFleteTercerizado =
                (int) (
                    $data['id_flete_tercerizado']
                    ?? 0
                );

            $monto =
                round(
                    (float) ($data['monto'] ?? 0),
                    2
                );

            $fechaPago =
                trim(
                    $data['fecha_pago'] ?? ''
                );

            $idMedioPago =
                !empty($data['id_medio_pago'])
                ? (int) $data['id_medio_pago']
                : null;

            $numeroOperacion =
                trim(
                    $data['numero_operacion']
                        ?? ''
                );

            $observacion =
                trim(
                    $data['observacion']
                        ?? ''
                );


            // =============================================
            // VALIDACIONES
            // =============================================

            if ($idPago <= 0) {
                throw new Exception(
                    'El pago seleccionado no es válido.'
                );
            }

            if ($idFleteTercerizado <= 0) {
                throw new Exception(
                    'El flete tercerizado no es válido.'
                );
            }

            if ($monto <= 0) {
                throw new Exception(
                    'El monto debe ser mayor a cero.'
                );
            }

            if (empty($fechaPago)) {
                throw new Exception(
                    'Debe ingresar la fecha del pago.'
                );
            }


            // =============================================
            // TRANSACCIÓN
            // =============================================

            $conn->beginTransaction();


            // =============================================
            // BLOQUEAR TERCERIZADO
            // =============================================

            $query = $conn->prepare("
            SELECT
                id_flete_tercerizado,
                costo
            FROM flete_tercerizado
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $tercerizado =
                $query->fetch(PDO::FETCH_ASSOC);

            if (!$tercerizado) {
                throw new Exception(
                    'No se encontró el flete tercerizado.'
                );
            }

            $costo =
                round(
                    (float) $tercerizado['costo'],
                    2
                );


            // =============================================
            // BLOQUEAR PAGO
            // =============================================

            $query = $conn->prepare("
            SELECT
                id_pago,
                id_flete_tercerizado,
                monto
            FROM flete_tercerizado_pago
            WHERE id_pago = :id_pago
              AND id_flete_tercerizado =
                  :id_flete_tercerizado
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_pago' =>
                $idPago,

                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $pagoActual =
                $query->fetch(PDO::FETCH_ASSOC);

            if (!$pagoActual) {
                throw new Exception(
                    'El pago seleccionado no existe.'
                );
            }


            // =============================================
            // SUMAR OTROS PAGOS
            // =============================================

            $query = $conn->prepare("
            SELECT
                COALESCE(SUM(monto), 0)
            FROM flete_tercerizado_pago
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
              AND id_pago <> :id_pago
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado,

                ':id_pago' =>
                $idPago
            ]);

            $otrosPagos =
                round(
                    (float) $query->fetchColumn(),
                    2
                );


            // =============================================
            // VALIDAR NUEVO MONTO
            // =============================================

            $disponible =
                round(
                    $costo - $otrosPagos,
                    2
                );

            if ($monto > $disponible) {

                throw new Exception(
                    'El monto no puede superar el saldo disponible de ' .
                        number_format(
                            $disponible,
                            2
                        )
                );
            }


            // =============================================
            // ACTUALIZAR PAGO
            // =============================================

            $query = $conn->prepare("
            UPDATE flete_tercerizado_pago
            SET
                monto = :monto,
                fecha_pago = :fecha_pago,
                id_medio_pago = :id_medio_pago,
                numero_operacion =
                    :numero_operacion,
                observacion =
                    :observacion
            WHERE id_pago = :id_pago
              AND id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([

                ':monto' =>
                $monto,

                ':fecha_pago' =>
                $fechaPago,

                ':id_medio_pago' =>
                $idMedioPago,

                ':numero_operacion' =>
                $numeroOperacion,

                ':observacion' =>
                $observacion,

                ':id_pago' =>
                $idPago,

                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);


            // =============================================
            // RECALCULAR
            // =============================================

            $nuevoTotalPagado =
                round(
                    $otrosPagos + $monto,
                    2
                );

            $nuevoSaldo =
                round(
                    $costo - $nuevoTotalPagado,
                    2
                );

            if ($nuevoTotalPagado <= 0) {

                $estadoPago = 'PENDIENTE';
            } elseif ($nuevoTotalPagado < $costo) {

                $estadoPago = 'PARCIAL';
            } else {

                $estadoPago = 'PAGADO';
            }


            // =============================================
            // ACTUALIZAR TERCERIZADO
            // =============================================

            $query = $conn->prepare("
            UPDATE flete_tercerizado
            SET estado_pago = :estado_pago
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([
                ':estado_pago' =>
                $estadoPago,

                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);


            $conn->commit();


            return [
                'success' => true,

                'message' =>
                'Pago actualizado correctamente.',

                'data' => [
                    'total_pagado' =>
                    $nuevoTotalPagado,

                    'saldo' =>
                    max($nuevoSaldo, 0),

                    'estado_pago' =>
                    $estadoPago
                ]
            ];
        } catch (Throwable $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function eliminar_pago_tercerizado($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();

            $idPago =
                (int) ($data['id_pago'] ?? 0);

            if ($idPago <= 0) {
                throw new Exception(
                    'El pago seleccionado no es válido.'
                );
            }

            $conn->beginTransaction();


            // =============================================
            // OBTENER Y BLOQUEAR PAGO
            // =============================================

            $query = $conn->prepare("
            SELECT
                id_pago,
                id_flete_tercerizado,
                monto
            FROM flete_tercerizado_pago
            WHERE id_pago = :id_pago
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_pago' => $idPago
            ]);

            $pago =
                $query->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                throw new Exception(
                    'El pago seleccionado no existe.'
                );
            }

            $idFleteTercerizado =
                (int) $pago['id_flete_tercerizado'];


            // =============================================
            // BLOQUEAR TERCERIZADO
            // =============================================

            $query = $conn->prepare("
            SELECT
                costo
            FROM flete_tercerizado
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $costo =
                $query->fetchColumn();

            if ($costo === false) {
                throw new Exception(
                    'No se encontró el flete tercerizado.'
                );
            }

            $costo =
                round(
                    (float) $costo,
                    2
                );


            // =============================================
            // ELIMINAR
            // =============================================

            $query = $conn->prepare("
            DELETE FROM flete_tercerizado_pago
            WHERE id_pago = :id_pago
        ");

            $query->execute([
                ':id_pago' => $idPago
            ]);


            // =============================================
            // RECALCULAR PAGOS
            // =============================================

            $query = $conn->prepare("
            SELECT
                COALESCE(SUM(monto), 0)
            FROM flete_tercerizado_pago
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([
                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);

            $totalPagado =
                round(
                    (float) $query->fetchColumn(),
                    2
                );

            $saldo =
                round(
                    $costo - $totalPagado,
                    2
                );


            // =============================================
            // ESTADO
            // =============================================

            if ($totalPagado <= 0) {

                $estadoPago = 'PENDIENTE';
            } elseif ($totalPagado < $costo) {

                $estadoPago = 'PARCIAL';
            } else {

                $estadoPago = 'PAGADO';
            }


            // =============================================
            // ACTUALIZAR TERCERIZADO
            // =============================================

            $query = $conn->prepare("
            UPDATE flete_tercerizado
            SET estado_pago = :estado_pago
            WHERE id_flete_tercerizado =
                  :id_flete_tercerizado
        ");

            $query->execute([
                ':estado_pago' =>
                $estadoPago,

                ':id_flete_tercerizado' =>
                $idFleteTercerizado
            ]);


            $conn->commit();


            return [
                'success' => true,

                'message' =>
                'Pago eliminado correctamente.',

                'data' => [
                    'total_pagado' =>
                    $totalPagado,

                    'saldo' =>
                    max($saldo, 0),

                    'estado_pago' =>
                    $estadoPago
                ]
            ];
        } catch (Throwable $e) {

            if (
                $conn &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
