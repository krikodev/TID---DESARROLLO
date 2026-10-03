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

class TCPDF_A4_NotaVenta extends TCPDF
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
$pdf = new TCPDF_A4_NotaVenta();
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();

// ============================================
// CABECERA - DISEÑO MEJORADO CON TRES COLUMNAS (ESTILO COMPROBANTE)
// ============================================
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect(MARGIN_LEFT, MARGIN_TOP, WIDTH_UTIL, 35, 'F'); // Aumentado para mejor espacio

// Definir anchos de columnas (ajustados para nota de venta)
$col_logo_width = 40;      // Ancho para logo (izquierda) - un poco más ancho
$col_empresa_width = 85;   // Ancho para datos empresa (centro)
$col_nota_width = WIDTH_UTIL - $col_logo_width - $col_empresa_width; // Derecha

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
        $maxWidth = 35;   // Ancho máximo para logo
        $maxHeight = 30;  // Alto máximo para logo

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
// COLUMNA CENTRAL: DATOS DE LA EMPRESA (con tamaños de fuente aumentados)
// ============================================
$pdf->SetY(MARGIN_TOP);
$pdf->SetX(MARGIN_LEFT + $col_logo_width);

// Razón Social - tamaño aumentado para mejor legibilidad
$pdf->SetFont('helvetica', 'B', 10); // Aumentado de 10 a 12
$razon_social = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"]) ?
    $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"] : '';
$pdf->MultiCell($col_empresa_width, 4, $razon_social, 0, 'L', 0, 1, '', '', true, 0, false, true, 16, 'M');

// RUC
$pdf->SetX(MARGIN_LEFT + $col_logo_width);
$pdf->SetFont('helvetica', 'B', 9); // Aumentado de 8 a 10
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
$pdf->SetFont('helvetica', '', 7); // Aumentado de 8 a 9
$direccion_fiscal = !empty($this->data["HEADER"]["HEADER_EMPRESA"]['direccion_fiscal']) ?
    'DOM. FISCAL: ' . $this->data["HEADER"]["HEADER_EMPRESA"]['direccion_fiscal'] : '';
$pdf->MultiCell($col_empresa_width, 3, $direccion_fiscal, 0, 'L', 0, 1);

// Terminales si existen
if (!empty($this->data["TERMINALES"])) {
    $pdf->SetFont('helvetica', '', 7);
    $terminalCount = 0;
    foreach ($this->data["TERMINALES"] as $terminal) {
        // Limitar a mostrar máximo 2 terminales
        if ($terminalCount >= 2)
            break;
        $pdf->SetX(MARGIN_LEFT + $col_logo_width);
        $direccion = $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . ' - Cel: ' . $terminal['celular'];
        $pdf->MultiCell($col_empresa_width, 3, $direccion, 0, 'L', 0, 1);
        $terminalCount++;
    }
}

// Frase empresa
if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'])) {
    $pdf->SetX(MARGIN_LEFT + $col_logo_width);
    $pdf->SetFont('helvetica', '', 7);
    $frase_empresa = html_entity_decode($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'], ENT_QUOTES, 'UTF-8');
    // Limitar altura a 2 líneas máximo
    $pdf->writeHTMLCell($col_empresa_width, 6, '', '', $frase_empresa, 0, 1, 0, true, 'L', true);
}

// ============================================
// COLUMNA DERECHA: "NOTA DE VENTA" CON ADVERTENCIA
// ============================================
$x_nota = MARGIN_LEFT + $col_logo_width + $col_empresa_width;
$y_nota = MARGIN_TOP + 5;
$ancho_recuadro = $col_nota_width - 5;
$alto_recuadro = 23; // Más alto para incluir la advertencia

// Crear recuadro con fondo blanco
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Rect($x_nota, $y_nota, $ancho_recuadro, $alto_recuadro, 'DF');

// Posicionar dentro del recuadro
$pdf->SetY($y_nota + 3);
$pdf->SetX($x_nota);

