<?php
ob_start();
ini_set('memory_limit', '1024M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// Incluye dependencias
require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/tc/tcpdf.php');

// Definiciones constantes para formato
define("WIDTH_LOGO", 25);
define("HEIGHT_LOGO", 20);
define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8.5);
define("TEXT_WIDTH_SIZE_BODY", 40);

// Clase personalizada para manejar celdas ajustadas en TCPDF
class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        parent::__construct('L', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetMargins(5, 10, 5);
        $this->SetAutoPageBreak(false); // Desactivar auto page break
    }

    public function SetWidths($w)
    {
        $this->widths = $w;
    }

    public function SetAligns($a)
    {
        $this->aligns = $a;
    }

    public function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $drawBorders = true)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = is_array($align) && isset($align[$i]) ? $align[$i] : (is_string($align) ? $align : 'L');

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();
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

// Función para calcular precio unitario y total por producto
function calcularPrecioUnitarioYTotal($encomienda)
{
    $op_gravada = floatval($encomienda["encomienda_precio_op_gravada"] ?? 0.00);
    $op_exonerada = floatval($encomienda["encomienda_precio_op_exonerada"] ?? 0.00);
    $op_inafecta = floatval($encomienda["encomienda_precio_op_inafecta"] ?? 0.00);
    $cantidad = floatval($encomienda["encomienda_cantidad"] ?? 1);
    $igv_rate = 1.18;

    $subtotal_sin_igv = 0.00;
    $es_gravada = false;
    if ($op_gravada > 0) {
        $subtotal_sin_igv = $op_gravada;
        $es_gravada = true;
    } elseif ($op_exonerada > 0) {
        $subtotal_sin_igv = $op_exonerada;
    } elseif ($op_inafecta > 0) {
        $subtotal_sin_igv = $op_inafecta;
    }

    $precio_unitario_sin_igv = ($cantidad > 0) ? round($subtotal_sin_igv / $cantidad, 2) : $subtotal_sin_igv;
    $precio_unitario_con_igv = $es_gravada ? round($precio_unitario_sin_igv * $igv_rate, 2) : $precio_unitario_sin_igv;
    $total = $es_gravada ? round($subtotal_sin_igv * $igv_rate, 2) : $subtotal_sin_igv;

    error_log("Encomienda ID: " . ($encomienda['id_encomienda'] ?? 'N/A') .
        ", Subtotal sin IGV: $subtotal_sin_igv, Cantidad: $cantidad, " .
        "Precio Unitario sin IGV: $precio_unitario_sin_igv, Precio Unitario con IGV: $precio_unitario_con_igv, " .
        "Total: $total, Es gravada: " . ($es_gravada ? 'Sí' : 'No'));

    return [
        'precio_unitario' => $precio_unitario_con_igv,
        'total' => $total
    ];
}

