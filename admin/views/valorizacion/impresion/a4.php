<?php

$es_inclusion = isset($es_interno) && $es_interno === true;

if (!$es_inclusion) {

    ini_set('memory_limit', '2048M');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('America/Lima');

    require_once('public/plugins/print/num_letras.php');
    require_once('public/plugins/tc/tcpdf.php');

    define("TEXT_SIZE_TITULO", 16);
    define("TEXT_SIZE_NORMAL", 9);
    define("TEXT_SIZE_REDUCIDO_COT", 8);
    define("COLOR_AZUL_R", 21);
    define("COLOR_AZUL_G", 40);
    define("COLOR_AZUL_B", 82);
    define("COLOR_NARANJA_R", 243);
    define("COLOR_NARANJA_G", 146);
    define("COLOR_NARANJA_B", 0);
    define("COLOR_ROJO_R", 178);
    define("COLOR_ROJO_G", 34);
    define("COLOR_ROJO_B", 34);
    define("COLOR_GRIS_R", 235);
    define("COLOR_GRIS_G", 235);
    define("COLOR_GRIS_B", 235);

    class TCPDF_CellFit extends TCPDF
    {
        protected $widths;
        protected $aligns;
        protected $cMargin = 1;

        public function __construct()
        {
            // A4, orientación vertical, unidades en mm
            parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
            $this->SetMargins(10, 10, 10);
            $this->SetAutoPageBreak(true, 10);
        }

        function AutoPrint($dialog = false)
        {
            $param = ($dialog ? 'true' : 'false');
            $this->IncludeJS("print($param);");
        }

        function SetWidths($w)
        {
            $this->widths = $w;
        }

        function SetAligns($a)
        {
            $this->aligns = $a;
        }

        // Fila de tabla con altura automática según el contenido más largo
        function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $fillColumns = [], $fillColor = null)
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

                if (in_array($i, $boldColumns))
                    $this->SetFont('', 'B');

                $fill = in_array($i, $fillColumns);
                if ($fill && $fillColor) {
                    $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                }

                $this->MultiCell($w, $h, $data[$i], 0, $a, $fill, 0, '', '', true, 0, false, true, $h, 'M');

                if (in_array($i, $boldColumns))
                    $this->SetFont('', '');

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
                if ($c == ' ')
                    $sep = $i;

                $l += $this->GetStringWidth($c);
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j)
                            $i++;
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

        // Barra de título con fondo de color (ej. "DATOS CLIENTE:")
        function BarraTitulo($texto, $w = 190, $h = 6, $r = COLOR_AZUL_R, $g = COLOR_AZUL_G, $b = COLOR_AZUL_B)
        {
            $this->SetFillColor($r, $g, $b);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('helvetica', 'B', 9);
            $this->Cell($w, $h, '  ' . $texto, 0, 1, 'L', true);
            $this->SetTextColor(0, 0, 0);
        }
    }

    $data = $this->data;
    $pdf = new TCPDF_CellFit();
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    generarValorizacion($pdf, $data);

    $pdf->AutoPrint(false);

    ob_clean();
    $pdf->Output('valorizacion.pdf', 'I');
    exit;
}

/**
 * Genera el documento de VALORIZACIÓN a partir de la data del backend:
 * $data['EMPRESA'], $data['CABECERA'], $data['BODY'] (array)
 */
