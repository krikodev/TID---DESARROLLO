<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');

define("WIDTH_LOGO", 35);
define("HEIGHT_LOGO", 15);
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
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
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

    //Funcion especifica para manifiesto
    function datess($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 2.5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            // Check if the current cell is the "Apellidos y Nombres" column
            $a = ($i == 1) ? 'L' : (isset($this->aligns[$i]) ? $this->aligns[$i] : 'C');
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

// CABECERA
if ($this->data["HEADER"]["HEADER_EMPRESA"]["logo"] && file_exists($this->data["HEADER"]["HEADER_EMPRESA"]["logo"])) {
    $pdf->Image($this->data["HEADER"]["HEADER_EMPRESA"]["logo"], 8, 2, WIDTH_LOGO, HEIGHT_LOGO);
    $pdf->Ln(-5);
}
//
$pdf->SetFont('Helvetica', '', 5.9);
$pdf->Cell(43); // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(104));
$pdf->Row1(array('LIMA: OTR. TERMINAL MOLINA YERBATEROS AV. NICOLAS AYLLON 1352 - SAN LUIS - LIMA - LIMA CELULAR: 935246343'));
$pdf->Ln(0.1);
$pdf->Cell(43);  // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(104));
$pdf->Row1(array('HUANCAYO: OTR. TERMINAL HUANCAYO AV. EVITAMIENTO S/N Int. 11 - EL TAMBO - HUANCAYO - JUNIN CELULAR: 935246343'));
$pdf->Ln(0.1);
$pdf->Cell(43);  // Añade una celda vacía de 40 mm de ancho
$pdf->SetWidths(array(104));
$pdf->Row1(array("DOM. FISCAL: ASC. LOS YAROWILCAS Nro S/N PACHACAMAC - LIMA - LIMA CELULAR: 935246343"));
//
$pdf->Ln(-17);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->Cell(147);  // Añade una celda vacía de 145 mm de ancho
$pdf->SetWidths(array(50));
$pdf->Row5(array("RUC: " . $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"]));

$pdf->Ln(1);
$pdf->Cell(147);  // Añade una celda vacía de 145 mm de ancho
$pdf->SetWidths(array(50));
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->Row5(array("MANIFIESTO DE USUARIOS"));

$pdf->Ln(1);
$pdf->Cell(147);  // Añade una celda vacía de 145 mm de ancho
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetWidths(array(50));
$pdf->Row5(array("Nro 001-000" . $this->data['HEADER']['HEADER_DETALLE']['correlativo']));
//

// BODY
$pdf->Ln(2.5);
$pdf->SetWidths(array(28, 100, 20, 46));
$pdf->Row2(array(
    utf8_decode("CONDUCTOR: "),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["conductor_nombres"] . " " . $this->data["HEADER"]["HEADER_DETALLE"]["conductor_apellidos"]),
    utf8_decode("LICENCIA:"),
    $this->data["HEADER"]["HEADER_DETALLE"]["conductor_licencia"]
));

for ($i = 0; $i < count($this->data["HEADER"]["COPILOTOS"]); $i++) {
    $pdf->SetWidths(array(28, 100, 20, 46));
    $pdf->Row2(array(
        utf8_decode("COPILOTO: "),
        utf8_decode($this->data["HEADER"]["COPILOTOS"][$i]["copiloto_nombres"] . " " . $this->data["HEADER"]["COPILOTOS"][$i]["copiloto_apellidos"]),
        utf8_decode("LICENCIA: "),
        utf8_decode($this->data["HEADER"]["COPILOTOS"][$i]["copiloto_licencia"])
    ));
}

for ($i = 0; $i < count($this->data["HEADER"]["AYUDANTES"]); $i++) {
    $pdf->SetWidths(array(28, 100, 20, 46));
    $pdf->Row2(array(
        utf8_decode("AYUDANTE: "),
        utf8_decode($this->data["HEADER"]["AYUDANTES"][$i]["ayudante_nombres"] . " " . $this->data["HEADER"]["AYUDANTES"][$i]["ayudante_apellidos"]),
        "",
        ""
    ));
}

$pdf->SetWidths(array(28, 20, 20, 30, 60, 36));
$pdf->Row2(array(
    utf8_decode("PLACA:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_placa"]),
    utf8_decode("MARCA:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_marca"]),
    utf8_decode("TARJETA UNICA DE CIRCULACION:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_tuc"]),
));
$pdf->SetWidths(array(28, 43, 30, 43, 25, 25));
$pdf->Row2(array(
    utf8_decode("LUGAR ORIGEN:"),
    utf8_decode(strtoupper($this->data["HEADER"]["HEADER_DETALLE"]["terminal_origen"])),
    utf8_decode("LUGAR DESTINO:"),
    utf8_decode(strtoupper($this->data["HEADER"]["HEADER_DETALLE"]["terminal_destino"])),
    utf8_decode("FECHA VIAJE:"),
    date("d-m-Y", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"]))
));
$pdf->SetWidths(array(35, 8, 40, 8, 28, 25, 25, 25));
$pdf->Row2(array(
    utf8_decode("CANTIDAD ASIENTOS:"),
    $this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_cantidad_asiento"],
    utf8_decode("CANTIDAD ENBARCADOS:"),
    count($this->data["DETALLE"]),
    utf8_decode("NRO DE POLIZA:"),
    utf8_decode($this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_num_poliza"]),
    utf8_decode("HORA VIAJE:"),
    date("H:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_hora_salida"]))
));
$pdf->Ln(1);

