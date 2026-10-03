<?php

/**
 * ORDEN DE SERVICIO (servicio tercerizado) — PDF con TCPDF
 *
 * Los datos se leen de: $this->data['orden_servicio']
 *
 * Diseño basado en el modelo original:
 *  - Encabezado: logo + datos de la empresa contratante | caja gris con título y número
 *  - Caja "DATOS DEL PROVEEDOR"
 *  - Ruta / Medida
 *  - Caja "DATOS DEL SERVICIO" (fecha, direcciones, vehículo, conductor)
 *  - Tabla de detalle (cabecera gris)
 *  - Valor de venta / IGV / Total
 *  - Importe en letras
 *  - Negociador / Fecha / Plazos de Pago
 *  - Firmas
 */

require_once('public/plugins/tc/tcpdf.php'); // <-- ajusta la ruta si es distinta en tu proyecto


class OrdenServicioPDF extends TCPDF
{
    // Si no tienes dejavusans en tu TCPDF, cambia a 'helvetica'
    const FUENTE = 'dejavusans';
    const TAM    = 8.5;

    public $numeroOrden = '';

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetMargins(12, 10, 12);
        $this->SetAutoPageBreak(true, 18);
        $this->setPrintHeader(false);
        $this->setPrintFooter(true);
        $this->SetCellPadding(0);
        $this->setCellHeightRatio(1.25);
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer()
    {
        $w = ($this->getPageWidth() - $this->lMargin - $this->rMargin) / 2;
        $this->SetY(-12);
        $this->SetFont(self::FUENTE, '', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell($w, 4, 'Orden de Servicio N° ' . $this->numeroOrden, 0, 0, 'L');
        $this->Cell($w, 4, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    // =====================================================
    // UTILIDADES
    // =====================================================

    public static function s($v): string
    {
        return trim((string) ($v ?? ''));
    }

    public static function fmt($n): string
    {
        return number_format((float) $n, 2, '.', '');
    }

    public static function fmtCant($n): string
    {
        $n = (float) $n;
        if (floor($n) == $n) {
            return (string) (int) $n;
        }
        return rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.');
    }

    private function anchoUtil(): float
    {
        return $this->getPageWidth() - $this->lMargin - $this->rMargin;
    }

    // Si no cabe $h en la página, agrega una nueva. Devuelve true si agregó página.
    private function comprobarEspacio(float $h): bool
    {
        if ($this->GetY() + $h > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
            return true;
        }
        return false;
    }

    // =====================================================
    // NÚMERO A LETRAS (formato peruano)
    // 1298 => "MIL DOSCIENTOS NOVENTA Y OCHO CON 00/100 SOLES"
    // =====================================================

    public static function letras(float $monto, string $nombreMoneda = 'SOLES'): string
    {
        $centavosTotal = (int) round($monto * 100);
        $entero = intdiv($centavosTotal, 100);
        $cent   = $centavosTotal % 100;
        return self::enteroALetras($entero) . ' CON ' . str_pad((string) $cent, 2, '0', STR_PAD_LEFT) . '/100 ' . $nombreMoneda;
    }

    private static function enteroALetras(int $n): string
    {
        if ($n === 0) {
            return 'CERO';
        }
        $partes    = [];
        $millones  = intdiv($n, 1000000);
        $miles     = intdiv($n % 1000000, 1000);
        $resto     = $n % 1000;

        if ($millones > 0) {
            $partes[] = ($millones === 1)
                ? 'UN MILLÓN'
                : self::apocope(self::enteroALetras($millones)) . ' MILLONES';
        }
        if ($miles > 0) {
            $partes[] = ($miles === 1)
                ? 'MIL'
                : self::apocope(self::grupo($miles)) . ' MIL';
        }
        if ($resto > 0) {
            $partes[] = self::grupo($resto);
        }
        return implode(' ', $partes);
    }

    private static function grupo(int $n): string
    {
        static $u = [
            '',
            'UNO',
            'DOS',
            'TRES',
            'CUATRO',
            'CINCO',
            'SEIS',
            'SIETE',
            'OCHO',
            'NUEVE',
            'DIEZ',
            'ONCE',
            'DOCE',
            'TRECE',
            'CATORCE',
            'QUINCE',
            'DIECISÉIS',
            'DIECISIETE',
            'DIECIOCHO',
            'DIECINUEVE',
            'VEINTE',
            'VEINTIUNO',
            'VEINTIDÓS',
            'VEINTITRÉS',
            'VEINTICUATRO',
            'VEINTICINCO',
            'VEINTISÉIS',
            'VEINTISIETE',
            'VEINTIOCHO',
            'VEINTINUEVE'
        ];
        static $d = [3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA'];
        static $c = [1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS', 6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS'];

        if ($n === 100) {
            return 'CIEN';
        }
        $out = [];
        $cen = intdiv($n, 100);
        $r   = $n % 100;
        if ($cen > 0) {
            $out[] = $c[$cen];
        }
        if ($r > 0) {
            if ($r < 30) {
                $out[] = $u[$r];
            } else {
                $dec = intdiv($r, 10);
                $uni = $r % 10;
                $out[] = $d[$dec] . ($uni > 0 ? ' Y ' . $u[$uni] : '');
            }
        }
        return implode(' ', $out);
    }

    // UNO -> UN / VEINTIUNO -> VEINTIÚN (cuando va antes de MIL / MILLONES)
    private static function apocope(string $t): string
    {
        $t = preg_replace('/VEINTIUNO$/u', 'VEINTIÚN', $t);
        return preg_replace('/UNO$/u', 'UN', $t);
    }

    // =====================================================
    // ENCABEZADO: logo + datos del emisor | caja gris con título y número
    // =====================================================

    public function encabezado(array $emisor, string $numero): void
    {
        $l     = $this->lMargin;
        $w     = $this->anchoUtil();
        $y0    = $this->GetY();
        $wBox  = 66;
        $hBox  = 26;
        $xBox  = $l + $w - $wBox;
        $xTxt  = $l;

        // Logo (opcional): usa $emisor['logo'] con la ruta del archivo
        $logo = self::s($emisor['logo'] ?? '');
        if ($logo !== '' && is_file($logo)) {
            $this->Image($logo, $l, $y0, 26, 0, '', '', '', false, 300);
            $xTxt = $l + 30;
        }
        $wTxt = $xBox - 4 - $xTxt;

        // Datos del emisor
        $direccion = self::s($emisor['direccion'] ?? '');
        $ubigeo = [];
        foreach (['departamento', 'provincia', 'distrito'] as $k) {
            $v = self::s($emisor[$k] ?? '');
            if ($v !== '') {
                $ubigeo[] = $v;
            }
        }
        if ($ubigeo) {
            $direccion .= ($direccion !== '' ? ', ' : '') . implode(' - ', $ubigeo);
        }

        $contacto = [];
        $tel = self::s($emisor['telefono'] ?? ($emisor['celular'] ?? ''));
        $mail = self::s($emisor['correo'] ?? ($emisor['email'] ?? ''));
        if ($tel !== '') {
            $contacto[] = $tel;
        }
        if ($mail !== '') {
            $contacto[] = $mail;
        }

        $this->SetXY($xTxt, $y0);
        $this->SetFont(self::FUENTE, 'B', 10);
        $this->MultiCell($wTxt, 5, self::s($emisor['razon_social'] ?? ''), 0, 'L', false, 1, $xTxt);
        $this->SetFont(self::FUENTE, '', self::TAM);
        $ruc = self::s($emisor['ruc'] ?? '');
        if ($ruc !== '') {
            $this->MultiCell($wTxt, 4.5, 'RUC: ' . $ruc, 0, 'L', false, 1, $xTxt);
        }
        if ($direccion !== '') {
            $this->MultiCell($wTxt, 4.5, $direccion, 0, 'L', false, 1, $xTxt);
        }
        if ($contacto) {
            $this->MultiCell($wTxt, 4.5, implode(' | ', $contacto), 0, 'L', false, 1, $xTxt);
        }
        $web = self::s($emisor['web'] ?? '');
        if ($web !== '') {
            $this->MultiCell($wTxt, 4.5, $web, 0, 'L', false, 1, $xTxt);
        }
        $yFinTxt = $this->GetY();

        // Caja gris "ORDEN DE SERVICIO"
        $this->SetFillColor(238, 238, 238);
        $this->SetDrawColor(190, 190, 190);
        $this->SetLineWidth(0.25);
        $this->RoundedRect($xBox, $y0, $wBox, $hBox, 3, '1111', 'DF');
        $this->SetFont(self::FUENTE, 'B', 12);
        $this->SetXY($xBox, $y0 + 5);
        $this->Cell($wBox, 7, 'ORDEN DE SERVICIO', 0, 0, 'C');
        $this->SetFont(self::FUENTE, 'B', 11);
        $this->SetXY($xBox, $y0 + 15);
        $this->Cell($wBox, 7, $numero, 0, 0, 'C');

        $this->SetXY($l, max($yFinTxt, $y0 + $hBox) + 6);
    }

    // =====================================================
    // CAJA CON BORDE REDONDEADO: etiqueta (negrita) + valor
    // =====================================================

    public function cajaDatos(string $titulo, array $filas, float $wEtq = 48): void
    {
        $l    = $this->lMargin;
        $w    = $this->anchoUtil();
        $pad  = 2.2;
        $min  = 5.4;
        $wVal = $w - $wEtq - 2 * $pad;

        $alturas = [];
        $suma = 0;
        foreach ($filas as $i => $f) {
            $this->SetFont(self::FUENTE, 'B', self::TAM);
            $hE = $this->getStringHeight($wEtq, (string) $f[0]);
            $this->SetFont(self::FUENTE, '', self::TAM);
            $hV = $this->getStringHeight($wVal, (string) $f[1]);
            $alturas[$i] = max($min, $hE, $hV);
            $suma += $alturas[$i];
        }
        $hBox = $suma + 2 * $pad;

        $this->comprobarEspacio(6 + $hBox);

        // Título de la sección
        $this->SetFont(self::FUENTE, 'B', 9.5);
        $this->SetX($l);
        $this->Cell($w, 6, $titulo, 0, 1, 'L');

        // Caja
        $y = $this->GetY();
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.25);
        $this->RoundedRect($l, $y, $w, $hBox, 3, '1111', 'D');

        $cy = $y + $pad;
        foreach ($filas as $i => $f) {
            $h = $alturas[$i];
            $this->SetFont(self::FUENTE, 'B', self::TAM);
            $this->MultiCell($wEtq, $h, (string) $f[0], 0, 'L', false, 0, $l + $pad, $cy, true, 0, false, true, $h + 2, 'M');
            $this->SetFont(self::FUENTE, '', self::TAM);
            $this->MultiCell($wVal, $h, (string) $f[1], 0, 'L', false, 0, $l + $pad + $wEtq, $cy, true, 0, false, true, $h + 2, 'M');
            $cy += $h;
        }
        $this->SetXY($l, $y + $hBox + 5);
    }

    // =====================================================
    // RUTA (izquierda) y MEDIDA (derecha)
    // =====================================================

    public function lineaRuta(string $ruta, string $medida): void
    {
        $l     = $this->lMargin;
        $w     = $this->anchoUtil();
        $wRuta = ($medida !== '') ? $w * 0.58 : $w;

        $this->SetFont(self::FUENTE, 'B', self::TAM);
        $wE = $this->GetStringWidth('Ruta: ') + 1;
        $this->SetFont(self::FUENTE, '', self::TAM);
        $h = max(5.4, $this->getStringHeight($wRuta - $wE - 2, $ruta));

        $this->comprobarEspacio($h + 6);
        $y = $this->GetY();

        $this->SetFont(self::FUENTE, 'B', self::TAM);
        $this->MultiCell($wE, $h, 'Ruta:', 0, 'L', false, 0, $l, $y, true, 0, false, true, $h + 2, 'M');
        $this->SetFont(self::FUENTE, '', self::TAM);
        $this->MultiCell($wRuta - $wE - 2, $h, $ruta, 0, 'L', false, 0, $l + $wE, $y, true, 0, false, true, $h + 2, 'M');

        if ($medida !== '') {
            $xMed = $l + $wRuta + 2;
            $this->SetFont(self::FUENTE, 'B', self::TAM);
            $wM = $this->GetStringWidth('Medida: ') + 1;
            $this->MultiCell($wM, $h, 'Medida:', 0, 'L', false, 0, $xMed, $y, true, 0, false, true, $h + 2, 'M');
            $this->MultiCell($l + $w - $xMed - $wM, $h, $medida, 0, 'L', false, 0, $xMed + $wM, $y, true, 0, false, true, $h + 2, 'M');
        }
        $this->SetXY($l, $y + $h + 4);
    }

    // =====================================================
    // TABLA DE DETALLE (cabecera gris, sin bordes)
    // =====================================================

    private function cabeceraTabla(array $cols): void
    {
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont(self::FUENTE, 'B', self::TAM);
        $y = $this->GetY();
        $x = $this->lMargin;
        foreach (['Cantidad', 'Descripción', 'V.U.', 'Importe'] as $i => $t) {
            $this->SetXY($x, $y);
            $this->Cell($cols[$i], 7, $t, 0, 0, 'C', true);
            $x += $cols[$i];
        }
        $this->SetXY($this->lMargin, $y + 7);
    }

    public function tablaDetalle(array $items): void
    {
        $l    = $this->lMargin;
        $cols = [28, 88, 35, 35]; // suma 186
        $this->setCellPaddings(1.5, 1, 1.5, 1);

        $this->comprobarEspacio(22);
        $this->cabeceraTabla($cols);

        foreach ($items as $it) {
            $cant = self::fmtCant($it['cantidad'] ?? 0);
            $desc = self::s($it['descripcion'] ?? '');
            $vu   = self::fmt($it['valor_unitario'] ?? 0);
            $imp  = self::fmt($it['importe'] ?? 0);

            $this->SetFont(self::FUENTE, '', self::TAM);
            $h = max(6.5, $this->getStringHeight($cols[1], $desc));

            if ($this->comprobarEspacio($h + 2)) {
                $this->cabeceraTabla($cols);
                $this->SetFont(self::FUENTE, '', self::TAM);
            }
            $y = $this->GetY();
            $x = $l;
            $vals   = [$cant, $desc, $vu, $imp];
            $aligns = ['C', 'L', 'R', 'R'];
            foreach ($vals as $i => $v) {
                $this->MultiCell($cols[$i], $h, $v, 0, $aligns[$i], false, 0, $x, $y, true, 0, false, true, $h + 2, 'M');
                $x += $cols[$i];
            }
            $this->SetXY($l, $y + $h);
        }

        $this->setCellPaddings(0, 0, 0, 0);
        $this->SetY($this->GetY() + 3);
    }

    // =====================================================
    // TOTALES (alineados a la derecha)
    // =====================================================

    public function totales(float $valorVenta, float $igv, float $total, string $simbolo): void
    {
        $this->comprobarEspacio(20);
        $wVal  = 32;
        $wEtq  = 48;
        $right = $this->lMargin + $this->anchoUtil() - 1.5;
        $xEtq  = $right - $wVal - $wEtq;

        $filas = [
            ['VALOR DE VENTA: ' . $simbolo, $valorVenta],
            ['IGV: ' . $simbolo,            $igv],
            ['TOTAL: ' . $simbolo,          $total],
        ];
        foreach ($filas as $f) {
            $y = $this->GetY();
            $this->SetXY($xEtq, $y);
            $this->SetFont(self::FUENTE, 'B', self::TAM);
            $this->Cell($wEtq, 5.5, $f[0], 0, 0, 'L');
            $this->SetFont(self::FUENTE, '', self::TAM);
            $this->Cell($wVal, 5.5, self::fmt($f[1]), 0, 1, 'R');
        }
        $this->SetXY($this->lMargin, $this->GetY() + 5);
    }

    // =====================================================
    // IMPORTE EN LETRAS (centrado)
    // =====================================================

    public function importeEnLetras(string $texto): void
    {
        $this->comprobarEspacio(14);
        $this->SetFont(self::FUENTE, '', self::TAM);
        $html = '<b>IMPORTE EN LETRAS:</b> ' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        $this->writeHTMLCell($this->anchoUtil(), 0, $this->lMargin, $this->GetY(), $html, 0, 1, false, true, 'C', true);
        $this->SetXY($this->lMargin, $this->GetY() + 6);
    }

    public function observacion(string $texto): void
    {
        if ($texto === '') {
            return;
        }
        $this->SetFont(self::FUENTE, '', self::TAM);
        $h = 5.5 + $this->getStringHeight($this->anchoUtil(), $texto);
        $this->comprobarEspacio($h + 4);
        $this->SetFont(self::FUENTE, 'B', self::TAM);
        $this->Cell(0, 5.5, 'Observaciones:', 0, 1, 'L');
        $this->SetFont(self::FUENTE, '', self::TAM);
        $this->MultiCell(0, 5, $texto, 0, 'L', false, 1);
        $this->SetXY($this->lMargin, $this->GetY() + 5);
    }

    // =====================================================
    // PAGOS HECHOS AL PROVEEDOR
    // =====================================================

    public function pagosProveedor(array $pagos, string $simbolo): void
    {
        if (empty($pagos)) {
            return;
        }

        $l = $this->lMargin;
        $w = $this->anchoUtil();

        $estado       = strtoupper(self::s($pagos['estado'] ?? ''));
        $totalServ    = (float) ($pagos['total_servicio'] ?? 0);
        $totalPagado  = (float) ($pagos['total_pagado'] ?? 0);
        $saldo        = (float) ($pagos['saldo'] ?? ($totalServ - $totalPagado));
        $historial    = $pagos['historial'] ?? [];

        // Color de la etiqueta de estado
        $coloresEstado = [
            'PAGADO'    => [40, 130, 60],
            'PARCIAL'   => [200, 140, 0],
            'PENDIENTE' => [180, 40, 40],
        ];
        $colorEstado = $coloresEstado[$estado] ?? [90, 90, 90];

        $this->comprobarEspacio(16);

        // Título de la sección
        $this->SetFont(self::FUENTE, 'B', 9.5);
        $this->SetX($l);
        $this->Cell($w * 0.7, 6, 'PAGOS HECHOS AL PROVEEDOR', 0, 0, 'L');

        if ($estado !== '') {
            $this->SetFont(self::FUENTE, 'B', 8);
            $this->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
            $this->Cell($w * 0.3, 6, $estado, 0, 1, 'R');
            $this->SetTextColor(0, 0, 0);
        } else {
            $this->Ln(6);
        }

        // Resumen: total del servicio / pagado / saldo
        $wCol = $w / 3;
        $y = $this->GetY();
        $resumen = [
            ['TOTAL SERVICIO', self::fmt($totalServ)],
            ['PAGADO',         self::fmt($totalPagado)],
            ['SALDO',          self::fmt($saldo)],
        ];
        $this->SetFillColor(245, 245, 245);
        $this->SetDrawColor(200, 200, 200);
        $this->RoundedRect($l, $y, $w, 13, 2, '1111', 'DF');
        foreach ($resumen as $i => $r) {
            $x = $l + $i * $wCol;
            $this->SetFont(self::FUENTE, '', 7.5);
            $this->SetXY($x, $y + 2);
            $this->Cell($wCol, 4, $r[0], 0, 0, 'C');
            $this->SetFont(self::FUENTE, 'B', 9.5);
            $this->SetXY($x, $y + 6.5);
            $this->Cell($wCol, 5, $simbolo . ' ' . $r[1], 0, 0, 'C');
        }
        $this->SetXY($l, $y + 13 + 4);

        // Historial de pagos
        if (!empty($historial)) {
            $cols = [30, 40, 40, 30, 46]; // Fecha | Medio de pago | N° operación | Monto | Observación (suma 186)
            $this->SetFillColor(200, 200, 200);
            $this->SetFont(self::FUENTE, 'B', 8);
            $y = $this->GetY();
            $x = $l;
            foreach (['Fecha', 'Medio de pago', 'N° operación', 'Monto', 'Observación'] as $i => $t) {
                $this->SetXY($x, $y);
                $this->Cell($cols[$i], 6.5, $t, 0, 0, $i === 3 ? 'R' : 'C', true);
                $x += $cols[$i];
            }
            $this->SetXY($l, $y + 6.5);

            $this->SetFont(self::FUENTE, '', 8);
            foreach ($historial as $p) {
                $fecha   = self::s($p['fecha_pago'] ?? '');
                $medio   = self::s($p['medio_pago'] ?? '') ?: '-';
                $numOp   = self::s($p['numero_operacion'] ?? '') ?: '-';
                $monto   = self::fmt($p['monto'] ?? 0);
                $obsPago = self::s($p['observacion'] ?? '') ?: '-';

                $vals   = [$fecha, $medio, $numOp, $monto, $obsPago];
                $aligns = ['C', 'C', 'C', 'R', 'L'];

                $h = 5;
                foreach ($vals as $i => $v) {
                    $h = max($h, $this->getStringHeight($cols[$i], $v));
                }

                if ($this->comprobarEspacio($h + 1)) {
                    $this->SetFillColor(200, 200, 200);
                    $this->SetFont(self::FUENTE, 'B', 8);
                    $yh = $this->GetY();
                    $xh = $l;
                    foreach (['Fecha', 'Medio de pago', 'N° operación', 'Monto', 'Observación'] as $i => $t) {
                        $this->SetXY($xh, $yh);
                        $this->Cell($cols[$i], 6.5, $t, 0, 0, $i === 3 ? 'R' : 'C', true);
                        $xh += $cols[$i];
                    }
                    $this->SetXY($l, $yh + 6.5);
                    $this->SetFont(self::FUENTE, '', 8);
                }

                $y = $this->GetY();
                $x = $l;
                foreach ($vals as $i => $v) {
                    $this->MultiCell($cols[$i] - 2, $h, $v, 0, $aligns[$i], false, 0, $x + 1, $y, true, 0, false, true, $h, 'M');
                    $x += $cols[$i];
                }
                $this->SetXY($l, $y + $h);
            }
        }

        $this->SetXY($l, $this->GetY() + 5);
    }

    // =====================================================
    // PIE EN 3 COLUMNAS: Negociador | Fecha | Plazos de Pago
    // =====================================================

    public function pie3(array $cols): void
    {
        $l  = $this->lMargin;
        $w3 = $this->anchoUtil() / 3;

        $this->SetFont(self::FUENTE, '', self::TAM);
        $hMax = 5;
        foreach ($cols as $c) {
            $hMax = max($hMax, $this->getStringHeight($w3 - 3, (string) $c[1]));
        }
        $h = 5.5 + $hMax;
        $this->comprobarEspacio($h + 4);
        $y = $this->GetY();

        foreach ($cols as $i => $c) {
            $x = $l + $i * $w3;
            $this->SetFont(self::FUENTE, 'B', 9);
            $this->SetXY($x, $y);
            $this->Cell($w3, 5.5, $c[0], 0, 0, 'L');
            $this->SetFont(self::FUENTE, '', self::TAM);
            $this->MultiCell($w3 - 3, $hMax, (string) $c[1], 0, 'L', false, 0, $x, $y + 5.5, true, 0, false, true, $hMax, 'T');
        }
        $this->SetXY($l, $y + $h);
    }

    // =====================================================
    // FIRMAS
    // =====================================================

    public function firmas(string $izq, string $der): void
    {
        $this->comprobarEspacio(40);
        $l  = $this->lMargin;
        $w  = $this->anchoUtil();
        $wl = 65;
        $y  = $this->GetY() + 22;
        $x1 = $l + 8;
        $x2 = $l + $w - 8 - $wl;

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->Line($x1, $y, $x1 + $wl, $y);
        $this->Line($x2, $y, $x2 + $wl, $y);

        $this->SetFont(self::FUENTE, 'B', 7.5);
        $this->SetXY($x1, $y + 1);
        $this->Cell($wl, 4, 'EMISOR', 0, 0, 'C');
        $this->SetXY($x2, $y + 1);
        $this->Cell($wl, 4, 'PROVEEDOR', 0, 0, 'C');

        $this->SetFont(self::FUENTE, '', 7);
        $this->MultiCell($wl, 3.5, $izq, 0, 'C', false, 0, $x1, $y + 5.5);
        $this->MultiCell($wl, 3.5, $der, 0, 'C', false, 0, $x2, $y + 5.5);
    }
}

// =============================================
// DATOS: $this->data['orden_servicio']
// =============================================

$os = (isset($this) && isset($this->data['orden_servicio'])) ? $this->data['orden_servicio'] : null;

if (empty($os) || !is_array($os)) {
    http_response_code(404);
    die('No se encontraron datos para generar la orden de servicio.');
}

date_default_timezone_set('America/Lima');

$emisor = $os['emisor']     ?? [];
$prov   = $os['proveedor']  ?? [];
$srv    = $os['servicio']   ?? [];
$trp    = $os['transporte'] ?? [];
$det    = $os['detalle']    ?? [];
$tot    = $os['totales']    ?? [];

$numero = OrdenServicioPDF::s($os['numero'] ?? '');

// Moneda (por defecto soles)
$moneda = strtoupper(OrdenServicioPDF::s($os['moneda'] ?? 'PEN'));
if ($moneda === 'USD') {
    $simbolo = 'US$';
    $nombreMoneda = 'DÓLARES AMERICANOS';
} else {
    $simbolo = 'S/';
    $nombreMoneda = 'SOLES';
}

// Totales (si no vienen, se calculan desde el detalle)
$sumaImportes = 0;
foreach ($det as $it) {
    $sumaImportes += (float) ($it['importe'] ?? 0);
}
$valorVenta = isset($tot['valor_venta']) ? (float) $tot['valor_venta'] : round($sumaImportes, 2);
$igv        = isset($tot['igv'])         ? (float) $tot['igv']         : round($valorVenta * 0.18, 2);
$total      = isset($tot['total'])       ? (float) $tot['total']       : round($valorVenta + $igv, 2);

// Ruta
$ruta = OrdenServicioPDF::s($srv['ruta'] ?? '');
if ($ruta === '') {
    $partesRuta = [];
    if (OrdenServicioPDF::s($srv['origen'] ?? '') !== '') {
        $partesRuta[] = OrdenServicioPDF::s($srv['origen']);
    }
    if (OrdenServicioPDF::s($srv['destino'] ?? '') !== '') {
        $partesRuta[] = OrdenServicioPDF::s($srv['destino']);
    }
    $ruta = implode(' - ', $partesRuta);
}

// =============================================
// ARMAR EL PDF
// =============================================

$pdf = new OrdenServicioPDF();
$pdf->numeroOrden = $numero;
$pdf->SetCreator('Sistema de Transporte');
$pdf->SetAuthor(OrdenServicioPDF::s($emisor['razon_social'] ?? ''));
$pdf->SetTitle('Orden de Servicio ' . $numero);
$pdf->AddPage();

// 1) Encabezado
$pdf->encabezado($emisor, $numero);

// 2) Datos del proveedor (se muestran las 6 filas aunque estén vacías, como en el original)
$pdf->cajaDatos('DATOS DEL PROVEEDOR', [
    ['RAZÓN SOCIAL',        OrdenServicioPDF::s($prov['razon_social'] ?? '')],
    ['RUC',                 OrdenServicioPDF::s($prov['ruc'] ?? '')],
    ['NOMBRE DE CONTACTO',  OrdenServicioPDF::s($prov['nombre_contacto'] ?? '')],
    ['DIRECCIÓN',           OrdenServicioPDF::s($prov['direccion'] ?? '')],
    ['TELÉFONO',            OrdenServicioPDF::s($prov['telefono'] ?? '')],
    ['EMAIL',               OrdenServicioPDF::s($prov['email'] ?? '')],
], 48);

// 3) Ruta y medida (la medida solo se muestra si viene con valor)
$pdf->lineaRuta($ruta, OrdenServicioPDF::s($srv['medida'] ?? ''));

// 4) Datos del servicio y transporte
$fecha = OrdenServicioPDF::s($srv['fecha'] ?? ($os['fecha_servicio'] ?? ''));
$hora  = OrdenServicioPDF::s($srv['hora'] ?? ($os['hora_servicio'] ?? ''));
$fechaHora = trim($fecha . ' ' . substr($hora, 0, 5));

$filasServicio = [];
if ($fechaHora !== '') {
    $filasServicio[] = ['FECHA DEL SERVICIO', $fechaHora];
}
if (OrdenServicioPDF::s($srv['direccion_origen'] ?? '') !== '') {
    $filasServicio[] = ['DIRECCIÓN DE ORIGEN', OrdenServicioPDF::s($srv['direccion_origen'])];
}
if (OrdenServicioPDF::s($srv['direccion_destino'] ?? '') !== '') {
    $filasServicio[] = ['DIRECCIÓN DE DESTINO', OrdenServicioPDF::s($srv['direccion_destino'])];
}
$hayTransporte = false;
foreach (['vehiculo', 'placa', 'conductor', 'documento_conductor'] as $k) {
    if (OrdenServicioPDF::s($trp[$k] ?? '') !== '') {
        $hayTransporte = true;
    }
}
if ($hayTransporte) {
    $filasServicio[] = ['VEHÍCULO',         OrdenServicioPDF::s($trp['vehiculo'] ?? '')];
    $filasServicio[] = ['PLACA',            OrdenServicioPDF::s($trp['placa'] ?? '')];
    $filasServicio[] = ['CONDUCTOR',        OrdenServicioPDF::s($trp['conductor'] ?? '')];
    $filasServicio[] = ['DOC. CONDUCTOR',   OrdenServicioPDF::s($trp['documento_conductor'] ?? '')];
}
if ($filasServicio) {
    $pdf->cajaDatos('DATOS DEL SERVICIO', $filasServicio, 48);
}

// 5) Detalle
$pdf->tablaDetalle($det);

// 6) Totales
$pdf->totales($valorVenta, $igv, $total, $simbolo);

// 7) Importe en letras
$pdf->importeEnLetras(OrdenServicioPDF::letras($total, $nombreMoneda));

// 8) Observación (solo si existe)
$pdf->observacion(OrdenServicioPDF::s($os['observacion'] ?? ''));

// 8.1) Pagos hechos al proveedor (solo si vienen datos)
$pdf->pagosProveedor($os['pagos'] ?? [], $simbolo);

// 9) Negociador / Fecha / Plazos de pago
$pdf->pie3([
    ['Negociador',     OrdenServicioPDF::s($os['negociador'] ?? '')],
    ['Fecha',          OrdenServicioPDF::s($os['fecha_emision'] ?? '')],
    ['Plazos de Pago', OrdenServicioPDF::s($os['plazo_pago'] ?? '')],
]);

// 10) Firmas
$pdf->firmas(
    OrdenServicioPDF::s($emisor['razon_social'] ?? ''),
    OrdenServicioPDF::s($prov['razon_social'] ?? '')
);

// =============================================
// SALIDA
// =============================================

$nombreArchivo = 'Orden_Servicio_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $numero !== '' ? $numero : 'sin_numero') . '.pdf';

if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output($nombreArchivo, 'I'); // I = ver en el navegador | D = forzar descarga
exit;
