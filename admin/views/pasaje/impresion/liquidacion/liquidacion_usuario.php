<?php
ob_start();
ini_set('memory_limit', '1024M'); // subir límite temporalmente
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/tc/tcpdf.php');

// Constantes de configuración
define("TEXT_SIZE_TITULO", 12);
define("TEXT_SIZE_BODY", 8);
define("TEXT_WIDTH_SIZE_BODY", 36);
define("WIDTH_LOGO", 40); // Aumentado para un logo más grande
define("HEIGHT_LOGO", 30); // Aumentado para dar flexibilidad

// Función auxiliar para limpiar y cargar imágenes (PNG o JPG)
function cleanImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        error_log("Imagen no encontrada o no legible: $filePath");
        return false;
    }

    // Obtener información de la imagen
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        error_log("No se pudo obtener información de la imagen: $filePath");
        return false;
    }

    $mime = $imageInfo['mime'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $imgWidth = $imageInfo[0];
    $imgHeight = $imageInfo[1];

    // Calcular escala para mantener proporciones
    $scale = min(WIDTH_LOGO / $imgWidth, HEIGHT_LOGO / $imgHeight);
    $finalWidth = $imgWidth * $scale;
    $finalHeight = $imgHeight * $scale;

    // Asegurar que el ancho no exceda el espacio disponible (100 mm para dejar espacio al título)
    if ($finalWidth > 100) {
        $finalWidth = 100;
        $finalHeight = $finalWidth * ($imgHeight / $imgWidth);
    }

    // Procesar según el tipo de imagen
    if ($mime === 'image/png' || $extension === 'png') {
        // Cargar PNG con soporte para transparencia
        $img = @imagecreatefrompng($filePath);
        if (!$img) {
            error_log("Error al cargar PNG: $filePath");
            return false;
        }

        // Crear nueva imagen con soporte para transparencia
        $new = imagecreatetruecolor($imgWidth, $imgHeight);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $imgWidth, $imgHeight, $transparent);

        // Copiar el contenido original
        imagecopy($new, $img, 0, 0, 0, 0, $imgWidth, $imgHeight);

        // Capturar como string
        ob_start();
        imagepng($new, null, 0);
        $data = ob_get_clean();

        imagedestroy($img);
        imagedestroy($new);

        return ['data' => $data, 'type' => 'PNG', 'width' => $finalWidth, 'height' => $finalHeight];
    } elseif ($mime === 'image/jpeg' || $extension === 'jpg' || $extension === 'jpeg') {
        // JPGs no necesitan reprocesamiento
        return ['data' => $filePath, 'type' => 'JPG', 'width' => $finalWidth, 'height' => $finalHeight];
    } else {
        error_log("Formato de imagen no soportado: $mime ($filePath)");
        return false;
    }
}

class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);
        $this->SetMargins(7, 10, 7);
        $this->SetAutoPageBreak(true, 10);
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $drawBorders = true)
    {
        // Limpiar valores nulos
        foreach ($data as $i => $value) {
            $data[$i] = (string) ($value ?? '');
        }

        // 1. Calcular el número máximo de líneas requeridas en esta fila
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;

        // 2. Verificar si hay espacio en la página
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        // 3. Imprimir cada celda
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];

            // Soporte para alineaciones por columna (array en $align)
            if (is_array($align) && isset($align[$i])) {
                $a = $align[$i];
            } else {
                $a = is_string($align) ? $align : 'L'; // valor por defecto
            }

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            // Activar negrita si corresponde
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

            // Quitar negrita
            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

            // Mover a la siguiente celda
            $this->SetXY($xBefore + $w, $yBefore);
        }

        // 4. Mover cursor a la siguiente línea
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
}

