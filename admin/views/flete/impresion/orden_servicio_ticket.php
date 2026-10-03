<?php

/**
 * ORDEN DE SERVICIO (servicio tercerizado) — FORMATO TICKET 80 mm — TCPDF
 *
 * Los datos se leen de: $this->data['orden_servicio']
 *
 * - Ancho fijo de 80 mm (impresora térmica).
 * - El alto se ajusta solo al contenido (no queda papel en blanco).
 */

require_once('public/plugins/tc/tcpdf.php'); // <-- ajusta la ruta si es distinta en tu proyecto


class OrdenServicioTicketPDF extends TCPDF
{
    const FUENTE = 'helvetica';
    const TAM    = 7.5;
    const ANCHO  = 80;   // mm
    const MARGEN = 3;    // mm

    public function __construct(float $alto = 300)
    {
        // Nota: TCPDF invierte medidas si el alto es menor que el ancho, por eso el mínimo de 90
        parent::__construct('P', 'mm', [self::ANCHO, max(90, $alto)], true, 'UTF-8', false);
        $this->SetMargins(self::MARGEN, self::MARGEN, self::MARGEN);
        $this->SetAutoPageBreak(false, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetCellPadding(0);
        $this->setCellHeightRatio(1.25);
        $this->SetTextColor(0, 0, 0);
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
        return self::ANCHO - 2 * self::MARGEN;
    }

    // ---------- Número a letras (formato peruano) ----------

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
        $partes   = [];
        $millones = intdiv($n, 1000000);
        $miles    = intdiv($n % 1000000, 1000);
        $resto    = $n % 1000;

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

    private static function apocope(string $t): string
    {
        $t = preg_replace('/VEINTIUNO$/u', 'VEINTIÚN', $t);
        return preg_replace('/UNO$/u', 'UN', $t);
    }

    // =====================================================
    // BLOQUES DE DIBUJO
    // =====================================================

    // Texto centrado (una o varias líneas)
    private function centrado(string $txt, string $estilo = '', float $tam = self::TAM, float $lh = 3.8): void
    {
        if ($txt === '') {
            return;
        }
        $this->SetFont(self::FUENTE, $estilo, $tam);
        $this->MultiCell($this->anchoUtil(), $lh, $txt, 0, 'C', false, 1, self::MARGEN);
    }

    // Línea punteada
    private function separador(float $antes = 1.5, float $despues = 2): void
    {
        $y = $this->GetY() + $antes;
        $this->SetLineStyle(['width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '1,1', 'color' => [0, 0, 0]]);
        $this->Line(self::MARGEN, $y, self::ANCHO - self::MARGEN, $y);
        $this->SetLineStyle(['width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => [0, 0, 0]]);
        $this->SetXY(self::MARGEN, $y + $despues);
    }

    // Barra gris con el título de la sección (como la cabecera gris del modelo)
    private function seccion(string $titulo): void
    {
        $this->SetFillColor(215, 215, 215);
        $this->SetFont(self::FUENTE, 'B', 7.5);
        $this->SetX(self::MARGEN);
        $this->Cell($this->anchoUtil(), 4.6, ' ' . $titulo, 0, 1, 'L', true);
        $this->SetY($this->GetY() + 1);
    }

    // "Etiqueta: valor" (etiqueta en negrita, el valor se ajusta a varias líneas)
    private function fila(string $etq, string $val, bool $siempre = false): void
    {
        if ($val === '' && !$siempre) {
            return;
        }
        $this->SetFont(self::FUENTE, '', self::TAM);
        $html = '<b>' . htmlspecialchars($etq, ENT_QUOTES, 'UTF-8') . '</b> ' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        $this->writeHTMLCell($this->anchoUtil(), 0, self::MARGEN, $this->GetY(), $html, 0, 1, false, true, 'L', true);
        $this->SetY($this->GetY() + 0.6);
    }

    // Pagos hechos al proveedor: resumen + historial
    private function pagosProveedor(array $pagos, string $simbolo): void
    {
        if (empty($pagos)) {
            return;
        }

        $l = self::MARGEN;
        $w = $this->anchoUtil();

        $estado      = strtoupper(self::s($pagos['estado'] ?? ''));
        $totalServ   = (float) ($pagos['total_servicio'] ?? 0);
        $totalPagado = (float) ($pagos['total_pagado'] ?? 0);
        $saldo       = (float) ($pagos['saldo'] ?? ($totalServ - $totalPagado));
        $historial   = $pagos['historial'] ?? [];

        $this->separador(0.5, 1.5);

        $this->seccion('PAGOS AL PROVEEDOR' . ($estado !== '' ? ' - ' . $estado : ''));

        $this->filaLR('Total servicio:', $simbolo . ' ' . self::fmt($totalServ));
        $this->filaLR('Pagado:',         $simbolo . ' ' . self::fmt($totalPagado));
        $this->filaLR('Saldo:',          $simbolo . ' ' . self::fmt($saldo), 'B', 8.5);
        $this->SetY($this->GetY() + 1);

        if (!empty($historial)) {
            $this->SetFont(self::FUENTE, 'B', 7);
            $this->SetX($l);
            $this->Cell($w, 4, 'Historial de pagos:', 0, 1, 'L');

            foreach ($historial as $p) {
                $fecha = self::s($p['fecha_pago'] ?? '');
                $monto = self::fmt($p['monto'] ?? 0);
                $medio = self::s($p['medio_pago'] ?? '');
                $numOp = self::s($p['numero_operacion'] ?? '');
                $obs   = self::s($p['observacion'] ?? '');

                $this->filaLR($fecha, $simbolo . ' ' . $monto, '', 7.5);

                $detalles = [];
                if ($medio !== '') {
                    $detalles[] = 'Medio: ' . $medio;
                }
                if ($numOp !== '') {
                    $detalles[] = 'Op.: ' . $numOp;
                }
                if ($detalles) {
                    $this->SetFont(self::FUENTE, '', 7);
                    $this->SetX($l);
                    $this->MultiCell($w, 3.2, implode(' | ', $detalles), 0, 'L', false, 1, $l);
                }
                if ($obs !== '') {
                    $this->SetFont(self::FUENTE, '', 7);
                    $this->SetX($l);
                    $this->MultiCell($w, 3.2, 'Obs: ' . $obs, 0, 'L', false, 1, $l);
                }
                $this->SetY($this->GetY() + 0.5);
            }
        }
    }

    // Izquierda / derecha en la misma línea
    private function filaLR(string $izq, string $der, string $estilo = '', float $tam = self::TAM): void
    {
        $w = $this->anchoUtil();
        $this->SetFont(self::FUENTE, $estilo, $tam);
        $this->SetX(self::MARGEN);
        $this->Cell($w * 0.62, 4.4, $izq, 0, 0, 'L');
        $this->Cell($w * 0.38, 4.4, $der, 0, 1, 'R');
    }

    // =====================================================
    // DIBUJA TODO EL TICKET Y DEVUELVE LA POSICIÓN Y FINAL
    // =====================================================

    public function dibujar(array $os): float
    {
        $l = self::MARGEN;
        $w = $this->anchoUtil();

        $emisor = $os['emisor']     ?? [];
        $prov   = $os['proveedor']  ?? [];
        $srv    = $os['servicio']   ?? [];
        $trp    = $os['transporte'] ?? [];
        $det    = $os['detalle']    ?? [];
        $tot    = $os['totales']    ?? [];
        $numero = self::s($os['numero'] ?? '');

        // Moneda (por defecto soles)
        if (strtoupper(self::s($os['moneda'] ?? 'PEN')) === 'USD') {
            $simbolo = 'US$';
            $nombreMoneda = 'DÓLARES AMERICANOS';
        } else {
            $simbolo = 'S/';
            $nombreMoneda = 'SOLES';
        }

        // Totales (si no vienen, se calculan)
        $suma = 0;
        foreach ($det as $it) {
            $suma += (float) ($it['importe'] ?? 0);
        }
        $valorVenta = isset($tot['valor_venta']) ? (float) $tot['valor_venta'] : round($suma, 2);
        $igv        = isset($tot['igv'])         ? (float) $tot['igv']         : round($valorVenta * 0.18, 2);
        $total      = isset($tot['total'])       ? (float) $tot['total']       : round($valorVenta + $igv, 2);

        // ---------- 1) EMISOR (centrado) ----------
        $this->SetXY($l, self::MARGEN);

        $logo = self::s($emisor['logo'] ?? '');
        if ($logo !== '' && is_file($logo)) {
            $wl   = 22;
            $y    = $this->GetY();
            $info = @getimagesize($logo);
            $hl   = ($info && $info[0] > 0) ? $wl * $info[1] / $info[0] : $wl;
            $this->Image($logo, (self::ANCHO - $wl) / 2, $y, $wl, 0, '', '', '', false, 300);
            $this->SetY($y + $hl + 2);
        }

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
        $tel  = self::s($emisor['telefono'] ?? ($emisor['celular'] ?? ''));
        $mail = self::s($emisor['correo'] ?? ($emisor['email'] ?? ''));
        if ($tel !== '') {
            $contacto[] = $tel;
        }
        if ($mail !== '') {
            $contacto[] = $mail;
        }

        $this->centrado(self::s($emisor['razon_social'] ?? ''), 'B', 9.5, 4.4);
        $ruc = self::s($emisor['ruc'] ?? '');
        $this->centrado($ruc !== '' ? 'RUC: ' . $ruc : '');
        $this->centrado($direccion);
        $this->centrado(implode(' | ', $contacto));
        $this->centrado(self::s($emisor['web'] ?? ''));

        $this->separador(1, 2.5);

        // ---------- 2) CAJA "ORDEN DE SERVICIO" ----------
        $y = $this->GetY();
        $this->SetFillColor(238, 238, 238);
        $this->SetDrawColor(190, 190, 190);
        $this->SetLineWidth(0.25);
        $this->RoundedRect($l, $y, $w, 14, 2.5, '1111', 'DF');
        $this->SetFont(self::FUENTE, 'B', 10);
        $this->SetXY($l, $y + 2);
        $this->Cell($w, 5, 'ORDEN DE SERVICIO', 0, 0, 'C');
        $this->SetFont(self::FUENTE, 'B', 12);
        $this->SetXY($l, $y + 7.5);
        $this->Cell($w, 5, $numero, 0, 0, 'C');
        $this->SetXY($l, $y + 14 + 2.5);

        // ---------- 3) PROVEEDOR ----------
        $this->seccion('DATOS DEL PROVEEDOR');
        $this->fila('Razón social:', self::s($prov['razon_social'] ?? ''), true);
        $this->fila('RUC:',          self::s($prov['ruc'] ?? ''), true);
        $this->fila('Contacto:',     self::s($prov['nombre_contacto'] ?? ''));
        $this->fila('Dirección:',    self::s($prov['direccion'] ?? ''));
        $this->fila('Teléfono:',     self::s($prov['telefono'] ?? ''));
        $this->fila('Email:',        self::s($prov['email'] ?? ''));
        $this->SetY($this->GetY() + 1.5);

        // ---------- 4) SERVICIO ----------
        $ruta = self::s($srv['ruta'] ?? '');
        if ($ruta === '') {
            $pr = [];
            if (self::s($srv['origen'] ?? '') !== '') {
                $pr[] = self::s($srv['origen']);
            }
            if (self::s($srv['destino'] ?? '') !== '') {
                $pr[] = self::s($srv['destino']);
            }
            $ruta = implode(' - ', $pr);
        }
        $fecha = self::s($srv['fecha'] ?? ($os['fecha_servicio'] ?? ''));
        $hora  = self::s($srv['hora'] ?? ($os['hora_servicio'] ?? ''));

        $this->seccion('DATOS DEL SERVICIO');
        $this->fila('Ruta:', $ruta, true);
        $this->fila('Medida:', self::s($srv['medida'] ?? ''));
        $this->fila('Origen:',  self::s($srv['direccion_origen'] ?? ''));
        $this->fila('Destino:', self::s($srv['direccion_destino'] ?? ''));
        $this->fila('Fecha servicio:', trim($fecha . ' ' . substr($hora, 0, 5)));
        $this->SetY($this->GetY() + 1.5);

        // ---------- 5) TRANSPORTE (solo si hay datos) ----------
        $hayTrp = false;
        foreach (['vehiculo', 'placa', 'conductor', 'documento_conductor'] as $k) {
            if (self::s($trp[$k] ?? '') !== '') {
                $hayTrp = true;
            }
        }
        if ($hayTrp) {
            $this->seccion('TRANSPORTE');
            $this->fila('Vehículo:',  self::s($trp['vehiculo'] ?? ''));
            $this->fila('Placa:',     self::s($trp['placa'] ?? ''));
            $this->fila('Conductor:', self::s($trp['conductor'] ?? ''));
            $this->fila('Doc.:',      self::s($trp['documento_conductor'] ?? ''));
            $this->SetY($this->GetY() + 1.5);
        }

        // ---------- 6) DETALLE ----------
        $this->SetFillColor(200, 200, 200);
        $this->SetFont(self::FUENTE, 'B', 7.5);
        $this->SetX($l);
        $this->Cell($w * 0.62, 4.6, ' DESCRIPCIÓN', 0, 0, 'L', true);
        $this->Cell($w * 0.38, 4.6, 'IMPORTE ', 0, 1, 'R', true);
        $this->SetY($this->GetY() + 1);

        foreach ($det as $it) {
            $desc = self::s($it['descripcion'] ?? '');
            $this->SetFont(self::FUENTE, '', self::TAM);
            $this->MultiCell($w, 3.6, $desc, 0, 'L', false, 1, $l);
            $this->filaLR(
                self::fmtCant($it['cantidad'] ?? 0) . ' x ' . self::fmt($it['valor_unitario'] ?? 0),
                self::fmt($it['importe'] ?? 0)
            );
            $this->SetY($this->GetY() + 0.8);
        }

        $this->separador(0.5, 1.5);

        // ---------- 7) TOTALES ----------
        $this->filaLR('VALOR DE VENTA: ' . $simbolo, self::fmt($valorVenta), 'B');
        $this->filaLR('IGV: ' . $simbolo,            self::fmt($igv),        'B');
        $this->filaLR('TOTAL: ' . $simbolo,          self::fmt($total),      'B', 9);

        $this->separador(0.5, 2);

        // ---------- 8) IMPORTE EN LETRAS ----------
        $this->SetFont(self::FUENTE, '', self::TAM);
        $html = '<b>IMPORTE EN LETRAS:</b><br>' . htmlspecialchars(self::letras($total, $nombreMoneda), ENT_QUOTES, 'UTF-8');
        $this->writeHTMLCell($w, 0, $l, $this->GetY(), $html, 0, 1, false, true, 'C', true);
        $this->SetY($this->GetY() + 1.5);

        // ---------- 9) OBSERVACIÓN ----------
        $obs = self::s($os['observacion'] ?? '');
        if ($obs !== '') {
            $this->separador(0.5, 1.5);
            $this->fila('Observación:', $obs);
        }

        // ---------- 9.1) PAGOS HECHOS AL PROVEEDOR ----------
        $this->pagosProveedor($os['pagos'] ?? [], $simbolo);

        // ---------- 10) NEGOCIADOR / FECHA / PLAZOS ----------
        $this->separador(0.5, 1.5);
        $this->fila('Negociador:',     self::s($os['negociador'] ?? ''), true);
        $this->fila('Fecha:',          self::s($os['fecha_emision'] ?? ''), true);
        $this->fila('Plazos de Pago:', self::s($os['plazo_pago'] ?? ''));

        // ---------- 11) FIRMAS ----------
        $izq = self::s($emisor['razon_social'] ?? '');
        $der = self::s($prov['razon_social'] ?? '');
        $wl  = 33;
        $x1  = $l + 1;
        $x2  = $l + $w - 1 - $wl;
        $y   = $this->GetY() + 14;

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->Line($x1, $y, $x1 + $wl, $y);
        $this->Line($x2, $y, $x2 + $wl, $y);

        $this->SetFont(self::FUENTE, 'B', 6.5);
        $this->SetXY($x1, $y + 0.8);
        $this->Cell($wl, 3, 'EMISOR', 0, 0, 'C');
        $this->SetXY($x2, $y + 0.8);
        $this->Cell($wl, 3, 'PROVEEDOR', 0, 0, 'C');

        $this->SetFont(self::FUENTE, '', 6);
        $hNom = max($this->getStringHeight($wl, $izq), $this->getStringHeight($wl, $der));
        $this->MultiCell($wl, $hNom, $izq, 0, 'C', false, 0, $x1, $y + 4);
        $this->MultiCell($wl, $hNom, $der, 0, 'C', false, 0, $x2, $y + 4);

        return $y + 4 + $hNom + 3;
    }
}

// =============================================
// DATOS: $this->data['orden_servicio']
// =============================================

$os = (isset($this) && isset($this->data['orden_servicio'])) ? $this->data['orden_servicio'] : null;

if (empty($os) || !is_array($os)) {
    http_response_code(404);
    die('No se encontraron datos para generar el ticket de la orden de servicio.');
}

date_default_timezone_set('America/Lima');

// Pasada 1: se dibuja en una página muy alta solo para medir cuánto ocupa el contenido
$medidor = new OrdenServicioTicketPDF(3000);
$medidor->AddPage();
$altoTicket = ceil($medidor->dibujar($os));
unset($medidor);

// Pasada 2: página con el alto exacto
$pdf = new OrdenServicioTicketPDF($altoTicket);
$pdf->SetCreator('Sistema de Transporte');
$pdf->SetAuthor(OrdenServicioTicketPDF::s($os['emisor']['razon_social'] ?? ''));
$pdf->SetTitle('Orden de Servicio ' . OrdenServicioTicketPDF::s($os['numero'] ?? ''));
$pdf->AddPage();
$pdf->dibujar($os);

$numero = OrdenServicioTicketPDF::s($os['numero'] ?? '');
$nombreArchivo = 'Ticket_Orden_Servicio_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $numero !== '' ? $numero : 'sin_numero') . '.pdf';

if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output($nombreArchivo, 'I'); // I = ver en el navegador | D = forzar descarga
exit;
