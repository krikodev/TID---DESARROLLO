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

    // Impresión automática
    public function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $this->IncludeJS("print($param);");
    }

    public function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        $script = "var pp = getPrintParams();";
        $script .= $dialog
            ? "pp.interactive = pp.constants.interactionLevel.full;"
            : "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
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
        if (!is_array($boldColumns)) {
            $boldColumns = [];
        }

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

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = is_array($align) && isset($align[$i]) ? $align[$i] : (is_string($align) ? $align : 'L');
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

        $this->SetY($y + $h);
    }

    // Cálculo de líneas (versión más segura)
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
$pdf->SetPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

$ventas_por_vendedor = [];
$encomiendas_por_vendedor = [];

$total_num_ventas_e = 0;
$total_ventas_e = 0.00;

$total_num_ventas = 0;
$total_ventas = 0.00;
$total_porcentaje_pa = 0.00;
$total_porcentaje_en = 0.00;

$total_general = 0;
$total_monto = 0;

// Parámetros del logo
$xLogo = 10;
$yLogo = 10;

// Inicializa variables para evitar undefined
$imageData = null;  // Asegura que $imageData sea null si no se carga
$hLogo = 10;  // Valor predeterminado para espacio vertical

// Obtener ruta del logo desde el array
$logoPath = isset($this->data["HEADER"]["HEADER_EMPRESA"]["logo"]) ? $this->data["HEADER"]["HEADER_EMPRESA"]["logo"] : '';

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
    }

    restore_error_handler();
} else {
}

// Posicionar título al lado del logo (o más centrado si no hay logo)
$logoWidth = $imageData['width'] ?? 0;  // Usa 0 si no hay imagen
$xTitle = $xLogo + $logoWidth + 5;
if ($logoWidth === 0) {
    $xTitle = ($pdf->getPageWidth() - 120) / 2;  // Centra el Cell de 120mm en la página (ancho total ~190mm)
}
$yTitle = $yLogo + 2;
$pdf->SetXY($xTitle, $yTitle);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(120, 6, "LIQUIDACION/SEGUIMIENTO GENERAL DE PROGRAMACIÓN", 0, 1, 'C');

$pdf->Ln($hLogo - 5); // Ajustado el espacio después del encabezado

// DATOS DEL VIAJE (ESTILO UNIFORME)

// Reducir tamaño de fuente para más espacio
$pdf->SetFont('helvetica', '', 7.5);

