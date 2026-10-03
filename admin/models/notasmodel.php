<?php

class NotasModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_dataTable($data)
    {
        try {
            // CORREGIDO: Usar CASE para determinar tipo_nota basado en tp_comprobante
            $selectFields = "n.*, 
                               CONCAT(n.serie_ref, ' ', n.correlativo_ref) AS comprobante_ref,
                               CONCAT(n.serie_ref, '-', n.correlativo_ref) AS documento_relacionado,
                               n.total,
                               CASE 
                                   WHEN tc.codigo = '07' THEN 'credito'
                                   WHEN tc.codigo = '08' THEN 'debito'
                                   ELSE 'desconocido'
                               END AS tipo_nota,
                               tc.codigo AS codigo_tp_comprobante,
                               tc.descripcion AS desc_tp_comprobante";

            $baseQuery = "FROM notas n 
                              LEFT JOIN tp_comprobante tc ON n.id_tp_comprobante = tc.id_tp_comprobante";

            $searchColumns = [
                "n.fecha_emision",
                "n.serie",
                "n.correlativo",
                "CONCAT(n.serie, '-', n.correlativo)",
                "n.descripcion",
                "n.mensaje_sunat",
                "n.serie_ref",
                "n.correlativo_ref",
                "CONCAT(n.serie_ref, '-', n.correlativo_ref)",
                "n.total",
                "tc.descripcion",  // Buscar en tipo de comprobante
                "CASE 
                    WHEN n.estado_sunat = 0 THEN 'SIN ENVIAR' 
                    WHEN n.estado_sunat = 1 THEN 'ENVIAR A SUNAT' 
                    WHEN n.estado_sunat = 2 THEN 'ENVIADO POR RESUMEN' 
                    ELSE 'DESCONOCIDO' 
                END"
            ];

            $orderBy = "n.fecha_emision DESC";

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

    // CORREGIDO: Método para obtener datos completos
    public function getNotaCompleta($id_nota)
    {
        try {
            error_log("=== INICIANDO getNotaCompleta para ID: $id_nota ===");

            // 1. Datos del emisor
            $queryEmpresa = $this->db->connect()->prepare("
            SELECT 
                e.id_empresa, e.logo, e.tp_docu, e.num_docu, e.razon_social,
                e.ubigeo, e.direccion_fiscal,
                ue.depa AS empresa_departamento, ue.provi AS empresa_provincia, ue.distri AS empresa_distrito
            FROM empresa e
            LEFT JOIN ubigeo ue ON e.ubigeo = ue.cod_ubigeo
            WHERE e.id_empresa = 1
        ");
            $queryEmpresa->execute();
            $emisor = $queryEmpresa->fetch(PDO::FETCH_ASSOC) ?: null;

            if (!$emisor) {
                error_log("ERROR: Empresa no encontrada (id=1)");
                return ['success' => false, 'message' => 'Empresa no configurada'];
            }

            // 2. Datos de la nota
            $queryNota = $this->db->connect()->prepare("
            SELECT 
                n.*,
                n.hash_cpe,
                tc.codigo AS codigo_tp_comprobante,
                tc.descripcion AS desc_tp_comprobante,
                CASE WHEN tc.codigo = '07' THEN 'credito' WHEN tc.codigo = '08' THEN 'debito' ELSE 'desconocido' END AS tipo_nota,
                CASE WHEN tc.codigo = '07' THEN 'C' WHEN tc.codigo = '08' THEN 'D' ELSE '' END AS motivo_tipo
            FROM notas n
            LEFT JOIN tp_comprobante tc ON n.id_tp_comprobante = tc.id_tp_comprobante
            WHERE n.id_nota = :id_nota
        ");
            $queryNota->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $queryNota->execute();
            $data_nota = $queryNota->fetch(PDO::FETCH_ASSOC);

            if (!$data_nota) {
                error_log("ERROR: Nota no encontrada ID $id_nota");
                return ['success' => false, 'message' => 'Nota no encontrada'];
            }

            // 3. Obtener datos de la venta relacionada (necesario para tipo y detalles)
            $venta = null;
            $detalles = [];
            if (!empty($data_nota['id_comprobante'])) {
                $queryVenta = $this->db->connect()->prepare("
                SELECT 
                    v.id_venta, v.id_tp_venta, v.serie, v.correlativo, v.fecha_emision, v.total, v.descuento,
                    tc.codigo AS tp_comprobante_codigo
                FROM venta v
                LEFT JOIN tp_comprobante tc ON v.id_tp_comprobante = tc.id_tp_comprobante
                WHERE v.id_venta = :id_venta
            ");
                $queryVenta->bindParam(':id_venta', $data_nota['id_comprobante'], PDO::PARAM_INT);
                $queryVenta->execute();
                $venta = $queryVenta->fetch(PDO::FETCH_ASSOC);

                if ($venta) {
                    // 4. Elegir método de detalles según tipo de venta
                    switch ($venta['id_tp_venta']) {
                        case 1: // PASAJE
                            $detalles = $this->getDetallesPasaje($data_nota, $id_nota);
                            break;
                        case 2: // ENCOMIENDA
                            $detalles = $this->getDetallesEncomienda($data_nota, $id_nota);
                            break;
                        default: // FACTURADOR / GENERAL / OTROS
                            // MODIFICACIÓN: Pasar $id_nota como segundo parámetro
                            $detalles = $this->getDetallesGenericos($venta['id_venta'], $id_nota);
                            break;
                    }
                }
            }

            // Fallback si no hay detalles
            if (empty($detalles)) {
                $detalles = $this->crearDetalleBasico($data_nota, $id_nota);
            }

            // 5. Cliente
            $cliente = null;
            if ($venta) {
                $queryCliente = $this->db->connect()->prepare("
                SELECT
                    CONCAT(c.nombres, ' ', c.apellidos) AS nombres_cliente,
                    c.id_tp_docu, c.num_docu, c.direccion,
                    CONCAT(COALESCE(c.direccion,''), ' - ', COALESCE(ub_c.distri,''), ', ', COALESCE(ub_c.provi,''), ', ', COALESCE(ub_c.depa,'')) AS direccion_completa,
                    tp_d_c.codigo AS tipo_doc_codigo, tp_d_c.descripcion AS tipo_doc_descripcion,
                    ub_c.depa AS ubigeo_depa, ub_c.provi AS ubigeo_provi, ub_c.distri AS ubigeo_distri
                FROM venta v
                LEFT JOIN usuario c ON v.id_cliente = c.id_usuario
                LEFT JOIN ubigeo ub_c ON c.ubigeo = ub_c.cod_ubigeo
                LEFT JOIN tp_docu tp_d_c ON c.id_tp_docu = tp_d_c.id_tp_docu
                WHERE v.id_venta = :id_venta
            ");
                $queryCliente->bindParam(':id_venta', $venta['id_venta'], PDO::PARAM_INT);
                $queryCliente->execute();
                $cliente = $queryCliente->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if (!$cliente) {
                $cliente = [
                    'nombres_cliente' => $data_nota['cliente'] ?? 'CLIENTE NO REGISTRADO',
                    'tipo_doc_descripcion' => 'N/A',
                    'num_docu' => '-',
                    'direccion_completa' => '',
                    'pais' => 'PE'
                ];
            }

            // 6. Vendedor
            $vendedor = $this->getVendedorPorVenta($data_nota['serie_ref'], $data_nota['correlativo_ref']);

            // 7. Motivo
            $motivo = null;
            if (!empty($data_nota['codmotivo']) && !empty($data_nota['motivo_tipo'])) {
                $queryMotivo = $this->db->connect()->prepare("
                SELECT descripcion FROM tabla_parametrica 
                WHERE codigo = :codigo AND tipo = :tipo
            ");
                $queryMotivo->bindParam(':codigo', $data_nota['codmotivo']);
                $queryMotivo->bindParam(':tipo', $data_nota['motivo_tipo']);
                $queryMotivo->execute();
                $motivo = $queryMotivo->fetch(PDO::FETCH_ASSOC);
            }

            // 8. Log de los detalles obtenidos
            error_log("Detalles obtenidos para nota ID $id_nota: " . count($detalles) . " ítems");
            if (!empty($detalles)) {
                foreach ($detalles as $index => $detalle) {
                    error_log("Ítem " . ($index + 1) . ": " . 
                             ($detalle['producto_nombre'] ?? 'sin nombre') . " - " . 
                             ($detalle['precio_unitario'] ?? '0') . " - " . 
                             ($detalle['importe_total'] ?? '0'));
                }
            }

            // 9. Respuesta final
            $respuesta = [
                'emisor' => $emisor,
                'data_nota' => $data_nota,
                'cliente' => $cliente,
                'vendedor' => $vendedor,
                'detalles' => $detalles,
                'motivo' => $motivo,
                'venta_relacionada' => $venta
            ];

            error_log("=== getNotaCompleta COMPLETADO CON ÉXITO ===");
            return ['success' => true, 'message' => $respuesta];

        } catch (PDOException $e) {
            error_log("ERROR GRAVE en getNotaCompleta: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

     private function getDetallesFromJson($id_nota)
    {
        try {
            $query = $this->db->connect()->prepare("
                SELECT detalles_json FROM notas WHERE id_nota = :id_nota
            ");
            $query->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $query->execute();
            
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['detalles_json'])) {
                $detalles = json_decode($result['detalles_json'], true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($detalles)) {
                    // Formatear los detalles
                    $formatted = [];
                    $item = 1;
                    foreach ($detalles as $detalle) {
                        $formatted[] = [
                            'item' => $item++,
                            'cantidad' => isset($detalle['cantidad']) ? number_format($detalle['cantidad'], 2) : '1.00',
                            'unidad_medida' => $detalle['unidad_medida'] ?? 'NIU',
                            'producto_nombre' => $detalle['producto_nombre'] ?? ($detalle['descripcion'] ?? 'Producto'),
                            'precio_unitario' => isset($detalle['precio_unitario']) ? number_format($detalle['precio_unitario'], 4) : '0.0000',
                            'valor_unitario' => isset($detalle['valor_unitario']) ? number_format($detalle['valor_unitario'], 4) : '0.0000',
                            'valor_total' => isset($detalle['valor_total']) ? number_format($detalle['valor_total'], 2) : 
                                           (isset($detalle['importe_total']) ? number_format($detalle['importe_total'], 2) : '0.00'),
                            'importe_total' => isset($detalle['importe_total']) ? number_format($detalle['importe_total'], 2) : 
                                              (isset($detalle['valor_total']) ? number_format($detalle['valor_total'], 2) : '0.00'),
                            'igv' => isset($detalle['igv']) ? number_format($detalle['igv'], 2) : '0.00',
                            'descuento' => isset($detalle['descuento']) ? number_format($detalle['descuento'], 2) : '0.00',
                            'descripcion' => $detalle['descripcion'] ?? $detalle['producto_nombre'] ?? 'Producto'
                        ];
                    }
                    return $formatted;
                }
            }
            
            return [];
            
        } catch (PDOException $e) {
            error_log("Error en getDetallesFromJson: " . $e->getMessage());
            return [];
        }
    }

    // CORREGIDO: Método para obtener los detalles/productos de la nota
    private function getDetallesGenericos($id_venta, $id_nota = null)
    {
        try {
            // PRIMERO: Intentar obtener desde JSON de la nota (detalles modificados)
            if ($id_nota) {
                $detallesNota = $this->getDetallesFromJson($id_nota);
                if (!empty($detallesNota)) {
                    error_log("Encontrados detalles en JSON para nota ID: $id_nota");
                    return $detallesNota;
                }
            }
            
            error_log("No hay detalles en JSON para nota ID: $id_nota, buscando en venta original");

            // SEGUNDO: Intentar obtener desde detalle_comprobante (venta original)
            $query = $this->db->connect()->prepare("
            SELECT 
                dc.item,
                dc.cantidad,
                dc.valor_unitario,
                dc.precio_unitario,
                dc.igv,
                dc.valor_total,
                dc.importe_total,
                COALESCE(p.codigo, 'NIU') AS unidad_medida,
                COALESCE(p.nombre, dc.descripcion) AS producto_nombre,
                dc.descripcion,
                p.id_producto
            FROM detalle_comprobante dc
            LEFT JOIN producto p ON dc.producto_id = p.id_producto
            WHERE dc.comprobante_id = :id_venta
            ORDER BY dc.item ASC
        ");

            $query->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
            $query->execute();
            $items = $query->fetchAll(PDO::FETCH_ASSOC);

            // Si encontramos detalles en detalle_comprobante
            if (!empty($items)) {
                error_log("Encontrados " . count($items) . " detalles en detalle_comprobante para venta ID: $id_venta");
                // Numerar ítems y formatear para la vista
                $resultado = [];
                $item = 1;
                foreach ($items as $row) {
                    $resultado[] = [
                        'item' => $item++,
                        'cantidad' => number_format($row['cantidad'], 2),
                        'unidad_medida' => $row['unidad_medida'] ?: 'NIU',
                        'producto_nombre' => $row['producto_nombre'] ?: ($row['descripcion'] ?: 'Producto sin nombre'),
                        'precio_unitario' => number_format($row['precio_unitario'], 4),
                        'valor_unitario' => number_format($row['valor_unitario'], 4),
                        'descuento' => 0,
                        'valor_total' => number_format($row['valor_total'], 2),
                        'importe_total' => number_format($row['importe_total'], 2),
                        'igv' => number_format($row['igv'] ?? 0, 2),
                        'descripcion' => $row['descripcion'] ?: $row['producto_nombre']
                    ];
                }
                return $resultado;
            }

            // TERCERO: Si no hay en detalle_comprobante, intentar desde dt_venta
            $query2 = $this->db->connect()->prepare("
            SELECT 
                dt.id_dt_venta,
                dt.cantidad,
                dt.precio_unitario,
                dt.valor_unitario,
                dt.descuento,
                dt.op_igv,
                dt.op_gravada,
                dt.op_exonerada,
                dt.op_inafecta,
                dt.op_total AS importe_total,
                dt.valor_total,
                COALESCE(p.codigo, 'NIU') AS unidad_medida,
                COALESCE(p.nombre, dt.descripcion) AS producto_nombre,
                dt.descripcion,
                dt.id_producto
            FROM dt_venta dt
            LEFT JOIN producto p ON dt.id_producto = p.id_producto
            WHERE dt.id_venta = :id_venta
            ORDER BY dt.id_dt_venta ASC
        ");

            $query2->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
            $query2->execute();
            $items = $query2->fetchAll(PDO::FETCH_ASSOC);

            // Si encontramos detalles en dt_venta
            if (!empty($items)) {
                error_log("Encontrados " . count($items) . " detalles en dt_venta para venta ID: $id_venta");
                $resultado = [];
                $item = 1;
                foreach ($items as $row) {
                    $resultado[] = [
                        'item' => $item++,
                        'cantidad' => number_format($row['cantidad'], 2),
                        'unidad_medida' => $row['unidad_medida'] ?: 'NIU',
                        'producto_nombre' => $row['producto_nombre'] ?: ($row['descripcion'] ?: 'Producto sin nombre'),
                        'precio_unitario' => number_format($row['precio_unitario'], 2),
                        'valor_unitario' => number_format($row['valor_unitario'], 2),
                        'descuento' => number_format($row['descuento'] ?? 0, 2),
                        'valor_total' => number_format($row['valor_total'], 2),
                        'importe_total' => number_format($row['importe_total'], 2),
                        'igv' => number_format($row['op_igv'] ?? 0, 2),
                        'descripcion' => $row['descripcion'] ?: $row['producto_nombre']
                    ];
                }
                return $resultado;
            }

            error_log("No se encontraron detalles en ninguna tabla para venta ID: $id_venta");
            // Si no hay detalles en ninguna tabla, devolver array vacío
            return [];

        } catch (PDOException $e) {
            error_log("Error al obtener detalles genéricos: " . $e->getMessage());
            return [];
        }
    }

    // Método auxiliar para notas de PASAJE
    private function getDetallesPasaje($notaInfo, $id_nota)
    {
        try {
            // PRIMERO: Intentar obtener desde JSON
            $detallesJson = $this->getDetallesFromJson($id_nota);
            if (!empty($detallesJson)) {
                return $detallesJson;
            }
            
            // SEGUNDO: Si no hay JSON, usar la lógica original
            // Obtener la venta relacionada con esta nota
            $queryVenta = $this->db->connect()->prepare("
            SELECT id_comprobante FROM notas WHERE id_nota = :id_nota
        ");
            $queryVenta->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $queryVenta->execute();
            $ventaNota = $queryVenta->fetch(PDO::FETCH_ASSOC);

            if (!$ventaNota || empty($ventaNota['id_comprobante'])) {
                return $this->crearDetalleBasico($notaInfo, $id_nota);
            }

            // Buscar datos del pasaje en dt_venta
            $query = $this->db->connect()->prepare("
            SELECT 
                dt_v.*,
                p.fecha_salida,
                p.hora_salida,
                t_o.nombre AS origen,
                t_d.nombre AS destino,
                tp_s.descripcion AS tipo_servicio
            FROM dt_venta dt_v
            LEFT JOIN programacion p ON dt_v.id_programacion = p.id_programacion
            LEFT JOIN terminal t_o ON p.id_terminal_origen = t_o.id_terminal
            LEFT JOIN terminal t_d ON p.id_terminal_destino = t_d.id_terminal
            LEFT JOIN tp_servicio tp_s ON dt_v.id_tp_servicio = tp_s.id_tp_servicio
            WHERE dt_v.id_venta = :id_venta
        ");

            $query->bindParam(':id_venta', $ventaNota['id_comprobante'], PDO::PARAM_INT);
            $query->execute();

            $detallePasaje = $query->fetch(PDO::FETCH_ASSOC);

            if ($detallePasaje) {
                return [
                    array(
                        'item' => 1,
                        'cantidad' => 1,
                        'unidad_medida' => 'ZZ',
                        'producto_nombre' => 'PASAJE: ' .
                            ($detallePasaje['origen'] ?? '') . ' - ' .
                            ($detallePasaje['destino'] ?? ''),
                        'precio_unitario' => $detallePasaje['precio'] ?? 0,
                        'valor_unitario' => $detallePasaje['precio'] ?? 0,
                        'importe_total' => $detallePasaje['op_total'] ?? 0,
                        'valor_total' => $detallePasaje['op_total'] ?? 0,
                        'igv' => $detallePasaje['op_igv'] ?? 0,
                        'descuento' => $detallePasaje['descuento'] ?? 0,
                        'descripcion' => $notaInfo['descripcion'] ?? 'Pasaje'
                    )
                ];
            }

            return $this->crearDetalleBasico($notaInfo, $id_nota);

        } catch (PDOException $e) {
            error_log("Error en getDetallesPasaje: " . $e->getMessage());
            return $this->crearDetalleBasico($notaInfo, $id_nota);
        }
    }

    // Método auxiliar para notas de ENCOMIENDA
    private function getDetallesEncomienda($notaInfo, $id_nota)
    {
        try {
            // PRIMERO: Intentar obtener desde JSON
            $detallesJson = $this->getDetallesFromJson($id_nota);
            if (!empty($detallesJson)) {
                return $detallesJson;
            }
            
            // SEGUNDO: Si no hay JSON, usar la lógica original
            // Obtener la venta relacionada con esta nota
            $queryVenta = $this->db->connect()->prepare("
            SELECT id_comprobante FROM notas WHERE id_nota = :id_nota
        ");
            $queryVenta->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $queryVenta->execute();
            $ventaNota = $queryVenta->fetch(PDO::FETCH_ASSOC);

            if (!$ventaNota || empty($ventaNota['id_comprobante'])) {
                return $this->crearDetalleBasico($notaInfo, $id_nota);
            }

            // Buscar productos de encomienda
            $query = $this->db->connect()->prepare("
            SELECT 
                pe.*,
                ct.descripcion AS categoria,
                um.abreviatura AS unidad_medida
            FROM producto_encomienda pe
            LEFT JOIN ctg_encomienda ct ON pe.id_ctg_encomienda = ct.id_ctg_encomienda
            LEFT JOIN unidad_medida um ON pe.id_unidad_medida = um.id
            WHERE pe.id_encomienda IN (
                SELECT id_encomienda FROM dt_venta WHERE id_venta = :id_venta
            )
        ");

            $query->bindParam(':id_venta', $ventaNota['id_comprobante'], PDO::PARAM_INT);
            $query->execute();

            $detalles = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($detalles && count($detalles) > 0) {
                $resultado = [];
                $item = 1;
                foreach ($detalles as $detalle) {
                    $resultado[] = array(
                        'item' => $item++,
                        'cantidad' => $detalle['cantidad'] ?? 1,
                        'unidad_medida' => $detalle['unidad_medida'] ?? 'KG',
                        'producto_nombre' => $detalle['categoria'] ?? 'ENCOMIENDA',
                        'precio_unitario' => $detalle['precio'] ?? 0,
                        'valor_unitario' => $detalle['valor_unitario'] ?? 0,
                        'importe_total' => $detalle['op_total'] ?? 0,
                        'valor_total' => $detalle['op_total'] ?? 0,
                        'igv' => $detalle['op_igv'] ?? 0,
                        'descuento' => $detalle['descuento'] ?? 0,
                        'descripcion' => $detalle['descripcion'] ?? $notaInfo['descripcion']
                    );
                }
                return $resultado;
            }

            return $this->crearDetalleBasico($notaInfo, $id_nota);

        } catch (PDOException $e) {
            error_log("Error en getDetallesEncomienda: " . $e->getMessage());
            return $this->crearDetalleBasico($notaInfo, $id_nota);
        }
    }

    // Crear un detalle básico cuando no hay detalles específicos
    private function crearDetalleBasico($notaInfo, $id_nota)
    {
        // Obtener total de la nota
        $query = $this->db->connect()->prepare("
        SELECT total, descripcion FROM notas WHERE id_nota = :id_nota
    ");
        $query->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
        $query->execute();
        $notaData = $query->fetch(PDO::FETCH_ASSOC);

        return [
            array(
                'item' => 1,
                'cantidad' => 1,
                'unidad_medida' => 'ZZ',
                'producto_nombre' => 'AJUSTE / NOTA',
                'precio_unitario' => $notaData['total'] ?? 0,
                'valor_unitario' => $notaData['total'] ?? 0,
                'importe_total' => $notaData['total'] ?? 0,
                'valor_total' => $notaData['total'] ?? 0,
                'igv' => 0,
                'descuento' => 0,
                'descripcion' => $notaData['descripcion'] ?? 'Nota'
            )
        ];
    }

    // CORREGIDO: Método para obtener datos del vendedor
    private function getVendedorPorVenta($serie, $correlativo)
    {
        try {
            $query = "SELECT 
                        us.id_usuario,
                        us.nombres AS vendedor_nombres,
                        us.apellidos AS vendedor_apellidos,
                        us.num_docu AS vendedor_dni
                      FROM venta v
                      LEFT JOIN usuario us ON v.id_vendedor = us.id_usuario
                      WHERE v.serie = :serie 
                        AND v.correlativo = :correlativo";

            $stmt = $this->db->connect()->prepare($query);
            $stmt->bindParam(':serie', $serie);
            $stmt->bindParam(':correlativo', $correlativo);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getVendedorPorVenta: " . $e->getMessage());
            return null;
        }
    }

    // CORREGIDO: Método para obtener tipo de nota
    public function getTipoNotaById($id_nota)
    {
        $query = "SELECT 
            n.id_nota,
            n.serie,
            n.correlativo,
            n.serie_ref,
            n.correlativo_ref,
            tc.codigo AS codigo_comprobante,
            tc.descripcion AS desc_comprobante,
            CASE 
                WHEN tc.codigo = '07' THEN 'credito'
                WHEN tc.codigo = '08' THEN 'debito'
                ELSE 'desconocido'
            END AS tipo_nota,
            CASE 
                WHEN tc.codigo = '07' THEN '07'
                WHEN tc.codigo = '08' THEN '08'
                ELSE ''
            END AS tipo_documento
          FROM notas n
          LEFT JOIN tp_comprobante tc ON n.id_tp_comprobante = tc.id_tp_comprobante
          WHERE n.id_nota = :id_nota";

        $stmt = $this->db->connect()->prepare($query);
        $stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
