<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');

define("WIDTH_LOGO", 25);
define("HEIGHT_LOGO", 25);
define("TEXT_SIZE_RAZON_SOCIAL", 10);
define("TEXT_SIZE_HEADER", 9);
define("TEXT_SIZE_COMPROBANTE_SERIE", 11);
define("TEXT_SIZE_BODY", 9);
define("TEXT_WIDTH_SIZE_BODY", 25);
define("TEXT_SIZE_BODY_PASAJERO", 10);
define("TEXT_SIZE_BODY_ORIGEN_DESTINO", 13);
define("TEXT_SIZE_BODY_PISO", 13);
define("TEXT_SIZE_BODY_ASIENTO", 20);

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
            if ($i === 0) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 2) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 4) {
                $this->SetFont('Helvetica', 'B', TEXT_SIZE_BODY); // Establece el tipo de letra deseado
            } elseif ($i === 6) {
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
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            //$this->Rect($x,$y,$w,$h);
            //
            if ($i === 0) {
                $this->SetFont('Helvetica', 'B', 7.5); // Establece el tipo de letra deseado
            } elseif ($i === 2) {
                $this->SetFont('Helvetica', 'B', 7.5); // Establece el tipo de letra deseado
            } elseif ($i === 4) {
                $this->SetFont('Helvetica', 'B', 7.5); // Establece el tipo de letra deseado
            } elseif ($i === 6) {
                $this->SetFont('Helvetica', 'B', 7.5); // Establece el tipo de letra deseado
            } else {
                $this->SetFont('Helvetica', '', 7.5); // Establece el tipo de letra para otras columnas
            }

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

    function ChapterBody($body)
    {
        // Cuerpo del capítulo
        $this->SetFont('Helvetica', '', 12);
        // Utiliza MultiCell para crear una celda que abarque dos columnas
        $this->MultiCell(80, 10, $body, 1);
        $this->Ln();
    }

    function CheckPageBreak($h)
    {
        // If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
        $this->SetMargins(2, 8, 0);
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


$pdf = new FPDF_CellFiti('P', 'mm', array(80, 350));
$pdf->AddPage();
$pdf->SetMargins(2, 0, 0);

// CABECERA
if ($this->data["EMISOR"]["empresa_logo"] && file_exists($this->data["EMISOR"]["empresa_logo"])) {
    $pdf->Image($this->data["EMISOR"]["empresa_logo"], 30, 5, WIDTH_LOGO, HEIGHT_LOGO);
    $pdf->Ln(16);
}
$pdf->SetFont('Helvetica', '', 9);
$pdf->MultiCell(76, 5, "", 0, 'C');
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->MultiCell(76, 5, $this->data["EMISOR"]["empresa_razon_social"], 0, 'C');
$pdf->Cell(76, 4, "RUC: " . $this->data["EMISOR"]["empresa_ruc"], 0, 1, 'C');
$pdf->Ln(0.5);
//Frase de la empresa
if ($this->data['EMISOR']['frase_empresa']) {
    $pdf->SetWidths(array(76));
    $pdf->Row3(array($this->data['EMISOR']['frase_empresa']));
    $pdf->Ln(1);
}

// DOM FISCAL
$pdf->SetFont('Helvetica', '', 6);
$pdf->SetWidths(array(76));
$pdf->Row3(array('DOM. FISCAL' . ': ' . $this->data['EMISOR']['direccion']));
$pdf->Ln(1);

//Utilizando Row3
$pdf->SetFont('Helvetica', '', 6);
for ($i = 0; $i < count($this->data["TERMINALES"]); $i++) {
    $pdf->SetWidths(array(76));
    $pdf->Row3(array(utf8_decode($this->data['TERMINALES'][$i]['nombre'] . ': ' . $this->data['TERMINALES'][$i]['direccion_fiscal'] . '- Cel: ' . $this->data['TERMINALES'][$i]['celular'])));
    $pdf->Ln(1);
}
//
$pdf->Cell(76, 0, '', 'B');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell(76, 4, utf8_decode($this->data["CABECERA"]["tp_comprobante"]), 0, 'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->Cell(76, 4, $this->data["CABECERA"]["serie"] . " - " . substr("00000000", 0, -strlen($this->data["CABECERA"]["correlativo"])) . $this->data["CABECERA"]["correlativo"], 0, 1, 'C');
$pdf->Ln(2);
$pdf->Cell(76, 0, '', 'T');
$pdf->Ln(2);
//Utilizando Row2
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetWidths(array(21, 55));
$pdf->Row2(array(
    utf8_decode($this->data["CLIENTE"]["cliente_tp_docu"]),
    $this->data["CLIENTE"]["cliente_num_docu"]
));

$pdf->SetWidths(array(21, 55));
$pdf->Row2(array(
    utf8_decode($this->data["CLIENTE"]["cliente_tp_docu"] == "DNI" ? 'CLIENTE:' : 'R. SOCIAL:'),
    utf8_decode($this->data["CLIENTE"]["cliente_nombres"]) . " " . utf8_decode($this->data["CLIENTE"]["cliente_apellidos"])
));

$pdf->SetWidths(array(21, 55));
$pdf->Row2(array(
    utf8_decode('DIRECCION:'),
    utf8_decode($this->data["CLIENTE"]["cliente_direccion"])
));

if ($this->data["CLIENTE"]["pasajero_nombres"]) {
    $pdf->SetWidths(array(21, 55));
    $edad_cliente = explode('-', $this->data["CLIENTE"]["pasajero_fecha_nacimiento"])[0] != "00" ? date("Y") - explode("-", $this->data["CLIENTE"]["pasajero_fecha_nacimiento"])[0] : '---';
    $pdf->Row2(array(
        utf8_decode('PASAJERO:'),
        utf8_decode($this->data["CLIENTE"]["pasajero_nombres"] . " " . $this->data["CLIENTE"]["pasajero_apellidos"])
    ));

    $pdf->SetWidths(array(10, 17, 11, 10, 30));
    $pdf->Row2(array(
        utf8_decode($this->data["CLIENTE"]["pasajero_tp_docu"]),
        $this->data["CLIENTE"]["pasajero_num_docu"],
        'Edad:',
        $edad_cliente,
        utf8_decode($this->data["CLIENTE"]["pasajero_nacionalidad"]),
    ));
}
$pdf->Ln(2);
$pdf->Cell(76, 0, '', 'T');
$pdf->Ln(2);
//Utilizando Row1
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(28, 18.5, 19.5, 10));
$pdf->Row1(array(
    utf8_decode('Unidad medida:'),
    'Servicio',
    utf8_decode('Cantidad:'),
    '1'
));
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->MultiCell(76, 4, 'SERVICIO DE TRANSPORTE DE PASAJEROS', 0, 'C');
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(18, 30));
$pdf->Row1(array(
    utf8_decode('ORIGEN:'),
    utf8_decode(strtoupper($this->data["CABECERA"]["terminal_origen"]))
));
$pdf->SetWidths(array(18, 30));
$pdf->Row1(array(
    utf8_decode('DESTINO:'),
    utf8_decode(strtoupper($this->data["CABECERA"]["terminal_destino"]))
));
$pdf->Ln(-4);
$pdf->Cell(48);
$pdf->Cell(14, 4, 'Asiento:', 0, 0, 'C');
$pdf->Ln(-4);
$pdf->Cell(62);
$pdf->SetFont('Helvetica', 'B', 13);
$pdf->MultiCell(14, 8, $this->data["ITEMS"][0]["num_asiento"] . ' - ' . trim($this->data["ITEMS"][0]["piso"]), 0, 'C');
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(31, 5, utf8_decode('Fecha-Hora de viaje:'), 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', 11.5);
$pdf->MultiCell(45, 5, date("d-m-Y", strtotime($this->data["CABECERA"]["programacion_fecha_salida"])) . " " . date("H:i A", strtotime($this->data["CABECERA"]["programacion_hora_salida"])), 0, 'L');
//Utilizando Row2
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(0, 19, 18));
$pdf->Row2(array('', utf8_decode('Exonerado:'), 'S/. ' . $this->data["CABECERA"]["op_exonerada"]));
$pdf->SetWidths(array(0, 19, 18));
$pdf->Row2(array('', utf8_decode('Grabado:'), 'S/. 0.00'));
$pdf->SetWidths(array(0, 19, 18));
$pdf->Row2(array('', utf8_decode('IGV:'), 'S/. 0.00'));
$pdf->SetWidths(array(0, 19, 18));
$pdf->Row2(array('', utf8_decode('IMPORTE:'), 'S/. ' . $this->data["CABECERA"]["total"]));
$pdf->Ln(-14);
$pdf->Cell(37);
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->MultiCell(39, 3, 'Servicios exonerados del impuesto General a las ventas', 0, 'C');
$pdf->Cell(37);
$pdf->MultiCell(39, 3, 'https://www.sunat.gob.pe/legistacion/igv/ley/apendice2.pdf', 0, 'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(0, 10, 66));
$pdf->Row2(array('', utf8_decode('SON:'), numtoletras($this->data["CABECERA"]["total"])));
$pdf->Ln(1);
if ($this->data["CABECERA"]["cod_qr"]) {
    // Verificamos si la cadena contiene "private/empresa/"
    if (strpos($this->data["CABECERA"]["cod_qr"], 'private/empresa/') !== false) {
        // Verificamos si el archivo local existe
        if (file_exists($this->data["CABECERA"]["cod_qr"])) {
            // Cargamos la imagen desde la ruta local
            $pdf->Image($this->data["CABECERA"]["cod_qr"], 05, $pdf->GetY(), 25, 25);
        }
    }
    // Verificamos si es una URL con "https://"
    else if (strpos($this->data["CABECERA"]["cod_qr"], 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $this->data['CABECERA']['cod_qr']);
        // Cargamos la imagen desde la URL
        $pdf->Image($urlQR, 05, $pdf->GetY(), 25, 25);
    }
}

$pdf->Cell(32, 15, '', 0, 0, 'C');
$pdf->SetFont('Helvetica', '', 7);
$pdf->MultiCell(44, 3, 'Resoluciones de Superintendencia N.182-2016-SUNAT/ N.318-2017-SUNAT', 0, 'C');
$pdf->SetFont('Helvetica', '', 7);
$pdf->Cell(32);
$pdf->MultiCell(44, 3, 'Usted puede consultar su Factura Electronica desde su clave SOL', 0, 'C');
$pdf->SetFont('Helvetica', '', 7);
$pdf->Cell(32);
$pdf->SetWidths(array(21, 23));
$pdf->Row1(array(utf8_decode('Forma de pago:'), utf8_decode(strtoupper($this->data["CABECERA"]["forma_pago"]))));
$pdf->Cell(32);
$pdf->Row1(array(utf8_decode('Metodo de pago:'), utf8_decode(strtoupper($this->data["CABECERA"]["medio_pago"]))));
$pdf->Cell(32);
$pdf->SetWidths(array(17, 27));
$pdf->Row1(array(utf8_decode('F.H Emision:'), date("d-m-Y H:i A", strtotime($this->data["CABECERA"]["fecha_emision"]))));
$pdf->Cell(32);
$pdf->SetWidths(array(17, 27));
$pdf->Row1(array(utf8_decode('Vendedor(a):'), utf8_decode($this->data["CABECERA"]["vendedor_nombres"] . " " . $this->data["CABECERA"]["vendedor_apellidos"])));
//Linea
$pdf->Ln(1);
$pdf->Cell(76, 0, '', 'T');
$pdf->Ln(1);
//
//Condiciones de servicios
//Utilizando Row3
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->SetWidths(array(76));
$pdf->Row3(array('CONDICIONES DE SERVICIOS DE VIAJE'));
$pdf->Ln(1);
//Utilizando Row1
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetWidths(array(76));
$pdf->Row1(array(utf8_decode('Al recibir el presente DOCUMENTO acepto todos los términos y condiciones del contrato del servicio de transporte detallado en el letrero, banner y/o panel a la vista ubicados en el counter de ventas al momento de la compra, los cuales también se encuentran publicados en la pagina web.')));
$pdf->Ln(1);
$pdf->SetWidths(array(76));
$pdf->Row1(array(utf8_decode('Todo pasajero tiene derecho a 15 Kg. de equipaje, de exceder deberá pagar la diferencia de peso según tarifa de la empresa.')));
$pdf->Ln(1);
$pdf->SetWidths(array(76));
$pdf->Row1(array(URL_PAGE_WEB_COMPROBANTES));
$pdf->Ln(1);
//Linea
$pdf->Cell(76, 0, ' _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _', '');
$pdf->Ln(4);
//Ticket
//Utilizando Row3
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetWidths(array(76));
$pdf->Row3(array('CORTE CON LA MANO EN LA LINEA'));
$pdf->Ln(3);
//Utilizando Row1
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->SetWidths(array(8, 23, 8, 23, 14));
$pdf->Row1(array(utf8_decode('DE:'), utf8_decode(strtoupper($this->data["CABECERA"]["terminal_origen"])), utf8_decode('A:'), utf8_decode(strtoupper($this->data["CABECERA"]["terminal_destino"])), $this->data["CABECERA"]["vehiculo_placa"]));
$pdf->Ln(1);
//Utilizando Row3
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetWidths(array(45, 7, 24));
$pdf->Row3(array($this->data["CABECERA"]["serie"] . " - " . substr("00000000", 0, -strlen($this->data["CABECERA"]["correlativo"])) . $this->data["CABECERA"]["correlativo"], 'S/', $this->data["CABECERA"]["op_exonerada"]));
$pdf->Ln(1);
//Utilizando Row4
$pdf->SetFont('Helvetica', '', 7.5);
$pdf->SetWidths(array(18, 30, 15, 13));
$pdf->Row4(array(utf8_decode('PASAJERO:'), utf8_decode($this->data["CLIENTE"]["cliente_nombres"]) . " " . utf8_decode($this->data["CLIENTE"]["cliente_apellidos"]), 'ASIENTO:', trim($this->data["ITEMS"][0]["num_asiento"]) . ' - ' . trim($this->data["ITEMS"][0]["piso"])));
$pdf->Ln(1);
$pdf->SetWidths(array(18, 20, 20, 18));
$pdf->Row4(array(utf8_decode('Fecha-Viaje:'), date("d-m-Y", strtotime($this->data["CABECERA"]["programacion_fecha_salida"])), 'Hora-Viaje:', date("H:i A", strtotime($this->data["CABECERA"]["programacion_hora_salida"]))));
$pdf->Ln(1);
$pdf->SetWidths(array(18, 30, 15, 13));
$pdf->Row4(array(utf8_decode('F.H emision:'), date('d-m-Y H:i A', strtotime($this->data["CABECERA"]["fecha_emision"])), '', ''));
$pdf->Ln(1);
$pdf->SetWidths(array(18, 58));
$pdf->Row4(array(utf8_decode('Usuario:'), utf8_decode($this->data["CABECERA"]["vendedor_nombres"] . " " . $this->data["CABECERA"]["vendedor_apellidos"])));
$pdf->Ln(1);
/////
print_r($pdf->Output('ticket.pdf', 'I'));