// 1. Fecha, Hora, Placa
$pdf->SetWidths([13, 85, 9, 14, 12, 18, 14, 25]);
$pdf->Row([
    "Piloto:",
    $this->data["HEADER"]["HEADER_DETALLE"]["nombres_conductor"] . " " . $this->data["HEADER"]["HEADER_DETALLE"]["apellidos_conductor"],
    "Hora:",
    date("h:i A", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_hora_salida"])),
    "Fecha:",
    date("d-m-Y", strtotime($this->data["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"])),
    "Placa:",
    $this->data["HEADER"]["HEADER_DETALLE"]["vehiculo_placa"]
], 5, 'L', [0, 1, 2, 4, 6], false);

// 2. Origen, Destino
$pdf->SetWidths(array(13, 85, 13, 79));
$pdf->Row(array(
    "Origen:",
    $this->data["HEADER"]["HEADER_DETALLE"]["terminal_origen"],
    "Destino:",
    $this->data["HEADER"]["HEADER_DETALLE"]["terminal_destino"]
), 5, 'L', [0, 2], false);

$pdf->Ln(3); // Espacio tras la tabla

// Función auxiliar para formato numérico
function formatNumber($number)
{
    // Asegurar que $number sea un string o número, nunca null
    $number = $number ?? 0; // Si es null, usa 0
    return str_pad(number_format(floatval($number), 2, '.', ','), 8, ' ', STR_PAD_LEFT);
}

// Inicio de la logica que controla el itpo deporgramacion

if (!empty($this->data["DETALLE"])) {
    // ENCABEZADO DE LA TABLA (estilo compacto)
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetWidths(array(12, 60, 15, 20, 25, 23, 20, 15));
    $pdf->Row(array(
        "Asiento",
        "Cliente",
        "Nro. Doc.",
        "Estado",
        "S-N Comprobante",
        "Destino",
        "Vendedor(@)",
        "Importe",
    ), 4, 'C');

    // Recolectar métodos de pago únicos y normalizados
    $metodos_pago_unicos = [];
    foreach ($this->data["DETALLE"] as $detalle) {
        $medio_pago_normalizado = strtoupper(trim($detalle["medio_pago"]));
        if (!in_array($medio_pago_normalizado, $metodos_pago_unicos)) {
            $metodos_pago_unicos[] = $medio_pago_normalizado;
        }
    }

    for ($i = 0; $i < count($this->data["DETALLE"]); $i++) {
        $medio_pago = $this->data["DETALLE"][$i]["medio_pago"];

        // Título del medio de pago
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(190, 0, $medio_pago, 0, 1, 'C');
        $pdf->Ln(1.5);

        $total_ventas_medioPago = 0.00;
        $total_venta_pa = 0.00;
        $num_ventas = 0;

        for ($x = 0; $x < count($this->data["DETALLE"][$i]["pisos"]); $x++) {
            $piso = $this->data["DETALLE"][$i]["pisos"][$x][0]["num_piso"];

            // Encabezado del piso
            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->Cell(190, 0, str_repeat('_ ', 40) . " Piso: $piso " . str_repeat(' _', 40), 0, 1, 'C');
            $pdf->Ln(1.5);

            $ventas_piso = $this->data["DETALLE"][$i]["pisos"][$x];
            $num_ventas += count($ventas_piso);
            $total_num_ventas += count($ventas_piso);

            foreach ($ventas_piso as $venta) {
                $vendedor = $venta["vendedor"];
                $vendedor_firstName = explode(' ', trim($vendedor))[0];
                $importe = ($venta["estado_asiento"] != "ANULADO") ? floatval($venta["importe"]) : 0.00;
                $porcent_venta = floatval($venta['porcentaje_venta']);

                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetWidths(array(12, 60, 15, 20, 25, 23, 20, 15));
                $pdf->Row(array(
                    $venta["num_asiento"],
                    $venta["cliente_nombres"] . " " . $venta["cliente_apellidos"],
                    $venta["cliente_num_docu"],
                    $venta["estado_asiento"],
                    $venta["venta_serie"] . " - " . str_pad($venta["venta_correlativo"], 8, "0", STR_PAD_LEFT),
                    $venta["terminal_destino"] ?? '',
                    $vendedor_firstName,
                    number_format($importe, 2, '.', '')
                ), 3.5, array('C', 'L', 'C', 'C', 'C', 'C', 'C', 'R'), [], false);

                if (!isset($ventas_por_vendedor[$vendedor])) {
                    $ventas_por_vendedor[$vendedor] = array(
                        'nombre' => $vendedor,
                        'num_ventas' => 0,
                        'total_ventas' => 0.00,
                        'metodos' => []
                    );
                }

                if ($venta["estado_asiento"] != "ANULADO") {
                    $total_ventas_medioPago += $importe;
                    $total_venta_pa += $porcent_venta;
                    $ventas_por_vendedor[$vendedor]['num_ventas']++;
                    $ventas_por_vendedor[$vendedor]['total_ventas'] += $importe;
                    $medio_pago_normalizado = strtoupper(trim($medio_pago));

                    if (!isset($ventas_por_vendedor[$vendedor]['metodos'][$medio_pago_normalizado])) {
                        $ventas_por_vendedor[$vendedor]['metodos'][$medio_pago_normalizado] = 0.00;
                    }
                    $ventas_por_vendedor[$vendedor]['metodos'][$medio_pago_normalizado] += $importe;
                }
            }
        }

        $total_porcentaje_pa += $total_venta_pa;
        $total_ventas += $total_ventas_medioPago;

        // RESUMEN DE MEDIO DE PAGO
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetWidths(array(25, 115, 30, 20));
        $pdf->Row(array(
            "N VENTAS:",
            $num_ventas,
            "TOTAL: S/:",
            number_format($total_ventas_medioPago, 2, '.', '')
        ), 4, array('L', 'L', 'L', 'R'), [0, 2], false);
    }

    // Línea divisoria superior
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(0.8);

    // Fila resumen de ventas
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetWidths(array(25, 115, 30, 20));
    $pdf->Row(
        array(
            "N VENTAS:",
            $total_num_ventas,
            "TOTAL: S/:",
            number_format($total_ventas, 2, '.', '')
        ),
        3.5,
        array('L', 'L', 'L', 'R'),
        array(0, 2),
        false
    );

    // Línea divisoria inferior
    $pdf->Ln(0.8);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    // Obtener los nombres de todos los vendedores
    $vendedores = [];
    foreach ($ventas_por_vendedor as $v) {
        $vendedores[] = $v['nombre'];
    }

    // Espaciado y título
    $pdf->Ln(1.5);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 5, 'TABLA DE TOTALES POR VENDEDOR', 0, 1, 'C');
    $pdf->Ln(1);

    // Construir los encabezados dinámicamente
    $columnas = array_merge(['MÉTODO DE PAGO'], $vendedores, ['TOTAL MÉTODO']);
    $ancho_columna = 190 / count($columnas); // ancho proporcional
    $pdf->SetWidths(array_fill(0, count($columnas), $ancho_columna));
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Row($columnas, 5, array_fill(0, count($columnas), 'C'));

    // Contenido dinámico por método de pago
    $pdf->SetFont('helvetica', '', 7.5);
    if (!empty($metodos_pago_unicos)) {
        foreach ($metodos_pago_unicos as $metodo) {
            $fila = [$metodo];
            $total_metodo = 0;
            foreach ($vendedores as $vendedor) {
                $monto = $ventas_por_vendedor[$vendedor]['metodos'][$metodo] ?? 0.00;
                $fila[] = number_format($monto, 2, '.', ',');
                $total_metodo += $monto;
            }
            $fila[] = number_format($total_metodo, 2, '.', ',');
            $pdf->Row($fila, 4.5, array_fill(0, count($fila), 'C'), [], false);
        }
    } else {
        $pdf->Cell(190, 5, 'NO HAY MÉTODOS DE PAGO DISPONIBLES', 0, 1, 'C');
    }

    // Línea separadora
    $pdf->Ln(1);
    $pdf->Line(
        $pdf->GetX(),
        $pdf->GetY(),
        $pdf->getPageWidth() - $pdf->getMargins()['right'],
        $pdf->GetY()
    );
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', 7.5);

    // FILA: TOTAL DE VENTAS
    $fila_total_ventas = ['TOTAL DE VENTAS'];
    foreach ($vendedores as $vendedor) {
        $total = $ventas_por_vendedor[$vendedor]['total_ventas'] ?? 0;
        $fila_total_ventas[] = number_format($total, 2, '.', ',');
    }
    $fila_total_ventas[] = number_format(array_sum(array_column($ventas_por_vendedor, 'total_ventas')), 2, '.', ',');
    $pdf->Row($fila_total_ventas, 4.5, array_fill(0, count($fila_total_ventas), 'C'), [], false);

    // Línea separadora
    $pdf->Ln(1);
    $pdf->Line(
        $pdf->GetX(),
        $pdf->GetY(),
        $pdf->getPageWidth() - $pdf->getMargins()['right'],
        $pdf->GetY()
    );
    $pdf->Ln(1);

    // FILA: NÚMERO DE VENTAS
    $fila_num_ventas = ['NÚMERO DE VENTAS'];
    foreach ($vendedores as $vendedor) {
        $fila_num_ventas[] = $ventas_por_vendedor[$vendedor]['num_ventas'] ?? 0;
    }
    $fila_num_ventas[] = array_sum(array_column($ventas_por_vendedor, 'num_ventas'));
    $pdf->Row($fila_num_ventas, 4.5, array_fill(0, count($fila_num_ventas), 'C'), [], false);

    // Línea separadora
    $pdf->Ln(1);
    $pdf->Line(
        $pdf->GetX(),
        $pdf->GetY(),
        $pdf->getPageWidth() - $pdf->getMargins()['right'],
        $pdf->GetY()
    );
    $pdf->Ln(1);

    // --- NUEVA FILA: COMISIÓN POR VENDEDOR ---
    // Recalculamos las comisiones por vendedor (esto podría hacerse más arriba para optimizar)
    $comisiones_por_vendedor = [];
    foreach ($this->data["DETALLE"] as $medioPago) {
        foreach ($medioPago["pisos"] as $piso) {
            foreach ($piso as $venta) {
                $vendedor = $venta["vendedor"];
                $comision = isset($venta["comision_vendedor"]) ? floatval($venta["comision_vendedor"]) : 0;

                if (!isset($comisiones_por_vendedor[$vendedor])) {
                    $comisiones_por_vendedor[$vendedor] = 0;
                }
                $comisiones_por_vendedor[$vendedor] += $comision;
            }
        }
    }

    // Crear la fila de comisiones
    $fila_comisiones = ['COMISIÓN'];
    $total_comisiones_general = 0;
    foreach ($vendedores as $vendedor) {
        $comision = $comisiones_por_vendedor[$vendedor] ?? 0;
        $fila_comisiones[] = number_format($comision, 2, '.', ',');
        $total_comisiones_general += $comision;
    }
    $fila_comisiones[] = number_format($total_comisiones_general, 2, '.', ',');

    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Row($fila_comisiones, 4.5, array_fill(0, count($fila_comisiones), 'C'), [], false);
    $pdf->Ln(2);
}