$pdf = new TCPDF_CellFit('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

$total_num_ventas = 0;
$total_ventas = 0.00;
$total_porcentaje_pa = 0.00;
$total_porcentaje_en = 0.00;
$total_ventas_e = 0.00;
$total_num_ventas_e = 0;
$encomiendas_por_vendedor = [];
$total_general = 0.00;
$total_egresos = 0.00;

// Obtener ruta del logo desde el array
$logoPath = $this->data["HEADER"]["HEADER_EMPRESA"]["logo"] ?? '';
$titulo = "LIQUIDACIÓN DE USUARIO";

// Posición y tamaño deseado del logo
$xLogo = 10;
$yLogo = 10;

// Inicializa variables para evitar undefined
$imageData = null;  // Asegura que $imageData sea null si no se carga
$hLogo = 10;  // Valor predeterminado para espacio vertical

// Verifica si existe el logo y es válido
if (!empty($logoPath) && file_exists($logoPath)) {
    set_error_handler(function ($errno, $errstr) {
        return strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false;
    });

    $imageData = cleanImage($logoPath);
    if ($imageData && isset($imageData['width'], $imageData['height'], $imageData['type'], $imageData['data'])) {
        $imageSrc = $imageData['type'] === 'PNG' ? '@' . $imageData['data'] : $imageData['data'];
        $pdf->Image($imageSrc, $xLogo, $yLogo, $imageData['width'], $imageData['height'], $imageData['type'], '', '', false, 300);
        $hLogo = $imageData['height'];  // Solo sobrescribe si se cargó correctamente
    } else {
        error_log("No se pudo procesar la imagen del logo: $logoPath");
    }

    restore_error_handler();
} else {
    error_log("Ruta del logo no válida o no encontrada: $logoPath");
}

// Posicionar título al lado del logo (o más centrado si no hay logo)
$logoWidth = $imageData['width'] ?? 0;  // Usa 0 si no hay imagen
$xTitle = $xLogo + $logoWidth + 5;
// Centrar el título si no hay logo
if ($logoWidth === 0) {
    $xTitle = ($pdf->getPageWidth() - 120) / 2;  // Centra el Cell de 120mm en la página (ancho total ~190mm)
}
$yTitle = $yLogo + 2;
$pdf->SetXY($xTitle, $yTitle);
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO);
$pdf->Cell(120, 6, $titulo, 0, 1, 'C');

$pdf->Ln($hLogo - 5); // Ajustado el espacio después del encabezado

// TABLA DE INFORMACION
$pdf->SetFont('helvetica', '', 7.5); // Reducir tamaño de fuente

// 1. Vendedor, DNI, Placa, Fecha
$pdf->SetWidths(array(18, 75, 9, 18, 14, 25, 12, 19));
$pdf->Row(array(
    "Vendedor:",
    $this->data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " . $this->data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"],
    "DNI:",
    $this->data["HEADER"]["HEADER_DETALLE"]["vendedor_num_docu"],
    "Placa:",
    $this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_placa"],
    "Fecha:",
    date("d-m-Y", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"])),
), 5, 'L', [0, 1, 2, 4, 6], false);

// 2. Origen, Destino, Hora
$pdf->SetWidths(array(18, 75, 14, 52, 10, 21));
$pdf->Row(array(
    "Origen:",
    $this->data["HEADER"]["HEADER_DETALLE"]["terminal_origen"],
    "Destino:",
    $this->data["HEADER"]["HEADER_DETALLE"]["terminal_destino"],
    "Hora:",
    date("h:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_hora_salida"])),
), 5, 'L', [0, 2, 4], false);

// 3. Piloto
$pdf->SetWidths(array(18, 172));
$pdf->Row(array(
    "Piloto:",
    $this->data["HEADER"]["HEADER_DETALLE"]["conductor_licencia"]
), 5, 'L', [0], false);

$pdf->Ln(3);