// Función para formatear datos de encomienda con documentos relacionados
function formatearDatosEncomiendaCompleto($encomienda, $tipo_comprobante_map)
{
    $fechaSalida = $encomienda["fecha_salida"] ?? '';
    if ($fechaSalida && $fechaSalida !== '0000-00-00') {
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaSalida);
        $fechaSalida = $fechaObj ? $fechaObj->format('d-m-Y') : 'Fecha inválida';
    } else {
        $fechaSalida = 'Fecha no disponible';
    }

    // Comprobante principal
    $comprobante = '';
    if (!empty($encomienda["venta_serie"]) && !empty($encomienda["venta_correlativo"])) {
        $comprobante = $encomienda["venta_serie"] . '-' .
            str_pad($encomienda["venta_correlativo"], 8, '0', STR_PAD_LEFT);
    }

    // Información de documentos relacionados
    $documentos_relacionados = [];

    // Documentos de referencia desde encomienda (tp_comprobante_ref, serie_ref, correlativo_ref)
    if (!empty($encomienda["serie_ref"]) && !empty($encomienda["correlativo_ref"])) {
        $tipo_comp_ref = $encomienda["tp_comprobante_ref"] ?? 'REF';
        $documentos_relacionados[] = "REF: {$tipo_comp_ref}-{$encomienda["serie_ref"]}-{$encomienda["correlativo_ref"]}";
    }

    // Guías desde encomienda (guia_serie, guia_correlativo)
    if (!empty($encomienda["guia_serie"]) && !empty($encomienda["guia_correlativo"])) {
        $documentos_relacionados[] = "GUIA: {$encomienda["guia_serie"]}-{$encomienda["guia_correlativo"]}";
    }

    // Documentos relacionados desde tabla doc_relacionado
    if (!empty($encomienda["doc_rel_serie"]) && !empty($encomienda["doc_rel_correlativo"])) {
        $tipo_comp_doc = $encomienda["doc_rel_tp_comprobante"] ?? 'DOC';
        $documentos_relacionados[] = "DOC: {$tipo_comp_doc}-{$encomienda["doc_rel_serie"]}-{$encomienda["doc_rel_correlativo"]}";
    }

    // Observación original
    $observacion = $encomienda["obs"] ?? '';

    // Construir texto extra con todos los documentos
    $extra_completo = '';
    if (!empty($documentos_relacionados) || !empty($observacion)) {
        $partes = array_merge($documentos_relacionados, [$observacion]);
        $partes = array_filter($partes);
        $extra_completo = '(' . implode(' | ', $partes) . ')';
    }

    return [
        'fecha' => $fechaSalida,
        'remitente' => trim($encomienda["remitente_nombres"] ?? '') . ' ' .
            trim($encomienda["remitente_apellidos"] ?? ''),
        'destinatario' => trim($encomienda["destinatario_nombres"] ?? '') . ' ' .
            trim($encomienda["destinatario_apellidos"] ?? ''),
        'comprobante' => $comprobante,
        'origen' => trim($encomienda["terminal_origen"] ?? '---'),
        'destino' => trim($encomienda["terminal_destino"] ?? '---'),
        'cantidad' => $encomienda["encomienda_cantidad"] ?? '0',
        'detalle' => trim($encomienda["encomienda_producto"] ?? '') . ' ' . $extra_completo,
        'forma_pago' => trim($encomienda["forma_pago"] ?? '---'),
        'medio_pago' => trim($encomienda["medio_pago"] ?? '---'),
        'estado_pago' => trim($encomienda["estado_pago"] ?? '---'),

        // Datos específicos para sección separada
        'documentos_relacionados' => $documentos_relacionados,
        'tiene_documentos_relacionados' => !empty($documentos_relacionados), // NUEVO: indicador si tiene documentos
        'ruc_ref' => $encomienda["ruc_ref"] ?? '',
        'guia_ruc' => $encomienda["guia_ruc"] ?? '',
        'doc_rel_ruc' => $encomienda["doc_rel_ruc"] ?? '',
        'id_encomienda' => $encomienda["id_encomienda"] ?? '',
        'id_venta' => $encomienda['id_venta'] ?? ($encomienda['venta_serie'] ?? '') . '-' . ($encomienda['venta_correlativo'] ?? '')
    ];
}

// Validación inicial de datos
$data = $this->data ?? [];
if (isset($data['success']) && $data['success'] === false) {
    error_log("Error en datos recibidos: " . json_encode($data['message']));
    die("Error al generar el reporte: " . ($data['message']['message'] ?? 'Datos inválidos'));
}

if (
    !is_array($data) ||
    !isset($data["BODY"]) || !is_array($data["BODY"]) ||
    !isset($data["HEADER"]) || !is_array($data["HEADER"]) ||
    !isset($data["TERMINALES"]) || !is_array($data["TERMINALES"]) ||
    !isset($data["EMPRESA"]) || !is_array($data["EMPRESA"]) ||
    !isset($data["RESUMEN_MEDIOS_PAGO"]) || !is_array($data["RESUMEN_MEDIOS_PAGO"])
) {
    error_log("Estructura de datos inválida: " . json_encode($data));
    throw new Exception("Los datos no contienen una estructura válida para generar el PDF.");
}