// APARTADO DE ENCOMIENDAS 
if (!empty($this->data["ENCOMIENDAS"])) {
    // Línea divisoria
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    // Título de sección
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(190, 6, 'ENCOMIENDAS', 0, 1, 'C');
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
    $pdf->Ln(3); // Espacio tras la tabla

    // ENCABEZADO DE LA TABLA (estilo compacto)
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetWidths([20, 30, 40, 40, 40, 23, 20]); // Cambiado el orden de las últimas dos columnas
    $pdf->Row([
        "S-C",
        "Descripción",
        "Remitente",
        "Destinatario",
        "Vendedor(@)", // Ahora antes que Importe
        "Importe",
    ], 4, 'C');

    // Inicializa tu gran total antes del loop de medios

    foreach ($this->data["ENCOMIENDAS"] as $item) {
        $medio_pago = $item["medio_pago"];
        $encomiendas = $item["encomiendas"];
        $total_mp = 0.00;
        $conteo_mp = 0;

        // --- Título del medio de pago ---
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
        $pdf->Cell(190, 0, $medio_pago, 0, 1, 'C');
        $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');
        $pdf->Ln(1.5);

        // --- Encabezados de columnas (si los necesitas) ---
        // Por ejemplo: ID, Remitente, DNI Remitente, Destinatario, DNI Destinatario, Estado, Comprobante, Vendedor, Importe
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetWidths([20, 30, 40, 40, 40, 23, 20]);

        // --- Filas de cada encomienda ---
        $pdf->SetFont('helvetica', '', 7);
        foreach ($encomiendas as $enc) {
            $vendedor = $enc["vendedor"];
            $porcent_encomienda = floatval($enc['porcentaje_venta']);

            // Fecha, series u otros campos, si los necesitas, añádelos aquí
            $importe = ($enc["estado"] !== "ANULADO")
                ? floatval($enc["monto"] ?? 0.00)  // ajusta "monto" o el campo correcto
                : 0.00;

            $pdf->Row([
                $enc["comprobante"],
                strtoupper($enc["producto"]),
                $enc["remitente"] . '-' . $enc["num_docu_remitente"],
                $enc["destinatario"] . '-' . $enc["num_docu_destinatario"],
                $enc["vendedor"],
                number_format($importe, 2, '.', '')
            ], 3.5, ['C', 'L', 'C', 'L', 'C', 'C', 'C', 'R'], [], false);

            if (!isset($encomiendas_por_vendedor[$vendedor])) {
                $encomiendas_por_vendedor[$vendedor] = [
                    'num_encomiendas' => 0,
                    'total_encomiendas' => 0.00
                ];
            }

            if (!isset($encomiendas_por_vendedor[$vendedor][$medio_pago])) {
                $encomiendas_por_vendedor[$vendedor][$medio_pago] = 0.00;
            }

            if ($enc["estado"] !== "ANULADO") {
                $importe = floatval($importe); // Ya está, pero aseguro

                $encomiendas_por_vendedor[$vendedor][$medio_pago] += $importe;
                $encomiendas_por_vendedor[$vendedor]['num_encomiendas']++;
                $encomiendas_por_vendedor[$vendedor]['total_encomiendas'] += $importe;

                $total_porcentaje_en += $porcent_encomienda;
                $total_mp += $importe;
                $conteo_mp++;
            }
        }

        // --- Resumen del medio de pago ---
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetWidths([30, 30, 87, 30, 20]);

        $pdf->Row([
            "TOTAL VENTAS :",
            $conteo_mp,
            "",
            "TOTAL " . $medio_pago . "  S/:",
            number_format($total_mp, 2, '.', '')
        ], 4, ['L', 'C', 'L', 'C'], [0, 2], false);

        $total_ventas_e += $total_mp;
        $total_num_ventas_e += $conteo_mp;
    }

    // Línea divisoria superior
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    // Espaciado más compacto
    $pdf->Ln(0.8);

    // Fila resumen de ventas (alineaciones personalizadas)
    $pdf->SetFont('helvetica', 'B', 8); // Tamaño más compacto
    $pdf->SetWidths([25, 115, 30, 20]);
    $pdf->Row(
        [
            "N VENTAS:",
            $total_num_ventas_e,
            "TOTAL: S/:",
            number_format($total_ventas_e, 2, '.', '')
        ],
        4,
        ['L', 'L', 'L', 'R'], // Alineaciones por columna
        [0, 2, 1, 3], // Columnas en negrita (opcional según tu clase)
        false
    );
    // Línea divisoria inferior más pegada
    $pdf->Ln(0.8);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    // Espaciado y título
    $pdf->Ln(1.5);
    // TABLA DE TOTALES POR VENDEDOR
    $pdf->Ln(5);

    // Título con líneas decorativas
    $pdf->SetFont('helvetica', 'B', 8);
    $linea = str_repeat('=', 110);
    $pdf->Cell(190, 4, $linea, 0, 1, 'C');
    $pdf->Cell(190, 5, 'TABLA DE TOTALES POR VENDEDOR', 0, 1, 'C');
    $pdf->Cell(190, 4, $linea, 0, 1, 'C');
    $pdf->Ln(2);

    // Obtener todos los medios de pago únicos para las columnas
    $medios_pago_unicos = [];
    foreach ($encomiendas_por_vendedor as $vendedor => $datos) {
        foreach ($datos as $key => $value) {
            if ($key !== 'num_encomiendas' && $key !== 'total_encomiendas') {
                if (!in_array($key, $medios_pago_unicos)) {
                    $medios_pago_unicos[] = $key;
                }
            }
        }
    }

    // Calcular anchos dinámicos para ocupar todo el ancho de página (190)
    $total_columnas = 2 + count($medios_pago_unicos) + 1; // Vendedor + N.Ventas + medios + Total
    $ancho_vendedor = 60;
    $ancho_ventas = 25;
    $ancho_total = 35;
    $ancho_restante = 190 - $ancho_vendedor - $ancho_ventas - $ancho_total;
    $ancho_medio_pago = $ancho_restante / count($medios_pago_unicos);

    // Crear array de anchos
    $anchos = [$ancho_vendedor, $ancho_ventas];
    foreach ($medios_pago_unicos as $medio) {
        $anchos[] = $ancho_medio_pago;
    }
    $anchos[] = $ancho_total;

    // Encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);

    $headers = ['Vendedor', 'N. Ventas'];
    foreach ($medios_pago_unicos as $medio) {
        $headers[] = strtoupper($medio);
    }
    $headers[] = 'Total Ventas S/.';

    $pdf->SetWidths($anchos);
    $pdf->Row($headers, 5, 'C', [1, 1, 1, 1, 1, 1], true);

    // Variables para totales
    $total_general_cantidad = 0;
    $total_general_monto = 0.00;
    $totales_por_medio = [];

    // Inicializar totales por medio de pago
    foreach ($medios_pago_unicos as $medio) {
        $totales_por_medio[$medio] = 0.00;
    }

    // Filas de datos
    $pdf->SetFont('helvetica', '', 7);
    foreach ($encomiendas_por_vendedor as $vendedor => $datos) {
        $fila_datos = [strtoupper($vendedor), $datos['num_encomiendas']];

        // Agregar montos por cada medio de pago
        foreach ($medios_pago_unicos as $medio) {
            $monto = isset($datos[$medio]) ? floatval($datos[$medio]) : 0.00;
            $fila_datos[] = number_format($monto, 2, '.', '');
            $totales_por_medio[$medio] += $monto;
        }

        // Total del vendedor
        $total_vendedor = floatval($datos['total_encomiendas']);
        $fila_datos[] = number_format($total_vendedor, 2, '.', '');

        // Alineaciones
        $alineaciones = ['L', 'C'];
        for ($i = 0; $i < count($medios_pago_unicos); $i++) {
            $alineaciones[] = 'R';
        }
        $alineaciones[] = 'R';

        // Mostrar fila con bordes
        $pdf->Row($fila_datos, 4, $alineaciones, [1, 1, 1, 1, 1, 1], true);

        // Acumular totales generales
        $total_general_cantidad += $datos['num_encomiendas'];
        $total_general_monto += $total_vendedor;
    }

    // Línea separadora antes de totales
    $pdf->SetFont('helvetica', 'B', 7);

    // Fila de totales
    $fila_totales = ['TOTALES', $total_general_cantidad];
    foreach ($medios_pago_unicos as $medio) {
        $fila_totales[] = number_format($totales_por_medio[$medio], 2, '.', '');
    }
    $fila_totales[] = number_format($total_general_monto, 2, '.', '');

    $alineaciones_totales = ['L', 'C'];
    for ($i = 0; $i < count($medios_pago_unicos); $i++) {
        $alineaciones_totales[] = 'R';
    }
    $alineaciones_totales[] = 'R';

    $pdf->Row($fila_totales, 5, $alineaciones_totales, [1, 1, 1, 1, 1, 1], true);

    // Línea inferior
    $pdf->Ln(2);

    // Definir encabezados fijos
    $columnas = ['MÉTODO DE PAGO', 'TOTAL MÉTODO'];
    $pdf->SetWidths([95, 95]);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->Row($columnas, 5, ['C', 'C']);

    // Contenido
    $pdf->SetFont('Helvetica', '', 7.5);

    if (!empty($medios_pago_unicos) && !empty($encomiendas_por_vendedor)) {
        foreach ($medios_pago_unicos as $metodo) {
            $suma_mp = 0;

            foreach ($encomiendas_por_vendedor as $data) {
                if (isset($data[$metodo])) {
                    $suma_mp += floatval($data[$metodo]);
                }
            }

            $total_general += $suma_mp;

            $fila = [
                $metodo,
                number_format($suma_mp, 2, '.', ',')
            ];

            $pdf->Row($fila, 4.5, ['C', 'C'], [], false);
        }

        // Fila de resumen final
        $fila_resumen = [
            'TOTAL GENERAL',
            number_format($total_general, 2, '.', ',')
        ];
        $pdf->SetFont('Helvetica', 'B', 7.5);
        $pdf->Row($fila_resumen, 5, ['C', 'C'], [], false);
    } else {
        $pdf->Row(['NO HAY DATOS', ''], 5, ['C', 'C']);
    }
}

