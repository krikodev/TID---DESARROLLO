<?php
ob_start();

require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    $datos        = $GLOBALS['export_datos']        ?? [];
    $empresa      = $GLOBALS['export_empresa']      ?? [];
    $fecha_inicio = $GLOBALS['export_fecha_inicio'] ?? null;
    $fecha_fin    = $GLOBALS['export_fecha_fin']    ?? null;
    $tipo         = $GLOBALS['export_tipo']         ?? null;
    $estado       = $GLOBALS['export_estado']       ?? null;

    // ── Detecta si el modelo ya aplicó config dinámica ──
    // Cuando hay config, la primera fila usa labels como claves
    // (ej: "Código Moneda") en vez de los aliases SQL (ej: "tp_moneda_codigo")
    $tiene_config = $GLOBALS['export_tiene_config'] ?? false;

    $nombre_empresa = $empresa['nombre']              ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa    = $empresa['num_docu']             ?? '';
    $logo_path      = $empresa['logo_absolute_path']  ?? '';
    $nombre_reporte = $empresa['nombre_reporte'] ?? 'NOTAS DE VENTA';

    $logo_exists    = !empty($logo_path) && file_exists($logo_path);

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('NotasVenta');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setTop(0.5)->setRight(0.25)->setLeft(0.25)->setBottom(0.5);

    $fila_actual = 1;

    // ============================================
    // ENCABEZADO EMPRESA
    // ============================================
    $col_fin_merge = $tiene_config && !empty($datos)
        ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(reset($datos)))
        : 'I';

    if ($logo_exists) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa')->setDescription('Logo de la empresa')
                ->setPath($logo_path)->setHeight(50)
                ->setCoordinates('A' . $fila_actual)->setOffsetX(5)->setOffsetY(5)
                ->setWorksheet($sheet);
            $sheet->getRowDimension($fila_actual)->setRowHeight(50);
            $sheet->setCellValue('C' . $fila_actual, $nombre_empresa . ' - REPORTE DE ' . $reporte_);
            $sheet->mergeCells('C' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $sheet->getStyle('C' . $fila_actual)->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('C' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual++;
        } catch (Exception $e) {
            $logo_exists = false;
            $fila_actual = 1;
        }
    }

    if (!$logo_exists) {
        $sheet->setCellValue('A' . $fila_actual, $nombre_empresa . ' - REPORTE DE ' . $nombre_reporte);
        $sheet->mergeCells('A' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $fila_actual++;
    }

    // Info empresa
    $info_empresa = [];
    if (!empty($ruc_empresa))          $info_empresa[] = 'RUC: ' . $ruc_empresa;
    if (!empty($empresa['direccion'])) $info_empresa[] = $empresa['direccion'];
    if (!empty($empresa['telefono']))  $info_empresa[] = 'Tel: ' . $empresa['telefono'];

    if (!empty($info_empresa)) {
        $sheet->setCellValue('A' . $fila_actual, implode(' - ', $info_empresa));
        $sheet->mergeCells('A' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $fila_actual++;
    }

    // Fecha generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    $fila_actual += 2;

    // ============================================
    // FILTROS
    // ============================================
    function getTipoTexto($tipo_id)
    {
        switch (intval($tipo_id)) {
            case 1:
                return 'PASAJE';
            case 2:
                return 'ENCOMIENDA';
            case 3:
                return 'FACTURADOR';
            case 4:
                return 'PAGO BLOQUE';
            default:
                return $tipo_id;
        }
    }

    if (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($tipo) || !empty($estado)) {
        $sheet->setCellValue('A' . $fila_actual, 'FILTROS APLICADOS:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->mergeCells('A' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
        $fila_actual++;

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $sheet->setCellValue('A' . $fila_actual, 'Período:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $fila_actual, $fecha_inicio . ' al ' . $fecha_fin);
            $sheet->mergeCells('B' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $fila_actual++;
        } elseif (!empty($fecha_inicio)) {
            $sheet->setCellValue('A' . $fila_actual, 'Fecha desde:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $fila_actual, $fecha_inicio);
            $sheet->mergeCells('B' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $fila_actual++;
        } elseif (!empty($fecha_fin)) {
            $sheet->setCellValue('A' . $fila_actual, 'Fecha hasta:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $fila_actual, $fecha_fin);
            $sheet->mergeCells('B' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $fila_actual++;
        }

        if (!empty($tipo)) {
            $sheet->setCellValue('A' . $fila_actual, 'Tipo:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $fila_actual, getTipoTexto($tipo));
            $sheet->mergeCells('B' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $fila_actual++;
        }

        if (!empty($estado)) {
            $sheet->setCellValue('A' . $fila_actual, 'Estado:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $fila_actual, $estado);
            $sheet->mergeCells('B' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
            $fila_actual++;
        }

        $fila_actual++;
    }

    // ============================================
    // ENCABEZADOS + DATOS — bifurcación principal
    // ============================================
    $estilo_encabezado = [
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
    ];

    $fila_encabezados = $fila_actual;

    if (!empty($datos) && is_array($datos)) {

        if ($tiene_config) {
            // ══════════════════════════════════════════
            // MODO DINÁMICO — campos configurados
            // Las claves de cada fila ya son los labels
            // ══════════════════════════════════════════
            $headers = array_keys(reset($datos)); // ['Código Moneda', 'Moneda', ...]
            $num_cols = count($headers);
            $col_total_idx = null;

            $col_total_idx = null;
            foreach ($headers as $idx => $header) {
                if (stripos($header, 'total') !== false) {
                    $col_total_idx = $idx;
                    break;
                }
            }

            // Anchos automáticos (podés ajustar)
            foreach ($headers as $idx => $header) {
                $col_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);

                $sheet->getColumnDimension($col_letra)->setWidth(20);
                $sheet->setCellValue($col_letra . $fila_actual, strtoupper($header));
                $sheet->getStyle($col_letra . $fila_actual)->applyFromArray($estilo_encabezado);
            }

            $sheet->getRowDimension($fila_actual)->setRowHeight(25);
            $fila_actual++;
            $total_general = 0;

            foreach ($datos as $registro) {
                foreach ($headers as $idx => $label) {
                    $col_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                    $valor = isset($registro[$label]) ? $registro[$label] : '';

                    $es_texto = preg_match('/telefono|celular|dni|ruc/i', $label);
                    // Detecta numéricos para formato
                    if (is_numeric($valor) && !empty($valor) && !$es_texto) {
                        $sheet->setCellValue($col_letra . $fila_actual, floatval($valor));
                        $sheet->getStyle($col_letra . $fila_actual)
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                        // Acumula si parece columna de total
                        if ($idx === $col_total_idx) {
                            $total_general += floatval($valor);
                        }
                    } else {
                        $sheet->setCellValue($col_letra . $fila_actual, $valor);
                    }
                }

                // Bordes por fila
                $col_ultima = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($num_cols);
                $sheet->getStyle('A' . $fila_actual . ':' . $col_ultima . $fila_actual)
                    ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

                $fila_actual++;
            }

            if ($col_total_idx !== null && $total_general > 0) {

                $col_total_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_total_idx + 1);
                $col_label_letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_total_idx);

                // Texto TOTAL a la izquierda
                if ($col_total_idx > 0) {
                    $sheet->setCellValue($col_label_letra . $fila_actual, 'TOTAL:');
                    $sheet->getStyle($col_label_letra . $fila_actual)->getFont()->setBold(true);
                    $sheet->getStyle($col_label_letra . $fila_actual)
                        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                }

                // Valor total en la columna correcta
                $sheet->setCellValue($col_total_letra . $fila_actual, $total_general);
                $sheet->getStyle($col_total_letra . $fila_actual)
                    ->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($col_total_letra . $fila_actual)->getFont()->setBold(true);
            }
            // Autofilter dinámico
            $col_ultima = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($num_cols);
            $sheet->setAutoFilter('A' . $fila_encabezados . ':' . $col_ultima . ($fila_actual - 1));
        } else {
            // ══════════════════════════════════════════
            // MODO CLÁSICO — layout hardcodeado original
            // ══════════════════════════════════════════
            $sheet->getColumnDimension('A')->setWidth(18);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(40);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(10);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(18);

            $encabezados = [
                'A' => 'NÚMERO',
                'B' => 'FECHA EMISIÓN',
                'C' => 'CLIENTE',
                'D' => 'DOCUMENTO',
                'E' => 'MONEDA',
                'F' => 'TOTAL',
                'G' => 'TIPO',
                'H' => 'ESTADO',
                'I' => 'FECHA REGISTRO'
            ];

            foreach ($encabezados as $columna => $titulo) {
                $sheet->setCellValue($columna . $fila_actual, $titulo);
                $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
            }

            $sheet->getRowDimension($fila_actual)->setRowHeight(25);
            $fila_actual++;
            $total_general = 0;

            foreach ($datos as $registro) {
                $numero = (!empty($registro['serie']) && !empty($registro['correlativo']))
                    ? $registro['serie'] . '-' . $registro['correlativo'] : '';
                $sheet->setCellValue('A' . $fila_actual, $numero);

                $fecha_emision = '';
                if (!empty($registro['fecha_emision'])) {
                    try {
                        $fecha_emision = date('d/m/Y H:i', strtotime($registro['fecha_emision']));
                    } catch (Exception $e) {
                        $fecha_emision = $registro['fecha_emision'];
                    }
                }
                $sheet->setCellValue('B' . $fila_actual, $fecha_emision);

                $nombre_cliente = trim(($registro['cliente_nombres'] ?? '') . ' ' . ($registro['cliente_apellidos'] ?? ''));
                $sheet->setCellValue('C' . $fila_actual, $nombre_cliente);
                $sheet->getStyle('C' . $fila_actual)->getAlignment()->setWrapText(true);

                $sheet->setCellValue('D' . $fila_actual, $registro['cliente_num_docu'] ?? '');
                $sheet->setCellValue('E' . $fila_actual, $registro['tp_moneda_codigo'] ?? '');

                $total = floatval($registro['total'] ?? 0);
                $sheet->setCellValue('F' . $fila_actual, $total);
                $sheet->getStyle('F' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');
                $total_general += $total;

                $tipo_texto = '';
                if (!empty($registro['id_tp_venta'])) {
                    $tipo_texto = getTipoTexto($registro['id_tp_venta']);
                } elseif (!empty($registro['tp_servicio'])) {
                    $tipo_texto = $registro['tp_servicio'];
                } else {
                    $tipo_texto = 'FACTURADOR';
                }
                $sheet->setCellValue('G' . $fila_actual, $tipo_texto);
                $sheet->setCellValue('H' . $fila_actual, $registro['estado'] ?? '');

                $fecha_registro = '';
                if (!empty($registro['fecha_registro'])) {
                    try {
                        $fecha_registro = date('d/m/Y H:i', strtotime($registro['fecha_registro']));
                    } catch (Exception $e) {
                        $fecha_registro = $registro['fecha_registro'];
                    }
                }
                $sheet->setCellValue('I' . $fila_actual, $fecha_registro);

                $sheet->getStyle('A' . $fila_actual . ':I' . $fila_actual)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                if (strlen($nombre_cliente) > 40) {
                    $sheet->getRowDimension($fila_actual)->setRowHeight(15 * ceil(strlen($nombre_cliente) / 40));
                }

                $fila_actual++;
            }

            // Total
            $sheet->setCellValue('E' . $fila_actual, 'TOTAL:');
            $sheet->getStyle('E' . $fila_actual)->getFont()->setBold(true);
            $sheet->getStyle('E' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('F' . $fila_actual, $total_general);
            $sheet->getStyle('F' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('F' . $fila_actual)->getFont()->setBold(true);
            $sheet->getStyle('E' . $fila_actual . ':F' . $fila_actual)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);

            $sheet->setAutoFilter('A' . $fila_encabezados . ':I' . ($fila_actual - 1));
        }
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':' . $col_fin_merge . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));
    }

    // ============================================
    // DESCARGA
    // ============================================
    $fecha_inicio_str = !empty($fecha_inicio) ? str_replace('-', '', $fecha_inicio) : 'todo';
    $fecha_fin_str    = !empty($fecha_fin)    ? str_replace('-', '', $fecha_fin)    : 'todo';
    $filename = "notas_venta_{$fecha_inicio_str}_{$fecha_fin_str}_" . date('Ymd_His') . ".xlsx";

    if (ob_get_length()) ob_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    (new Xlsx($spreadsheet))->save('php://output');
    exit;
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    error_log("Error exportación Excel: " . $e->getMessage());
    exit;
}
