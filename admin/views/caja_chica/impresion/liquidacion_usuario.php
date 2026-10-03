<?php
ob_start();
ini_set('memory_limit', '1024M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

// Constantes de configuración
define("TEXT_SIZE_TITULO", 12); // Reducido de 13 para consistencia con el ejemplo
define("TEXT_SIZE_BODY", 7.5);
define("TEXT_WIDTH_SIZE_BODY", 36);

class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 0.5; // Reducido para más espacio

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 10);
    }

    function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        $script = "var pp = this->getPrintParams();";
        if ($dialog) {
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
        } else {
            $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
        }
        $script .= "pp.printerName = '\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "this->print(pp);";
        $this->IncludeJS($script);
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $drawBorders = false)
    {
        // Limpiar valores nulos y espacios
        foreach ($data as $i => $value) {
            $data[$i] = trim((string) ($value ?? '')); // Añadido trim para limpiar espacios
        }

        // Omitir filas completamente vacías
        $isEmptyRow = true;
        foreach ($data as $value) {
            if ($value !== '') {
                $isEmptyRow = false;
                break;
            }
        }
        if ($isEmptyRow) {
            error_log("Row: Fila vacía omitida - " . json_encode($data));
            return;
        }

        // Calcular el número máximo de líneas requeridas
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        // Verificar si hay espacio en la página
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        // Imprimir cada celda
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = is_array($align) && isset($align[$i]) ? $align[$i] : (is_string($align) ? $align : 'L');
            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if (in_array($i, $boldColumns)) {
                $this->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            } else {
                $this->SetFont('helvetica', '', TEXT_SIZE_BODY);
            }

            $this->MultiCell(
                $w,
                $h,
                $data[$i],
                $drawBorders ? 1 : 0,
                $a,
                false,
                0,
                $xBefore,
                $yBefore,
                true,
                0,
                false,
                true,
                $h,
                'M'
            );

            $this->SetXY($xBefore + $w, $yBefore);
        }

        $this->SetY($y + $h);
        error_log("Row: Altura calculada h=$h, nb=$nb, data=" . json_encode($data));
    }

    function NbLines($w, $txt)
    {
        if (!is_numeric($w)) {
            throw new Exception("El valor de \$w debe ser numérico");
        }

        $wmax = $w - 2 * $this->cMargin;
        $s = str_replace("\r", '', (string) $txt);
        $nb = mb_strlen($s, 'UTF-8');

        if ($nb === 0) {
            error_log("NbLines: w=$w, wmax=$wmax, txt='$s', result=0");
            return 0;
        }

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

        error_log("NbLines: w=$w, wmax=$wmax, txt='$s', result=$nl");
        return $nl;
    }
}

