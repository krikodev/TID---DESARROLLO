<?php
date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/tc/tcpdf.php');

define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8);

class TCPDF_CellFiti extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;
    public $isTableHeader = false;
    public $forceHeaderWhite = false;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->SetMargins(7, 10, 7);
        $this->SetAutoPageBreak(false);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
    }

    public function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    public function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        $script = "var pp = this.getPrintParams();";
        $script .= $dialog ? "pp.interactive = pp.constants.interactionLevel.full;" :
            "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "this.print(pp);";
        $this->IncludeJS($script);
    }

    public function SetWidths($w)
    {
        $this->widths = $w;
    }

    public function SetAligns($a)
    {
        $this->aligns = $a;
    }

    public function Row($data, $lineHeight = 5, $isHeader = false, $forceWhiteHeader = false, $boldColumns = [], $drawBorders = true)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        if ($this->GetY() + $h > ($this->getPageHeight() - 10)) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(10);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : ($isHeader ? 'C' : 'L');

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if ($isHeader) {
                $this->SetFillColor($forceWhiteHeader ? 255 : 0, $forceWhiteHeader ? 255 : 136, $forceWhiteHeader ? 255 : 204);
                $this->SetTextColor($forceWhiteHeader ? 0 : 255, $forceWhiteHeader ? 0 : 255, $forceWhiteHeader ? 0 : 255);
            } else {
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(0, 0, 0);
                if (in_array($i, $boldColumns)) {
                    $this->SetFont('', 'B');
                }
            }

            $this->MultiCell(
                $w,
                $h,
                $data[$i],
                $drawBorders ? 1 : 0,
                $a,
                $isHeader,
                0,
                $xBefore,
                $yBefore,
                true,
                0,
                true,
                true,
                $h,
                'M'
            );

            $this->SetXY($xBefore + $w, $yBefore);
        }

        $this->SetY($y + $h);
    }

    public function NbLines($w, $txt)
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

        return $nl;
    }
}

