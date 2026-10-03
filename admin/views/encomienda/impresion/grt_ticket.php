<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("WIDTH_LOGO", 30);
define("HEIGHT_LOGO", 25);
define("TEXT_SIZE_COMPROBANTE_SERIE", 9);

class TCPDF_CellFiti extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        parent::__construct('P', 'mm', array(72.1, 297), true, 'UTF-8', false);

        $this->SetMargins(1.5, 5, 1.5);
        $this->SetAutoPageBreak(true, 5);
    }

    function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
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

    // Establecer los anchos de las columnas
    function SetWidths($w)
    {
        $this->widths = $w;
    }

    // Establecer los alineamientos de las columnas
    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $lineHeight = 4, $align = 'L', $boldColumns = [])
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }

        $h = $lineHeight * $nb;

        if ($this->GetY() + $h > ($this->getPageHeight() - $this->bMargin)) {
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : $align;

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false, 0, '', '', true, 0, false, true, $lineHeight, 'M');

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

            $this->SetXY($xBefore + $w, $yBefore);
        }

        $this->SetY($y + $h);
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

            $charWidth = $this->GetStringWidth($c);
            $l += $charWidth;

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

function cortarTexto($pdf, $texto, $ancho, $font = 'helvetica', $size = 8)
{
    $pdf->SetFont($font, '', $size);
    $palabras = explode(' ', $texto);
    $linea = '';
    $lineas = [];

    foreach ($palabras as $p) {
        $tmp = $linea . $p . ' ';
        if ($pdf->GetStringWidth($tmp) > $ancho) {
            $lineas[] = trim($linea);
            $linea = $p . ' ';
        } else {
            $linea = $tmp;
        }
    }
    if ($linea !== '')
        $lineas[] = trim($linea);
    return $lineas;
}

function cortarTexto_2($pdf, $texto, $ancho)
{
    $palabras = explode(' ', $texto);
    $linea = '';
    $lineas = [];

    foreach ($palabras as $p) {
        $tmp = $linea . $p . ' ';
        if ($pdf->GetStringWidth($tmp) > $ancho) {
            $lineas[] = trim($linea);
            $linea = $p . ' ';
        } else {
            $linea = $tmp;
        }
    }

    if ($linea !== '') {
        $lineas[] = trim($linea);
    }

    return $lineas;
}

$W_UTIL = 69; // Ancho utilizable dentro del ticket

$pdf = new TCPDF_CellFiti();
$pdf->setPrintHeader(false); // Desactiva el encabezado
$pdf->SetPrintFooter(false);
$pdf->AddPage();

// CABECERA: Fondo + Logo
if (!empty($this->data["data"]['empresa']["logo"]) && file_exists($this->data["data"]['empresa']["logo"])) {
    $bgWidth = 72.1 - 3; // Ancho total menos márgenes (1.5 mm por lado)
    $bgX = 1.5;          // Posición X tomando en cuenta el margen izquierdo
    $bgY = 5;            // Posición Y donde inicias la cabecera

    // Dibujar fondo (rectángulo de ancho completo y alto 25mm)
    $pdf->SetFillColor(255, 255, 255); // Color blanco de fondo
    $pdf->Rect($bgX, $bgY, $bgWidth, 25, 'F'); // 'F' para rellenar

    // Evitar warning por imágenes defectuosas
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true; // Ignora este warning específico
        }
        return false; // Otros errores normales
    });

    // Calcular proporción para mantener la imagen dentro de 35x25 mm
    list($imgWidth, $imgHeight) = getimagesize($this->data["data"]['empresa']["logo"]);
    $scale = min(35 / $imgWidth, 25 / $imgHeight);
    $finalWidth = $imgWidth * $scale;
    $finalHeight = $imgHeight * $scale;

    // Centrar la imagen en el rectángulo
    $xLogo = (72.1 - $finalWidth) / 2;
    $yLogo = $bgY + (25 - $finalHeight) / 2;

    // Insertar imagen
    $pdf->Image(
        $this->data["data"]['empresa']["logo"],
        $xLogo,
        $yLogo,
        $finalWidth,
        $finalHeight,
        '',
        '',
        '',
        false,
        300
    );

    restore_error_handler(); // Restablecer manejo de errores normal

    $pdf->Ln(23); // Saltar después de la cabecera
}

// Datos de la empresa
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell($W_UTIL, 5, "", 0, 'C', 0, 1); // Línea en blanco

$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell($W_UTIL, 5, $this->data["data"]['empresa']["razon_social"], 0, 'C', 0, 1);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($W_UTIL, 4, "RUC: " . $this->data["data"]['empresa']["num_docu"], 0, 0.1, 'C');

