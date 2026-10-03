<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
$pdf = new TCPDF(
    'P',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->SetCreator('SIGTRANS');
$pdf->SetAuthor('SIGTRANS');
$pdf->SetTitle('Reporte de Proveedores');

$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 8);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$empresa = $this->empresa_info;

/*
|--------------------------------------------------------------------------
| ENCABEZADO EMPRESA
|--------------------------------------------------------------------------
*/

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(
    0,
    5,
    $empresa['nombre'],
    0,
    1,
    'C'
);

$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(
    0,
    4,
    'RUC: ' . $empresa['ruc'],
    0,
    1,
    'C'
);

$pdf->Cell(
    0,
    4,
    $empresa['direccion'],
    0,
    1,
    'C'
);

$pdf->Ln(3);

/*
|--------------------------------------------------------------------------
| TÍTULO
|--------------------------------------------------------------------------
*/

$pdf->SetFont('helvetica', 'B', 12);

$pdf->Cell(
    0,
    6,
    'REPORTE DE PROVEEDORES',
    0,
    1,
    'C'
);

$pdf->Ln(2);

/*
|--------------------------------------------------------------------------
| INFORMACIÓN DEL REPORTE
|--------------------------------------------------------------------------
*/

$pdf->SetFillColor(240, 240, 240);

$pdf->SetFont('helvetica', '', 7);

$pdf->Cell(
    0,
    5,
    'Fecha de generación: ' . date('d/m/Y H:i:s'),
    0,
    1,
    'L',
    true
);

/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$filtros = [];

if (!empty($this->terminal)) {
    $filtros[] = 'Terminal: ' . $this->terminal;
}

if (!empty($this->tp_docu)) {
    $filtros[] = 'Tipo documento: ' . $this->tp_docu;
}

if ($this->estado !== '') {
    $filtros[] = 'Estado: ' .
        ($this->estado == '1'
            ? 'Habilitado'
            : 'Deshabilitado');
}

if (!empty($this->search)) {
    $filtros[] = 'Búsqueda: ' . $this->search;
}

if (!empty($filtros)) {

    $pdf->SetFont('helvetica', '', 6);

    $pdf->MultiCell(
        0,
        5,
        implode(' | ', $filtros),
        0,
        'L',
        true,
        1
    );

    $pdf->Ln(2);
}

/*
|--------------------------------------------------------------------------
| TABLA
|--------------------------------------------------------------------------
*/

$widths = [
    18,
    31,
    19,
    20,
    16,
    28,
    29,
    16,
    17
];

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

/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA ENCABEZADO DE TABLA
|--------------------------------------------------------------------------
*/

function imprimirCabeceraProveedor($pdf, $headers, $widths)
{
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetFillColor(4, 59, 117);
    $pdf->SetTextColor(255, 255, 255);

    foreach ($headers as $i => $header) {

        $pdf->MultiCell(
            $widths[$i],
            7,
            $header,
            1,
            'C',
            true,
            0,
            '',
            '',
            true,
            0,
            false,
            true,
            7,
            'M'
        );
    }

    $pdf->Ln(7);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 6);
}

imprimirCabeceraProveedor(
    $pdf,
    $headers,
    $widths
);

/*
|--------------------------------------------------------------------------
| DATOS
|--------------------------------------------------------------------------
*/

foreach ($this->registros as $registro) {

    $fila = [
        $registro['terminal'] ?? '',
        $registro['proveedor'] ?? '',
        $registro['tp_docu'] ?? '',
        $registro['num_docu'] ?? '',
        $registro['celular'] ?? '',
        $registro['email'] ?? '',
        $registro['direccion'] ?? '',
        $registro['ubigeo'] ?? '',
        $registro['estado'] == 1
            ? 'Habilitado'
            : 'Deshabilitado'
    ];

    /*
    |--------------------------------------------------------------------------
    | CALCULAR ALTURA DE LA FILA
    |--------------------------------------------------------------------------
    */

    $alturaFila = 6;

    foreach ($fila as $i => $valor) {

        $altura = $pdf->getStringHeight(
            $widths[$i],
            $valor
        );

        $alturaFila = max(
            $alturaFila,
            $altura
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SALTO DE PÁGINA
    |--------------------------------------------------------------------------
    */

    if (
        $pdf->GetY() + $alturaFila >
        $pdf->getPageHeight() - 10
    ) {

        $pdf->AddPage();

        imprimirCabeceraProveedor(
            $pdf,
            $headers,
            $widths
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMPRIMIR FILA
    |--------------------------------------------------------------------------
    */

    foreach ($fila as $i => $valor) {

        $alineacion = 'L';

        if (
            $i === 3 ||
            $i === 4 ||
            $i === 7 ||
            $i === 8
        ) {
            $alineacion = 'C';
        }

        $pdf->MultiCell(
            $widths[$i],
            $alturaFila,
            $valor,
            1,
            $alineacion,
            false,
            0,
            '',
            '',
            true,
            0,
            false,
            true,
            $alturaFila,
            'M'
        );
    }

    $pdf->Ln($alturaFila);
}

/*
|--------------------------------------------------------------------------
| SALIDA
|--------------------------------------------------------------------------
*/

$pdf->Output(
    'proveedores_' . date('Ymd_His') . '.pdf',
    'I'
);

exit;