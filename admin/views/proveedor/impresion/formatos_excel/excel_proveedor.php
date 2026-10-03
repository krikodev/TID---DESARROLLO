<?php

ob_start();

require __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Proveedores');

$empresa = $this->empresa_info;

$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', $empresa['nombre']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'RUC: ' . $empresa['ruc']);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:I3');
$sheet->setCellValue('A3', $empresa['direccion']);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A5:I5');
$sheet->setCellValue('A5', 'REPORTE DE PROVEEDORES');
$sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13);
$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A6', 'Fecha de generación:');
$sheet->setCellValue('B6', date('d/m/Y H:i:s'));

$headers = [
    'TERMINAL',
    'PROVEEDOR',
    'TIPO DOCUMENTO',
    'NRO. DOCUMENTO',
    'CELULAR',
    'EMAIL',
    'DIRECCIÓN',
    'UBIGEO',
    'ESTADO'
];

$columnas = range('A', 'I');

foreach ($headers as $i => $header) {
    $sheet->setCellValue($columnas[$i] . '8', $header);
}

$headerStyle = $sheet->getStyle('A8:I8');

$headerStyle->getFont()->setBold(true);
$headerStyle->getFont()->getColor()->setRGB('FFFFFF');

$headerStyle->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setRGB('043B75');

$headerStyle->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);

$headerStyle->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

$fila = 9;

foreach ($this->registros as $registro) {

    $sheet->setCellValue('A' . $fila, $registro['terminal']);
    $sheet->setCellValue('B' . $fila, $registro['proveedor']);
    $sheet->setCellValue('C' . $fila, $registro['tp_docu']);

    $sheet->setCellValueExplicit(
        'D' . $fila,
        $registro['num_docu'],
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
    );

    $sheet->setCellValue('E' . $fila, $registro['celular']);
    $sheet->setCellValue('F' . $fila, $registro['email']);
    $sheet->setCellValue('G' . $fila, $registro['direccion']);
    $sheet->setCellValue('H' . $fila, $registro['ubigeo']);

    $sheet->setCellValue(
        'I' . $fila,
        $registro['estado'] == 1 ? 'Habilitado' : 'Deshabilitado'
    );

    $fila++;
}

if ($fila > 9) {

    $sheet->getStyle('A9:I' . ($fila - 1))
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    $sheet->getStyle('A9:I' . ($fila - 1))
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER);
}

$anchos = [
    'A' => 18,
    'B' => 30,
    'C' => 20,
    'D' => 18,
    'E' => 15,
    'F' => 30,
    'G' => 35,
    'H' => 15,
    'I' => 18
];

foreach ($anchos as $columna => $ancho) {
    $sheet->getColumnDimension($columna)->setWidth($ancho);
}

$sheet->getStyle('A:I')
    ->getAlignment()
    ->setVertical(Alignment::VERTICAL_CENTER);

$sheet->freezePane('A9');

$filename = 'proveedores_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;