$pdf->Ln(1);
// Dirección fiscal (esto siempre se imprime)
$pdf->SetFont('helvetica', '', 6.5);
$pdf->MultiCell($W_UTIL, 4, 'DOM. FISCAL: ' . $this->data['data']["empresa"]['direccion_fiscal'], 0, 'C', 0, 1);

// Línea separadora (fina)
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'B');
$pdf->Ln(5);

// TIPO DE COMPROBANTE
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell($W_UTIL, 4, strtoupper("GUÍA DE REMISIÓN TRANSPORTISTA"), 0, 'C', 0, 1);
$pdf->Ln(2);

// SERIE Y CORRELATIVO
$correlativo = $this->data["data"]["serie"] . "-" . str_pad($this->data["data"]["correlativo"], 8, '0', STR_PAD_LEFT);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell($W_UTIL, 4, $correlativo, 0, 'C', 0, 1);
$pdf->Ln(2);

// Línea separadora superior
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(2);

// DATOS CLIENTE - DESTINATARIO
$pdf->SetFont('helvetica', '', 8);

// FECHA - Negrita en "FECHA:"
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, 'FECHA EMISION:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 5, $this->data["data"]["fecha_emision"], 0, 1, 'L');

// FECHA - Negrita en "FECHA:"
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 5, 'FECHA TRASLADO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 5, $this->data["data"]["fecha_envio"], 0, 1, 'L');
$pdf->Ln(3);

$posY_QR = $pdf->GetY();

if (!empty($this->data["data"]["cod_qr"])) {
    $qrPath = $this->data["data"]["cod_qr"];

    // Ancho del ticket y del QR
    $anchoTicket = 72; // Ancho típico de ticket térmico en mm
    $anchoQR = 30;

    // Calcular posición X para centrar
    $posX_QR = ($anchoTicket - $anchoQR) / 2;

    if (strpos($qrPath, 'private/empresa/') !== false && file_exists($qrPath)) {
        $pdf->Image($qrPath, $posX_QR, $posY_QR, $anchoQR, $anchoQR, '', '', '', false, 300, '', false, false, 0);
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, $posX_QR, $posY_QR, $anchoQR, $anchoQR, '', '', '', false, 300, '', false, false, 0);
    }
}
$pdf->SetY($pdf->GetY() + 30);

$direccion_partida = $this->data['data']['partida_direccion'] . ' - ' . $this->data['data']['departamento_partida'] . " - " . $this->data['data']['provincia_partida'] . " - " . $this->data['data']['distrito_partida'];
$direccion_destino = $this->data['data']['destino_direccion'] . ' - ' . $this->data['data']['departamento_destino'] . " - " . $this->data['data']['provincia_destino'] . " - " . $this->data['data']['distrito_destino'];
$pdf->Ln(3);
// PUNTO DE PARTIDA
$anchoLabel = 35;
$anchoValor = 40;
$anchoTicket = 70;
$margenIzq = 2;

$texto = '(' . $this->data['data']['partida_ubigeo'] . ')  ' . $direccion_partida;

$lineasPrimera = cortarTexto($pdf, $texto, $anchoValor);
$primeraLinea = $lineasPrimera[0] ?? '';
$textoRestante = trim(substr($texto, strlen($primeraLinea)));
$lineasResto = cortarTexto($pdf, $textoRestante, $anchoTicket);

// Línea principal
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 3, 'PUNTO DE PARTIDA:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell($anchoValor, 3, $primeraLinea, 0, 1, 'L');

// Líneas debajo → ancho completo
$pdf->SetX($margenIzq);
foreach ($lineasResto as $linea) {
    $pdf->Cell($anchoTicket, 3, trim($linea), 0, 1, 'L');
    $pdf->SetX($margenIzq);
}


$texto_destino = '(' . $this->data['data']['destino_ubigeo'] . ')  ' . $direccion_destino;

$lineasPrimera = cortarTexto($pdf, $texto_destino, $anchoValor);
$primeraLinea = $lineasPrimera[0] ?? '';
$textoRestante = trim(substr($texto_destino, strlen($primeraLinea)));
$lineasResto = cortarTexto($pdf, $textoRestante, $anchoTicket);

// Línea principal
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 3, 'PUNTO DE DESTINO:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell($anchoValor, 3, $primeraLinea, 0, 1, 'L');

