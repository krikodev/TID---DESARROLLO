<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');

define("PAGE_WIDTH", 210);
define("PAGE_HEIGHT", 297);
define("MARGIN_LEFT", 8);
define("MARGIN_RIGHT", 8);
define("MARGIN_TOP", 8);
define("MARGIN_BOTTOM", 8);
define("WIDTH_UTIL", PAGE_WIDTH - MARGIN_LEFT - MARGIN_RIGHT);

class TCPDF_A4_Conductor extends TCPDF
{
    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);

        $this->SetMargins(MARGIN_LEFT, MARGIN_TOP, MARGIN_RIGHT);
        $this->SetAutoPageBreak(true, MARGIN_BOTTOM);
        $this->setPrintHeader(false);
        $this->SetPrintFooter(false);
    }

    public function Footer()
    {
        $this->SetY(-8);
        $this->SetFont('helvetica', '', 7);

        $this->Cell(
            0,
            5,
            'Reporte de conductores / copilotos | Página ' .
                $this->getAliasNumPage() .
                ' de ' .
                $this->getAliasNbPages(),
            0,
            0,
            'C'
        );
    }
}

$pdf = new TCPDF_A4_Conductor();

$pdf->AddPage();

$registros = $this->registros ?? [];
$empresa = $this->empresa_info ?? [];

/* TÍTULO */

$pdf->SetFont('helvetica', 'B', 14);

$pdf->SetXY(MARGIN_LEFT, MARGIN_TOP);

$pdf->Cell(
    WIDTH_UTIL,
    8,
    'REPORTE DE CONDUCTORES / COPILOTOS',
    0,
    1,
    'C'
);

/* CABECERA */

$pdf->SetFillColor(245, 245, 245);

$pdf->Rect(
    MARGIN_LEFT,
    MARGIN_TOP + 10,
    WIDTH_UTIL,
    32,
    'F'
);

$col_logo = 35;
$col_empresa = 95;
$col_titulo = WIDTH_UTIL - $col_logo - $col_empresa;

/* LOGO */

$logo = $empresa['logo'] ?? '';

if (!empty($logo)) {

    $nombreLogo = basename($logo);

    $logoPath = 'D:/xampp/htdocs/tid-transporte/img/admin/' . $nombreLogo;

    if (file_exists($logoPath)) {

        $pdf->Image(
            $logoPath,
            MARGIN_LEFT + 5,
            MARGIN_TOP + 15,
            35,
            22,
            '',
            '',
            '',
            false,
            300
        );
    }
}

/* DATOS EMPRESA */

$pdf->SetXY(
    MARGIN_LEFT + $col_logo,
    MARGIN_TOP + 14
);

$pdf->SetFont('helvetica', 'B', 9);

$nombre_empresa = $empresa['nombre'] ?? '';

$pdf->Cell(
    $col_empresa,
    6,
    $nombre_empresa,
    0,
    1,
    'L'
);

$pdf->SetX(MARGIN_LEFT + $col_logo);

$pdf->SetFont('helvetica', '', 8);

$ruc = $empresa['ruc'] ?? '';

$pdf->Cell(
    $col_empresa,
    5,
    'RUC: ' . $ruc,
    0,
    1,
    'L'
);

$pdf->SetX(MARGIN_LEFT + $col_logo);

$direccion = $empresa['direccion'] ?? '';

$pdf->Cell(
    $col_empresa,
    5,
    'DOM. FISCAL: ' . $direccion,
    0,
    1,
    'L'
);

/* FECHA */

$x_titulo = MARGIN_LEFT + $col_logo + $col_empresa;

$pdf->SetFont('helvetica', '', 8);

$pdf->SetXY(
    $x_titulo,
    MARGIN_TOP + 28
);

$pdf->Cell(
    $col_titulo,
    5,
    'Fecha: ' . date('d/m/Y H:i'),
    0,
    1,
    'C'
);

/* RESUMEN DE FILTROS */

$pdf->SetY(MARGIN_TOP + 44);

$terminalTexto = 'Todos';
$categoriaTexto = 'Todos';
$estadoCivilTexto = 'Todos';
$tipoUsuarioTexto = 'Todos';
$estadoTexto = 'Todos';

if (!empty($this->terminal)) {

    foreach ($registros as $registro) {

        if (!empty($registro['terminal'])) {

            $terminalTexto = $registro['terminal'];

            break;
        }
    }
}

if (!empty($this->categoria)) {

    $categoriaTexto = $this->categoria;
}

if (!empty($this->estado_civil)) {

    $estadoCivilTexto = $this->estado_civil;
}