// ENCABEZADO DE TABLA GENERAL
$pdf->SetFont('helvetica', 'B', 7.5); // Letra más pequeña
$pdf->SetWidths(array(12, 68, 15, 24, 28, 23, 20));
$pdf->Row(array(
    "Asiento",
    "Cliente",
    "Nro. Doc.",
    "Estado",
    "S-N Comprobante",
    "Destino",
    "Importe",
), 4, 'C');

// Función auxiliar para formato numérico
function formatNumber($number)
{
    return str_pad(number_format(floatval($number), 2, '.', ','), 8, ' ', STR_PAD_LEFT);
}

foreach ($this->data["DETALLE"] ?? [] as $item) {
    $medio_pago = $item["medio_pago"];

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, $medio_pago, 0, 1, 'C');
    $pdf->Ln(1.5);

    $total_ventas_medioPago = 0.00;
    $num_ventas = 0;

    foreach ($item["pisos"] ?? [] as $pisoGroup) {
        $piso = $pisoGroup[0]["num_piso"] ?? 1;

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->Cell(190, 0, str_repeat('_ ', 40) . " Piso: $piso " . str_repeat(' _', 40), 0, 1, 'C');
        $pdf->Ln(1.5);

        foreach ($pisoGroup as $detalle) {
            $importe = ($detalle["estado_asiento"] !== "ANULADO")
                ? floatval($detalle["importe_total"] ?? $detalle["importe"] ?? 0)
                : 0.00;

            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetWidths([12, 68, 15, 24, 28, 23, 20]);
            $pdf->Row([
                $detalle["num_asiento"],
                $detalle["cliente_nombres"] . " " . $detalle["cliente_apellidos"],
                $detalle["cliente_num_docu"],
                $detalle["estado_asiento"],
                ($detalle["venta_serie"] ?? '') . " - " . str_pad($detalle["venta_correlativo"] ?? '', 8, "0", STR_PAD_LEFT),
                $detalle["terminal_destino"],
                number_format($importe, 2, '.', '')
            ], 3.5, ['C', 'L', 'C', 'C', 'C', 'C', 'C'], [], false);

            if ($detalle["estado_asiento"] !== "ANULADO") {
                $total_ventas_medioPago += $importe;
                $num_ventas++;
                $total_num_ventas++;
            }
        }
    }

    $total_ventas += $total_ventas_medioPago;

    // Resumen por medio de pago
    $pdf->Ln(1.5);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row(
        ["N VENTAS:", $num_ventas, "TOTAL: S/.", number_format($total_ventas_medioPago, 2, '.', '')],
        4,
        ['L', 'L', 'L', 'C'],
        [0, 2],
        false
    );
}

// Totales generales pasajes
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
$pdf->Ln(1.5);

