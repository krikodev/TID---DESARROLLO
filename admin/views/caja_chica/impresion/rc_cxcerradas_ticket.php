<?php
date_default_timezone_set('America/Lima');
require_once('public/plugins/tc/tcpdf.php');

$pdf = new TCPDF('P', 'mm', [80, 250], true, 'UTF-8', false);
$pdf->SetMargins(5, 3, 5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AddPage();

$data = $this->data;

$empresa = $data['empresa'] ?? [];
$fecha = $data['fecha'] ?? date('Y-m-d');
$modo = $data['modo'] ?? 'resumen_terminal';
$totales = $data['totales'] ?? [];
$terminales = $data['terminales'] ?? [];
$usuarios = $data['usuarios'] ?? [];
$cajas = $data['cajas'] ?? [];

$nombreEmpresa = is_array($empresa)
    ? ($empresa['razon_social'] ?? 'EMPRESA DE TRANSPORTES')
    : 'EMPRESA DE TRANSPORTES';

$ruc = is_array($empresa)
    ? ($empresa['num_docu'] ?? 'N/A')
    : 'N/A';

function moneyTicket($monto)
{
    return 'S/ ' . number_format(floatval($monto), 2);
}

function fechaTicket($fecha)
{
    return $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '-';
}

function lineTicket($pdf)
{
    $pdf->Cell(70, 3, str_repeat('-', 48), 0, 1, 'C');
}

/* CABECERA */
$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell(70, 4, strtoupper($nombreEmpresa), 0, 'C');

$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(70, 4, 'RUC: ' . $ruc, 0, 1, 'C');

lineTicket($pdf);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell(70, 4, 'REPORTE CAJAS CERRADAS', 0, 'C');

$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(70, 4, 'Fecha: ' . date('d/m/Y', strtotime($fecha)), 0, 1, 'C');
$pdf->Cell(70, 4, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');

lineTicket($pdf);

/* RESUMEN GENERAL */
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(70, 4, 'RESUMEN GENERAL', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 7.5);

$pdf->Cell(40, 4, 'Total cajas:', 0, 0, 'L');
$pdf->Cell(30, 4, $data['total_cajas'] ?? 0, 0, 1, 'R');

$pdf->Cell(40, 4, 'Total ventas:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['total_ventas'] ?? 0), 0, 1, 'R');

$pdf->Cell(40, 4, 'Monto empresa:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['monto_empresa'] ?? 0), 0, 1, 'R');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(40, 4, 'Dinero entregar:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['dinero_entregar'] ?? 0), 0, 1, 'R');
$pdf->SetFont('helvetica', '', 7.5);

$pdf->Cell(40, 4, 'Saldo inicial:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['saldo_inicial'] ?? 0), 0, 1, 'R');

$pdf->Cell(40, 4, 'Saldo final:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['saldo_final'] ?? 0), 0, 1, 'R');

$pdf->Cell(40, 4, 'Saldo real:', 0, 0, 'L');
$pdf->Cell(30, 4, moneyTicket($totales['saldo_real'] ?? 0), 0, 1, 'R');

lineTicket($pdf);

/* RESUMEN POR TERMINAL */
if ($modo === 'resumen_terminal') {

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(70, 4, 'RESUMEN POR TERMINAL', 0, 1, 'C');

    foreach ($terminales as $t) {
        $pdf->Ln(1);

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->MultiCell(70, 4, strtoupper($t['terminal'] ?? 'SIN TERMINAL'), 0, 'L');

        $pdf->SetFont('helvetica', '', 7);

        $pdf->Cell(40, 4, 'Cajas:', 0, 0, 'L');
        $pdf->Cell(30, 4, $t['total_cajas'] ?? 0, 0, 1, 'R');

        $pdf->Cell(40, 4, 'Ventas:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($t['total_ventas'] ?? 0), 0, 1, 'R');

        $pdf->Cell(40, 4, 'Empresa:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($t['monto_empresa'] ?? 0), 0, 1, 'R');

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->Cell(40, 4, 'Entregar:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($t['dinero_entregar'] ?? 0), 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 7);

        $pdf->Cell(40, 4, 'Saldo final:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($t['saldo_final'] ?? 0), 0, 1, 'R');

        $pdf->Cell(40, 4, 'Saldo real:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($t['saldo_real'] ?? 0), 0, 1, 'R');

        lineTicket($pdf);
    }

    if (!empty($usuarios)) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(70, 4, 'RESUMEN POR VENDEDOR', 0, 1, 'C');

        foreach ($usuarios as $u) {
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->MultiCell(70, 4, strtoupper($u['usuario'] ?? 'SIN USUARIO'), 0, 'L');

            $pdf->SetFont('helvetica', '', 7);

            $pdf->Cell(40, 4, 'Terminal:', 0, 0, 'L');
            $pdf->Cell(30, 4, $u['terminal'] ?? '-', 0, 1, 'R');

            $pdf->Cell(40, 4, 'Cajas:', 0, 0, 'L');
            $pdf->Cell(30, 4, $u['total_cajas'] ?? 0, 0, 1, 'R');

            $pdf->Cell(40, 4, 'Ventas:', 0, 0, 'L');
            $pdf->Cell(30, 4, moneyTicket($u['total_ventas'] ?? 0), 0, 1, 'R');

            $pdf->Cell(40, 4, 'Empresa:', 0, 0, 'L');
            $pdf->Cell(30, 4, moneyTicket($u['monto_empresa'] ?? 0), 0, 1, 'R');

            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(40, 4, 'Entregar:', 0, 0, 'L');
            $pdf->Cell(30, 4, moneyTicket($u['dinero_entregar'] ?? 0), 0, 1, 'R');
            $pdf->SetFont('helvetica', '', 7);

            lineTicket($pdf);
        }
    }
}

/* DETALLE POR VENDEDOR */
if ($modo === 'detalle_usuario' || $modo === 'detalle_vendedor') {

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(70, 4, 'DETALLE DE CAJAS', 0, 1, 'C');

    foreach ($cajas as $c) {
        $pdf->Ln(1);

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->Cell(70, 4, 'CAJA #' . ($c['id_caja_chica'] ?? '-'), 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7);

        $pdf->Cell(22, 4, 'Terminal:', 0, 0, 'L');
        $pdf->MultiCell(48, 4, $c['terminal'] ?? '-', 0, 'R');

        $pdf->Cell(22, 4, 'Vendedor:', 0, 0, 'L');
        $pdf->MultiCell(48, 4, $c['usuario'] ?? '-', 0, 'R');

        $pdf->Cell(22, 4, 'Apertura:', 0, 0, 'L');
        $pdf->Cell(48, 4, fechaTicket($c['fecha_inicio'] ?? null), 0, 1, 'R');

        $pdf->Cell(22, 4, 'Cierre:', 0, 0, 'L');
        $pdf->Cell(48, 4, fechaTicket($c['fecha_fin'] ?? null), 0, 1, 'R');

        $pdf->Cell(40, 4, 'Saldo inicial:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['saldo_inicial'] ?? 0), 0, 1, 'R');

        $pdf->Cell(40, 4, 'Total ventas:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['total_ventas'] ?? 0), 0, 1, 'R');

        $pdf->Cell(40, 4, '% Empresa:', 0, 0, 'L');
        $pdf->Cell(30, 4, number_format(floatval($c['porcentaje_empresa'] ?? 0), 2) . '%', 0, 1, 'R');

        $pdf->Cell(40, 4, 'Monto empresa:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['monto_empresa'] ?? 0), 0, 1, 'R');

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->Cell(40, 4, 'Dinero entregar:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['dinero_entregar'] ?? 0), 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 7);

        $pdf->Cell(40, 4, 'Saldo final:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['saldo_final'] ?? 0), 0, 1, 'R');

        $pdf->Cell(40, 4, 'Saldo real:', 0, 0, 'L');
        $pdf->Cell(30, 4, moneyTicket($c['saldo_real'] ?? 0), 0, 1, 'R');

        lineTicket($pdf);
    }
}

/* PIE DESTACADO */
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(70, 5, 'TOTAL EMPRESA: ' . moneyTicket($totales['monto_empresa'] ?? 0), 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(70, 6, 'DINERO A ENTREGAR:', 0, 1, 'C');
$pdf->Cell(70, 6, moneyTicket($totales['dinero_entregar'] ?? 0), 0, 1, 'C');

$pdf->SetFont('helvetica', '', 6.5);
$pdf->Cell(70, 4, 'Documento interno - Reporte POS', 0, 1, 'C');

ob_clean();
$pdf->Output('reporte_cajas_cerradas_ticket.pdf', 'I');
exit;