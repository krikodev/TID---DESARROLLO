<?php

$es_inclusion = isset($es_interno) && $es_interno === true;

if (!$es_inclusion) {

    ini_set('memory_limit', '2048M');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('America/Lima');

    require_once('public/plugins/print/num_letras.php');
    require_once('public/plugins/tc/tcpdf.php');

    define("TEXT_SIZE_REDUCIDO_TCOT", 8);

    class TCPDF_CellFit extends TCPDF
    {
        protected $widths;
        protected $aligns;
        protected $cMargin = 1;

        public function __construct()
        {
            // Ticket térmico: 72.1mm de ancho, alto variable (297 como base, con AutoPageBreak)
            parent::__construct('P', 'mm', array(72.1, 297), true, 'UTF-8', false);
            $this->SetMargins(1.5, 2, 1.5);
            $this->SetAutoPageBreak(true, 2);
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

        function Row($data, $lineHeight = 4, $align = 'L', $boldColumns = [])
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

                $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false, 0, '', '', true, 0, false, true, $lineHeight, 'M');

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

        // Línea punteada/sólida de separación, ancho completo del ticket
        function LineaSeparadora($alto = 1.5)
        {
            $this->Ln($alto);
            $this->SetLineWidth(0.15);
            $this->Cell(69, 0, '', 'T');
            $this->Ln($alto);
        }
    }

    $data = $this->data;

    $pdf = new TCPDF_CellFit();
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    generarTicketCotizacion($pdf, $data);

    $pdf->AutoPrint(false);

    ob_clean();
    $pdf->Output('cotizacion_ticket.pdf', 'I');
    exit;
}