$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetWidths([25, 115, 25, 25]);
$pdf->Row(
    ["N VENTAS:", $total_num_ventas, "TOTAL: S/.", number_format($total_ventas, 2, '.', '')],
    5,
    ['L', 'L', 'R', 'C'],
    [0, 2, 3],
    false
);
// ====================== APARTADO DE ENCOMIENDAS ======================
if (!empty($this->data["ENCOMIENDAS"])) {

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(190, 6, 'ENCOMIENDAS', 0, 1, 'C');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(3);

    $total_ventas_e = 0.00;
    $total_num_ventas_e = 0;

    foreach ($this->data["ENCOMIENDAS"] as $item) {
        $medio_pago = $item["medio_pago"] ?? 'SIN MEDIO';
        $encomiendas = $item["encomiendas"] ?? [];

        $total_mp = 0.00;
        $conteo_mp = 0;

        // Título del medio de pago
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
        $pdf->Cell(190, 0, strtoupper($medio_pago), 0, 1, 'C');
        $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
        $pdf->Ln(1.5);

        // Encabezado de tabla de encomiendas
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetWidths([20, 32, 38, 38, 35, 27]);
        $pdf->Row([
            "S-C",
            "Descripción",
            "Remitente",
            "Destinatario",
            "Vendedor",
            "Importe"
        ], 4, 'C');

        // Filas de encomiendas
        $pdf->SetFont('helvetica', '', 7);
        foreach ($encomiendas as $enc) {
            $importe = ($enc["estado"] !== "ANULADO")
                ? floatval($enc["monto_total"] ?? $enc["monto"] ?? 0)
                : 0.00;

            $pdf->Row([
                $enc["comprobante"] ?? '',
                strtoupper($enc["producto"] ?? 'SIN DESCRIPCIÓN'),
                ($enc["remitente"] ?? '') . '-' . ($enc["num_docu_remitente"] ?? ''),
                ($enc["destinatario"] ?? '') . '-' . ($enc["num_docu_destinatario"] ?? ''),
                $enc["vendedor"] ?? 'N/A',
                number_format($importe, 2, '.', '')
            ], 3.5, ['C', 'L', 'C', 'L', 'C', 'R'], [], false);

            if ($enc["estado"] !== "ANULADO") {
                $total_mp += $importe;
                $conteo_mp++;
                $total_ventas_e += $importe;
                $total_num_ventas_e++;

                // Acumular para tabla de totales por vendedor (si se necesita)
                $vendedor = $enc["vendedor"] ?? $vendedor_actual ?? 'SIN VENDEDOR';
                if (!isset($encomiendas_por_vendedor[$vendedor])) {
                    $encomiendas_por_vendedor[$vendedor] = [
                        'num_encomiendas' => 0,
                        'total_encomiendas' => 0.00
                    ];
                }
                if (!isset($encomiendas_por_vendedor[$vendedor][$medio_pago])) {
                    $encomiendas_por_vendedor[$vendedor][$medio_pago] = 0.00;
                }

                $encomiendas_por_vendedor[$vendedor][$medio_pago] += $importe;
                $encomiendas_por_vendedor[$vendedor]['num_encomiendas']++;
                $encomiendas_por_vendedor[$vendedor]['total_encomiendas'] += $importe;
            }
        }

        // Resumen por medio de pago
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetWidths([30, 30, 87, 30, 20]);
        $pdf->Row([
            "TOTAL VENTAS :",
            $conteo_mp,
            "",
            "TOTAL " . strtoupper($medio_pago) . " S/:",
            number_format($total_mp, 2, '.', '')
        ], 4, ['L', 'C', 'L', 'C'], [0, 2], false);
    }

    // ==================== RESUMEN GENERAL DE ENCOMIENDAS ====================
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(0.8);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row([
        "N VENTAS:",
        $total_num_ventas_e,
        "TOTAL ENCOMIENDAS: S/:",
        number_format($total_ventas_e, 2, '.', '')
    ], 4, ['L', 'L', 'L', 'R'], [0, 2, 3], false);

    $pdf->Ln(0.8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(3);

    // ==================== TABLA DE TOTALES POR VENDEDOR (ENCOMIENDAS) ====================
    if (!empty($encomiendas_por_vendedor)) {
        $pdf->SetFont('helvetica', 'B', 8);
        $linea = str_repeat('=', 110);
        $pdf->Cell(190, 4, $linea, 0, 1, 'C');
        $pdf->Cell(190, 5, 'TOTALES POR VENDEDOR - ENCOMIENDAS', 0, 1, 'C');
        $pdf->Cell(190, 4, $linea, 0, 1, 'C');
        $pdf->Ln(2);

        // Obtener medios únicos
        $medios_pago_unicos = [];
        foreach ($encomiendas_por_vendedor as $datos) {
            foreach ($datos as $key => $value) {
                if (!in_array($key, ['num_encomiendas', 'total_encomiendas']) && is_string($key)) {
                    if (!in_array($key, $medios_pago_unicos)) {
                        $medios_pago_unicos[] = $key;
                    }
                }
            }
        }

        // Anchos dinámicos
        $ancho_vendedor = 65;
        $ancho_ventas = 25;
        $ancho_total = 35;
        $ancho_restante = 190 - $ancho_vendedor - $ancho_ventas - $ancho_total;
        $ancho_medio = count($medios_pago_unicos) > 0 ? $ancho_restante / count($medios_pago_unicos) : 30;

        $anchos = [$ancho_vendedor, $ancho_ventas];
        foreach ($medios_pago_unicos as $medio)
            $anchos[] = $ancho_medio;
        $anchos[] = $ancho_total;

        // Encabezados
        $headers = ['Vendedor', 'N. Ventas'];
        foreach ($medios_pago_unicos as $medio)
            $headers[] = strtoupper($medio);
        $headers[] = 'Total S/.';

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetWidths($anchos);
        $pdf->Row($headers, 5, 'C', [], true);

        // Filas
        $pdf->SetFont('helvetica', '', 7);
        $total_general_cantidad = 0;
        $total_general_monto = 0.00;

        foreach ($encomiendas_por_vendedor as $vendedor => $datos) {
            $fila = [strtoupper($vendedor), $datos['num_encomiendas'] ?? 0];

            foreach ($medios_pago_unicos as $medio) {
                $monto = $datos[$medio] ?? 0.00;
                $fila[] = number_format($monto, 2, '.', '');
            }

            $total_vendedor = $datos['total_encomiendas'] ?? 0.00;
            $fila[] = number_format($total_vendedor, 2, '.', '');

            $pdf->Row($fila, 4, ['L', 'C'] + array_fill(0, count($medios_pago_unicos) + 1, 'R'), [], true);

            $total_general_cantidad += ($datos['num_encomiendas'] ?? 0);
            $total_general_monto += $total_vendedor;
        }

        // Total final
        $fila_total = ['TOTALES', $total_general_cantidad];
        foreach ($medios_pago_unicos as $medio)
            $fila_total[] = ''; // puedes calcular si quieres
        $fila_total[] = number_format($total_general_monto, 2, '.', '');

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Row($fila_total, 5, ['L', 'C'] + array_fill(0, count($medios_pago_unicos) + 1, 'R'), [], true);
    }
}

// ====================== RESUMEN DE VENTAS POR VENDEDOR ======================
$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 8);
$linea = str_repeat('=', 115);
$pdf->Cell(190, 4, $linea, 0, 1, 'C');
$pdf->Cell(190, 5, 'RESUMEN DE VENTAS POR VENDEDOR', 0, 1, 'C');
$pdf->Cell(190, 4, $linea, 0, 1, 'C');
$pdf->Ln(2);

