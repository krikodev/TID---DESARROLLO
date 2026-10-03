<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("TEXT_SIZE_COMPROBANTE_SERIE", 10); // Un poco más grande para A4

class TCPDF_A4 extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);

        // Márgenes más amplios para A4
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 10);
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

    function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [])
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

// Ancho útil en A4 (210mm - márgenes izquierdo y derecho)
$W_UTIL = 190; // 210 - 10 - 10 = 180 mm

$pdf = new TCPDF_A4();
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();

// CABECERA: Logo, datos de empresa y tipo de comprobante (tres columnas)
$pdf->Ln(0); // Espacio superior

// Definir anchos para las tres columnas
$col_logo = 50;    // Ancho para el logo
$col_centro = 90;  // Ancho para datos de empresa
$col_derecha = 50; // Ancho para tipo de comprobante
$total_ancho = $col_logo + $col_centro + $col_derecha;

// Posición inicial
$startY = $pdf->GetY();

// ========== COLUMNA IZQUIERDA: LOGO ==========
// CORRECCIÓN: Acceder correctamente a los datos del emisor
$logo_path = '';
if (isset($this->view->data['emisor']['logo'])) {
    $logo_path = $this->view->data['emisor']['logo'];
} elseif (isset($data['emisor']['logo'])) {
    $logo_path = $data['emisor']['logo'];
} elseif (isset($this->data['emisor']['logo'])) {
    $logo_path = $this->data['emisor']['logo'];
}

if (!empty($logo_path) && file_exists($logo_path)) {
    // Manejo de warning de imágenes
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true;
        }
        return false;
    });

    list($imgWidth, $imgHeight) = @getimagesize($logo_path);
    
    if ($imgWidth && $imgHeight) {
        // Calcular dimensiones para encajar en el espacio de la columna
        $maxWidth = $col_logo - 10; // 5mm de margen interno
        $maxHeight = 40; // Altura máxima para el logo
        
        // Calcular escala manteniendo proporción
        $scaleW = $maxWidth / $imgWidth;
        $scaleH = $maxHeight / $imgHeight;
        $scale = min($scaleW, $scaleH);
        
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;
        
        // Centrar verticalmente en la columna
        $xLogo = 10; // Margen izquierdo
        $yLogo = $startY + (($maxHeight - $finalHeight) / 2) - 10;
        
        $pdf->Image(
            $logo_path,
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
    }
    
    restore_error_handler();
}

// ========== COLUMNA CENTRAL: DATOS DE LA EMPRESA ==========
$pdf->SetY($startY);
$pdf->SetX($col_logo + 5); // Posición después del logo

// CORRECCIÓN: Acceder correctamente a los datos
$emisor = isset($this->view->data['emisor']) ? $this->view->data['emisor'] : 
          (isset($data['emisor']) ? $data['emisor'] : 
          (isset($this->data['emisor']) ? $this->data['emisor'] : []));

// Nombre de la empresa
$pdf->SetFont('helvetica', 'B', 10);
$nombre_empresa = isset($emisor['razon_social']) ? $emisor['razon_social'] : 'EMPRESA NO CONFIGURADA';
$pdf->MultiCell($col_centro, 7, $nombre_empresa, 0, 'L', 0, 1);

// RUC
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($col_logo + 5);
$ruc = isset($emisor['num_docu']) ? "RUC: " . $emisor['num_docu'] : "RUC: NO CONFIGURADO";
$pdf->Cell($col_centro, 6, $ruc, 0, 1, 'L');

// Dirección fiscal
$pdf->SetFont('helvetica', '', 8);
$pdf->SetX($col_logo + 5);
$direccion_fiscal = isset($emisor['direccion_fiscal']) ? 
                   'DOM. FISCAL: ' . $emisor['direccion_fiscal'] : 
                   'DOM. FISCAL: NO CONFIGURADO';
$pdf->MultiCell($col_centro, 5, $direccion_fiscal, 0, 'L', 0, 1);

// Terminales (si existen)
if (isset($this->view->data["TERMINALES"]) && !empty($this->view->data["TERMINALES"])) {
    $pdf->SetFont('helvetica', '', 8);
    foreach ($this->view->data["TERMINALES"] as $terminal) {
        $pdf->SetX($col_logo + 5);
        $direccion = $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . ' - Cel: ' . $terminal['celular'];
        $pdf->MultiCell($col_centro, 4, $direccion, 0, 'C', 0, 1);
    }
}

