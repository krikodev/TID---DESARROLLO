<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

//require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');

define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 9.6);
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
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }

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
            if ($i === 0) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 2) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 4) {
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
$pdf->SetMargins(10, 0, 0);


// CABECERA
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(190, 0, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(190, 0, 'RELACION DE REMISION DE ENCOMIENDAS (MEDIANO)', 0, 1, 'C');
$pdf->Ln(1);
$pdf->Cell(190, 0, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'C');
$pdf->Ln(4);

//-------------------------------------------------------
// BODY
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->SetWidths(array(20, 70, 20, 80));
$pdf->Row2(array(
    utf8_decode("ORIGEN:"),
    strtoupper($this->data["HEADER"]["terminal_origen_nombre"]),
    "DESTINO:",
    strtoupper($this->data["HEADER"]["terminal_destino_nombre"]),
));
$pdf->SetWidths(array(20, 45, 15, 40, 30, 40));
$pdf->Row2(array(
    utf8_decode("FECHA:"),
    implode("-", array_reverse(explode("-", $this->data["HEADER"]["fecha_salida"]))),
    utf8_decode("HORA:"),
    $this->data["HEADER"]["hora_salida"],
    utf8_decode("FLOTA (PLACA):"),
    $this->data["HEADER"]["vehiculo_placa"],
));
$pdf->SetWidths(array(30, 65, 27, 68));
$pdf->Row2(array(
    utf8_decode("Nro LICENCIA:"),
    $this->data["HEADER"]["conductor_licencia"],
    "CONDUCTOR:",
    utf8_decode($this->data["HEADER"]["conductor_nombres"] . " " . $this->data["HEADER"]["conductor_apellidos"],)
));
$pdf->Ln(4);
//-------------------------------------------------------
//TABLA

// Columnas
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->SetWidths(array(30, 50, 10, 60, 15, 30));
$pdf->Row1(array(
    "Comprobante",
    "CONSIGNATARIO",
    "Cant",
    "CONTENIDO",
    "P. U.",
    "IMPORTE",
));
$suma_total = 0;
$pdf->Cell(190, 0, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'C');
for ($i = 0; $i < count($this->data["BODY"]); $i++) {
    //Detalles
    $pdf->Ln(3);
    $pdf->SetFont('Helvetica', '', 9.5);
    $pdf->SetWidths(array(30, 50, 10, 60, 15, 30));
    $pdf->Row1(array(
        $this->data["BODY"][$i]["venta_serie"] . "-" . substr("00000000", 0, -strlen($this->data["BODY"][$i]["venta_correlativo"])) . $this->data["BODY"][$i]["venta_correlativo"],
        utf8_decode("DNI: " . $this->data["BODY"][$i]["remitente_num_docu"] . " - " . $this->data["BODY"][$i]["remitente_nombres"] . " " . $this->data["BODY"][$i]["remitente_apellidos"]),
        $this->data["BODY"][$i]["encomienda_cantidad"],
        utf8_decode($this->data["BODY"][$i]["encomienda_producto"]),
        number_format($this->data["BODY"][$i]["encomienda_precio_unitario"], 2),
        number_format(($this->data["BODY"][$i]["encomienda_cantidad"] * $this->data["BODY"][$i]["encomienda_precio_unitario"]), 2)
    ));
    $pdf->Cell(190, 0, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', 0, 1, 'C');
    $suma_total += number_format(($this->data["BODY"][$i]["encomienda_cantidad"] * $this->data["BODY"][$i]["encomienda_precio_unitario"]), 2);
}

//
$pdf->Ln(3);
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetWidths(array(40, 87, 40, 23));
$pdf->Row2(array(
    "Total de encomiendas:",
    count($this->data["BODY"]),
    "COMISION (0.00 %) S/. :",
    "0.00"
));
$pdf->SetFont('Helvetica', '', 9.5);
$pdf->SetWidths(array(40, 87, 40, 23));
$pdf->Row2(array(
    "",
    "",
    "DESCUENTO S/. :",
    "0.00"
));
$pdf->SetFont('Helvetica', 'B', 9.5);
$pdf->SetWidths(array(40, 87, 40, 23));
$pdf->Row2(array(
    "",
    "",
    "TOTAL S/. :",
    number_format($suma_total, 2)
));

$pdf->Output('I', 'reporte.pdf');

// print_r($pdf->Output('reporte.pdf', 'F'));