//TABLA
$pdf->SetFont('Helvetica', '', 6.5);
$pdf->SetWidths(array(10, 60, 8, 18, 12, 25, 24, 18, 22, 18));
$pdf->Row4(array(
    'Asiento',
    'APELLIDOS Y NOMBRES',
    'Edad',
    'N Documento',
    'N Celular',
    'DESTINO',
    'Serie-Nro de Boleto',
    'IMPORTE',
    'Observaciones',
));
$importe_total = 0.0;
$pdf->Ln(0.9);
for ($i = 0; $i < count($this->data["DETALLE"]); $i++) {
    $pdf->SetFont('Helvetica', '', 7);
    $pdf->SetWidths(array(10, 60, 8, 18, 12, 25, 24, 18, 22));
    $anio_nacimiento = (int)explode("-", $this->data["DETALLE"][$i]["cliente_fecha_nacimiento"])[0];
    $edad_cliente = date("Y") - $anio_nacimiento;
    $pdf->datess(array(
        $this->data["DETALLE"][$i]["num_asiento"],
        utf8_decode($this->data["DETALLE"][$i]["cliente_nombres"] . " " . $this->data["DETALLE"][$i]["cliente_apellidos"]),
        $edad_cliente > 100 ? '' : $edad_cliente,
        $this->data["DETALLE"][$i]["cliente_num_docu"],
        $this->data["DETALLE"][$i]["cliente_celular"],
        $this->data["DETALLE"][$i]["t_d_distrito"],
        $this->data["DETALLE"][$i]["venta_serie"] . " - " . substr("00000000", 0, -strlen($this->data["DETALLE"][$i]["venta_correlativo"])) . $this->data["DETALLE"][$i]["venta_correlativo"],
        'S/ ' . $this->data["DETALLE"][$i]["importe"],
        $this->data["DETALLE"][$i]["venta_obs"],
    ));
    $importe_total += $this->data["DETALLE"][$i]["importe"];
    $pdf->Ln(0.15);
}

$pdf->Ln(20);
$pdf->SetFont('Helvetica', 'B', 7.5);
$pdf->SetWidths(array(64.6, 64.6, 64.6));
$pdf->Row3(array(
    '___________________________',
    '___________________________',
    '___________________________'
));


$pdf->SetWidths(array(64.6, 64.6, 64.6));
$pdf->Row3(array(
    'V. B. EMPRESA',
    'CONDUCTOR',
    'S/. ' . $importe_total
));

print_r($pdf->Output('reporte.pdf', 'I'));
