<?php
date_default_timezone_set('America/Lima');
require_once('public/plugins/tc/tcpdf.php');

define("TEXT_SIZE_TITULO", 14);
define("TEXT_SIZE_BODY", 8);
define("TEXT_WIDTH_SIZE_BODY", 33);

class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $headerPrintedOnPage = [];

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row1($data, $isHeader = false)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->getNumLines($data[$i], $this->widths[$i]));
        }
        $h = 4 * $nb;

        if ($this->GetY() + $h > $this->getPageHeight() - 5 && !$isHeader) {
            $this->AddPage();
            $this->SetMargins(5, 8, 0);
        }

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 4, $data[$i], 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }
}

try {
    $pdf = new TCPDF_CellFit('P', 'mm', array(80, 300), true, 'UTF-8', false);
    $pdf->SetMargins(5, 0, 0);
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    // CABECERA
    $pdf->SetFont('helvetica', '', 6);
    $pdf->Cell(70, 0, '', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
    $pdf->Cell(70, 5, 'Reporte Punto de Venta', 0, 0, 'C');
    $pdf->Ln(7);
    $pdf->SetLeftMargin(12);
    $pdf->Cell(58, 0, '', 'T');
    $pdf->SetLeftMargin(5);
    $pdf->Ln(5);

    // BODY
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Empresa: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $empresa = isset($this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"]) ? $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"] : "N/A";
    $pdf->MultiCell(0, 3.5, $empresa, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Fecha reporte: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->MultiCell(0, 3.5, date("d-m-Y"), 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "RUC: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $ruc = isset($this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"]) ? $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"] : "N/A";
    $pdf->MultiCell(0, 3.5, $ruc, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Establecimiento: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $terminal = isset($this->data["HEADER"]["HEADER_DETALLE"]["terminal"]) ? $this->data["HEADER"]["HEADER_DETALLE"]["terminal"] : "N/A";
    $pdf->MultiCell(0, 3.5, $terminal, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Vendedor: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $vendedor = isset($this->data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"])
        ? $this->data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . ($this->data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"] ?? "")
        : "N/A";
    $pdf->MultiCell(0, 3.5, $vendedor, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Fecha y hora apertura: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $fecha_inicio = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_fecha_inicio"]) && $this->data["HEADER"]["HEADER_DETALLE"]["caja_fecha_inicio"]
        ? date("d-m-Y H:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["caja_fecha_inicio"]))
        : "N/A";
    $pdf->MultiCell(0, 3.5, $fecha_inicio, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Estado de caja: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $estado = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_estado"])
        ? ($this->data["HEADER"]["HEADER_DETALLE"]["caja_estado"] ? 'Aperturada' : 'Cerrado')
        : "N/A";
    $pdf->MultiCell(0, 3.5, $estado, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Fecha y hora cierre: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $fecha_fin = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_estado"]) && $this->data["HEADER"]["HEADER_DETALLE"]["caja_estado"] == 0 && $this->data["HEADER"]["HEADER_DETALLE"]["caja_fecha_fin"]
        ? date("d-m-Y H:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["caja_fecha_fin"]))
        : '----';
    $pdf->MultiCell(0, 3.5, $fecha_fin, 0, 'L');
    $pdf->Ln(1);

    // Calcular el total de ingresos desde DETALLE
    $totalIngresos = 0.00;
    foreach ($this->data["DETALLE"] ?? [] as $item) {
        $totalIngresos += floatval($item['total'] ?? 0.00);
    }

    // Calcular el total de egresos desde EGRESOS
    $egresos = $this->data['EGRESOS'] ?? [];
    $totalEgresos = array_sum(array_column($egresos, 'monto'));

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Montos de operación: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->MultiCell(0, 3.5, "S/. " . number_format($totalIngresos, 2), 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Saldo inicial: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $saldo_inicial = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_saldo_inicial"])
        ? "S/. " . number_format($this->data["HEADER"]["HEADER_DETALLE"]["caja_saldo_inicial"], 2)
        : "S/. 0.00";
    $pdf->MultiCell(0, 3.5, $saldo_inicial, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Total egresos: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $pdf->MultiCell(0, 3.5, "S/. " . number_format($totalEgresos, 2), 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Saldo Final: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $saldo_final = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_saldo_final"])
        ? "S/. " . number_format($this->data["HEADER"]["HEADER_DETALLE"]["caja_saldo_final"], 2)
        : "S/. 0.00";
    $pdf->MultiCell(0, 3.5, $saldo_final, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Total facturas: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $total_facturas = isset($this->data["HEADER"]["TOTALES_TPD"]["total_facturas"])
        ? "S/. " . number_format($this->data["HEADER"]["TOTALES_TPD"]["total_facturas"], 2)
        : "S/. 0.00";
    $pdf->MultiCell(0, 3.5, $total_facturas, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Total notas de ventas: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $total_notas = isset($this->data["HEADER"]["TOTALES_TPD"]["total_notas"])
        ? "S/. " . number_format($this->data["HEADER"]["TOTALES_TPD"]["total_notas"], 2)
        : "S/. 0.00";
    $pdf->MultiCell(0, 3.5, $total_notas, 0, 'L');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Total boletas: ", 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
    $total_boletas = isset($this->data["HEADER"]["TOTALES_TPD"]["total_boletas"])
        ? "S/. " . number_format($this->data["HEADER"]["TOTALES_TPD"]["total_boletas"], 2)
        : "S/. 0.00";
    $pdf->MultiCell(0, 3.5, $total_boletas, 0, 'L');
    $n_lineas = $this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1 ? 1 : 4;
    $pdf->Ln($n_lineas);

    if ($this->data['HEADER']['HEADER_EMPRESA']['porcent_venta'] == 1) {
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "T. porcentaje ventas: ", 0, 0, 'L');
        $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
        $total_porcentaje_ventas = isset($this->data["HEADER"]["TOTALES_TPD"]["total_porcentaje_ventas"])
            ? "S/. " . number_format($this->data["HEADER"]["TOTALES_TPD"]["total_porcentaje_ventas"], 2)
            : "S/. 0.00";
        $pdf->MultiCell(0, 3.5, $total_porcentaje_ventas, 0, 'L');
        $pdf->Ln(4);
    }

    if ($this->config_general['success'] && $this->config_general['message']['p_empresaxcaja'] == 1) {
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(TEXT_WIDTH_SIZE_BODY, 3.5, "Monto para empresa: ", 0, 0, 'L');
        $pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
        $total_porcentaje_ventas = isset($this->data["HEADER"]["HEADER_DETALLE"]["caja_monto_empresa"])
            ? "S/. " . number_format($this->data["HEADER"]["HEADER_DETALLE"]["caja_monto_empresa"], 2)
            : "S/. 0.00";
        $pdf->MultiCell(0, 3.5, $total_porcentaje_ventas, 0, 'L');
        $pdf->Ln(4);
    }

    // TABLA 1 (Medios de pago dinámicos)
    $pdf->SetLeftMargin(5);
    if (!empty($this->data["MEDIO_PAGOS"])) {
        $valid_payment_methods = [];
        $index = 0;
        foreach ($this->data["MEDIO_PAGOS"] as $payment) {
            if (isset($payment['total']) && floatval($payment['total']) > 0) {
                $valid_payment_methods[] = [
                    'index' => ++$index,
                    'method' => $payment['medio_pago'] ?? 'N/A',
                    'suma' => number_format($payment['total'], 2)
                ];
            }
        }

        if (!empty($valid_payment_methods)) {
            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(8, 6, '#', 1, 0, 'C');
            $pdf->Cell(50, 6, 'Descripción', 1, 0, 'C');
            $pdf->Cell(13, 6, 'Suma', 1, 0, 'C');
            $pdf->Ln(6);

            foreach ($valid_payment_methods as $payment) {
                $pdf->SetFont('helvetica', '', 7.5);
                $pdf->Cell(8, 6, strval($payment['index']), 1, 0, 'C');
                $pdf->Cell(50, 6, $payment['method'], 1, 0, 'C');
                $pdf->Cell(13, 6, $payment['suma'], 1, 0, 'C');
                $pdf->Ln(6);
            }
            $pdf->Ln(4);
        } else {
            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->Cell(0, 6, 'Sin medios de pago disponibles', 0, 1, 'C');
            $pdf->Ln(4);
        }
    } else {
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->Cell(0, 6, 'Sin medios de pago disponibles', 0, 1, 'C');
        $pdf->Ln(4);
    }

    // TABLA 2 (Detalles)
    $pdf->SetLeftMargin(5);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetWidths(array(6, 20, 22, 12, 11));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
    $pdf->Row1(array('#', 'Transacción', 'Documento', 'Moneda', 'Total'), true);

    if (isset($this->data["DETALLE"]) && is_array($this->data["DETALLE"]) && count($this->data["DETALLE"]) > 0) {
        foreach ($this->data["DETALLE"] as $i => $detalle) {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Row1(array(
                $i + 1,
                $detalle["tp_servicio"] ?? "N/A",
                ($detalle["venta_serie"] ?? "") . " - " . str_pad($detalle["venta_correlativo"] ?? 0, 8, "0", STR_PAD_LEFT),
                'PEN',
                number_format($detalle["total"] ?? 0.00, 2)
            ));
        }
        // Nueva fila para Total Ingresos (sin celdas, usando Cell)
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(255, 255, 255); // fondo blanco
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(55, 4, 'Montos de Operación', 0, 0, 'L', true); // Espacio vacío para alinear con las primeras cuatro columnas
        $pdf->Cell(16, 4, "S/. " . number_format($totalIngresos, 2), 0, 1, 'C', true); // Alineado con la columna Total
    } else {
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Row1(array('', 'Sin datos disponibles', '', '', ''));
    }
    $pdf->Ln(3);

    // TABLA 3: Caja de Egresos
    $egresos = $this->data['EGRESOS'] ?? [];
    if (!empty($egresos)) {
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->Cell(0, 5, 'CAJA DE EGRESOS', 0, 1, 'C'); // Título centrado
        $pdf->Ln(2);

        $table3Header = [
            "#",
            "Comprob.",
            "Serie",
            "Concepto",
            "Monto"
        ];
        $table3Widths = [5, 15, 11, 28, 12];
        $table3HeaderAligns = ['C', 'C', 'C', 'C', 'C'];
        $table3ContentAligns = ['C', 'L', 'C', 'L', 'C'];

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetWidths($table3Widths);
        $pdf->SetAligns($table3HeaderAligns);
        $pdf->Row1($table3Header, true);

        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetAligns($table3ContentAligns);
        $index = 1;

        foreach ($egresos as $egreso) {
            $tp_comprobante = $egreso['tp_comprobante'] ?? 'N/A';
            $serie = $egreso['serie'] ?? 'N/A';
            $concepto = $egreso['concepto'] ?? 'N/A';
            $monto = number_format($egreso['monto'] ?? 0.00, 2);

            $pdf->Row1([
                $index,
                $tp_comprobante,
                $serie,
                $concepto,
                $monto
            ], false);
            $index++;
        }

        // Nueva tabla para Total Egresos (sin celdas, usando Cell)
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $totalEgresos = array_sum(array_column($egresos, 'monto'));
        $pdf->SetFillColor(255, 255, 255); // fondo blanco
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(30, 4, 'Total Egresos', 0, 0, 'L', true); // Espacio vacío para alinear
        $pdf->Cell(29, 4, '', 0, 0, 'R', true); // Alineado con Concepto
        $pdf->Cell(12, 4, "S/. " . number_format($totalEgresos, 2), 0, 1, 'C', true); // Alineado con Monto
        $pdf->Ln(2);
    }

    $pdf->SetAutoPageBreak(true, 5);
    $pdf->Output('reporte.pdf', 'I');
} catch (Exception $e) {
    error_log("Error al generar el PDF: " . $e->getMessage());
    die("Error al generar el PDF: " . $e->getMessage());
}