// Mapa de tipos de comprobante
$tipo_comprobante_map = $data['TIPO_COMPROBANTE_MAP'] ?? [
    1 => 'FACTURA DE VENTA ELECTRÓNICA',
    2 => 'NOTA DE VENTA',
    3 => 'BOLETA DE VENTA ELECTRÓNICA',
    7 => 'NOTA DE CREDITO',
    8 => 'NOTA DE DEBITO',
    9 => 'GUIA DE REMISIÓN REMITENTE',
    31 => 'GUIA DE REMISIÓN TRANSPORTISTA',
    0 => 'NO ESPECIFICADO'
];

// Agrupar encomiendas y calcular resúmenes
$encomiendas_por_terminal = [];
$calculated_totals = [];
$resumen_forma_pago = [];
$resumen_comprobante_forma_estado = [];

// Arrays auxiliares para rastrear ventas únicas (usando id_venta para unicidad)
$seen_ventas_por_medio = [];
$seen_ventas_por_forma = [];
$seen_ventas_por_clave = [];

foreach ($data["BODY"] as $encomienda) {
    $terminal_destino = trim($encomienda["terminal_destino"] ?? '');

    $encomiendas_por_terminal[$terminal_destino][] = array_merge($encomienda, [
        'detalle' => trim($encomienda['encomienda_producto'] ?? $encomienda['detalle'] ?? ''),
        'id_tp_comprobante' => $encomienda['tp_comprobante'] ?? $encomienda['id_tp_comprobante'] ?? 0
    ]);

    $totals = calcularPrecioUnitarioYTotal($encomienda);
    $total_producto = $totals['total'];

    $medio_pago = strtoupper(trim($encomienda["medio_pago"] ?? 'NO ESPECIFICADO'));
    $forma_pago = strtoupper(trim($encomienda["forma_pago"] ?? 'NO ESPECIFICADO'));
    $estado_pago = strtoupper(trim($encomienda["estado_pago"] ?? 'NO ESPECIFICADO'));
    $id_tp_comprobante = $encomienda['id_tp_comprobante'] ?? $encomienda['tp_comprobante'] ?? 0;
    $tipo_comprobante = $tipo_comprobante_map[$id_tp_comprobante] ?? 'NO ESPECIFICADO';

    $id_venta = $encomienda['id_venta'] ?? ($encomienda['venta_serie'] ?? '') . '-' . ($encomienda['venta_correlativo'] ?? '');
    $clave = $tipo_comprobante . '|' . $forma_pago . '|' . $estado_pago;

    // Inicializar si no existe
    if (!isset($calculated_totals[$medio_pago])) {
        $calculated_totals[$medio_pago] = ['cantidad' => 0, 'total' => 0.00];
        $seen_ventas_por_medio[$medio_pago] = [];
    }
    if (!isset($resumen_forma_pago[$forma_pago])) {
        $resumen_forma_pago[$forma_pago] = ['cantidad' => 0, 'total' => 0.00];
        $seen_ventas_por_forma[$forma_pago] = [];
    }
    if (!isset($resumen_comprobante_forma_estado[$clave])) {
        $resumen_comprobante_forma_estado[$clave] = [
            'tipo_comprobante' => $tipo_comprobante,
            'forma_pago' => $forma_pago,
            'estado_pago' => $estado_pago,
            'cantidad' => 0
        ];
        $seen_ventas_por_clave[$clave] = [];
    }

    // Sumar total siempre (por producto)
    $calculated_totals[$medio_pago]['total'] += $total_producto;
    $resumen_forma_pago[$forma_pago]['total'] += $total_producto;

    // Incrementar cantidad solo si es una venta única (nueva id_venta en este grupo)
    if (!empty($id_venta) && !in_array($id_venta, $seen_ventas_por_medio[$medio_pago])) {
        $calculated_totals[$medio_pago]['cantidad'] += 1;
        $seen_ventas_por_medio[$medio_pago][] = $id_venta;
    }
    if (!empty($id_venta) && !in_array($id_venta, $seen_ventas_por_forma[$forma_pago])) {
        $resumen_forma_pago[$forma_pago]['cantidad'] += 1;
        $seen_ventas_por_forma[$forma_pago][] = $id_venta;
    }
    if (!empty($id_venta) && !in_array($id_venta, $seen_ventas_por_clave[$clave])) {
        $resumen_comprobante_forma_estado[$clave]['cantidad'] += 1;
        $seen_ventas_por_clave[$clave][] = $id_venta;
    }
}

