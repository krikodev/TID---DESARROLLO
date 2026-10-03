<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

// CONSTANTES PARA FORMATO A4
define("PAGE_WIDTH", 210);  // Ancho A4 en mm
define("PAGE_HEIGHT", 297); // Alto A4 en mm
define("MARGIN_LEFT", 8);
define("MARGIN_RIGHT", 8);
define("MARGIN_TOP", 8);
define("MARGIN_BOTTOM", 8);
define("WIDTH_UTIL", PAGE_WIDTH - MARGIN_LEFT - MARGIN_RIGHT); // Ancho utilizable

class TCPDF_A4_Comprobante extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        // Tamaño A4
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Márgenes A4 estándar
        $this->SetMargins(MARGIN_LEFT, MARGIN_TOP, MARGIN_RIGHT);
        $this->SetAutoPageBreak(true, MARGIN_BOTTOM);
        $this->setPrintHeader(false);
        $this->SetPrintFooter(false);
    }

    // Métodos heredados del ticket para mantener compatibilidad
    function SetWidths($w)
    {
        $this->widths = $w;
    }

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

        // Verificación de salto de página
        if ($this->GetY() + $h > ($this->getPageHeight() - MARGIN_BOTTOM)) {
            $this->AddPage();
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

// Crear instancia PDF A4
$pdf = new TCPDF_A4_Comprobante();
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();

// ============================================
// CABECERA - DISEÑO MEJORADO CON TRES COLUMNAS
// ============================================
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect(MARGIN_LEFT, MARGIN_TOP, WIDTH_UTIL, 30, 'F');

// Definir anchos de columnas (ajustados)
$col_logo_width = 40;      // Ancho para logo (izquierda) - reducido
$col_empresa_width = 85;   // Ancho para datos empresa (centro) - ajustado
$col_comprobante_width = WIDTH_UTIL - $col_logo_width - $col_empresa_width; // Derecha

// ============================================
// COLUMNA IZQUIERDA: LOGO (adaptable)
// ============================================
if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]["logo"]) && file_exists($this->data["HEADER"]["HEADER_EMPRESA"]["logo"])) {
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true;
        }
        return false;
    });

    // Obtener dimensiones de la imagen
    list($imgWidth, $imgHeight) = @getimagesize($this->data["HEADER"]["HEADER_EMPRESA"]["logo"]);
    restore_error_handler();
    
    if ($imgWidth && $imgHeight) {
        // Calcular tamaño manteniendo proporción
        $maxWidth = 35;  // Ancho máximo para logo (reducido)
        $maxHeight = 30; // Alto máximo para logo (reducido)
        
        // Calcular escala manteniendo proporción
        $widthRatio = $maxWidth / $imgWidth;
        $heightRatio = $maxHeight / $imgHeight;
        $scale = min($widthRatio, $heightRatio);
        
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;
        
        // Centrar vertical y horizontalmente en su columna
        $xLogo = MARGIN_LEFT + (($col_logo_width - $finalWidth) / 2);
        $yLogo = MARGIN_TOP + 5;
        
        $pdf->Image(
            $this->data["HEADER"]["HEADER_EMPRESA"]["logo"],
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
}

// ============================================
// COLUMNA CENTRAL: DATOS DE LA EMPRESA (con MultiCell para evitar superposición)
// ============================================
$pdf->SetY(MARGIN_TOP );
$pdf->SetX(MARGIN_LEFT + $col_logo_width);

// Razón Social - limitar altura máxima
$pdf->SetFont('helvetica', 'B', 10);
$razon_social = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"]) ? 
    $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"] : '';
// Usar MultiCell con altura máxima
$pdf->MultiCell($col_empresa_width, 4, $razon_social, 0, 'L', 0, 1, '', '', true, 0, false, true, 12, 'M');

// RUC
$pdf->SetX(MARGIN_LEFT + $col_logo_width);
$pdf->SetFont('helvetica', 'B', 9);
$ruc = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"]) ? 
    $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"] : '';
$pdf->Cell($col_empresa_width, 4, "RUC: " . $ruc, 0, 1, 'L');

// NUEVO: Teléfono de la empresa y celular del terminal en la misma línea
$pdf->SetX(MARGIN_LEFT + $col_logo_width);
$pdf->SetFont('helvetica', '', 8);

$telefono_empresa = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["telefono_empresa"]) 
    ? $this->data["HEADER"]["HEADER_EMPRESA"]["telefono_empresa"] 
    : 'S/T';

$celular_terminal = !empty($this->data["HEADER"]["HEADER_TERMINAL"]["celular"]) 
    ? $this->data["HEADER"]["HEADER_TERMINAL"]["celular"] 
    : 'S/C';

$pdf->Cell($col_empresa_width, 4, "TELF: " . $telefono_empresa . " | CEL: " . $celular_terminal, 0, 1, 'L');

// Dirección fiscal
$pdf->SetX(MARGIN_LEFT + $col_logo_width);
$pdf->SetFont('helvetica', '', 7); // Tamaño de fuente reducido
$direccion_fiscal = !empty($this->data["HEADER"]["HEADER_EMPRESA"]['direccion_fiscal']) ? 
    'DOM. FISCAL: ' . $this->data["HEADER"]["HEADER_EMPRESA"]['direccion_fiscal'] : '';
// Usar MultiCell para que el texto se ajuste
$pdf->MultiCell($col_empresa_width, 3, $direccion_fiscal, 0, 'L', 0, 1);

