<?php
ob_start();
require __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // ============================================
    // LAS VARIABLES ESTÁN EN $this->view
    // ============================================
    if (!isset($this) || !isset($this->view)) {
        // Si no existe $this, intentamos con variables globales
        global $view_data;
        $view_data = $this ?? null;
        
        throw new Exception('No se ha inicializado la vista correctamente. $this->view no está disponible.');
    }
    
    // Obtener datos con validación
    $datos = isset($this->view->data) && is_array($this->view->data) ? $this->view->data : [];
    $empresa = isset($this->view->empresa_info) && is_array($this->view->empresa_info) ? $this->view->empresa_info : [];
    $terminales_info = isset($this->view->terminales_info) && is_array($this->view->terminales_info) ? $this->view->terminales_info : [];
    
    // Filtros
    $fecha_inicio = $this->view->fecha_inicio ?? '';
    $fecha_fin = $this->view->fecha_fin ?? '';
    $origen = $this->view->origen ?? '';
    $destino = $this->view->destino ?? '';

    // Datos de empresa con valores por defecto
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = !empty($logo_path) && file_exists($logo_path);
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa = $empresa['ruc'] ?? $empresa['num_docu'] ?? '';

    // Crear nuevo archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('GuiasTransportista');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO
    // ============================================
    $fila_actual = 1;
    $tiene_logo = false;
    $ultima_columna = 'M';

    // Intentar insertar logo si existe
    if ($logo_exists) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa');
            $drawing->setPath($logo_path);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A' . $fila_actual);
            $drawing->setWorksheet($sheet);
            $sheet->getRowDimension($fila_actual)->setRowHeight(50);
            $sheet->getColumnDimension('A')->setWidth(15);
            $tiene_logo = true;
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            // Continuar sin logo
        }
    }

    // Título del reporte
    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE GUÍAS TRANSPORTISTAS');
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE GUÍAS TRANSPORTISTAS');
        $sheet->mergeCells('A1:' . $ultima_columna . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // RUC
    $sheet->setCellValue('A' . ($fila_actual + 1), 'RUC: ' . $ruc_empresa);
    $sheet->mergeCells('A' . ($fila_actual + 1) . ':' . $ultima_columna . ($fila_actual + 1));
    $sheet->getStyle('A' . ($fila_actual + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Fecha de generación
    $sheet->setCellValue('A' . ($fila_actual + 2), 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . ($fila_actual + 2) . ':' . $ultima_columna . ($fila_actual + 2));
    $sheet->getStyle('A' . ($fila_actual + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila_actual += 4;

    // ============================================
    // FILTROS APLICADOS
    // ============================================
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        
        try {
            $fecha_inicio_formateada = date('d/m/Y', strtotime($fecha_inicio));
            $fecha_fin_formateada = date('d/m/Y', strtotime($fecha_fin));
        } catch (Exception $e) {
            $fecha_inicio_formateada = $fecha_inicio;
            $fecha_fin_formateada = $fecha_fin;
        }
        
        $sheet->setCellValue('C' . $fila_actual, $fecha_inicio_formateada . ' al ' . $fecha_fin_formateada);
        $fila_actual++;
    }

    if (!empty($origen)) {
        $nombre_origen = $terminales_info['origen'] ?? $origen;
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen);
        $fila_actual++;
    }

    if (!empty($destino)) {
        $nombre_destino = $terminales_info['destino'] ?? $destino;
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino);
        $fila_actual++;
    }

    // Si no hay filtros, mostrar mensaje
    if (empty($fecha_inicio) && empty($fecha_fin) && empty($origen) && empty($destino)) {
        $sheet->mergeCells('A' . $fila_actual . ':C' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'SIN FILTROS - MOSTRANDO TODOS LOS REGISTROS');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $fila_actual++;
    }

    $fila_actual += 1;

    // ============================================
    // ENCABEZADOS DE TABLA
    // ============================================
    $encabezados = [
        'A' => 'N° GUÍA',
        'B' => 'FECHA EMISIÓN',
        'C' => 'REMITENTE',
        'D' => 'DOC. REMITENTE',
        'E' => 'DESTINATARIO',
        'F' => 'DOC. DESTINATARIO',
        'G' => 'ORIGEN',
        'H' => 'DESTINO',
        'I' => 'FECHA SALIDA',
        'J' => 'ESTADO ENVÍO',
        'K' => 'TOTAL',
        'L' => 'ESTADO VENTA',
        'M' => 'ESTADO SUNAT'
    ];

    $anchos_columnas = [
        'A' => 18, 'B' => 15, 'C' => 35, 'D' => 18, 'E' => 35, 'F' => 18,
        'G' => 25, 'H' => 25, 'I' => 15, 'J' => 15, 'K' => 12, 'L' => 15, 'M' => 15
    ];

    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER]
    ];

    $fila_encabezados = $fila_actual;
    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_encabezados, $titulo);
        $sheet->getStyle($columna . $fila_encabezados)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_encabezados)->setRowHeight(30);
    $fila_actual = $fila_encabezados + 1;

    // ============================================
    // DATOS
    // ============================================
    $total_general = 0;
    $fila_inicio_datos = $fila_actual;

    if (!empty($datos)) {
        foreach ($datos as $row) {
            // Asegurarse de que row sea un array
            if (!is_array($row)) continue;
            
            // Formatear nombres completos
            $remitente = trim(($row['remitente_nombres'] ?? '') . ' ' . ($row['remitente_apellidos'] ?? ''));
            $destinatario = trim(($row['destinatario_nombres'] ?? '') . ' ' . ($row['destinatario_apellidos'] ?? ''));
            
            $sheet->setCellValue('A' . $fila_actual, $row['numero_comprobante'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha_emision'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, $remitente ?: 'N/A');
            $sheet->setCellValue('D' . $fila_actual, $row['remitente_num_docu'] ?? '');
            $sheet->setCellValue('E' . $fila_actual, $destinatario ?: 'N/A');
            $sheet->setCellValue('F' . $fila_actual, $row['destinatario_num_docu'] ?? '');
            $sheet->setCellValue('G' . $fila_actual, $row['terminal_origen'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $row['terminal_destino'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['fecha_salida'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['estado_envio'] ?? '');
            $sheet->setCellValue('K' . $fila_actual, $row['total'] ?? 0);
            $sheet->setCellValue('L' . $fila_actual, $row['estado_venta'] ?? '');
            $sheet->setCellValue('M' . $fila_actual, $row['estado_sunat'] ?? '');

            // Formato de moneda para la columna total
            $sheet->getStyle('K' . $fila_actual)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            
            // Bordes para la fila
            $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $total_general += floatval($row['total'] ?? 0);
            $fila_actual++;
        }

        // Fila de totales
        if ($total_general > 0 || count($datos) > 0) {
            $sheet->setCellValue('J' . $fila_actual, 'TOTAL GENERAL:');
            $sheet->getStyle('J' . $fila_actual)->getFont()->setBold(true);
            $sheet->setCellValue('K' . $fila_actual, $total_general);
            $sheet->getStyle('K' . $fila_actual)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $sheet->getStyle('K' . $fila_actual)->getFont()->setBold(true);
            $sheet->getStyle('J' . $fila_actual . ':K' . $fila_actual)
                  ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
            
            // Aplicar bordes a la fila de totales
            $sheet->getStyle('J' . $fila_actual . ':K' . $fila_actual)
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                  
            // Aplicar auto-filtro (solo si hay datos)
            $sheet->setAutoFilter('A' . $fila_encabezados . ':M' . ($fila_actual - 1));
        }
        
    } else {
        // Mensaje cuando no hay datos
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':M' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // ============================================
    // DESCARGAR ARCHIVO
    // ============================================
    // Generar nombre de archivo
    $fecha_inicio_str = $fecha_inicio ? preg_replace('/[^0-9]/', '', $fecha_inicio) : 'todo';
    $fecha_fin_str = $fecha_fin ? preg_replace('/[^0-9]/', '', $fecha_fin) : 'todo';
    $filename = "guias_transportista_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    // Limpiar cualquier salida previa
    if (ob_get_length()) ob_clean();
    
    // Cabeceras para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Guardar y enviar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Limpiar buffer
    if (ob_get_length()) ob_clean();
    
    // Registrar error
    error_log("Error en exportación Excel: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    // Mostrar error (para depuración)
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h3>Error al generar Excel</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Revise el log de errores para más detalles.</p>';
    exit;
}