// Inicializar PDF
$pdf = new TCPDF_CellFit('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(5, 10, 5);
$pdf->SetAutoPageBreak(false); // Desactivar auto page break
$pdf->AddPage();

if (empty($data["BODY"])) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'No hay encomiendas para mostrar en este reporte.', 0, 1, 'C');
    $pdf->Output('reporte.pdf', 'I');
    exit;
}

// Función para imprimir el encabezado
function imprimirEncabezado($pdf, $data, $terminal_actual, $is_first_page = true)
{
    if ($is_first_page) {
        $logoPath = Session::get("data_empresa")["logo"] ?? '';

        // GUARDAR LA POSICIÓN INICIAL
        $initialY = $pdf->GetY();

        if (!empty($logoPath) && file_exists($logoPath)) {
            $bgWidth = 72.1 - 3;
            $bgX = 10;
            $bgY = 5;

            set_error_handler(function ($errno, $errstr) {
                return strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false;
            });

            list($imgWidth, $imgHeight) = getimagesize($logoPath);
            $scale = min(35 / $imgWidth, 25 / $imgHeight);
            $finalWidth = $imgWidth * $scale;
            $finalHeight = $imgHeight * $scale;

            $xLogo = $bgX + 2;
            $yLogo = $bgY + (25 - $finalHeight) / 2;

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
                500
            );

            restore_error_handler();

            // NO mover el cursor después del logo - mantener posición original
            $pdf->SetY($initialY);
        } else {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 10, 'Logo no disponible', 0, 1, 'L');
            $pdf->SetY($initialY + 10); // Ajustar solo si mostró mensaje
        }

        // SECCIÓN DE TERMINALES - POSICIONAR CORRECTAMENTE
        $pdf->SetFont('helvetica', '', 5.5);
        $pdf->SetY(3); // Posición fija para terminales
        $pdf->SetLeftMargin(10);

        // Obtener configuración de la empresa desde SESSION
        $configEmpresa = Session::get("data_empresa") ?? [];
        $mostrarTodasTerminales = isset($configEmpresa['m_terminalesManifi']) ? $configEmpresa['m_terminalesManifi'] == 1 : false;

        // Determinar qué terminales mostrar
        $terminalesAMostrar = [];
        if ($mostrarTodasTerminales) {
            // Mostrar todas las terminales
            $terminalesAMostrar = $data["TERMINALES"];
        } else {
            // Mostrar solo terminal de origen
            $terminalOrigen = $data["HEADER"]["terminal_origen"] ?? '';
            foreach ($data["TERMINALES"] as $terminal) {
                if (trim($terminal['nombre'] ?? '') === trim($terminalOrigen)) {
                    $terminalesAMostrar = [$terminal];
                    break;
                }
            }
            // Si no se encuentra la terminal de origen, mostrar la primera
            if (empty($terminalesAMostrar) && !empty($data["TERMINALES"])) {
                $terminalesAMostrar = [reset($data["TERMINALES"])];
            }
        }

        foreach ($terminalesAMostrar as $terminal) {
            $pdf->Cell(40);
            $pdf->SetWidths([160]);
            $pdf->Row([
                strtoupper(trim($terminal['nombre'] ?? '')) . ': ' .
                trim($terminal['direccion_fiscal'] ?? '') . ' - ' .
                trim($terminal['ubigeo'] ?? '') . ' - CELULAR: ' .
                trim($terminal['celular'] ?? '')
            ], 3.5, 'L', [], false);
        }

        // RESTABLECER POSICIÓN PARA EL RESTO DEL ENCABEZADO
        $pdf->SetY($initialY + 25); // Volver a la posición después del logo

        $pdf->Ln(-30);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(228);
        $pdf->SetWidths([50]);
        $pdf->Row(["RUC: " . (Session::get("data_empresa")["num_docu"] ?? '---')], 4, 'C');

        $pdf->Ln(1);
        $pdf->Cell(228);
        $pdf->SetWidths([50]);
        $pdf->Row(["MANIFIESTO DE EMBARQUE DE ENCOMIENDA"], 8, 'C');

        $pdf->Ln(1);
        $pdf->Cell(228);
        $pdf->SetWidths([50]);

        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetWidths([25, 70, 25, 60, 20, 50]);

        $conductor = trim($data["HEADER"]["conductor_nombres"] ?? '') . ' ' . trim($data["HEADER"]["conductor_apellidos"] ?? '');
        $licencia = $data["HEADER"]["conductor_licencia"] ?? '---';
        $placa = $data["HEADER"]["vehiculo_placa"] ?? '---';
        $origen = strtoupper($data["HEADER"]["terminal_origen"] ?? '---');
        $destino = strtoupper($data["HEADER"]["terminal_destino"] ?? '---');
        $fechaSalida = $data["HEADER"]["fecha_salida"] ?? '';
        $fechaSalida = $fechaSalida && $fechaSalida !== '0000-00-00'
            ? (DateTime::createFromFormat('Y-m-d', $fechaSalida) ?: 'Fecha inválida')->format('d-m-Y')
            : 'Fecha no disponible';

        $pdf->Row([
            "CONDUCTOR:",
            $conductor,
            "LICENCIA:",
            $licencia,
            "PLACA:",
            $placa
        ], 5, 'L', [], false);

        $pdf->Row([
            "ORIGEN:",
            $origen,
            "DESTINO:",
            $destino,
            "SALIDA:",
            $fechaSalida
        ], 5, 'L', [], false);

        $pdf->Ln(2);

        // Imprimir encabezado de la tabla solo en la primera página
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetWidths([14, 25, 25, 17, 25, 25, 10, 28, 13, 20, 20, 18, 13, 25]);
        $pdf->Row([
            'FECHA',
            'REMITENTE',
            'DESTINATARIO',
            'N. COMPRO.',
            'T. ORIGEN',
            'T. DESTINO',
            'CANT.',
            'DESCRIPCION',
            'P. UNIT.',
            'FORMA PAGO',
            'MEDIO PAGO',
            'ESTADO',
            'TOTAL',
            'OBSERVACIONES'
        ], 5, 'C');
    }
}

