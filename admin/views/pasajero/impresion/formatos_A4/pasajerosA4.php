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

class TCPDF_A4_Pasajeros extends TCPDF
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
        $this->Cell(0, 5, 'Reporte de pasajeros | Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new TCPDF_A4_Pasajeros();

$pdf->AddPage();

$registros = $this->registros ?? [];
$empresa = $this->empresa_info ?? [];

// TÍTULO DEL REPORTE
// ============================================

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetXY(MARGIN_LEFT, MARGIN_TOP);
$pdf->Cell(WIDTH_UTIL, 8, 'REPORTE DE PASAJEROS', 0, 1, 'C');

// ============================================
// CABECERA DEL REPORTE
// ============================================

$pdf->SetFillColor(245, 245, 245);
$pdf->Rect(MARGIN_LEFT, MARGIN_TOP + 10, WIDTH_UTIL, 32, 'F');

$col_logo = 35;
$col_empresa = 95;
$col_titulo = WIDTH_UTIL - $col_logo - $col_empresa;

// ============================================
// LOGO
// ============================================
$logo = $empresa['logo'] ?? '';

if (!empty($logo)) {
    $nombreLogo = basename($logo);
    $logoPath = 'D:/xampp/htdocs/tid-transporte/img/admin/' . $nombreLogo;

    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, MARGIN_LEFT + 5, MARGIN_TOP + 15, 35, 22, '', '', '', false, 300);
    }
}

// ============================================
// DATOS DE EMPRESA
// ============================================
$pdf->SetXY(MARGIN_LEFT + $col_logo, MARGIN_TOP + 14);
$pdf->SetFont('helvetica', 'B', 9);

$nombre_empresa = $empresa['nombre'] ?? '';
$pdf->Cell($col_empresa, 6, $nombre_empresa, 0, 1, 'L');

$pdf->SetX(MARGIN_LEFT + $col_logo);
$pdf->SetFont('helvetica', '', 8);

$ruc = $empresa['ruc'] ?? '';

$pdf->Cell($col_empresa, 5, 'RUC: ' . $ruc, 0, 1, 'L');

$pdf->SetX(MARGIN_LEFT + $col_logo);

$direccion = $empresa['direccion'] ?? '';
$pdf->MultiCell($col_empresa, 4, 'DOM. FISCAL: ' . $direccion, 0, 'L');

// ============================================
// TÍTULO
// ============================================

$x_titulo = MARGIN_LEFT + $col_logo + $col_empresa;

$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY($x_titulo, MARGIN_TOP + 28);
$pdf->Cell($col_titulo, 5, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'C');

$pdf->SetY(MARGIN_TOP + 38);

// ============================================
// RESUMEN DE FILTROS
// ============================================
$pdf->SetY(MARGIN_TOP + 44);
$terminalTexto = 'Todos';
$tipoDocumentoTexto = 'Todos';
$estadoTexto = 'Todos';

if (!empty($this->terminal)) {
    foreach ($registros as $registro) {
        if (!empty($registro['terminal'])) {
            $terminalTexto = $registro['terminal'];
            break;
        }
    }
}

if (!empty($this->tp_docu)) {
    foreach ($registros as $registro) {
        if (!empty($registro['tp_docu'])) {
            $tipoDocumentoTexto = $registro['tp_docu'];
            break;
        }
    }
}

if ($this->estado !== '') {
    $estadoTexto = $this->estado == '1' ? 'Habilitado' : 'Deshabilitado';
}

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(18, 5, 'Filtros:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(65, 5, 'Terminal: ' . $terminalTexto, 0, 0, 'L');
$pdf->Cell(65, 5, 'Tipo documento: ' . $tipoDocumentoTexto, 0, 0, 'L');
$pdf->Cell(55, 5, 'Estado: ' . $estadoTexto, 0, 0, 'L');

if (!empty($this->search)) {
    $pdf->Cell(0, 5, 'Búsqueda: ' . $this->search, 0, 1, 'L');
} else {
    $pdf->Ln(5);
}

// ============================================
// TABLA DE PASAJEROS
// ============================================

$headers = [
    'Terminal',
    'Personal',
    'Género',
    'Doc.',
    'N. Doc.',
    'F. Nac.',
    'Nacionalidad',
    'Celular',
    'Dirección',
    'Ubigeo',
    'Estado'
];

$widths = [16, 30, 12, 10, 17, 15, 17, 18, 25, 15, 20];

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetTextColor(0, 0, 0);

foreach ($headers as $i => $header) {
    $pdf->Cell($widths[$i], 5, $header, 1, 0, 'C', true);
}

$pdf->Ln();

$pdf->SetFont('helvetica', '', 6.5);

foreach ($registros as $registro) {
    $personal = trim(($registro['nombres'] ?? '') . ' ' . ($registro['apellidos'] ?? ''));
    $estado = ($registro['estado'] ?? '') == '1' ? 'Habilitado' : 'Deshabilitado';

    $fila = [
        $registro['terminal'] ?? '',
        $personal,
        $registro['genero'] ?? '',
        $registro['tp_docu'] ?? '',
        $registro['num_docu'] ?? '',
        $registro['fecha_nacimiento'] ?? '',
        $registro['nacionalidad'] ?? '',
        $registro['celular'] ?? '',
        $registro['direccion'] ?? '',
        $registro['ubigeo'] ?? '',
        $estado
    ];

    $alturaFila = 7;

    foreach ($fila as $i => $valor) {
        $altura = $pdf->getStringHeight($widths[$i], $valor);
        $alturaFila = max($alturaFila, $altura);
    }

    if ($pdf->GetY() + $alturaFila > $pdf->getPageHeight() - MARGIN_BOTTOM) {
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(230, 230, 230);
        foreach ($headers as $i => $header) {
            $pdf->Cell($widths[$i], 8, $header, 1, 0, 'C', true);
        }

        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 6.5);
    }

    $alignments = ['C', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'L', 'C', 'C'];
    foreach ($fila as $i => $valor) {
        $pdf->MultiCell($widths[$i], $alturaFila, $valor, 1, $alignments[$i], false, 0, '', '', true, 0, false, true, $alturaFila, 'M');
    }

    $pdf->Ln($alturaFila);
}

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 5, 'Total de registros: ' . count($registros), 0, 1, 'L');

$pdf->Output('pasajeros.pdf', 'I');
