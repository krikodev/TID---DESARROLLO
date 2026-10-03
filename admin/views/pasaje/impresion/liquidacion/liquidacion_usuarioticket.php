<?php

// Usar la variable global
if (!isset($GLOBALS['liquidacion_data']) || empty($GLOBALS['liquidacion_data'])) {
    error_log("ERROR: No hay datos en la variable global");
    die("No hay datos para mostrar en la liquidación. Verifica los logs.");
}

$data = $GLOBALS['liquidacion_data'];
$ventas_por_vendedor = [];
$comisiones_por_vendedor = [];
$medios_pago_unicos = [];

// Obtener totales desde la nueva estructura
$totales = $data["TOTALES"];

// Iniciar buffer limpio
ob_start();
ini_set('memory_limit', '1024M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// Constantes para formato TICKET (80mm de ancho)
define("PAGE_WIDTH", 80);
define("TEXT_SIZE_TITULO", 10);
define("TEXT_SIZE_BODY", 8);
define("TEXT_SIZE_SUBTITLE", 9);
define("TEXT_SIZE_SMALL", 7);
define("MARGIN", 2);

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct($orientation = 'P', $unit = 'mm', $format = array(80, 300))
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->SetMargins(MARGIN, 2, MARGIN);
        $this->SetAutoPageBreak(true, 3);
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $lineHeight = 4, $align = 'L', $boldColumns = [], $drawBorders = false)
    {
        foreach ($data as $i => $value) {
            $data[$i] = (string) ($value ?? '');
        }

        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
        }

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];

            if (is_array($align) && isset($align[$i])) {
                $a = $align[$i];
            } else {
                $a = is_string($align) ? $align : 'L';
            }

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $this->MultiCell(
                $w,
                $h,
                $data[$i],
                $drawBorders ? 1 : 0,
                $a,
                false,
                1,
                $xBefore,
                $yBefore,
                true,
                0,
                false,
                true,
                $h,
                'M'
            );

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

            $this->SetXY($xBefore + $w, $yBefore);
        }
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
}

// Crear PDF
$pdf = new TCPDF_CellFit('P', 'mm', array(PAGE_WIDTH, 300));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// =========================
// CABECERA: LOGO
// =========================

$logo_a_usar = '';

// Obtener logo desde los datos de la empresa
if (
    isset($data["HEADER"]["HEADER_EMPRESA"]["logo"]) &&
    !empty($data["HEADER"]["HEADER_EMPRESA"]["logo"])
) {
    $logo_a_usar = $data["HEADER"]["HEADER_EMPRESA"]["logo"];
}

// Si RUC separado está activado, usar logo de encomienda si existe
if (
    isset($data["RUC_ENCOMIENDA"]) &&
    $data["RUC_ENCOMIENDA"] == 1 &&
    !empty($data["HEADER"]["HEADER_EMPRESA"]["logo_encomienda"])
) {
    $logo_a_usar = $data["HEADER"]["HEADER_EMPRESA"]["logo_encomienda"];
}

// Insertar logo
if (!empty($logo_a_usar) && file_exists($logo_a_usar)) {

    $maxWidth = 50;
    $maxHeight = 30;

    list($imgWidth, $imgHeight) = getimagesize($logo_a_usar);

    $ratio = min(
        $maxWidth / $imgWidth,
        $maxHeight / $imgHeight
    );

    $newWidth = $imgWidth * $ratio;
    $newHeight = $imgHeight * $ratio;

    $x = (PAGE_WIDTH - $newWidth) / 2;
    $y = 2;

    try {
        $pdf->Image($logo_a_usar, $x, $y, $newWidth, $newHeight);
    } catch (Exception $e) {
        error_log("Error al cargar el logo: " . $e->getMessage());
    }

    $pdf->SetY(20);
}

// ============= ENCABEZADO COMPACTO =============
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
$pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'LIQUIDACIÓN DE USUARIO', 0, 1, 'C');

$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 2, str_repeat('-', 48), 0, 1, 'C');
$pdf->Ln(1);

// ============= INFORMACIÓN BÁSICA =============
$ancho_col1 = 18;      // Ancho para etiquetas como "Vendedor:", "DNI:", etc.
$ancho_col2 = PAGE_WIDTH - (2 * MARGIN) - $ancho_col1;  // Ancho para valores

// Línea 1: Vendedor (usa MultiCell por posible texto largo)
$y_inicio = $pdf->GetY();

$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell($ancho_col1, 4, "Vendedor:", 0, 'L', false, 0, MARGIN, $y_inicio, true);

$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$vendedor_nombre = $data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . $data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"];
$pdf->MultiCell($ancho_col2, 4, $vendedor_nombre, 0, 'L', false, 1, MARGIN + $ancho_col1, $y_inicio, true);

$y_actual = $pdf->GetY();