// Nombre del vendedor actual
$vendedor_actual = trim($this->data["HEADER"]["HEADER_DETALLE"]["vendedor_nombres"] . " " .
    $this->data["HEADER"]["HEADER_DETALLE"]["vendedor_apellidos"]);

// Usar los totales precalculados del modelo cuando existan
$total_ventas_neto = $this->data["TOTALES"]["total_ventas_neto"] ?? 0.00;
$total_num_ventas_total = ($total_num_ventas ?? 0) + ($total_num_ventas_e ?? 0);

// Recolectar métodos de pago únicos y montos (por si no vienen en TOTALES)
$metodos_pago_unicos = [];
$montos_por_metodo = [];

// 1. Métodos y montos de pasajes
foreach ($this->data["DETALLE"] ?? [] as $detalle) {
    $medio = strtoupper(trim($detalle["medio_pago"] ?? ''));
    if ($medio === '')
        continue;

    if (!in_array($medio, $metodos_pago_unicos)) {
        $metodos_pago_unicos[] = $medio;
    }

    $monto_medio = 0.00;
    foreach ($detalle["pisos"] ?? [] as $piso) {
        foreach ($piso as $venta) {
            if (($venta["estado_asiento"] ?? '') !== "ANULADO") {
                $monto_medio += floatval($venta["importe_total"] ?? $venta["importe"] ?? 0);
            }
        }
    }
    $montos_por_metodo[$medio] = ($montos_por_metodo[$medio] ?? 0.00) + $monto_medio;
}

