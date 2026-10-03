<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("WIDTH_LOGO", 30);
define("HEIGHT_LOGO", 25);
define("LOGO_MARGIN_LEFT", 45); // Margen izquierdo para el logo
define("LOGO_MARGIN_TOP", 8);   // Margen superior para el logo/QR
define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8.5);

$piso = [
    1 => '1er Piso',
    2 => '2do Piso',
];

class TCPDF_A4_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 10);
    }

    function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $this->IncludeJS("print($param);");
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        $script = "var pp = getPrintParams();";
        $script .= $dialog ? "pp.interactive = pp.constants.interactionLevel.full;" :
            "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\" . $server . "\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }

    // Anchos de columnas para la fila actual
    function SetWidths($w)
    {
        $this->widths = $w;
    }

    // Alineaciones de columnas para la fila actual
    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    /**
     * Método único para pintar filas / celdas de texto, reemplaza a Row1..Row7.
     *
     * @param array $data         Textos de cada columna (usa $this->widths para los anchos)
     * @param float $lineHeight   Alto de línea dentro de la celda
     * @param string $align       Alineación por defecto si no se definió con SetAligns()
     * @param int|string $border  0 = sin borde, 1 = borde completo en cada celda (TCPDF lo dibuja solo)
     * @param array $boldColumns  Índices de columnas que van en negrita
     * @param bool $fill          Si se rellena el fondo (usa el color seteado con SetFillColor)
     */
    function Row($data, $lineHeight = 5, $align = 'L', $border = 0, $boldColumns = [], $fill = false)
    {
        // Calcula el alto de la fila según el texto más largo
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        // Salto de página si no entra la fila
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->bMargin)) {
            $this->AddPage($this->CurOrientation);
        }

        $y = $this->GetY();
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : $align;

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $this->MultiCell($w, $lineHeight, $data[$i], $border, $a, $fill, 0, '', '', true, 0, false, true, $h, 'M');

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

            $this->SetXY($xBefore + $w, $yBefore);
        }
        $this->SetY($y + $h);
    }

    // Getter público, ya que $lMargin es protegido en TCPDF y no se puede leer desde fuera de la clase
    function getLMargin()
    {
        return $this->lMargin;
    }

    // Atajo para pintar el encabezado de una sección (fondo gris + negrita + borde)
    function SectionHeader($titulo, $anchoTotal = 190)
    {
        $this->SetX($this->lMargin);
        $this->SetWidths([$anchoTotal]);
        $this->SetAligns(['L']);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        $this->Row([$titulo], 4.5, 'L', 1, [], true);
    }

    // Atajo para el bloque de datos debajo de un SectionHeader (borde, sin relleno)
    function SectionBody($texto, $anchoTotal = 190)
    {
        $this->SetX($this->lMargin);
        $this->SetWidths([$anchoTotal]);
        $this->SetAligns(['L']);
        $this->SetFont('helvetica', '', 8);
        $this->Row([$texto], 5, 'L', 1);
    }

    function NbLines($w, $txt)
    {
        if (!is_numeric($w)) {
            throw new Exception("El valor de \$w debe ser numérico");
        }

        $wmax = $w - 2 * $this->cMargin;
        $s = str_replace("\r", '', (string) $txt);
        $nb = mb_strlen($s, 'UTF-8');

        if ($nb > 0 && mb_substr($s, $nb - 1, 1, 'UTF-8') == "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = mb_substr($s, $i, 1, 'UTF-8');
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }

            if ($c == ' ') {
                $sep = $i;
            }

            $l += $this->GetStringWidth($c);

            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }
}

// Inserta el logo manteniendo proporción dentro de un recuadro máximo, centrado.
function insertLogoAdaptive($pdf, $logoPath, $x, $y, $maxWidth, $maxHeight)
{
    if (!file_exists($logoPath)) {
        return ['width' => 0, 'height' => 0, 'y' => $y];
    }

    $imageInfo = @getimagesize($logoPath);
    if (!$imageInfo) {
        return ['width' => 0, 'height' => 0, 'y' => $y];
    }

    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];

    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = $originalWidth * $ratio;
    $newHeight = $originalHeight * $ratio;
    $yCentered = $y + (($maxHeight - $newHeight) / 2);

    // Evita el warning típico de perfil sRGB incorrecto en PNGs
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true;
        }
        return false;
    });

    $pdf->Image($logoPath, $x, $yCentered, $newWidth, $newHeight, '', '', '', false, 300);

    restore_error_handler();

    return ['width' => $newWidth, 'height' => $newHeight, 'y' => $yCentered];
}