if (!empty($this->tp_usuario)) {

    foreach ($registros as $registro) {

        if (!empty($registro['tp_usuario'])) {

            $tipoUsuarioTexto = $registro['tp_usuario'];

            break;
        }
    }
}

if ($this->estado !== '') {

    $estadoTexto = $this->estado == '1'
        ? 'Habilitado'
        : 'Deshabilitado';
}

$pdf->SetFont('helvetica', 'B', 8);

$pdf->Cell(
    18,
    5,
    'Filtros:',
    0,
    0,
    'L'
);

$pdf->SetFont('helvetica', '', 8);

$pdf->Cell(
    42,
    5,
    'Terminal: ' . $terminalTexto,
    0,
    0,
    'L'
);

$pdf->Cell(
    38,
    5,
    'Categoría: ' . $categoriaTexto,
    0,
    0,
    'L'
);

$pdf->Cell(
    42,
    5,
    'Estado civil: ' . $estadoCivilTexto,
    0,
    0,
    'L'
);

$pdf->Cell(
    54,
    5,
    'Tipo usuario: ' . $tipoUsuarioTexto,
    0,
    1,
    'L'
);

$pdf->Cell(
    18,
    5,
    'Estado:',
    0,
    0,
    'L'
);

$pdf->Cell(
    42,
    5,
    $estadoTexto,
    0,
    1,
    'L'
);

if (!empty($this->search)) {

    $pdf->Cell(
        18,
        5,
        'Búsqueda:',
        0,
        0,
        'L'
    );

    $pdf->Cell(
        176,
        5,
        $this->search,
        0,
        1,
        'L'
    );
}

/* TABLA */

$headers = [
    'Terminal',
    'DNI',
    'Personal',
    'Licencia',
    'Categoría',
    'Estado Civil',
    'Celular',
    'Dirección',
    'Ubigeo',
    'Tipo Usuario',
    'Estado'
];

$widths = [
    16,
    14,
    29,
    18,
    16,
    18,
    14,
    28,
    13,
    20,
    14
];

/* ENCABEZADO TABLA */

$pdf->SetFont('helvetica', 'B', 6.5);

$pdf->SetFillColor(230, 230, 230);

$pdf->SetTextColor(0, 0, 0);

foreach ($headers as $i => $header) {

    $pdf->Cell(
        $widths[$i],
        7,
        $header,
        1,
        0,
        'C',
        true
    );
}

$pdf->Ln();

$pdf->SetFont('helvetica', '', 6);

/* ALINEACIONES */

$alignments = [
    'C',
    'C',
    'L',
    'C',
    'C',
    'C',
    'C',
    'L',
    'C',
    'C',
    'C'
];

/* DATOS */

foreach ($registros as $registro) {

    $estado = ($registro['estado'] ?? '') == '1'
        ? 'Habilitado'
        : 'Deshabilitado';

    $fila = [

        $registro['terminal'] ?? '',

        $registro['num_docu'] ?? '',

        $registro['personal'] ?? '',

        $registro['licencia'] ?? '',

        $registro['categoria'] ?? '',

        $registro['estado_civil'] ?? '',

        $registro['celular'] ?? '',

        $registro['direccion'] ?? '',

        $registro['ubigeo'] ?? '',

        $registro['tp_usuario'] ?? '',

        $estado

    ];

    $alturaFila = 7;

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

    /* SALTO DE PÁGINA */

    if (
        $pdf->GetY() + $alturaFila >
        $pdf->getPageHeight() - MARGIN_BOTTOM
    ) {

        $pdf->AddPage();

        $pdf->SetFont(
            'helvetica',
            'B',
            6.5
        );

        $pdf->SetFillColor(
            230,
            230,
            230
        );

        foreach ($headers as $i => $header) {

            $pdf->Cell(
                $widths[$i],
                7,
                $header,
                1,
                0,
                'C',
                true
            );
        }

        $pdf->Ln();

        $pdf->SetFont(
            'helvetica',
            '',
            6
        );
    }

    /* FILA */

    foreach ($fila as $i => $valor) {

        $pdf->MultiCell(
            $widths[$i],
            $alturaFila,
            $valor,
            1,
            $alignments[$i],
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

/* TOTAL */

$pdf->Ln(4);

$pdf->SetFont(
    'helvetica',
    'B',
    8
);

$pdf->Cell(
    0,
    5,
    'Total de registros: ' . count($registros),
    0,
    1,
    'L'
);

/* SALIDA */

$pdf->Output(
    'conductores.pdf',
    'I'
);