// 2. Métodos y montos de encomiendas
if (!empty($this->data["ENCOMIENDAS"])) {
    foreach ($this->data["ENCOMIENDAS"] as $item) {
        $medio = strtoupper(trim($item["medio_pago"] ?? ''));
        if ($medio === '')
            continue;

        if (!in_array($medio, $metodos_pago_unicos)) {
            $metodos_pago_unicos[] = $medio;
        }

        $monto_medio = 0.00;
        foreach ($item["encomiendas"] ?? [] as $enc) {
            if (($enc["estado"] ?? '') !== "ANULADO") {
                $monto_medio += floatval($enc["monto_total"] ?? $enc["monto"] ?? 0);
            }
        }
        $montos_por_metodo[$medio] = ($montos_por_metodo[$medio] ?? 0.00) + $monto_medio;
    }
}

// ==================== TABLA RESUMEN POR VENDEDOR ====================
$pdf->SetWidths([95, 95]); // 2 columnas
$pdf->SetFont('helvetica', 'B', 7);

$columnas = ['MÉTODO DE PAGO', $vendedor_actual];
$pdf->Row($columnas, 5, ['C', 'C'], [], true);

// Contenido
$pdf->SetFont('helvetica', '', 7.5);

$total_calculado = 0.00;

foreach ($metodos_pago_unicos as $metodo) {
    $monto = $montos_por_metodo[$metodo] ?? 0.00;
    $total_calculado += $monto;

    $fila = [
        $metodo,
        number_format($monto, 2, '.', ',')
    ];

    $pdf->Row($fila, 4.5, ['C', 'C'], [], true);
}

// FILA TOTAL VENTAS
$pdf->SetFont('helvetica', 'B', 7.5);
$fila_total_ventas = [
    'TOTAL VENTAS',
    number_format($total_calculado, 2, '.', ',')
];
$pdf->Row($fila_total_ventas, 5, ['C', 'C'], [], true);

// FILA NÚMERO DE VENTAS
$pdf->SetFont('helvetica', '', 7.5);
$fila_num_ventas = [
    'N° VENTAS TOTAL',
    $total_num_ventas_total
];
$pdf->Row($fila_num_ventas, 4.5, ['C', 'C'], [], true);

