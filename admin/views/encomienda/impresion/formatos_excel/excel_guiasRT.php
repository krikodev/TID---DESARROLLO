<?php
ob_start();
require __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // CAMBIO IMPORTANTE: Usar $this en lugar de $this->view
    $datos = $this->data ?? [];
    $empresa_info = $this->empresa_info ?? [];
    
    // Extraer datos de empresa_info
    $logo_path = $empresa_info['logo_absolute_path'] ?? '';
    $logo_exists = $empresa_info['logo_exists'] ?? false;
    $nombre_empresa = $empresa_info['nombre'] ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa = $empresa_info['ruc'] ?? '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('GuiasRemision');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO
    // ============================================
    $fila_actual = 1;
    $tiene_logo = false;
    $ultima_columna = 'P';

    if ($logo_exists && !empty($logo_path) && file_exists($logo_path)) {
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
            error_log("Error al cargar logo: " . $e->getMessage());
        }
    }

    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - GUÍAS DE REMISIÓN - TRANSPORTISTA');
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - GUÍAS DE REMISIÓN - TRANSPORTISTA');
        $sheet->mergeCells('A1:' . $ultima_columna . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    $sheet->setCellValue('A' . ($fila_actual + 1), 'RUC: ' . $ruc_empresa);
    $sheet->mergeCells('A' . ($fila_actual + 1) . ':' . $ultima_columna . ($fila_actual + 1));
    $sheet->getStyle('A' . ($fila_actual + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $sheet->setCellValue('A' . ($fila_actual + 2), 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . ($fila_actual + 2) . ':' . $ultima_columna . ($fila_actual + 2));
    $sheet->getStyle('A' . ($fila_actual + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila_actual += 4;

    // ============================================
    // FILTROS
    // ============================================
    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    if (!empty($this->fecha_inicio) && !empty($this->fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $fecha_inicio_formateada = date('d/m/Y', strtotime($this->fecha_inicio));
        $fecha_fin_formateada = date('d/m/Y', strtotime($this->fecha_fin));
        $sheet->setCellValue('C' . $fila_actual, $fecha_inicio_formateada . ' al ' . $fecha_fin_formateada);
        $fila_actual++;
    }

    if (!empty($this->partida)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'D. PARTIDA:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $this->nombre_partida ?? $this->partida);
        $fila_actual++;
    }

    if (!empty($this->destino)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'D. DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $this->nombre_destino ?? $this->destino);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE TABLA
    // ============================================
    $encabezados = [
        'A' => 'N° GUÍA',
        'B' => 'FECHA EMISIÓN',
        'C' => 'VEHÍCULO',
        'D' => 'CONDUCTOR',
        'E' => 'DOC. CONDUCTOR',
        'F' => 'LICENCIA',
        'G' => 'D. PARTIDA',
        'H' => 'DIRECCIÓN PARTIDA',
        'I' => 'D. DESTINO',
        'J' => 'DIRECCIÓN DESTINO',
        'K' => 'REMITENTE',
        'L' => 'DOC. REMITENTE',
        'M' => 'PESO (KG)',
        'N' => 'ESTADO SUNAT',
        'O' => 'MENSAJE SUNAT'
    ];

    $anchos_columnas = [
        'A' => 18, 'B' => 15, 'C' => 20, 'D' => 30, 'E' => 18,
        'F' => 15, 'G' => 25, 'H' => 30, 'I' => 25, 'J' => 30,
        'K' => 30, 'L' => 20, 'M' => 12, 'N' => 20, 'O' => 40
    ];

    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER, 
            'wrapText' => true, 
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];

    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);
        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(35);
    $fila_actual++;
    
    // Guardar la fila de inicio de datos
    $fila_inicio_datos = $fila_actual;
    
    // ============================================
    // DATOS
    // ============================================
    // Columnas que necesitan wrap text
    $columnas_con_wrap = ['C', 'D', 'G', 'H', 'I', 'J', 'K', 'L', 'N', 'O'];
    
    if (!empty($datos)) {
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila_actual, $row['numero_guia'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha_emision'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, $row['vehiculo_placa'] ?? '');
            
            $conductor = trim(($row['conductor_nombres'] ?? '') . ' ' . ($row['conductor_apellidos'] ?? ''));
            $sheet->setCellValue('D' . $fila_actual, $conductor);
            
            $sheet->setCellValue('E' . $fila_actual, $row['conductor_dni'] ?? '');
            $sheet->setCellValue('F' . $fila_actual, $row['conductor_licencia'] ?? '');
            $sheet->setCellValue('G' . $fila_actual, $row['partida_ubigeo_completo'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $row['partida_direccion'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['destino_ubigeo_completo'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['destino_direccion'] ?? '');
            
            $remitente = trim(($row['remitente_nombres'] ?? '') . ' ' . ($row['remitente_apellidos'] ?? ''));
            $sheet->setCellValue('K' . $fila_actual, $remitente);
            
            $sheet->setCellValue('L' . $fila_actual, $row['remitente_doc'] ?? '');
            $sheet->setCellValue('M' . $fila_actual, $row['peso'] ?? 0);
            $sheet->setCellValue('N' . $fila_actual, $row['estado_sunat'] ?? '');
            $sheet->setCellValue('O' . $fila_actual, $row['mensaje_sunat'] ?? '');

            // Aplicar wrap text SOLO a esta fila para las columnas específicas
            foreach ($columnas_con_wrap as $columna) {
                $sheet->getStyle($columna . $fila_actual)->getAlignment()->setWrapText(true);
            }

            // Altura automática
            $sheet->getRowDimension($fila_actual)->setRowHeight(-1);

            // Aplicar bordes
            $sheet->getStyle('A' . $fila_actual . ':O' . $fila_actual)
                  ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            $fila_actual++;
        }
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros');
        $sheet->mergeCells('A' . $fila_actual . ':O' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $sheet->getRowDimension($fila_actual)->setRowHeight(-1);
    }

    // Auto-filtro (solo si hay datos)
    if (!empty($datos)) {
        $fila_encabezados = $fila_inicio_datos - 1;
        $sheet->setAutoFilter('A' . $fila_encabezados . ':O' . ($fila_actual - 1));
    }

    // Descarga
    $fecha_inicio_str = $this->fecha_inicio ? str_replace('-', '', $this->fecha_inicio) : 'todo';
    $fecha_fin_str = $this->fecha_fin ? str_replace('-', '', $this->fecha_fin) : 'todo';
    $filename = "guias_remision_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}