// Título "NOTA DE VENTA"
$pdf->SetFont('helvetica', 'B', 10); // Aumentado de 10 a 11
$pdf->Cell($ancho_recuadro, 5, 'NOTA DE VENTA', 0, 1, 'C');

// Línea divisoria
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line($x_nota + 5, $pdf->GetY(), $x_nota + $ancho_recuadro - 5, $pdf->GetY());

// Serie y número
$pdf->SetY($pdf->GetY() + 1);
$pdf->SetX($x_nota);
$pdf->SetFont('helvetica', 'B', 12);
$serie = !empty($this->data["BODY"]["serie"]) ? $this->data["BODY"]["serie"] : '';
$correlativo_num = !empty($this->data["BODY"]["correlativo"]) ?
    str_pad($this->data["BODY"]["correlativo"], 8, '0', STR_PAD_LEFT) : '';
$correlativo_completo = $serie . "-" . $correlativo_num;
$pdf->Cell($ancho_recuadro, 8, $correlativo_completo, 0, 1, 'C');

$pdf->Ln(10);

// ============================================
// DATOS PRINCIPALES - DISEÑO CON CUADRO ÚNICO (ESTILO COMPROBANTE)
// ============================================
$pdf->SetY($pdf->GetY() + 5);
$y_start = $pdf->GetY(); // Guardar posición Y inicial para el cuadro

// ============================================
// FILA 1: FECHA, TIPO, TRACKING
// ============================================
// Fecha de Emisión
$pdf->SetFont('helvetica', 'B', 8); // Tamaño aumentado de 8 a 9
$pdf->Cell(30, 5, 'FECHA DE EMISION:', 0, 0, 'L'); // Ancho ajustado
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(45, 5, date("d-m-Y H:i A", strtotime($this->data["BODY"]["fecha_emision"])), 0, 0, 'L');

// Tipo
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(10, 5, 'TIPO:', 0, 0, 'L'); // Ancho ajustado
$pdf->SetFont('helvetica', '', 8);
$tipo_servicio = !empty($this->data["DESTINO"]["tipo"]) ?
    strtoupper($this->data["DESTINO"]["tipo"]) : 'S/D';
$pdf->Cell(40, 5, $tipo_servicio, 0, 0, 'L');