$pdf->Ln(2);
/// ============= COMISIONES POR VENDEDOR =============
$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 8);
$linea = str_repeat('=', 110);
$pdf->Cell(190, 4, $linea, 0, 1, 'C');
$pdf->Cell(190, 5, 'DETALLE DE COMISIONES DEL VENDEDOR', 0, 1, 'C');
$pdf->Cell(190, 4, $linea, 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetWidths([30, 40, 40, 40, 40]);
$pdf->SetFont('helvetica', 'B', 7.5);

$pdf->Row([
    'Tipo',
    'Cantidad',
    'Comisión aplicada',
    'Base Imponible',
    'Comisión S/.'
], 5, ['C', 'C', 'C', 'C', 'C'], [], true);

// PASAJES
$total_comisiones_pasajes = 0;
$total_base_pasajes = 0;
$cantidad_pasajes = 0;
$comision_aplicada_pasajes = '0.00%';

foreach ($this->data["DETALLE"] as $detalle) {
    foreach ($detalle["pisos"] as $piso) {
        foreach ($piso as $venta) {
            if ($venta["estado_asiento"] != "ANULADO") {

                $base_imponible = floatval($venta["precio_neto"] ?? $venta["importe_total"] ?? 0);
                $comision = floatval($venta["comision_vendedor"] ?? 0);

                $valor_comision = floatval($venta["porcentaje_comision"] ?? 0);
                $tipo_comision = $venta["tipo_comision"] ?? "PORCENTAJE";

                $comision_aplicada_pasajes = $tipo_comision === "MONTO"
                    ? "S/ " . number_format($valor_comision, 2, '.', ',')
                    : number_format($valor_comision, 2, '.', ',') . "%";

                $total_comisiones_pasajes += $comision;
                $total_base_pasajes += $base_imponible;
                $cantidad_pasajes++;
            }
        }
    }
}

$pdf->SetFont('helvetica', '', 7.5);
$pdf->Row([
    'PASAJES',
    $cantidad_pasajes,
    $comision_aplicada_pasajes,
    number_format($total_base_pasajes, 2, '.', ','),
    number_format($total_comisiones_pasajes, 2, '.', ',')
], 5, ['L', 'C', 'C', 'R', 'R'], [], true);

// ENCOMIENDAS
$total_comisiones_encomiendas = 0;
$total_base_encomiendas = 0;
$cantidad_encomiendas = 0;
$comision_aplicada_encomiendas = '0.00%';

if (!empty($this->data["ENCOMIENDAS"])) {
    foreach ($this->data["ENCOMIENDAS"] as $item) {
        foreach ($item["encomiendas"] as $enc) {
            if ($enc["estado"] != "ANULADO") {

                $base_imponible = floatval($enc["monto_total"] ?? $enc["monto"] ?? 0);
                $comision = floatval($enc["comision_vendedor"] ?? 0);

                $valor_comision = floatval($enc["porcentaje_comision"] ?? 0);
                $tipo_comision = $enc["tipo_comision"] ?? "PORCENTAJE";

                $comision_aplicada_encomiendas = $tipo_comision === "MONTO"
                    ? "S/ " . number_format($valor_comision, 2, '.', ',')
                    : number_format($valor_comision, 2, '.', ',') . "%";

                $total_comisiones_encomiendas += $comision;
                $total_base_encomiendas += $base_imponible;
                $cantidad_encomiendas++;
            }
        }
    }

    $pdf->Row([
        'ENCOMIENDAS',
        $cantidad_encomiendas,
        $comision_aplicada_encomiendas,
        number_format($total_base_encomiendas, 2, '.', ','),
        number_format($total_comisiones_encomiendas, 2, '.', ',')
    ], 5, ['L', 'C', 'C', 'R', 'R'], [], true);
}
// ==================== OBTENER TOTALES DEL MODELO (FUENTE OFICIAL) ====================
// CORRECCIÓN: Obtener todos los totales del modelo de una vez
$total_ventas_neto = $this->data["TOTALES"]["total_ventas_neto"] ?? 0;
$total_comision_vendedor = $this->data["TOTALES"]["total_comisiones"] ??
    ($total_comisiones_pasajes + $total_comisiones_encomiendas);
$comision_empresa = $this->data["TOTALES"]["comision_empresa"] ?? 0;
$porcentaje_empresa = $this->data["TOTALES"]["porcentaje_empresa"] ?? 0;
$total_egresos_generales = $this->data["TOTALES"]["total_egresos_generales"] ?? 0;

// ==================== TABLA DE EGRESOS GENERALES ====================
if (!empty($this->data["EGRESOS_GENERALES"])) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Cell(190, 6, 'EGRESOS GENERALES (LIQUIDACIÓN DEL VEHÍCULO)', 0, 1, 'C');
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetWidths([60, 20, 25, 45, 40]);
    $pdf->Row([
        "Tipo Comprobante",
        "Serie",
        "Correlativo",
        "Concepto",
        "Monto S/."
    ]);

    $pdf->SetFont('helvetica', '', 9);

    // CORRECCIÓN: Variable local para mostrar, NO reutilizar $total_egresos
    $total_mostrar_egresos = 0;

    foreach ($this->data["EGRESOS_GENERALES"] as $egreso) {
        $monto = floatval($egreso['monto'] ?? 0);
        $pdf->Row([
            $egreso['tipo_comprobante_desc'] ?? $egreso['tp_comprobante'] ?? 'N/D',
            $egreso['serie'] ?? '',
            $egreso['correlativo'] ?? '',
            $egreso['concepto'] ?? 'Sin concepto',
            number_format($monto, 2, '.', ',')
        ]);
        $total_mostrar_egresos += $monto;
    }

    // Total egresos - USAR el total calculado localmente o el del modelo
    $total_egresos_a_usar = ($total_mostrar_egresos > 0) ? $total_mostrar_egresos : $total_egresos_generales;

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Row([
        "TOTAL EGRESOS GENERALES",
        "",
        "",
        "",
        number_format($total_egresos_a_usar, 2, '.', ',')
    ]);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(2);
} else {
    // Si no hay egresos generales, usar el valor del modelo
    $total_egresos_a_usar = $total_egresos_generales;
}