// Línea 2: DNI y Sucursal (en una misma fila)
// DNI
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(12, 4, "DNI:", 0, 'L', false, 0, MARGIN, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$dni = $data["HEADER"]["HEADER_DETALLE"]["vendedor_num_docu"] ?? '';
$pdf->MultiCell(20, 4, $dni, 0, 'L', false, 0, MARGIN + 12, $y_actual, true);

// Sucursal - Etiqueta
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(15, 4, "Sucursal:", 0, 'L', false, 0, MARGIN + 12 + 18, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$sucursal = $data["HEADER"]["HEADER_DETALLE"]["terminal_origen"] ?? 'No especificada';
$ancho_restante = PAGE_WIDTH - (2 * MARGIN) - (12 + 20 + 14);
$pdf->MultiCell($ancho_restante, 4, $sucursal, 0, 'L', false, 1, MARGIN + 12 + 20 + 14, $y_actual, true);

$y_actual = $pdf->GetY();

// Línea 3: Fecha, Hora y Placa (en una misma fila)
// Fecha - Etiqueta
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(12, 4, "Fecha:", 0, 'L', false, 0, MARGIN, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$fecha_salida = $data["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"] ?? '';
$fecha_formateada = !empty($fecha_salida) ? date("d/m/Y", strtotime($fecha_salida)) : '';
$pdf->MultiCell(18, 4, $fecha_formateada, 0, 'L', false, 0, MARGIN + 12, $y_actual, true);

// Hora
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(10, 4, "Hora:", 0, 'L', false, 0, MARGIN + 12 + 18, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$hora_salida = $data["HEADER"]["HEADER_DETALLE"]["programacion_hora_salida"] ?? '';
$hora_formateada = !empty($hora_salida) ? date("H:i", strtotime($hora_salida)) : '';
$pdf->MultiCell(13, 4, $hora_formateada, 0, 'L', false, 0, MARGIN + 12 + 18 + 10, $y_actual, true);

// Placa 
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(12, 4, "Placa:", 0, 'L', false, 0, MARGIN + 12 + 15 + 10 + 15, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$placa = $data["HEADER"]["HEADER_DETALLE"]["vehiculo_placa"] ?? '';
$ancho_placa = PAGE_WIDTH - (2 * MARGIN) - (12 + 18 + 10 + 15 + 10);
$pdf->MultiCell(15, 4, $placa, 0, 'L', false, 1, MARGIN + 12 + 18 + 10 + 15 + 6, $y_actual, true);

$y_actual = $pdf->GetY();

// Línea 4: Origen y Destino (en una misma fila)
// Origen - Etiqueta
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(13, 4, "Origen:", 0, 'L', false, 0, MARGIN, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$origen = $data["HEADER"]["HEADER_DETALLE"]["terminal_origen"] ?? '';
$ancho_origen = (PAGE_WIDTH - (2 * MARGIN) - 12 - 14) / 2;
$pdf->MultiCell($ancho_origen, 4, $origen, 0, 'L', false, 0, MARGIN + 12, $y_actual, true);

// Destino - Etiqueta
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
$pdf->MultiCell(14, 4, "Destino:", 0, 'L', false, 0, MARGIN + 12 + $ancho_origen, $y_actual, true);
$pdf->SetFont('helvetica', '', TEXT_SIZE_BODY);
$destino = $data["HEADER"]["HEADER_DETALLE"]["terminal_destino"] ?? '';
$ancho_destino = PAGE_WIDTH - (2 * MARGIN) - (12 + $ancho_origen + 14);
$pdf->MultiCell($ancho_destino, 4, $destino, 0, 'L', false, 1, MARGIN + 12 + $ancho_origen + 14, $y_actual, true);

$pdf->Ln(0.5);
$pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 2, str_repeat('=', 48), 0, 1, 'C');
$pdf->Ln(0.5);

// ============= VENTAS DE PASAJES AGRUPADAS POR DESTINO =============
$total_num_ventas = 0;
$total_ventas = 0;
$total_precio_pasajes = 0;
$total_egresos_general = 0;  // Total de egresos individuales
$total_importe_neto_general = 0;  // Total de importe neto

if (isset($data["DETALLE"]) && is_array($data["DETALLE"])) {
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'VENTAS DE PASAJES', 0, 1, 'C');

    // Primero, agrupar las ventas por destino
    $ventas_por_destino = [];

    for ($i = 0; $i < count($data["DETALLE"]); $i++) {
        $medio_pago = $data["DETALLE"][$i]["medio_pago"];

        for ($x = 0; $x < count($data["DETALLE"][$i]["pisos"]); $x++) {
            $ventas_piso = $data["DETALLE"][$i]["pisos"][$x];

            foreach ($ventas_piso as $detalle) {
                if ($detalle["estado_asiento"] != "ANULADO") {
                    $destino = $detalle["terminal_destino"] ?? 'S/D';

                    if (!isset($ventas_por_destino[$destino])) {
                        $ventas_por_destino[$destino] = [];
                    }

                    $ventas_por_destino[$destino][] = $detalle;
                }
            }
        }
    }

    // Definir anchos de columnas (6 columnas)
    $ancho_serie = 16;
    $ancho_fecha = 15;
    $ancho_medio_pago = 15;
    $ancho_importe = 10;
    $ancho_egreso = 11;
    $ancho_imp_neto = 11;

    // Iterar por cada destino
    foreach ($ventas_por_destino as $destino => $ventas_destino) {
        $total_destino_importe = 0;
        $total_destino_egreso = 0;
        $total_destino_imp_neto = 0;

        // Título del destino
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 5, ' ' . strtoupper($destino), 0, 1, 'L', true);
        $pdf->SetFillColor(255, 255, 255);

        // Encabezados de la tabla (NUEVO ORDEN)
        $x_start = MARGIN;
        $y_start = $pdf->GetY();
        $line_height = 4;

        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

        // SERIE
        $pdf->MultiCell($ancho_serie, $line_height, 'SERIE', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_serie;

        // FECHA
        $pdf->MultiCell($ancho_fecha, $line_height, 'FECHA', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_fecha;

        // MEDIO PAGO
        $pdf->MultiCell($ancho_medio_pago, $line_height, 'M. PAGO', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_medio_pago;

        // IMPORTE
        $pdf->MultiCell($ancho_importe, $line_height, 'IMP.', 0, 'R', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_importe;

        // EGRESO
        $pdf->MultiCell($ancho_egreso, $line_height, 'EGRE.', 0, 'R', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_egreso;

        // IMP. NETO
        $pdf->MultiCell($ancho_imp_neto, $line_height, 'I.NETO', 0, 'R', false, 1, $x_start, $y_start, true);

        $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);

        // Separar ventas normales y pasajes pospuestos
        $ventas_normales = [];
        $ventas_pospuestas = [];
        $ventas_pospuestas_utilizados = [];
        foreach ($ventas_destino as $detalle) {
            $estado = strtoupper(trim($detalle["estado_asiento"] ?? ''));

            if ($estado === 'POSPUESTO US.') {
                $ventas_pospuestas_utilizados[] = $detalle;
            } elseif ($estado === 'POSPUESTO') {
                $ventas_pospuestas[] = $detalle;
            } else {
                $ventas_normales[] = $detalle;
            }
        }

        // MOSTRAR VENTAS NORMALES
        foreach ($ventas_normales as $detalle) {
            $precio_pasaje = floatval($detalle["precio_pasaje"] ?? 0);
            $importe_total = floatval($detalle["importe_total"] ?? $precio_pasaje);
            $monto_egreso = floatval($detalle["monto_egreso"] ?? 0);
            $precio_neto = floatval($detalle["precio_neto"] ?? ($importe_total - $monto_egreso));
            $vendedor_nombre = $detalle["vendedor"] ?? $data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . $data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"];

            $serie_completa = ($detalle["venta_serie"] ?? '') . '-' . ($detalle["venta_correlativo"] ?? '');
            $fecha = isset($detalle["fecha_emision"]) ? date('d/m/Y', strtotime($detalle["fecha_emision"])) : date('d/m/Y');

            // Obtener el medio de pago
            $medio_pago_text = '';
            for ($i = 0; $i < count($data["DETALLE"]); $i++) {
                if (isset($data["DETALLE"][$i]["pisos"])) {
                    foreach ($data["DETALLE"][$i]["pisos"] as $piso) {
                        foreach ($piso as $v) {
                            if ($v === $detalle) {
                                $medio_pago_text = $data["DETALLE"][$i]["medio_pago"];
                                break 3;
                            }
                        }
                    }
                }
            }
            $medio_pago_short = substr($medio_pago_text, 0, 50);
            $y_start_fila = $pdf->GetY();
            $x_start = MARGIN;

            // Calcular altura máxima necesaria
            $alturas = [];
            $alturas[] = $pdf->NbLines($ancho_serie, $serie_completa);
            $alturas[] = $pdf->NbLines($ancho_fecha, $fecha);
            $alturas[] = $pdf->NbLines($ancho_medio_pago, $medio_pago_short);
            $alturas[] = $pdf->NbLines($ancho_importe, number_format($importe_total, 2));
            $alturas[] = $pdf->NbLines($ancho_egreso, number_format($monto_egreso, 2));
            $alturas[] = $pdf->NbLines($ancho_imp_neto, number_format($precio_neto, 2));
            $max_lines = max($alturas);
            $cell_height = $max_lines * 3;

            if ($cell_height < 3.5) {
                $cell_height = 3.5;
            }

            // SERIE
            $pdf->MultiCell($ancho_serie, $cell_height, $serie_completa, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_serie;

            // FECHA
            $pdf->MultiCell($ancho_fecha, $cell_height, $fecha, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_fecha;

            // MEDIO PAGO
            $pdf->MultiCell($ancho_medio_pago, $cell_height, $medio_pago_short, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_medio_pago;

            // IMPORTE
            $pdf->MultiCell($ancho_importe, $cell_height, number_format($importe_total, 2), 0, 'R', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_importe;

            // EGRESO
            $pdf->MultiCell($ancho_egreso, $cell_height, number_format($monto_egreso, 2), 0, 'R', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_egreso;

            // IMP. NETO
            $pdf->MultiCell($ancho_imp_neto, $cell_height, number_format($precio_neto, 2), 0, 'R', false, 1, $x_start, $y_start_fila, true);

            $pdf->SetY(
                $y_start_fila + $cell_height
            );
            // Acumular totales por destino
            $total_destino_importe += $importe_total;
            $total_destino_egreso += $monto_egreso;
            $total_destino_imp_neto += $precio_neto;

            // Recolectar datos para resúmenes
            if (!isset($ventas_por_vendedor[$vendedor_nombre])) {
                $ventas_por_vendedor[$vendedor_nombre] = [
                    'num_ventas' => 0,
                    'total_precio_pasajes' => 0.00,
                    'total_importe' => 0.00,
                    'total_egresos' => 0.00,
                    'total_neto' => 0.00,
                    // 'metodos' => []
                    'metodos_pasajes' => [],
                    'metodos_encomiendas' => [],
                ];
            }

            if (!isset($ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text])) {
                $ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text] = 0.00;
            }

            $ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text] += $precio_pasaje;
            $ventas_por_vendedor[$vendedor_nombre]['num_ventas']++;
            $ventas_por_vendedor[$vendedor_nombre]['total_precio_pasajes'] += $precio_pasaje;
            $ventas_por_vendedor[$vendedor_nombre]['total_importe'] += $importe_total;
            $ventas_por_vendedor[$vendedor_nombre]['total_egresos'] += $monto_egreso;
            $ventas_por_vendedor[$vendedor_nombre]['total_neto'] += $precio_neto;

            $comision = isset($detalle["comision_vendedor"]) ? floatval($detalle["comision_vendedor"]) : 0;
            $porcentaje = isset($detalle["porcentaje_comision"]) ? floatval($detalle["porcentaje_comision"]) : 0;

            if (!isset($comisiones_por_vendedor[$vendedor_nombre])) {
                $comisiones_por_vendedor[$vendedor_nombre] = [
                    'pasajes' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => $porcentaje],
                    'encomiendas' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0]
                ];
            }

            $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['cantidad']++;
            $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['base'] += $precio_neto;
            $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['comision'] += $comision;
            $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['porcentaje'] = $porcentaje;

            $total_num_ventas++;
            $total_precio_pasajes += $precio_pasaje;
            $total_ventas += $importe_total;
            $total_egresos_general += $monto_egreso;
            $total_importe_neto_general += $precio_neto;
        }

        // MOSTRAR PASAJES POSPUESTOS
        $secciones_pospuestos = [
            [
                'titulo' => 'POSPUESTOS',
                'ventas' => $ventas_pospuestas
            ],
            [
                'titulo' => 'POSPUESTO UTILIZADO',
                'ventas' => $ventas_pospuestas_utilizados
            ]
        ];
        foreach ($secciones_pospuestos as $seccion) {
            if (empty($seccion['ventas'])) {
                continue;
            }

            $pdf->Ln(1);
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, $seccion['titulo'], 0, 1, 'C');
            $pdf->Ln(0.5);

            foreach ($seccion['ventas'] as $detalle) {
                $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
                $precio_pasaje = floatval($detalle["precio_pasaje"] ?? 0);
                $importe_total = floatval($detalle["importe_total"] ?? $precio_pasaje);
                $monto_egreso = floatval($detalle["monto_egreso"] ?? 0);
                $precio_neto = floatval($detalle["precio_neto"] ?? ($importe_total - $monto_egreso));
                $vendedor_nombre = $detalle["vendedor"] ?? $data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . $data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"];

                $serie_completa = ($detalle["venta_serie"] ?? '') . '-' . ($detalle["venta_correlativo"] ?? '');
                $fecha = isset($detalle["fecha_emision"]) ? date('d/m/Y', strtotime($detalle["fecha_emision"])) : date('d/m/Y');

                // Obtener el medio de pago
                $medio_pago_text = '';
                for ($i = 0; $i < count($data["DETALLE"]); $i++) {
                    if (isset($data["DETALLE"][$i]["pisos"])) {
                        foreach ($data["DETALLE"][$i]["pisos"] as $piso) {
                            foreach ($piso as $v) {
                                if ($v === $detalle) {
                                    $medio_pago_text = $data["DETALLE"][$i]["medio_pago"];
                                    break 3;
                                }
                            }
                        }
                    }
                }
                $medio_pago_short = substr($medio_pago_text, 0, 50);

                $y_start_fila = $pdf->GetY();
                $x_start = MARGIN;

                // Calcular altura máxima necesaria
                $alturas = [];
                $alturas[] = $pdf->NbLines($ancho_serie, $serie_completa);
                $alturas[] = $pdf->NbLines($ancho_fecha, $fecha);
                $alturas[] = $pdf->NbLines($ancho_medio_pago, $medio_pago_short);
                $alturas[] = $pdf->NbLines($ancho_importe, number_format($importe_total, 2));
                $alturas[] = $pdf->NbLines($ancho_egreso, number_format($monto_egreso, 2));
                $alturas[] = $pdf->NbLines($ancho_imp_neto, number_format($precio_neto, 2));
                $max_lines = max($alturas);
                $cell_height = $max_lines * 3;

                if ($cell_height < 3.5) {
                    $cell_height = 3.5;
                }

                // SERIE
                $pdf->MultiCell($ancho_serie, $cell_height, $serie_completa, 0, 'L', false, 0, $x_start, $y_start_fila, true);
                $x_start += $ancho_serie;

                // FECHA
                $pdf->MultiCell($ancho_fecha, $cell_height, $fecha, 0, 'L', false, 0, $x_start, $y_start_fila, true);
                $x_start += $ancho_fecha;

                // MEDIO PAGO
                $pdf->MultiCell($ancho_medio_pago, $cell_height, $medio_pago_short, 0, 'L', false, 0, $x_start, $y_start_fila, true);
                $x_start += $ancho_medio_pago;

                // IMPORTE
                $pdf->MultiCell($ancho_importe, $cell_height, number_format($importe_total, 2), 0, 'R', false, 0, $x_start, $y_start_fila, true);
                $x_start += $ancho_importe;

                // EGRESO
                $pdf->MultiCell($ancho_egreso, $cell_height, number_format($monto_egreso, 2), 0, 'R', false, 0, $x_start, $y_start_fila, true);
                $x_start += $ancho_egreso;

                // IMP. NETO
                $pdf->MultiCell($ancho_imp_neto, $cell_height, number_format($precio_neto, 2), 0, 'R', false, 1, $x_start, $y_start_fila, true);

                $pdf->SetY(
                    $y_start_fila + $cell_height
                );
                // Acumular totales por destino
                $total_destino_importe += $importe_total;
                $total_destino_egreso += $monto_egreso;
                $total_destino_imp_neto += $precio_neto;

                // Recolectar datos para resúmenes
                if (!isset($ventas_por_vendedor[$vendedor_nombre])) {
                    $ventas_por_vendedor[$vendedor_nombre] = [
                        'num_ventas' => 0,
                        'total_precio_pasajes' => 0.00,
                        'total_importe' => 0.00,
                        'total_egresos' => 0.00,
                        'total_neto' => 0.00,
                        // 'metodos' => []
                        'metodos_pasajes' => [],
                        'metodos_encomiendas' => [],
                    ];
                }

                if (!isset($ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text])) {
                    $ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text] = 0.00;
                }

                $ventas_por_vendedor[$vendedor_nombre]['metodos_pasajes'][$medio_pago_text] += $precio_pasaje;
                $ventas_por_vendedor[$vendedor_nombre]['num_ventas']++;
                $ventas_por_vendedor[$vendedor_nombre]['total_precio_pasajes'] += $precio_pasaje;
                $ventas_por_vendedor[$vendedor_nombre]['total_importe'] += $importe_total;
                $ventas_por_vendedor[$vendedor_nombre]['total_egresos'] += $monto_egreso;
                $ventas_por_vendedor[$vendedor_nombre]['total_neto'] += $precio_neto;

                $comision = isset($detalle["comision_vendedor"]) ? floatval($detalle["comision_vendedor"]) : 0;
                $porcentaje = isset($detalle["porcentaje_comision"]) ? floatval($detalle["porcentaje_comision"]) : 0;

                if (!isset($comisiones_por_vendedor[$vendedor_nombre])) {
                    $comisiones_por_vendedor[$vendedor_nombre] = [
                        'pasajes' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => $porcentaje],
                        'encomiendas' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0]
                    ];
                }

                $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['cantidad']++;
                $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['base'] += $precio_neto;
                $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['comision'] += $comision;
                $comisiones_por_vendedor[$vendedor_nombre]['pasajes']['porcentaje'] = $porcentaje;

                $total_num_ventas++;
                $total_precio_pasajes += $precio_pasaje;
                $total_ventas += $importe_total;
                $total_egresos_general += $monto_egreso;
                $total_importe_neto_general += $precio_neto;
            }
        }
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);

        // Subtotal por destino con todas las columnas
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

        $ancho_etiquetas = $ancho_serie + $ancho_fecha + $ancho_medio_pago;

        $pdf->Cell($ancho_etiquetas, 3, 'SUBTOTAL', 0, 0, 'L');
        $pdf->Cell($ancho_importe, 3, '', 0, 0, 'R');
        $pdf->Cell($ancho_egreso, 3, '', 0, 0, 'R');
        $pdf->Cell($ancho_imp_neto, 3, '', 0, 1, 'R');

        $pdf->Cell($ancho_etiquetas, 3, strtoupper($destino) . ':', 0, 0, 'L');
        $pdf->Cell($ancho_importe, 3, number_format($total_destino_importe, 2),  0, 0, 'R');
        $pdf->Cell($ancho_egreso, 3, number_format($total_destino_egreso, 2), 0, 0, 'R');
        $pdf->Cell($ancho_imp_neto, 3, number_format($total_destino_imp_neto, 2), 0, 1, 'R');
        $pdf->Ln(1);
    }

    // Línea separadora
    $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');

    // TOTAL GENERAL con todas las columnas
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

    $ancho_etiquetas = $ancho_serie + $ancho_fecha + $ancho_medio_pago;

    $pdf->Cell($ancho_etiquetas, 3, 'TOTAL GENERAL (' . $total_num_ventas . '):', 0, 0, 'L');
    $pdf->Cell($ancho_importe, 3, number_format($total_ventas, 2), 0, 0, 'R');
    $pdf->Cell($ancho_egreso, 3, number_format($total_egresos_general, 2), 0, 0, 'R');
    $pdf->Cell($ancho_imp_neto, 3, number_format($total_importe_neto_general, 2), 0, 1, 'R');

    $pdf->Ln(1);
}

// ============= ENCOMIENDAS AGRUPADAS POR DESTINO =============
if (!empty($data["ENCOMIENDAS"])) {
    // Primero, agrupar las encomiendas por destino
    $encomiendas_por_destino = [];

    foreach ($data["ENCOMIENDAS"] as $item) {
        $medio_pago = $item["medio_pago"];

        foreach ($item["encomiendas"] as $enc) {
            if ($enc["estado"] != "ANULADO") {
                // Obtener el destino (puede venir de la encomienda o usar el destino de la programación)
                $destino = $enc["terminal_destino"] ?? $data["HEADER"]["HEADER_DETALLE"]["terminal_destino"] ?? 'S/D';
                $fecha_emision = $enc["fecha_emision"] ?? $data["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"] ?? date('Y-m-d');

                if (!isset($encomiendas_por_destino[$destino])) {
                    $encomiendas_por_destino[$destino] = [];
                }

                $encomiendas_por_destino[$destino][] = [
                    'serie' => $enc["comprobante"] ?? '',
                    'fecha' => $fecha_emision,
                    'total' => floatval($enc["monto_total"] ?? 0),
                    'medio_pago' => $medio_pago,
                    'remitente' => $enc["remitente"] ?? '',
                    'destinatario' => $enc["destinatario"] ?? '',
                    'producto' => $enc["producto"] ?? '',
                    'vendedor' => $enc["vendedor"] ?? $data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . $data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"],
                    'comision_vendedor' => floatval($enc["comision_vendedor"] ?? 0)
                ];
            }
        }
    }

    // Definir anchos de columnas (4 columnas)
    $ancho_serie = 18;      // SERIE
    $ancho_fecha = 15;      // FECHA
    $ancho_medio_pago = 25;  // MÉTODO PAGO
    $ancho_total = 15;      // TOTAL

    // Verificar que la suma de anchos no exceda el ancho de página
    $ancho_total_columnas = $ancho_serie + $ancho_fecha + $ancho_medio_pago + $ancho_total;
    if ($ancho_total_columnas > (PAGE_WIDTH - (2 * MARGIN))) {
        // Ajustar proporcionalmente si es necesario
        $factor = (PAGE_WIDTH - (2 * MARGIN)) / $ancho_total_columnas;
        $ancho_serie = round($ancho_serie * $factor);
        $ancho_fecha = round($ancho_fecha * $factor);
        $ancho_medio_pago = round($ancho_medio_pago * $factor);
        $ancho_total = round($ancho_total * $factor);
    }

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'ENCOMIENDAS', 0, 1, 'C');
    $pdf->Ln(1);

    $total_general_encomiendas = 0;
    $total_num_encomiendas = 0;

    // Iterar por cada destino
    foreach ($encomiendas_por_destino as $destino => $encomiendas_destino) {
        $total_destino = 0;
        $cantidad_destino = 0;

        // Título del destino
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 5, ' ' . strtoupper($destino), 0, 1, 'L', true);
        $pdf->SetFillColor(255, 255, 255);

        // Encabezados de la tabla
        $x_start = MARGIN;
        $y_start = $pdf->GetY();
        $line_height = 4;

        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

        // SERIE
        $pdf->MultiCell($ancho_serie, $line_height, 'SERIE', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_serie;

        // FECHA
        $pdf->MultiCell($ancho_fecha, $line_height, 'FECHA', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_fecha;

        // MÉTODO PAGO
        $pdf->MultiCell($ancho_medio_pago, $line_height, 'MÉTODO PAGO', 0, 'L', false, 0, $x_start, $y_start, true);
        $x_start += $ancho_medio_pago;

        // TOTAL
        $pdf->MultiCell($ancho_total, $line_height, 'TOTAL', 0, 'R', false, 1, $x_start, $y_start, true);

        $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);

        // Mostrar las encomiendas de este destino
        foreach ($encomiendas_destino as $enc) {
            $serie = substr($enc['serie'], 0, 15);
            $fecha = date('d/m/Y', strtotime($enc['fecha']));
            $medio_pago = substr($enc['medio_pago'], 0, 20);
            $total = $enc['total'];
            $vendedor_nombre = $enc['vendedor'];

            $y_start_fila = $pdf->GetY();
            $x_start = MARGIN;

            // Calcular altura máxima necesaria para cada celda
            $alturas = [];
            $alturas[] = $pdf->NbLines($ancho_serie, $serie);
            $alturas[] = $pdf->NbLines($ancho_fecha, $fecha);
            $alturas[] = $pdf->NbLines($ancho_medio_pago, $medio_pago);
            $alturas[] = $pdf->NbLines($ancho_total, number_format($total, 2));
            $max_lines = max($alturas);
            $cell_height = max($max_lines * 3, 3.5);

            // SERIE
            $pdf->MultiCell($ancho_serie, $cell_height, $serie, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_serie;

            // FECHA
            $pdf->MultiCell($ancho_fecha, $cell_height, $fecha, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_fecha;

            // MÉTODO PAGO
            $pdf->MultiCell($ancho_medio_pago, $cell_height, $medio_pago, 0, 'L', false, 0, $x_start, $y_start_fila, true);
            $x_start += $ancho_medio_pago;

            // TOTAL
            $pdf->MultiCell($ancho_total, $cell_height, number_format($total, 2), 0, 'R', false, 1, $x_start, $y_start_fila, true);

            // Acumular totales por destino
            $total_destino += $total;
            $cantidad_destino++;

            // Recolectar datos para resúmenes (comisiones, etc.)
            if (!isset($ventas_por_vendedor[$vendedor_nombre])) {
                $ventas_por_vendedor[$vendedor_nombre] = [
                    'num_ventas' => 0,
                    'total_precio_pasajes' => 0.00,
                    'total_importe' => 0.00,
                    'total_egresos' => 0.00,
                    'total_neto' => 0.00,
                    // 'metodos' => []
                    'metodos_pasajes' => [],
                    'metodos_encomiendas' => [],
                ];
            }

            if (!isset($ventas_por_vendedor[$vendedor_nombre]['metodos_encomiendas'][$medio_pago])) {
                $ventas_por_vendedor[$vendedor_nombre]['metodos_encomiendas'][$medio_pago] = 0.00;
            }

            $ventas_por_vendedor[$vendedor_nombre]['metodos_encomiendas'][$medio_pago] += $total;
            $ventas_por_vendedor[$vendedor_nombre]['num_ventas']++;
            $ventas_por_vendedor[$vendedor_nombre]['total_importe'] += $total;

            // Acumular comisiones
            if (!isset($comisiones_por_vendedor[$vendedor_nombre])) {
                $comisiones_por_vendedor[$vendedor_nombre] = [
                    'pasajes' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0],
                    'encomiendas' => ['cantidad' => 0, 'base' => 0.00, 'comision' => 0.00, 'porcentaje' => 0]
                ];
            }

            $comision_enc = $enc['comision_vendedor'];
            $porcentaje_enc = ($total > 0 && $comision_enc > 0) ? ($comision_enc * 100) / $total : 0;

            $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['cantidad']++;
            $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['base'] += $total;
            $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['comision'] += $comision_enc;
            $comisiones_por_vendedor[$vendedor_nombre]['encomiendas']['porcentaje'] = $porcentaje_enc;
        }

        // Subtotal por destino
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

        $ancho_subtotal = $ancho_serie + $ancho_fecha + $ancho_medio_pago;
        $pdf->Cell($ancho_serie + $ancho_fecha + $ancho_medio_pago, 3, 'SUBTOTAL ' . strtoupper($destino) . ' (' . $cantidad_destino . '):', 0, 0, 'L');
        $pdf->Cell($ancho_total, 3, number_format($total_destino, 2), 0, 1, 'R');
        $pdf->Ln(1);

        $total_general_encomiendas += $total_destino;
        $total_num_encomiendas += $cantidad_destino;
    }

    // Línea separadora
    $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');

    // TOTAL GENERAL
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
    $pdf->Cell($ancho_serie + $ancho_fecha + $ancho_medio_pago, 3, 'TOTAL ENCOMIENDAS (' . $total_num_encomiendas . '):', 0, 0, 'L');
    $pdf->Cell($ancho_total, 3, number_format($total_general_encomiendas, 2), 0, 1, 'R');

    $pdf->Ln(2);

    // Acumular a los totales generales
    $total_ventas += $total_general_encomiendas;
    $total_num_ventas += $total_num_encomiendas;
}

// ============= TABLA DE RESUMEN DE VENTAS POR VENDEDOR =============
if (!empty($ventas_por_vendedor)) {
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SUBTITLE);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'RESUMEN DE VENTAS POR VENDEDOR', 0, 1, 'C');
    $pdf->Ln(1);

    $total_neto_pasajes = 0;
    $total_encomiendas_general = 0;
    $total_ventas_general = 0;

    $total_por_metodo_pago = 0;

    // Variables para almacenar totales por tipo de venta
    $totales_pasajes_por_vendedor = [];
    $totales_encomiendas_por_vendedor = [];

    foreach ($ventas_por_vendedor as $vendedor => $datos) {

        // ============= VENTAS DE PASAJES =============
        $tiene_pasajes = isset($datos['total_neto']) && $datos['total_neto'] > 0;
        if ($tiene_pasajes) {
            // Tamaño normal para el texto "VENDEDOR:"
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
            $pdf->Cell(16, 4, 'VENDEDOR:', 0, 0, 'L');
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            $pdf->Cell(60, 4, strtoupper($vendedor), 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
            $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 3, 'VENTAS DE PASAJES - DETALLE POR MÉTODO DE PAGO:', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
            $ancho_metodo = 40;
            $ancho_monto = PAGE_WIDTH - (2 * MARGIN) - $ancho_metodo;

            if (!empty($datos['metodos_pasajes'])) {
                foreach ($datos['metodos_pasajes'] as $medio => $monto) {
                    $pdf->Cell($ancho_metodo, 3, '   ' . $medio . ':', 0, 0, 'L');
                    $pdf->Cell($ancho_monto, 3, 'S/ ' . number_format($monto, 2), 0, 1, 'R');
                }
            }

            // Mostrar subtotal de pasajes
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            $pdf->Cell($ancho_metodo, 3, '   SUBTOTAL PASAJES:', 0, 0, 'L');
            $pdf->Cell($ancho_monto, 3, 'S/ ' . number_format($datos['total_importe'], 2), 0, 1, 'R');
            $pdf->Ln(2);

            $total_neto_pasajes += $datos['total_neto'];
            $totales_pasajes_por_vendedor[$vendedor] = $datos['total_neto'];
        }
    }

    // ============= SECCIÓN DE ENCOMIENDAS (usando comisiones_por_vendedor) =============
    $tiene_encomiendas = false;
    $encomiendas_por_vendedor = [];

    if (!empty($comisiones_por_vendedor)) {
        foreach ($comisiones_por_vendedor as $vendedor => $comisiones) {
            $total_encomienda_vendedor = $comisiones['encomiendas']['base'];
            if ($total_encomienda_vendedor > 0) {
                $tiene_encomiendas = true;
                $encomiendas_por_vendedor[$vendedor] = $total_encomienda_vendedor;
            }
        }
    }

    // También verificar en ventas_por_vendedor si hay datos de encomiendas
    foreach ($ventas_por_vendedor as $vendedor => $datos) {
        if (isset($datos['total_encomiendas']) && $datos['total_encomiendas'] > 0) {
            $tiene_encomiendas = true;
            if (!isset($encomiendas_por_vendedor[$vendedor])) {
                $encomiendas_por_vendedor[$vendedor] = $datos['total_encomiendas'];
            }
        }
    }

    if ($tiene_encomiendas) {
        foreach ($encomiendas_por_vendedor as $vendedor => $total_encomienda) {
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
            $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 3, 'ENCOMIENDAS - DETALLE POR MÉTODO DE PAGO:', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
            $ancho_metodo = 40;
            $ancho_monto = PAGE_WIDTH - (2 * MARGIN) - $ancho_metodo;

            // Buscar métodos de pago para encomiendas de este vendedor
            $metodos_encomiendas = $datos['metodos_encomiendas'] ?? [];

            if (!empty($metodos_encomiendas)) {
                foreach ($metodos_encomiendas as $medio => $monto) {
                    $pdf->Cell($ancho_metodo, 3, '   ' . $medio . ':', 0, 0, 'L');
                    $pdf->Cell($ancho_monto, 3, 'S/ ' . number_format($monto, 2), 0, 1, 'R');
                }
            } else {
                $pdf->Cell($ancho_metodo, 3, '   No se encontraron métodos de pago', 0, 1, 'L');
            }

            // Mostrar subtotal de encomiendas
            $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
            $pdf->Cell($ancho_metodo, 3, '   SUBTOTAL ENCOMIENDAS:', 0, 0, 'L');
            $pdf->Cell($ancho_monto, 3, 'S/ ' . number_format($total_encomienda, 2), 0, 1, 'R');
            $pdf->Ln(2);

            $total_encomiendas_general += $total_encomienda;
        }
    }

    // ============= MOSTRAR TOTALES GENERALES =============
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');
    $pdf->Ln(1);

    // Total ventas general
    $total_ventas_general = $total_neto_pasajes + $total_encomiendas_general;

    $ancho_label = 40;
    $ancho_valor = PAGE_WIDTH - (2 * MARGIN) - $ancho_label;

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
    $pdf->Cell($ancho_label, 4, 'TOTAL VENTA PASAJES (Neto):', 0, 0, 'L');
    $pdf->Cell($ancho_valor, 4, 'S/ ' . number_format($total_neto_pasajes, 2), 0, 1, 'R');

    $pdf->Cell($ancho_label, 4, 'TOTAL ENCOMIENDAS:', 0, 0, 'L');
    $pdf->Cell($ancho_valor, 4, 'S/ ' . number_format($total_encomiendas_general, 2), 0, 1, 'R');

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($ancho_label, 5, 'TOTAL VENTAS:', 0, 0, 'L');
    $pdf->Cell($ancho_valor, 5, 'S/ ' . number_format($total_ventas_general, 2), 0, 1, 'R');

    $pdf->Ln(0.5);

    // ============= MOSTRAR TOTALES GENERALES POR MÉTODO DE PAGO=============
    $totales_metodo_pago = [];
    // Recorrer todos los vendedores para obtener los métodos de pago
    foreach ($ventas_por_vendedor as $vendedor => $datos) {

        // ---------------- PASAJES ----------------
        if (!empty($datos['metodos_pasajes'])) {
            foreach ($datos['metodos_pasajes'] as $medio => $monto) {
                if (!isset($totales_metodo_pago[$medio])) {
                    $totales_metodo_pago[$medio] = 0.00;
                }
                $totales_metodo_pago[$medio] += floatval($monto);
            }
        }

        // ---------------- ENCOMIENDAS ----------------
        if (!empty($datos['metodos_encomiendas'])) {
            foreach ($datos['metodos_encomiendas'] as $medio => $monto) {
                if (!isset($totales_metodo_pago[$medio])) {
                    $totales_metodo_pago[$medio] = 0.00;
                }
                $totales_metodo_pago[$medio] += floatval($monto);
            }
        }
    }

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SUBTITLE);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');
    $pdf->Ln(1);

    $ancho_label = 40;
    $ancho_valor = PAGE_WIDTH - (2 * MARGIN) - $ancho_label;

    // Mostrar cada método de pago
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SUBTITLE);

    foreach ($totales_metodo_pago as $medio => $monto) {
        $pdf->Cell($ancho_label, 4, strtoupper($medio) . ':', 0, 0, 'L');
        $pdf->Cell($ancho_valor, 4, 'S/ ' . number_format($monto, 2), 0, 1, 'R');
    }

    // Total general
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
    $pdf->Cell($ancho_label, 5, 'TOTAL VENTAS:', 0, 0, 'L');
    $pdf->Cell($ancho_valor, 5, 'S/ ' . number_format($total_ventas_general, 2), 0, 1, 'R');

    $pdf->Ln(0.5);
}

