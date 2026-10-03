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
    // IMPORTANTE: Las propiedades están directamente en $this, NO en $this->view
    $datos = $this->data ?? [];
    $empresa = $this->empresa_info ?? [];
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Desembarques');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO
    // ============================================
    $fila_actual = 1;
    $tiene_logo = false;
    $ultima_columna = 'J';

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
        } catch (Exception $e) {}
    }

    $columna_inicio_titulo = $tiene_logo ? 'C' : 'A';

    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - HISTORIAL DE DESEMBARQUES');
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - HISTORIAL DE DESEMBARQUES');
        $sheet->mergeCells('A1:' . $ultima_columna . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    $sheet->setCellValue('A' . ($fila_actual + 1), 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . ($fila_actual + 1) . ':' . $ultima_columna . ($fila_actual + 1));
    $sheet->getStyle('A' . ($fila_actual + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila_actual += 3;

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

    if (!empty($this->origen)) {
        $nombre_origen = $this->nombre_origen ?? $this->origen;
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen);
        $fila_actual++;
    }

    if (!empty($this->destino)) {
        $nombre_destino = $this->nombre_destino ?? $this->destino;
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE TABLA
    // ============================================
    $encabezados = [
        'A' => 'N°',
        'B' => 'FECHA',
        'C' => 'HORA',
        'D' => 'ORIGEN',
        'E' => 'DESTINO',
        'F' => 'CONDUCTOR',
        'G' => 'LICENCIA',
        'H' => 'VEHÍCULO',
        'I' => 'USUARIO',
        'J' => 'TOTAL ENCOMIENDAS'
    ];

    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
    ];

    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);
        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(25);
    $fila_actual++;

    // ============================================
    // DATOS
    // ============================================
    if (!empty($datos)) {
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila_actual, $row['id'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, $row['hora'] ?? '');
            $sheet->setCellValue('D' . $fila_actual, $row['origen_nombre'] ?? $row['origen'] ?? '');
            $sheet->setCellValue('E' . $fila_actual, $row['destino_nombre'] ?? $row['destino'] ?? '');
            
            // SOLUCIÓN 1: Usar el campo correcto 'conductor' que viene de la BD
            $conductor = $row['conductor'] ?? '';
            $sheet->setCellValue('F' . $fila_actual, $conductor);
            $sheet->setCellValue('G' . $fila_actual, $row['licencia'] ?? ''); // Usar el campo 'licencia' que viene de la BD
            $sheet->setCellValue('H' . $fila_actual, $row['vehiculo_placa'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['usuario'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['total_encomiendas'] ?? 0);

            // Aplicar bordes
            $sheet->getStyle('A' . $fila_actual . ':J' . $fila_actual)
                  ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
            
            // Alineación vertical centrada para todas las celdas
            $sheet->getStyle('A' . $fila_actual . ':J' . $fila_actual)
                  ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            
            // Permitir texto envolvente para celdas con texto largo
            $sheet->getStyle('D' . $fila_actual . ':I' . $fila_actual)
                  ->getAlignment()->setWrapText(true);

            $fila_actual++;
        }
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros');
        $sheet->mergeCells('A' . $fila_actual . ':J' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $fila_actual++;
    }

    // ============================================
    // SOLUCIÓN 2: AUTO-AJUSTE DE COLUMNAS
    // ============================================
    // Aplicar auto-ajuste a todas las columnas después de llenar los datos
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Pero establecer un ancho máximo para evitar columnas demasiado anchas
    foreach (range('A', 'J') as $col) {
        $currentWidth = $sheet->getColumnDimension($col)->getWidth();
        if ($currentWidth > 50) { // Si es muy ancho, limitar a 50
            $sheet->getColumnDimension($col)->setWidth(50);
        }
    }

    // Auto-filtro
    $fila_encabezados = $fila_actual - (empty($datos) ? 0 : count($datos)) - 1;
    $sheet->setAutoFilter('A' . $fila_encabezados . ':J' . ($fila_actual - 1));

    // Descarga
    $fecha_inicio_str = $this->fecha_inicio ? str_replace('-', '', $this->fecha_inicio) : 'todo';
    $fecha_fin_str = $this->fecha_fin ? str_replace('-', '', $this->fecha_fin) : 'todo';
    $filename = "desembarques_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

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