// ==================== CÁLCULO MANUAL COMPLETO ====================

// 1. Calcular TOTAL VENTAS NETO (pasajes netos + encomiendas)
$total_neto_pasajes = 0;
$total_encomiendas = 0;

foreach ($this->data["DETALLE"] ?? [] as $detalle) {
    foreach ($detalle["pisos"] ?? [] as $piso) {
        foreach ($piso as $venta) {
            if (($venta["estado_asiento"] ?? '') !== "ANULADO") {
                // Usar precio_neto (importe_total - egresos_individuales)
                $neto = floatval($venta["precio_neto"] ?? $venta["importe_total"] ?? 0);
                $total_neto_pasajes += $neto;
            }
        }
    }
}

foreach ($this->data["ENCOMIENDAS"] ?? [] as $item) {
    foreach ($item["encomiendas"] ?? [] as $enc) {
        if (($enc["estado"] ?? '') !== "ANULADO") {
            $total_encomiendas += floatval($enc["monto_total"] ?? $enc["monto"] ?? 0);
        }
    }
}

$total_ventas_neto = $total_neto_pasajes + $total_encomiendas;

// 2. Calcular COMISIONES (10% según tu ejemplo)
$porcentaje_comision = 10; // Cambiar según corresponda
$total_comision_vendedor = ($total_ventas_neto * $porcentaje_comision) / 100;
$comision_empresa = ($total_ventas_neto * $porcentaje_empresa) / 100;

// 3. Egresos generales
$total_egresos_a_usar = 0;
foreach ($this->data["EGRESOS_GENERALES"] ?? [] as $egreso) {
    $total_egresos_a_usar += floatval($egreso['monto'] ?? 0);
}

// 4. Total a liquidar
$total_liquidacion = $total_ventas_neto - $total_comision_vendedor - $comision_empresa - $total_egresos_a_usar;

// Mostrar resultados
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetWidths([115, 50, 25]);

$pdf->Row(["", "TOTAL VENTAS (NETO): S/.", formatNumber($total_ventas_neto)], 5, ['L', 'R', 'C'], [1, 2], false);
$pdf->Row(["", "COMISIÓN VENDEDOR (" . $porcentaje_comision . "%): S/.", formatNumber($total_comision_vendedor)], 5, ['L', 'R', 'C'], [1, 2], false);
$pdf->Row(["", "COMISIÓN EMPRESA (" . number_format($porcentaje_empresa, 2) . "%): S/.", formatNumber($comision_empresa)], 5, ['L', 'R', 'C'], [1, 2], false);
$pdf->Row(["", "TOTAL EGRESOS: S/.", formatNumber($total_egresos_a_usar)], 5, ['L', 'R', 'C'], [1, 2], false);

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Row(["", "TOTAL A LIQUIDAR: S/.", formatNumber($total_liquidacion)], 7, ['L', 'R', 'C'], [2], false);

// Limpiar el buffer antes de enviar el PDF al navegador
ob_end_clean();

// Salida del PDF
$pdf->Output('reporte.pdf', 'I');
