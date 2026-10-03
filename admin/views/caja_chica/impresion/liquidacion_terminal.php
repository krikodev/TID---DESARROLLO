<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');


define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 9);
define("TEXT_WIDTH_SIZE_BODY", 40);

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
        $h = 3 * $nb;
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
            $this->MultiCell($w, 3, $data[$i], 0, $a);
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
    function Row3($data)
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
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            $this->Rect($x, $y, $w, $h);
            // Print the text
            $this->MultiCell($w, 4, $data[$i], 0, $a);
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
$pdf->SetMargins(10, 10, 0);

// CABECERA
$pdf->SetFont('Helvetica', '', 10);
$pdf->Cell(80, 0, '', 0, 1, 'C');
$pdf->Ln(-4);

// BODY
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(30, 160));
$pdf->Row2(array(
    utf8_decode("LIQUIDACION DE:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["terminal_origen"])
));
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(40, 50, 40, 30, 25, 30));
$pdf->Row2(array(
    utf8_decode("Fecha de Liquidación:"),
    isset($this->data["HEADER"]["HEADER_DETALLE"]["fecha_inicio_cajaChica"]) ? date("d-m-Y", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["fecha_inicio_cajaChica"])) : '---',
    utf8_decode("Hora de Liquidación:"),
    isset($this->data["HEADER"]["HEADER_DETALLE"]["fecha_inicio_cajaChica"]) ? date("H:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["fecha_inicio_cajaChica"])) : '---',
));
$pdf->SetWidths(array(40, 50, 40, 30, 25, 30));
$pdf->Row2(array(
    utf8_decode("Fecha de Impresión:"),
    date("d-m-Y"),
    utf8_decode("Hora de Impresión:"),
    date("H:i A"),
));
$pdf->SetWidths(array(40, 65, 30, 65));
$pdf->Row2(array(
    utf8_decode("Terminal:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["terminal_origen"]),
));
$pdf->Ln(5);
//TABLA
//Columnas
$total_num_ventas = 0;
$total_ventasPasaje = 0.00;
if (isset($this->data["DETALLE"]["PASAJE"]) && $this->data["DETALLE"]["PASAJE"]) {
    $pdf->Ln(7);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(190, 0, utf8_decode("PASAJE"), 0, 1, 'C');
    $pdf->Ln(7);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetWidths(array(20, 50, 20, 20, 20, 30, 30));
    $pdf->Row3(array(
        utf8_decode("Asiento"),
        utf8_decode("Cliente"),
        utf8_decode("Nro. Documento"),
        utf8_decode("Estado asiento"),
        utf8_decode("S-N Boleta"),
        utf8_decode("Destino"),
        utf8_decode("Importe"),
    ));

    for ($i = 0; $i < count($this->data["DETALLE"]["PASAJE"]); $i++) {
        if ($this->data["DETALLE"]["PASAJE"][$i]["pisos"]) {
            $pdf->Ln(4);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell(190, 0, utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["medio_pago"]), 0, 1, 'C');
            $pdf->Ln(4);
            $total_ventas_medioPago = 0.00;
            $num_ventas = 0;
            for ($x = 0; $x < count($this->data["DETALLE"]["PASAJE"][$i]["pisos"]); $x++) {
                $piso = $this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][0]["num_piso"];
                $pdf->SetFont('Helvetica', 'B', 9);
                $pdf->Cell(190, 0, "_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ Piso: $piso _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _", 0, 1, 'C');
                $pdf->Ln(4);
                $num_ventas += count($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x]);
                $total_num_ventas += count($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x]);
                for ($y = 0; $y < count($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x]); $y++) {
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetWidths(array(20, 50, 20, 20, 20, 30, 30));
                    $pdf->Row1(array(
                        $this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["num_asiento"],
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["cliente_nombres"] . " " . $this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["cliente_apellidos"]),
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["cliente_num_docu"]),
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["estado_asiento"]),
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["venta_serie"] . " - " . substr("00000000", 0, -strlen($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["venta_correlativo"])) . $this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["venta_correlativo"]),
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["terminal_destino"]),
                        utf8_decode($this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["importe"]),
                    ));
                    $pdf->Ln(1.5);
                    $total_ventas_medioPago += $this->data["DETALLE"]["PASAJE"][$i]["pisos"][$x][$y]["importe"];
                }
            }
            $total_ventasPasaje += $total_ventas_medioPago;
            $pdf->Ln(3);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetWidths(array(25, 115, 30, 20));
            $pdf->Row2(array(
                utf8_decode("N VENTAS:"),
                $num_ventas,
                utf8_decode("TOTAL: S/."),
                number_format($total_ventas_medioPago, 2, '.')
            ));
        }
    }

    $pdf->Cell(190, 0, '=======================================================================================================', 0, 1, 'C');
    //
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(25, 115, 30, 30));
    $pdf->Row2(array(
        utf8_decode("N VENTAS:"),
        $total_num_ventas,
        utf8_decode("TOTAL: S/."),
        number_format($total_ventasPasaje, 2, '.')
    ));
    //
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(127, 45, 25));
    $pdf->Row2(array(
        utf8_decode(""),
        "COMISION (0.00):S/.",
        "0.00"
    ));
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(98, 74, 25));
    $pdf->Row2(array(
        utf8_decode(""),
        "DESC. EMBARQUE/DESEMBARQUE:S/.",
        "0.00"
    ));
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(132, 38, 40));
    $pdf->Row2(array(
        utf8_decode(""),
        "PAGO TOTAL:S/",
        number_format($total_ventasPasaje, 2, '.')
    ));
}