// ============= COMISIONES POR VENDEDOR =============
$mostrar_comisiones = false;
if (!empty($comisiones_por_vendedor)) {
    foreach ($comisiones_por_vendedor as $vendedor => $datos) {
        if ($datos['pasajes']['comision'] > 0 || $datos['encomiendas']['comision'] > 0) {
            $mostrar_comisiones = true;
            break;
        }
    }
}

if ($mostrar_comisiones) {
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'COMISIONES POR VENDEDOR', 0, 1, 'C');

    $ancho_vendedor = 25;
    $ancho_tipo = 25;
    $ancho_cant = 8;
    $ancho_porc = 10;
    $ancho_total = 18;
    $ancho_comision = 15;

    // Variables para acumular totales de comisiones
    $total_comision_pasajes = 0;
    $total_comision_encomiendas = 0;
    $total_base_pasajes_neto = 0;      // Base neta de pasajes (importe - egresos)
    $total_base_encomiendas = 0;

    foreach ($comisiones_por_vendedor as $vendedor => $datos) {
        // Encabezados de tabla
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
        $pdf->Cell($ancho_tipo, 3, 'TIPO', 0, 0, 'L');
        $pdf->Cell($ancho_cant, 3, 'CANT', 0, 0, 'C');
        $pdf->Cell($ancho_porc, 3, '%', 0, 0, 'C');
        $pdf->Cell($ancho_total, 3, 'BASE NETA', 0, 0, 'R');
        $pdf->Cell($ancho_comision, 3, 'COMISION', 0, 1, 'R');

        $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);

        // Pasajes - Usar importe neto (importe total - egresos) como base para la comisión
        if ($datos['pasajes']['cantidad'] > 0 && $datos['pasajes']['base'] > 0) {
            $base_pasajes_neto = $datos['pasajes']['base'];
            $comision_pasajes = $datos['pasajes']['comision'];
            $porcentaje_pasajes = $datos['pasajes']['porcentaje'];

            $pdf->Cell($ancho_tipo, 3, 'PASAJES', 0, 0, 'L');
            $pdf->Cell($ancho_cant, 3, $datos['pasajes']['cantidad'], 0, 0, 'C');
            $pdf->Cell($ancho_porc, 3, number_format($porcentaje_pasajes, 1) . '%', 0, 0, 'C');
            $pdf->Cell($ancho_total, 3, number_format($base_pasajes_neto, 2), 0, 0, 'R');
            $pdf->Cell($ancho_comision, 3, number_format($comision_pasajes, 2), 0, 1, 'R');

            $total_base_pasajes_neto += $base_pasajes_neto;
            $total_comision_pasajes += $comision_pasajes;
        }

        // Encomiendas (usar importe total de encomiendas como base)
        if ($datos['encomiendas']['cantidad'] > 0 && $datos['encomiendas']['base'] > 0) {
            $base_encomiendas = $datos['encomiendas']['base'];
            $comision_encomiendas = $datos['encomiendas']['comision'];
            $porcentaje_encomiendas = $datos['encomiendas']['porcentaje'];

            $pdf->Cell($ancho_tipo, 3, 'ENCOMIENDAS', 0, 0, 'L');
            $pdf->Cell($ancho_cant, 3, $datos['encomiendas']['cantidad'], 0, 0, 'C');
            $pdf->Cell($ancho_porc, 3, number_format($porcentaje_encomiendas, 1) . '%', 0, 0, 'C');
            $pdf->Cell($ancho_total, 3, number_format($base_encomiendas, 2), 0, 0, 'R');
            $pdf->Cell($ancho_comision, 3, number_format($comision_encomiendas, 2), 0, 1, 'R');

            $total_base_encomiendas += $base_encomiendas;
            $total_comision_encomiendas += $comision_encomiendas;
        }

        $pdf->Ln(1);
    }

    $total_comision_general = $total_comision_pasajes + $total_comision_encomiendas;
    $total_base_general = $total_base_pasajes_neto + $total_base_encomiendas;

    $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');

    // Calcular el ancho total para las etiquetas
    $ancho_etiqueta_total = $ancho_tipo + $ancho_cant + $ancho_porc;

    // TOTAL COMISIONES VENDEDOR
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
    $pdf->Cell($ancho_etiqueta_total, 4, 'TOTAL COMISIONES VENDEDOR:', 0, 0, 'L');
    $pdf->Cell($ancho_total, 4, '', 0, 0, 'R');
    $pdf->Cell($ancho_comision, 4, 'S/ ' . number_format($total_comision_general, 2), 0, 1, 'R');

    // Comisión Empresa (CALCULAR sobre $total_ventas_general)
    if (isset($total_ventas_general) && isset($totales['porcentaje_empresa'])) {
        $comision_empresa_calculada = ($total_ventas_general * $totales['porcentaje_empresa']) / 100;

        // CORRECCIÓN: Usar $ancho_etiqueta_total en lugar de $ancho_total
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
        $pdf->Cell($ancho_etiqueta_total, 4, 'TOTAL COMISIONES EMPRESA (' . number_format($totales['porcentaje_empresa'], 2) . '%):', 0, 0, 'L');
        $pdf->Cell($ancho_total, 4, '', 0, 0, 'R');
        $pdf->Cell($ancho_comision, 4, 'S/ ' . number_format($comision_empresa_calculada, 2), 0, 1, 'R');
    }
    $pdf->Ln(0.5);
}