// ========== COLUMNA DERECHA: TIPO DE COMPROBANTE EN CUADRO ==========
$pdf->SetY($startY);
$pdf->SetX($col_logo + $col_centro + 10); // Posición después del centro

// CORRECCIÓN: Acceder correctamente a los datos de la nota
$data_nota = isset($this->view->data['data_nota']) ? $this->view->data['data_nota'] : 
            (isset($data['data_nota']) ? $data['data_nota'] : 
            (isset($this->data['data_nota']) ? $this->data['data_nota'] : []));

// Calcular posición para el cuadro
$x_cuadro = $col_logo + $col_centro + 10;
$y_cuadro = $startY;
$ancho_cuadro = $col_derecha;
$alto_cuadro = 20; // Altura suficiente para el contenido

// Dibujar el cuadro con borde
$pdf->SetLineWidth(0.5); // Borde más grueso
$pdf->Rect($x_cuadro, $y_cuadro, $ancho_cuadro, $alto_cuadro);

// Posicionar dentro del cuadro
$x_interior = $x_cuadro + 2; // Pequeño margen interno
$y_interior = $y_cuadro + 5;

// Tipo de Comprobante (centrado en la parte superior del cuadro)
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$tipo_comprobante = isset($data_nota['desc_tp_comprobante']) ? 
                   strtoupper($data_nota['desc_tp_comprobante']) : 'NOTA';
$pdf->SetXY($x_interior, $y_interior);
$pdf->MultiCell($ancho_cuadro - 4, 6, $tipo_comprobante, 0, 'C', 0, 1);

// Serie y Correlativo (centrado en la parte inferior del cuadro)
$pdf->SetFont('helvetica', 'B', 10);
if (isset($data_nota['serie']) && isset($data_nota['correlativo'])) {
    $correlativo = $data_nota['serie'] . "-" . 
                   str_pad($data_nota['correlativo'], 8, '0', STR_PAD_LEFT);
} else {
    $correlativo = 'SIN SERIE';
}
$pdf->SetXY($x_interior, $y_interior + 8);
$pdf->MultiCell($ancho_cuadro - 4, 7, $correlativo, 0, 'C', 0, 1);

// Línea separadora (más gruesa para A4)
$pdf->SetLineWidth(0.3);
$pdf->Cell($W_UTIL, 0, '', 'B');
$pdf->Ln(8);

// Sección de datos principales con fuente más pequeña
$pdf->SetFillColor(240, 240, 240);

// Fecha de emisión
$fecha_emision = !empty($this->data["data_nota"]["fecha_emision"])
    ? date("d-m-Y", strtotime($this->data["data_nota"]["fecha_emision"]))
    : date("d-m-Y");

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 6, 'FECHA EMISIÓN:', 0, 0, 'L'); // Ancho reducido
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 6, $fecha_emision, 0, 1, 'L');

// CLIENTE y DNI en la misma línea
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 6, 'CLIENTE:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$cliente_nombres = $this->data["cliente"]["nombres_cliente"] ?? 'NO REGISTRADO';

// Calcular espacio disponible para el nombre del cliente
$ancho_disponible = 90; // Ancho estimado disponible para el nombre
$longitud_nombre = $pdf->GetStringWidth($cliente_nombres);

// Si el nombre es muy largo, usar MultiCell
if ($longitud_nombre > $ancho_disponible) {
    $pdf->MultiCell($ancho_disponible, 6, $cliente_nombres, 0, 'L', 0, 0);
} else {
    $pdf->Cell($ancho_disponible, 6, $cliente_nombres, 0, 0, 'L');
}