// Terminales si existen - con altura reducida
if (!empty($this->data["TERMINALES"])) {
    $pdf->SetFont('helvetica', '', 7);
    $terminalCount = 0;
    foreach ($this->data["TERMINALES"] as $terminal) {
        // Limitar a mostrar máximo 2 terminales para no exceder espacio
        if ($terminalCount >= 2) break;
        $pdf->SetX(MARGIN_LEFT + $col_logo_width);
        $direccion = $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . ' - Cel: ' . $terminal['celular'];
        $pdf->MultiCell($col_empresa_width, 3, $direccion, 0, 'L', 0, 1);
        $terminalCount++;
    }
}

// Frase empresa - limitar altura
if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'])) {
    $pdf->SetX(MARGIN_LEFT + $col_logo_width);
    $pdf->SetFont('helvetica', '', 7);
    $frase_empresa = html_entity_decode($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'], ENT_QUOTES, 'UTF-8');
    // Limitar a 2 líneas máximo
    $pdf->writeHTMLCell($col_empresa_width, 6, '', '', $frase_empresa, 0, 1, 0, true, 'L', true);
}

// ============================================
// COLUMNA DERECHA: TIPO DE COMPROBANTE EN CUADRADO (con texto en dos líneas)
// ============================================
$x_comprobante = MARGIN_LEFT + $col_logo_width + $col_empresa_width;
$y_comprobante = MARGIN_TOP + 3;
$ancho_recuadro = $col_comprobante_width - 5;
$alto_recuadro = 25;

// Crear recuadro con fondo
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Rect($x_comprobante, $y_comprobante, $ancho_recuadro, $alto_recuadro, 'DF');

// Procesar el tipo de comprobante para dividirlo en dos líneas
$tipo_comprobante = !empty($this->data["BODY"]["tp_comprobante"]) ? 
    strtoupper($this->data["BODY"]["tp_comprobante"]) : '';

// Dividir "BOLETA DE VENTA ELECTRONICA" en dos líneas
if (strpos($tipo_comprobante, 'ELECTRONICA') !== false) {
    $tipo_comprobante = str_replace('ELECTRONICA', '', $tipo_comprobante);
    $linea1 = trim($tipo_comprobante);
    $linea2 = 'ELECTRONICA';
} elseif (strpos($tipo_comprobante, 'ELECTRÓNICA') !== false) {
    $tipo_comprobante = str_replace('ELECTRÓNICA', '', $tipo_comprobante);
    $linea1 = trim($tipo_comprobante);
    $linea2 = 'ELECTRÓNICA';
} else {
    // Si no contiene "ELECTRONICA", dividir por palabras si es muy largo
    $words = explode(' ', $tipo_comprobante);
    if (count($words) > 2) {
        $mid = ceil(count($words) / 2);
        $linea1 = implode(' ', array_slice($words, 0, $mid));
        $linea2 = implode(' ', array_slice($words, $mid));
    } else {
        $linea1 = $tipo_comprobante;
        $linea2 = '';
    }
}

// Posicionar dentro del recuadro
$pdf->SetY($y_comprobante + 3); // Un poco más arriba
$pdf->SetX($x_comprobante);

// Tipo de comprobante - Línea 1
$pdf->SetFont('helvetica', 'B', 9); // Tamaño reducido
$pdf->Cell($ancho_recuadro, 5, $linea1, 0, 1, 'C');

// Tipo de comprobante - Línea 2 (si existe)
if (!empty($linea2)) {
    $pdf->SetX($x_comprobante);
    $pdf->Cell($ancho_recuadro, 5, $linea2, 0, 1, 'C');
}

// Línea divisoria
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line($x_comprobante + 3, $pdf->GetY(), $x_comprobante + $ancho_recuadro - 3, $pdf->GetY());

// Serie y número
$serie = !empty($this->data["BODY"]["serie"]) ? $this->data["BODY"]["serie"] : '';
$correlativo_num = !empty($this->data["BODY"]["correlativo"]) ? 
    str_pad($this->data["BODY"]["correlativo"], 8, '0', STR_PAD_LEFT) : '';
$correlativo_completo = $serie . "-" . $correlativo_num;

$pdf->SetY($pdf->GetY() + 1);
$pdf->SetX($x_comprobante);
$pdf->SetFont('helvetica', 'B', 12); // Tamaño aumentado para el número
$pdf->Cell($ancho_recuadro, 8, $correlativo_completo, 0, 1, 'C');

$pdf->Ln(3);

// ============================================
// DATOS PRINCIPALES - DISEÑO CON CUADRO ÚNICO
// ============================================
$pdf->SetY($pdf->GetY() + 5);
$y_start = $pdf->GetY(); // Guardar posición Y inicial para el cuadro