// ============= EGRESOS GENERALES (LIQUIDACIÓN DEL VEHÍCULO) =============

if (!empty($data["EGRESOS_GENERALES"])) {
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'EGRESOS GENERALES (GASTOS)', 0, 1, 'C');
    $pdf->Ln(1);
    // Definir anchos de columnas
    $ancho_concepto = 28;
    $ancho_tipo = 13;
    $ancho_serie = 25;
    $ancho_monto = 15;

    // Verificar que la suma no exceda el ancho de página
    $suma_anchos = $ancho_concepto + $ancho_tipo + $ancho_serie + $ancho_monto;
    if ($suma_anchos > (PAGE_WIDTH - (2 * MARGIN))) {
        $factor = (PAGE_WIDTH - (2 * MARGIN)) / $suma_anchos;
        $ancho_concepto = round($ancho_concepto * $factor);
        $ancho_tipo = round($ancho_tipo * $factor);
        $ancho_serie = round($ancho_serie * $factor);
        $ancho_monto = round($ancho_monto * $factor);
    }

    // Encabezados
    $x_start = MARGIN;
    $y_start = $pdf->GetY();
    $line_height = 4;

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);

    $pdf->MultiCell($ancho_concepto, $line_height, 'CONCEPTO', 0, 'L', false, 0, $x_start, $y_start, true);
    $x_start += $ancho_concepto;

    $pdf->MultiCell($ancho_tipo, $line_height, 'TIPO', 0, 'L', false, 0, $x_start, $y_start, true);
    $x_start += $ancho_tipo;

    $pdf->MultiCell($ancho_serie, $line_height, 'COMPROBANTE', 0, 'L', false, 0, $x_start, $y_start, true);
    $x_start += $ancho_serie;

    $pdf->MultiCell($ancho_monto, $line_height, 'MONTO', 0, 'R', false, 1, $x_start, $y_start, true);

    $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);

    $total_egresos_generales_mostrar = 0;
    $num_egresos = 0;

    foreach ($data["EGRESOS_GENERALES"] as $egreso) {
        $concepto = substr($egreso['concepto'] ?? 'S/C', 0, 20);

        // Obtener tipo de comprobante (puede venir como descripción o ID)
        $tipo_comprobante = $egreso['tipo_comprobante_desc'] ?? '';
        if (empty($tipo_comprobante)) {
            $tp = $egreso['tp_comprobante'] ?? 0;
            $tipos = [1 => 'BOLETA', 2 => 'FACTURA', 3 => 'TICKET', 4 => 'N.CRÉDITO', 5 => 'N.DÉBITO'];
            $tipo_comprobante = $tipos[$tp] ?? 'OTRO';
        }
        $tipo_comprobante = substr($tipo_comprobante, 0, 10);

        $serie = ($egreso['serie'] ?? '') . '-' . ($egreso['correlativo'] ?? '');
        $serie = substr($serie, 0, 15);
        $monto = floatval($egreso['monto'] ?? 0);

        $y_start_fila = $pdf->GetY();
        $x_start = MARGIN;

        // Calcular altura máxima
        $alturas = [];
        $alturas[] = $pdf->NbLines($ancho_concepto, $concepto);
        $alturas[] = $pdf->NbLines($ancho_tipo, $tipo_comprobante);
        $alturas[] = $pdf->NbLines($ancho_serie, $serie);
        $alturas[] = $pdf->NbLines($ancho_monto, number_format($monto, 2));
        $max_lines = max($alturas);
        $cell_height = max($max_lines * 3, 3.5);

        // CONCEPTO
        $pdf->MultiCell($ancho_concepto, $cell_height, $concepto, 0, 'L', false, 0, $x_start, $y_start_fila, true);
        $x_start += $ancho_concepto;

        // TIPO COMPROBANTE
        $pdf->MultiCell($ancho_tipo, $cell_height, $tipo_comprobante, 0, 'L', false, 0, $x_start, $y_start_fila, true);
        $x_start += $ancho_tipo;

        // SERIE/CORRELATIVO
        $pdf->MultiCell($ancho_serie, $cell_height, $serie, 0, 'L', false, 0, $x_start, $y_start_fila, true);
        $x_start += $ancho_serie;

        // MONTO
        $pdf->MultiCell($ancho_monto, $cell_height, number_format($monto, 2), 0, 'R', false, 1, $x_start, $y_start_fila, true);

        $total_egresos_generales_mostrar += $monto;
        $num_egresos++;
    }

    // Línea separadora
    $pdf->SetFont('helvetica', '', TEXT_SIZE_SMALL);
    $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('-', 48), 0, 1, 'C');

    // Total
    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_SMALL);
    $ancho_etiqueta = $ancho_concepto + $ancho_tipo + $ancho_serie;
    $pdf->Cell($ancho_etiqueta, 3, 'TOTAL EGRESOS GENERALES (' . $num_egresos . '):', 0, 0, 'L');
    $pdf->Cell($ancho_monto, 3, 'S/ ' . number_format($total_egresos_generales_mostrar, 2), 0, 1, 'R');

    $pdf->Ln(2);
}