// Tracking (solo si está habilitado)
$mostrar_tracking = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_tracking'] ?? 0;
if ($mostrar_tracking == 1) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(18, 5, 'TRACKING:', 0, 0, 'L'); // Ancho ajustado
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
$pdf->Cell(30, 5, 'CLIENTE:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Datos del cliente
$cliente_nombre = trim($this->data["BODY"]["remitente_nombres"] . ' ' . $this->data["BODY"]["remitente_apellidos"]);
$pdf->MultiCell(85, 5, $cliente_nombre, 0, 'L', 0, 0); // Ancho ajustado

// Guardar posición Y después del MultiCell para ajuste
$y_despues_cliente = $pdf->GetY();

// DNI del cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(10, 5, $this->data["BODY"]["remitente_tp_docu"] . ':', 0, 0, 'L'); // Ancho ajustado
$pdf->SetFont('helvetica', '', 8);
$dni_cliente = !empty($this->data["BODY"]["remitente_num_docu"]) ?
    $this->data["BODY"]["remitente_num_docu"] : 'S/N';
$pdf->Cell(25, 5, $dni_cliente, 0, 0, 'L'); // Ancho ajustado

// Celular del cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(10, 5, 'CEL:', 0, 0, 'L'); // Ancho ajustado
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
$pdf->Cell(30, 5, 'DIRECCIÓN:', 0, 0, 'L');
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
    $pdf->Cell(30, 5, 'DESTINATARIO:', 0, 0, 'L');
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
    $pdf->Cell(30, 5, 'ORIGEN:', 0, 0, 'L');
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
    $pdf->Cell(30, 5, 'DESTINO:', 0, 0, 'L');
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
// TABLA DE DETALLES - ADAPTATIVA Y MEJORADA (ESTILO COMPROBANTE)
// ============================================
$pdf->SetFont('helvetica', 'B', 9); // Tamaño aumentado de 9 a 10
$pdf->SetFillColor(240, 240, 240);

// Encabezado de tabla (anchos ajustados para nota de venta)
$pdf->Cell(12, 6, 'CANT.', 1, 0, 'C', true);       // Ancho aumentado
$pdf->Cell(18, 6, 'UNIDAD', 1, 0, 'C', true);      // Ancho aumentado
$pdf->Cell(89, 6, 'DESCRIPCIÓN', 1, 0, 'C', true); // Ancho ajustado
$pdf->Cell(20, 6, 'PESO (Kg)', 1, 0, 'C', true);   // Ancho aumentado
$pdf->Cell(25, 6, 'P. UNIT.', 1, 0, 'C', true);    // Ancho aumentado
$pdf->Cell(30, 6, 'TOTAL', 1, 1, 'C', true);       // Ancho aumentado

$suma_total = 0;

// Función mejorada para iniciales de unidad
function getInicialesUnidad($unidad)
{
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

    // Preparar detalles adicionales compactos
    $detalles = trim(
        ($comprobante != '' ? 'COMP: ' . $comprobante : '') .
        ($guia != '' ? '  GUIA: ' . $guia : '') .
        ($observacion != '' ? '  OBS: ' . substr($observacion, 0, 30) . (strlen($observacion) > 30 ? '...' : '') : '')
    );

    $descripcion_completa = $descripcion;

    if ($detalles !== '') {
        // Usar HTML para tamaño de fuente diferente
        $descripcion_completa .= '<br/><span style="font-size:8pt;">' . $detalles . '</span>';
    }

    $pdf->SetFont('helvetica', '', 8); // Tamaño aumentado de 8 a 9

    // ============================================
    // CALCULAR ALTURA DINÁMICAMENTE
    // ============================================
    // Clonar el PDF temporal para medir altura real
    $temp_pdf = clone $pdf;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Medir altura del contenido HTML
    $temp_pdf->writeHTMLCell(85, 0, $x, $y, $descripcion_completa, 0, 1, false, true, 'L', true);
    $altura_real = $temp_pdf->GetY() - $y;

    // Garantizar altura mínima (ajustada para A4)
    $altura_fila = max($altura_real, 6); // Aumentado de 5 a 6

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

    // Celda Descripción con HTML
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
// TOTALES - COMPACTOS (ESTILO COMPROBANTE, ADAPTADO PARA NOTA DE VENTA)
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
// INFORMACIÓN DE PAGO Y DOCUMENTOS RELACIONADOS - ESTRUCTURA DE 2 COLUMNAS
// ============================================
$y_start_seccion = $pdf->GetY();

// Definir anchos de columnas
$col_info_width = 120;  // Ancho para información de pago
$col_docs_width = WIDTH_UTIL - $col_info_width - 5; // Ancho para documentos (con separación)

// ============================================
// FILA 1: TÍTULOS (AMBOS EN LA MISMA LÍNEA)
// ============================================
// Título izquierdo: INFORMACIÓN DE PAGO
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($col_info_width, 8, 'INFORMACIÓN DE PAGO', 0, 0, 'C');

// Título derecho: DOCUMENTOS RELACIONADOS
$docs_x = MARGIN_LEFT + $col_info_width + 5;
$pdf->SetX($docs_x);
$pdf->Cell($col_docs_width, 8, 'DOCUMENTOS RELACIONADOS', 0, 1, 'C');

$pdf->Ln(1);

// ============================================
// SECCIÓN IZQUIERDA: INFORMACIÓN DE PAGO (2 COLUMNAS INTERNAS CON MULTICELL)
// ============================================
// Configurar las posiciones internas
$col_info_left = MARGIN_LEFT; // Columna izquierda de etiquetas
$col_info_right = MARGIN_LEFT + 55; // Columna derecha de valores

// Anchos de las celdas
$ancho_etiqueta_izq = 21;  // Ancho de etiquetas izquierda
$ancho_valor_izq = 30;     // Ancho de valores izquierda (aumentado para MultiCell)
$ancho_etiqueta_der = 23;  // Ancho de etiquetas derecha  
$ancho_valor_der = 35;     // Ancho de valores derecha

// Guardar posición Y inicial de la sección
$y_inicio_seccion = $pdf->GetY();

// ============================================
// FILA 1: Estado de pago y Fecha/hora
// ============================================
$alturas_fila1 = [];

// Estado de pago (columna izquierda)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($col_info_left);
$pdf->Cell($ancho_etiqueta_izq, 6, 'Estado pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

// Determinar estado real
$estado_real = '';
if (isset($this->data["BODY"]["encomienda_pago"])) {
    $estado_real = $this->data["BODY"]["encomienda_pago"];
} elseif (isset($this->data["DESTINO"]["tipo_envio"])) {
    $estado_real = $this->data["DESTINO"]["tipo_envio"];
} elseif (isset($this->data["BODY"]["estado"])) {
    $estado_real = $this->data["BODY"]["estado"];
}
$estado_real = strtoupper(trim($estado_real));

// Guardar posición para MultiCell
$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_izq, 6, $estado_real, 0, 'L', false, 1);
$alturas_fila1['izq'] = $pdf->GetY() - $y_actual;

// Fecha y hora (columna derecha)
$pdf->SetXY($col_info_right, $y_actual);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($ancho_etiqueta_der, 6, 'Fecha/hora:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$fecha_texto = date("d-m-Y H:i", strtotime($this->data["BODY"]["fecha_emision"]));
$pdf->MultiCell($ancho_valor_der, 6, $fecha_texto, 0, 'L', false, 1);
$alturas_fila1['der'] = $pdf->GetY() - $y_actual;

// Ajustar a la altura máxima de la fila
$altura_fila1 = max($alturas_fila1['izq'], $alturas_fila1['der']);
$pdf->SetY($y_inicio_seccion + $altura_fila1);

// ============================================
// FILA 2: Forma de pago y Método de pago
// ============================================
$y_inicio_fila2 = $pdf->GetY();
$alturas_fila2 = [];

// Forma de pago (columna izquierda)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($col_info_left);
$pdf->Cell($ancho_etiqueta_izq, 6, 'Forma pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

if ($estado_real == "PAGADO") {
    $forma_pago_texto = strtoupper($this->data["BODY"]["forma_pago"] ?? 'CONTADO');
} else {
    $forma_pago_texto = '----';
}

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_izq, 6, $forma_pago_texto, 0, 'L', false, 1);
$alturas_fila2['izq'] = $pdf->GetY() - $y_actual;

// Método de pago (columna derecha)
$pdf->SetXY($col_info_right, $y_inicio_fila2);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($ancho_etiqueta_der, 6, 'Método pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

if ($estado_real == "PAGADO") {
    $medio_pago_texto = strtoupper($this->data["BODY"]["medio_pago"] ?? 'EFECTIVO');
} else {
    $medio_pago_texto = '----';
}

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_der, 6, $medio_pago_texto, 0, 'L', false, 1);
$alturas_fila2['der'] = $pdf->GetY() - $y_actual;

// Ajustar a la altura máxima de la fila
$altura_fila2 = max($alturas_fila2['izq'], $alturas_fila2['der']);
$pdf->SetY($y_inicio_fila2 + $altura_fila2);

// ============================================
// FILA 3: Referencia y Código seguridad
// ============================================
$y_inicio_fila3 = $pdf->GetY();
$alturas_fila3 = [];

// Referencia (columna izquierda)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($col_info_left);
$pdf->Cell($ancho_etiqueta_izq, 6, 'Referencia:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

$referencia = !empty(trim($this->data["BODY"]["obs"])) ?
    trim($this->data["BODY"]["obs"]) : '---';

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_izq, 6, $referencia, 0, 'L', false, 1);
$alturas_fila3['izq'] = $pdf->GetY() - $y_actual;

// Código seguridad (columna derecha)
$pdf->SetXY($col_info_right, $y_inicio_fila3);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(25, 6, 'Cód. seguridad:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

$codigo_completo = $this->data["BODY"]["encomienda_pass"] ?? '';
$codigo_oculto = '';
if (!empty($codigo_completo) && strlen($codigo_completo) > 1) {
    $primer_digito = substr($codigo_completo, 0, 1);
    $resto_oculto = str_repeat('x', strlen($codigo_completo) - 1);
    $codigo_oculto = $primer_digito . $resto_oculto;
} elseif (!empty($codigo_completo)) {
    $codigo_oculto = $codigo_completo;
} else {
    $codigo_oculto = 'S/N';
}

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_der, 6, $codigo_oculto, 0, 'L', false, 1);
$alturas_fila3['der'] = $pdf->GetY() - $y_actual;

// Ajustar a la altura máxima de la fila
$altura_fila3 = max($alturas_fila3['izq'], $alturas_fila3['der']);
$pdf->SetY($y_inicio_fila3 + $altura_fila3);

// ============================================
// FILA 4: Vendedor y Cuenta bancaria (CON SOPORTE HTML)
// ============================================
$y_inicio_fila4 = $pdf->GetY();
$alturas_fila4 = [];

// Vendedor (columna izquierda) - Sin cambios
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($col_info_left);
$pdf->Cell($ancho_etiqueta_izq, 6, 'Vendedor:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);

$mostrar_vendedor = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_vendedor'] ?? 0;
if ($mostrar_vendedor == 1) {
    $vendedor_texto = trim($this->data["BODY"]["vendedor_nombres"] . ' ' . $this->data["BODY"]["vendedor_apellidos"]);
    $vendedor_texto = !empty($vendedor_texto) ? $vendedor_texto : '---';
} else {
    $vendedor_texto = '---';
}

$x_actual = $pdf->GetX();
$y_actual = $pdf->GetY();
$pdf->MultiCell($ancho_valor_izq, 6, $vendedor_texto, 0, 'L', false, 1);
$alturas_fila4['izq'] = $pdf->GetY() - $y_actual;

// ============================================
// CUENTA BANCARIA (columna derecha) - CON SOPORTE HTML (como en comprobante.php)
// ============================================
$pdf->SetXY($col_info_right, $y_inicio_fila4);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(26, 6, 'Cuenta bancaria:', 0, 0, 'L');

// Obtener contenido de cuenta bancaria
$nro_cuenta_raw = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"]
    : '';

// Verificar si hay contenido HTML REAL (no solo etiquetas vacías o espacios)
$contenido_sin_html = trim(strip_tags($nro_cuenta_raw));
$hay_contenido_real = !empty($contenido_sin_html);

if ($hay_contenido_real) {
    // Decodificar entidades HTML
    $cuentas_html = html_entity_decode($nro_cuenta_raw, ENT_QUOTES, 'UTF-8');
    
    // Verificar si tiene formato HTML
    $tiene_html = ($cuentas_html != strip_tags($cuentas_html));
    
    // Guardar posición actual para writeHTML
    $current_x = $pdf->GetX();
    $current_y = $pdf->GetY();
    
    if ($tiene_html) {
        // Si tiene etiquetas HTML, usar writeHTMLCell para renderizar completo
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTMLCell(42, 0, $current_x, $current_y, $cuentas_html, 0, 1, 0, true, 'L', true);
        $alturas_fila4['der'] = $pdf->GetY() - $current_y;
    } else {
        // Texto plano, usar MultiCell
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(42, 6, $cuentas_html, 0, 'L', false, 1);
        $alturas_fila4['der'] = $pdf->GetY() - $current_y;
    }
} else {
    // No hay contenido, mostrar "S/C" como texto normal
    $current_x = $pdf->GetX();
    $current_y = $pdf->GetY();
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(42, 6, 'S/C', 0, 'L', false, 1);
    $alturas_fila4['der'] = $pdf->GetY() - $current_y;
}

// Ajustar a la altura máxima de la fila
$altura_fila4 = max($alturas_fila4['izq'], $alturas_fila4['der']);
$pdf->SetY($y_inicio_fila4 + $altura_fila4);

// Guardar posición Y después de la información de pago
$y_after_info = $pdf->GetY();

// ============================================
// SECCIÓN DERECHA: DOCUMENTOS RELACIONADOS (ESTILO COMPROBANTE)
// ============================================
// Posicionar en la columna derecha
$docs_y = $y_start_seccion + 10; // Debajo del título
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

if (
    $this->data["BODY"]["tp_comprobante_ref"] != '0' &&
    !empty($this->data["BODY"]["serie_ref"]) &&
    !empty($this->data["BODY"]["correlativo_ref"])
) {
    $serie_ref = trim($this->data["BODY"]["serie_ref"]);
    $correlativo_ref = trim($this->data["BODY"]["correlativo_ref"]);
    $comprobante_rela = $serie_ref . '-' . $correlativo_ref;
    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $hayDocumentos = true;
    }
}

if ($hayDocumentos) {
    // Título con fondo (ya se dibujó arriba, solo contenido aquí)
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetDrawColor(200, 200, 200);

    // Espaciado debajo del título
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
    $pdf->SetX($docs_x);
    $pdf->MultiCell($col_docs_width, 15, 'Sin documentos relacionados', 0, 'C', false, 1);
    $current_docs_y = $pdf->GetY();
}

// Guardar altura de columna derecha
$altura_docs = $current_docs_y - $docs_y;

// ============================================
// AJUSTAR POSICIÓN Y PARA CONTINUAR
// ============================================
// Tomar la altura máxima entre las dos secciones
$y_max = max($y_after_info, $current_docs_y);
$pdf->SetY($y_max);

// ============================================
// DIBUJAR CUADRO QUE ENVUELVE TODA LA SECCIÓN
// ============================================
$y_end_seccion = $pdf->GetY();
$altura_seccion = $y_end_seccion - $y_start_seccion + 5;

// Dibujar el cuadro alrededor de toda la sección
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);
$pdf->Rect(MARGIN_LEFT, $y_start_seccion - 2, WIDTH_UTIL, $altura_seccion, 'D');

// Línea vertical divisoria entre las dos secciones
$pdf->SetLineWidth(0.1);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(
    $docs_x - 2,
    $y_start_seccion,
    $docs_x - 2,
    $y_end_seccion + 1
);

$pdf->SetY($y_end_seccion + 3);

// ============================================
// TÉRMINOS Y CONDICIONES
// ============================================
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(WIDTH_UTIL, 8, 'CONDICIONES DEL SERVICIO', 0, 1, 'C');

// Obtener términos personalizados
$hay_terminos_personalizados = false;
$terminos_raw = '';

if (isset($this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'])) {
    $terminos_raw = $this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'];
    $terminos_sin_html = strip_tags($terminos_raw);
    $terminos_sin_espacios = trim($terminos_sin_html);
    $hay_terminos_personalizados = !empty($terminos_sin_espacios);
}

if ($hay_terminos_personalizados) {
    // Términos personalizados
    $pdf->SetFont('helvetica', '', 8);
    $terminos_html = html_entity_decode($terminos_raw, ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell(WIDTH_UTIL, 0, MARGIN_LEFT, $pdf->GetY(), $terminos_html, 0, 1, 0, true, 'L', true);
} else {
    // Términos por defecto
    $pdf->SetFont('helvetica', '', 8);
    $url_web = defined('URL_PAGE_WEB_COMPROBANTES') ? URL_PAGE_WEB_COMPROBANTES : 'www.tudominio.com';

    $terminos = "Al recibir el presente documento acepto todos los términos y condiciones del contrato del servicio de transporte detallado en el letrero, banner y/o panel a la vista ubicados en el counter de ventas al momento de la compra, los cuales también se encuentran publicados en la página web " . $url_web . ". Dichas condiciones del presente contrato se encuentran dentro del ámbito de negociación permitida por el Ministerio de Transporte y Comunicaciones.";

    $pdf->MultiCell(WIDTH_UTIL, 5, $terminos, 0, 'L');
}

// URL
$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(WIDTH_UTIL, 5, $url_web, 0, 1, 'L');

// Salida del PDF
ob_end_clean();
$pdf->Output('nota_venta_A4.pdf', 'I');