// Procesar encomiendas por terminal
imprimirEncabezado($pdf, $data, '', true);

// Definimos anchos y alineaciones para las filas de datos
$widths = [14, 25, 25, 17, 25, 25, 10, 28, 13, 20, 20, 18, 13, 25];
$pdf->SetWidths($widths);
$lineHeight = 4.5;
// echo json_encode($data['BODY']);
// die;
foreach ($data['BODY'] as $index => $encomienda) {
    // Preparar los datos de la fila usando la nueva función
    $totals = calcularPrecioUnitarioYTotal($encomienda);
    $datos_formateados = formatearDatosEncomiendaCompleto($encomienda, $tipo_comprobante_map);

    $pdf->SetFont('helvetica', '', 6.5);

    // echo json_encode($datos_formateados);
    // die;
    $data_fila = [
        $datos_formateados['fecha'],
        $datos_formateados['remitente'],
        $datos_formateados['destinatario'],
        $datos_formateados['comprobante'],
        $datos_formateados['origen'],
        $datos_formateados['destino'],
        $datos_formateados['cantidad'],
        $datos_formateados['detalle'],
        number_format($totals['precio_unitario'], 2),
        $datos_formateados['forma_pago'],
        $datos_formateados['medio_pago'],
        $datos_formateados['estado_pago'],
        number_format($totals['total'], 2),
        '' // Observaciones (ya incluidas en detalle)
    ];

    // Calcular dinámicamente la altura requerida para esta fila
    $nb = 0;
    for ($i = 0; $i < count($data_fila); $i++) {
        $nb = max($nb, $pdf->NbLines($widths[$i], $data_fila[$i]));
    }
    $h = $lineHeight * $nb;

    // Verificar si cabe en la página actual (margen inferior de 15 mm para resúmenes)
    if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
        $pdf->AddPage();
    }

    // Imprimir la fila
    $pdf->Row($data_fila, $lineHeight, 'C', [], true); // Asegurar boldColumns vacío
}

$pdf->Ln(3); // Reducir espacio después de cada terminal

