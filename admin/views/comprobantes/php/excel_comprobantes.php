<?php
// Limpiar buffer de salida
ob_start();

// Incluir PhpSpreadsheet
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // ============================================
    // LAS VARIABLES VIENEN DIRECTAMENTE DEL EXTRACT EN EL CONTROLADOR
    // ============================================
    
    // Verificar que las variables existen
    $datos = isset($datos) ? $datos : [];
    $empresa = isset($empresa) ? $empresa : [];
    $fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
    $fecha_fin = isset($fecha_fin) ? $fecha_fin : '';
    $tp_comprobante = isset($tp_comprobante) ? $tp_comprobante : '';
    $tipo_venta = isset($tipo_venta) ? $tipo_venta : '';

    // DEBUG - Verificar que llegaron datos
    error_log("=== REPORTE COMPROBANTES - Vista Excel ===");
    error_log("Fecha Inicio: " . $fecha_inicio);
    error_log("Fecha Fin: " . $fecha_fin);
    error_log("Tipo Comprobante: " . $tp_comprobante);
    error_log("Tipo Venta: " . $tipo_venta);
    error_log("Cantidad de datos: " . (is_array($datos) ? count($datos) : 0));
    
    // Verificar estructura de empresa
    error_log("Empresa: " . print_r($empresa, true));

    // ============================================
    // CONSTRUIR RUTA DEL LOGO
    // ============================================
    
    $logo_path = '';
    $logo_exists = false;
    
    if (!empty($empresa) && is_array($empresa) && !empty($empresa['logo'])) {
        $rutas_posibles = [
            $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/admin/public/' . $empresa['logo'],
            __DIR__ . '/../../../public/' . $empresa['logo'],
            __DIR__ . '/../../../../public/' . $empresa['logo']
        ];
        
        foreach ($rutas_posibles as $ruta) {
            if (file_exists($ruta)) {
                $logo_path = $ruta;
                $logo_exists = true;
                break;
            }
        }
    }
    
    $nombre_empresa = (!empty($empresa) && is_array($empresa) && !empty($empresa['nombre'])) ? $empresa['nombre'] : 'EMPRESA';
    $ruc_empresa = (!empty($empresa) && is_array($empresa)) ? ($empresa['num_docu'] ?? '') : '';
    $direccion_empresa = (!empty($empresa) && is_array($empresa)) ? ($empresa['direccion'] ?? '') : '';

    error_log("Logo path: " . ($logo_path ?: 'NO ENCONTRADO'));
    error_log("Logo existe: " . ($logo_exists ? 'SÍ' : 'NO'));

    // ============================================
    // CREAR SPREADSHEET
    // ============================================
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Comprobantes');

    // Configuración para impresión
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setTop(0.5);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setBottom(0.5);

    // ============================================
    // ENCABEZADO CON LOGO
    // ============================================

    $fila_actual = 1;

    // Agregar logo si existe
    $tiene_logo = false;
    if ($logo_exists && !empty($logo_path)) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa');
            $drawing->setDescription('Logo de la empresa');
            $drawing->setPath($logo_path);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A' . $fila_actual);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);

            $sheet->getRowDimension($fila_actual)->setRowHeight(50);
            $sheet->getColumnDimension('A')->setWidth(15);
            $tiene_logo = true;
            error_log("Logo insertado correctamente");
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    }

    // Título del reporte
    $titulo_reporte = $nombre_empresa . ' - REPORTE DE COMPROBANTES ELECTRÓNICOS';
    
    if ($tiene_logo) {
        $sheet->setCellValue('C1', $titulo_reporte);
        $sheet->mergeCells('C1:M1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:M2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        $sheet->setCellValue('A1', $titulo_reporte);
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:M2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':M' . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    $fila_actual++;

    $fila_actual += 1;

    // ============================================
    // INFORMACIÓN DE FILTROS
    // ============================================

    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    // Período
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        
        $fecha_inicio_formateada = date('d/m/Y', strtotime($fecha_inicio));
        $fecha_fin_formateada = date('d/m/Y', strtotime($fecha_fin));
        $sheet->setCellValue('C' . $fila_actual, $fecha_inicio_formateada . ' al ' . $fecha_fin_formateada);
        $fila_actual++;
    }

    // Tipo de comprobante
    if (!empty($tp_comprobante)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'COMPROBANTE:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        
        $mapa_comprobantes = [
            '1' => 'FACTURA',
            '3' => 'BOLETA'
        ];
        $tipo_comp_texto = isset($mapa_comprobantes[$tp_comprobante]) ? $mapa_comprobantes[$tp_comprobante] : 'TODOS';
        $sheet->setCellValue('C' . $fila_actual, $tipo_comp_texto);
        $fila_actual++;
    }

    // Tipo de venta
    if (!empty($tipo_venta)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TIPO VENTA:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        
        $mapa_tipos = [
            '1' => 'PASAJE',
            '2' => 'ENCOMIENDA',
            '3' => 'FACTURADOR',
            '4' => 'NOTAS BLOQUE',
            'OTRO' => 'OTRO'
        ];
        $tipo_texto = isset($mapa_tipos[$tipo_venta]) ? $mapa_tipos[$tipo_venta] : 'TODOS';
        $sheet->setCellValue('C' . $fila_actual, $tipo_texto);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE LA TABLA
    // ============================================

    $encabezados = [
        'A' => 'EMISIÓN',
        'B' => 'CLIENTE',
        'C' => 'DOCUMENTO',
        'D' => 'NÚMERO',
        'E' => 'ESTADO',
        'F' => 'TIPO',
        'G' => 'T. GRAVADO',
        'H' => 'T. EXONERADO',
        'I' => 'T. INAFECTO',
        'J' => 'T. IGV',
        'K' => 'TOTAL',
        'L' => 'ESTADO SUNAT',
        'M' => 'FORMA PAGO'
    ];

    $anchos_columnas = [
        'A' => 15,
        'B' => 35,
        'C' => 15,
        'D' => 18,
        'E' => 12,
        'F' => 15,
        'G' => 13,
        'H' => 13,
        'I' => 13,
        'J' => 12,
        'K' => 13,
        'L' => 15,
        'M' => 15
    ];

    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);

        $estilo_encabezado = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ];

        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(30);
    $fila_actual++;

    // ============================================
    // LLENAR DATOS
    // ============================================

    $total_gravado = 0;
    $total_exonerado = 0;
    $total_inafecto = 0;
    $total_igv = 0;
    $total_general = 0;

    if (!empty($datos) && is_array($datos)) {
        foreach ($datos as $index => $registro) {
            // Asegurarse de que $registro es un array
            if (!is_array($registro)) {
                continue;
            }
            
            // Formatear fecha
            $fecha = !empty($registro['fecha_emision']) ? 
                date('d/m/Y', strtotime($registro['fecha_emision'])) : '';
            
            $sheet->setCellValue('A' . $fila_actual, $fecha);
            
            $cliente = trim(($registro['cliente'] ?? '') . ' ' . ($registro['cliente_apellidos'] ?? ''));
            if (empty($cliente) && isset($registro['cliente_nombres'])) {
                $cliente = trim(($registro['cliente_nombres'] ?? '') . ' ' . ($registro['cliente_apellidos'] ?? ''));
            }
            $sheet->setCellValue('B' . $fila_actual, $cliente);
            
            $documento = $registro['documento_cliente'] ?? $registro['cliente_num_docu'] ?? '';
            $sheet->setCellValue('C' . $fila_actual, $documento);
            
            $numero = $registro['numero_comprobante'] ?? '';
            if (empty($numero) && isset($registro['serie'])) {
                $numero = ($registro['serie'] ?? '') ? ($registro['serie'] . '-' . ($registro['correlativo'] ?? '')) : '---';
            }
            $sheet->setCellValue('D' . $fila_actual, $numero);
            
            $sheet->setCellValue('E' . $fila_actual, $registro['estado'] ?? '');
            
            $tipo_display = $registro['tipo_venta'] ?? $registro['tipo_display'] ?? $registro['tipo_venta_nombre'] ?? 'OTRO';
            $sheet->setCellValue('F' . $fila_actual, $tipo_display);
            
            $sheet->setCellValue('G' . $fila_actual, floatval($registro['op_gravada'] ?? 0));
            $sheet->setCellValue('H' . $fila_actual, floatval($registro['op_exonerada'] ?? 0));
            $sheet->setCellValue('I' . $fila_actual, floatval($registro['op_inafecta'] ?? 0));
            $sheet->setCellValue('J' . $fila_actual, floatval($registro['op_igv'] ?? 0));
            $sheet->setCellValue('K' . $fila_actual, floatval($registro['total'] ?? 0));
            
            $estado_sunat_texto = $registro['estado_sunat'] ?? '';
            if (empty($estado_sunat_texto) && isset($registro['envio_sunat'])) {
                $estado_sunat = intval($registro['envio_sunat'] ?? 0);
                $estado_sunat_texto = $estado_sunat == 1 ? 'ENVIADO' : 
                                      ($estado_sunat == 2 ? 'RESUMEN' : 'SIN ENVIAR');
            }
            $sheet->setCellValue('L' . $fila_actual, $estado_sunat_texto);
            
            $sheet->setCellValue('M' . $fila_actual, $registro['forma_pago'] ?? '');

            // Formato numérico
            $sheet->getStyle('G' . $fila_actual . ':K' . $fila_actual)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            // Wrap text para cliente
            $sheet->getStyle('B' . $fila_actual)->getAlignment()->setWrapText(true);

            // Bordes con color alternado
            $bordeStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            
            if ($index % 2 == 1) {
                $bordeStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9F9F9']
                ];
            }
            
            $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)
                ->applyFromArray($bordeStyle);

            // Acumular totales
            $total_gravado += floatval($registro['op_gravada'] ?? 0);
            $total_exonerado += floatval($registro['op_exonerada'] ?? 0);
            $total_inafecto += floatval($registro['op_inafecta'] ?? 0);
            $total_igv += floatval($registro['op_igv'] ?? 0);
            $total_general += floatval($registro['total'] ?? 0);

            $fila_actual++;
        }

        // Fila de totales
        $fila_totales = $fila_actual;
        
        $sheet->setCellValue('F' . $fila_totales, 'TOTALES:');
        $sheet->getStyle('F' . $fila_totales)->getFont()->setBold(true);
        $sheet->setCellValue('G' . $fila_totales, $total_gravado);
        $sheet->setCellValue('H' . $fila_totales, $total_exonerado);
        $sheet->setCellValue('I' . $fila_totales, $total_inafecto);
        $sheet->setCellValue('J' . $fila_totales, $total_igv);
        $sheet->setCellValue('K' . $fila_totales, $total_general);

        $sheet->getStyle('G' . $fila_totales . ':K' . $fila_totales)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        
        $sheet->getStyle('G' . $fila_totales . ':K' . $fila_totales)
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('F' . $fila_totales . ':K' . $fila_totales)
            ->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);

        // Auto-filtro
        $fila_encabezados = $fila_totales - count($datos) - 1;
        $sheet->setAutoFilter('A' . $fila_encabezados . ':M' . ($fila_totales - 1));

    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':M' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)
            ->getFont()
            ->setItalic(true);
    }

    // ============================================
    // PREPARAR DESCARGA
    // ============================================

    $fecha_inicio_str = !empty($fecha_inicio) ? str_replace('-', '', $fecha_inicio) : 'todo';
    $fecha_fin_str = !empty($fecha_fin) ? str_replace('-', '', $fecha_fin) : 'todo';
    $filename = "comprobantes_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    ob_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Error en exportación Excel: " . $e->getMessage());
    
    ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte Excel: ' . $e->getMessage()
    ]);
    exit;
}
?>