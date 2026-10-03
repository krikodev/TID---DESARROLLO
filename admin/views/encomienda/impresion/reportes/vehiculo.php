<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');


define("WIDTH_LOGO", 45);
define("HEIGHT_LOGO", 20);
define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8.5);
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
            $this->MultiCell($w, 4, $data[$i], 0, $a);
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
            $this->MultiCell($w, 5, $data[$i], 1, $a);
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

$pdf = new FPDF_CellFiti('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 0, 0);

//INFORMACION EMPRESA
if (file_exists(Session::get("data_empresa")["logo"])) {
    $pdf->Image(Session::get("data_empresa")["logo"], 10, 2, WIDTH_LOGO, HEIGHT_LOGO);
    $pdf->Ln(5);
}
//Dirección    
$pdf->Ln(-8);
$pdf->SetFont('Helvetica', '', 7);
$pdf->Cell(60);  // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(150));
$pdf->Row1(array("LIMA: OTR. TERMINAL MOLINA YERBATEROS AV. NICOLAS AYLLON 1352 - SAN LUIS - LIMA - LIMA CELULAR: 935246343"));
$pdf->Ln(0.1);
$pdf->Cell(60);  // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(150));
$pdf->Row1(array("HUANCAYO: OTR. TERMINAL HUANCAYO AV. EVITAMIENTO S/N Int. 11 - EL TAMBO - HUANCAYO - JUNIN CELULAR: 935246343"));
$pdf->Cell(60);  // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(150));
$pdf->Row1(array('DOM. FISCAL: ASC. LOS YAROWILCAS Nro S/N PACHACAMAC - LIMA - LIMA CELULAR: 935246343'));
$pdf->Ln(0.1);

//Ruc
$pdf->Ln(-18);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(225);  // Añade una celda vacía de 145 mm de ancho
$pdf->SetWidths(array(50));
$ruc = utf8_decode($this->data["EMPRESA"]["num_docu"]);
$pdf->Row5(array("RUC: " . $ruc));

$pdf->Ln(0.1);
$pdf->Cell(225);  // Añade una celda vacía de 145 mm de ancho
$pdf->SetWidths(array(50));
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Row5(array("REPORTE DE ENCOMIENDAS POR VEHICULO"));

// BODY
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(10);
$pdf->SetWidths(array(30, 80, 17, 40, 25, 80));
$pdf->Row2(array(
    utf8_decode("TERMINAL: "),
    utf8_decode($this->data["HEADER"]["terminal_nombre"]),
    utf8_decode("CELULAR:"),
    $this->data["HEADER"]["terminal_celular"],
    utf8_decode("DIRECCION:"),
    utf8_decode($this->data["HEADER"]["terminal_direccion_comercial"]),

));
$pdf->Ln(0);

$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Ln(0);
$pdf->SetWidths(array(30, 80, 17, 40, 25, 40, 20, 30));
$pdf->Row2(array(
    utf8_decode("CONDUCTOR: "),
    utf8_decode($this->data["HEADER"]["conductor_nombres"] . " " . $this->data["HEADER"]["conductor_nombres"]),
    utf8_decode("CELULAR:"),
    $this->data["HEADER"]["conductor_celular"],
    utf8_decode("LICENCIA:"),
    utf8_decode($this->data["HEADER"]["conductor_licencia"]),
    utf8_decode("PLACA:"),
    utf8_decode($this->data["HEADER"]["vehiculo_placa"]),

));
$pdf->Ln(3);

//TABLA
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetWidths(array(20, 40, 25, 19, 18, 12, 40, 18, 25, 25, 21, 14));
$pdf->Row4(array(
    'FECHA',
    'REMITENTE',
    'Num. COMPROBANTE',
    'TERMINAL ORIGEN',
    'TERMINAL DESTINO',
    'CANT.',
    'DESCRIPCION',
    'P.UNITARIO',
    'FORMA DE PAGO',
    'MEDIO DE PAGO',
    'ESTADO',
    'TOTAL'
));

for ($i = 0; $i < count($this->data["BODY"]); $i++) {
    $pdf->Ln(0.1);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetWidths(array(20, 40, 25, 19, 18, 12, 40, 18, 25, 25, 21, 14));
    $pdf->Row4(array(
        implode("-", array_reverse(explode("-", $this->data["BODY"][$i]["fecha_salida"]))),
        $this->data["BODY"][$i]["remitente_nombres"] . " " . $this->data["BODY"][$i]["remitente_apellidos"],
        $this->data["BODY"][$i]["venta_serie"] . "-" . substr("00000000", 0, -strlen($this->data["BODY"][$i]["venta_correlativo"])) . $this->data["BODY"][$i]["venta_correlativo"],
        $this->data["BODY"][$i]["terminal_origen"],
        $this->data["BODY"][$i]["terminal_destino"],
        $this->data["BODY"][$i]["encomienda_cantidad"],
        $this->data["BODY"][$i]["encomienda_producto"],
        $this->data["BODY"][$i]["encomienda_precio_unitario"],
        $this->data["BODY"][$i]["forma_pago"] ?? '---',
        $this->data["BODY"][$i]["medio_pago"] ?? '---',
        $this->data["BODY"][$i]["encomienda_estado"],
        $this->data["BODY"][$i]["total"]
    ));
}



print_r($pdf->Output('reporte.pdf', 'I'));
