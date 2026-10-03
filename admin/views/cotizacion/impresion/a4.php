<?php

$es_inclusion = isset($es_interno) && $es_interno === true;

if (!$es_inclusion) {

    ini_set('memory_limit', '2048M');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('America/Lima');

    require_once('public/plugins/print/num_letras.php');
    require_once('public/plugins/tc/tcpdf.php');

    define("TEXT_SIZE_TITULO", 16);
    define("TEXT_SIZE_NORMAL", 9);
    define("TEXT_SIZE_REDUCIDO_COT", 8);
    define("COLOR_AZUL_R", 21);
    define("COLOR_AZUL_G", 40);
    define("COLOR_AZUL_B", 82);
    define("COLOR_NARANJA_R", 243);
    define("COLOR_NARANJA_G", 146);
    define("COLOR_NARANJA_B", 0);
    define("COLOR_ROJO_R", 178);
    define("COLOR_ROJO_G", 34);
    define("COLOR_ROJO_B", 34);

    class TCPDF_CellFit extends TCPDF
    {
        protected $widths;
        protected $aligns;
        protected $cMargin = 1;

        public function __construct()
        {
            // A4, orientación vertical, unidades en mm
            parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
            $this->SetMargins(10, 10, 10);
            $this->SetAutoPageBreak(true, 10);
        }

        function AutoPrint($dialog = false)
        {
            $param = ($dialog ? 'true' : 'false');
            $this->IncludeJS("print($param);");
        }

        function SetWidths($w)
        {
            $this->widths = $w;
        }

        function SetAligns($a)
        {
            $this->aligns = $a;
        }

        // Fila de tabla con altura automática según el contenido más largo
        function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $fillColumns = [], $fillColor = null)
        {
            $nb = 0;
            for ($i = 0; $i < count($data); $i++) {
                $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
            }

            $h = $lineHeight * $nb;

            if ($this->GetY() + $h > ($this->getPageHeight() - $this->bMargin)) {
                $this->AddPage($this->CurOrientation);
            }

            $x = $this->GetX();
            $y = $this->GetY();

            for ($i = 0; $i < count($data); $i++) {
                $w = $this->widths[$i];
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : $align;
                $xBefore = $this->GetX();
                $yBefore = $this->GetY();

                if (in_array($i, $boldColumns))
                    $this->SetFont('', 'B');

                $fill = in_array($i, $fillColumns);
                if ($fill && $fillColor) {
                    $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                }

                $this->MultiCell($w, $h, $data[$i], 0, $a, $fill, 0, '', '', true, 0, false, true, $h, 'M');

                if (in_array($i, $boldColumns))
                    $this->SetFont('', '');

                $this->SetXY($xBefore + $w, $yBefore);
            }

            $this->SetY($y + $h);
        }

        function NbLines($w, $txt)
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
                if ($c == ' ')
                    $sep = $i;

                $l += $this->GetStringWidth($c);
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j)
                            $i++;
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

        // Barra de título con fondo de color (ej. "DATOS CLIENTE:")
        function BarraTitulo($texto, $w = 190, $h = 6, $r = COLOR_AZUL_R, $g = COLOR_AZUL_G, $b = COLOR_AZUL_B)
        {
            $this->SetFillColor($r, $g, $b);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('helvetica', 'B', 9);
            $this->Cell($w, $h, '  ' . $texto, 0, 1, 'L', true);
            $this->SetTextColor(0, 0, 0);
        }
    }

    $data = $this->data;
    $pdf = new TCPDF_CellFit();
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    generarCotizacion($pdf, $data);

    $pdf->AutoPrint(false);

    ob_clean();
    $pdf->Output('cotizacion.pdf', 'I');
    exit;
}

