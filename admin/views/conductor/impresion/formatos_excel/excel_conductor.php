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

$sheet->setTitle('Conductores');

$empresa = $this->empresa_info;

$sheet->mergeCells('A1:K1');

$sheet->setCellValue('A1', $empresa['nombre']);

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:K2');

$sheet->setCellValue('A2', 'RUC: ' . $empresa['ruc']);

$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:K3');

$sheet->setCellValue('A3', $empresa['direccion']);

$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A5:K5');

$sheet->setCellValue('A5', 'REPORTE DE CONDUCTORES / COPILOTOS');

$sheet->getStyle('A5')->getFont()->setBold(true)->setSize(13);

$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A6', 'Fecha de generación:');

$sheet->setCellValue('B6', date('d/m/Y H:i:s'));

$headers = [
    'TERMINAL',
    'DNI',
    'PERSONAL',
    'LICENCIA',
    'CATEGORÍA',
    'ESTADO CIVIL',
    'CELULAR',
    'DIRECCIÓN',
    'UBIGEO',
    'TIPO DE USUARIO',
    'ESTADO'
];

$columnas = range('A', 'K');

foreach ($headers as $i => $header) {

    $sheet->setCellValue($columnas[$i] . '8', $header);
}

$headerStyle = $sheet->getStyle('A8:K8');

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

    $sheet->setCellValue(
        'A' . $fila,
        $registro['terminal']
    );

    $sheet->setCellValueExplicit(
        'B' . $fila,
        $registro['num_docu'],
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
    );

    $sheet->setCellValue(
        'C' . $fila,
        $registro['personal']
    );

    $sheet->setCellValue(
        'D' . $fila,
        $registro['licencia']
    );

    $sheet->setCellValue(
        'E' . $fila,
        $registro['categoria']
    );

    $sheet->setCellValue(
        'F' . $fila,
        $registro['estado_civil']
    );

    $sheet->setCellValue(
        'G' . $fila,
        $registro['celular']
    );

    $sheet->setCellValue(
        'H' . $fila,
        $registro['direccion']
    );

    $sheet->setCellValue(
        'I' . $fila,
        $registro['ubigeo']
    );

    $sheet->setCellValue(
        'J' . $fila,
        $registro['tp_usuario']
    );

    $sheet->setCellValue(
        'K' . $fila,
        $registro['estado'] == 1
            ? 'Habilitado'
            : 'Deshabilitado'
    );

    $fila++;
}

if ($fila > 9) {

    $sheet->getStyle('A9:K' . ($fila - 1))
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    $sheet->getStyle('A9:K' . ($fila - 1))
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER);
}

$anchos = [
    'A' => 18,
    'B' => 15,
    'C' => 30,
    'D' => 18,
    'E' => 15,
    'F' => 18,
    'G' => 15,
    'H' => 35,
    'I' => 15,
    'J' => 20,
    'K' => 18
];

foreach ($anchos as $columna => $ancho) {

    $sheet->getColumnDimension($columna)->setWidth($ancho);
}

$sheet->getStyle('A:K')
    ->getAlignment()
    ->setVertical(Alignment::VERTICAL_CENTER);

$sheet->freezePane('A9');

$filename = 'conductores_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header('Content-Disposition: attachment; filename="' . $filename . '"');

header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;