// EGRESOS (si existen)
if (!empty($this->data["EGRESOS"])) {
    // Separación mínima
    $pdf->Ln(1.5);

    // Línea divisoria
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

    // Título sección egresos
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(190, 5, 'TABLA DE EGRESOS', 0, 1, 'C');

    // Encabezado
    $pdf->SetFont('Helvetica', 'B', 7.5);
    $pdf->SetWidths([60, 20, 25, 45, 40]);
    $pdf->Row(
        ["Tipo comprobante", "Serie", "Correlativo", "Concepto", "Monto S/."],
        4,
        ['C', 'C', 'C', 'C', 'C']
    );

    // Contenido
    $pdf->SetFont('Helvetica', '', 7.5);
    foreach ($this->data["EGRESOS"] as $egreso) {
        $pdf->Row([
            $egreso['tp_comprobante'],
            $egreso['serie'],
            $egreso['correlativo'],
            $egreso['concepto'],
            number_format($egreso['monto'], 2, '.', ',')
        ], 4, ['L', 'C', 'C', 'L', 'C'], '', false);
        $total_monto += floatval($egreso['monto']);
    }

    // Total de egresos
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Row(
        ["TOTALES", "", "", "", number_format($total_monto, 2, '.', ',')],
        4,
        'C',
        '',
        false
    );
}