// =============================================
// FUNCIÓN PRINCIPAL (REUTILIZABLE)
// =============================================
function generarCotizacion($pdf, $data)
{
    if (!defined('COLOR_AZUL_R')) {
        define('COLOR_AZUL_R', 21);
        define('COLOR_AZUL_G', 40);
        define('COLOR_AZUL_B', 82);
        define('COLOR_NARANJA_R', 243);
        define('COLOR_NARANJA_G', 146);
        define('COLOR_NARANJA_B', 0);
    }
    if (!defined('COLOR_ROJO_R')) {
        define('COLOR_ROJO_R', 178);
        define('COLOR_ROJO_G', 34);
        define('COLOR_ROJO_B', 34);
    }

    $emp = $data["HEADER_EMPRESA"];
    $cot = $data["COTIZACION"];
    $cli = $data["CLIENTE"];
    $items = $data["productos"];

    // Detracción: viene calculada del back, aquí solo se muestra
    $det = isset($data["detraccion"]) ? $data["detraccion"] : null;
    $aplica_detraccion = ((int) ($cot["afecta_detraccion"] ?? 0) === 1) && $det;

    // =========================================================
    // BLOQUE 1: LOGO + DATOS EMPRESA + CAJA COTIZACIÓN
    // =========================================================
    $y_inicio = $pdf->GetY();

    if (!empty($emp["logo"]) && file_exists($emp["logo"])) {

        // Evitar warning por imágenes defectuosas
        set_error_handler(function ($errno, $errstr) {
            if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
                return true;
            }
            return false;
        });

        // Calcular proporción para mantener la imagen dentro de 55x20 mm
        list($imgWidth, $imgHeight) = getimagesize($emp["logo"]);
        $scale = min(55 / $imgWidth, 20 / $imgHeight);
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        $pdf->Image(
            $emp["logo"],
            10,
            $y_inicio,
            $finalWidth,
            $finalHeight,
            '',
            '',
            '',
            false,
            300
        );

        restore_error_handler();
    } else {
        // Placeholder si todavía no hay logo configurado
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Rect(10, $y_inicio, 55, 20);
        $pdf->SetXY(10, $y_inicio + 8);
        $pdf->Cell(55, 4, 'Logo', 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    // Datos de la empresa debajo del logo
    $pdf->SetXY(10, $y_inicio + 21);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(90, 4, $emp['razon_social'], 0, 'L');
    $pdf->SetX(10);
    $pdf->Cell(90, 4, 'RUC: ' . $emp['ruc'], 0, 1, 'L');
    $pdf->SetX(10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(90, 3.5, $emp['direccion'], 0, 'L');
    $pdf->SetX(10);
    $pdf->Cell(90, 3.5, $emp['ciudad'], 0, 1, 'L');
    $pdf->SetX(10);
    $pdf->Cell(90, 3.5, 'Cel: ' . $emp['celular'], 0, 1, 'L');
    $pdf->SetX(10);
    $pdf->SetTextColor(21, 40, 82);
    $pdf->MultiCell(90, 3.5, $emp['correo'], 0, 'L');
    $pdf->SetTextColor(0, 0, 0);

    // Caja "COTIZACIÓN" (derecha)
    $x_caja = 120;
    $w_caja = 80;
    $pdf->SetXY($x_caja, $y_inicio);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(21, 40, 82);
    $pdf->Cell($w_caja, 7, 'COTIZACIÓN', 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);

    $filas_caja = [
        ['FECHA', date("d/m/Y", strtotime($cot['fecha']))],
        ['N°', $cot['numero']],
        ['C. PAGO', strtoupper($cot['condicion_pago'])],
    ];

    if (!empty($cot["numero_comprobante"])) {
        $filas_caja[] = ['COMP.', $cot['numero_comprobante']];
    }

    $filas_caja[] = ['DOC.', $cli['tipo_documento'] . ' ' . $cli['num_docu']];
    $filas_caja[] = ['VALIDO HASTA', date("d/m/Y", strtotime($cot['fecha_vencimiento']))];

    if ($aplica_detraccion) {
        $filas_caja[] = ['SUJETO A DETRACCIÓN', 'SI'];
    }

    $pdf->SetX($x_caja);
    foreach ($filas_caja as $fila) {
        $x0 = $pdf->GetX();
        $y0 = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(35, 5, $fila[0], 1, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(45, 5, $fila[1], 1, 1, 'C');
        $pdf->SetXY($x0, $y0 + 5);
    }

    // Bajar a debajo del bloque más alto (logo+datos vs caja)
    $pdf->SetY(max($pdf->GetY(), $y_inicio + 42));
    $pdf->Ln(3);
    // =========================================================
    // BLOQUE 2: DATOS CLIENTE
    // =========================================================
    $pdf->BarraTitulo('DATOS CLIENTE:', 190, 6);
    $pdf->Ln(1);

    $filas_cliente = [
        ['Cliente:', $cli['nombre_cliente'] ?? ''],
        [($cli['tipo_documento'] ?? 'DOC') . ':', $cli['num_docu'] ?? ''],
        ['Dirección:', $cli['direccion'] ?? ''],
        ['Ubicación:', $cli['ciudad'] ?? ''],
        ['Celular:', $cli['celular'] ?? ''],
        ['Correo:', $cli['email'] ?? ''],
    ];

    foreach ($filas_cliente as $fila) {

        if (empty(trim((string) $fila[1]))) {
            continue;
        }

        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(35, 5, $fila[0], 0, 0, 'L');

        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->MultiCell(155, 5, $fila[1], 0, 'L', 0, 1);
    }

    $pdf->Ln(2);


    // =========================================================
    // BLOQUE 2.5: ORIGEN Y DESTINO
    // =========================================================
    if (!empty($cot['nombre_origen']) || !empty($cot['nombre_destino'])) {

        $pdf->BarraTitulo('ORIGEN Y DESTINO:', 190, 6);
        $pdf->Ln(1);

        if (!empty($cot['nombre_origen'])) {
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->Cell(35, 5, 'Origen:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 8.5);
            $pdf->MultiCell(
                155,
                5,
                $cot['nombre_origen'],
                0,
                'L',
                0,
                1
            );
        }

        if (!empty($cot['nombre_destino'])) {
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->Cell(35, 5, 'Destino:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 8.5);
            $pdf->MultiCell(
                155,
                5,
                $cot['nombre_destino'],
                0,
                'L',
                0,
                1
            );
        }

        // Direcciones específicas, solo si fueron registradas
        if (!empty($cot['direccion_origen'])) {
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->Cell(35, 5, 'Dir. origen:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 8.5);
            $pdf->MultiCell(
                155,
                5,
                $cot['direccion_origen'],
                0,
                'L',
                0,
                1
            );
        }

        if (!empty($cot['direccion_destino'])) {
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->Cell(35, 5, 'Dir. destino:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 8.5);
            $pdf->MultiCell(
                155,
                5,
                $cot['direccion_destino'],
                0,
                'L',
                0,
                1
            );
        }

        $pdf->Ln(2);
    }
    // =========================================================
    // BLOQUE 3: TABLA DE ITEMS
    // =========================================================
    $anchos = [80, 30, 25, 20, 35];
    $pdf->SetWidths($anchos);
    $pdf->SetAligns(['L', 'C', 'R', 'C', 'R']);

    $pdf->SetFillColor(21, 40, 82);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->Row(['DESCRIPCIÓN', 'TIP. UNIDAD', 'P. UNITARIO', 'CANT. VJS', 'TOTAL'], 5, 'C', [], [0, 1, 2, 3, 4], [21, 40, 82]);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFont('helvetica', '', 8);
    $filas_pintadas = 0;

    foreach ($items as $item) {
        $total_item = $item['p_unitario'] * $item['cant_vjs'];
        $tip_unidad = $item['tip_unidad'] ?? '';

        $fill = ($filas_pintadas % 2 == 1);
        if ($fill) {
            $pdf->SetFillColor(240, 240, 240);
        }

        $pdf->Row(
            [
                $item['descripcion'],
                $tip_unidad,
                $cot['simbolo_moneda'] . ' ' .  number_format($item['p_unitario'], 2),
                number_format($item['cant_vjs'], 2),
                $cot['simbolo_moneda'] . ' ' . number_format($total_item, 2),
            ],
            5,
            'L',
            [],
            $fill ? [0, 1, 2, 3, 4] : [],
            [240, 240, 240]
        );
        $filas_pintadas++;
    }

    // Filas vacías para rellenar visualmente la tabla (opcional, estilo del modelo)
    $filas_vacias_min = 3;
    for ($i = $filas_pintadas; $i < $filas_pintadas + $filas_vacias_min; $i++) {
        $fill = ($i % 2 == 1);
        if ($fill)
            $pdf->SetFillColor(240, 240, 240);
        $pdf->Row(['', '', '', '', ''], 5, 'L', [], $fill ? [0, 1, 2, 3, 4] : [], [240, 240, 240]);
    }

    $pdf->Ln(2);

    // =========================================================
    // BLOQUE 4: DATOS BANCARIOS (izq) + TOTALES (der)
    // =========================================================
    $y_bloque4 = $pdf->GetY();

    // --- Datos bancarios (columna izquierda) ---
    $pdf->SetXY(10, $y_bloque4);
    foreach ($emp['cuentas'] as $cuenta) {
        $pdf->SetX(10);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $linea = $cuenta['banco'] . ': ' . ($cuenta['cuenta'] ?? '');
        $pdf->MultiCell(110, 4.5, $linea, 0, 'L', 0, 1);
        if (!empty($cuenta['cci'])) {
            $pdf->SetX(10);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(110, 4.5, 'CCI: ' . $cuenta['cci'], 0, 'L', 0, 1);
        }
    }
    // Guardamos dónde terminó la columna de bancos (izquierda)
    $y_bancos_fin = $pdf->GetY();

    // --- Totales (columna derecha) ---
    $totales_filas = [];

    $subtotal = (float) ($cot["subtotal"] ?? 0);
    $igv = (float) ($cot["igv"] ?? 0);
    $porcentaje_igv = (float) ($cot["porcentaje_igv"] ?? 18);
    $total = (float) ($cot["total"] ?? 0);

    // Valor de venta
    $totales_filas[] = [
        'VALOR DE VENTA',
        $subtotal,
        false
    ];

    // IGV
    $totales_filas[] = [
        'IGV (' . number_format($porcentaje_igv, 0) . '%)',
        $igv,
        false
    ];

    // Total
    $totales_filas[] = [
        'TOTAL',
        $total,
        true
    ];

    // Detracción
    if ($aplica_detraccion) {

        $totales_filas[] = [
            'DETRACCIÓN (' . number_format((float) $det['porcentaje'], 2) . '%)',
            (float) $det['monto_detraccion'],
            false
        ];

        $totales_filas[] = [
            'NETO A PAGAR',
            $total - (float) $det['monto_detraccion'],
            true
        ];
    }

    $x_tot = 130;
    $y_tot = $y_bloque4;
    foreach ($totales_filas as $fila) {
        $pdf->SetXY($x_tot, $y_tot);
        if ($fila[2]) {
            $pdf->SetFillColor(21, 40, 82);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 9);
        } else {
            $pdf->SetFont('helvetica', '', 8.5);
        }
        $pdf->Cell(40, 5, $fila[0], 0, 0, 'L', $fila[2]);
        $pdf->Cell(30, 5, $cot['simbolo_moneda'] . ' ' . number_format($fila[1], 2), 0, 1, 'R', $fila[2]);
        $pdf->SetTextColor(0, 0, 0);
        $y_tot += 5;
    }

    // Monto en letras, debajo de la columna de totales
    $pdf->SetXY($x_tot, $y_tot + 1);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->MultiCell(70, 3.5, 'SON: ' . numtoletras(number_format($cot["total"], 2, '.', ''), $cot['codigo_moneda']), 0, 'L');
    $y_totales_fin = $pdf->GetY(); // Y absoluta donde terminó la columna derecha

    // Bajar debajo del bloque más alto (columna bancos vs columna totales+letras)
    $pdf->SetY(max($y_bancos_fin, $y_totales_fin) + 2);

    // =========================================================
    // BLOQUE 4.5: DATOS DE LA DETRACCIÓN
    // =========================================================
    if ($aplica_detraccion) {
        $pdf->BarraTitulo('DATOS DE LA DETRACCIÓN', 190, 6, COLOR_ROJO_R, COLOR_ROJO_G, COLOR_ROJO_B);
        $pdf->Ln(1);

        // Buscar la cuenta del Banco de la Nación entre las cuentas de la empresa
        $cuenta_nacion = '';
        foreach ($emp['cuentas'] as $cuenta) {
            if (stripos($cuenta['banco'], 'naci') !== false) {
                $cuenta_nacion = $cuenta['cuenta'];
                break;
            }
        }

        $filas_detraccion = [
            ['N° Cuenta (Bco. Nación):', $cuenta_nacion],
            ['Monto Operación:', $cot['simbolo_moneda'] . ' ' . number_format((float) $det['monto_operacion'], 2)],
            ['Base Detracción:', $cot['simbolo_moneda'] . ' ' . number_format((float) $det['base_detraccion'], 2)],
            ['Porcentaje:', number_format((float) $det['porcentaje'], 2) . '%'],
            ['Monto Detracción:', $cot['simbolo_moneda'] . ' ' . number_format((float) $det['monto_detraccion'], 2)],
            ['Origen:', $det['origen_detraccion'] ?? ''],
            ['Destino:', $det['destino_detraccion'] ?? ''],
        ];

        if (!empty($det['motivo'])) {
            $filas_detraccion[] = ['Motivo:', $det['motivo']];
        }

        foreach ($filas_detraccion as $fila) {
            if (empty($fila[1]))
                continue;
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->Cell(45, 5, $fila[0], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 8.5);
            $pdf->MultiCell(145, 5, $fila[1], 0, 'L', 0, 1);
        }

        $pdf->Ln(2);
    }

    // =========================================================
    // BLOQUE 5: TÉRMINOS Y CONDICIONES + FIRMA
    // =========================================================
    $pdf->BarraTitulo('TERMINOS Y CONDICIONES', 110, 6);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetX(10);
    $html = $data['terminos_condiciones'];
    $pdf->writeHTML($html, true, false, true, false, '');

    if (!empty($cot["obs"])) {
        $pdf->SetX(10);
        $pdf->SetFont('helvetica', 'BI', 7.5);
        $pdf->MultiCell(110, 3.5, 'OBSERVACIONES: ' . $cot["obs"], 0, 'L', 0, 1);
    }

    // Firma
    $pdf->Ln(8);
    $pdf->SetX(10);
    $pdf->Cell(60, 0, '', 'T');
    $pdf->Ln(4);
    $pdf->SetX(10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(60, 4, 'V°B° cliente', 0, 1, 'C');

    $pdf->Ln(4);

    // =========================================================
    // BLOQUE 6: FOOTER / DESPEDIDA
    // =========================================================
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->MultiCell(190, 4, 'Si usted tiene alguna pregunta sobre esta cotización, por favor, póngase en contacto con nosotros', 0, 'C');
    $pdf->MultiCell(190, 4, 'Cel: ' . $emp['celular'] . '   Email: ' . $emp['correo'], 0, 'C');
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'BI', 9);
    $pdf->MultiCell(190, 4, 'Gracias por hacer negocios con nosotros :)', 0, 'C');
}

if ($es_inclusion) {
    generarCotizacion($pdf, $this->data);
}
