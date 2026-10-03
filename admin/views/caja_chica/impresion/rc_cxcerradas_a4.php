<?php
date_default_timezone_set('America/Lima');
require_once('public/plugins/tc/tcpdf.php');

/**
 * REPORTE CONSOLIDADO DE CAJAS CERRADAS - A4
 * Diseño profesional sobrio
 */

define('C_PRIMARY_R', 63);
define('C_PRIMARY_G', 204);
define('C_PRIMARY_B', 186);

define('C_DARK_R', 35);
define('C_DARK_G', 55);
define('C_DARK_B', 70);

define('C_TEXT_R', 35);
define('C_TEXT_G', 45);
define('C_TEXT_B', 55);

define('C_MUTED_R', 110);
define('C_MUTED_G', 120);
define('C_MUTED_B', 130);

define('C_LIGHT_R', 246);
define('C_LIGHT_G', 249);
define('C_LIGHT_B', 250);

define('C_BORDER_R', 220);
define('C_BORDER_G', 228);
define('C_BORDER_B', 232);

class PDF_CajasCerradas extends TCPDF
{
    public $nombreEmpresa = '';
    public $ruc = '';
    public $usuarioSesion = '';
    public $logoPath = '';

    public function Header()
    {
        $pageW = $this->getPageWidth();

        $this->SetFillColor(C_DARK_R, C_DARK_G, C_DARK_B);
        $this->Rect(0, 0, $pageW, 32, 'F');

        $this->SetFillColor(C_PRIMARY_R, C_PRIMARY_G, C_PRIMARY_B);
        $this->Rect(0, 31, $pageW, 1.5, 'F');

        if (!empty($this->logoPath) && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 12, 6, 22, 0, '', '', '', false, 300);
        } else {
            $this->SetFillColor(255, 255, 255);
            $this->RoundedRect(12, 6, 22, 20, 2, '1111', 'F');
            $this->SetTextColor(C_DARK_R, C_DARK_G, C_DARK_B);
            $this->SetFont('helvetica', 'B', 7);
            $this->SetXY(12, 13);
            $this->Cell(22, 5, 'LOGO', 0, 0, 'C');
        }

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(38, 7);
        $this->Cell(95, 5, $this->nombreEmpresa, 0, 1, 'L');

        $this->SetFont('helvetica', '', 7.5);
        $this->SetX(38);
        $this->Cell(95, 4, 'RUC: ' . $this->ruc, 0, 1, 'L');

        $this->SetFont('helvetica', '', 7);
        $this->SetX(38);
        $this->Cell(95, 4, 'Sistema de Gestion de Transporte Terrestre', 0, 1, 'L');

        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY($pageW - 88, 7);
        $this->Cell(76, 5, 'REPORTE CONSOLIDADO', 0, 1, 'R');

        $this->SetFont('helvetica', 'B', 9);
        $this->SetX($pageW - 88);
        $this->Cell(76, 5, 'DE CAJAS CERRADAS', 0, 1, 'R');

        $this->SetFont('helvetica', '', 7);
        $this->SetX($pageW - 88);
        $this->Cell(76, 4, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');

        $this->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    }

    public function Footer()
    {
        $pageW = $this->getPageWidth();

        $this->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->getPageHeight() - 15, $pageW - 12, $this->getPageHeight() - 15);

        $this->SetY(-13);
        $this->SetFont('helvetica', '', 7);
        $this->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);

        $this->Cell(70, 5, 'Generado por: ' . $this->usuarioSesion, 0, 0, 'L');
        $this->Cell(0, 5, 'Uso interno', 0, 0, 'C');
        $this->Cell(55, 5, 'Pag. ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');

        $this->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    }
}

