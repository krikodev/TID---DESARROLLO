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
    // CORRECCIÓN: Los datos están directamente en $this
    // ============================================
    error_log("=== REPORTE NOTAS DE VENTA - Parámetros recibidos ===");
    error_log("Fecha Inicio: " . ($this->fecha_inicio ?? 'vacío'));
    error_log("Fecha Fin: " . ($this->fecha_fin ?? 'vacío'));
    error_log("Origen: " . ($this->origen ?? 'vacío'));
    error_log("Destino: " . ($this->destino ?? 'vacío'));

    // Los datos de la empresa YA vienen completos desde el controlador
    // No necesitas llamar a $this->model->getEmpresaInfo()
    $empresa = $this->empresa_info ?? [];
    $datos = $this->data ?? [];

    // Extraer datos de la empresa (ya vienen procesados del controlador)
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa = $empresa['ruc'] ?? '';
    $direccion_empresa = $empresa['direccion'] ?? '';
    $telefono_empresa = $empresa['telefono'] ?? '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('NotasVenta');

    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO
    // ============================================
    $fila_actual = 1;
    $tiene_logo = false;
    $ultima_columna = 'M'; // Columnas A-M (13 columnas)

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
            error_log("Error al insertar logo: " . $e->getMessage());
        }
    }

    $columna_inicio_titulo = $tiene_logo ? 'C' : 'A';

    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE ENCOMIENDAS - NOTAS DE VENTA');
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa))
            $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa))
            $info_empresa[] = $direccion_empresa;
        if (!empty($telefono_empresa))
            $info_empresa[] = 'Tel: ' . $telefono_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:' . $ultima_columna . '2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE ENCOMIENDAS - NOTAS DE VENTA');
        $sheet->mergeCells('A1:' . $ultima_columna . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa))
            $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa))
            $info_empresa[] = $direccion_empresa;
        if (!empty($telefono_empresa))
            $info_empresa[] = 'Tel: ' . $telefono_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:' . $ultima_columna . '2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':' . $ultima_columna . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $fila_actual++;
    $fila_actual += 1;

    // ============================================
    // FILTROS DE BÚSQUEDA
    // ============================================
    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    // CORRECCIÓN: Para obtener el nombre de la terminal, necesitamos pasar los IDs
    // pero como no tenemos acceso al modelo aquí, mostraremos los IDs directamente
    // o podrías pasar los nombres desde el controlador

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
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        // Usar el nombre si está disponible, si no, mostrar el ID como fallback
        $nombre_origen = !empty($this->nombre_origen) ? $this->nombre_origen : $this->origen;
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen);
        $fila_actual++;
    }

    if (!empty($this->destino)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        // Usar el nombre si está disponible, si no, mostrar el ID como fallback
        $nombre_destino = !empty($this->nombre_destino) ? $this->nombre_destino : $this->destino;
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE TABLA
    // ============================================
    $encabezados = [
        'A' => 'N° COMPROBANTE',
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
        'M' => 'COMPROBANTE PAGO'
    ];

    $anchos_columnas = [
        'A' => 18,
        'B' => 15,
        'C' => 35,
        'D' => 18,
        'E' => 35,
        'F' => 18,
        'G' => 25,
        'H' => 25,
        'I' => 15,
        'J' => 15,
        'K' => 12,
        'L' => 15,
        'M' => 18
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

    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);
        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(30);
    $fila_actual++;

    // ============================================
    // DATOS
    // ============================================
    $total_general = 0;
    $columnas_wrap_text = ['C', 'E', 'G', 'H'];

    if (!empty($datos)) {
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila_actual, $row['numero_comprobante'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha_emision'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, ($row['remitente_nombres'] ?? '') . ' ' . ($row['remitente_apellidos'] ?? ''));
            $sheet->setCellValue('D' . $fila_actual, $row['remitente_num_docu'] ?? '');
            $sheet->setCellValue('E' . $fila_actual, ($row['destinatario_nombres'] ?? '') . ' ' . ($row['destinatario_apellidos'] ?? ''));
            $sheet->setCellValue('F' . $fila_actual, $row['destinatario_num_docu'] ?? '');
            $sheet->setCellValue('G' . $fila_actual, $row['terminal_origen'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $row['terminal_destino'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['fecha_salida'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['estado_envio'] ?? '');
            $sheet->setCellValue('K' . $fila_actual, $row['total'] ?? 0);
            $sheet->setCellValue('L' . $fila_actual, $row['estado_venta'] ?? '');
            $sheet->setCellValue('M' . $fila_actual, $row['comprobante_pago'] ?? '');

            $sheet->getStyle('K' . $fila_actual)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            foreach ($columnas_wrap_text as $columna) {
                $sheet->getStyle($columna . $fila_actual)->getAlignment()->setWrapText(true);
            }

            $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)
                ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            $total_general += floatval($row['total'] ?? 0);
            $fila_actual++;
        }

        // Totales
        $sheet->setCellValue('J' . $fila_actual, 'TOTAL GENERAL:');
        $sheet->getStyle('J' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('K' . $fila_actual, $total_general);
        $sheet->getStyle('K' . $fila_actual)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle('K' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('J' . $fila_actual . ':K' . $fila_actual)
            ->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros');
        $sheet->mergeCells('A' . $fila_actual . ':M' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // Auto-filtro
    $fila_encabezados = $fila_actual - (empty($datos) ? 0 : count($datos)) - 1;
    $sheet->setAutoFilter('A' . $fila_encabezados . ':M' . ($fila_actual - 1));

    // Descarga
    $fecha_inicio_str = !empty($this->fecha_inicio) ? str_replace('-', '', $this->fecha_inicio) : 'todo';
    $fecha_fin_str = !empty($this->fecha_fin) ? str_replace('-', '', $this->fecha_fin) : 'todo';
    $filename = "notas_venta_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

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