$pdf->SetX($margenIzq);
foreach ($lineasResto as $linea) {
    $pdf->Cell($anchoTicket, 3, trim($linea), 0, 1, 'L');
    $pdf->SetX($margenIzq);
}

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'DATOS DEL REMITENTE', 0, 'L', 0, 0);
$pdf->Ln(1);
// CLIENTE
$descrip_remitente = $this->data['data']['r_tp_docu'] == 1 ? 'Nombre:' : 'Raz. Social:';
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, $descrip_remitente, 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$cliente_nombres = $this->data["data"]["remitente"];
$pdf->MultiCell(0, 5, $cliente_nombres, 0, 'L', 0, 1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'DNI/RUC:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data["data"]["r_num_doc"], 0, 'L', 0, 1);

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'DATOS DEL DESTINATARIO', 0, 'L', 0, 0);
$pdf->Ln(1);

//DESTINO
$descrip_destino = $this->data['data']['d_tipo_doc'] == 1 ? 'Nombre:' : 'Raz. Social:';
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, $descrip_destino, 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data["data"]["destinatario"], 0, 'L', 0, 1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'DNI/RUC:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data["data"]["d_num_doc"], 0, 'L', 0, 1);

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'DATOS DEL PAGADOR', 0, 'L', 0, 0);
$pdf->Ln(1);
// PAGADOR
$descrip_pagador = $this->data['data']['pagador_flete']['tipo_documento'] == 1 ? 'Nombre:' : 'Raz. Social:';
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, $descrip_pagador, 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data["data"]['pagador_flete']["razon_social"], 0, 'L', 0, 1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'DNI/RUC:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data['data']['pagador_flete']['num_doc'], 0, 'L', 0, 1);

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'BIENES POR TRANSPORTAR', 0, 'L', 0, 0);
$pdf->Ln(1);

// TABLA DE DETALLE AJUSTADA AL TICKET (ancho total: 72 mm)
$pdf->setX(0);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell(8, 5, 'N°', 1, 0, 'C');
$pdf->Cell(10, 5, 'CANT', 1, 0, 'C');
$pdf->Cell(16, 5, 'COD', 1, 0, 'C');
$pdf->Cell(39, 5, 'DESCRIPCION', 1, 1, 'C');

$suma_total = 0;

$pdf->SetFont('helvetica', '', 7);

foreach ($this->data['data']['productos'] as $producto) {

    $descripcion = $producto['nombre'];
    $lineasDesc = cortarTexto_2($pdf, $descripcion, 39);
    $altoFila = count($lineasDesc) * 5;
    $pdf->setX(0);
    // N°
    $pdf->MultiCell(8, $altoFila, $producto['item'], 1, 'C', false, 0);

    // Cantidad
    $pdf->MultiCell(10, $altoFila, $producto['cantidad'], 1, 'C', false, 0);

    // Código
    $pdf->MultiCell(16, $altoFila, $producto['codigo'], 1, 'L', false, 0);

    // Descripción (línea por línea)
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->MultiCell(39, 5, implode("\n", $lineasDesc), 1, 'L');
}


// Ajustar la posición del cursor después de la tabla de detalles
$pdf->Ln(2);

// FECHA - Negrita en "FECHA:"
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(50, 4, 'Unidad de Medida del Peso Bruto : ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 4, 'KGM', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(50, 4, 'Peso Bruto total de la carga : ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 4, $this->data['data']['peso'], 0, 1, 'L');

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'DATOS DEL VEHICULO', 0, 'L', 0, 0);
$pdf->Ln(1);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(30, 5, 'Vehículo Principal : ', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data['data']['vehiculo_placa'], 0, 'L', 0, 1);
if (!empty($this->data['data']['tarjeta_mtc'])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'T. única de circulación MTC :', 0, 'L', 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 5, $this->data["data"]["tarjeta_mtc"], 0, 'L', 0, 1);
}

$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(50, 3, 'DATOS DE LOS CONDUCTORES', 0, 'L', 0, 0);
$pdf->Ln(1);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'Principal : ', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data['data']['conductor_nombres'] . " " . $this->data['data']['conductor_apellidos'], 0, 'L', 0, 1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'Licencia :', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data['data']['conductor_licencia'], 0, 'L', 0, 1);

if (!empty($this->data['data']['observaciones'])) {
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(50, 3, 'OBSERVACIONES', 0, 'L', 0, 0);
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 5, $this->data['data']['observaciones'], 0, 'L', 0, 1);
}

$pdf->SetAutoPageBreak(true, 5); // Ya lo tienes en tu clase

ob_clean();
$pdf->Output('ticket_guia.pdf', 'I');