// =============================================
// FUNCIÓN PRINCIPAL (REUTILIZABLE)
// =============================================
function generarTicketCotizacion($pdf, $data)
{
    if (!defined('TEXT_SIZE_REDUCIDO_TCOT')) {
        define('TEXT_SIZE_REDUCIDO_TCOT', 8);
    }

    $emp = $data["HEADER_EMPRESA"];
    $cot = $data["COTIZACION"];
    $cli = $data["CLIENTE"];
    $items = $data["productos"];

    // Detracción: viene calculada del back, aquí solo se muestra
    $det = isset($data["detraccion"]) ? $data["detraccion"] : null;
    $aplica_detraccion = ((int) ($cot["afecta_detraccion"] ?? 0) === 1) && $det;

    if (!empty($emp["logo"]) && file_exists($emp["logo"]) && is_readable($emp["logo"])) {

        $bgWidth = 72.1 - 3;
        $bgX = 1.5;
        $bgY = 5;

        $pdf->SetFillColor(
            255,
            255,
            255
        );

        $pdf->Rect(
            $bgX,
            $bgY,
            $bgWidth,
            25,
            'F'
        );

        $imageInfo =
            @getimagesize(
                $emp["logo"]
            );

        if ($imageInfo === false) {

            $pdf->Ln(5);
        } else {

            $imgWidth =
                $imageInfo[0];

            $imgHeight =
                $imageInfo[1];

            $extension =
                strtolower(
                    pathinfo(
                        $emp["logo"],
                        PATHINFO_EXTENSION
                    )
                );

            $type =
                (
                    $extension === 'jpg' ||
                    $extension === 'jpeg'
                )
                ? 'JPG'
                : 'PNG';

            $scale =
                min(
                    35 / $imgWidth,
                    25 / $imgHeight
                );

            $finalWidth =
                $imgWidth * $scale;

            $finalHeight =
                $imgHeight * $scale;

            if ($finalWidth > $bgWidth) {

                $finalWidth =
                    $bgWidth;

                $finalHeight =
                    $finalWidth *
                    (
                        $imgHeight /
                        $imgWidth
                    );
            }

            $xLogo =
                (
                    $pdf->getPageWidth() -
                    $finalWidth
                ) / 2;

            $yLogo =
                $bgY +
                (
                    25 - $finalHeight
                ) / 2 -
                8;

            try {

                $pdf->Image(
                    $emp["logo"],
                    $xLogo,
                    $yLogo,
                    $finalWidth,
                    $finalHeight,
                    $type
                );

                $pdf->Ln(15);
            } catch (Exception $e) {

                $pdf->Ln(5);
            }
        }
    } else {

        $pdf->Ln(5);
    }

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(69, 3.5, $emp['razon_social'], 0, 'C');
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->Cell(69, 3, 'RUC: ' . $emp['ruc'], 0, 1, 'C');
    $pdf->MultiCell(69, 3, $emp['direccion'] . ' - ' . $emp['ciudad'], 0, 'C');
    $pdf->Cell(69, 3, 'Cel: ' . $emp['celular'], 0, 1, 'C');
    $pdf->MultiCell(69, 3, $emp['correo'], 0, 'C');

    $pdf->LineaSeparadora();

    // =========================================================
    // BLOQUE 2: TÍTULO COTIZACIÓN + DATOS DE CABECERA
    // =========================================================
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(69, 5, 'COTIZACIÓN', 0, 1, 'C');
    $pdf->Ln(0.5);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(20, 3.5, 'FECHA:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(49, 3.5, date("d/m/Y", strtotime($cot['fecha'])), 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(20, 3.5, 'N°:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(49, 3.5, $cot['numero'], 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 8);

    $pdf->Cell(
        20,
        3.5,
        'C. PAGO:',
        0,
        0,
        'L'
    );

    $pdf->SetFont('helvetica', '', 8);

    $pdf->Cell(
        49,
        3.5,
        strtoupper($cot["condicion_pago"]),
        0,
        1,
        'L'
    );

    if (!empty($cot["numero_comprobante"])) {

        $pdf->SetFont('helvetica', 'B', 8);

        $pdf->Cell(
            20,
            3.5,
            'COMP.:',
            0,
            0,
            'L'
        );

        $pdf->SetFont('helvetica', '', 8);

        $pdf->Cell(
            49,
            3.5,
            $cot["numero_comprobante"],
            0,
            1,
            'L'
        );
    }

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(20, 3.5, 'DOC.:', 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(
        49,
        3.5,
        $cli['tipo_documento'] . ' ' . $cli['num_docu'],
        0,
        1,
        'L'
    );

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(20, 3.5, 'VALIDO H.:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(49, 3.5, date("d/m/Y", strtotime($cot['fecha_vencimiento'])), 0, 1, 'L');

    if ($aplica_detraccion) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(178, 34, 34);
        $pdf->Cell(69, 3.5, '** SUJETO A DETRACCIÓN **', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->LineaSeparadora();
    // =========================================================
    // BLOQUE 3: DATOS CLIENTE
    // =========================================================
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->Cell(69, 3.5, 'DATOS CLIENTE:', 0, 1, 'L');
    $pdf->Ln(0.3);

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

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->Cell(19, 3, $fila[0], 0, 0, 'L');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->MultiCell(50, 3, $fila[1], 0, 'L', 0, 1);
    }


    // =========================================================
    // ORIGEN Y DESTINO
    // =========================================================
    if (
        !empty($cot['nombre_origen']) ||
        !empty($cot['nombre_destino'])
    ) {

        $pdf->Ln(0.5);

        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(69, 3.5, 'ORIGEN Y DESTINO:', 0, 1, 'L');
        $pdf->Ln(0.3);

        if (!empty($cot['nombre_origen'])) {

            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(19, 3, 'Origen:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->MultiCell(
                50,
                3,
                $cot['nombre_origen'],
                0,
                'L',
                0,
                1
            );
        }

        if (!empty($cot['nombre_destino'])) {

            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(19, 3, 'Destino:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->MultiCell(
                50,
                3,
                $cot['nombre_destino'],
                0,
                'L',
                0,
                1
            );
        }

        // Dirección específica de origen, si existe
        if (!empty($cot['direccion_origen'])) {

            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(19, 3, 'Dir. origen:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->MultiCell(
                50,
                3,
                $cot['direccion_origen'],
                0,
                'L',
                0,
                1
            );
        }

        // Dirección específica de destino, si existe
        if (!empty($cot['direccion_destino'])) {

            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(19, 3, 'Dir. destino:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->MultiCell(
                50,
                3,
                $cot['direccion_destino'],
                0,
                'L',
                0,
                1
            );
        }
    }

    $pdf->LineaSeparadora();

    // =========================================================
    // BLOQUE 4: TABLA DE ITEMS (formato apilado, no columnas)
    // =========================================================
    $pdf->SetFont('helvetica', 'B', 7.7);
    $pdf->Cell(29, 3.5, 'DESCRIPCION', 0, 0, 'L');
    $pdf->Cell(15, 3.5, 'P.UNIT', 0, 0, 'R');
    $pdf->Cell(10, 3.5, 'CANT', 0, 0, 'R');
    $pdf->Cell(15, 3.5, 'TOTAL', 0, 1, 'R');
    $pdf->LineaSeparadora(0.8);

    foreach ($items as $item) {

        $total_item = $item['p_unitario'] * $item['cant_vjs'];
        $tip_unidad = $item['tip_unidad'] ?? '';

        if (!empty($tip_unidad)) {
            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->MultiCell(
                69,
                3,
                $tip_unidad,
                0,
                'L',
                0,
                1
            );
        }

        $pdf->SetFont('helvetica', '', 7.3);
        $pdf->MultiCell(
            69,
            3,
            $item['descripcion'],
            0,
            'L',
            0,
            1
        );

        $pdf->SetFont('helvetica', '', 7.5);

        $pdf->Cell(29, 3, '', 0, 0, 'L');

        $pdf->Cell(
            15,
            3,
            $cot['simbolo_moneda'] . ' ' . number_format($item['p_unitario'], 2),
            0,
            0,
            'R'
        );

        $pdf->Cell(
            10,
            3,
            number_format($item['cant_vjs'], 2),
            0,
            0,
            'R'
        );

        $pdf->Cell(
            15,
            3,
            $cot['simbolo_moneda'] . ' ' . number_format($total_item, 2),
            0,
            1,
            'R'
        );

        $pdf->Ln(.8);
    }

    $pdf->LineaSeparadora();

    $totales_filas = [];

    // =========================================================
    // TOTALES DE LA COTIZACIÓN
    // =========================================================

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


    // =========================================================
    // DETRACCIÓN
    // =========================================================

    if ($aplica_detraccion) {

        $totales_filas[] = [
            'DETRACCIÓN (' .
                number_format((float) $det['porcentaje'], 2) .
                '%)',
            (float) $det['monto_detraccion'],
            false
        ];

        $totales_filas[] = [
            'NETO A PAGAR',
            $total - (float) $det['monto_detraccion'],
            true
        ];
    }

    foreach ($totales_filas as $fila) {
        $pdf->SetFont('helvetica', $fila[2] ? 'B' : '', $fila[2] ? 9 : 8);
        $pdf->Cell(40, 4, $fila[0], 0, 0, 'L');
        $pdf->Cell(29, 4, $cot['simbolo_moneda'] . ' ' . number_format($fila[1], 2), 0, 1, 'R');
    }

    $pdf->Ln(0.5);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(69, 3, 'SON: ' . substr(numtoletras(number_format($cot["total"], 2, '.', ''), $cot['codigo_moneda']), 0, 60), 0, 'L');

    $pdf->LineaSeparadora();

    // =========================================================
    // BLOQUE 5: DATOS DE LA DETRACCIÓN
    // =========================================================
    if ($aplica_detraccion) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(178, 34, 34);
        $pdf->Cell(69, 3.5, 'DATOS DE LA DETRACCIÓN:', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(0.3);

        // Buscar la cuenta del Banco de la Nación entre las cuentas de la empresa
        $cuenta_nacion = '';
        foreach ($emp['cuentas'] as $cuenta) {
            if (stripos($cuenta['banco'], 'naci') !== false) {
                $cuenta_nacion = $cuenta['cuenta'];
                break;
            }
        }

        $filas_detraccion = [
            ['Cta. Bco. Nación:', $cuenta_nacion],
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
            $pdf->SetFont('helvetica', 'B', 7.3);
            $pdf->Cell(24, 3, $fila[0], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 7.3);
            $pdf->MultiCell(45, 3, $fila[1], 0, 'L', 0, 1);
        }

        $pdf->LineaSeparadora();
    }

    // =========================================================
    // BLOQUE 6: DATOS BANCARIOS
    // =========================================================
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(69, 3.5, 'CUENTAS BANCARIAS:', 0, 1, 'L');
    $pdf->Ln(0.3);

    foreach ($emp['cuentas'] as $cuenta) {
        $pdf->SetFont('helvetica', 'B', 7.3);
        $linea = $cuenta['banco'] . ': ' . ($cuenta['cuenta'] ?? '');
        $pdf->MultiCell(69, 3, $linea, 0, 'L', 0, 1);
        if (!empty($cuenta['cci'])) {
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell(69, 3, 'CCI: ' . $cuenta['cci'], 0, 'L', 0, 1);
        }
    }

    $pdf->LineaSeparadora();

    // =========================================================
    // BLOQUE 7: TÉRMINOS Y CONDICIONES
    // =========================================================
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(69, 3.5, 'TERMINOS Y CONDICIONES:', 0, 1, 'L');
    $pdf->Ln(0.3);

    $pdf->SetFont('helvetica', '', 7);

    $html = $data['terminos_condiciones'];

    $pdf->writeHTML($html, true, false, true, false, '');

    if (!empty($cot["obs"])) {

        $pdf->Ln(.3);

        $pdf->SetFont(
            'helvetica',
            'BI',
            6.5
        );

        $pdf->MultiCell(
            69,
            3,
            'OBSERVACIONES: ' . $cot["obs"],
            0,
            'L',
            0,
            1
        );
    }

    $pdf->LineaSeparadora();

    // =========================================================
    // BLOQUE 8: FIRMA
    // =========================================================
    $pdf->Ln(6);
    $ancho_firma = 45;
    $inicio_x = (72.1 - $ancho_firma) / 2;
    $pdf->SetX($inicio_x);
    $pdf->Cell($ancho_firma, 0, '', 'T');
    $pdf->Ln(0.5);
    $pdf->SetX($inicio_x);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->Cell($ancho_firma, 3, 'V°B° cliente', 0, 1, 'C');

    $pdf->Ln(2);

    // =========================================================
    // BLOQUE 9: FOOTER / DESPEDIDA
    // =========================================================
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(69, 3, 'Si tiene alguna pregunta sobre esta cotización, contáctenos:', 0, 'C');
    $pdf->MultiCell(69, 3, 'Cel: ' . $emp['celular'] . ' - Email: ' . $emp['correo'], 0, 'C');
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', 'BI', 8);

    $pdf->MultiCell(
        69,
        3,
        'Gracias por confiar en nuestros servicios.',
        0,
        'C'
    );
}

if ($es_inclusion) {
    generarTicketCotizacion($pdf, $this->data);
}
