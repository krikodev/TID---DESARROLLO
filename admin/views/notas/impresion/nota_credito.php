<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("WIDTH_LOGO", 30);
define("HEIGHT_LOGO", 25);
define("TEXT_SIZE_COMPROBANTE_SERIE", 9);

class TCPDF_CellFiti extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        parent::__construct('P', 'mm', array(72.1, 297), true, 'UTF-8', false);

        $this->SetMargins(1.5, 5, 1.5);
        $this->SetAutoPageBreak(true, 5);
    }

    function AutoPrint($dialog = false)
    {
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        $script = "var pp = getPrintParams();";
        $script .= $dialog ? "pp.interactive = pp.constants.interactionLevel.full;" :
            "pp.interactive = pp.constants.interactionLevel.automatic;";
        $script .= "pp.printerName = '\\\\" . $server . "\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }

    // Establecer los anchos de las columnas
    function SetWidths($w)
    {
        $this->widths = $w;
    }

    // Establecer los alineamientos de las columnas
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

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false, 0, '', '', true, 0, false, true, $lineHeight, 'M');

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

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

            if ($c == ' ') {
                $sep = $i;
            }

            $charWidth = $this->GetStringWidth($c);
            $l += $charWidth;

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

$W_UTIL = 69; // Ancho utilizable dentro del ticket

$pdf = new TCPDF_CellFiti();
$pdf->setPrintHeader(false); // Desactiva el encabezado
$pdf->SetPrintFooter(false);
$pdf->AddPage();

// CABECERA: Fondo + Logo
if (!empty($this->data['emisor']["logo"]) && file_exists($this->data['emisor']["logo"])) {
    $bgWidth = 72.1 - 3; // Ancho total menos márgenes (1.5 mm por lado)
    $bgX = 1.5;          // Posición X tomando en cuenta el margen izquierdo
    $bgY = 5;            // Posición Y donde inicias la cabecera

    // Dibujar fondo (rectángulo de ancho completo y alto 25mm)
    $pdf->SetFillColor(255, 255, 255); // Color blanco de fondo
    $pdf->Rect($bgX, $bgY, $bgWidth, 25, 'F'); // 'F' para rellenar

    // Evitar warning por imágenes defectuosas
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true; // Ignora este warning específico
        }
        return false; // Otros errores normales
    });

    // Calcular proporción para mantener la imagen dentro de 35x25 mm
    list($imgWidth, $imgHeight) = getimagesize($this->data['emisor']["logo"]);
    $scale = min(35 / $imgWidth, 25 / $imgHeight);
    $finalWidth = $imgWidth * $scale;
    $finalHeight = $imgHeight * $scale;

    // Centrar la imagen en el rectángulo
    $xLogo = (72.1 - $finalWidth) / 2;
    $yLogo = $bgY + (25 - $finalHeight) / 2;

    // Insertar imagen
    $pdf->Image(
        $this->data['emisor']["logo"],
        $xLogo,
        $yLogo,
        $finalWidth,
        $finalHeight,
        '',
        '',
        '',
        false,
        300
    );

    restore_error_handler(); // Restablecer manejo de errores normal

    $pdf->Ln(23); // Saltar después de la cabecera
}

// Datos de la empresa
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell($W_UTIL, 5, "", 0, 'C', 0, 1); // Línea en blanco

$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell($W_UTIL, 5, $this->data["emisor"]["razon_social"], 0, 'C', 0, 1);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($W_UTIL, 4, "RUC: " . $this->data["emisor"]["num_docu"], 0, 0.1, 'C');

$pdf->Ln(1);
// Dirección fiscal (esto siempre se imprime)
$pdf->SetFont('helvetica', '', 6.5);
$pdf->MultiCell($W_UTIL, 4, 'DOM. FISCAL: ' . $this->data["emisor"]['direccion_fiscal'], 0, 'C', 0, 1);

// Solo hace espacio si hubo terminales
if (!empty($this->data["TERMINALES"])) {
    $pdf->SetFont('helvetica', '', 6.5);
    foreach ($this->data["TERMINALES"] as $terminal) {
        $direccion = $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . ' - Cel: ' . $terminal['celular'];
        $pdf->MultiCell($W_UTIL, 4, $direccion, 0, 'C', 0, 0.1);
        $pdf->Ln(0.1);  // Pequeño espacio entre terminales, como tenías
    }
}

// Línea separadora (fina)
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'B');
$pdf->Ln(5);

// TIPO DE COMPROBANTE
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell($W_UTIL, 4, strtoupper($this->data["data_nota"]["desc_tp_comprobante"]), 0, 'C', 0, 1);
$pdf->Ln(2);