$total_porcentaje_li = $total_porcentaje_pa + $total_porcentaje_en;

// Variables para los totales
$total_todo = $total_ventas_e + $total_ventas;

// Separación final mínima
$pdf->Ln(2);

// Línea divisoria
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

// Título de sección
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->Cell(190, 6, 'TOTALES GENERALES', 0, 1, 'C');
$pdf->Ln(1);

// Ajuste de columnas
$col1 = 110;
$col2 = 60;
$col3 = 20;

// Obtener comisión
$total_comision = $this->data["HEADER"]["HEADER_DETALLE"]["comision"] ?? 0;

// Fuente general
$pdf->SetFont('Helvetica', '', 8);

// Total ventas
$pdf->SetWidths([$col1, $col2, $col3]);
$pdf->Row([
    "",
    "TOTAL VENTAS: S/",
    formatNumber($total_todo)
], 5, ['L', 'L', 'C'], [2], false);

$pdf->Row([
    "",
    "TOTAL COMISIONES: S/",
    formatNumber($total_porcentaje_li)
], 5, ['L', 'L', 'C'], [2], false);

// Comisión vehículo
$pdf->Row([
    "",
    "COMISION VEHICULO: S/",
    formatNumber($total_comision)
], 5, ['L', 'L', 'C'], [2], false);

// Total egresos
$pdf->Row([
    "",
    "TOTAL EGRESOS: S/",
    formatNumber($total_monto)
], 5, ['L', 'L', 'C'], [2], false);

if ($this->data['LIQUIDACIONES']) {
    $total_comision_empresa = 0;
    foreach ($this->data['LIQUIDACIONES'] as $liquidacion) {
        $total_comision_empresa += $liquidacion['comision_empresa'];
    }
    // Total egresos
    $pdf->Row([
        "",
        "TOTAL COMISION EMPRESA: S/",
        formatNumber($total_comision_empresa)
    ], 5, ['L', 'L', 'C'], [2], false);
}

// Cálculo de total general
$total_e = $total_monto + $total_comision + $total_porcentaje_li;
$total_general_t = $total_todo - $total_e;

// Línea final y resultado total
$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(190, 0, str_repeat('=', 115), 0, 1, 'C');

$pdf->Ln(1);
$pdf->SetWidths([$col1, $col2, $col3]);
$pdf->Row([
    "",
    "TOTAL LIQUIDACION: S/",
    formatNumber($total_general_t)
], 5, ['L', 'L', 'C'], [1, 2], false);


// Salida del PDF
$pdf->Output('reporte.pdf', 'I');