// // ============= RESUMEN DE LIQUIDACION =============
// $pdf->SetFont('helvetica', 'B', TEXT_SIZE_BODY);
// $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 4, 'RESUMEN DE LIQUIDACION', 0, 1, 'C');
// $pdf->Cell(PAGE_WIDTH - (2 * MARGIN), 1, str_repeat('=', 48), 0, 1, 'C');

// $ancho_label = 45;
// $ancho_monto = PAGE_WIDTH - (2 * MARGIN) - $ancho_label;

// $pdf->SetFont('helvetica', '', 7.5);

// // Total Ventas (importe neto pasajes + encomiendas)
// $pdf->Cell($ancho_label, 3.5, 'TOTAL VENTAS:', 0, 0, 'L');
// $pdf->Cell($ancho_monto, 3.5, 'S/ ' . number_format($total_ventas_general, 2), 0, 1, 'R');

// // Comisión del Vendedor (sobre el neto de pasajes y encomiendas)
// if (isset($total_comision_general) && $total_comision_general > 0) {
//     $pdf->Cell($ancho_label, 3.5, 'Comisión Vendedor:', 0, 0, 'L');
//     $pdf->Cell($ancho_monto, 3.5, 'S/ ' . number_format($total_comision_general, 2), 0, 1, 'R');
// }