// ============================================
// FILA 1: FECHA, TIPO, TRACKING
// ============================================
// Fecha de Emisión
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(28, 5, 'FECHA EMISIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(45, 5, date("d-m-Y H:i", strtotime($this->data["BODY"]["fecha_emision"])), 0, 0, 'L');

// Tipo
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(10, 5, 'TIPO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$tipo_servicio = !empty($this->data["DESTINO"]["tipo"]) ? 
    strtoupper($this->data["DESTINO"]["tipo"]) : 'S/D';
$pdf->Cell(35, 5, $tipo_servicio, 0, 0, 'L');

// Tracking (solo si está habilitado)
$mostrar_tracking = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_tracking'] ?? 0;
if ($mostrar_tracking == 1) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(18, 5, 'TRACKING:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $tracking = !empty($this->data["BODY"]["codigo_tracking"]) ? 
        $this->data["BODY"]["codigo_tracking"] : 'S/T';
    $pdf->Cell(0, 5, $tracking, 0, 1, 'L');
} else {
    $pdf->Ln(); // Salto de línea si no hay tracking
}

// ============================================
// FILA 2: CLIENTE
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(28, 5, 'CLIENTE:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Datos del cliente
$cliente_nombre = trim($this->data["BODY"]["remitente_nombres"] . ' ' . $this->data["BODY"]["remitente_apellidos"]);
$pdf->MultiCell(85, 5, $cliente_nombre, 0, 'L', 0, 0);

// Guardar posición Y después del MultiCell para ajuste
$y_despues_cliente = $pdf->GetY();

// DNI del cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(10, 5, $this->data["BODY"]["remitente_tp_docu"] . ':', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$dni_cliente = !empty($this->data["BODY"]["remitente_num_docu"]) ? 
    $this->data["BODY"]["remitente_num_docu"] : 'S/N';
$pdf->Cell(25, 5, $dni_cliente, 0, 0, 'L');

// Celular del cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(8, 5, 'CEL:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$cel_cliente = !empty($this->data["BODY"]["remitente_celular"]) ? 
    $this->data["BODY"]["remitente_celular"] : 'S/N';
$pdf->Cell(0, 5, $cel_cliente, 0, 1, 'L');

// Ajustar posición si el MultiCell fue más alto
if ($y_despues_cliente > ($pdf->GetY() - 5)) {
    $pdf->SetY($y_despues_cliente);
}

// NUEVA FILA: DIRECCIÓN DEL CLIENTE
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(28, 5, 'DIRECCIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Usar la dirección completa combinada
$direccion_remitente = !empty($this->data["BODY"]["remitente_direccion_completa"]) 
    ? $this->data["BODY"]["remitente_direccion_completa"] 
    : (!empty($this->data["BODY"]["remitente_direccion"]) 
        ? $this->data["BODY"]["remitente_direccion"] 
        : 'S/D');

$pdf->MultiCell(0, 5, $direccion_remitente, 0, 'L', 0, 1);

// ============================================
// FILA 3: DESTINATARIO
// ============================================
$datos_destinatario = $this->data["HEADER"]["HEADER_EMPRESA"]['datos_destinatario'] ?? 0;
if ($datos_destinatario == 1) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(28, 5, 'DESTINATARIO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);

    // Datos del destinatario
    $destinatario_nombre = trim($this->data["BODY"]["destinatario_nombres"] . ' ' . $this->data["BODY"]["destinatario_apellidos"]);
    $obs_destinatario = !empty($this->data["BODY"]["obs_destinatario"]) ?
        ' (' . $this->data["BODY"]["obs_destinatario"] . ')' : '';
    $destinatario_completo = $destinatario_nombre .
        ($this->data["BODY"]["destinatario_num_docu"] == '00000000' ? $obs_destinatario : '');

    $pdf->MultiCell(85, 5, $destinatario_completo, 0, 'L', 0, 0);

    // Guardar posición Y después del MultiCell para ajuste
    $y_despues_destinatario = $pdf->GetY();

    // DNI del destinatario
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(10, 5, $this->data["BODY"]["destinatario_tp_docu"] . ':', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $dni_destinatario = !empty($this->data["BODY"]["destinatario_num_docu"]) ?
        $this->data["BODY"]["destinatario_num_docu"] : 'S/N';
    $pdf->Cell(25, 5, $dni_destinatario, 0, 0, 'L');

    // Celular del destinatario
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(10, 5, 'CEL:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $cel_destinatario = !empty($this->data["BODY"]["destinatario_celular"]) ?
        $this->data["BODY"]["destinatario_celular"] : 'S/N';
    $pdf->Cell(0, 5, $cel_destinatario, 0, 1, 'L');

    // Ajustar posición si el MultiCell fue más alto
    if ($y_despues_destinatario > ($pdf->GetY() - 5)) {
        $pdf->SetY($y_despues_destinatario);
    }

    // Agregar espacio después de la sección
    $pdf->Ln(0.1);
}

// ============================================
// SECCIÓN ORIGEN/DESTINO 
// ============================================
$origen_destino = $this->data["HEADER"]["HEADER_EMPRESA"]['origen_destino'] ?? 0;

if ($origen_destino == 1) {

    // Determinar si es entrega a domicilio
    $is_entrega_domicilio = isset($this->data["DESTINO"]["tipo"]) &&
        $this->data["DESTINO"]["tipo"] === 'ENTREGA A DOMICILIO';

    // Obtener la configuración de dirección completa
    $mostrar_direccion_completa = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_direccion_completa'] ?? 0;

    // FILA ORIGEN
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(28, 5, 'ORIGEN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);

    $origen_text = !empty($this->data["DESTINO"]["terminal_origen"]) ?
        strtoupper($this->data["DESTINO"]["terminal_origen"]) : 'S/D';

    // Mostrar dirección completa si está configurado
    if ($mostrar_direccion_completa == 1 && !empty($this->data["DESTINO"]["terminal_origen_direccion"])) {
        $direccion_origen = strtoupper($this->data["DESTINO"]["terminal_origen_direccion"]);
        if (!empty($this->data["DESTINO"]["ubigeo_origen_distri"])) {
            $direccion_origen .= ' - ' . $this->data["DESTINO"]["ubigeo_origen_distri"] . ', ' .
                $this->data["DESTINO"]["ubigeo_origen_provi"] . ', ' .
                $this->data["DESTINO"]["ubigeo_origen_depa"];
        }
        $origen_text = $origen_text . "\n" . $direccion_origen;
    }

    $pdf->MultiCell(WIDTH_UTIL - 28, 5, $origen_text, 0, 'L', 0, 1);

    // FILA DESTINO
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(28, 5, 'DESTINO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);

    $is_entrega_domicilio = isset($this->data["DESTINO"]["tipo"]) &&
        $this->data["DESTINO"]["tipo"] === 'ENTREGA A DOMICILIO';

    if ($is_entrega_domicilio) {
        $destino_text = strtoupper(!empty($this->data["DESTINO"]["dir_destino_custom"]) ?
            $this->data["DESTINO"]["dir_destino_custom"] : 'S/D');
    } else {
        $destino_text = strtoupper(!empty($this->data["DESTINO"]["terminal_destino"]) ?
            $this->data["DESTINO"]["terminal_destino"] : 'S/D');
    }

    // Mostrar dirección completa si está configurado
    if ($mostrar_direccion_completa == 1) {
        if ($is_entrega_domicilio && !empty($this->data["DESTINO"]["dir_destino_custom"])) {
            $direccion_destino = strtoupper($this->data["DESTINO"]["dir_destino_custom"]);
            if (!empty($this->data["DESTINO"]["ubigeo_llegada_distri"])) {
                $direccion_destino .= ' - ' . $this->data["DESTINO"]["ubigeo_llegada_distri"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_llegada_provi"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_llegada_depa"];
            }
            $destino_text = $destino_text . "\n" . $direccion_destino;
        } elseif (!$is_entrega_domicilio && !empty($this->data["DESTINO"]["terminal_destino_direccion"])) {
            $direccion_destino = strtoupper($this->data["DESTINO"]["terminal_destino_direccion"]);
            if (!empty($this->data["DESTINO"]["ubigeo_destino_distri"])) {
                $direccion_destino .= ' - ' . $this->data["DESTINO"]["ubigeo_destino_distri"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_destino_provi"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_destino_depa"];
            }
            $destino_text = $destino_text . "\n" . $direccion_destino;
        }
    }

    $pdf->MultiCell(WIDTH_UTIL - 28, 5, $destino_text, 0, 'L', 0, 1);

    // Agregar espacio después de la sección
    $pdf->Ln(0.3);
}

// ============================================
// DIBUJAR CUADRO ÚNICO QUE ENVUELVE TODO
// ============================================
$y_end = $pdf->GetY();
$altura_real = $y_end - $y_start + 3;

// Dibujar el cuadro alrededor de todo el contenido
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);
$pdf->Rect(MARGIN_LEFT, $y_start - 2, WIDTH_UTIL, $altura_real, 'D');

$pdf->SetY($y_end + 3);

// ============================================
// SECCIÓN DETRACCIÓN (SI APLICA) - SIN CUADRO
// ============================================
if (isset($this->data["BODY"]["afecta_detraccion"]) && $this->data["BODY"]["afecta_detraccion"] == 1 && !empty($this->data["DATA_DETRACCION"])) {
    $detraccion = $this->data["DATA_DETRACCION"];
    
    // Título simple
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(WIDTH_UTIL, 6, 'INFORMACIÓN DE DETRACCIÓN', 0, 1, 'C');
    
    // ============================================
    // FILA 1: CUENTA, PORCENTAJE, MONTO
    // ============================================
    // N. CTA DETRACCIONES
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(35, 5, 'N. CTA DETRACCIONES:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $cuenta_detraccion = isset($detraccion['cuenta_detraccion']) ? $detraccion['cuenta_detraccion'] : 
        (isset($this->data["HEADER"]["HEADER_EMPRESA"]['nro_cuenta_BN']) ? 
            $this->data["HEADER"]["HEADER_EMPRESA"]['nro_cuenta_BN'] : 'POR DEFINIR');
    // Acortar si es muy largo
    if (strlen($cuenta_detraccion) > 25) {
        $cuenta_detraccion = substr($cuenta_detraccion, 0, 22) . '...';
    }
    $pdf->Cell(60, 5, $cuenta_detraccion, 0, 0, 'L');
    
    // PORCENTAJE
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(22, 5, 'PORCENTAJE:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    if (isset($detraccion['porcentaje_decimal'])) {
        $porcentaje_valor = floatval($detraccion['porcentaje_decimal']) * 100;
        $porcentaje = number_format($porcentaje_valor, 2) . '%';
    } elseif (isset($detraccion['porcentaje_operacion'])) {
        $porcentaje = number_format(floatval($detraccion['porcentaje_operacion']), 2) . '%';
    } else {
        $porcentaje = '4.00%';
    }
    $pdf->Cell(20, 5, $porcentaje, 0, 0, 'L');
    
    // MONTO DETRACCIÓN
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(33, 5, 'MONTO DETRACCIÓN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $monto_detraccion = isset($detraccion['monto_detraccion']) ? 
        'S/. ' . number_format($detraccion['monto_detraccion'], 2) : 'S/. 0.00';
    $pdf->Cell(0, 5, $monto_detraccion, 0, 1, 'L');
    
    // ============================================
    // FILA 2: MÉTODO DE PAGO
    // ============================================
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(35, 5, 'MÉTODO DE PAGO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $metodo_pago = isset($detraccion['medio_pago_descripcion']) ? 
        $detraccion['medio_pago_descripcion'] : 'NO ESPECIFICADO';
    // Acortar si es muy largo
    if (strlen($metodo_pago) > 40) {
        $metodo_pago = substr($metodo_pago, 0, 37) . '...';
    }
    $pdf->Cell(0, 5, $metodo_pago, 0, 1, 'L');
    
    // ============================================
    // FILA 3: TIPO DE OPERACIÓN (NUEVA - desde facturador)
    // ============================================
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(35, 5, 'TIPO OPERACIÓN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    
    $tipo_operacion = '';
    if (isset($this->data["BODY"]['codigo_tp_operacion_sunat']) && isset($this->data["BODY"]['tipo_operacion_sunat'])) {
        $tipo_operacion = $this->data["BODY"]['codigo_tp_operacion_sunat'] . ' ' . 
                         $this->data["BODY"]['tipo_operacion_sunat'];
    } else {
        $tipo_operacion = '1004 Operación Sujeta a Detracción';
    }
    
    $pdf->MultiCell(0, 5, $tipo_operacion, 0, 'L', false, 1);
    
    // ============================================
    // FILA 4: BIEN/SERVICIO (MEJORADA)
    // ============================================
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(35, 5, 'BIEN/SERVICIO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    
    // Versión mejorada con código si está disponible
    if (isset($detraccion['tipo_detraccion_codigo']) && isset($detraccion['tipo_detraccion_descripcion'])) {
        $bien_servicio = $detraccion['tipo_detraccion_codigo'] . ' ' . 
                        $detraccion['tipo_detraccion_descripcion'];
    } elseif (isset($detraccion['tipo_detraccion_descripcion'])) {
        $bien_servicio = $detraccion['tipo_detraccion_descripcion'];
    } else {
        $bien_servicio = 'SERVICIO DE TRANSPORTE DE BIENES POR VÍA TERRESTRE';
    }
    
    $bien_servicio = strtoupper($bien_servicio);
    $pdf->MultiCell(0, 5, $bien_servicio, 0, 'L', false, 1);
    
    $pdf->Ln(3);
}

// ============================================
// TABLA DE DETALLES - ADAPTATIVA Y MEJORADA
// ============================================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(240, 240, 240);

// Encabezado de tabla (anchos ajustados)
$pdf->Cell(12, 6, 'CANT.', 1, 0, 'C', true);
$pdf->Cell(18, 6, 'UNIDAD', 1, 0, 'C', true);
$pdf->Cell(89, 6, 'DESCRIPCIÓN', 1, 0, 'C', true);
$pdf->Cell(20, 6, 'PESO (Kg)', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'P. UNIT.', 1, 0, 'C', true);
$pdf->Cell(30, 6, 'TOTAL', 1, 1, 'C', true);

$suma_total = 0;

// Función mejorada para iniciales de unidad
function getInicialesUnidad($unidad) {
    $unidad = strtoupper(trim($unidad));
    $iniciales = [
        'UNIDAD' => 'UN',
        'CAJA' => 'BX',
        'GALON' => 'GLL',
        'KILOS' => 'KGM',
        'FRASCO' => 'JR',
        'ENVASE' => 'CH',
        'SACO' => 'SA',
        'LATA' => 'CA',
        'SERVICIO' => 'ZZ',
        'PAQUETE' => 'PK',
        'ROLLO' => 'RL',
        'METRO' => 'MTR',
        'LITRO' => 'LTR',
        'PAR' => 'PR'
    ];
    return $iniciales[$unidad] ?? substr($unidad, 0, 2);
}

// Detalles
foreach ($this->data["DETALLE"] as $detalle) {
    $total_item = $detalle["cantidad"] * $detalle["precio"];
    $peso = isset($this->data["BODY"]["peso_encomienda"]) ? 
        $this->data["BODY"]["peso_encomienda"] : 
        (isset($detalle["peso"]) ? $detalle["peso"] : '0.00');
    
    $descripcion = $detalle["descripcion"];
    $observacion = $detalle["obs"];
    $comprobante = !empty($detalle["serie"]) && !empty($detalle["correlativo"]) ? 
        $detalle["serie"] . '-' . $detalle["correlativo"] : '';
    $guia = !empty($detalle["guia_serie"]) && !empty($detalle["guia_correlativo"]) ? 
        $detalle["guia_serie"] . '-' . $detalle["guia_correlativo"] : '';
    
    // OBTENER LAS INICIALES DE LA UNIDAD DE MEDIDA
    $unidad_medida = isset($detalle["unid_medida"]) ? $detalle["unid_medida"] : 'UNIDAD';
    $iniciales_unidad = getInicialesUnidad($unidad_medida);
    
    // Preparar detalles adicionales compactos (como en comprobante.php)
    $detalles = trim(
        ($comprobante != '' ? 'COMP: ' . $comprobante : '') .
        ($guia != '' ? '  GUIA: ' . $guia : '') .
        ($observacion != '' ? '  OBS: ' . substr($observacion, 0, 30) . (strlen($observacion) > 30 ? '...' : '') : '')
    );
    
    $descripcion_completa = $descripcion;
    
    if ($detalles !== '') {
        // Usar HTML para tamaño de fuente diferente (como en comprobante.php)
        $descripcion_completa .= '<br/><span style="font-size:7pt;">' . $detalles . '</span>';
    }
    
    $pdf->SetFont('helvetica', '', 8);
    
    // ============================================
    // CALCULAR ALTURA DINÁMICAMENTE (como en comprobante.php)
    // ============================================
    // Clonar el PDF temporal para medir altura real
    $temp_pdf = clone $pdf;
    
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    
    // Medir altura del contenido HTML (como en comprobante.php)
    $temp_pdf->writeHTMLCell(89, 0, $x, $y, $descripcion_completa, 0, 1, false, true, 'L', true);
    $altura_real = $temp_pdf->GetY() - $y;
    
    // Garantizar altura mínima (ajustada para A4)
    $altura_fila = max($altura_real, 5);
    
    // Verificar si necesita nueva página
    if ($pdf->GetY() + $altura_fila > PAGE_HEIGHT - MARGIN_BOTTOM) {
        $pdf->AddPage();
        // Redibujar encabezado de tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(12, 6, 'CANT.', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'UNIDAD', 1, 0, 'C', true);
        $pdf->Cell(89, 6, 'DESCRIPCIÓN', 1, 0, 'C', true);
        $pdf->Cell(20, 6, 'PESO (Kg)', 1, 0, 'C', true);
        $pdf->Cell(25, 6, 'P. UNIT.', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'TOTAL', 1, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 8);
    }
    
    // Guardar posición inicial
    $x_inicio = $pdf->GetX();
    $y_inicio = $pdf->GetY();
    
    // ============================================
    // DIBUJAR CELDAS CON ALTURA ADAPTATIVA
    // ============================================
    // Celda Cantidad
    $pdf->Cell(12, $altura_fila, $detalle["cantidad"], 1, 0, 'C');
    
    // Celda Unidad
    $pdf->Cell(18, $altura_fila, $iniciales_unidad, 1, 0, 'C');
    
    // Celda Descripción con HTML (como en comprobante.php)
    $x_desc = $pdf->GetX();
    $y_desc = $pdf->GetY();
    
    $pdf->writeHTMLCell(89, $altura_fila, $x_desc, $y_desc, $descripcion_completa, 1, 0, false, true, 'L', true);
    
    // Posicionar para siguiente celda
    $pdf->SetXY($x_desc + 89, $y_desc);
    
    // Celda Peso
    $peso_text = ($peso > 0) ? number_format($peso, 2) : '';
    $pdf->Cell(20, $altura_fila, $peso_text, 1, 0, 'C');
    
    // Celda Precio Unitario
    $pdf->Cell(25, $altura_fila, number_format($detalle["precio"], 2), 1, 0, 'R');
    
    // Celda Total
    $pdf->Cell(30, $altura_fila, number_format($total_item, 2), 1, 1, 'R');
    
    $suma_total += $total_item;
    
    // Ajustar posición Y si la altura real fue diferente
    if ($altura_fila > 5) {
        $pdf->SetY($y_inicio + $altura_fila);
    }
}

$pdf->Ln(2);

// ============================================
// TOTALES - COMPACTOS
// ============================================

// Tabla de totales (diseño compacto)
$items_totales = [];

// Solo mostrar Operación Gravada si es mayor a 0
if (isset($this->data["BODY"]["op_gravada"]) && floatval($this->data["BODY"]["op_gravada"]) > 0) {
    $items_totales['Operación Gravada:'] = $this->data["BODY"]["op_gravada"];
}

// Solo mostrar IGV si es mayor a 0
if (isset($this->data["BODY"]["op_igv"]) && floatval($this->data["BODY"]["op_igv"]) > 0) {
    $items_totales['IGV (18%):'] = $this->data["BODY"]["op_igv"];
}

// Solo mostrar Operación Exonerada si es mayor a 0
if (isset($this->data["BODY"]["op_exonerada"]) && floatval($this->data["BODY"]["op_exonerada"]) > 0) {
    $items_totales['Operación Exonerada:'] = $this->data["BODY"]["op_exonerada"];
}

// Solo mostrar Operación Inafecta si es mayor a 0
if (isset($this->data["BODY"]["op_inafecta"]) && floatval($this->data["BODY"]["op_inafecta"]) > 0) {
    $items_totales['Operación Inafecta:'] = $this->data["BODY"]["op_inafecta"];
}

// TOTAL siempre se muestra (es el total)
$items_totales['TOTAL:'] = $suma_total;

$pdf->SetFont('helvetica', '', 8); // Reducido de 11 a 9
foreach ($items_totales as $label => $value) {
    $value = is_numeric($value) ? $value : 0;
    
    if ($label === 'TOTAL:') {
        $pdf->SetFont('helvetica', 'B', 10); // Reducido de 12 a 11
        $pdf->SetFillColor(220, 220, 220);
    }
    
    $pdf->Cell(WIDTH_UTIL - 90, 6, $label, 0, 0, 'R'); // Reducido ancho
    $pdf->Cell(10, 6, 'S/.', 0, 0, 'L'); // Reducido ancho
    $pdf->Cell(80, 6, number_format($value, 2), 0, 1, 'R'); // Reducido ancho
    
    if ($label === 'TOTAL:') {
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetFillColor(255, 255, 255);
    }
}

$pdf->Ln(2);

// Total en letras (tamaño reducido)
$pdf->SetFont('helvetica', 'B', 9); // Reducido de 11 a 9
$letras = 'SON: ' . numtoletras(number_format($suma_total, 2, '.', ''));
$pdf->MultiCell(WIDTH_UTIL, 5, $letras, 0, 'L'); // Altura reducida
$pdf->Ln(3);

// ============================================
// INFORMACIÓN ADICIONAL Y QR (3 COLUMNAS)
// ============================================
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect(MARGIN_LEFT, $pdf->GetY(), WIDTH_UTIL, 45, 'F'); // Aumentado ligeramente

// Configuración de columnas
$col_qr_width = 45;        // Ancho para QR (izquierda)
$col_info_width = 100;     // Ancho para información (centro)
$col_docs_width = WIDTH_UTIL - $col_qr_width - $col_info_width - 15; // Derecha (con más espacio)

$y_start = $pdf->GetY() + 5;

// ============================================
// COLUMNA IZQUIERDA: QR (CORREGIDA)
// ============================================
$qr_y = $y_start;
if (!empty($this->data["BODY"]["cod_qr"])) {
    $qrPath = $this->data["BODY"]["cod_qr"];
    $qr_size = 35; // Tamaño QR
    
    // Posición absoluta para el QR
    $x_qr = MARGIN_LEFT + 10; // Margen izquierdo fijo
    
    // Guardar posición Y actual
    $current_y = $pdf->GetY();
    
    if (strpos($qrPath, 'private/empresa/') !== false && file_exists($qrPath)) {
        $pdf->Image($qrPath, $x_qr, $qr_y, $qr_size, $qr_size);
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, $x_qr, $qr_y, $qr_size, $qr_size);
    }
    
    // Restaurar posición Y para continuar
    $pdf->SetY($current_y);
}

// ============================================
// COLUMNA CENTRAL: INFORMACIÓN ADICIONAL
// ============================================
$info_x = MARGIN_LEFT + 50;
$info_y = $y_start;

// Posicionar en columna central
$pdf->SetXY($info_x, $info_y);

// Tamaños de fuente
$label_font_size = 8;
$value_font_size = 8;

// Ancho para etiquetas y valores
$label_width = 25;
$value_width = $col_info_width - $label_width - 5;

// Array con toda la información
$info_items = [];

// 1. FECHA/HORA
$info_items[] = [
    'label' => 'FECHA/HORA:',
    'value' => date("d-m-Y H:i", strtotime($this->data["BODY"]["fecha_emision"]))
];

// 2. FORMA DE PAGO
if (strtoupper($this->data["BODY"]["estado"]) == "PAGADO") {
    $mostrar_forma_pago = strtoupper($this->data["BODY"]["forma_pago"]);
} elseif (!empty($this->data["BODY"]["encomienda_pago"]) && 
          strtoupper($this->data["BODY"]["encomienda_pago"]) == "CREDITO") {
    $mostrar_forma_pago = 'CRÉDITO';
} else {
    $mostrar_forma_pago = '---';
}
$info_items[] = [
    'label' => 'F. PAGO:',
    'value' => $mostrar_forma_pago
];

// 3. MÉTODO DE PAGO
$metodo_pago = strtoupper($this->data["BODY"]["estado"]) == "PAGADO" ? 
    strtoupper($this->data["BODY"]["medio_pago"]) : '---';
$info_items[] = [
    'label' => 'M. PAGO:',
    'value' => $metodo_pago
];

// 4. REFERENCIA
$referencia = !empty(trim($this->data["BODY"]["obs"])) ? 
    trim($this->data["BODY"]["obs"]) : '---';
$info_items[] = [
    'label' => 'REFERENCIA:',
    'value' => $referencia,
    'multicell' => true
];

// 5. CÓDIGO DE SEGURIDAD
$codigo_completo = $this->data["BODY"]["encomienda_pass"] ?? '';
$codigo_oculto = '';
if (!empty($codigo_completo) && strlen($codigo_completo) > 1) {
    $primer_digito = substr($codigo_completo, 0, 1);
    $resto_oculto = str_repeat('x', strlen($codigo_completo) - 1);
    $codigo_oculto = $primer_digito . $resto_oculto;
} elseif (!empty($codigo_completo)) {
    $codigo_oculto = $codigo_completo;
} else {
    $codigo_oculto = '---';
}
$info_items[] = [
    'label' => 'CÓD. SEG.:',
    'value' => $codigo_oculto
];

// 6. VENDEDOR (si está configurado)
$mostrar_vendedor = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_vendedor'] ?? 0;
if ($mostrar_vendedor == 1) {
    $vendedor_text = trim(
        ($this->data["BODY"]["vendedor_nombres"] ?? '') . ' ' .
        ($this->data["BODY"]["vendedor_apellidos"] ?? '')
    );
    if (!empty($vendedor_text)) {
        $info_items[] = [
            'label' => 'VENDEDOR:',
            'value' => $vendedor_text
        ];
    }
}

// 7. CUENTA BANCARIA - CON SOPORTE HTML COMPLETO (como en comprobante.php)
$nro_cuenta_raw = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"]
    : '';

// Verificar si hay contenido REAL (ignorando etiquetas vacías)
$contenido_sin_html = trim(strip_tags($nro_cuenta_raw));
$hay_contenido_real = !empty($contenido_sin_html);

if ($hay_contenido_real) {
    // Decodificar entidades HTML
    $cuentas_html = html_entity_decode($nro_cuenta_raw, ENT_QUOTES, 'UTF-8');
    
    // Verificar si tiene formato HTML
    $tiene_html = ($cuentas_html != strip_tags($cuentas_html));
    
    if ($tiene_html) {
        // Modo HTML (negritas, colores, etc.)
        $info_items[] = [
            'label' => 'CTA. BANCARIA:',
            'value_html' => $cuentas_html,
            'type' => 'html'
        ];
    } else {
        // Texto plano, permitir múltiples líneas si es necesario
        $info_items[] = [
            'label' => 'CTA. BANCARIA:',
            'value' => $cuentas_html,
            'multicell' => true
        ];
    }
}
// Si NO hay contenido real, NO se muestra el campo

// ============================================
// RENDERIZAR INFORMACIÓN
// ============================================
$current_info_y = $info_y;
foreach ($info_items as $item) {
    $pdf->SetX($info_x);
    
    // Etiqueta
    $pdf->SetFont('helvetica', 'B', $label_font_size);
    $pdf->Cell($label_width, 5, $item['label'], 0, 0, 'L');
    
    // Posición X para el valor
    $current_x = $pdf->GetX();
    $current_y = $pdf->GetY();
    
    // Manejar diferentes tipos de contenido
    if (isset($item['type']) && $item['type'] == 'html') {
        // Para contenido HTML (como en comprobante.php)
        $pdf->SetFont('helvetica', '', $value_font_size);
        
        // Usar writeHTMLCell para renderizar HTML completo sin truncar
        // El ancho es value_width, altura automática (0)
        $pdf->writeHTMLCell($value_width, 0, $current_x, $current_y, $item['value_html'], 0, 1, 0, true, 'L', true);
        
        // Actualizar posición Y después del HTML
        $current_info_y = $pdf->GetY();
        $pdf->SetY($current_info_y);
        
    } elseif (isset($item['multicell']) && $item['multicell']) {
        // Para texto largo, usar MultiCell (sin truncar)
        $pdf->SetFont('helvetica', '', $value_font_size);
        $pdf->MultiCell($value_width, 5, $item['value'], 0, 'L', false, 1);
        $current_info_y = $pdf->GetY();
        
    } else {
        // Para texto normal, usar Cell
        $pdf->SetFont('helvetica', '', $value_font_size);
        $pdf->Cell($value_width, 5, $item['value'], 0, 1, 'L');
        $current_info_y = $pdf->GetY();
    }
}

// Guardar altura máxima de la columna central
$altura_info = $current_info_y - $info_y;

// ============================================
// COLUMNA DERECHA: DOCUMENTOS RELACIONADOS (CORREGIDA)
// ============================================
$docs_x = MARGIN_LEFT + 140;
$docs_y = $y_start;

// Posicionar absolutamente en columna derecha
$pdf->SetXY($docs_x, $docs_y);

// Preparar documentos relacionados
$guia_rela = '';
$comprobante_rela = '';
$hayDocumentos = false;

if (!empty($this->data["BODY"]["guia_serie"]) && !empty($this->data["BODY"]["guia_correlativo"])) {
    $guia_serie = trim($this->data["BODY"]["guia_serie"]);
    $guia_correlativo = trim($this->data["BODY"]["guia_correlativo"]);
    $guia_rela = $guia_serie . '-' . $guia_correlativo;
    if (!empty($guia_rela) && $guia_rela != '-') {
        $hayDocumentos = true;
    }
}

if ($this->data["BODY"]["tp_comprobante_ref"] != '0' &&
    !empty($this->data["BODY"]["serie_ref"]) &&
    !empty($this->data["BODY"]["correlativo_ref"])) {
    $serie_ref = trim($this->data["BODY"]["serie_ref"]);
    $correlativo_ref = trim($this->data["BODY"]["correlativo_ref"]);
    $comprobante_rela = $serie_ref . '-' . $correlativo_ref;
    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $hayDocumentos = true;
    }
}

if ($hayDocumentos) {
    // Título con fondo
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell($col_docs_width, 6, 'DOC. RELACIONADOS', 1, 1, 'C', true);
    
    // Espaciado
    $pdf->SetX($docs_x);
    $pdf->Cell($col_docs_width, 2, '', 0, 1, 'C');
    
    // Documentos
    $pdf->SetFont('helvetica', '', 8);
    
    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $pdf->SetX($docs_x);
        // Usar MultiCell para permitir saltos si es muy largo
        $pdf->MultiCell($col_docs_width, 5, 'Comp: ' . $comprobante_rela, 0, 'L', false, 1);
    }
    
    if (!empty($guia_rela) && $guia_rela != '-') {
        $pdf->SetX($docs_x);
        $pdf->MultiCell($col_docs_width, 5, 'Guía: ' . $guia_rela, 0, 'L', false, 1);
    }
    
    $current_docs_y = $pdf->GetY();
} else {
    // Si no hay documentos
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->MultiCell($col_docs_width, 15, 'Sin documentos relacionados', 0, 'C', false, 1);
    $current_docs_y = $pdf->GetY();
}

// Guardar altura de columna derecha
$altura_docs = $current_docs_y - $docs_y;

// ============================================
// AJUSTE FINAL DE POSICIÓN
// ============================================
// Calcular la altura máxima entre todas las columnas
$altura_qr = isset($qr_size) ? $qr_size + 5 : 10;
$altura_max = max($altura_qr, $altura_info, $altura_docs);

// Posicionar para la siguiente sección (tomar la posición más baja)
$y_next_section = $y_start + $altura_max + 10;

// Limpiar cualquier posición residual
$pdf->SetXY(MARGIN_LEFT, $y_next_section);

// ============================================
// INFORMACIÓN FINAL
// ============================================

$pdf->Ln(-6);
$pdf->SetFont('helvetica', '', 9);
$pdf->MultiCell(WIDTH_UTIL, 6, 
    'Usted puede consultar su Comprobante de Pago Electrónico (CPE) desde su Clave SOL o desde nuestra Página Web.', 
    0, 'L', false, 1);

$pdf->Ln(1);

// Términos y condiciones
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(WIDTH_UTIL, 7, 'CONDICIONES DEL SERVICIO', 0, 1, 'C');

$terminos_empresa = !empty($this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones']) ?
    $this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'] : '';

$hay_terminos_personalizados = false;
if (!empty($terminos_empresa)) {
    $terminos_sin_html = strip_tags($terminos_empresa);
    $terminos_sin_espacios = trim($terminos_sin_html);
    $hay_terminos_personalizados = !empty($terminos_sin_espacios);
}

if ($hay_terminos_personalizados) {
    $pdf->SetFont('helvetica', '', 9);
    $terminos_html = html_entity_decode($terminos_empresa, ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell(WIDTH_UTIL, 5, MARGIN_LEFT, $pdf->GetY(), $terminos_html, 0, 1, 0, true, 'L', true);
} else {
    $pdf->SetFont('helvetica', '', 9);
    $url_web = defined('URL_PAGE_WEB_COMPROBANTES') ? URL_PAGE_WEB_COMPROBANTES : 'www.tudominio.com';
    $pdf->MultiCell(WIDTH_UTIL, 5, 
        'Al recibir el presente DOCUMENTO acepto todos los términos y condiciones del contrato del servicio de transporte detallado en el letrero, banner y/o panel a la vista ubicados en el counter de ventas al momento de la compra, los cuales también se encuentran publicados en la página web ' . $url_web, 
        0, 'L', false, 1);
    
    $pdf->MultiCell(WIDTH_UTIL, 5, 
        'Dichas condiciones del presente contrato se encuentran dentro del ámbito de negociación permitida por el Ministerio de Transporte y Comunicaciones.', 
        0, 'L', false, 1);
}

$pdf->Ln(1);

// URL de la empresa
$pdf->SetFont('helvetica', 'B', 9);
$url_web = defined('URL_PAGE_WEB_COMPROBANTES') ? URL_PAGE_WEB_COMPROBANTES : 'www.tudominio.com';
$pdf->Cell(WIDTH_UTIL, 6, $url_web, 0, 1, 'L');

// Salida del PDF
ob_clean();
$pdf->Output('comprobante_A4.pdf', 'I');