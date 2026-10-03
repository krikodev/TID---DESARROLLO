<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/pdf/cellfit.php');

define("WIDTH_LOGO", 30);
define("HEIGHT_LOGO", 20);
define("TEXT_SIZE_RAZON_SOCIAL", 10);
define("TEXT_SIZE_HEADER", 9);
define("TEXT_SIZE_COMPROBANTE_SERIE", 11);
define("TEXT_SIZE_BODY", 9);
define("TEXT_WIDTH_SIZE_BODY", 25);
define("TEXT_SIZE_BODY_PASAJERO", 10);
define("TEXT_SIZE_BODY_ORIGEN_DESTINO", 13);
define("TEXT_SIZE_BODY_PISO", 13);
define("TEXT_SIZE_BODY_ASIENTO", 20);

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
}

$pdf = new FPDF_CellFiti('P', 'mm', array(80, 200));
$pdf->AddPage();
$pdf->SetMargins(0, 0, 0, 0);

// CABECERA
$pdf->Image('public/image/logo.png', 22, 4, WIDTH_LOGO, HEIGHT_LOGO);
$pdf->Ln(14);
$pdf->SetFont('Helvetica', '', 6);
$pdf->Cell(78, 4, '', 0, 1, 'C');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_RAZON_SOCIAL);
$pdf->MultiCell(75, 5, $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"], 0, 'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_HEADER);
$pdf->Cell(78, 4, "RUC: " . $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"], 0, 1, 'C');
$pdf->Ln(1);
$direccion_completa = $this->data["HEADER"]["HEADER_TERMINAL"]["direccion_fiscal"] . " - " . $this->data["HEADER"]["HEADER_TERMINAL"]["ubigeo_depa"] . " - " . $this->data["HEADER"]["HEADER_TERMINAL"]["ubigeo_provi"] . " - " . $this->data["HEADER"]["HEADER_TERMINAL"]["ubigeo_distri"];
$pdf->MultiCell(75, 4, $direccion_completa, 0, 'C');
$pdf->Ln(1);
$pdf->Cell(78, 4, $this->data["HEADER"]["HEADER_TERMINAL"]["email"], 0, 1, 'C');
$pdf->Ln(4);

// Comprbante y correlativo
$pdf->SetLeftMargin(1);
$pdf->Cell(78, 0, '', 'B');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->Cell(78, 4, "TICKET DE POSPONER", 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->Cell(78, 4, $this->data["BODY"]["serie"] . "-" . substr("00000000", 0, -strlen($this->data["BODY"]["correlativo"])) . $this->data["BODY"]["correlativo"], 0, 1, 'C');
$pdf->SetLeftMargin(1);
$pdf->Ln(2);
$pdf->Cell(78, 0, '', 'T');
$pdf->Ln(4);

// Body
$pdf->SetLeftMargin(1);
$fecha_emision = date("d-m-Y", strtotime($this->data["BODY"]["fecha_emision"]));
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("F. Emisión: "), 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(0, 4, date("d-m-Y", strtotime($this->data["BODY"]["fecha_emision"])), 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("H. Emisión: "), 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(0, 4, date("H:i:m", strtotime($this->data["BODY"]["fecha_emision"])), 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("F. Vencimiento: "), 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(0, 4, date("d-m-Y"), 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("Cliente: "), 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$cliente = utf8_decode($this->data["BODY"]["cliente_nombres"]) . " " . utf8_decode($this->data["BODY"]["cliente_apellidos"]);
$pdf->MultiCell(0, 4, $cliente, 0, 'L');
$pdf->Ln(1);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, $this->data["BODY"]["cliente_tp_docu"] . ": ", 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(0, 4, $this->data["BODY"]["cliente_num_docu"], 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("Dirección: "), 0, 0, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->MultiCell(0, 4, utf8_decode($this->data["BODY"]["cliente_direccion"]), 0, 'L');
$pdf->Ln(1);

// Pasajero
if ($this->data["DETALLE"]["id_pasajero"]) {
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_PASAJERO);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, utf8_decode("Pasajero: "), 0, 0, 'L');
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_PASAJERO);
    $pasajero = utf8_decode($this->data["DETALLE"]["pasajero_nombres"]) . " " . utf8_decode($this->data["DETALLE"]["pasajero_apellidos"]);
    $pdf->MultiCell(0, 4, $pasajero, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_PASAJERO);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, $this->data["DETALLE"]["pasajero_tp_docu"] . ": ", 0, 0, 'L');
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_PASAJERO);
    $pdf->Cell(0, 4, $this->data["DETALLE"]["pasajero_num_docu"], 0, 1, 'L');
    $pdf->Ln(1);
}

$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_ORIGEN_DESTINO);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, "Origen", 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_ORIGEN_DESTINO);
$pdf->MultiCell(0, 4, utf8_decode($this->data["DETALLE"]["terminal_origen"]), 0, 'L');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_ORIGEN_DESTINO);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, "Destino", 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_ORIGEN_DESTINO);
$pdf->MultiCell(0, 4, utf8_decode($this->data["DETALLE"]["terminal_destino"]), 0, 'L');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, "Piso: ", 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_PISO);
$pdf->MultiCell(0, 4, $piso[$this->data["DETALLE"]["piso"]], 0, 'L');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_WIDTH_SIZE_BODY, 4, trim(utf8_decode("N° Asiento: ")), 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY_ASIENTO);
$pdf->MultiCell(0, 4, trim($this->data["DETALLE"]["num_asiento"]), 0, 'L');
$pdf->Ln(4);