// // Comisión Empresa (se coloca ANTES de los egresos generales)
// if (isset($totales['porcentaje_empresa']) && $totales['porcentaje_empresa'] > 0) {
//     $comision_empresa_calculada = ($total_ventas_general * $totales['porcentaje_empresa']) / 100;
//     $pdf->Cell($ancho_label, 3.5, 'Comisión Empresa (' . number_format($totales['porcentaje_empresa'], 2) . '%):', 0, 0, 'L');
//     $pdf->Cell($ancho_monto, 3.5, 'S/ ' . number_format($comision_empresa_calculada, 2), 0, 1, 'R');
// }

// // Egresos Generales (se coloca DESPUÉS de la comisión empresa)
// if (isset($totales['total_egresos_generales']) && $totales['total_egresos_generales'] > 0) {
//     $pdf->Cell($ancho_label, 3.5, 'Egresos Generales:', 0, 0, 'L');
//     $pdf->Cell($ancho_monto, 3.5, 'S/ ' . number_format($totales['total_egresos_generales'], 2), 0, 1, 'R');
// }

// // Línea separadora
// $pdf->SetFont('helvetica', '', 8);
// $pdf->Cell(70, 1, str_repeat('-', 80), 0, 1, 'C');

// // Calcular subtotal antes de TOTAL A PAGAR (opcional)
// $subtotal = $total_ventas_general;
// if (isset($total_comision_general) && $total_comision_general > 0) {
//     $subtotal -= $total_comision_general;
// }
// if (isset($comision_empresa_calculada) && $comision_empresa_calculada > 0) {
//     $subtotal -= $comision_empresa_calculada;
// }
// if (isset($totales['total_egresos_generales']) && $totales['total_egresos_generales'] > 0) {
//     $subtotal -= $totales['total_egresos_generales'];
// }

// // TOTAL A PAGAR 
// $pdf->SetFont('helvetica', 'B', 10); 
// $pdf->SetTextColor(0, 0, 0);
// $pdf->Cell($ancho_label, 6, 'TOTAL A PAGAR:', 0, 0, 'L');
// $pdf->SetFont('helvetica', 'B', 10);
// $pdf->Cell($ancho_monto, 6, 'S/ ' . number_format($subtotal, 2), 0, 1, 'R');

// Limpiar buffer y generar PDF
ob_end_clean();
$pdf->Output('liquidacion_ticket.pdf', 'I');