try {
    $modo = $this->data['modo'] ?? 'resumen_terminal';
    $filtros = $this->data['filtros'] ?? [];
    $totales = $this->data['totales'] ?? [];
    $terminales = $this->data['terminales'] ?? [];
    $usuarios = $this->data['usuarios'] ?? [];
    $cajas = $this->data['cajas'] ?? [];
    $empresa = $this->data['empresa'] ?? [];
    $fecha = $this->data['fecha'] ?? date('Y-m-d');
    $totalCajas = $this->data['total_cajas'] ?? 0;

    $nombreEmpresa = strtoupper($empresa['razon_social'] ?? 'EMPRESA DE TRANSPORTES S.A.C.');
    $ruc = $empresa['num_docu'] ?? '20XXXXXXXXXX';
    $usuarioSesion = strtoupper(Session::get('nombre') ?? 'SISTEMA');
    $logoPath = Session::get('data_empresa')['logo'] ?? '';

    $fmtMoney = fn($v) => 'S/ ' . number_format((float) $v, 2, '.', ',');

    $fmtFecha = function ($f) {
        if (empty($f))
            return '-';

        try {
            return (new DateTime($f))->format('d/m/Y H:i');
        } catch (Exception $e) {
            return $f;
        }
    };

    $getNombreTerminal = function () use ($filtros, $terminales) {
        $id = $filtros['id_terminal'] ?? 0;

        if (!$id)
            return 'TODAS';

        foreach ($terminales as $t) {
            if (($t['id_terminal'] ?? null) == $id) {
                return strtoupper($t['terminal']);
            }
        }

        return 'ID: ' . $id;
    };

    $getNombreVendedor = function () use ($filtros, $usuarios) {
        $id = $filtros['id_usuario'] ?? 0;

        if (!$id)
            return 'TODOS';

        foreach ($usuarios as $u) {
            if (($u['id_usuario'] ?? null) == $id) {
                return strtoupper($u['usuario']);
            }
        }

        return 'ID: ' . $id;
    };

    $pdf = new PDF_CajasCerradas('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->nombreEmpresa = $nombreEmpresa;
    $pdf->ruc = $ruc;
    $pdf->usuarioSesion = $usuarioSesion;
    $pdf->logoPath = $logoPath;

    $pdf->SetCreator('Sistema de Transporte');
    $pdf->SetAuthor($usuarioSesion);
    $pdf->SetTitle('Reporte Consolidado de Cajas Cerradas');

    $pdf->SetMargins(12, 38, 12);
    $pdf->SetHeaderMargin(4);
    $pdf->SetFooterMargin(14);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->AddPage();

    $pageW = $pdf->getPageWidth();
    $margins = $pdf->getMargins();
    $usableW = $pageW - $margins['left'] - $margins['right'];

    $tituloSeccion = function ($texto) use ($pdf, $margins, $usableW) {
        $pdf->Ln(3);
        $y = $pdf->GetY();

        $pdf->SetFillColor(C_LIGHT_R, C_LIGHT_G, C_LIGHT_B);
        $pdf->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
        $pdf->RoundedRect($margins['left'], $y, $usableW, 8, 1.5, '1111', 'DF');

        $pdf->SetFillColor(C_PRIMARY_R, C_PRIMARY_G, C_PRIMARY_B);
        $pdf->Rect($margins['left'], $y, 2.5, 8, 'F');

        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(C_DARK_R, C_DARK_G, C_DARK_B);
        $pdf->SetXY($margins['left'] + 5, $y + 1.3);
        $pdf->Cell($usableW - 6, 5, strtoupper($texto), 0, 1, 'L');

        $pdf->SetY($y + 10);
        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    };

    $cardMetrica = function ($x, $y, $w, $h, $label, $valor, $destacado = false) use ($pdf) {
        if ($destacado) {
            $pdf->SetFillColor(232, 255, 251);
            $pdf->SetDrawColor(C_PRIMARY_R, C_PRIMARY_G, C_PRIMARY_B);
        } else {
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
        }

        $pdf->RoundedRect($x, $y, $w, $h, 2, '1111', 'DF');

        if ($destacado) {
            $pdf->SetFillColor(C_PRIMARY_R, C_PRIMARY_G, C_PRIMARY_B);
            $pdf->Rect($x, $y, 2.5, $h, 'F');
        }

        $pdf->SetFont('helvetica', '', 6.8);
        $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
        $pdf->SetXY($x + 4, $y + 3);
        $pdf->Cell($w - 6, 4, strtoupper($label), 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', $destacado ? 11 : 10);
        $pdf->SetTextColor(C_DARK_R, C_DARK_G, C_DARK_B);
        $pdf->SetX($x + 4);
        $pdf->Cell($w - 6, 7, $valor, 0, 1, 'L');

        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    };

    $cabeceraTabla = function ($cols) use ($pdf, $margins) {
        $y = $pdf->GetY();
        $totalW = array_sum(array_column($cols, 'width'));

        $pdf->SetFillColor(C_DARK_R, C_DARK_G, C_DARK_B);
        $pdf->Rect($margins['left'], $y, $totalW, 7, 'F');

        $pdf->SetFont('helvetica', 'B', 6.8);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($margins['left'], $y);

        foreach ($cols as $col) {
            $pdf->Cell($col['width'], 7, strtoupper($col['label']), 0, 0, $col['align']);
        }

        $pdf->Ln(7);
        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    };

    $filaTabla = function ($cols, $fila, $index) use ($pdf, $margins, $usableW, $cabeceraTabla) {
        $rowH = 6.3;

        if ($pdf->GetY() + $rowH > ($pdf->getPageHeight() - $pdf->getBreakMargin())) {
            $pdf->AddPage();
            $cabeceraTabla($cols);
        }

        $y = $pdf->GetY();

        if ($index % 2 === 0) {
            $pdf->SetFillColor(255, 255, 255);
        } else {
            $pdf->SetFillColor(248, 250, 252);
        }

        $pdf->Rect($margins['left'], $y, $usableW, $rowH, 'F');

        $pdf->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
        $pdf->SetLineWidth(0.1);
        $pdf->Line($margins['left'], $y + $rowH, $margins['left'] + $usableW, $y + $rowH);

        $pdf->SetFont('helvetica', '', 6.7);
        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
        $pdf->SetXY($margins['left'], $y);

        foreach ($cols as $k => $col) {
            $pdf->Cell($col['width'], $rowH, $fila[$k] ?? '', 0, 0, $col['align']);
        }

        $pdf->Ln($rowH);
    };

    $filaTotalTabla = function ($cols, $fila) use ($pdf, $margins, $usableW) {
        $y = $pdf->GetY() + 1;

        $pdf->SetFillColor(238, 247, 246);
        $pdf->Rect($margins['left'], $y, $usableW, 8, 'F');

        $pdf->SetDrawColor(C_PRIMARY_R, C_PRIMARY_G, C_PRIMARY_B);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($margins['left'], $y, $margins['left'] + $usableW, $y);

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetTextColor(C_DARK_R, C_DARK_G, C_DARK_B);
        $pdf->SetXY($margins['left'], $y);

        foreach ($cols as $k => $col) {
            $pdf->Cell($col['width'], 8, $fila[$k] ?? '', 0, 0, $col['align']);
        }

        $pdf->Ln(10);
        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    };

    $sinDatos = function () use ($pdf, $margins, $usableW) {
        $y = $pdf->GetY();

        $pdf->SetFillColor(C_LIGHT_R, C_LIGHT_G, C_LIGHT_B);
        $pdf->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
        $pdf->RoundedRect($margins['left'], $y, $usableW, 12, 2, '1111', 'DF');

        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
        $pdf->SetXY($margins['left'], $y + 3);
        $pdf->Cell($usableW, 6, 'No se encontraron datos para los filtros seleccionados.', 0, 1, 'C');

        $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    };

    /* FILTROS */
    $tituloSeccion('Filtros aplicados');

    $startY = $pdf->GetY();

    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(C_BORDER_R, C_BORDER_G, C_BORDER_B);
    $pdf->RoundedRect($margins['left'], $startY, $usableW, 26, 2, '1111', 'DF');

    $modoLabel = ($modo === 'detalle_usuario' || $modo === 'detalle_vendedor')
        ? 'Detalle por usuario'
        : 'Resumen por terminal';

    $fechaFiltro = date('d/m/Y', strtotime($fecha));
    $terminalFiltro = $getNombreTerminal();
    $vendedorFiltro = $getNombreVendedor();

    $rowH = 5;
    $labelW = 18;

    $x1 = $margins['left'] + 8;
    $x2 = $margins['left'] + 105;

    $valueX1 = $x1 + $labelW + 3;
    $valueX2 = $x2 + $labelW + 3;

    $valueW1 = 70;
    $valueW2 = 55;

    $y1 = $startY + 7;
    $y2 = $startY + 17;

    /* Fecha */
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
    $pdf->SetXY($x1, $y1);
    $pdf->Cell($labelW, $rowH, 'Fecha:', 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    $pdf->SetXY($valueX1, $y1);
    $pdf->Cell($valueW1, $rowH, $fechaFiltro, 0, 0, 'L');

    /* Terminal */
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
    $pdf->SetXY($x2, $y1);
    $pdf->Cell($labelW, $rowH, 'Terminal:', 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    $pdf->SetXY($valueX2, $y1);
    $pdf->Cell($valueW2, $rowH, $terminalFiltro ?: 'TODAS', 0, 0, 'L');

    /* Vendedor */
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
    $pdf->SetXY($x1, $y2);
    $pdf->Cell($labelW, $rowH, 'Vendedor:', 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    $pdf->SetXY($valueX1, $y2);
    $pdf->Cell($valueW1, $rowH, $vendedorFiltro ?: 'TODOS', 0, 0, 'L');

    /* Modo */
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(C_MUTED_R, C_MUTED_G, C_MUTED_B);
    $pdf->SetXY($x2, $y2);
    $pdf->Cell($labelW, $rowH, 'Modo:', 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    $pdf->SetXY($valueX2, $y2);
    $pdf->Cell($valueW2, $rowH, $modoLabel, 0, 0, 'L');

    $pdf->SetTextColor(C_TEXT_R, C_TEXT_G, C_TEXT_B);
    $pdf->SetY($startY + 31);

    /* RESUMEN GENERAL */
    $tituloSeccion('Resumen general');

    $metricas = [
        ['Total cajas', number_format((int) $totalCajas), false],
        ['Total ventas', $fmtMoney($totales['total_ventas'] ?? 0), false],
        ['Monto empresa', $fmtMoney($totales['monto_empresa'] ?? 0), false],
        ['Dinero a entregar', $fmtMoney($totales['dinero_entregar'] ?? 0), true],
        ['Saldo final', $fmtMoney($totales['saldo_final'] ?? 0), false],
        ['Saldo real', $fmtMoney($totales['saldo_real'] ?? 0), false],
    ];

    $numCols = 3;
    $gap = 3;
    $cardW = ($usableW - (($numCols - 1) * $gap)) / $numCols;
    $cardH = 18;
    $startX = $margins['left'];
    $startY = $pdf->GetY();

    foreach ($metricas as $i => $m) {
        $col = $i % $numCols;
        $row = (int) floor($i / $numCols);

        $x = $startX + ($col * ($cardW + $gap));
        $y = $startY + ($row * ($cardH + $gap));

        $cardMetrica($x, $y, $cardW, $cardH, $m[0], $m[1], $m[2]);
    }

    $rows = (int) ceil(count($metricas) / $numCols);
    $pdf->SetY($startY + ($rows * ($cardH + $gap)) + 3);

    /* DETALLE O RESUMEN */
    if ($modo === 'detalle_usuario' || $modo === 'detalle_vendedor') {

        $tituloSeccion('Detalle de cajas por vendedor');

        if (empty($cajas)) {
            $sinDatos();
        } else {
            $cols = [
                ['label' => 'Caja', 'width' => 12, 'align' => 'C'],
                ['label' => 'Cierre', 'width' => 24, 'align' => 'C'],
                ['label' => 'Terminal', 'width' => 30, 'align' => 'L'],
                ['label' => 'Vendedor', 'width' => 34, 'align' => 'L'],
                ['label' => 'Ventas', 'width' => 22, 'align' => 'R'],
                ['label' => 'Empresa', 'width' => 22, 'align' => 'R'],
                ['label' => 'Entregar', 'width' => 24, 'align' => 'R'],
                ['label' => 'Saldo Real', 'width' => 18, 'align' => 'R'],
            ];

            $cabeceraTabla($cols);

            foreach ($cajas as $i => $c) {
                $filaTabla($cols, [
                    '#' . ($c['id_caja_chica'] ?? '-'),
                    $fmtFecha($c['fecha_fin'] ?? ''),
                    strtoupper($c['terminal'] ?? '-'),
                    strtoupper($c['usuario'] ?? '-'),
                    $fmtMoney($c['total_ventas'] ?? 0),
                    $fmtMoney($c['monto_empresa'] ?? 0),
                    $fmtMoney($c['dinero_entregar'] ?? 0),
                    $fmtMoney($c['saldo_real'] ?? 0),
                ], $i);
            }

            $filaTotalTabla($cols, [
                'TOTAL',
                '',
                '',
                number_format((int) count($cajas)) . ' caja(s)',
                $fmtMoney($totales['total_ventas'] ?? 0),
                $fmtMoney($totales['monto_empresa'] ?? 0),
                $fmtMoney($totales['dinero_entregar'] ?? 0),
                $fmtMoney($totales['saldo_real'] ?? 0),
            ]);
        }

    } else {

        $tituloSeccion('Resumen por terminal');

        if (empty($terminales)) {
            $sinDatos();
        } else {
            $cols = [
                ['label' => 'Terminal', 'width' => 44, 'align' => 'L'],
                ['label' => 'Cajas', 'width' => 16, 'align' => 'C'],
                ['label' => 'Ventas', 'width' => 30, 'align' => 'R'],
                ['label' => 'Empresa', 'width' => 28, 'align' => 'R'],
                ['label' => 'Entregar', 'width' => 30, 'align' => 'R'],
                ['label' => 'Saldo Real', 'width' => 38, 'align' => 'R'],
            ];

            $cabeceraTabla($cols);

            foreach ($terminales as $i => $t) {
                $filaTabla($cols, [
                    strtoupper($t['terminal'] ?? '-'),
                    number_format((int) ($t['total_cajas'] ?? 0)),
                    $fmtMoney($t['total_ventas'] ?? 0),
                    $fmtMoney($t['monto_empresa'] ?? 0),
                    $fmtMoney($t['dinero_entregar'] ?? 0),
                    $fmtMoney($t['saldo_real'] ?? 0),
                ], $i);
            }

            $filaTotalTabla($cols, [
                'TOTAL GENERAL',
                number_format((int) array_sum(array_column($terminales, 'total_cajas'))),
                $fmtMoney($totales['total_ventas'] ?? 0),
                $fmtMoney($totales['monto_empresa'] ?? 0),
                $fmtMoney($totales['dinero_entregar'] ?? 0),
                $fmtMoney($totales['saldo_real'] ?? 0),
            ]);

            if (!empty($usuarios)) {
                $tituloSeccion('Resumen por vendedor');

                $cols2 = [
                    ['label' => 'Vendedor', 'width' => 50, 'align' => 'L'],
                    ['label' => 'Terminal', 'width' => 34, 'align' => 'L'],
                    ['label' => 'Cajas', 'width' => 16, 'align' => 'C'],
                    ['label' => 'Ventas', 'width' => 28, 'align' => 'R'],
                    ['label' => 'Empresa', 'width' => 26, 'align' => 'R'],
                    ['label' => 'Entregar', 'width' => 32, 'align' => 'R'],
                ];

                $cabeceraTabla($cols2);

                foreach ($usuarios as $i => $u) {
                    $filaTabla($cols2, [
                        strtoupper($u['usuario'] ?? '-'),
                        strtoupper($u['terminal'] ?? '-'),
                        number_format((int) ($u['total_cajas'] ?? 0)),
                        $fmtMoney($u['total_ventas'] ?? 0),
                        $fmtMoney($u['monto_empresa'] ?? 0),
                        $fmtMoney($u['dinero_entregar'] ?? 0),
                    ], $i);
                }

                $filaTotalTabla($cols2, [
                    'TOTAL GENERAL',
                    '',
                    number_format((int) array_sum(array_column($usuarios, 'total_cajas'))),
                    $fmtMoney($totales['total_ventas'] ?? 0),
                    $fmtMoney($totales['monto_empresa'] ?? 0),
                    $fmtMoney($totales['dinero_entregar'] ?? 0),
                ]);
            }
        }
    }

    $pdf->Output('reporte_cajas_cerradas.pdf', 'I');

} catch (Exception $e) {
    error_log('Error PDF cajas cerradas: ' . $e->getMessage());
    die('Error al generar el PDF: ' . $e->getMessage());
}