// Verificar si hay espacio para los resúmenes
if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) { // 60 mm para resúmenes y firmas
    $pdf->AddPage();
}

// Resumen por Método de Pago
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 5, 'RESUMEN POR MÉTODO DE PAGO', 0, 1, 'L');
$pdf->Ln(1);

$pdf->SetWidths([40, 20, 20]);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Row(['MÉTODO DE PAGO', 'CANTIDAD', 'TOTAL'], 5, ['C', 'C', 'C']);

$pdf->SetFont('helvetica', '', 6.5);
$total_cantidad = 0;
$total_monto = 0.00;
$widths_resumen1 = [40, 20, 20];
$pdf->SetWidths($widths_resumen1);
$lineHeight_resumen = 4.5;
foreach ($calculated_totals as $medio_pago => $datos) {
    if ($datos['cantidad'] > 0 || $datos['total'] > 0) {
        $data_resumen = [
            $medio_pago,
            $datos['cantidad'],
            number_format($datos['total'], 2)
        ];

        $nb = 0;
        for ($i = 0; $i < count($data_resumen); $i++) {
            $nb = max($nb, $pdf->NbLines($widths_resumen1[$i], $data_resumen[$i]));
        }
        $h = $lineHeight_resumen * $nb;

        if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
            $pdf->AddPage();
        }

        $pdf->Row($data_resumen, $lineHeight_resumen, ['L', 'C', 'C'], [], false);
        $total_cantidad += $datos['cantidad'];
        $total_monto += $datos['total'];
    }
}

$pdf->Ln(1);
$line_width = array_sum($widths_resumen1);
$x_start = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x_start, $y, $x_start + $line_width, $y);
$pdf->Ln(1);

$pdf->SetFont('helvetica', 'B', 7);
$data_total = ['TOTAL', $total_cantidad, number_format($total_monto, 2)];
$nb = 0;
for ($i = 0; $i < count($data_total); $i++) {
    $nb = max($nb, $pdf->NbLines($widths_resumen1[$i], $data_total[$i]));
}
$h = $lineHeight_resumen * $nb;

if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
    $pdf->AddPage();
}
$pdf->Row($data_total, $lineHeight_resumen, ['L', 'C', 'C'], [], false);

$pdf->Ln(4); // Reducir espacio

// Resumen por Tipo de Comprobante, Forma y Estado de Pago
if ($pdf->GetY() > ($pdf->getPageHeight() - 40)) { // 40 mm para segundo resumen y firmas
    $pdf->AddPage();
}

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 5, 'RESUMEN POR TIPO DE COMPROBANTE, FORMA Y ESTADO DE PAGO', 0, 1, 'L');
$pdf->Ln(1);

$pdf->SetWidths([45, 30, 35, 20]);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Row(['TIPO DE COMPROBANTE', 'FORMA DE PAGO', 'ESTADO DE PAGO', 'CANTIDAD'], 5, ['C', 'C', 'C', 'C']);

$pdf->SetFont('helvetica', '', 6.5);
$total_cantidad = 0;
$widths_resumen2 = [45, 30, 35, 20];
$pdf->SetWidths($widths_resumen2);
if (empty($resumen_comprobante_forma_estado)) {
    $data_resumen = ['No hay datos disponibles', '', '', ''];
    $nb = 0;
    for ($i = 0; $i < count($data_resumen); $i++) {
        $nb = max($nb, $pdf->NbLines($widths_resumen2[$i], $data_resumen[$i]));
    }
    $h = $lineHeight_resumen * $nb;

    if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
        $pdf->AddPage();
    }
    $pdf->Row($data_resumen, $lineHeight_resumen, ['C', 'C', 'C', 'C'], [], false);
} else {
    foreach ($resumen_comprobante_forma_estado as $datos) {
        $data_resumen = [
            $datos['tipo_comprobante'],
            $datos['forma_pago'],
            $datos['estado_pago'],
            $datos['cantidad']
        ];

        $nb = 0;
        for ($i = 0; $i < count($data_resumen); $i++) {
            $nb = max($nb, $pdf->NbLines($widths_resumen2[$i], $data_resumen[$i]));
        }
        $h = $lineHeight_resumen * $nb;

        if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
            $pdf->AddPage();
        }

        $pdf->Row($data_resumen, $lineHeight_resumen, ['L', 'C', 'C', 'C'], [], false);
        $total_cantidad += $datos['cantidad'];
    }
}