$pdf = new TCPDF_A4_CellFit();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// -------------------------------------------------------------------
// QR (izquierda) + Logo (al costado) + datos de la empresa debajo
// -------------------------------------------------------------------
if (!empty($this->data["data"]["cod_qr"])) {
    $qrPath = $this->data["data"]["cod_qr"];
    if (strpos($qrPath, 'private/empresa/') !== false) {
        if (file_exists($qrPath)) {
            $pdf->Image($qrPath, 10, LOGO_MARGIN_TOP, 25, 25, '', '', '', false, 300);
        }
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, 10, LOGO_MARGIN_TOP, 25, 25, '', '', '', false, 300);
    }
}

$logoActualY = LOGO_MARGIN_TOP;
$logoActualHeight = HEIGHT_LOGO;

if (!empty($this->data['data']['empresa']['logo'])) {
    $logoDimensions = insertLogoAdaptive(
        $pdf,
        $this->data['data']['empresa']['logo'],
        LOGO_MARGIN_LEFT,
        LOGO_MARGIN_TOP,
        WIDTH_LOGO,
        HEIGHT_LOGO - 4
    );

    if ($logoDimensions['width'] > 0) {
        $logoActualY = $logoDimensions['y'];
        $logoActualHeight = $logoDimensions['height'];
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(LOGO_MARGIN_LEFT, LOGO_MARGIN_TOP);
        $pdf->Cell(WIDTH_LOGO, 5, 'LOGO', 0, 0, 'C');
    }
} else {
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetXY(LOGO_MARGIN_LEFT, LOGO_MARGIN_TOP);
    $pdf->Cell(WIDTH_LOGO, 5, 'Sin logo', 0, 0, 'C');
}

// Datos de la empresa, justo debajo del logo
$textStartX = LOGO_MARGIN_LEFT;
$textWidth = 104;
$textY = $logoActualY + $logoActualHeight + 1;

$pdf->SetXY($textStartX, $textY);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell($textWidth, 4, $this->data['data']['empresa']['razon_social'], 0, 1, 'L');

$pdf->SetX($textStartX);
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell($textWidth, 4, $this->data['data']['empresa']['direccion_fiscal'], 0, 1, 'L');

$pdf->SetX($textStartX);
$pdf->Cell(
    $textWidth,
    4,
    $this->data['data']['empresa']['distri'] . " - " .
        $this->data['data']['empresa']['provi'] . " - " .
        $this->data['data']['empresa']['depa'],
    0,
    1,
    'L'
);

// -------------------------------------------------------------------
// Recuadro superior derecho: RUC / Tipo de comprobante / Serie-correlativo
// -------------------------------------------------------------------
$correlativo = str_pad($this->data['data']['correlativo'], 8, '0', STR_PAD_LEFT);
$tituloRecuadro = "RUC: " . $this->data["data"]["empresa"]["num_docu"] . "\n" .
    "GUÍA DE REMISIÓN TRANSPORTISTA" . "\n" .
    $this->data['data']['serie'] . "-" . $correlativo;

$pdf->SetXY(139, 10);
$pdf->SetWidths([50]);
$pdf->SetAligns(['C']);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Row([$tituloRecuadro], 6, 'C', 1);

// -------------------------------------------------------------------
// CUERPO
// -------------------------------------------------------------------
$pdf->SetY(max($pdf->GetY(), $textY + 12) + 4);

$pdf->SectionHeader("REMITENTE");
$pdf->SectionBody(
    "RUC: " . $this->data['data']['r_num_doc'] . "\n" .
        "DENOMINACIÓN: " . $this->data['data']['remitente']
);

$pdf->Ln(2);
$pdf->SectionHeader("DESTINATARIO");
$pdf->SectionBody(
    "RUC: " . $this->data['data']['d_num_doc'] . "\n" .
        "DENOMINACIÓN: " . $this->data['data']['destinatario']
);