// COLUMNAS
$pdf->SetLeftMargin(1);

// Columnas
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(42, 10, utf8_decode('DESCRIPCIÓN'), 0);
$pdf->Cell(5, 10, 'CANT', 0, 0, 'R');
$pdf->Cell(15, 10, 'P.U.', 0, 0, 'R');
$pdf->Cell(15, 10, 'TOTAL', 0, 0, 'R');
$pdf->Ln(8);
$pdf->Cell(78, 0, '', 'T');
$pdf->Ln(1);

// Detalle
$pdf->SetFont('Helvetica', '', 9);
$pdf->MultiCell(40, 4, utf8_decode($this->data["DETALLE"]["terminal_origen"]) . " - " . utf8_decode($this->data["DETALLE"]["terminal_destino"]), 0, 'L');
$pdf->Cell(47, -4, "1", 0, 0, 'R');
$pdf->Cell(15, -4, $this->data["DETALLE"]["precio"], 0, 0, 'R');
$pdf->Cell(15, -4, $this->data["DETALLE"]["precio"], 0, 0, 'R');
$pdf->Ln(1);

// IGV; TOTAL; ETC
$pdf->Cell(78, 0, '', 'T');
$pdf->Ln(2);

if (in_array($this->data["BODY"]["id_tp_comprobante"], array(1, 3))) {
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(66, 4, trim(utf8_decode("OP. EXONERADAS: S/")), 0, 0, 'R');
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(10, 4, $this->data["BODY"]["op_exoneradas"], 0, 1, 'R');
    $pdf->Ln(1);
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(66, 4, trim(utf8_decode("IGV: S/")), 0, 0, 'R');
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(11, 4, $this->data["BODY"]["igv"], 0, 1, 'R');
    $pdf->Ln(1);
}

$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(66, 4, trim(utf8_decode("TOTAL A PAGAR: S/")), 0, 0, 'R');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(11, 4, $this->data["BODY"]["op_gravada"], 0, 1, 'R');
$pdf->Ln(4);

// precio en texto
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, "SON: ", 0, 0, 'L');
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, numtoletras($this->data["BODY"]["op_gravada"]), 0, 1, 'L');
$pdf->Ln(4);

// Condicion de pago
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, utf8_decode("CONDICIÓN DE PAGO: " . $this->data["BODY"]["forma_pago"]), 0, 0, 'L');
$pdf->Ln(7);

// PAGOS
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, "PAGOS: ", 0, 1, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, utf8_decode("- " . $this->data["BODY"]["medio_pago"] . " - S/ " . $this->data["BODY"]["op_gravada"]), 0, 1, 'L');
$pdf->Ln(4);

// ESTADO
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, "ESTADO: ", 0, 1, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, utf8_decode($this->data["BODY"]["estado"]), 0, 1, 'L');
$pdf->Ln(4);

// VENDEDOR
$pdf->SetFont('Helvetica', 'B', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, "VENDEDOR: ", 0, 1, 'L');
$pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(TEXT_SIZE_BODY, 4, utf8_decode($this->data["BODY"]["vendedor_nombres"]), 0, 1, 'L');
$pdf->Ln(4);

// CODIGO QR
if (in_array($this->data["BODY"]["id_tp_comprobante"], array(1, 3))) {
    $pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
    $pdf->MultiCell(72, 4, utf8_decode("Representación impresa del Comprobante de Pago Electrónico."), 0, 'C');
    $pdf->SetFont('Helvetica', '', TEXT_SIZE_BODY);
}

print_r($pdf->Output('ticket.pdf', 'I'));
