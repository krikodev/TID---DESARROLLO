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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // AHORA OBTENEMOS LOS DATOS DE LA VISTA (NO DEL MODELO DIRECTAMENTE)
    $datos = $this->datos_excel ?? [];
    $empresa = $this->empresa_excel ?? [];
    $filtros = $this->filtros_excel ?? $_POST; // Usar $_POST como fallback
    
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA';
    $ruc_empresa = $empresa['num_docu'] ?? '';

    // Crear Spreadsheet
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
    // ENCABEZADO CON LOGO E INFORMACIÓN DE EMPRESA
    // ============================================

    $fila_actual = 1;

    // Agregar logo si existe
    $tiene_logo = false;
    if ($logo_exists && !empty($logo_path) && file_exists($logo_path)) {
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
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    }

    // Título del reporte
    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE COMPROBANTES');
        $sheet->mergeCells('C1:O1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Información de la empresa
        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($empresa['direccion'])) $info_empresa[] = $empresa['direccion'];
        if (!empty($empresa['telefono'])) $info_empresa[] = 'Tel: ' . $empresa['telefono'];

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:O2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE COMPROBANTES');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($empresa['direccion'])) $info_empresa[] = $empresa['direccion'];
        if (!empty($empresa['telefono'])) $info_empresa[] = 'Tel: ' . $empresa['telefono'];

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:O2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación y filtros aplicados
    $fecha_generacion = 'Generado: ' . date('d/m/Y H:i:s');
    
    // Agregar filtros aplicados
    $filtros_aplicados = [];
    if (!empty($_POST['filtro_fecha_desde']) && !empty($_POST['filtro_fecha_hasta'])) {
        $filtros_aplicados[] = 'Período: ' . $_POST['filtro_fecha_desde'] . ' al ' . $_POST['filtro_fecha_hasta'];
    }
    if (!empty($_POST['filtro_tipo_comprobante'])) {
        $tipos = ['1' => 'Factura', '2' => 'Nota Venta', '3' => 'Boleta'];
        $filtros_aplicados[] = 'Tipo: ' . ($tipos[$_POST['filtro_tipo_comprobante']] ?? $_POST['filtro_tipo_comprobante']);
    }
    if (!empty($_POST['filtro_estado'])) {
        $filtros_aplicados[] = 'Estado: ' . $_POST['filtro_estado'];
    }
    if (!empty($_POST['search']['value'])) {
        $filtros_aplicados[] = 'Búsqueda: ' . $_POST['search']['value'];
    }

    $texto_filtros = !empty($filtros_aplicados) ? ' | ' . implode(' | ', $filtros_aplicados) : '';

    $sheet->setCellValue('A' . $fila_actual, $fecha_generacion . $texto_filtros);
    $sheet->mergeCells('A' . $fila_actual . ':O' . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE LA TABLA
    // ============================================

    $encabezados = [
        'A' => 'FECHA EMISIÓN',
        'B' => 'COMPROBANTE',
        'C' => 'TIPO',
        'D' => 'CLIENTE',
        'E' => 'RUC/DNI',
        'F' => 'OP. GRAVADA',
        'G' => 'OP. EXONERADA',
        'H' => 'OP. INAFECTA',
        'I' => 'IGV',
        'J' => 'ICBPER',
        'K' => 'TOTAL',
        'L' => 'ESTADO',
        'M' => 'ESTADO SUNAT',
        'N' => 'DETRACCIÓN',
        'O' => 'DESC. SUNAT'
    ];

    // Anchos de columna - MODIFICADO: Aumentado ancho de TIPO (C) a 25
    $anchos = [
        'A' => 15,  // Fecha
        'B' => 17,  // Comprobante
        'C' => 20,  // TIPO (Aumentado de 20 a 25)
        'D' => 40,  // Cliente
        'E' => 15,  // RUC/DNI
        'F' => 14,  // Gravada
        'G' => 14,  // Exonerada
        'H' => 14,  // Inafecta
        'I' => 12,  // IGV
        'J' => 10,  // ICBPER
        'K' => 14,  // Total
        'L' => 12,  // Estado
        'M' => 15,  // Estado SUNAT
        'N' => 12,  // Detracción
        'O' => 30   // Descripción SUNAT
    ];

    // Aplicar anchos
    foreach ($anchos as $col => $ancho) {
        $sheet->getColumnDimension($col)->setWidth($ancho);
    }

    // Aplicar estilos a encabezados
    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
    ];

    foreach ($encabezados as $col => $titulo) {
        $sheet->setCellValue($col . $fila_actual, $titulo);
        $sheet->getStyle($col . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(25);
    $fila_actual++;

    // ============================================
    // LLENAR DATOS
    // ============================================

    $total_gravada = 0;
    $total_exonerada = 0;
    $total_inafecta = 0;
    $total_igv = 0;
    $total_icbper = 0;
    $total_general = 0;

    if (!empty($datos)) {
        foreach ($datos as $row) {
            // Formatear fecha
            $fecha = !empty($row['fecha_emision']) ? date('d/m/Y', strtotime($row['fecha_emision'])) : '';
            
            // Serie-Correlativo
            $numero = $row['serie'] . '-' . $row['correlativo'];
            
            // Tipo comprobante
            $tipo = $row['tipo_comprobante'] ?? '';
            
            // Cliente
            $cliente = $row['cliente'] ?? '';
            
            // RUC/DNI
            $ruc = $row['ruc_cliente'] ?? '';
            
            // Montos
            $es_anulado = ($row['estado'] === 'ANULADO');
            $gravada = $es_anulado ? 0 : floatval($row['op_gravada'] ?? 0);
            $exonerada = $es_anulado ? 0 : floatval($row['op_exonerada'] ?? 0);
            $inafecta = $es_anulado ? 0 : floatval($row['op_inafecta'] ?? 0);
            $igv = $es_anulado ? 0 : floatval($row['op_igv'] ?? 0);
            $icbper = $es_anulado ? 0 : floatval($row['icbper'] ?? 0);
            $total = $es_anulado ? 0 : floatval($row['total'] ?? 0);
            
            // Estado
            $estado = $row['estado'] ?? '';
            
            // Estado SUNAT
            $estado_sunat = '';
            switch ($row['envio_sunat'] ?? '') {
                case '0': $estado_sunat = 'PENDIENTE'; break;
                case '1': $estado_sunat = 'ACEPTADO'; break;
                case '2': $estado_sunat = 'RECHAZADO'; break;
                case '3': $estado_sunat = 'EXCEPCIÓN'; break;
                default: $estado_sunat = 'DESCONOCIDO';
            }
            
            // Detracción
            $detraccion = ($row['afecta_detraccion'] ?? 0) == 1 ? 'SÍ' : 'NO';
            
            // Descripción SUNAT
            $desc_sunat = $row['descrip_cdr_sunat'] ?? '';

            // Insertar datos
            $sheet->setCellValue('A' . $fila_actual, $fecha);
            $sheet->setCellValue('B' . $fila_actual, $numero);
            $sheet->setCellValue('C' . $fila_actual, $tipo);
            $sheet->setCellValue('D' . $fila_actual, $cliente);
            $sheet->setCellValue('E' . $fila_actual, $ruc);
            $sheet->setCellValue('F' . $fila_actual, $gravada);
            $sheet->setCellValue('G' . $fila_actual, $exonerada);
            $sheet->setCellValue('H' . $fila_actual, $inafecta);
            $sheet->setCellValue('I' . $fila_actual, $igv);
            $sheet->setCellValue('J' . $fila_actual, $icbper);
            $sheet->setCellValue('K' . $fila_actual, $total);
            $sheet->setCellValue('L' . $fila_actual, $estado);
            $sheet->setCellValue('M' . $fila_actual, $estado_sunat);
            $sheet->setCellValue('N' . $fila_actual, $detraccion);
            $sheet->setCellValue('O' . $fila_actual, $desc_sunat);

            // APLICAR ALINEACIONES PERSONALIZADAS
            // Columnas centradas (todas excepto D y O)
            $columnas_centradas = ['A', 'B', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
            foreach ($columnas_centradas as $col) {
                $sheet->getStyle($col . $fila_actual)
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);
            }

            // Columna CLIENTE (C) - Izquierda con wrap text
            $sheet->getStyle('C' . $fila_actual)
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setVertical(Alignment::VERTICAL_CENTER)
                  ->setWrapText(true);
            
            // Columna CLIENTE (D) - Izquierda con wrap text
            $sheet->getStyle('D' . $fila_actual)
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setVertical(Alignment::VERTICAL_CENTER)
                  ->setWrapText(true);
            
            // Columna DESC. SUNAT (O) - Izquierda con wrap text
            $sheet->getStyle('O' . $fila_actual)
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setVertical(Alignment::VERTICAL_CENTER)
                  ->setWrapText(true);

            // Formato numérico para montos
            $sheet->getStyle('F' . $fila_actual . ':K' . $fila_actual)
                  ->getNumberFormat()
                  ->setFormatCode('#,##0.00');

            // Bordes para cada fila
            $sheet->getStyle('A' . $fila_actual . ':O' . $fila_actual)
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Acumular totales
            $total_gravada += $gravada;
            $total_exonerada += $exonerada;
            $total_inafecta += $inafecta;
            $total_igv += $igv;
            $total_icbper += $icbper;
            $total_general += $total;

            $fila_actual++;
        }

        // Fila de totales
        $fila_totales = $fila_actual;
        
        $sheet->setCellValue('E' . $fila_totales, 'TOTALES:');
        $sheet->getStyle('E' . $fila_totales)->getFont()->setBold(true);
        $sheet->getStyle('E' . $fila_totales)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        $sheet->setCellValue('F' . $fila_totales, $total_gravada);
        $sheet->setCellValue('G' . $fila_totales, $total_exonerada);
        $sheet->setCellValue('H' . $fila_totales, $total_inafecta);
        $sheet->setCellValue('I' . $fila_totales, $total_igv);
        $sheet->setCellValue('J' . $fila_totales, $total_icbper);
        $sheet->setCellValue('K' . $fila_totales, $total_general);

        // Formato para totales
        $sheet->getStyle('F' . $fila_totales . ':K' . $fila_totales)
              ->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F' . $fila_totales . ':K' . $fila_totales)
              ->getFont()->setBold(true);
        $sheet->getStyle('F' . $fila_totales . ':K' . $fila_totales)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bordes y fondo para totales
        $sheet->getStyle('E' . $fila_totales . ':K' . $fila_totales)
              ->applyFromArray([
                  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
              ]);

        // NOTA: Se ha ELIMINADO el auto-filtro para que no aparezcan los controles de filtro
        
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':O' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // ============================================
    // PREPARAR DESCARGA
    // ============================================

    $filename = 'comprobantes_' . date('Ymd_His') . '.xlsx';

    // Limpiar buffer
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
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar Excel: ' . $e->getMessage()
    ]);
    exit;
}