$pdf->Ln(1);
$line_width = array_sum($widths_resumen2);
$x_start = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x_start, $y, $x_start + $line_width, $y);
$pdf->Ln(1);

$pdf->SetFont('helvetica', 'B', 7);
$data_total = ['TOTAL', '', '', $total_cantidad];
$nb = 0;
for ($i = 0; $i < count($data_total); $i++) {
    $nb = max($nb, $pdf->NbLines($widths_resumen2[$i], $data_total[$i]));
}
$h = $lineHeight_resumen * $nb;

if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 15)) {
    $pdf->AddPage();
}
$pdf->Row($data_total, $lineHeight_resumen, ['L', 'C', 'C', 'C'], [], false);

$pdf->Ln(4); // Reducir espacio

// NUEVA SECCIÓN: DOCUMENTOS RELACIONADOS - MODIFICADA SEGÚN REQUERIMIENTOS
// Verificar espacio para la nueva tabla
if ($pdf->GetY() > ($pdf->getPageHeight() - 60)) { // 60 mm para la nueva tabla
    $pdf->AddPage();
}

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 6, 'DOCUMENTOS RELACIONADOS', 0, 1, 'L');
$pdf->Ln(1);

// Definir anchos para la tabla de documentos (sin TIPO COMPROBANTE ni ORIGEN)
$widths_documentos = [40, 20, 20, 20, 25]; // Reducido de 7 a 5 columnas
$pdf->SetWidths($widths_documentos);
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Row([
    'COMPROBANTE ENCOMIENDA',
    'TIPO DOC',
    'SERIE',
    'CORRELATIVO',
    'RUC'
], 5, 'C');

$pdf->SetFont('helvetica', '', 6.5);

// Recolectar todos los documentos relacionados SIN DUPLICADOS
$todos_documentos = [];
$documentos_unicos = []; // Para evitar duplicados

foreach ($encomiendas_por_terminal as $terminal => $encomiendas) {
    foreach ($encomiendas as $encomienda) {
        // Verificar si la encomienda tiene documentos relacionados
        $datos_formateados = formatearDatosEncomiendaCompleto($encomienda, $tipo_comprobante_map);

        // SOLO procesar encomiendas que tengan documentos relacionados
        if (!$datos_formateados['tiene_documentos_relacionados']) {
            continue; // Saltar esta encomienda
        }

        // Obtener el comprobante principal de la encomienda
        $comprobante_principal = '';
        if (!empty($encomienda["venta_serie"]) && !empty($encomienda["venta_correlativo"])) {
            $comprobante_principal = $encomienda["venta_serie"] . '-' .
                str_pad($encomienda["venta_correlativo"], 8, '0', STR_PAD_LEFT);
        }

        // Documentos de referencia (tp_comprobante_ref, serie_ref, correlativo_ref)
        if (!empty($encomienda["serie_ref"]) && !empty($encomienda["correlativo_ref"])) {
            $tipo_ref = $encomienda["tp_comprobante_ref"] ?? 'REF';
            $clave_unica = $comprobante_principal . '|REF|' . $encomienda["serie_ref"] . '|' . $encomienda["correlativo_ref"];

            if (!isset($documentos_unicos[$clave_unica])) {
                $todos_documentos[] = [
                    'comprobante' => $comprobante_principal,
                    'tipo_doc' => $tipo_ref,
                    'serie' => $encomienda["serie_ref"] ?? '',
                    'correlativo' => $encomienda["correlativo_ref"] ?? '',
                    'ruc' => $encomienda["ruc_ref"] ?? ''
                ];
                $documentos_unicos[$clave_unica] = true;
            }
        }

        // Documentos relacionados desde tabla doc_relacionado
        if (!empty($encomienda["doc_rel_serie"]) && !empty($encomienda["doc_rel_correlativo"])) {
            $tipo_doc = $encomienda["doc_rel_tp_comprobante"] ?? 'DOC';
            $clave_unica = $comprobante_principal . '|DOC|' . $encomienda["doc_rel_serie"] . '|' . $encomienda["doc_rel_correlativo"];

            if (!isset($documentos_unicos[$clave_unica])) {
                $todos_documentos[] = [
                    'comprobante' => $comprobante_principal,
                    'tipo_doc' => $tipo_doc,
                    'serie' => $encomienda["doc_rel_serie"] ?? '',
                    'correlativo' => $encomienda["doc_rel_correlativo"] ?? '',
                    'ruc' => $encomienda["doc_rel_ruc"] ?? ''
                ];
                $documentos_unicos[$clave_unica] = true;
            }
        }

        // Guías (guia_serie, guia_correlativo)
        if (!empty($encomienda["guia_serie"]) && !empty($encomienda["guia_correlativo"])) {
            $clave_unica = $comprobante_principal . '|GUIA|' . $encomienda["guia_serie"] . '|' . $encomienda["guia_correlativo"];

            if (!isset($documentos_unicos[$clave_unica])) {
                $todos_documentos[] = [
                    'comprobante' => $comprobante_principal,
                    'tipo_doc' => 'GUIA',
                    'serie' => $encomienda["guia_serie"] ?? '',
                    'correlativo' => $encomienda["guia_correlativo"] ?? '',
                    'ruc' => $encomienda["guia_ruc"] ?? ''
                ];
                $documentos_unicos[$clave_unica] = true;
            }
        }

        // NOTA: Ya no incluimos el comprobante principal si no tiene documentos relacionados
        // porque eso fue filtrado al inicio del bucle
    }
}

