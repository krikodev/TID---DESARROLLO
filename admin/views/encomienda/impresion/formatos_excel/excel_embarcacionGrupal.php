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
    // CORREGIDO: usar $this-> en lugar de $this->view->
    $datos = $this->data ?? [];
    $empresa = $this->empresa_info ?? [];
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';

    // Estos valores vienen pre-resueltos desde el controlador
    $nombre_origen = $this->nombre_origen ?? '';
    $nombre_destino = $this->nombre_destino ?? '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('EmbarcacionesGrupales');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO
    // ============================================
    $fila_actual = 1;
    $tiene_logo = false;
    $ultima_columna = 'K';

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
        }
    }

    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - EMBARCACIONES GRUPALES');
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - EMBARCACIONES GRUPALES');
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
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen ?: $this->origen);
        $fila_actual++;
    }

    if (!empty($this->destino)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino ?: $this->destino);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE TABLA
    // ============================================
    $encabezados = [
        'A' => 'N° GRUPO',
        'B' => 'FECHA',
        'C' => 'HORA',
        'D' => 'ORIGEN',
        'E' => 'DESTINO',
        'F' => 'CONDUCTOR',
        'G' => 'LICENCIA',
        'H' => 'VEHÍCULO',
        'I' => 'USUARIO',
        'J' => 'TOTAL EMBARCACIONES',
        'K' => 'TOTAL ENCOMIENDAS'
    ];

    // ANCHOS DE COLUMNA AJUSTADOS para mejor visualización
    $anchos_columnas = [
        'A' => 12,
        'B' => 15,
        'C' => 12,
        'D' => 35, // Aumentado
        'E' => 35, // Aumentado
        'F' => 40, // Aumentado
        'G' => 20, // Aumentado
        'H' => 20, // Aumentado
        'I' => 40, // Aumentado
        'J' => 20,
        'K' => 20
    ];

    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ];

    $fila_encabezados = $fila_actual;
    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);
        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(25);
    $fila_actual++;

    // ============================================
    // DATOS - CORREGIDO: usando los campos correctos del modelo
    // ============================================
    if (!empty($datos)) {
        foreach ($datos as $row) {
            // Usar los campos que vienen del modelo getDataExportEmbarcacionGrupal()
            $sheet->setCellValue('A' . $fila_actual, $row['id_grupo'] ?? $row['id'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, $row['hora'] ?? '');
            $sheet->setCellValue('D' . $fila_actual, $row['origen_nombre'] ?? $row['origen'] ?? '');
            $sheet->setCellValue('E' . $fila_actual, $row['destino_nombre'] ?? $row['destino'] ?? '');
            
            // Conductor puede venir como 'conductor' o concatenado
            $conductor = $row['conductor'] ?? '';
            if (empty($conductor) && isset($row['nombre_conductor'])) {
                $conductor = trim(($row['nombre_conductor'] ?? '') . ' ' . ($row['apellidos_conductor'] ?? ''));
            }
            $sheet->setCellValue('F' . $fila_actual, $conductor);
            $sheet->setCellValue('G' . $fila_actual, $row['conductor_licencia'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $row['vehiculo_placa'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['usuario'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['total_embarcaciones'] ?? 0);
            $sheet->setCellValue('K' . $fila_actual, $row['total_encomiendas'] ?? 0);

            // APLICAR AJUSTE DE TEXTO a las columnas que necesitas (D, E, F, G, H, I, J)
            $columnas_ajuste = ['D', 'E', 'F', 'G', 'H', 'I', 'J', "K"];
            foreach ($columnas_ajuste as $col) {
                $sheet->getStyle($col . $fila_actual)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP); // Alinear arriba
            }

            // Establecer altura automática para la fila
            $sheet->getRowDimension($fila_actual)->setRowHeight(-1);

            // Bordes para toda la fila
            $sheet->getStyle('A' . $fila_actual . ':K' . $fila_actual)
                ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            $fila_actual++;
        }
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros');
        $sheet->mergeCells('A' . $fila_actual . ':K' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $fila_actual++;
    }

    // Auto-filtro
    if (!empty($datos)) {
        $sheet->setAutoFilter('A' . $fila_encabezados . ':K' . ($fila_actual - 1));
    }

    // Descarga
    $fecha_inicio_str = $this->fecha_inicio ? str_replace('-', '', $this->fecha_inicio) : 'todo';
    $fecha_fin_str = $this->fecha_fin ? str_replace('-', '', $this->fecha_fin) : 'todo';
    $filename = "embarcaciones_grupales_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: text/plain');
    echo "Error al generar el Excel: " . $e->getMessage();
    exit;
}