// SERIE Y CORRELATIVO
$correlativo = $this->data["data_nota"]["serie"] . "-" . str_pad($this->data["data_nota"]["correlativo"], 8, '0', STR_PAD_LEFT);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell($W_UTIL, 4, $correlativo, 0, 'C', 0, 1);
$pdf->Ln(2);

// Línea separadora superior
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(2);

// Fecha de emisión
$fecha_emision = !empty($this->data["data_nota"]["fecha_emision"])
    ? date("d-m-Y", strtotime($this->data["data_nota"]["fecha_emision"]))
    : date("d-m-Y");

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, 'FECHA EMISIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 5, $fecha_emision, 0, 1, 'L');

// CLIENTE
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(25, 5, 'CLIENTE:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$cliente_nombres = $this->data["cliente"]["nombres_cliente"];
$pdf->MultiCell(0, 5, $cliente_nombres, 0, 'L', 0, 1);
$pdf->Ln(-0.5);

// RUC/DNI
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, $this->data["cliente"]["tipo_doc_descripcion"] . ': ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 5, $this->data["cliente"]["num_docu"], 0, 1, 'L');

// Dirección (puede ser multilínea)
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, 'DIRECCION:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

$direccion_completa = '';
if (isset($this->data["cliente"]["direccion_completa"]) && !empty($this->data["cliente"]["direccion_completa"])) {
    $direccion_completa = $this->data["cliente"]["direccion_completa"];
} elseif (isset($this->data["cliente"]["direccion"]) && !empty($this->data["cliente"]["direccion"])) {
    $direccion_completa = $this->data["cliente"]["direccion"];

    // Agregar ubigeo si está disponible
    if (isset($this->data["cliente"]["ubigeo_depa"])) {
        $direccion_completa .= ' - ' . $this->data["cliente"]["ubigeo_distri"]
            . ', ' . $this->data["cliente"]["ubigeo_provi"]
            . ', ' . $this->data["cliente"]["ubigeo_depa"];
    }
}

$pdf->MultiCell(0, 5, $direccion_completa, 0, 'L', false, 1);

// SECCIÓN: DOCUMENTO AFECTADO Y MOTIVO

// Documento afectado
$doc_afectado = '';
if (!empty($this->data["data_nota"]["serie_ref"]) && !empty($this->data["data_nota"]["correlativo_ref"])) {
    $doc_afectado = $this->data["data_nota"]["serie_ref"] . '-' .
        str_pad($this->data["data_nota"]["correlativo_ref"], 8, '0', STR_PAD_LEFT);
} elseif (isset($this->data["venta_relacionada"]["serie"]) && isset($this->data["venta_relacionada"]["correlativo"])) {
    $doc_afectado = $this->data["venta_relacionada"]["serie"] . '-' .
        str_pad($this->data["venta_relacionada"]["correlativo"], 8, '0', STR_PAD_LEFT);
}

if (!empty($doc_afectado)) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(25, 5, 'DOC. AFECTADO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 5, $doc_afectado, 0, 1, 'L');
}

// Tipo de nota (motivo)
if (isset($this->data["motivo"]["descripcion"]) && !empty($this->data["motivo"]["descripcion"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(25, 5, 'MOTIVO NOTA:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 5, $this->data["motivo"]["descripcion"], 0, 'L', 0, 1);
}

// Descripción
if (isset($this->data["data_nota"]["descripcion"]) && !empty($this->data["data_nota"]["descripcion"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(25, 5, 'DESCRIPCIÓN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);

    // Si la descripción es larga, usar MultiCell
    $descripcion = $this->data["data_nota"]["descripcion"];
    $pdf->MultiCell(0, 5, $descripcion, 0, 'L', false, 1);
}

// ============================================
// SECCIÓN: TABLA DE DETALLES MEJORADA
// ============================================

$pdf->Ln(1);

// DEFINIR ANCHOS (optimizados para 72mm)
$w_cant = 8;    // Cantidad
$w_uni = 8;    // Unidad
$w_desc = 28;   // Descripción (ligeramente más ancho)
$w_punit = 11;   // Precio Unitario
$w_total = 11;   // Total

$ancho_total_tabla = $w_cant + $w_uni + $w_desc + $w_punit + $w_total; // 66mm

// Cabecera de tabla
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell($w_cant, 5, 'CANT', 1, 0, 'C');
$pdf->Cell($w_uni, 5, 'UNID', 1, 0, 'C');
$pdf->Cell($w_desc, 5, 'DESCRIPCION', 1, 0, 'C');
$pdf->Cell($w_punit, 5, 'P.UNIT', 1, 0, 'C');
$pdf->Cell($w_total, 5, 'TOTAL', 1, 1, 'C');

// Verificar si hay detalles
if (isset($this->data["detalles"]) && count($this->data["detalles"]) > 0) {

    foreach ($this->data["detalles"] as $index => $detalle) {

        // Obtener valores con defaults seguros
        $nombre_producto = trim($detalle['producto_nombre'] ?? 'Producto');
        $descripcion_detalle = trim($detalle['descripcion'] ?? '');
        $unidad = $detalle['unidad_medida'] ?? $detalle['unidad_medida_codigo'] ?? 'NIU';
        $cantidad = isset($detalle['cantidad']) ? number_format($detalle['cantidad'], 2) : '1.00';

        // PRECIO UNITARIO CON 2 DECIMALES
        $p_unitario = isset($detalle['precio_unitario']) ? number_format($detalle['precio_unitario'], 2) : '0.00';

        // Determinar el importe total (TOTAL) con 2 decimales
        if (isset($detalle['importe_total'])) {
            $importe_total = number_format($detalle['importe_total'], 2);
        } elseif (isset($detalle['valor_total'])) {
            $importe_total = number_format($detalle['valor_total'], 2);
        } elseif (isset($detalle['total'])) {
            $importe_total = number_format($detalle['total'], 2);
        } else {
            $valor = ($detalle['cantidad'] ?? 1) * ($detalle['precio_unitario'] ?? 0);
            $importe_total = number_format($valor, 2);
        }

        // ============================================
        // CONSTRUIR CONTENIDO HTML PARA DESCRIPCIÓN
        // ============================================
        $descripcion_html = '<span style="font-size:7pt; font-weight:normal;">';
        $descripcion_html .= htmlspecialchars($nombre_producto);

        // Agregar descripción detallada si existe y es diferente del nombre
        if (!empty($descripcion_detalle) && $descripcion_detalle != $nombre_producto) {
            if (mb_strlen($descripcion_detalle, 'UTF-8') > 60) {
                $descripcion_corta = mb_substr($descripcion_detalle, 0, 57, 'UTF-8') . '...';
                $descripcion_html .= '<br/><span style="font-size:5.5pt; color:#555;">' .
                    htmlspecialchars($descripcion_corta) . '</span>';
            } else {
                $descripcion_html .= '<br/><span style="font-size:5.5pt; color:#555;">' .
                    htmlspecialchars($descripcion_detalle) . '</span>';
            }
        }
        $descripcion_html .= '</span>';

        // ============================================
        // CALCULAR ALTURA DINÁMICA
        // ============================================

        // Guardar posición actual
        $x_inicial = $pdf->GetX();
        $y_inicial = $pdf->GetY();

        // Crear PDF temporal para medir altura
        $pdf_temp = new TCPDF_CellFiti();
        $pdf_temp->AddPage();
        $pdf_temp->SetFont('helvetica', '', 7);

        // Medir altura del contenido HTML
        $pdf_temp->writeHTMLCell($w_desc, 0, 0, 0, $descripcion_html, 0, 1, false, true, 'L', true);
        $altura_medida = $pdf_temp->GetY();

        // Altura mínima garantizada
        $altura_fila = max($altura_medida, 5);

        // Verificar si cabe en la página actual
        if ($pdf->GetY() + $altura_fila > ($pdf->getPageHeight() - 15)) {
            $pdf->AddPage();
            $x_inicial = $pdf->GetX();
            $y_inicial = $pdf->GetY();
        }
        
        // ============================================
        // DIBUJAR LA FILA (CON BORDES)
        // ============================================

        // 1. CANTIDAD
        $pdf->Cell($w_cant, $altura_fila, $cantidad, 1, 0, 'C', 0); // '0' = sin relleno

        // 2. UNIDAD
        $pdf->Cell($w_uni, $altura_fila, $unidad, 1, 0, 'C', 0);

        // 3. DESCRIPCIÓN (con HTML)
        $x_desc = $pdf->GetX();
        $y_desc = $pdf->GetY();

        $pdf->writeHTMLCell(
            $w_desc,
            $altura_fila,
            $x_desc,
            $y_desc,
            $descripcion_html,
            1,
            0,
            false,
            true,
            'L',
            true
        );

        // 4. PRECIO UNITARIO
        $pdf->SetXY($x_desc + $w_desc, $y_desc);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($w_punit, $altura_fila, $p_unitario, 1, 0, 'R', 0);

        // 5. TOTAL
        $pdf->Cell($w_total, $altura_fila, $importe_total, 1, 1, 'R', 0);

        // La posición Y ya está actualizada por Cell(..., 1, 1)
    }

} else {
    // Mensaje si no hay detalles
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell($ancho_total_tabla, 8, 'NO HAY DETALLES REGISTRADOS', 1, 1, 'C');
}

$pdf->Ln(2);

// ============================================
// SECCIÓN: RESUMEN DE TOTALES
// ============================================

// Obtener valores de la nota
$op_gravada = isset($this->data["data_nota"]["op_gravadas"]) ? (float) $this->data["data_nota"]["op_gravadas"] : 0;
$op_exonerada = isset($this->data["data_nota"]["op_exoneradas"]) ? (float) $this->data["data_nota"]["op_exoneradas"] : 0;
$op_inafecta = isset($this->data["data_nota"]["op_inafectas"]) ? (float) $this->data["data_nota"]["op_inafectas"] : 0;
$igv = isset($this->data["data_nota"]["igv"]) ? (float) $this->data["data_nota"]["igv"] : 0;
$total = isset($this->data["data_nota"]["total"]) ? (float) $this->data["data_nota"]["total"] : 0;

// También podemos calcular desde los detalles si es necesario
$total_calculado = 0;
if (isset($this->data["detalles"]) && count($this->data["detalles"]) > 0) {
    foreach ($this->data["detalles"] as $detalle) {
        $importe = isset($detalle['importe_total']) ? (float) $detalle['importe_total'] :
            (isset($detalle['valor_total']) ? (float) $detalle['valor_total'] : 0);
        $total_calculado += $importe;
    }

    // Si el total de la nota es 0 pero calculamos desde detalles
    if ($total == 0 && $total_calculado > 0) {
        $total = $total_calculado;
    }
}

// Mostrar totales
$pdf->SetFont('helvetica', '', 8);

// OP GRAVADA
$pdf->Cell(30, 4, 'OP GRAVADA:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 4, 'S/ ' . number_format($op_gravada, 2), 0, 1, 'R');

// OP EXONERADA
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(30, 4, 'OP EXONERADA:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 4, 'S/ ' . number_format($op_exonerada, 2), 0, 1, 'R');

// OP INAFECTA
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(30, 4, 'OP INAFECTA:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 4, 'S/ ' . number_format($op_inafecta, 2), 0, 1, 'R');

// IGV
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(30, 4, 'IGV:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(0, 4, 'S/ ' . number_format($igv, 2), 0, 1, 'R');

// Línea separadora antes del total
$pdf->SetLineWidth(0.2);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(0.5);

// TOTAL PAGAR (en negrita y más grande)
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 4, 'IMPORTE:', 0, 0, 'L');
$pdf->Cell(0, 4, 'S/ ' . number_format($total, 2), 0, 1, 'R');

$pdf->Ln(1);

// ============================================
// MONTO EN LETRAS (AGREGADO)
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$letras = 'SON: ';
$pdf->SetFont('helvetica', '', 8);

// Verificar que la función numtoletras existe
if (function_exists('numtoletras')) {
    // Usar el total calculado (priorizar $total_calculado si es > 0)
    $monto_letras = $total_calculado > 0 ? $total_calculado : $total;
    $letras .= numtoletras(number_format($monto_letras, 2, '.', ''));
} else {
    $letras .= '[FUNCIÓN numtoletras NO ENCONTRADA]';
    error_log("Error: Función numtoletras no encontrada en nota_credito.php");
}

$pdf->MultiCell($W_UTIL, 5, $letras, 0, 'L');
$pdf->Ln(0.5);
// ============================================

// SECCIÓN: HASH CPE CON BORDE
if (!empty($this->data["data_nota"]["hash_cpe"])) {
    $hash_cpe = $this->data["data_nota"]["hash_cpe"];

    $pdf->Ln(1);

    // Dibujar caja/cuadro alrededor del hash
    $boxX = 1.5; // Margen izquierdo
    $boxY = $pdf->GetY();
    $boxWidth = $W_UTIL;
    $boxHeight = 12; // Altura de la caja

    $pdf->SetLineWidth(0.2);
    $pdf->Rect($boxX, $boxY, $boxWidth, $boxHeight);

    // Título dentro de la caja
    $pdf->SetXY($boxX, $boxY + 1);
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->Cell($boxWidth, 3, 'CODIGO HASH (CPE)', 0, 1, 'C');

    // Hash centrado dentro de la caja
    $pdf->SetXY($boxX, $boxY + 5);
    $pdf->SetFont('helvetica', '', 5.5);

    // Mostrar hash completo o dividido
    if (strlen($hash_cpe) <= 60) {
        $pdf->Cell($boxWidth, 4, $hash_cpe, 0, 1, 'C');
    } else {
        // Dividir en dos líneas
        $part1 = substr($hash_cpe, 0, 60);
        $part2 = substr($hash_cpe, 60);

        $pdf->Cell($boxWidth, 3, $part1, 0, 1, 'C');
        $pdf->SetX($boxX);
        $pdf->Cell($boxWidth, 3, $part2, 0, 1, 'C');
    }

    $pdf->SetY($boxY + $boxHeight + 2);
}

$pdf->Output('ticket.pdf', 'I');