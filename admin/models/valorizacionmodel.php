<?php

class ValorizacionModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_dataTable($data)
    {
        try {
            $conn = $this->db->connect();
            $filtros = [];

            $baseQuery = "FROM valorizacion v
        INNER JOIN usuario c ON c.id_usuario = v.id_cliente
        LEFT JOIN venta vc ON vc.id_venta = v.id_comprobante
LEFT JOIN (
    SELECT
        vd.id_valorizacion,
        COUNT(*) cantidad_fletes
    FROM valorizacion_detalle vd
    INNER JOIN flete f ON f.id_flete = vd.id_flete
    GROUP BY vd.id_valorizacion
) vd ON vd.id_valorizacion = v.id
        WHERE 1 = 1";

            if (!empty($data['filtro_fecha_inicio'])) {
                $baseQuery .= " AND DATE(v.fecha) >= :fecha_inicio";
                $filtros[':fecha_inicio'] = $data['filtro_fecha_inicio'];
            }

            if (!empty($data['filtro_fecha_fin'])) {
                $baseQuery .= " AND DATE(v.fecha) <= :fecha_fin";
                $filtros[':fecha_fin'] = $data['filtro_fecha_fin'];
            }

            if (!empty($data['filtro_cliente'])) {
                $baseQuery .= " AND v.id_cliente = :cliente";
                $filtros[':cliente'] = $data['filtro_cliente'];
            }

            if (!empty($data['filtro_estado'])) {
                $baseQuery .= " AND v.estado = :estado";
                $filtros[':estado'] = $data['filtro_estado'];
            }

            $stmtTotal = $conn->prepare("SELECT COUNT(*) $baseQuery");

            foreach ($filtros as $key => $value) {
                $stmtTotal->bindValue($key, $value);
            }

            $stmtTotal->execute();

            $recordsTotal = $stmtTotal->fetchColumn();

            $searchValue = trim($data['search']['value'] ?? '');
            $searchQuery = "";

            if ($searchValue !== "") {

                $searchQuery = " AND (
                CONCAT(v.serie,'-',LPAD(v.correlativo,8,'0')) LIKE :search
                OR CONCAT(c.nombres,' ',c.apellidos) LIKE :search
                OR c.num_docu LIKE :search
                OR v.estado LIKE :search
                OR CONCAT(vc.serie,'-',vc.correlativo) LIKE :search
            )";
            }

            $stmtFiltered = $conn->prepare("SELECT COUNT(*) $baseQuery $searchQuery");

            foreach ($filtros as $key => $value) {
                $stmtFiltered->bindValue($key, $value);
            }

            if ($searchValue !== "") {
                $stmtFiltered->bindValue(':search', "%{$searchValue}%");
            }

            $stmtFiltered->execute();

            $recordsFiltered = $stmtFiltered->fetchColumn();

            $selectFields = "v.id,
        v.fecha,
        v.serie,
        v.correlativo,
        v.total,
        v.observacion,
        v.estado,
        v.id_comprobante,
        CONCAT(c.nombres,' ',c.apellidos, ' - ',c.num_docu) cliente,
        c.num_docu,
        c.id_tp_docu,
        CONCAT(vc.serie,'-',vc.correlativo) comprobante,
        IFNULL(vd.cantidad_fletes,0) cantidad_fletes";

            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 20);

            $limitClause = "";
            if ($length > 0) {
                $limitClause = "LIMIT :start,:length";
            }

            $sqlData = "SELECT $selectFields
        $baseQuery
        $searchQuery
        ORDER BY v.fecha DESC, v.id DESC
        $limitClause";

            $stmtData = $conn->prepare($sqlData);

            foreach ($filtros as $key => $value) {
                $stmtData->bindValue($key, $value);
            }

            if ($searchValue !== "") {
                $stmtData->bindValue(':search', "%{$searchValue}%");
            }

            if ($length > 0) {
                $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
                $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
            }

            $stmtData->execute();

            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $stmtData->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {

            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $e->getMessage()
            ];
        }
    }

    public function get_documentosxpagar($data)
    {
        try {
            $conn = $this->db->connect();
            $id_cliente = $data['id_cliente'] ?? 0;
            if ($id_cliente == 0) {
                throw new Exception('No se puso ningún cliente');
            }

            $query = $conn->prepare("
         SELECT 
         v.id_venta,
         CONCAT(v.serie, '-', v.correlativo) AS numero_guia,
         DATE(v.fecha_emision) AS fecha_emision,
         v.total AS monto,
         dtv.id_encomienda
         FROM venta v
         LEFT JOIN dt_venta dtv ON dtv.id_venta = v.id_venta
         LEFT JOIN encomienda e ON e.id_encomienda = dtv.id_encomienda
         LEFT JOIN valorizacion_detalle vd ON vd.id_venta = v.id_venta
         WHERE v.id_cliente = :id_cliente 
         AND v.id_tp_comprobante IN (2,31)  
         AND e.pago = 'PAGO EN BLOQUE' 
         AND v.estado != 'PAGADO'
         AND vd.id IS NULL
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
                return [
                    'success' => true,
                    'message' => 'Se hallaron registros',
                    'data' => $notas
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se hallaron registros',
                    'data' => []
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ups.... ocurrio un error: ' . $e,
                'data' => []
            ];
        }
    }

    public function get_fletes_cliente($data)
    {
        try {

            $conn = $this->db->connect();

            $idCliente = (int) ($data['id_cliente'] ?? 0);

            if ($idCliente <= 0) {
                throw new Exception('Cliente no válido.');
            }

            $query = $conn->prepare("
            SELECT
                f.id_flete,
                f.serie,
                f.id_cliente,

                f.ubigeo_origen,
                f.direccion_origen,
                u_ori.distri AS origen,

                f.ubigeo_destino,
                f.direccion_destino,
                u_desti.distri AS destino,

                f.subtotal,
                f.igv,
                f.total,

                f.id_tp_moneda,
                tm.codigo AS codigo_moneda,
                tm.simbolo AS simbolo_moneda,

                f.fecha_salida,
                f.hora_salida,
                f.tipo_operacion,
                f.estado,

                CASE
                    WHEN f.tipo_operacion = 'TERCERIZADO'
                        THEN vt.placa
                    ELSE vp.placa
                END AS vehiculo

            FROM flete f

            LEFT JOIN ubigeo u_ori
                ON u_ori.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo u_desti
                ON u_desti.cod_ubigeo = f.ubigeo_destino

            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = f.id_tp_moneda

            /* Vehículo propio */
            LEFT JOIN vehiculo vp
                ON vp.id_vehiculo = f.id_vehiculo

            /* Tercerización */
            LEFT JOIN flete_tercerizado ft
                ON ft.id_flete = f.id_flete
                AND ft.estado <> 'CANCELADO'

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo = ft.id_vehiculo

            /* Evitar fletes que ya pertenecen a una valorización */
            LEFT JOIN valorizacion_detalle vd
                ON vd.id_flete = f.id_flete

            /* Evitar fletes que ya fueron facturados */
            LEFT JOIN flete_venta fv
                ON fv.id_flete = f.id_flete

            WHERE f.id_cliente = :id_cliente

            AND f.estado = 'FINALIZADO'

            AND vd.id_flete IS NULL

            AND fv.id_flete IS NULL

            ORDER BY
                f.fecha_salida DESC,
                f.id_flete DESC
        ");

            $query->bindValue(
                ':id_cliente',
                $idCliente,
                PDO::PARAM_INT
            );

            $query->execute();

            $fletes = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fletes as &$flete) {

                /*
             * Número interno del Flete
             */
                $flete['numero_flete'] =
                    !empty($flete['serie'])
                    ? sprintf(
                        '%s-%08d',
                        $flete['serie'],
                        $flete['id_flete']
                    )
                    : sprintf(
                        '%08d',
                        $flete['id_flete']
                    );

                /*
             * Conversión numérica
             */
                $flete['subtotal'] =
                    round((float) $flete['subtotal'], 2);

                $flete['igv'] =
                    round((float) $flete['igv'], 2);

                $flete['total'] =
                    round((float) $flete['total'], 2);

                /*
             * TEMPORAL:
             * tu JS actual todavía trabaja con "precio".
             *
             * Esto nos permite migrar backend primero sin romper
             * inmediatamente el frontend.
             */
                $flete['precio'] = $flete['total'];
            }

            unset($flete);

            return [
                'success' => true,
                'message' => 'Fletes disponibles encontrados.',
                'data' => $fletes
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    public function add_register($data)
    {
        $conn = $this->db->connect();

        try {
            $conn->beginTransaction();
            if (
                empty($data['id_cliente']) ||
                (int) $data['id_cliente'] <= 0
            ) {
                throw new Exception('Debe seleccionar un cliente válido.');
            }

            if (
                empty($data['id_serie']) ||
                (int) $data['id_serie'] <= 0
            ) {
                throw new Exception('Debe seleccionar una serie válida.');
            }
            $id_empresa = 1;

            // =========================================================
            // 1. Validar documentos recibidos
            // =========================================================

            $documentos = json_decode($data['documentos'], true);

            if (!is_array($documentos) || empty($documentos)) {
                throw new Exception('No se han enviado documentos válidos');
            }

            // Evitar fletes repetidos
            $ids_flete = [];

            foreach ($documentos as $doc) {

                if (empty($doc['id_flete'])) {
                    throw new Exception('Se ha recibido un documento sin flete');
                }

                $id_flete = (int) $doc['id_flete'];

                if (in_array($id_flete, $ids_flete)) {
                    throw new Exception('No se puede agregar el mismo flete más de una vez');
                }

                $ids_flete[] = $id_flete;
            }

            // =========================================================
            // 2. Obtener los datos reales de los fletes
            // =========================================================

            $stmtFlete = $conn->prepare("
    SELECT
        f.id_flete,
        f.id_cliente,
        f.id_tp_moneda,
        f.subtotal,
        f.igv,
        f.total,
        f.estado,

        vd.id AS id_valorizacion_detalle,
        fv.id_flete_venta

    FROM flete f

    LEFT JOIN valorizacion_detalle vd
        ON vd.id_flete = f.id_flete

    LEFT JOIN flete_venta fv
        ON fv.id_flete = f.id_flete

    WHERE f.id_flete = :id_flete

    LIMIT 1

    FOR UPDATE
");

            $idMonedaValorizacion = null;

            $subtotal = 0;
            $igv = 0;
            $total = 0;

            $fletes = [];

            foreach ($ids_flete as $id_flete) {

                $stmtFlete->bindValue(
                    ':id_flete',
                    $id_flete,
                    PDO::PARAM_INT
                );

                $stmtFlete->execute();

                $flete = $stmtFlete->fetch(PDO::FETCH_ASSOC);

                if (!$flete) {
                    throw new Exception(
                        'No se encontró el flete con ID: ' . $id_flete
                    );
                }

                // =========================================================
                // VALIDAR CLIENTE
                // =========================================================

                if ((int) $flete['id_cliente'] !== (int) $data['id_cliente']) {

                    throw new Exception(
                        'El flete ' . $id_flete .
                            ' no pertenece al cliente seleccionado.'
                    );
                }

                // =========================================================
                // VALIDAR ESTADO
                // =========================================================

                if ($flete['estado'] !== 'FINALIZADO') {

                    throw new Exception(
                        'El flete ' . $id_flete .
                            ' debe estar FINALIZADO para ser valorizado.'
                    );
                }

                // =========================================================
                // YA ESTÁ EN UNA VALORIZACIÓN
                // =========================================================

                if (!empty($flete['id_valorizacion_detalle'])) {

                    throw new Exception(
                        'El flete ' . $id_flete .
                            ' ya pertenece a otra valorización.'
                    );
                }

                // =========================================================
                // YA ESTÁ FACTURADO
                // =========================================================

                if (!empty($flete['id_flete_venta'])) {

                    throw new Exception(
                        'El flete ' . $id_flete .
                            ' ya fue facturado.'
                    );
                }

                // =========================================================
                // VALIDAR MONEDA
                // =========================================================

                $idMonedaFlete = (int) $flete['id_tp_moneda'];

                if ($idMonedaFlete <= 0) {

                    throw new Exception(
                        'El flete ' . $id_flete .
                            ' no tiene una moneda configurada.'
                    );
                }

                if ($idMonedaValorizacion === null) {

                    $idMonedaValorizacion = $idMonedaFlete;
                } elseif ($idMonedaValorizacion !== $idMonedaFlete) {

                    throw new Exception(
                        'No se pueden mezclar fletes de diferentes monedas en una valorización.'
                    );
                }

                // =========================================================
                // IMPORTES REALES
                // =========================================================

                $fleteSubtotal = round(
                    (float) $flete['subtotal'],
                    2
                );

                $fleteIgv = round(
                    (float) $flete['igv'],
                    2
                );

                $fleteTotal = round(
                    (float) $flete['total'],
                    2
                );

                /*
     * Seguridad:
     * subtotal + igv debe coincidir con total.
     */
                if (
                    abs(
                        ($fleteSubtotal + $fleteIgv)
                            - $fleteTotal
                    ) > 0.02
                ) {

                    throw new Exception(
                        'Los importes del flete ' .
                            $id_flete .
                            ' no son consistentes.'
                    );
                }

                $subtotal += $fleteSubtotal;
                $igv += $fleteIgv;
                $total += $fleteTotal;

                $fletes[] = [
                    'id_flete' => $id_flete,
                    'importe' => $fleteTotal
                ];
            }

            // Redondeamos los totales
            $subtotal = round($subtotal, 2);
            $igv = round($igv, 2);
            $total = round($total, 2);

            if ($total <= 0) {
                throw new Exception(
                    'El total de la valorización debe ser mayor a cero.'
                );
            }

            // =========================================================
            // 3. Obtener serie y correlativo
            // =========================================================

            $data_serie = $this->get_data_serie($data, $conn);

            if (!$data_serie['success']) {
                throw new Exception(
                    'No se ha encontrado la información correcta de la serie'
                );
            }

            $serie = $data_serie['message']['serie'];
            $correlativo = $data_serie['message']['correlativo'];

            // =========================================================
            // 4. Insertar valorización
            // =========================================================

            $query = $conn->prepare("
            INSERT INTO valorizacion (
    id_empresa,
    id_cliente,
    id_tp_moneda,
    id_serie,
    serie,
    correlativo,
    fecha,
    observacion,
    subtotal,
    igv,
    total,
    estado,
    created_by
)
VALUES (
    :id_empresa,
    :id_cliente,
    :id_tp_moneda,
    :id_serie,
    :serie,
    :correlativo,
    NOW(),
    :observacion,
    :subtotal,
    :igv,
    :total,
    'ABIERTA',
    :usuario
)
            ");

            $query->bindValue(
                ':id_empresa',
                $id_empresa,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':id_cliente',
                $data['id_cliente'],
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':id_tp_moneda',
                $idMonedaValorizacion,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':id_serie',
                $data['id_serie'],
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':serie',
                $serie
            );

            $query->bindValue(
                ':correlativo',
                $correlativo
            );

            $query->bindValue(
                ':observacion',
                $data['observacion'] ?? null
            );

            $query->bindValue(
                ':subtotal',
                $subtotal
            );

            $query->bindValue(
                ':igv',
                $igv
            );

            $query->bindValue(
                ':total',
                $total
            );

            $query->bindValue(
                ':usuario',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $id_valorizacion = $conn->lastInsertId();

            // =========================================================
            // 5. Actualizar correlativo
            // =========================================================

            $actualizarCorrelativo = $this->actualizar_correlativo([
                'id_serie' => $data['id_serie'],
                'correlativo' => $correlativo,
            ], $conn);

            if (!$actualizarCorrelativo['success']) {
                throw new Exception(
                    'No se pudo actualizar el correlativo de la valorización.'
                );
            }

            // =========================================================
            // 6. Insertar detalle
            // =========================================================

            $stmtDetalle = $conn->prepare("
            INSERT INTO valorizacion_detalle (
                id_valorizacion,
                id_flete,
                importe,
                observacion
            ) VALUES (
                :id_valorizacion,
                :id_flete,
                :importe,
                :observacion
            )
            ");

            foreach ($fletes as $flete) {

                $stmtDetalle->bindValue(
                    ':id_valorizacion',
                    $id_valorizacion,
                    PDO::PARAM_INT
                );

                $stmtDetalle->bindValue(
                    ':id_flete',
                    $flete['id_flete'],
                    PDO::PARAM_INT
                );

                $stmtDetalle->bindValue(
                    ':importe',
                    $flete['importe']
                );

                $stmtDetalle->bindValue(
                    ':observacion',
                    null
                );

                $stmtDetalle->execute();
            }

            // =========================================================
            // 7. Historial
            // =========================================================

            $stmtHist = $conn->prepare("
    INSERT INTO valorizacion_historial (
        id_valorizacion,
        estado,
        observacion,
        usuario
    ) VALUES (
        :id_valorizacion,
        'ABIERTA',
        'Creación de valorización',
        :usuario
    )
");

            $stmtHist->bindValue(
                ':id_valorizacion',
                $id_valorizacion,
                PDO::PARAM_INT
            );

            $stmtHist->bindValue(
                ':usuario',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $stmtHist->execute();

            // =========================================================
            // 8. Confirmar
            // =========================================================

            $conn->commit();

            return [
                'success' => true,
                'message' => 'Valorización creada con éxito',
                'id_valorizacion' => $id_valorizacion,
            ];
        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
                // 'debug' => $e->getMessage()
            ];
        }
    }
    public function get_data_serie($data, $conn)
    {
        $query = $conn->prepare("SELECT serie, correlativo FROM serie WHERE id_serie = :id_serie FOR UPDATE");
        $query->bindParam(':id_serie', $data['id_serie']);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['success' => false, 'message' => 'Serie no encontrada'];
        }

        $correlativo = ($row['correlativo'] == 0 || $row['correlativo'] === null)
            ? 1
            : $row['correlativo'] + 1;

        return [
            'success' => true,
            'message' => [
                'serie' => $row['serie'],
                'correlativo' => $correlativo,
            ],
        ];
    }

    public function actualizar_correlativo($data, $conn)
    {
        $update = $conn->prepare("UPDATE serie SET correlativo = :correlativo WHERE id_serie = :id_serie");
        $update->bindParam(':correlativo', $data['correlativo']);
        $update->bindParam(':id_serie', $data['id_serie']);
        $update->execute();

        return ['success' => true, 'message' => 'Se actualizó el correlativo'];
    }
    public function edit_register($data)
    {
        $conn = $this->db->connect();
        try {
            $conn->beginTransaction();

            // 1. Bloquear y validar estado actual
            $query = $conn->prepare("SELECT estado FROM valorizacion WHERE id = :id FOR UPDATE");
            $query->bindParam(':id', $data['id_valorizacion']);
            $query->execute();
            $estadoActual = $query->fetchColumn();

            if ($estadoActual === false) {
                $conn->rollBack();
                return array('success' => false, 'message' => 'La valorización no existe');
            }

            if (!in_array($estadoActual, ['BORRADOR', 'ABIERTA'])) {
                $conn->rollBack();
                return array('success' => false, 'message' => 'No se puede editar una valorización ' . $estadoActual);
            }

            // 2. Reemplazar el detalle completo
            $stmtDelete = $conn->prepare("DELETE FROM valorizacion_detalle WHERE id_valorizacion = :id_valorizacion");
            $stmtDelete->bindParam(':id_valorizacion', $data['id_valorizacion']);
            $stmtDelete->execute();

            $documentos = json_decode($data['documentos'], true);

            if (empty($documentos)) {
                $conn->rollBack();
                return array('success' => false, 'message' => 'Debe haber al menos un documento en la valorización');
            }

            // Evitar fletes repetidos (igual que en add_register)
            $ids_flete = [];
            foreach ($documentos as $doc) {
                if (empty($doc['id_flete'])) {
                    throw new Exception('Se ha recibido un documento sin flete');
                }
                $id_flete = (int) $doc['id_flete'];
                if (in_array($id_flete, $ids_flete)) {
                    throw new Exception('No se puede agregar el mismo flete más de una vez');
                }
                $ids_flete[] = $id_flete;
            }

            // 3. Traer subtotal/igv REALES de cada flete (nunca recalcular desde precio del front)
            $stmtFlete = $conn->prepare("
            SELECT id_flete, subtotal, igv
            FROM flete
            WHERE id_flete = :id_flete
            LIMIT 1
        ");

            $stmtDetalle = $conn->prepare("INSERT INTO valorizacion_detalle (
            id_valorizacion, id_flete, importe, observacion
        ) VALUES (:id_valorizacion, :id_flete, :importe, :observacion)");

            $subtotal = 0;
            $igv = 0;
            $total = 0;

            foreach ($documentos as $doc) {
                $id_flete = (int) $doc['id_flete'];

                $stmtFlete->bindValue(':id_flete', $id_flete, PDO::PARAM_INT);
                $stmtFlete->execute();
                $flete = $stmtFlete->fetch(PDO::FETCH_ASSOC);

                if (!$flete) {
                    throw new Exception('No se encontró el flete con ID: ' . $id_flete);
                }

                $fleteSubtotal = (float) $flete['subtotal'];
                $fleteIgv = (float) $flete['igv'];
                $fleteTotal = $fleteSubtotal + $fleteIgv;

                $subtotal += $fleteSubtotal;
                $igv += $fleteIgv;
                $total += $fleteTotal;

                $stmtDetalle->bindValue(':id_valorizacion', $data['id_valorizacion'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':id_flete', $id_flete, PDO::PARAM_INT);
                $stmtDetalle->bindValue(':importe', $fleteTotal);
                $stmtDetalle->bindValue(':observacion', $doc['observacion'] ?? null);
                $stmtDetalle->execute();
            }

            $subtotal = round($subtotal, 2);
            $igv = round($igv, 2);
            $total = round($total, 2);

            // 4. Actualizar cabecera
            $stmtUpdate = $conn->prepare("UPDATE valorizacion SET
        observacion = :observacion,
        subtotal = :subtotal,
        igv = :igv,
        total = :total,
        updated_by = :usuario,
        updated_at = NOW()
        WHERE id = :id_valorizacion
        ");
            $stmtUpdate->bindValue(':observacion', $data['observacion'] ?? null);
            $stmtUpdate->bindParam(':subtotal', $subtotal);
            $stmtUpdate->bindParam(':igv', $igv);
            $stmtUpdate->bindParam(':total', $total);
            $stmtUpdate->bindParam(':usuario', $this->id_usuario_sesion);
            $stmtUpdate->bindParam(':id_valorizacion', $data['id_valorizacion']);
            $stmtUpdate->execute();

            $conn->commit();
            return array('success' => true, 'message' => 'Valorización actualizada con éxito');
        } catch (PDOException $e) {
            $conn->rollBack();
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, 'message' => 'Uno de los documentos ya pertenece a otra valorización');
                default:
                    return array('success' => false, 'message' => 'Ha ocurrido un error, inténtalo más tarde.');
            }
        } catch (Exception $e) {
            $conn->rollBack();
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM valorizacion WHERE id=:id_valorizacion");
            $query->bindParam(":id_valorizacion", $data["id_valorizacion"]);
            $query->execute();
            return ['success' => true, "message" => "Registro eliminado con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "El registro se encuentra protegido"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
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

    public function get_guiasxpagar($data)
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
        WHERE v.id_cliente = :id_cliente AND id_tp_comprobante = 31  
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

    public function get_valorizacion($data)
    {
        try {

            $conn = $this->db->connect();

            // =========================================================
            // 1. VALIDAR ID
            // =========================================================

            $idValorizacion = (int) ($data['id_valorizacion'] ?? 0);

            if ($idValorizacion <= 0) {
                throw new Exception(
                    'No se ha especificado una valorización válida.'
                );
            }

            // =========================================================
            // 2. OBTENER CABECERA
            // =========================================================

            $query = $conn->prepare("
            SELECT
                v.id,
                v.id_empresa,
                v.id_cliente,
                v.id_tp_moneda,

                v.id_serie,
                v.serie,
                v.correlativo,

                v.fecha,
                v.observacion,

                v.subtotal,
                v.igv,
                v.total,

                v.estado,
                v.id_comprobante,

                v.created_by,
                v.updated_by,
                v.closed_by,
                v.fecha_cierre,

                tm.codigo AS codigo_moneda,
                tm.simbolo AS simbolo_moneda,

                CONCAT(
                    COALESCE(c.nombres, ''),
                    ' ',
                    COALESCE(c.apellidos, '')
                ) AS cliente

            FROM valorizacion v

            INNER JOIN usuario c
                ON c.id_usuario = v.id_cliente

            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = v.id_tp_moneda

            WHERE v.id = :id_valorizacion

            LIMIT 1
        ");

            $query->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $query->execute();

            $valorizacion = $query->fetch(PDO::FETCH_ASSOC);

            if (!$valorizacion) {
                throw new Exception(
                    'No se encontró la valorización solicitada.'
                );
            }

            // =========================================================
            // 3. OBTENER FLETES DE LA VALORIZACIÓN
            // =========================================================

            $queryDetalle = $conn->prepare("
            SELECT
                vd.id AS id_valorizacion_detalle,
                vd.id_valorizacion,
                vd.id_flete,
                vd.importe,
                vd.observacion AS observacion_detalle,

                f.serie AS serie_flete,
                f.id_cliente,
                f.id_tp_moneda,

                f.ubigeo_origen,
                f.direccion_origen,
                u_ori.distri AS origen,

                f.ubigeo_destino,
                f.direccion_destino,
                u_desti.distri AS destino,

                f.fecha_salida,
                f.hora_salida,

                f.tipo_operacion,
                f.estado AS estado_flete,

                f.subtotal,
                f.igv,
                f.total,

                tm.codigo AS codigo_moneda,
                tm.simbolo AS simbolo_moneda,

                CASE
                    WHEN f.tipo_operacion = 'TERCERIZADO'
                        THEN vt.placa
                    ELSE vp.placa
                END AS vehiculo

            FROM valorizacion_detalle vd

            INNER JOIN flete f
                ON f.id_flete = vd.id_flete

            LEFT JOIN ubigeo u_ori
                ON u_ori.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo u_desti
                ON u_desti.cod_ubigeo = f.ubigeo_destino

            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = f.id_tp_moneda

            /* Vehículo propio */
            LEFT JOIN vehiculo vp
                ON vp.id_vehiculo = f.id_vehiculo

            /* Vehículo tercerizado */
            LEFT JOIN flete_tercerizado ft
                ON ft.id_flete = f.id_flete
                AND ft.estado <> 'CANCELADO'

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo = ft.id_vehiculo

            WHERE vd.id_valorizacion = :id_valorizacion

            ORDER BY
                f.fecha_salida ASC,
                f.id_flete ASC
        ");

            $queryDetalle->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $queryDetalle->execute();

            $detalle = $queryDetalle->fetchAll(PDO::FETCH_ASSOC);

            // =========================================================
            // 4. FORMATEAR FLETES
            // =========================================================

            foreach ($detalle as &$flete) {

                // Número interno
                $flete['numero_flete'] =
                    !empty($flete['serie_flete'])
                    ? sprintf(
                        '%s-%08d',
                        $flete['serie_flete'],
                        $flete['id_flete']
                    )
                    : sprintf(
                        '%08d',
                        $flete['id_flete']
                    );

                // Importes numéricos
                $flete['subtotal'] =
                    round((float) $flete['subtotal'], 2);

                $flete['igv'] =
                    round((float) $flete['igv'], 2);

                $flete['total'] =
                    round((float) $flete['total'], 2);

                $flete['importe'] =
                    round((float) $flete['importe'], 2);

                /*
             * Compatibilidad temporal con tu JS actual.
             *
             * Actualmente agregarDocumento() todavía espera:
             * doc.precio
             *
             * Después lo cambiaremos definitivamente por total.
             */
                $flete['precio'] =
                    $flete['total'];

                /*
             * Evitamos NULL visual.
             */
                $flete['vehiculo'] =
                    !empty($flete['vehiculo'])
                    ? $flete['vehiculo']
                    : '-';

                $flete['origen'] =
                    !empty($flete['origen'])
                    ? $flete['origen']
                    : $flete['direccion_origen'];

                $flete['destino'] =
                    !empty($flete['destino'])
                    ? $flete['destino']
                    : $flete['direccion_destino'];
            }

            unset($flete);

            // =========================================================
            // 5. RESPUESTA
            // =========================================================

            return [
                'success' => true,
                'message' => [
                    'cabecera' => $valorizacion,
                    'detalle' => $detalle
                ]
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    public function getDataReporteValorizacion($id_valorizacion)
    {
        try {

            $conn = $this->db->connect();

            $id_valorizacion = (int) $id_valorizacion;

            if ($id_valorizacion <= 0) {
                return [
                    'success' => false,
                    'message' => [
                        'message' => 'El ID de la valorización no es válido.'
                    ]
                ];
            }

            /*=====================================================
        =            DATOS DE LA EMPRESA EMISORA              =
        =====================================================*/

            $query = $conn->prepare("
            SELECT
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
                e.guia_clave AS api_clave,
                e.nro_cuenta_BN AS cuenta_detraccion,
                e.logo
            FROM terminal t
            LEFT JOIN empresa e
                ON e.id_empresa = t.id_empresa
            LEFT JOIN ubigeo ub
                ON ub.cod_ubigeo = e.ubigeo
            LEFT JOIN tp_docu td
                ON e.tp_docu = td.descripcion
            WHERE t.id_terminal = :id_terminal
            LIMIT 1
        ");

            $query->bindValue(
                ":id_terminal",
                $this->id_terminal_sesion,
                PDO::PARAM_INT
            );

            $query->execute();

            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                return [
                    'success' => false,
                    'message' => [
                        'message' => 'No se encontraron los datos de la empresa.'
                    ]
                ];
            }

            $empresa['pais'] = "PE";


            /*=====================================================
        =            CABECERA DE LA VALORIZACIÓN              =
        =====================================================*/

            $query = $conn->prepare("
            SELECT
                v.id,
                v.id_empresa,
                v.id_cliente,
                v.id_tp_moneda,

                v.fecha,
                v.observacion,

                v.subtotal,
                v.igv,
                v.total,

                v.estado,

                v.id_comprobante,

                v.fecha_cierre,
                v.created_at,
                v.updated_at,

                s.id_serie,
                s.serie,

                tm.codigo AS codigo_moneda,
                tm.descripcion AS moneda,
                tm.simbolo AS simbolo_moneda,

                u.id_usuario AS id_cliente,
                u.num_docu,
                CONCAT(
                    COALESCE(u.nombres, ''),
                    ' ',
                    COALESCE(u.apellidos, '')
                ) AS cliente,

                u.direccion AS direccion_cliente,

                ven.serie AS serie_comprobante,
                ven.correlativo AS correlativo_comprobante,
                ven.fecha_emision AS fecha_emision_comprobante,
                ven.envio_sunat

            FROM valorizacion v

            INNER JOIN serie s
                ON s.id_serie = v.id_serie

            INNER JOIN usuario u
                ON u.id_usuario = v.id_cliente

            LEFT JOIN tp_moneda tm
                ON tm.id_tp_moneda = v.id_tp_moneda

            LEFT JOIN venta ven
                ON ven.id_venta = v.id_comprobante

            WHERE v.id = :id_valorizacion

            LIMIT 1
        ");

            $query->bindValue(
                ":id_valorizacion",
                $id_valorizacion,
                PDO::PARAM_INT
            );

            $query->execute();

            $cabecera = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                return [
                    'success' => false,
                    'message' => [
                        'message' => 'No se encontró la valorización.'
                    ]
                ];
            }


            /*=====================================================
        =          NÚMERO FORMATEADO VALORIZACIÓN             =
        =====================================================*/

            $cabecera['numero_valorizacion'] =
                $cabecera['serie'] . '-' .
                str_pad(
                    $cabecera['id'],
                    8,
                    '0',
                    STR_PAD_LEFT
                );


            /*=====================================================
        =          NÚMERO DEL COMPROBANTE SI EXISTE           =
        =====================================================*/

            $cabecera['numero_comprobante'] = null;

            if (
                !empty($cabecera['serie_comprobante']) &&
                !empty($cabecera['correlativo_comprobante'])
            ) {

                $cabecera['numero_comprobante'] =
                    $cabecera['serie_comprobante'] . '-' .
                    str_pad(
                        $cabecera['correlativo_comprobante'],
                        8,
                        '0',
                        STR_PAD_LEFT
                    );
            }


            /*=====================================================
        =                 DETALLE DE FLETES                    =
        =====================================================*/

            $query = $conn->prepare("
            SELECT
                vd.id AS id_valorizacion_detalle,
                vd.id_flete,

                /* Importe congelado en la valorización */
                vd.importe,
                vd.observacion AS observacion_detalle,

                /* Datos del flete */
                f.serie AS serie_flete,

                f.tipo_operacion,

                f.fecha_salida,
                f.hora_salida,

                f.ubigeo_origen,
                f.direccion_origen,

                f.ubigeo_destino,
                f.direccion_destino,

                u_ori.distri AS origen,
                u_desti.distri AS destino,

                f.subtotal AS subtotal_flete,
                f.igv AS igv_flete,
                f.total AS total_flete,

                f.incluye_igv,

                /* Vehículo propio */
                vp.placa AS vehiculo_propio,

                /* Conductor propio */
                CONCAT(
                    COALESCE(cp.nombres, ''),
                    ' ',
                    COALESCE(cp.apellidos, '')
                ) AS conductor_propio,

                /* Datos tercerizados */
                ft.id_flete_tercerizado,
                ft.id_proveedor,
                ft.costo AS costo_tercerizado,
                ft.estado AS estado_tercerizado,

                vt.placa AS vehiculo_tercerizado,

                CONCAT(
                    COALESCE(ct.nombres, ''),
                    ' ',
                    COALESCE(ct.apellidos, '')
                ) AS conductor_tercerizado,

                /* Datos finales para impresión */
                CASE
                    WHEN f.tipo_operacion = 'TERCERIZADO'
                    THEN vt.placa
                    ELSE vp.placa
                END AS vehiculo,

                CASE
                    WHEN f.tipo_operacion = 'TERCERIZADO'
                    THEN TRIM(
                        CONCAT(
                            COALESCE(ct.nombres, ''),
                            ' ',
                            COALESCE(ct.apellidos, '')
                        )
                    )
                    ELSE TRIM(
                        CONCAT(
                            COALESCE(cp.nombres, ''),
                            ' ',
                            COALESCE(cp.apellidos, '')
                        )
                    )
                END AS conductor

            FROM valorizacion_detalle vd

            INNER JOIN flete f
                ON f.id_flete = vd.id_flete

            LEFT JOIN ubigeo u_ori
                ON u_ori.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo u_desti
                ON u_desti.cod_ubigeo = f.ubigeo_destino

            /* PROPIO */

            LEFT JOIN vehiculo vp
                ON vp.id_vehiculo = f.id_vehiculo

            LEFT JOIN usuario cp
                ON cp.id_usuario = f.id_conductor

            /* TERCERIZADO */

            LEFT JOIN flete_tercerizado ft
                ON ft.id_flete = f.id_flete
                AND ft.estado <> 'CANCELADO'

            LEFT JOIN vehiculo vt
                ON vt.id_vehiculo = ft.id_vehiculo

            LEFT JOIN usuario ct
                ON ct.id_usuario = ft.id_conductor

            WHERE vd.id_valorizacion = :id_valorizacion

            ORDER BY
                f.fecha_salida ASC,
                f.hora_salida ASC,
                vd.id ASC
        ");

            $query->bindValue(
                ":id_valorizacion",
                $id_valorizacion,
                PDO::PARAM_INT
            );

            $query->execute();

            $detalle = $query->fetchAll(PDO::FETCH_ASSOC);


            /*=====================================================
        =             FORMATEAR NÚMERO DE FLETE                =
        =====================================================*/

            foreach ($detalle as &$item) {

                $serieFlete = !empty($item['serie_flete'])
                    ? $item['serie_flete']
                    : null;

                $idFlete = (int) $item['id_flete'];

                $item['numero_flete'] = $serieFlete
                    ? sprintf(
                        '%s-%08d',
                        $serieFlete,
                        $idFlete
                    )
                    : sprintf(
                        '%08d',
                        $idFlete
                    );

                /*
             * IMPORTANTE:
             * Para la impresión de la valorización usamos
             * vd.importe, porque ese es el importe que quedó
             * registrado dentro de la valorización.
             */
                $item['precio'] = (float) $item['importe'];

                /*
             * Evitamos campos vacíos en el PDF.
             */
                $item['vehiculo'] =
                    !empty($item['vehiculo'])
                    ? $item['vehiculo']
                    : '-';

                $item['conductor'] =
                    !empty($item['conductor'])
                    ? $item['conductor']
                    : '-';
            }

            unset($item);


            /*=====================================================
        =                    RESPUESTA                         =
        =====================================================*/

            return [
                'success' => true,

                'message' =>
                'Datos obtenidos correctamente.',

                'EMPRESA' => $empresa,

                'CABECERA' => $cabecera,

                'BODY' => $detalle
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,

                'message' => [
                    'message' =>
                    'Error en la base de datos: ' .
                        $e->getMessage(),

                    'code' => $e->getCode()
                ]
            ];
        }
    }

    public function cerrar_valorizacion($data)
    {
        $conn = $this->db->connect();

        try {

            $conn->beginTransaction();

            // =========================================================
            // 1. VALIDAR ID
            // =========================================================

            $idValorizacion = (int) ($data['id_valorizacion'] ?? 0);

            if ($idValorizacion <= 0) {
                throw new Exception(
                    'No se ha especificado una valorización válida.'
                );
            }

            // =========================================================
            // 2. BLOQUEAR CABECERA
            // =========================================================

            $stmtValorizacion = $conn->prepare("
            SELECT
                id,
                id_cliente,
                id_tp_moneda,
                subtotal,
                igv,
                total,
                estado,
                id_comprobante
            FROM valorizacion
            WHERE id = :id_valorizacion
            LIMIT 1
            FOR UPDATE
        ");

            $stmtValorizacion->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $stmtValorizacion->execute();

            $valorizacion = $stmtValorizacion->fetch(PDO::FETCH_ASSOC);

            if (!$valorizacion) {
                throw new Exception(
                    'No se encontró la valorización.'
                );
            }

            // =========================================================
            // 3. VALIDAR ESTADO
            // =========================================================

            if (
                !in_array(
                    $valorizacion['estado'],
                    ['BORRADOR', 'ABIERTA'],
                    true
                )
            ) {
                throw new Exception(
                    'La valorización no se encuentra en un estado válido para cerrar.'
                );
            }

            if (!empty($valorizacion['id_comprobante'])) {
                throw new Exception(
                    'La valorización ya tiene un comprobante asociado.'
                );
            }

            // =========================================================
            // 4. OBTENER Y BLOQUEAR FLETES
            // =========================================================

            $stmtFletes = $conn->prepare("
            SELECT
                vd.id,
                vd.id_flete,
                vd.importe,

                f.id_cliente,
                f.id_tp_moneda,
                f.subtotal,
                f.igv,
                f.total,
                f.estado,

                fv.id_flete_venta

            FROM valorizacion_detalle vd

            INNER JOIN flete f
                ON f.id_flete = vd.id_flete

            LEFT JOIN flete_venta fv
                ON fv.id_flete = f.id_flete

            WHERE vd.id_valorizacion = :id_valorizacion

            FOR UPDATE
        ");

            $stmtFletes->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $stmtFletes->execute();

            $fletes = $stmtFletes->fetchAll(PDO::FETCH_ASSOC);

            if (empty($fletes)) {
                throw new Exception(
                    'La valorización no contiene fletes.'
                );
            }

            // =========================================================
            // 5. REVALIDAR TODOS LOS FLETES
            // =========================================================

            $subtotal = 0;
            $igv      = 0;
            $total    = 0;

            foreach ($fletes as $flete) {

                $idFlete = (int) $flete['id_flete'];

                // -----------------------------------------------------
                // Cliente
                // -----------------------------------------------------

                if (
                    (int) $flete['id_cliente'] !==
                    (int) $valorizacion['id_cliente']
                ) {
                    throw new Exception(
                        'El flete ' .
                            $idFlete .
                            ' no pertenece al cliente de la valorización.'
                    );
                }

                // -----------------------------------------------------
                // Moneda
                // -----------------------------------------------------

                if (
                    (int) $flete['id_tp_moneda'] !==
                    (int) $valorizacion['id_tp_moneda']
                ) {
                    throw new Exception(
                        'El flete ' .
                            $idFlete .
                            ' tiene una moneda diferente a la valorización.'
                    );
                }

                // -----------------------------------------------------
                // Estado
                // -----------------------------------------------------

                if ($flete['estado'] !== 'FINALIZADO') {
                    throw new Exception(
                        'El flete ' .
                            $idFlete .
                            ' debe estar FINALIZADO antes de cerrar la valorización.'
                    );
                }

                // -----------------------------------------------------
                // Facturación
                // -----------------------------------------------------

                if (!empty($flete['id_flete_venta'])) {
                    throw new Exception(
                        'El flete ' .
                            $idFlete .
                            ' ya se encuentra facturado.'
                    );
                }

                // -----------------------------------------------------
                // Importes
                // -----------------------------------------------------

                $fleteSubtotal = round(
                    (float) $flete['subtotal'],
                    2
                );

                $fleteIgv = round(
                    (float) $flete['igv'],
                    2
                );

                $fleteTotal = round(
                    (float) $flete['total'],
                    2
                );

                if ($fleteTotal <= 0) {
                    throw new Exception(
                        'El flete ' .
                            $idFlete .
                            ' tiene un importe no válido.'
                    );
                }

                if (
                    abs(
                        ($fleteSubtotal + $fleteIgv) -
                            $fleteTotal
                    ) > 0.02
                ) {
                    throw new Exception(
                        'Los importes del flete ' .
                            $idFlete .
                            ' no son consistentes.'
                    );
                }

                /*
             * También verificamos el snapshot guardado
             * en valorizacion_detalle.
             */
                $importeValorizado = round(
                    (float) $flete['importe'],
                    2
                );

                if (
                    abs(
                        $importeValorizado -
                            $fleteTotal
                    ) > 0.02
                ) {
                    throw new Exception(
                        'El importe valorizado del flete ' .
                            $idFlete .
                            ' no coincide con su total actual.'
                    );
                }

                $subtotal += $fleteSubtotal;
                $igv      += $fleteIgv;
                $total    += $fleteTotal;
            }

            // =========================================================
            // 6. RECALCULAR TOTALES
            // =========================================================

            $subtotal = round($subtotal, 2);
            $igv      = round($igv, 2);
            $total    = round($total, 2);

            if ($total <= 0) {
                throw new Exception(
                    'El total de la valorización debe ser mayor a cero.'
                );
            }

            // =========================================================
            // 7. ACTUALIZAR Y CERRAR
            // =========================================================

            $stmtCerrar = $conn->prepare("
            UPDATE valorizacion
            SET
                subtotal = :subtotal,
                igv = :igv,
                total = :total,

                estado = 'CERRADA',

                closed_by = :closed_by,
                fecha_cierre = NOW(),

                updated_by = :updated_by
            WHERE id = :id_valorizacion
        ");

            $stmtCerrar->bindValue(
                ':subtotal',
                $subtotal
            );

            $stmtCerrar->bindValue(
                ':igv',
                $igv
            );

            $stmtCerrar->bindValue(
                ':total',
                $total
            );

            $stmtCerrar->bindValue(
                ':closed_by',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $stmtCerrar->bindValue(
                ':updated_by',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $stmtCerrar->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $stmtCerrar->execute();

            // =========================================================
            // 8. HISTORIAL
            // =========================================================

            $stmtHistorial = $conn->prepare("
            INSERT INTO valorizacion_historial (
                id_valorizacion,
                estado,
                observacion,
                usuario
            )
            VALUES (
                :id_valorizacion,
                'CERRADA',
                'Cierre de valorización',
                :usuario
            )
        ");

            $stmtHistorial->bindValue(
                ':id_valorizacion',
                $idValorizacion,
                PDO::PARAM_INT
            );

            $stmtHistorial->bindValue(
                ':usuario',
                $this->id_usuario_sesion,
                PDO::PARAM_INT
            );

            $stmtHistorial->execute();

            // =========================================================
            // 9. CONFIRMAR
            // =========================================================

            $conn->commit();

            return [
                'success' => true,
                'message' => 'Valorización cerrada correctamente.',
                'id_valorizacion' => $idValorizacion
            ];
        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function pagar_documentos($data)
    {
        $conn = null;

        try {

            $conn = $this->db->connect();
            $conn->beginTransaction();

            // =========================================================
            // 1. VALORIZACIÓN
            // =========================================================

            $idValorizacion = (int) (
                $data['id_valorizacion_pagar']
                ?? $data['id_valorizacion']
                ?? 0
            );

            if ($idValorizacion <= 0) {
                throw new Exception(
                    'La valorización seleccionada no es válida.'
                );
            }

            // =========================================================
            // 2. BLOQUEAR Y OBTENER VALORIZACIÓN
            // =========================================================

            $query = $conn->prepare("
            SELECT
                v.id,
                v.id_cliente,
                v.id_tp_moneda,
                v.subtotal,
                v.igv,
                v.total,
                v.estado,
                v.id_comprobante,
                v.observacion
            FROM valorizacion v
            WHERE v.id = :id_valorizacion
            LIMIT 1
            FOR UPDATE
        ");

            $query->execute([
                ':id_valorizacion' => $idValorizacion
            ]);

            $valorizacion = $query->fetch(PDO::FETCH_ASSOC);

            if (!$valorizacion) {
                throw new Exception(
                    'La valorización no existe.'
                );
            }

            // =========================================================
            // 3. VALIDAR ESTADO
            // =========================================================

            if ($valorizacion['estado'] !== 'CERRADA') {
                throw new Exception(
                    'Solo se pueden facturar valorizaciones cerradas.'
                );
            }

            if (!empty($valorizacion['id_comprobante'])) {
                throw new Exception(
                    'La valorización ya tiene un comprobante asociado.'
                );
            }

            if ((int) $valorizacion['id_cliente'] <= 0) {
                throw new Exception(
                    'La valorización no tiene un cliente válido.'
                );
            }

            if ((int) $valorizacion['id_tp_moneda'] <= 0) {
                throw new Exception(
                    'La valorización no tiene una moneda válida.'
                );
            }

            // =========================================================
            // 4. OBTENER FLETES DE LA VALORIZACIÓN
            // =========================================================

            $query = $conn->prepare("
            SELECT
                vd.id_flete,
                vd.importe,

                f.serie,
                f.id_cliente,
                f.id_tp_moneda,

                f.subtotal,
                f.igv,
                f.total,

                f.estado,

                f.ubigeo_origen,
                f.direccion_origen,

                f.ubigeo_destino,
                f.direccion_destino,

                uo.distri AS origen,
                ud.distri AS destino

            FROM valorizacion_detalle vd

            INNER JOIN flete f
                ON f.id_flete = vd.id_flete

            LEFT JOIN ubigeo uo
                ON uo.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo ud
                ON ud.cod_ubigeo = f.ubigeo_destino

            WHERE vd.id_valorizacion = :id_valorizacion

            ORDER BY vd.id ASC

            FOR UPDATE
        ");

            $query->execute([
                ':id_valorizacion' => $idValorizacion
            ]);

            $fletes = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($fletes)) {
                throw new Exception(
                    'La valorización no contiene fletes.'
                );
            }

            // =========================================================
            // 5. VALIDAR FLETES Y RECALCULAR TOTALES
            // =========================================================

            $subtotal = 0;
            $igv      = 0;
            $total    = 0;

            /*
         * Lo preparamos una sola vez para no hacer
         * prepare() dentro del foreach.
         */
            $stmtVenta = $conn->prepare("
            SELECT
                id_flete_venta,
                id_venta
            FROM flete_venta
            WHERE id_flete = :id_flete
            LIMIT 1
        ");

            $stmtDetalle = $conn->prepare("
            SELECT
                COUNT(*) AS cantidad,
                ROUND(SUM(importe_total), 2) AS total_detalle
            FROM flete_detalle
            WHERE id_flete = :id_flete
        ");

            foreach ($fletes as $flete) {

                $idFlete = (int) $flete['id_flete'];

                // -----------------------------------------------------
                // Estado
                // -----------------------------------------------------

                if ($flete['estado'] !== 'FINALIZADO') {

                    throw new Exception(
                        "El flete {$idFlete} no está FINALIZADO."
                    );
                }

                // -----------------------------------------------------
                // Cliente
                // -----------------------------------------------------

                if (
                    (int) $flete['id_cliente'] !==
                    (int) $valorizacion['id_cliente']
                ) {

                    throw new Exception(
                        "El flete {$idFlete} pertenece a otro cliente."
                    );
                }

                // -----------------------------------------------------
                // Moneda
                // -----------------------------------------------------

                if (
                    (int) $flete['id_tp_moneda'] !==
                    (int) $valorizacion['id_tp_moneda']
                ) {

                    throw new Exception(
                        "El flete {$idFlete} tiene una moneda diferente."
                    );
                }

                // -----------------------------------------------------
                // Evitar doble facturación
                // -----------------------------------------------------

                $stmtVenta->execute([
                    ':id_flete' => $idFlete
                ]);

                if ($stmtVenta->fetch(PDO::FETCH_ASSOC)) {

                    throw new Exception(
                        "El flete {$idFlete} ya fue facturado."
                    );
                }

                // -----------------------------------------------------
                // Validar detalle comercial
                // -----------------------------------------------------

                $stmtDetalle->execute([
                    ':id_flete' => $idFlete
                ]);

                $resumen = $stmtDetalle->fetch(PDO::FETCH_ASSOC);

                if (
                    !$resumen ||
                    (int) $resumen['cantidad'] <= 0
                ) {

                    throw new Exception(
                        "El flete {$idFlete} no tiene detalle comercial."
                    );
                }

                // -----------------------------------------------------
                // Importes
                // -----------------------------------------------------

                $fleteSubtotal = round(
                    (float) $flete['subtotal'],
                    2
                );

                $fleteIgv = round(
                    (float) $flete['igv'],
                    2
                );

                $totalFlete = round(
                    (float) $flete['total'],
                    2
                );

                $totalDetalle = round(
                    (float) $resumen['total_detalle'],
                    2
                );

                if ($totalFlete <= 0) {

                    throw new Exception(
                        "El flete {$idFlete} tiene un total inválido."
                    );
                }

                // -----------------------------------------------------
                // Flete vs flete_detalle
                // -----------------------------------------------------

                if (
                    abs(
                        $totalFlete -
                            $totalDetalle
                    ) > 0.02
                ) {

                    throw new Exception(
                        "El detalle comercial del flete {$idFlete} no coincide con su total."
                    );
                }

                // -----------------------------------------------------
                // Snapshot de la valorización
                // -----------------------------------------------------

                $importeValorizado = round(
                    (float) $flete['importe'],
                    2
                );

                if (
                    abs(
                        $importeValorizado -
                            $totalFlete
                    ) > 0.02
                ) {

                    throw new Exception(
                        "El importe valorizado del flete {$idFlete} no coincide con su total."
                    );
                }

                // -----------------------------------------------------
                // Acumular
                // -----------------------------------------------------

                $subtotal += $fleteSubtotal;
                $igv      += $fleteIgv;
                $total    += $totalFlete;
            }

            $subtotal = round($subtotal, 2);
            $igv      = round($igv, 2);
            $total    = round($total, 2);

            // =========================================================
            // 6. VALIDAR TOTALES CONTRA VALORIZACIÓN
            // =========================================================

            if (
                abs(
                    $subtotal -
                        round((float) $valorizacion['subtotal'], 2)
                ) > 0.02
            ) {

                throw new Exception(
                    'El subtotal actual de los fletes no coincide con la valorización.'
                );
            }

            if (
                abs(
                    $igv -
                        round((float) $valorizacion['igv'], 2)
                ) > 0.02
            ) {

                throw new Exception(
                    'El IGV actual de los fletes no coincide con la valorización.'
                );
            }

            if (
                abs(
                    $total -
                        round((float) $valorizacion['total'], 2)
                ) > 0.02
            ) {

                throw new Exception(
                    'El total actual de los fletes no coincide con la valorización.'
                );
            }

            // =========================================================
            // 7. PREPARAR DATOS DEL COMPROBANTE
            // =========================================================

            /*
         * 6 = Venta generada desde Valorización.
         *
         * Aunque crear_registro_venta_valorizacion()
         * ya lo fija internamente, lo dejamos también
         * explícito aquí.
         */
            $data['id_tp_venta'] = 6;

            $data['id_cliente'] =
                (int) $valorizacion['id_cliente'];

            $data['id_tp_moneda'] =
                (int) $valorizacion['id_tp_moneda'];

            $data['subtotal'] = $subtotal;
            $data['igv']      = $igv;
            $data['total']    = $total;

            $data['id_tp_operacion'] = 1;
            $data['afecta_detraccion'] = 0;

            /*
         * Usamos los totales que acabamos de recalcular.
         *
         * Así crear_registro_venta_valorizacion()
         * recibe una cabecera confiable.
         */
            $valorizacion['subtotal'] = $subtotal;
            $valorizacion['igv']      = $igv;
            $valorizacion['total']    = $total;

            // =========================================================
            // 8. CREAR UNA SOLA VENTA
            // =========================================================

            $venta =
                $this->crear_registro_venta_valorizacion(
                    $valorizacion,
                    $data,
                    $conn
                );

            if (
                empty($venta['success']) ||
                !$venta['success']
            ) {

                throw new Exception(
                    $venta['message']
                        ?? 'No se pudo crear el comprobante.'
                );
            }

            $idVenta = (int) (
                $venta['id_venta']
                ?? 0
            );

            if ($idVenta <= 0) {

                throw new Exception(
                    'No se pudo obtener el ID del comprobante.'
                );
            }

            // =========================================================
            // 9. REGISTRAR DETALLE DEL COMPROBANTE
            // =========================================================

            /*
         * Este método obtiene TODOS los flete_detalle
         * pertenecientes a la valorización y genera:
         *
         * item 1
         * item 2
         * item 3
         * ...
         */

            $resultadoDetalle =
                $this->registrar_detalle_comprobante_valorizacion(
                    $idValorizacion,
                    $idVenta,
                    $conn
                );

            if (
                empty($resultadoDetalle['success']) ||
                !$resultadoDetalle['success']
            ) {

                throw new Exception(
                    $resultadoDetalle['message']
                        ?? 'No se pudo registrar el detalle del comprobante.'
                );
            }

            // =========================================================
            // 10. REGISTRAR CUOTA SI ES CRÉDITO
            // =========================================================

            if (
                (int) ($data['forma_pago'] ?? 0) === 2
            ) {

                /*
             * Por ahora reutilizamos el método que ya tienes
             * funcionando para Flete.
             *
             * Después podemos duplicarlo como
             * registrar_cuota_valorizacion() si deseas
             * separar completamente ambos módulos.
             */

                $cuota =
                    $this->registrar_cuota_flete(
                        $idVenta,
                        $total,
                        $data,
                        $conn
                    );

                if (
                    empty($cuota['success']) ||
                    !$cuota['success']
                ) {

                    throw new Exception(
                        $cuota['message']
                            ?? 'No se pudo registrar la cuota.'
                    );
                }
            }

            // =========================================================
            // 11. RELACIONAR TODOS LOS FLETES CON LA VENTA
            // =========================================================

            $relacion =
                $this->registrar_fletes_venta_valorizacion(
                    $idValorizacion,
                    $idVenta,
                    $conn
                );

            if (
                empty($relacion['success']) ||
                !$relacion['success']
            ) {

                throw new Exception(
                    $relacion['message']
                        ?? 'No se pudieron asociar los fletes al comprobante.'
                );
            }

            // =========================================================
            // 12. MARCAR VALORIZACIÓN COMO FACTURADA
            // =========================================================

            $query = $conn->prepare("
            UPDATE valorizacion
            SET
                estado = 'FACTURADA',
                id_comprobante = :id_comprobante,
                updated_by = :usuario,
                updated_at = NOW()
            WHERE id = :id_valorizacion
              AND estado = 'CERRADA'
              AND id_comprobante IS NULL
        ");

            $query->execute([
                ':id_comprobante' =>
                $idVenta,

                ':usuario' =>
                $this->id_usuario_sesion,

                ':id_valorizacion' =>
                $idValorizacion
            ]);

            if ($query->rowCount() !== 1) {

                throw new Exception(
                    'No se pudo marcar la valorización como facturada.'
                );
            }

            // =========================================================
            // 13. HISTORIAL
            // =========================================================

            $query = $conn->prepare("
            INSERT INTO valorizacion_historial (
                id_valorizacion,
                estado,
                observacion,
                usuario
            )
            VALUES (
                :id_valorizacion,
                'FACTURADA',
                :observacion,
                :usuario
            )
        ");

            $query->execute([

                ':id_valorizacion' =>
                $idValorizacion,

                ':observacion' =>
                'Comprobante generado desde valorización. Venta ID: ' .
                    $idVenta,

                ':usuario' =>
                $this->id_usuario_sesion
            ]);

            // =========================================================
            // 14. COMMIT
            // =========================================================

            $conn->commit();

            // =========================================================
            // 15. RESPUESTA
            // =========================================================

            /*
         * AQUÍ TERMINA LA TRANSACCIÓN.
         *
         * NO:
         * - get_data_comprobante_sunat()
         * - enviar_json_a_api()
         * - actualizar_venta_sunat()
         *
         * Eso ocurre posteriormente desde la vista previa.
         */

            return [

                'success' =>
                true,

                'message' =>
                'Comprobante generado correctamente. Revise la vista previa antes de enviarlo a SUNAT.',

                'id_venta' =>
                $idVenta,

                'id_comprobante' =>
                $idVenta,

                'id_valorizacion' =>
                $idValorizacion,

                'serie' =>
                $venta['serie'] ?? null,

                'correlativo' =>
                $venta['correlativo'] ?? null,

                'cantidad_fletes' =>
                count($fletes),

                'cantidad_items' =>
                (int) (
                    $resultadoDetalle['cantidad_items']
                    ?? 0
                ),

                'pendiente_sunat' =>
                true
            ];
        } catch (Throwable $e) {

            if (
                $conn !== null &&
                $conn->inTransaction()
            ) {
                $conn->rollBack();
            }

            /*
         * Mientras estamos desarrollando dejamos el mensaje
         * real para detectar cualquier problema rápidamente.
         */
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function crear_servicio($data, $conn = null)
    {
        try {
            if ($conn === null) {
                $conn = $this->db->connect();
            }

            $tipo = '';
            $codigo_interno_temp = '';
            $factor_icbper = 0.00;
            $descripcion = '';
            $precio_unitario = 0.00;
            $afectacion_prod = 1;
            $estado_prod = 1;
            $unidad_medida = 10;
            $codigo_sunat = '';
            $afecto_icbper = 0;

            $query = $conn->prepare("
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
            $query->bindParam(':descripcion', $descripcion);
            $query->bindParam(':valor_unitario', $precio_unitario);
            $query->bindParam(':tipo_afectacion', $afectacion_prod);
            $query->bindParam(':unidad_id', $unidad_medida);
            $query->bindParam(':codigo_interno', $codigo_interno_temp);
            $query->bindParam(':codigo_sunat', $codigo_sunat);
            $query->bindParam(':afecto_icbper', $afecto_icbper);
            $query->bindParam(':factor_icbper', $factor_icbper);
            $query->bindParam(':tipo', $tipo);
            $query->bindParam(':estado', $estado_prod);
            $query->execute();

            $id_producto = $conn->lastInsertId();

            $codigo_interno = 'P' . str_pad($id_producto, 4, '0', STR_PAD_LEFT);

            $update = $conn->prepare("
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

    public function registrar_detalle($productos, $conn = null, $id_comprobante)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        try {
            $igv_config = $this->get_igv();
            $item = 0;

            foreach ($productos as $producto) {
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

    public function registrar_flete_venta($idFlete, $idVenta, $conn, $origen = 'DIRECTO', $idValorizacion = null)
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
                :origen_facturacion,
                :id_valorizacion
            )
        ");

            $query->bindValue(
                ':id_flete',
                $idFlete,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':id_venta',
                $idVenta,
                PDO::PARAM_INT
            );

            $query->bindValue(
                ':origen_facturacion',
                $origen
            );

            if ($idValorizacion === null) {
                $query->bindValue(
                    ':id_valorizacion',
                    null,
                    PDO::PARAM_NULL
                );
            } else {
                $query->bindValue(
                    ':id_valorizacion',
                    $idValorizacion,
                    PDO::PARAM_INT
                );
            }

            $query->execute();

            return [
                'success' => true,
                'message' => 'Flete asociado correctamente.'
            ];
        } catch (PDOException $e) {

            return [
                'success' => false,
                'message' => $e->getCode() === '23000'
                    ? 'El flete ya se encuentra asociado a un comprobante.'
                    : $e->getMessage()
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

    private function crear_registro_venta_valorizacion(array $valorizacion, array $data, PDO $conn): array
    {
        try {

            // =====================================================
            // TIPO DE COMPROBANTE
            // =====================================================

            $idTpComprobante = (int) (
                $data["tp_comprobante"]
                ?? $data["tipo_comprobante_venta"]
                ?? 0
            );

            if (!in_array($idTpComprobante, [1, 3], true)) {
                throw new Exception(
                    "Tipo de comprobante inválido."
                );
            }

            // =====================================================
            // SERIE
            // =====================================================

            $idSerie = (int) (
                $data["serie"]
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
                ":id_serie" => $idSerie,
                ":id_tp_comprobante" => $idTpComprobante
            ]);

            $serieData = $query->fetch(PDO::FETCH_ASSOC);

            if (!$serieData) {
                throw new Exception(
                    "La serie seleccionada no corresponde al tipo de comprobante."
                );
            }

            // =====================================================
            // CLIENTE
            // =====================================================

            $idCliente = (int) (
                $valorizacion["id_cliente"]
                ?? 0
            );

            if ($idCliente <= 0) {
                throw new Exception(
                    "La valorización no tiene un cliente válido."
                );
            }

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
                ":id_cliente" => $idCliente
            ]);

            $cliente = $query->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                throw new Exception(
                    "El cliente de la valorización no existe."
                );
            }

            // =====================================================
            // FACTURA -> RUC
            // =====================================================

            if ($idTpComprobante === 1) {

                if (
                    strlen(
                        trim(
                            (string) $cliente["num_docu"]
                        )
                    ) !== 11
                ) {
                    throw new Exception(
                        "Para emitir una factura el cliente debe tener un RUC válido."
                    );
                }
            }

            // =====================================================
            // FECHA DE EMISIÓN
            // =====================================================

            $fechaEmision = trim(
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
                $fechaEmision . " " . date("H:i:s");

            // =====================================================
            // FORMA DE PAGO
            // =====================================================

            $formaPago = (int) (
                $data["forma_pago"]
                ?? 0
            );

            if (!in_array($formaPago, [1, 2], true)) {
                throw new Exception(
                    "Forma de pago inválida."
                );
            }

            // =====================================================
            // MONEDA
            // =====================================================

            $idTpMoneda = (int) (
                $valorizacion["id_tp_moneda"]
                ?? 0
            );

            if ($idTpMoneda <= 0) {
                throw new Exception(
                    "La valorización no tiene una moneda configurada."
                );
            }

            // =====================================================
            // MEDIO DE PAGO
            // =====================================================

            $idMedioPago = null;

            if ($formaPago === 1) {

                $idMedioPago = (int) (
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

                $fechaVencimiento = trim(
                    $data["fecha_credito"]
                        ?? ""
                );

                if (empty($fechaVencimiento)) {
                    throw new Exception(
                        "La fecha de vencimiento del crédito es requerida."
                    );
                }
            } else {

                $fechaVencimiento = $fechaEmision;
            }

            // =====================================================
            // CORRELATIVO DEL COMPROBANTE
            // =====================================================

            $dataCorrelativo = $data;

            $dataCorrelativo["id_serie"] = $idSerie;
            $dataCorrelativo["serie_venta"] = $idSerie;
            $dataCorrelativo["tp_comprobante"] = $idTpComprobante;

            $correlativo = $this->get_correlativo(
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

            $subtotal = round(
                (float) ($valorizacion["subtotal"] ?? 0),
                2
            );

            $igv = round(
                (float) ($valorizacion["igv"] ?? 0),
                2
            );

            $total = round(
                (float) ($valorizacion["total"] ?? 0),
                2
            );

            if ($total <= 0) {
                throw new Exception(
                    "El total de la valorización debe ser mayor a cero."
                );
            }

            if (
                abs(
                    ($subtotal + $igv) - $total
                ) > 0.02
            ) {
                throw new Exception(
                    "Los totales de la valorización no son consistentes."
                );
            }

            // =====================================================
            // CONSTANTES DE VALORIZACIÓN
            // =====================================================

            $idTpOperacion = 1;

            /*
         * 6 = venta originada desde Valorización.
         */
            $idTpVenta = 6;

            $afectaDetraccion = 0;

            // =====================================================
            // ESTADO DE PAGO
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

                ":codigo" => '',

                ":id_terminal" =>
                $serieData["id_terminal"],

                ":id_vendedor" =>
                $this->id_usuario_sesion,

                ":id_cliente" =>
                $idCliente,

                ":id_forma_pago" =>
                $formaPago,

                ":id_medio_pago" =>
                $idMedioPago,

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

                /*
             * MUY IMPORTANTE:
             * todavía NO enviamos a SUNAT.
             */
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
                    (string) (
                        $valorizacion["observacion"]
                        ?? ""
                    )
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

            $idVenta = (int) $conn->lastInsertId();

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
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    private function registrar_detalle_comprobante_valorizacion(
        int $idValorizacion,
        int $idVenta,
        PDO $conn
    ): array {
        try {

            if ($idValorizacion <= 0) {
                throw new Exception(
                    "Valorización no válida."
                );
            }

            if ($idVenta <= 0) {
                throw new Exception(
                    "Venta no válida."
                );
            }

            // =====================================================
            // OBTENER TODOS LOS DETALLES DE TODOS LOS FLETES
            // =====================================================

            $query = $conn->prepare("
            SELECT
                vd.id_flete,

                f.serie AS serie_flete,
                f.ubigeo_origen,
                f.ubigeo_destino,

                uo.distri AS origen,
                ud.distri AS destino,

                fd.id_flete_detalle,
                fd.id_producto,
                fd.item AS item_flete,

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

            FROM valorizacion_detalle vd

            INNER JOIN flete f
                ON f.id_flete = vd.id_flete

            INNER JOIN flete_detalle fd
                ON fd.id_flete = f.id_flete

            INNER JOIN producto p
                ON p.id = fd.id_producto

            LEFT JOIN ubigeo uo
                ON uo.cod_ubigeo = f.ubigeo_origen

            LEFT JOIN ubigeo ud
                ON ud.cod_ubigeo = f.ubigeo_destino

            WHERE vd.id_valorizacion = :id_valorizacion

            ORDER BY
                vd.id ASC,
                fd.item ASC,
                fd.id_flete_detalle ASC
        ");

            $query->execute([
                ":id_valorizacion" =>
                $idValorizacion
            ]);

            $detalles =
                $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception(
                    "La valorización no tiene detalle comercial para facturar."
                );
            }

            // =====================================================
            // INSERT DETALLE COMPROBANTE
            // =====================================================

            $insert = $conn->prepare("
            INSERT INTO detalle_comprobante (
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
            VALUES (
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

            // =====================================================
            // INSERTAR
            // =====================================================

            $item = 0;

            foreach ($detalles as $detalle) {

                $item++;

                $idFlete =
                    (int) $detalle["id_flete"];

                // -----------------------------------------------
                // Número del Flete
                // -----------------------------------------------

                $numeroFlete =
                    !empty($detalle["serie_flete"])
                    ? sprintf(
                        "%s-%08d",
                        $detalle["serie_flete"],
                        $idFlete
                    )
                    : sprintf(
                        "%08d",
                        $idFlete
                    );

                // -----------------------------------------------
                // Ruta
                // -----------------------------------------------

                $origen = trim(
                    (string) (
                        $detalle["origen"]
                        ?? ""
                    )
                );

                $destino = trim(
                    (string) (
                        $detalle["destino"]
                        ?? ""
                    )
                );

                // -----------------------------------------------
                // Descripción inicial
                // -----------------------------------------------

                $nombreProducto = trim(
                    (string) (
                        $detalle["producto_nombre"]
                        ?? ""
                    )
                );

                if ($nombreProducto === "") {
                    $nombreProducto =
                        "SERVICIO DE FLETE";
                }

                /*
             * Para Valorización conviene identificar
             * cada Flete dentro del comprobante.
             *
             * Después esta descripción podrá modificarse
             * desde la vista previa.
             */
                $descripcion =
                    $nombreProducto .
                    " - FLETE " .
                    $numeroFlete;

                if (
                    $origen !== "" ||
                    $destino !== ""
                ) {

                    $descripcion .=
                        " - " .
                        $origen .
                        " / " .
                        $destino;
                }

                /*
             * detalle_comprobante.descripcion
             * es VARCHAR(1000)
             */
                $descripcion =
                    mb_substr(
                        $descripcion,
                        0,
                        1000,
                        "UTF-8"
                    );

                $cantidad =
                    (float) $detalle["cantidad"];

                // -----------------------------------------------
                // INSERT
                // -----------------------------------------------

                $insert->execute([

                    ":comprobante_id" =>
                    $idVenta,

                    ":item" =>
                    $item,

                    ":producto_id" =>
                    (int) $detalle["id_producto"],

                    ":descripcion" =>
                    $descripcion,

                    ":cantidad" =>
                    $cantidad,

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
                    (float) (
                        $detalle["factor_icbper"]
                        ?? 0
                    ),

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
                "Detalle de la valorización registrado correctamente.",
                "cantidad_items" =>
                $item
            ];
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                "Error al registrar detalle de la valorización: "
                    . $e->getMessage()
            ];
        }
    }

    private function registrar_fletes_venta_valorizacion(int $idValorizacion, int $idVenta, PDO $conn): array
    {
        try {

            if ($idValorizacion <= 0) {
                throw new Exception(
                    "Valorización no válida."
                );
            }

            if ($idVenta <= 0) {
                throw new Exception(
                    "Venta no válida."
                );
            }

            // =====================================================
            // OBTENER FLETES
            // =====================================================

            $query = $conn->prepare("
            SELECT
                vd.id_flete
            FROM valorizacion_detalle vd
            WHERE vd.id_valorizacion = :id_valorizacion
            ORDER BY vd.id ASC
        ");

            $query->execute([
                ":id_valorizacion" =>
                $idValorizacion
            ]);

            $fletes =
                $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($fletes)) {
                throw new Exception(
                    "La valorización no contiene fletes."
                );
            }

            // =====================================================
            // INSERT
            // =====================================================

            $insert = $conn->prepare("
            INSERT INTO flete_venta (
                id_flete,
                id_venta,
                origen_facturacion,
                id_valorizacion
            )
            VALUES (
                :id_flete,
                :id_venta,
                'VALORIZACION',
                :id_valorizacion
            )
        ");

            foreach ($fletes as $flete) {

                $idFlete =
                    (int) $flete["id_flete"];

                /*
             * Validación explícita.
             * Además tienes UNIQUE(id_flete) en flete_venta,
             * por lo que la BD también protege.
             */
                $check = $conn->prepare("
                SELECT
                    id_flete_venta,
                    id_venta
                FROM flete_venta
                WHERE id_flete = :id_flete
                LIMIT 1
            ");

                $check->execute([
                    ":id_flete" =>
                    $idFlete
                ]);

                $existente =
                    $check->fetch(PDO::FETCH_ASSOC);

                if ($existente) {

                    throw new Exception(
                        "El flete {$idFlete} ya está asociado al comprobante " .
                            $existente["id_venta"] . "."
                    );
                }

                $insert->execute([
                    ":id_flete" =>
                    $idFlete,

                    ":id_venta" =>
                    $idVenta,

                    ":id_valorizacion" =>
                    $idValorizacion
                ]);
            }

            return [
                "success" => true,
                "message" =>
                "Fletes asociados al comprobante correctamente.",
                "cantidad_fletes" =>
                count($fletes)
            ];
        } catch (Throwable $e) {

            return [
                "success" => false,
                "message" =>
                "Error al asociar los fletes: "
                    . $e->getMessage()
            ];
        }
    }
}