// DNI/RUC en la misma línea después del nombre
$pdf->SetFont('helvetica', 'B', 8);
$tipo_doc = $this->data["cliente"]["tipo_doc_descripcion"] ?? '';
$pdf->Cell(15, 6, $tipo_doc . ':', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$num_docu = $this->data["cliente"]["num_docu"] ?? 'SIN DOCUMENTO';
$pdf->Cell(0, 6, $num_docu, 0, 1, 'L');

// Dirección (en línea aparte)
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(30, 6, 'DIRECCIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

$direccion_completa = '';
if (isset($this->data["cliente"]["direccion_completa"]) && !empty($this->data["cliente"]["direccion_completa"])) {
    $direccion_completa = $this->data["cliente"]["direccion_completa"];
} elseif (isset($this->data["cliente"]["direccion"]) && !empty($this->data["cliente"]["direccion"])) {
    $direccion_completa = $this->data["cliente"]["direccion"];
    if (isset($this->data["cliente"]["ubigeo_depa"])) {
        $direccion_completa .= ' - ' . $this->data["cliente"]["ubigeo_distri"]
            . ', ' . $this->data["cliente"]["ubigeo_provi"]
            . ', ' . $this->data["cliente"]["ubigeo_depa"];
    }
}

$pdf->MultiCell(0, 6, $direccion_completa, 0, 'L', false, 1);

// SECCIÓN: DOCUMENTO AFECTADO Y MOTIVO
$pdf->Ln(2);

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
    $pdf->Cell(30, 6, 'DOC. AFECTADO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 6, $doc_afectado, 0, 1, 'L');
}

// Tipo de nota (motivo)
if (isset($this->data["motivo"]["descripcion"]) && !empty($this->data["motivo"]["descripcion"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(30, 6, 'MOTIVO NOTA:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 6, $this->data["motivo"]["descripcion"], 0, 'L', 0, 1);
}

// Descripción
if (isset($this->data["data_nota"]["descripcion"]) && !empty($this->data["data_nota"]["descripcion"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(30, 6, 'DESCRIPCIÓN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $descripcion = $this->data["data_nota"]["descripcion"];
    $pdf->MultiCell(0, 6, $descripcion, 0, 'L', false, 1);
}

// ============================================
// SECCIÓN: TABLA DE DETALLES MEJORADA - FORMATO A4
// ============================================
$pdf->Ln(2);

// DEFINIR ANCHOS OPTIMIZADOS PARA A4
$w_uni      = 18;    // Unidad (aumentado ligeramente)
$w_desc     = 90;    // Descripción 
$w_cant     = 20;    // Cantidad 
$w_punit    = 30;    // Precio Unitario (reducido)
$w_total    = 32;    // Total (ajustado)

$ancho_total_tabla = $w_uni + $w_desc + $w_cant + $w_punit + $w_total;

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($w_uni,  8, 'UNIDAD',      1, 0, 'C', true);
$pdf->Cell($w_desc, 8, 'DESCRIPCIÓN', 1, 0, 'C', true);
$pdf->Cell($w_cant, 8, 'CANTIDAD',    1, 0, 'C', true);
$pdf->Cell($w_punit,8, 'P. UNITARIO', 1, 0, 'C', true);
$pdf->Cell($w_total,8, 'TOTAL',       1, 1, 'C', true);

// Verificar si hay detalles
if (isset($this->data["detalles"]) && count($this->data["detalles"]) > 0) {
    
    foreach ($this->data["detalles"] as $index => $detalle) {
        
        // ============================================
        // OBTENER VALORES CON DEFAULTS SEGUROS
        // ============================================
        $nombre_producto = trim($detalle['producto_nombre'] ?? $detalle['descripcion'] ?? 'Producto');
        $descripcion_detalle = trim($detalle['descripcion'] ?? '');
        $unidad = $detalle['unidad_medida'] ?? $detalle['unidad_medida_codigo'] ?? 'NIU';
        $cantidad = isset($detalle['cantidad']) ? number_format($detalle['cantidad'], 2) : '1.00';
        
        // PRECIO UNITARIO - 4 DECIMALES PARA SUNAT, 2 PARA VISUALIZACIÓN
        $p_unitario_raw = isset($detalle['precio_unitario']) ? (float)$detalle['precio_unitario'] : 0;
        $p_unitario = number_format($p_unitario_raw, 2);
        $p_unitario_4d = number_format($p_unitario_raw, 4);
        
        // DETERMINAR IMPORTE TOTAL
        if (isset($detalle['importe_total'])) {
            $importe_total = number_format($detalle['importe_total'], 2);
            $importe_total_raw = (float)$detalle['importe_total'];
        } elseif (isset($detalle['valor_total'])) {
            $importe_total = number_format($detalle['valor_total'], 2);
            $importe_total_raw = (float)$detalle['valor_total'];
        } elseif (isset($detalle['total'])) {
            $importe_total = number_format($detalle['total'], 2);
            $importe_total_raw = (float)$detalle['total'];
        } else {
            $importe_total_raw = ($detalle['cantidad'] ?? 1) * ($detalle['precio_unitario'] ?? 0);
            $importe_total = number_format($importe_total_raw, 2);
        }
        
        // ============================================
        // CONSTRUIR CONTENIDO HTML PARA DESCRIPCIÓN
        // ============================================
        $descripcion_html = '<span style="font-size:8pt; font-weight:normal;">';
        $descripcion_html .= htmlspecialchars($nombre_producto);
        
        // Agregar descripción detallada si existe y es diferente del nombre
        if (!empty($descripcion_detalle) && $descripcion_detalle != $nombre_producto) {
            // Para A4 podemos mostrar más texto
            if (mb_strlen($descripcion_detalle, 'UTF-8') > 100) {
                $descripcion_corta = mb_substr($descripcion_detalle, 0, 97, 'UTF-8') . '...';
                $descripcion_html .= '<br/><span style="font-size:6.5pt; color:#555;">' . 
                                   htmlspecialchars($descripcion_corta) . '</span>';
            } else {
                $descripcion_html .= '<br/><span style="font-size:6.5pt; color:#555;">' . 
                                   htmlspecialchars($descripcion_detalle) . '</span>';
            }
        }
        
        // Agregar código de producto si existe (útil para A4)
        if (!empty($detalle['codigo_producto'])) {
            $descripcion_html .= '<br/><span style="font-size:6pt; color:#777;">Código: ' . 
                               htmlspecialchars($detalle['codigo_producto']) . '</span>';
        }
        
        $descripcion_html .= '</span>';
        
        // ============================================
        // CALCULAR ALTURA DINÁMICA
        // ============================================
        
        // Guardar posición actual
        $x_inicial = $pdf->GetX();
        $y_inicial = $pdf->GetY();
        
        // Crear PDF temporal para medir altura
        $pdf_temp = new TCPDF_A4(); // Usar la clase A4
        $pdf_temp->AddPage();
        $pdf_temp->SetFont('helvetica', '', 8);
        $pdf_temp->setPrintHeader(false);
        $pdf_temp->setPrintFooter(false);
        
        // Medir altura del contenido HTML
        $pdf_temp->writeHTMLCell($w_desc, 0, 0, 0, $descripcion_html, 0, 1, false, true, 'L', true);
        $altura_medida = $pdf_temp->GetY();
        
        // Altura mínima garantizada
        $altura_fila = max($altura_medida, 6);
        
        // Verificar si cabe en la página actual
        if ($pdf->GetY() + $altura_fila > ($pdf->getPageHeight() - 20)) {
            $pdf->AddPage();
            
            // Reimprimir cabecera en nueva página
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell($w_uni,  8, 'UNIDAD',      1, 0, 'C', true);
            $pdf->Cell($w_desc, 8, 'DESCRIPCIÓN', 1, 0, 'C', true);
            $pdf->Cell($w_cant, 8, 'CANTIDAD',    1, 0, 'C', true);
            $pdf->Cell($w_punit,8, 'P. UNITARIO', 1, 0, 'C', true);
            $pdf->Cell($w_total,8, 'TOTAL',       1, 1, 'C', true);
            
            $x_inicial = $pdf->GetX();
            $y_inicial = $pdf->GetY();
        }
        
        // ============================================
        // DIBUJAR LA FILA
        // ============================================
        
        // Guardar posición para referencia
        $x_actual = $pdf->GetX();
        $y_actual = $pdf->GetY();
        
        // 1. UNIDAD
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell($w_uni, $altura_fila, $unidad, 1, 0, 'C', 0);
        
        // 2. DESCRIPCIÓN (con HTML)
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
            false,  // No rellenar (ya aplicamos fondo)
            true,
            'L',
            true
        );
        
        // 3. CANTIDAD
        $pdf->SetXY($x_desc + $w_desc, $y_desc);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell($w_cant, $altura_fila, $cantidad, 1, 0, 'R', 0);
        
        // 4. PRECIO UNITARIO
        $pdf->Cell($w_punit, $altura_fila, $p_unitario, 1, 0, 'R', 0);
        
        // 5. TOTAL
        $pdf->Cell($w_total, $altura_fila, $importe_total, 1, 1, 'R', 0);
        
        // Acumular total para el resumen
        if (!isset($suma_total)) $suma_total = 0;
        $suma_total += $importe_total_raw;
        
    } // Fin foreach
    
} else {
    // Mensaje si no hay detalles
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell($ancho_total_tabla, 10, 'NO HAY DETALLES REGISTRADOS', 1, 1, 'C', false);
}

$pdf->Ln(2);

// ============================================
// SECCIÓN: RESUMEN DE TOTALES - VERSIÓN SIMPLE
// ============================================

// Ancho para alinear a la derecha
$ancho_etiqueta = 50;
$ancho_valor = 50;
$x_derecha = $W_UTIL - $ancho_valor;

// Obtener valores (mismo código anterior)
$op_gravada = isset($this->data["data_nota"]["op_gravadas"]) ? (float) $this->data["data_nota"]["op_gravadas"] : 0;
$op_exonerada = isset($this->data["data_nota"]["op_exoneradas"]) ? (float) $this->data["data_nota"]["op_exoneradas"] : 0;
$op_inafecta = isset($this->data["data_nota"]["op_inafectas"]) ? (float) $this->data["data_nota"]["op_inafectas"] : 0;
$igv = isset($this->data["data_nota"]["igv"]) ? (float) $this->data["data_nota"]["igv"] : 0;
$total = isset($this->data["data_nota"]["total"]) ? (float) $this->data["data_nota"]["total"] : 0;

// Calcular total desde detalles
$total_calculado = 0;
if (isset($this->data["detalles"]) && count($this->data["detalles"]) > 0) {
    foreach ($this->data["detalles"] as $detalle) {
        $importe = isset($detalle['importe_total']) ? (float) $detalle['importe_total'] :
            (isset($detalle['valor_total']) ? (float) $detalle['valor_total'] : 0);
        $total_calculado += $importe;
    }
    if ($total == 0 && $total_calculado > 0) {
        $total = $total_calculado;
    }
}

// Mostrar totales alineados a la derecha
$pdf->SetFont('helvetica', '', 9);

// OP GRAVADA
$pdf->Cell($ancho_etiqueta, 6, 'OPERACIÓN GRAVADA:', 0, 0, 'L');
$pdf->SetX($x_derecha);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(60, 6, 'S/ ' . number_format($op_gravada, 2), 0, 1, 'R');

// OP EXONERADA
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($ancho_etiqueta, 6, 'OPERACIÓN EXONERADA:', 0, 0, 'L');
$pdf->SetX($x_derecha);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(60, 6, 'S/ ' . number_format($op_exonerada, 2), 0, 1, 'R');

// OP INAFECTA
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($ancho_etiqueta, 6, 'OPERACIÓN INAFECTA:', 0, 0, 'L');
$pdf->SetX($x_derecha);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(60, 6, 'S/ ' . number_format($op_inafecta, 2), 0, 1, 'R');

// IGV
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($ancho_etiqueta, 6, 'IGV:', 0, 0, 'L');
$pdf->SetX($x_derecha);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(60, 6, 'S/ ' . number_format($igv, 2), 0, 1, 'R');

// Línea separadora
$pdf->Ln(0.5);
$pdf->SetLineWidth(0.3);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(0.5);

// TOTAL
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($ancho_etiqueta, 6, 'IMPORTE:', 0, 0, 'L');
$pdf->SetX($x_derecha);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(60, 6, 'S/ ' . number_format($total, 2), 0, 1, 'R');

// MONTO EN LETRAS
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(25, 6, 'SON:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

if (function_exists('numtoletras')) {
    $monto_letras = $total_calculado > 0 ? $total_calculado : $total;
    $letras = numtoletras(number_format($monto_letras, 2, '.', ''));
} else {
    $letras = '[FUNCIÓN numtoletras NO ENCONTRADA]';
}

$pdf->MultiCell($W_UTIL - 50, 5, $letras, 0, 'L');
$pdf->Ln(4);

// SECCIÓN: HASH CPE CON BORDE
if (!empty($this->data["data_nota"]["hash_cpe"])) {
    $hash_cpe = $this->data["data_nota"]["hash_cpe"];
    
    $pdf->Ln(5);
    
    // Caja para el hash
    $boxX = 15;
    $boxY = $pdf->GetY();
    $boxWidth = $W_UTIL;
    $boxHeight = 15;
    
    $pdf->SetLineWidth(0.3);
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Rect($boxX, $boxY, $boxWidth, $boxHeight, 'DF');
    
    // Título
    $pdf->SetXY($boxX, $boxY + 2);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell($boxWidth, 4, 'CÓDIGO HASH DEL COMPROBANTE ELECTRÓNICO', 0, 1, 'C');
    
    // Hash
    $pdf->SetXY($boxX, $boxY + 7);
    $pdf->SetFont('helvetica', '', 7);
    
    // Dividir hash en varias líneas si es muy largo
    $hash_length = strlen($hash_cpe);
    $lines = ceil($hash_length / 80);
    
    for ($i = 0; $i < $lines; $i++) {
        $part = substr($hash_cpe, $i * 80, 80);
        $pdf->Cell($boxWidth, 3, $part, 0, 1, 'C');
    }
    
    $pdf->SetY($boxY + $boxHeight + 5);
}

// Salida
$pdf->Output('comprobante_a4.pdf', 'I');