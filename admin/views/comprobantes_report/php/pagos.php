<?php
// Incluir config.php para obtener NAME_BUSINESS
require_once __DIR__ . '/../../../config.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

// Verificar salida previa
if (ob_get_length() > 0) {
    $output = ob_get_contents();
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Salida no deseada detectada: ' . $output]);
    exit;
}

// Verificar Composer autoload
if (!file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error: No se encontró vendor/autoload.php en la raíz del proyecto.']);
    exit;
}

// Incluir PhpSpreadsheet
require __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

try {
    $model = new Comprobantes_ReportModel();
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error al instanciar el modelo: ' . $e->getMessage()]);
    exit;
}

// Obtener parámetros
$terminal = $this->view->terminal ?? '';
$tp_comprobante = $this->view->tp_comprobante ?? 'TODO';
$fecha_inicio = $this->view->fecha_inicio ?? '';
$fecha_fin = $this->view->fecha_fin ?? '';

// Validar fechas
if ($fecha_inicio && $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    if ($fin < $inicio) {
        ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['estado' => false, 'message' => 'La fecha final no puede ser anterior a la fecha de inicio.']);
        exit;
    }
} elseif ($fecha_fin && !$fecha_inicio) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Es necesaria una fecha de inicio para usar la fecha final.']);
    exit;
}

// Obtener información de la terminal
try {
    $terminal_info = $terminal && $terminal !== '' ? $model->getTerminalById($terminal) : ['ruc' => '20123456789', 'razon_social' => 'TRANSPORTES XYZ S.A.C.'];
    $ruc = $terminal_info['ruc'] ?? '20123456789';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error al obtener información de la terminal: ' . $e->getMessage()]);
    exit;
}

// Obtener información de la empresa
try {
    $empresa = $model->getEmpresa();
    $logo_path = $empresa['logo'] ?? '';
    $ruc_empresa = $empresa['num_docu'] ?? '20123456789';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error al obtener información de la empresa: ' . $e->getMessage()]);
    exit;
}

// Obtener datos de comprobantes
try {
    $data = [
        'terminal' => $terminal,
        'tp_comprobante' => $tp_comprobante,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin
    ];
    $comprobantes = $model->getDataExportacion($data);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage()]);
    exit;
}