function generarValorizacion($pdf, $data)
{
    $empresa = $data['EMPRESA'];
    $cabecera = $data['CABECERA'];
    $body = $data['BODY'];

    $pdf->SetMargins(10, 10, 10);
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);

    // ---------- ENCABEZADO: dirección de la empresa (línea superior) ----------
    $direccionCompleta = trim($empresa['direccion'] . ' - ' . $empresa['distrito'] . ' - ' . $empresa['provincia'] . ' - ' . $empresa['departamento']);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetXY(10, 10);
    $pdf->Cell(190, 4, $direccionCompleta, 0, 1, 'C');

    // ---------- LOGO / NOMBRE COMERCIAL (a la izquierda) ----------
    $y_inicio = $pdf->GetY();

    if (!empty($empresa["logo"]) && file_exists($empresa["logo"])) {

        // Evitar warning por imágenes defectuosas
        set_error_handler(function ($errno, $errstr) {
            if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
                return true;
            }
            return false;
        });

        // Calcular proporción para mantener la imagen dentro de 55x20 mm
        list($imgWidth, $imgHeight) = getimagesize($empresa["logo"]);
        $scale = min(55 / $imgWidth, 20 / $imgHeight);
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        $pdf->Image(
            $empresa["logo"],
            10,
            $y_inicio,
            $finalWidth,
            $finalHeight,
            '',
            '',
            '',
            false,
            300
        );

        restore_error_handler();

    } else {
        // Placeholder si todavía no hay logo configurado
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Rect(10, $y_inicio, 55, 20);
        $pdf->SetXY(10, $y_inicio + 8);
        $pdf->Cell(55, 4, 'Logo', 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    // ---------- CUADRO RUC (arriba derecha) ----------
    $pdf->SetXY(140, 10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Cell(60, 7, 'RUC: ' . $empresa['ruc'], 1, 1, 'C');

    // ---------- CUADRO TÍTULO DEL DOCUMENTO ----------
    $pdf->SetXY(140, 17);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->MultiCell(60, 12, "VALORIZACIÓN\nDE SERVICIOS", 1, 'C', false, 1, '', '', true, 0, false, true, 12, 'M');

    // ---------- CUADRO NRO (serie + id) ----------
    $nroDocumento = $cabecera['serie'] . '-' . str_pad($cabecera['id'], 6, '0', STR_PAD_LEFT);
    $pdf->SetXY(140, 29);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 7, 'NRO: ' . $nroDocumento, 1, 1, 'C');

    $pdf->SetY(40);

    // ---------- DATOS DEL CLIENTE ----------
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
    $pdf->SetX(10);
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
    $pdf->Cell(25, 6, 'CLIENTE:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
    $pdf->Cell(95, 6, $cabecera['cliente'], 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
    $pdf->Cell(15, 6, 'RUC/DNI:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
    $pdf->Cell(45, 6, $cabecera['num_docu'], 0, 1, 'L');

    $pdf->SetX(10);
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
    $pdf->Cell(25, 6, 'FECHA:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
    $fechaFormateada = date('d-m-Y', strtotime($cabecera['fecha']));
    $pdf->Cell(95, 6, $fechaFormateada, 0, 0, 'L');


    $pdf->Ln(6);

    $headers = ['FECHA', 'ORIGEN', 'O.DIRECCION', 'DESTINO', 'D.DIRECCION', 'CONDUCTOR', 'TOTAL'];
    $widths = [20, 25, 35, 25, 35, 30, 20];
    $aligns = ['C', 'C', 'L', 'C', 'L', 'C', 'R'];

    $pdf->SetWidths($widths);
    $pdf->SetAligns($aligns);

    $pdf->SetFillColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetX(10);
    for ($i = 0; $i < count($headers); $i++) {
        $pdf->Cell($widths[$i], 6, $headers[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFont('helvetica', '', 7.5);
    $totalGeneral = 0;

    foreach ($body as $item) {
        $fechaItem = date('d-m-Y', strtotime($item['fecha_salida']));
        $fila = [
            $fechaItem,
            $item['origen'],
            $item['direccion_origen'],
            $item['destino'],
            $item['direccion_destino'],
            $item['conductor'],
            number_format((float) $item['precio'], 2),
        ];
        $pdf->SetX(10);
        $pdf->Row($fila, 4.5);
        $totalGeneral += (float) $item['total'];
    }

    $pdf->Ln(3);

    // ---------- RESUMEN DE TOTALES (SUBTOTAL / IGV / TOTAL) ----------
    $xTotales = 130;
    $wLabel = 30;
    $wValor = 30;

    $pdf->SetX($xTotales);
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
    $pdf->Cell($wLabel, 6, 'SUBTOTAL:', 1, 0, 'L');
    $pdf->Cell($wValor, 6, 'S/ ' . number_format((float) $cabecera['subtotal'], 2), 1, 1, 'R');

    $pdf->SetX($xTotales);
    $pdf->Cell($wLabel, 6, 'IGV:', 1, 0, 'L');
    $pdf->Cell($wValor, 6, 'S/ ' . number_format((float) $cabecera['igv'], 2), 1, 1, 'R');

    $pdf->SetX($xTotales);
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
    $pdf->SetFillColor(COLOR_GRIS_R, COLOR_GRIS_G, COLOR_GRIS_B);
    $pdf->Cell($wLabel, 7, 'TOTAL:', 1, 0, 'L', true);
    $pdf->Cell($wValor, 7, 'S/ ' . number_format((float) $cabecera['total'], 2), 1, 1, 'R', true);
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL);

    $pdf->Ln(4);

    // ---------- OBSERVACIÓN ----------
    if (!empty($cabecera['observacion'])) {
        $pdf->BarraTitulo('OBSERVACIÓN');
        $pdf->SetX(10);
        $pdf->MultiCell(190, 8, $cabecera['observacion'], 1, 'L');
        $pdf->Ln(4);
    }

    // ---------- FIRMAS ----------
    $yFirma = $pdf->GetY() + 20;
    if ($yFirma > $pdf->getPageHeight() - 30) {
        $yFirma = $pdf->getPageHeight() - 30;
    }

    $pdf->SetY($yFirma);
    $pdf->SetX(20);
    $pdf->Cell(80, 0, '', 'T', 0, 'C');
    $pdf->SetX(120);
    $pdf->Cell(80, 0, '', 'T', 1, 'C');

    $pdf->SetX(20);
    $pdf->Cell(80, 6, 'Firma del Cliente', 0, 0, 'C');
    $pdf->SetX(120);
    $pdf->Cell(80, 6, 'Firma del Responsable', 0, 1, 'C');
}