// Encomienda

$pdf->Ln(5);
$total_num_ventas = 0;
$total_ventasEncomienda = 0.00;
$total_ventas_medioPago = 0.00;
if (isset($this->data["DETALLE"]["ENCOMIENDA"]) && $this->data["DETALLE"]["ENCOMIENDA"]) {
    $pdf->Ln(7);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(190, 0, utf8_decode("ENCOMIENDA"), 0, 1, 'C');
    $pdf->Ln(7);
    //TABLA
    //Columnas
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetWidths(array(50, 20, 20, 30, 20, 30, 20));
    $pdf->Row3(array(
        utf8_decode("Cliente"),
        utf8_decode("Nro. Documento"),
        utf8_decode("Estado"),
        utf8_decode("S-N Comprobante"),
        utf8_decode("Estado Pago"),
        utf8_decode("Destino"),
        utf8_decode("Importe"),
    ));
    for ($i = 0; $i < count($this->data["DETALLE"]["ENCOMIENDA"]); $i++) {
        if ($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"]) {
            $pdf->Ln(4);
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell(190, 0, utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["medio_pago"]), 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->Cell(190, 0, "_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _", 0, 1, 'C');
            $pdf->Ln(4);
            $num_ventas = count($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"]);
            $total_num_ventas += $num_ventas;
            $total_ventas_medioPago = 0;
            for ($x = 0; $x < count($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"]); $x++) {
                if ($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"]) {
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetWidths(array(50, 20, 20, 30, 20, 30, 20));
                    $pdf->Row1(array(
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["cliente_nombres"] . " " . $this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["cliente_apellidos"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["cliente_num_docu"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["estado_encomienda"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["venta_serie"] . " - " . substr("00000000", 0, -strlen($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["venta_correlativo"])) . $this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["venta_correlativo"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["pago_encomienda"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["terminal_destino"]),
                        utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["importe"]),
                    ));
                    $pdf->Ln(1.5);
                    $total_ventas_medioPago += utf8_decode($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"][$x]["importe"]);
                }
            }
            $total_ventasEncomienda += $total_ventas_medioPago;
            $pdf->Ln(3);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetWidths(array(25, 125, 25, 10));
            $pdf->Row2(array(
                utf8_decode("N VENTAS:"),
                $num_ventas,
                utf8_decode("TOTAL: S/."),
                number_format($total_ventas_medioPago, 2, '.')
            ));
        }
    }
    $pdf->Cell(190, 0, '=======================================================================================================', 0, 1, 'C');
    //
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(25, 125, 25, 30));
    $pdf->Row2(array(
        utf8_decode("N VENTAS:"),
        $total_num_ventas,
        utf8_decode("TOTAL: S/."),
        number_format($total_ventasEncomienda, 2, '.')
    ));
    //
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(137, 38, 30));
    $pdf->Row2(array(
        utf8_decode(""),
        "COMISION (0.00):S/.",
        "0.00"
    ));
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(108, 67, 20));
    $pdf->Row2(array(
        utf8_decode(""),
        "DESC. EMBARQUE/DESEMBARQUE:S/.",
        "0.00"
    ));
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(143, 32, 20));
    $pdf->Row2(array(
        utf8_decode(""),
        "PAGO TOTAL:S/",
        number_format($total_ventasEncomienda, 2, '.')
    ));
}

// TOTAL PASAJE Y ENCOMIENDA
$pdf->Ln(10);
$pdf->Cell(190, 0, '=======================================================================================================', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetWidths(array(150, 26, 20));
$pdf->Row2(array(
    utf8_decode(""),
    "TOTAL:S/",
    number_format($total_ventasPasaje + $total_ventasEncomienda, 2, '.')
));


$pdf->Output('reporte.pdf', 'I');