// Crear Spreadsheet
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('RegistroVentas');

    // Configuración para impresión (orientación horizontal, márgenes)
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setTop(0.5);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setBottom(0.5);

    // Agregar logo
    $logoRow = 3;
    if (!empty($logo_path) && file_exists(__DIR__ . '/../../../' . $logo_path)) {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la empresa');
        $drawing->setPath(__DIR__ . '/../../../' . $logo_path);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A' . $logoRow);
        $drawing->setWorksheet($sheet);
        $sheet->getRowDimension($logoRow)->setRowHeight(60);
    } else {
    }

    // Título general (fila 1, merge completo)
    $sheet->setCellValue('A1', 'FORMATO 14.1: REGISTRO DE VENTAS E INGRESOS');
    $sheet->mergeCells('A1:W1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezado superior (período, RUC, razón social)
    $sheet->setCellValue('A2', 'PERIODO:');
    $periodo = '';
    if ($fecha_inicio) {
        try {
            $inicio = new DateTime($fecha_inicio);
            $periodo .= $inicio->format('d/m/Y');
        } catch (Exception $e) {
            $periodo .= 'Fecha inicio inválida';
        }
    } else {
        $periodo .= 'Sin fecha';
    }
    $periodo .= ' - ';
    if ($fecha_fin) {
        try {
            $fin = new DateTime($fecha_fin);
            $periodo .= $fin->format('d/m/Y');
        } catch (Exception $e) {
            $periodo .= 'Fecha fin inválida';
        }
    } else {
        $periodo .= 'Sin fecha';
    }
    $sheet->setCellValue('B2', $periodo);
    $sheet->setCellValue('A3', 'RUC:');
    $sheet->setCellValue('B3', $ruc_empresa);
    $sheet->setCellValue('A4', 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:');
    $sheet->getStyle('A4')->getAlignment()->setWrapText(true);
    $sheet->setCellValue('B4', defined('NAME_BUSINESS') ? NAME_BUSINESS : 'EMPRESA NO DEFINIDA');

    // Encabezados de la tabla (tres filas: 5, 6 y 7)
    $mainHeaders = [
        'A5' => 'NÚMERO CORRELATIVO DEL REGISTRO / CÓDIGO ÚNICO DE LA OPERACIÓN',
        'B5' => 'FECHA DE EMISIÓN DEL COMPROBANTE O DOCUMENTO',
        'C5' => 'FECHA DE VENCIMIENTO Y/O PAGO',
        'D5' => 'COMPROBANTE DE PAGO O DOCUMENTO',
        'G5' => 'INFORMACIÓN DEL CLIENTE',
        'I5' => 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL',
        'J5' => 'VALOR FACTURADO DE LA EXPORTACIÓN',
        'K5' => 'BASE IMPONIBLE DE LA OPERACIÓN GRAVADA',
        'L5' => 'IMPORTE TOTAL DE LA OPERACIÓN',
        'N5' => 'ISC',
        'O5' => 'IGV',
        'P5' => 'IPM',
        'Q5' => 'OTROS TRIBUTOS Y CARGOS QUE NO FORMAN PARTE DE LA BASE IMPONIBLE',
        'R5' => 'IMPORTE TOTAL DEL COMPROBANTE DE PAGO',
        'S5' => 'TIPO DE CAMBIO',
        'T5' => 'REFERENCIA DEL COMPROBANTE DE PAGO O DOCUMENTO ORIGINAL QUE SE MODIFICA'
    ];
    foreach ($mainHeaders as $cell => $val) {
        $sheet->setCellValue($cell, $val);
    }

    // Fila 6: Subtítulos
    $subHeaders = [
        'D6' => 'TIPO (SEGÚN TABLA 10)',
        'E6' => 'N° SERIE O N° DE SERIE DE LA MÁQUINA REGISTRADORA',
        'F6' => 'NÚMERO',
        'G6' => 'DOCUMENTO DE IDENTIDAD',
        'I6' => 'APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL',
        'L6' => 'EXONERADA',
        'M6' => 'INAFECTA',
        'T6' => 'FECHA',
        'U6' => 'TIPO (TABLA 10)',
        'V6' => 'SERIE',
        'W6' => 'N° DEL COMPROBANTE DE PAGO O DOCUMENTO'
    ];
    foreach ($subHeaders as $cell => $val) {
        $sheet->setCellValue($cell, $val);
    }

    // Fila 7: Subtítulos adicionales
    $subSubHeaders = [
        'G7' => 'TIPO (TABLA 2)',
        'H7' => 'NÚMERO'
    ];
    foreach ($subSubHeaders as $cell => $val) {
        $sheet->setCellValue($cell, $val);
    }

    // Rellenar celdas vacías en merges
    for ($col = 'A'; $col <= 'W'; $col++) {
        for ($rowNum = 5; $rowNum <= 7; $rowNum++) {
            $cell = $col . $rowNum;
            if (!isset($mainHeaders[$cell]) && !isset($subHeaders[$cell]) && !isset($subSubHeaders[$cell])) {
                $sheet->setCellValue($cell, '');
            }
        }
    }

    // Merges para las filas 5, 6 y 7
    $sheet->mergeCells('A5:A7');
    $sheet->mergeCells('B5:B7');
    $sheet->mergeCells('C5:C7');
    $sheet->mergeCells('D5:F5');
    $sheet->mergeCells('D6:D7');
    $sheet->mergeCells('E6:E7');
    $sheet->mergeCells('G5:H5');
    $sheet->mergeCells('G6:H6');
    $sheet->mergeCells('I5:I7');
    $sheet->mergeCells('J5:J7');
    $sheet->mergeCells('K5:K7');
    $sheet->mergeCells('L5:M5');
    $sheet->mergeCells('N5:N7');
    $sheet->mergeCells('O5:O7');
    $sheet->mergeCells('P5:P7');
    $sheet->mergeCells('Q5:Q7');
    $sheet->mergeCells('R5:R7');
    $sheet->mergeCells('S5:S7');
    $sheet->mergeCells('T5:W5');
    $sheet->mergeCells('W6:W7');

    // Estilos para headers (filas 5-7)
    $sheet->getStyle('A5:W7')->getFont()->setBold(true);
    $sheet->getStyle('A5:W7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');
    $sheet->getStyle('A5:W7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A5:W7')->getAlignment()->setWrapText(true);

    // Ajustar altura de filas para legibilidad
    $sheet->getRowDimension(5)->setRowHeight(30);
    $sheet->getRowDimension(6)->setRowHeight(30);
    $sheet->getRowDimension(7)->setRowHeight(20);
    $sheet->getRowDimension(4)->setRowHeight(30);

    // Establecer dimensiones de columnas
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(40);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(10);
    $sheet->getColumnDimension('E')->setWidth(22);
    $sheet->getColumnDimension('F')->setWidth(10);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(12);
    $sheet->getColumnDimension('J')->setWidth(15);
    $sheet->getColumnDimension('K')->setWidth(12);
    $sheet->getColumnDimension('L')->setWidth(12);
    $sheet->getColumnDimension('M')->setWidth(12);
    $sheet->getColumnDimension('N')->setWidth(10);
    $sheet->getColumnDimension('O')->setWidth(10);
    $sheet->getColumnDimension('P')->setWidth(10);
    $sheet->getColumnDimension('Q')->setWidth(18);
    $sheet->getColumnDimension('R')->setWidth(15);
    $sheet->getColumnDimension('S')->setWidth(10);
    $sheet->getColumnDimension('T')->setWidth(15);
    $sheet->getColumnDimension('U')->setWidth(10);
    $sheet->getColumnDimension('V')->setWidth(10);
    $sheet->getColumnDimension('W')->setWidth(22);

    // Llenar datos
    if (empty($comprobantes)) {
        $sheet->setCellValue('A8', 'No se encontraron datos para los filtros seleccionados.');
    } else {
        $row = 8;
        $total_exportacion = 0;
        $total_base_imponible = 0;
        $total_exonerada = 0;
        $total_inafecta = 0;
        $total_isc = 0;
        $total_igv = 0;
        $total_ipm = 0;
        $total_otros_tributos = 0;
        $total_importe = 0;

        foreach ($comprobantes as $index => $comprobante) {
            $tipo_comprobante = '';
            switch ($comprobante['id_tp_comprobante'] ?? '') {
                case '1':
                    $tipo_comprobante = '01';
                    break;
                case '3':
                    $tipo_comprobante = '03';
                    break;
                case '7':
                    $tipo_comprobante = '07';
                    break;
                case '8':
                    $tipo_comprobante = '08';
                    break;
                case '2':
                    $tipo_comprobante = '12';
                    break;
            }

            // Determinar el tipo de comprobante de referencia
            $ref_tipo_comprobante = '';
            if (!empty($comprobante['ref_tipo_comprobante'])) {
                // Mapear el ID del tipo de comprobante a código SUNAT
                switch ($comprobante['ref_tipo_comprobante']) {
                    case '1':
                        $ref_tipo_comprobante = '01'; // Factura
                        break;
                    case '3':
                        $ref_tipo_comprobante = '03'; // Boleta
                        break;
                    case '2':
                        $ref_tipo_comprobante = '12'; // Nota de venta
                        break;
                    default:
                        $ref_tipo_comprobante = $comprobante['ref_tipo_comprobante'];
                }
            }

            // Llenado de celdas
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $comprobante['fecha_emision'] ? date('d/m/Y', strtotime($comprobante['fecha_emision'])) : '');
            $sheet->setCellValue('C' . $row, $comprobante['fecha_emision'] ? date('d/m/Y', strtotime($comprobante['fecha_emision'])) : '');
            $sheet->setCellValue('D' . $row, $tipo_comprobante);
            $sheet->setCellValue('E' . $row, $comprobante['serie'] ?? '');
            $sheet->setCellValue('F' . $row, $comprobante['correlativo'] ?? '');
            $sheet->setCellValue('G' . $row, $comprobante['tipo_docu'] ?? '');
            $sheet->setCellValue('H' . $row, $comprobante['cliente_num_docu'] ?? '');
            $sheet->setCellValue('I' . $row, $comprobante['cliente'] ?? '');
            $sheet->setCellValue('J' . $row, $comprobante['valor_exportacion'] ?? 0);
            $sheet->setCellValue('K' . $row, $comprobante['op_gravada'] ?? 0);
            $sheet->setCellValue('L' . $row, $comprobante['op_exonerada'] ?? 0);
            $sheet->setCellValue('M' . $row, $comprobante['op_inafecta'] ?? 0);
            $sheet->setCellValue('N' . $row, $comprobante['isc'] ?? 0);
            $sheet->setCellValue('O' . $row, $comprobante['igv'] ?? 0);
            $sheet->setCellValue('P' . $row, $comprobante['ipm'] ?? 0);
            $sheet->setCellValue('Q' . $row, $comprobante['otros_tributos'] ?? 0);
            $sheet->setCellValue('R' . $row, $comprobante['total'] ?? 0);
            $sheet->setCellValue('S' . $row, $comprobante['codigo_moneda'] ?? 'PEN');
            if (in_array($comprobante['id_tp_comprobante'] ?? '', ['7', '8']) && !empty($comprobante['ref_serie'])) {
                $sheet->setCellValue('T' . $row, $comprobante['ref_fecha'] ? date('d/m/Y', strtotime($comprobante['ref_fecha'])) : '');
                $sheet->setCellValue('U' . $row, $ref_tipo_comprobante);
                $sheet->setCellValue('V' . $row, $comprobante['ref_serie'] ?? '');
                $sheet->setCellValue('W' . $row, $comprobante['ref_correlativo'] ?? '');
            } else {
                // Si no es nota o no tiene referencia, dejar vacío
                $sheet->setCellValue('T' . $row, '');
                $sheet->setCellValue('U' . $row, '');
                $sheet->setCellValue('V' . $row, '');
                $sheet->setCellValue('W' . $row, '');
            }
            // Formato numérico (2 decimales) y fechas
            $sheet->getStyle('J' . $row . ':R' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $sheet->getStyle('S' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            // Formato para celdas de referencia (texto)
            $sheet->getStyle('T' . $row . ':W' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            // Acumular totales
            $total_exportacion += $comprobante['valor_exportacion'] ?? 0;
            $total_base_imponible += $comprobante['op_gravada'] ?? 0;
            $total_exonerada += $comprobante['op_exonerada'] ?? 0;
            $total_inafecta += $comprobante['op_inafecta'] ?? 0;
            $total_isc += $comprobante['isc'] ?? 0;
            $total_igv += $comprobante['igv'] ?? 0;
            $total_ipm += $comprobante['ipm'] ?? 0;
            $total_otros_tributos += $comprobante['otros_tributos'] ?? 0;
            $total_importe += $comprobante['total'] ?? 0;

            $row++;
        }

        // Fila de totales
        $totalRow = $row;
        $sheet->setCellValue('A' . $totalRow, 'TOTALES');
        $sheet->mergeCells('A' . $totalRow . ':I' . $totalRow);
        $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
        $sheet->setCellValue('J' . $totalRow, $total_exportacion);
        $sheet->setCellValue('K' . $totalRow, $total_base_imponible);
        $sheet->setCellValue('L' . $totalRow, $total_exonerada);
        $sheet->setCellValue('M' . $totalRow, $total_inafecta);
        $sheet->setCellValue('N' . $totalRow, $total_isc);
        $sheet->setCellValue('O' . $totalRow, $total_igv);
        $sheet->setCellValue('P' . $totalRow, $total_ipm);
        $sheet->setCellValue('Q' . $totalRow, $total_otros_tributos);
        $sheet->setCellValue('R' . $totalRow, $total_importe);

        $sheet->getStyle('J' . $totalRow . ':R' . $totalRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('A8:W' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:W' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A8:W' . $totalRow)->getAlignment()->setWrapText(true);
    }

    // Exportar archivo
    $writer = new Xlsx($spreadsheet);
    $filename = "formato_14_1_registro_ventas_completo_" . date('Ymd_His') . ".xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    ob_end_clean();
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['estado' => false, 'message' => 'Error al generar el archivo: ' . $e->getMessage()]);
    exit;
}