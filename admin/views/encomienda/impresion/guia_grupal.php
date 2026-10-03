<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');

define("WIDTH_LOGO", 25);
define("HEIGHT_LOGO", 25);
define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8.5);
define("TEXT_WIDTH_SIZE_BODY", 40);

$piso = [
    1 => '1er Piso',
    2 => '2do Piso',
];

class FPDF_CellFiti extends FPDF_CellFit
{
    function AutoPrint($dialog = false)
    {
        //Open the print dialog or start printing immediately on the standard printer
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        //Print on a shared printer (requires at least Acrobat 6)
        $script = "var pp = getPrintParams();";
        if ($dialog)
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
        else
            $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }

    protected $widths;
    protected $aligns;

    function SetWidths($w)
    {
        // Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function Row1($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 2.5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);

            // Print the text
            $this->MultiCell($w, 2.5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }
    function Row2($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);
            //
            if ($i === 1) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 3) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 5) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 7) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } else {
                $this->SetFont('Helvetica', '', TEXT_SIZE_BODY); // Establece el tipo de letra para otras columnas
            }


            // Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }


        // Go to the next line
        $this->Ln($h);
    }

    function Row3($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);

            // Print the text
            $this->MultiCell($w, 3.5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }
    function Row4($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 3.5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            $this->Rect($x, $y, $w, $h);

            // Print the text
            $this->MultiCell($w, 3.5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }
    function Row7($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 4 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            $this->Rect($x, $y, $w, $h);

            // Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }
    function Row5($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);

            // Print the text
            $this->MultiCell($w, 6, $data[$i], 1, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }

    function Row6($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);

            // Print the text
            $this->MultiCell($w, 6, $data[$i], 1, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Restablece el color de fondo después de la primera fila
        //$this->SetFillColor(255, 255, 255);
        // Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        // If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
        $this->SetMargins(10, 10, 0);
    }

    function NbLines($w, $txt)
    {
        // Compute the number of lines a MultiCell of width w will take
        // Calcule el número de líneas que tomará una MultiCell de ancho w
        if (!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
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
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

$pdf = new FPDF_CellFiti('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(8, 0, 0);

// if ($this->data["data"]['data']['logo'] && file_exists($this->data["data"]["empresa"]["logo"])) {
//     $pdf->Image($this->data["data"]["empresa"]["logo"], 10, 6, WIDTH_LOGO, HEIGHT_LOGO);
//     $pdf->Ln(-10);
// }

foreach ($this->data['data'] as $index => $data) {
    if ($index > 0) {
        $pdf->AddPage();
    }

    // QR CODE
    if ($data["cod_qr"]) {
        if (strpos($data["cod_qr"], 'private/empresa/') !== false) {
            if (file_exists($data["cod_qr"])) {
                $pdf->Image($data["cod_qr"], 10, 5, 25, 25);
            }
        } elseif (strpos($data["cod_qr"], 'https://') !== false) {
            $urlQR = str_replace(' ', '%20', $data['cod_qr']);
            $pdf->Image($urlQR, 10, 5, 25, 25);
        }
    }

    // HEADER
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Ln(10);
    $pdf->Cell(43);
    $pdf->SetWidths(array(104));
    $pdf->Row1(array(''));
    $pdf->Ln(0.1);
    $pdf->Cell(30);
    $pdf->SetWidths(array(104));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row1(array($data['empresa']['razon_social']));
    $pdf->Ln(0.1);
    $pdf->Cell(43);
    $pdf->SetWidths(array(104));
    $pdf->Row1(array());
    $pdf->Ln(0.5);
    $pdf->Cell(30);
    $pdf->SetWidths(array(86));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row1(array($data['empresa']['direccion_fiscal'] . " - " . $data['empresa']['distri'] . " - " . $data['empresa']['provi'] . " - " . $data['empresa']['depa']));
    $pdf->Ln(0.1);
    $pdf->Cell(43);
    $pdf->SetWidths(array(104));
    $pdf->Row1(array());
    $pdf->Ln(0.1);
    $pdf->Cell(43);
    $pdf->SetWidths(array(104));
    $pdf->Row1(array(""));
    $pdf->Ln(0.1);
    $pdf->Cell(43);
    $pdf->SetWidths(array(104));
    $pdf->Row1(array(""));

    $pdf->Ln(-30);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(139);
    $pdf->SetWidths(array(50));
    $pdf->Row5(array("RUC: " . $data["empresa"]["num_docu"] . "\n" . utf8_decode("Guía de remisión transportista" . "\n" . $data['serie'] . "-" . $data['correlativo'])));

    $pdf->Ln(1);
    $pdf->Cell(147);
    $pdf->SetWidths(array(50));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row2(array(""));

    $pdf->Ln(1);
    $pdf->Cell(147);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetWidths(array(50));
    $pdf->Row2(array(""));

    // BODY - REMITENTE
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetWidths(array(190));
    $pdf->Row7(array("REMITENTE"));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row6(array(
        utf8_decode(
            "RUC: " . $data['r_num_doc'] . "\n" .
                "DENOMINACIÓN: " . $data['remitente'] . "\n"
        ),
    ));

    // DESTINATARIO
    $pdf->Ln(6);
    $pdf->Ln(2.5);
    $pdf->SetWidths(array(190));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row7(array("DESTINATARIO"));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row6(array(
        utf8_decode(
            "RUC: " . $data['d_num_doc'] . "\n" .
                "DENOMINACIÓN: " . $data['destinatario'] . "\n"
        ),
    ));

    // DOCUMENTOS RELACIONADOS
    if (!empty($data['doc_relacionados'])) {
        $pdf->Ln(6);
        $pdf->Ln(2.5);
        $pdf->SetWidths(array(190));
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Row7(array("DOCUMENTOS RELACIONADOS"));
        $pdf->SetFont('Helvetica', '', 8);
        foreach ($data['doc_relacionados'] as $doc_r) {
            $correlativo = str_pad($doc_r['correlativo'], 6, '0', STR_PAD_LEFT);
            $pdf->Row6(array(
                utf8_decode(
                    $doc_r['comprobante'] . ' N° ' . $doc_r['serie'] . '-' . $correlativo . ' - ' . 'RUC N° ' . $doc_r['ruc'] . "\n"
                ),
            ));
        }
    }

    // DATOS DE TRASLADO
    $pdf->Ln(6);
    $pdf->Ln(2.5);
    $pdf->SetWidths(array(190));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row7(array("DATOS DE TRASLADO"));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row6(array(
        utf8_decode(
            "FECHA EMISIÓN: " . $data['fecha_emision'] . "\n" .
                "FECHA INICIO DE TRASLADO: " . $data['fecha_envio'] . "\n" .
                "PESO BRUTO TOTAL (KGM): " . $data['peso'] . "\n" .
                " PAGADOR DE FLETE : " .
                (isset($data['pagador_flete']) && $data['pagador_flete'] != [] ?
                    $data['pagador_flete']['num_doc'] . ' - ' . $data['pagador_flete']['razon_social'] :
                    ''
                ) . "\n"
        ),
    ));

    // DATOS DEL TRANSPORTE
    $pdf->Ln(6);
    $pdf->Ln(2.5);
    $pdf->SetWidths(array(190));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row7(array("DATOS DEL TRANSPORTE"));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row6(array(
        utf8_decode(
            "VEHÍCULO PRINCIPAL: " . $data['vehiculo_placa'] . "\n" .
                "CONDUCTOR PRINCIPAL: " . $data['conductor_nombres'] . " " . $data['conductor_apellidos'] . "\n" .
                "DOCUMENTO CONDUCTOR: " . $data['conductor_nro_doc'] . "\n" .
                "LICENCIA DE CONDUCIR DEL CONDUCTOR PRINCIPAL: " . $data['conductor_licencia'] . "\n"
        ),
    ));

    // PUNTO DE PARTIDA Y LLEGADA
    $direccion_partida = $data['departamento_partida'] . " - " . $data['provincia_partida'] . " - " . $data['distrito_partida'] . " - " . $data['partida_direccion'];
    $direccion_destino = $data['departamento_destino'] . " - " . $data['provincia_destino'] . " - " . $data['distrito_destino'] . " - " . $data['destino_direccion'];

    $pdf->Ln(6);
    $pdf->Ln(2.5);
    $pdf->SetWidths(array(190));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row7(array("DATOS DEL PUNTO DE PARTIDA Y PUNTO DE LLEGADA"));
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Row6(array(
        utf8_decode(
            "PUNTO DE PARTIDA: (" . $data['partida_ubigeo'] . ")  " . $direccion_partida . "\n" .
                "PUNTO DE LLEGADA: (" . $data['destino_ubigeo'] . ")  " . $direccion_destino . "\n"
        ),
    ));

    // TABLA DE PRODUCTOS
    $pdf->Ln(6);
    $pdf->Ln(2.5);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetWidths(array(20, 30, 100, 20, 20));
    $pdf->Row4(array(
        'Item',
        'Codigo',
        'Descripcion',
        'Unidad',
        'Cantidad',
    ));

    $pdf->Ln(0.1);
    for ($i = 0; $i < count($data["productos"]); $i++) {
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetWidths(array(20, 30, 100, 20, 20));
        $pdf->Row4(array(
            $data["productos"][$i]['item'],
            $data["productos"][$i]['codigo'],
            utf8_decode($data["productos"][$i]['nombre']),
            "NIU",
            $data["productos"][$i]['cantidad'],
        ));
        $pdf->Ln(0.1);
    }

    // OBSERVACIONES
    if (!empty($data['observaciones'])) {
        $pdf->Ln(6);
        $pdf->SetWidths(array(190));
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Row1(array("OBSERVACIONES"));
        $pdf->Ln(1);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Row1(array(
            utf8_decode($data['observaciones'])
        ));
    }
}

print_r($pdf->Output('reporte.pdf', 'I'));