try {
    $pdf = new TCPDF_CellFiti('P', 'mm', 'A4');
    $pdf->AddPage();

    // CABECERA
    $logoPath = Session::get("data_empresa")["logo"] ?? '';
    $bgX = 7;
    $bgY = 10;
    $maxWidth = 35;
    $maxHeight = 25;
    $titleY = $bgY;

    if (!empty($logoPath) && file_exists($logoPath)) {
        set_error_handler(function ($errno, $errstr) {
            if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
                return true;
            }
            return false;
        });

        list($imgWidth, $imgHeight) = getimagesize($logoPath);
        $scale = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        $xLogo = $bgX;
        $yLogo = $bgY;

        $pdf->Image(
            $logoPath,
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

        restore_error_handler();
        $titleY = $bgY + ($finalHeight - 5) / 2;
    } else {
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetXY(7, 10);
        $pdf->Cell(35, 10, 'Logo no disponible', 0, 1, 'L');
    }

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
    $titleText = 'Reporte Punto de Venta';
    $titleWidth = $pdf->GetStringWidth($titleText);
    $availableWidth = 196 - 35 - 3;
    $titleX = $bgX + 35 + 3 + ($availableWidth - $titleWidth) / 2;
    $pdf->SetXY($titleX, $titleY);
    $pdf->Cell($titleWidth, 5, $titleText, 0, 1, 'C');

    $pdf->SetLineStyle(['width' => 0.2, 'color' => [0, 0, 0]]);
    $lineWidth = $titleWidth;
    $lineX = $titleX;
    $lineY = $titleY + 5 + 2;
    $pdf->Line($lineX, $lineY, $lineX + $lineWidth, $lineY);
    $pdf->SetLineStyle(['width' => 0.2, 'color' => [0, 0, 0]]);
    $pdf->SetLeftMargin(7);
    $pdf->SetY($lineY + 3);

    $pdf->Ln(5);

    // BODY
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->SetWidths([34, 64, 33, 64]);

    $header = $this->data['HEADER'] ?? [];
    $headerEmpresa = $header['HEADER_EMPRESA'] ?? [];
    $headerDetalle = $header['HEADER_DETALLE'] ?? [];
    $totalesTpd = $header['TOTALES_TPD'] ?? [];
    $egresos = $this->data['EGRESOS'] ?? [];
    $creditos_pagados = $this->data['TOTAL_CUOTAS'] ?? 0.00;

    // Calcular el total de ingresos desde DETALLE
    $totalIngresos = 0.00;
    foreach ($this->data['DETALLE'] ?? [] as $item) {
        $totalIngresos += floatval($item['total'] ?? 0.00);
    }

    // Calcular el total de egresos desde EGRESOS
    $totalEgresos = array_sum(array_column($egresos, 'monto'));

    $pdf->Row([
        "Empresa: ",
        $headerEmpresa['razon_social'] ?? 'N/A',
        "Vendedor: ",
        ($headerDetalle['vendedor_nombres'] ?? '') . " " . ($headerDetalle['vendedor_apellidos'] ?? ''),
    ], 5, false, false, [], false);

    $pdf->Row([
        "RUC: ",
        $headerEmpresa['num_docu'] ?? 'N/A',
        "Establecimiento: ",
        $headerDetalle['terminal'] ?? 'N/A'
    ], 5, false, false, [], false);

    $pdf->Row([
        "Fecha y hora apertura: ",
        isset($headerDetalle['caja_fecha_inicio']) && $headerDetalle['caja_fecha_inicio']
        ? date("d-m-Y H:i A", strtotime($headerDetalle['caja_fecha_inicio']))
        : 'N/A',
        "Fecha y hora cierre: ",
        isset($headerDetalle['caja_estado']) && $headerDetalle['caja_estado'] == 0 && $headerDetalle['caja_fecha_fin']
        ? date("d-m-Y H:i A", strtotime($headerDetalle['caja_fecha_fin']))
        : '----',
    ], 5, false, false, [], false);

    $pdf->Row([
        "Estado de caja: ",
        isset($headerDetalle['caja_estado']) ? ($headerDetalle['caja_estado'] ? 'Aperturada' : 'Cerrado') : 'N/A',
        "Fecha de impresion: ",
        date("d-m-Y")
    ], 5, false, false, [], false);

    $pdf->Row([
        "Montos de operacion: ",
        "S/. " . number_format($totalIngresos, 2),
        "Total facturas: ",
        "S/. " . (isset($totalesTpd['total_facturas']) ? number_format($totalesTpd['total_facturas'], 2) : '0.00')
    ], 5, false, false, [], false);

    $pdf->Row([
        "Saldo inicial: ",
        "S/. " . (isset($headerDetalle['caja_saldo_inicial']) ? number_format($headerDetalle['caja_saldo_inicial'], 2) : '0.00'),
        "Total notas de ventas: ",
        "S/. " . (isset($totalesTpd['total_notas']) ? number_format($totalesTpd['total_notas'], 2) : '0.00')
    ], 5, false, false, [], false);

    $pdf->Row([
        "Total egresos: ",
        "S/. " . number_format($totalEgresos, 2),
        "Total boletas: ",
        "S/. " . (isset($totalesTpd['total_boletas']) ? number_format($totalesTpd['total_boletas'], 2) : '0.00')
    ], 5, false, false, [], false);

    $pdf->Row([
        "Saldo final: ",
        "S/. " . (isset($headerDetalle['caja_saldo_final']) ? number_format($headerDetalle['caja_saldo_final'], 2) : '0.00'),
        $this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1 ? "T. porcentaje pasajes: " : "",
        $this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1 ? "S/. " . (isset($totalesTpd['total_p_pasaje']) ? number_format($totalesTpd['total_p_pasaje'], 2) : '0.00') : "",
    ], 5, false, false, [], false);

    $pdf->Row([
        "Total pago facturas a credito: ",
        "S/. " . number_format($creditos_pagados, 2),
        $this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1 ? "T. porcentaje encomiendas: " : "",
        $this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1 ? "S/. " . (isset($totalesTpd['total_p_encomienda']) ? number_format($totalesTpd['total_p_encomienda'], 2) : '0.00') : "",
    ], 5, false, false, [], false);

    if ($this->config_general['success'] && $this->config_general['message']['p_empresaxcaja'] == 1) {
        $pdf->Row([
            "Monto para empresa: ",
            "S/. " . number_format($headerDetalle['caja_monto_empresa'], 2)
        ], 5, false, false, [], false);
    }

    $pdf->Ln(3);

    // TABLA 1: Medios de pago dinámicos
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->SetWidths([28, 120, 47]);
    $pdf->SetAligns(['C', 'C', 'C']);
    $pdf->Row(['#', 'Descripción', 'Suma'], 5, true, false, [], true);

    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->SetAligns(['C', 'C', 'C']);
    $mediosPagos = $this->data['MEDIO_PAGOS'] ?? [];
    $valid_payment_methods = [];
    $index = 0;

    if (!empty($mediosPagos)) {
        foreach ($mediosPagos as $payment) {
            if (isset($payment['total']) && floatval($payment['total']) > 0) {
                $valid_payment_methods[] = [
                    'index' => ++$index,
                    'method' => $payment['medio_pago'] ?? 'N/A',
                    'suma' => number_format($payment['total'], 2)
                ];
            }
        }

        if (!empty($valid_payment_methods)) {
            foreach ($valid_payment_methods as $payment) {
                $pdf->Row([
                    $payment['index'],
                    $payment['method'],
                    $payment['suma']
                ], 5, false, false, [], true);
            }
        } else {
            $pdf->Row(['', 'Sin medios de pago disponibles', ''], 5, false, false, [], true);
        }
    } else {
        $pdf->Row(['', 'Sin medios de pago disponibles', ''], 5, false, false, [], true);
    }

    // Agregar fila de CREDITO al final
    $creditoTotal = $this->data['CREDITOS']['total'] ?? '0.00';
    $pdf->Row([
        ++$index,
        'CREDITO',
        number_format(floatval($creditoTotal), 2)
    ], 5, false, false, [], true);

    $pdf->Ln(4);

    // TABLA 2: Detalles
    $table2Header = [
        "#",
        "Transacción",
        "Tipo de doc.",
        "Documento",
        "Fecha emisión",
        "Cliente/Proveedor",
        "N° Doc",
        "Moneda",
        "Total"
    ];
    $table2Widths = [7, 19, 24, 22, 25, 54, 18, 13, 13];
    $table2HeaderAligns = ['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'];
    $table2ContentAligns = ['C', 'C', 'C', 'C', 'C', 'L', 'C', 'C', 'C'];

    $tipoComprobanteMap = [
        1 => 'FACTURA ELECT.',
        2 => 'NOTA DE VENTA',
        3 => 'BOLETA ELECT.'
    ];

    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetWidths($table2Widths);
    $pdf->SetAligns($table2HeaderAligns);
    $pdf->Row($table2Header, 4, true, true, [], true);

    $pdf->SetFont('helvetica', '', 7);
    $totalIngresos = 0.00; // Variable para acumular el total de ingresos
    foreach ($this->data['DETALLE'] ?? [] as $i => $item) {
        $serie = $item['venta_serie'] ?? '';
        $correlativo = str_pad($item['venta_correlativo'] ?? 0, 8, "0", STR_PAD_LEFT);
        $documento = "{$serie} - {$correlativo}";
        $cliente = ($item['cliente_nombres'] ?? '') . " " . ($item['cliente_apellidos'] ?? '');

        $tipoComprobante = isset($item['id_tp_comprobante']) && isset($tipoComprobanteMap[$item['id_tp_comprobante']])
            ? $tipoComprobanteMap[$item['id_tp_comprobante']]
            : 'N/A';

        $totalIngresos += floatval($item['total'] ?? 0.00); // Acumular el total

        $pdf->SetWidths($table2Widths);
        $pdf->SetAligns($table2ContentAligns);
        $pdf->Row([
            $i + 1,
            $item['tp_servicio'] ?? 'N/A',
            $tipoComprobante,
            $documento,
            $item['fecha_emision'] ?? 'N/A',
            $cliente,
            $item['cliente_num_docu'] ?? 'N/A',
            "PEN",
            number_format($item['total'] ?? 0.00, 2)
        ], 4);
    }

    // Fila para el Total de Ingresos
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetWidths([170, 25]); // Ajustar anchos para alinear con las últimas columnas
    $pdf->SetAligns(['L', 'R']);
    $pdf->SetFillColor(240, 240, 240); // Fondo gris claro
    $pdf->Row([
        'Montos de Operación',
        'S/. ' . number_format($totalIngresos, 2)
    ], 4, false, false, [0, 1], false); // Sin bordes, fondo activado

    // Reducir el espacio vertical antes de la Tabla 3: Egresos
    $pdf->Ln(3);

    // TABLA 3: Caja de Egresos
    $egresos = $this->data['EGRESOS'] ?? [];
    if (!empty($egresos)) {
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(0, 5, 'CAJA DE EGRESOS', 0, 1, 'C'); // Título centrado
        $pdf->Ln(2);

        $table3Header = [
            "#",
            "Tipo Comprobante",
            "Serie",
            "Concepto",
            "Monto"
        ];
        $table3Widths = [10, 40, 30, 80, 35];
        $table3HeaderAligns = ['C', 'C', 'C', 'C', 'C'];
        $table3ContentAligns = ['C', 'L', 'C', 'L', 'C'];

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetWidths($table3Widths);
        $pdf->SetAligns($table3HeaderAligns);
        $pdf->Row($table3Header, 4, true, true, [], true);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetAligns($table3ContentAligns);
        $index = 1;

        foreach ($egresos as $egreso) {
            $tp_comprobante = $egreso['tp_comprobante'] ?? 'N/A';
            $serie = $egreso['serie'] ?? 'N/A';
            $concepto = $egreso['concepto'] ?? 'N/A';
            $monto = number_format($egreso['monto'] ?? 0.00, 2);

            $pdf->Row([
                $index,
                $tp_comprobante,
                $serie,
                $concepto,
                $monto
            ], 4);
            $index++;
        }

        // Nueva tabla para Total Egresos (sin celdas)
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetWidths([80, 80, 35]); // 60 mm para alinear con las primeras tres columnas, 80+36 para Concepto y Monto
        $pdf->SetAligns(['L', 'R', 'C']);
        $totalEgresos = array_sum(array_column($egresos, 'monto'));
        $pdf->SetFillColor(240, 240, 240); // Fondo gris claro
        $pdf->Row([
            'Total Egresos',
            '',
            'S/. ' . number_format($totalEgresos, 2)
        ], 4, false, false, [1, 2], false, true); // Sin bordes, fondo activado
    }

    $pdf->SetAutoPageBreak(false);
    $pdf->Output('reporte.pdf', 'I');
} catch (Exception $e) {
    error_log("Error al generar el PDF: " . $e->getMessage());
    die("Error al generar el PDF: " . $e->getMessage());
}