if (!empty($this->data['data']['doc_relacionados'])) {
    $pdf->Ln(2);
    $pdf->SectionHeader("DOCUMENTOS RELACIONADOS");
    foreach ($this->data['data']['doc_relacionados'] as $doc_r) {
        $correlativoDoc = str_pad($doc_r['correlativo'], 6, '0', STR_PAD_LEFT);
        $pdf->SectionBody(
            $doc_r['comprobante'] . ' N° ' . $doc_r['serie'] . '-' . $correlativoDoc .
                ' - RUC N° ' . $doc_r['ruc']
        );
    }
}

$pdf->Ln(2);
$pdf->SectionHeader("DATOS DE TRASLADO");
$pdf->SectionBody(
    "FECHA EMISIÓN: " . $this->data['data']['fecha_emision'] . "\n" .
        "FECHA INICIO DE TRASLADO: " . $this->data['data']['fecha_emision'] . "\n" .
        "PESO BRUTO TOTAL (KGM): " . $this->data['data']['peso'] . "\n" .
        "PAGADOR DE FLETE: " .
        (!empty($this->data['data']['pagador_flete']) ?
            $this->data['data']['pagador_flete']['num_doc'] . ' - ' . $this->data['data']['pagador_flete']['razon_social'] :
            '')
);

$pdf->Ln(2);
$pdf->SectionHeader("DATOS DEL TRANSPORTE");
$pdf->SectionBody(
    "VEHÍCULO PRINCIPAL: " . $this->data['data']['vehiculo_placa'] . "\n" .
        "CONDUCTOR PRINCIPAL: " . $this->data['data']['conductor_nombres'] . " " . $this->data['data']['conductor_apellidos'] . "\n" .
        "DOCUMENTO CONDUCTOR: " . $this->data['data']['conductor_nro_doc'] . "\n" .
        "LICENCIA DE CONDUCIR DEL CONDUCTOR PRINCIPAL: " . $this->data['data']['conductor_licencia']
);

$direccion_partida = $this->data['data']['departamento_partida'] . " - " . $this->data['data']['provincia_partida'] . " - " . $this->data['data']['distrito_partida'] . " - " . $this->data['data']['partida_direccion'];
$direccion_destino = $this->data['data']['departamento_destino'] . " - " . $this->data['data']['provincia_destino'] . " - " . $this->data['data']['distrito_destino'] . " - " . $this->data['data']['destino_direccion'];

$pdf->Ln(2);
$pdf->SectionHeader("DATOS DEL PUNTO DE PARTIDA Y PUNTO DE LLEGADA");
$pdf->SectionBody(
    "PUNTO DE PARTIDA: (" . $this->data['data']['partida_ubigeo'] . ")  " . $direccion_partida . "\n" .
        "PUNTO DE LLEGADA: (" . $this->data['data']['destino_ubigeo'] . ")  " . $direccion_destino
);

$pdf->Ln(4);

// -------------------------------------------------------------------
// TABLA DE PRODUCTOS
// -------------------------------------------------------------------
$pdf->SetX($pdf->getLMargin());
$pdf->SetWidths([15, 30, 105, 20, 20]);
$pdf->SetAligns(['C', 'C', 'L', 'C', 'C']);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(230, 230, 230);
$pdf->Row(['Item', 'Código', 'Descripción', 'Unidad', 'Cantidad'], 5, 'C', 1, [], true);

$pdf->SetFont('helvetica', '', 7);
foreach ($this->data['data']["productos"] as $producto) {
    $pdf->SetX($pdf->getLMargin());
    $pdf->Row([
        $producto['item'],
        $producto['codigo'],
        $producto['nombre'],
        "NIU",
        $producto['cantidad'],
    ], 4, 'C', 1);
}

// -------------------------------------------------------------------
// OBSERVACIONES
// -------------------------------------------------------------------
if (!empty($this->data['data']['observaciones'])) {
    $pdf->Ln(4);
    $pdf->SectionHeader("OBSERVACIONES");
    $pdf->SectionBody($this->data['data']['observaciones']);
}

ob_clean();
$pdf->Output('reporte.pdf', 'I');