// Mostrar los documentos en la tabla
if (empty($todos_documentos)) {
    $pdf->Row(['No hay documentos relacionados', '', '', '', ''], 4, 'C');
} else {
    // Opcional: Ordenar los documentos por comprobante
    usort($todos_documentos, function ($a, $b) {
        return strcmp($a['comprobante'], $b['comprobante']);
    });

    foreach ($todos_documentos as $documento) {
        $data_documento = [
            $documento['comprobante'],
            $documento['tipo_doc'],
            $documento['serie'],
            $documento['correlativo'],
            $documento['ruc']
        ];

        // Calcular altura de la fila
        $nb = 0;
        for ($i = 0; $i < count($data_documento); $i++) {
            $nb = max($nb, $pdf->NbLines($widths_documentos[$i], $data_documento[$i]));
        }
        $h = 4 * $nb;

        // Verificar espacio antes de imprimir
        if ($pdf->GetY() + $h > ($pdf->getPageHeight() - 25)) {
            $pdf->AddPage();
            // Reimprimir encabezado de la tabla en nueva página
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Row([
                'COMPROBANTE ENCOMIENDA',
                'TIPO DOC',
                'SERIE',
                'CORRELATIVO',
                'RUC'
            ], 5, 'C');
            $pdf->SetFont('helvetica', '', 6.5);
        }

        $pdf->Row($data_documento, 4, 'C');
    }
}

$pdf->Ln(6);

// Firmas
if ($pdf->GetY() > ($pdf->getPageHeight() - 20)) { // 20 mm para firmas
    $pdf->AddPage();
}

$y = $pdf->GetY();
$pageWidth = $pdf->getPageWidth();
$cellWidth = 40;
$spacing = 8;
$extraMargin = 12;
$xFirmaReceptor = $pageWidth - (2 * $cellWidth + $spacing + $extraMargin);

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetXY($xFirmaReceptor, $y);
$pdf->Cell($cellWidth, 0, '', 'T');
$pdf->SetXY($xFirmaReceptor, $y + 3);
$pdf->Cell($cellWidth, 5, 'Firma del Receptor', 0, 0, 'C');

$xFirmaResponsable = $xFirmaReceptor + $cellWidth + $spacing;
$pdf->SetXY($xFirmaResponsable, $y);
$pdf->Cell($cellWidth, 0, '', 'T');
$pdf->SetXY($xFirmaResponsable, $y + 3);
$pdf->Cell($cellWidth, 5, 'Firma del Responsable', 0, 0, 'C');

ob_clean();
$pdf->Output('reporte.pdf', 'I');
ob_end_flush();