$pdf = new TCPDF_CellFit('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// BODY
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$pdf->SetWidths([30, 65, 30, 65]);
$pdf->Row(
    [
        "LIQUIDACION DE:",
        $this->data["HEADER"]["HEADER_DETALLE"]["terminal"] ?? 'N/A',
        "Usuario:",
        ($this->data["HEADER"]["HEADER_DETALLE"]["usuario_nombres"] ?? '') . " " . ($this->data["HEADER"]["HEADER_DETALLE"]["usuario_apellidos"] ?? '')
    ],
    5,
    ['L', 'L', 'L', 'L'],
    [0, 2],
    false
);

$pdf->SetWidths([30, 65, 30, 65]);
$pdf->Row(
    ["Fecha:", date("d-m-Y"), "Hora:", date("H:i A")],
    5,
    ['L', 'L', 'L', 'L'],
    [0, 2],
    false
);
$pdf->Ln(1);

// TABLA - PASAJE
$total_num_ventas = 0;
$total_ventasPasaje = 0.00;
if (isset($this->data["DETALLE"]["PASAJE"]) && is_array($this->data["DETALLE"]["PASAJE"]) && count($this->data["DETALLE"]["PASAJE"]) > 0) {
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(190, 0, "PASAJE", 0, 1, 'C');
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->SetWidths([12, 69, 20, 15, 24, 30, 20]);
    $pdf->Row(
        ["Asiento", "Cliente", "Nro. Doc.", "Estado", "S-N Boleta", "Destino", "Importe"],
        4,
        ['C', 'C', 'C', 'C', 'C', 'C', 'C'],
        [0, 1, 2, 3, 4, 5, 6],
        true
    );

    // Obtener métodos de pago dinámicamente
    $metodos_pago = [];
    foreach ($this->data["DETALLE"]["PASAJE"] as $detalle) {
        $medio_pago = isset($detalle["medio_pago"]) ? strtoupper(trim($detalle["medio_pago"])) : 'DESCONOCIDO';
        if (!in_array($medio_pago, $metodos_pago)) {
            $metodos_pago[] = $medio_pago;
        }
    }
    error_log("Métodos de pago: " . json_encode($metodos_pago));

    for ($i = 0; $i < count($this->data["DETALLE"]["PASAJE"]); $i++) {
        $medio_pago = isset($this->data["DETALLE"]["PASAJE"][$i]["medio_pago"]) ? strtoupper(trim($this->data["DETALLE"]["PASAJE"][$i]["medio_pago"])) : 'DESCONOCIDO';
        $pisos = isset($this->data["DETALLE"]["PASAJE"][$i]["pisos"]) ? $this->data["DETALLE"]["PASAJE"][$i]["pisos"] : [];

        if (empty($pisos)) {
            error_log("No hay pisos para medio_pago: $medio_pago");
            continue;
        }

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(190, 0, $medio_pago, 0, 1, 'C');
        $pdf->Ln(2);

        $total_ventas_medioPago = 0.00;
        $num_ventas = 0;

        for ($x = 0; $x < count($pisos); $x++) {
            $piso = isset($pisos[$x][0]["num_piso"]) ? trim($pisos[$x][0]["num_piso"]) : 'N/A';
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            $pdf->Cell(190, 0, str_repeat('_ ', 41) . " Piso: $piso " . str_repeat(' _', 40), 0, 1, 'C');
            $pdf->Ln(2);

            $ventas_piso = $pisos[$x];
            $num_ventas += count($ventas_piso);
            $total_num_ventas += count($ventas_piso);

            foreach ($ventas_piso as $venta) {
                $row_data = [
                    'num_asiento' => trim((string) ($venta["num_asiento"] ?? 'N/A')),
                    'cliente' => trim((string) ($venta["cliente_nombres"] ?? '') . " " . ($venta["cliente_apellidos"] ?? '')),
                    'cliente_num_docu' => trim((string) ($venta["cliente_num_docu"] ?? 'N/A')),
                    'estado_asiento' => trim((string) ($venta["estado_asiento"] ?? 'N/A')),
                    'venta_serie' => trim((string) ($venta["venta_serie"] ?? '')),
                    'venta_correlativo' => trim((string) ($venta["venta_correlativo"] ?? '')),
                    'terminal_destino' => trim((string) ($venta["terminal_destino"] ?? 'N/A')),
                    'importe' => number_format(floatval($venta["importe"] ?? 0.00), 2, '.', '')
                ];

                $boleta = $row_data['venta_serie'] && $row_data['venta_correlativo'] 
                    ? $row_data['venta_serie'] . '-' . str_pad($row_data['venta_correlativo'], 8, '0', STR_PAD_LEFT) 
                    : 'N/A';

                error_log("Fila [$i][$x]: " . json_encode($row_data) . ", boleta='$boleta'");

                $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
                $pdf->SetWidths([12, 69, 20, 15, 24, 30, 20]);
                $pdf->Row(
                    [
                        $row_data['num_asiento'],
                        $row_data['cliente'],
                        $row_data['cliente_num_docu'],
                        $row_data['estado_asiento'],
                        $boleta,
                        $row_data['terminal_destino'],
                        $row_data['importe']
                    ],
                    3.5, // Aumentado para textos largos
                    ['C', 'L', 'C', 'C', 'C', 'C', 'C'],
                    [],
                    false// Bordes para depuración
                );

                $total_ventas_medioPago += ($row_data['estado_asiento'] !== 'ANULADO') ? floatval($row_data['importe']) : 0.00;
            }
        }

        $total_ventasPasaje += $total_ventas_medioPago;

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
        $pdf->SetWidths([25, 115, 30, 20]);
        $pdf->Row(
            ["N VENTAS:", $num_ventas, "TOTAL: S/.", number_format($total_ventas_medioPago, 2, '.', '')],
            4,
            ['L', 'L', 'C', 'C'],
            [0, 2],
            false
        );
    }

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(0.8);

    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row(
        ["N VENTAS:", $total_num_ventas, "TOTAL: S/.", number_format($total_ventasPasaje, 2, '.', '')],
        4,
        ['L', 'L', 'R', 'C'],
        [0, 2],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([120, 50, 20]);
    $pdf->Row(
        ["", "COMISION (0.00):S/.", "0.00"],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([98, 72, 20]);
    $pdf->Row(
        ["", "DESC. EMBARQUE/DESEMBARQUE:S/.", "0.00"],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([132, 38, 20]);
    $pdf->Row(
        ["", "PAGO TOTAL:S/", number_format($total_ventasPasaje, 2, '.', '')],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );
}

// ENCOMIENDA

$total_num_ventas_encomienda = 0;
$total_ventasEncomienda = 0.00;
if (isset($this->data["DETALLE"]["ENCOMIENDA"]) && is_array($this->data["DETALLE"]["ENCOMIENDA"]) && count($this->data["DETALLE"]["ENCOMIENDA"]) > 0) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(190, 0, "ENCOMIENDA", 0, 1, 'C');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->SetWidths([55, 20, 20, 25, 19, 31, 20]);
    $pdf->Row(
        ["Cliente", "Nro. Doc.", "Estado", "S-N Comprobante", "Estado Pago", "Destino", "Importe"],
        4,
        ['C', 'C', 'C', 'C', 'C', 'C', 'C'],
        [0, 1, 2, 3, 4, 5, 6],
        true
    );

    $metodos_pago = [];
    foreach ($this->data["DETALLE"]["ENCOMIENDA"] as $detalle) {
        $medio_pago = isset($detalle["medio_pago"]) ? strtoupper(trim($detalle["medio_pago"])) : 'DESCONOCIDO';
        if (!in_array($medio_pago, $metodos_pago)) {
            $metodos_pago[] = $medio_pago;
        }
    }
    error_log("Métodos de pago (ENCOMIENDA): " . json_encode($metodos_pago));

    for ($i = 0; $i < count($this->data["DETALLE"]["ENCOMIENDA"]); $i++) {
        $medio_pago = isset($this->data["DETALLE"]["ENCOMIENDA"][$i]["medio_pago"]) ? strtoupper(trim($this->data["DETALLE"]["ENCOMIENDA"][$i]["medio_pago"])) : 'DESCONOCIDO';
        $encomiendas = isset($this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"]) ? $this->data["DETALLE"]["ENCOMIENDA"][$i]["encomiendas"] : [];

        if (empty($encomiendas)) {
            error_log("No hay encomiendas para medio_pago: $medio_pago (ENCOMIENDA)");
            continue;
        }

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(190, 0, $medio_pago, 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->Cell(190, 0, str_repeat('_ ', 40) . " Encomiendas " . str_repeat(' _', 40), 0, 1, 'C');
        $pdf->Ln(2);

        $total_ventas_medioPago = 0.00;
        $num_ventas = count($encomiendas);
        $total_num_ventas_encomienda += $num_ventas;

        foreach ($encomiendas as $x => $encomienda) {
            $row_data = [
                'cliente' => trim((string) ($encomienda["cliente_nombres"] ?? '') . " " . ($encomienda["cliente_apellidos"] ?? '')),
                'cliente_num_docu' => trim((string) ($encomienda["cliente_num_docu"] ?? 'N/A')),
                'estado_encomienda' => trim((string) ($encomienda["estado_encomienda"] ?? 'N/A')),
                'venta_serie' => trim((string) ($encomienda["venta_serie"] ?? '')),
                'venta_correlativo' => trim((string) ($encomienda["venta_correlativo"] ?? '')),
                'pago_encomienda' => trim((string) ($encomienda["pago_encomienda"] ?? 'N/A')),
                'terminal_destino' => trim((string) ($encomienda["terminal_destino"] ?? 'N/A')),
                'importe' => number_format(floatval($encomienda["importe"] ?? 0.00), 2, '.', '')
            ];

            $comprobante = $row_data['venta_serie'] && $row_data['venta_correlativo'] 
                ? $row_data['venta_serie'] . '-' . str_pad($row_data['venta_correlativo'], 8, '0', STR_PAD_LEFT) 
                : 'N/A';

            error_log("Fila [$i][$x] (ENCOMIENDA): " . json_encode($row_data) . ", comprobante='$comprobante'");

            $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
            $pdf->SetWidths([55, 20, 20, 25, 19, 31, 20]);
            $pdf->Row(
                [
                    $row_data['cliente'],
                    $row_data['cliente_num_docu'],
                    $row_data['estado_encomienda'],
                    $comprobante,
                    $row_data['pago_encomienda'],
                    $row_data['terminal_destino'],
                    $row_data['importe']
                ],
                3.5,
                ['L', 'C', 'C', 'C', 'C', 'C', 'C'],
                [],
                false
            );

            $total_ventas_medioPago += ($row_data['estado_encomienda'] !== 'ANULADO') ? floatval($row_data['importe']) : 0.00;
        }

        $total_ventasEncomienda += $total_ventas_medioPago;

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
        $pdf->SetWidths([25, 115, 30, 20]);
        $pdf->Row(
            ["N VENTAS:", $num_ventas, "TOTAL: S/.", number_format($total_ventas_medioPago, 2, '.', '')],
            4,
            ['L', 'L', 'C', 'C'],
            [0, 2],
            false
        );
    }

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(0.8);

    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row(
        ["N VENTAS:", $total_num_ventas_encomienda, "TOTAL: S/.", number_format($total_ventasEncomienda, 2, '.', '')],
        4,
        ['L', 'L', 'R', 'C'],
        [0, 2],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([140, 30, 20]);
    $pdf->Row(
        ["", "COMISION (0.00):S/.", "0.00"],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([98, 72, 20]);
    $pdf->Row(
        ["", "DESC. EMBARQUE/DESEMBARQUE:S/.", "0.00"],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );

    $pdf->Ln(0.8);
    $pdf->SetWidths([132, 38, 20]);
    $pdf->Row(
        ["", "PAGO TOTAL:S/", number_format($total_ventasEncomienda, 2, '.', '')],
        4,
        ['L', 'R', 'C'],
        [1],
        false
    );
}

// TOTAL PASAJE Y ENCOMIENDA
if ($total_num_ventas_pasaje > 0 || $total_num_ventas_encomienda > 0) {
    $total_general_ventas = $total_ventasPasaje + $total_ventasEncomienda;
    $total_general_num_ventas = $total_num_ventas_pasaje + $total_num_ventas_encomienda;

    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(0.8);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row(
        [" ", '', "TOTAL: S/.", number_format($total_general_ventas, 2, '.', '')],
        4,
        ['L', 'L', 'R', 'C'],
        [0, 2],
        false
    );
}

// Limpiar el buffer antes de enviar el PDF al navegador
ob_end_clean();

// Salida del PDF
$pdf->Output('reporte.pdf', 'I');