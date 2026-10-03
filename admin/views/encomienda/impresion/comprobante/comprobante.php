<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php'); // Se cambia la inclusión de FPDF a TCPDF
require_once('public/plugins/print/num_letras.php');

define("WIDTH_LOGO", 30);
define("HEIGHT_LOGO", 25);
define("TEXT_SIZE_COMPROBANTE_SERIE", 9); // Ajustar tamaño de texto
define("TEXT_SIZE_REDUCIDO", 8);
class TCPDF_CellFiti extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public function __construct()
    {
        // Tamaño de impresión real (72.1 mm de ancho)
        parent::__construct('P', 'mm', array(72.1, 297), true, 'UTF-8', false);

        // Márgenes más reducidos (1.5 mm izquierda y derecha para mayor espacio útil)
        $this->SetMargins(1.5, 2, 1.5);
        $this->SetAutoPageBreak(true, 2);
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

        // Solo una verificación de salto de página
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->bMargin)) {
            error_log("Salto de página forzado en Y=" . $this->GetY() . " altura fila=" . $h);
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : $align;

            // Guardar posición antes de MultiCell
            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            // MultiCell en modo "continuo" (0 para último parámetro para no mover X/Y al final)
            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false, 0, '', '', true, 0, false, true, $lineHeight, 'M');

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }

            // Restaurar Y y avanzar en X manualmente
            $this->SetXY($xBefore + $w, $yBefore);
        }

        // Bajar a la siguiente línea
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
error_log("Generando fila en Y=" . $pdf->GetY());

// CABECERA: Fondo + Logo
$logo_a_usar = $this->data["HEADER"]["HEADER_EMPRESA"]["logo"];

// Si RUC separado está activado, preferir logo_encomienda si existe
if (isset($this->data["RUC_ENCOMIENDA"]) && $this->data["RUC_ENCOMIENDA"] == 1) {
    if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]["logo_encomienda"])) {
        $logo_a_usar = $this->data["HEADER"]["HEADER_EMPRESA"]["logo_encomienda"];
    }
}

if (!empty($logo_a_usar) && file_exists($logo_a_usar)) {
    $bgWidth = 72.1 - 3; // Ancho total disponible (aprox 69.1mm)
    $bgHeight = 25; // Altura máxima para el logo
    $bgX = 1.5;
    $bgY = 2;

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($bgX, $bgY, $bgWidth, $bgHeight, 'F');

    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true;
        }
        return false;
    });

    list($imgWidth, $imgHeight) = getimagesize($logo_a_usar);

    // Calcular proporción de la imagen
    $proporcion_imagen = $imgWidth / $imgHeight;
    $proporcion_area = $bgWidth / $bgHeight;

    // NUEVA LÓGICA: Determinar si es rectangular horizontal
    $es_rectangular_horizontal = $proporcion_imagen > 1.5; // Más de 1.5:1 (horizontal alargada)
    $es_rectangular_vertical = $proporcion_imagen < 0.67; // Menos de 0.67:1 (vertical alargada)

    if ($es_rectangular_horizontal) {
        $finalWidth = $bgWidth;
        $finalHeight = $finalWidth / $proporcion_imagen;

        // Si la altura resultante supera el bgHeight, ajustar
        if ($finalHeight > $bgHeight) {
            $finalHeight = $bgHeight;
            $finalWidth = $finalHeight * $proporcion_imagen;
        }

        // Centrar horizontal y verticalmente
        $xLogo = $bgX + ($bgWidth - $finalWidth) / 2;
        $yLogo = $bgY + ($bgHeight - $finalHeight) / 2;

    } elseif ($es_rectangular_vertical) {
        $finalHeight = $bgHeight;
        $finalWidth = $finalHeight * $proporcion_imagen;

        if ($finalWidth > $bgWidth) {
            $finalWidth = $bgWidth;
            $finalHeight = $finalWidth / $proporcion_imagen;
        }

        // Centrar horizontal y verticalmente
        $xLogo = $bgX + ($bgWidth - $finalWidth) / 2;
        $yLogo = $bgY + ($bgHeight - $finalHeight) / 2;

    } else {
        $scale_w = $bgWidth / $imgWidth;
        $scale_h = $bgHeight / $imgHeight;
        $scale = min($scale_w, $scale_h);

        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        $xLogo = $bgX + ($bgWidth - $finalWidth) / 2;
        $yLogo = $bgY + ($bgHeight - $finalHeight) / 2;
    }
    $pdf->Image(
        $logo_a_usar,
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

    restore_error_handler();

    $pdf->Ln(23);
}

// Datos de la empresa
$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell($W_UTIL, 5, $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"], 0, 'C', 0, 1);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($W_UTIL, 4, "RUC: " . $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"], 0, 0.1, 'C');

// NUEVO: Teléfono de la empresa y celular del terminal
$pdf->SetFont('helvetica', '', 7.5);

// Teléfono de la empresa
$telefono_empresa = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["telefono_empresa"])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]["telefono_empresa"]
    : 'S/T';

// Celular del terminal
$celular_terminal = !empty($this->data["HEADER"]["HEADER_TERMINAL"]["celular"])
    ? $this->data["HEADER"]["HEADER_TERMINAL"]["celular"]
    : 'S/C';

$pdf->MultiCell($W_UTIL, 4, "TELF: " . $telefono_empresa . " - CEL: " . $celular_terminal, 0, 'C', 0, 1);

// Dirección fiscal (esto siempre se imprime)
$pdf->SetFont('helvetica', '', 7);
$pdf->MultiCell($W_UTIL, 4, 'DOM. FISCAL: ' . $this->data["HEADER"]["HEADER_EMPRESA"]['direccion_fiscal'], 0, 'C', 0, 1);

// Solo hace espacio si hubo terminales
if (!empty($this->data["TERMINALES"])) {
    $pdf->SetFont('helvetica', '', 7);
    foreach ($this->data["TERMINALES"] as $terminal) {
        $direccion = $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . ' - Cel: ' . $terminal['celular'];
        $pdf->MultiCell($W_UTIL, 4, $direccion, 0, 'C', 0, 0.1);
        $pdf->Ln(0.1);  // Pequeño espacio entre terminales, como tenías
    }
}

// Frase empresa
if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'])) {
    // Solo agregar el contenido si la frase no está vacía
    $pdf->SetFont('helvetica', '', 7);
    $frase_empresa = html_entity_decode($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'], ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell(72, 3.5, '', '', $frase_empresa, 0, 1, 0, true, 'C', true);

    // Ajuste del salto de línea después de la frase
    $pdf->Ln(-3); // Ajustar este valor para que haya espacio después de la frase
} else {
    // No agregamos ningún salto de línea adicional si no hay frase
    $pdf->Ln(-1);
}

// Línea separadora (fina)
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'B');
$pdf->Ln(4);

// TIPO DE COMPROBANTE
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell($W_UTIL, 4, strtoupper($this->data["BODY"]["tp_comprobante"]), 0, 'C', 0, 1);
$pdf->Ln(0.5);

// SERIE Y CORRELATIVO
$correlativo = $this->data["BODY"]["serie"] . "-" . str_pad($this->data["BODY"]["correlativo"], 8, '0', STR_PAD_LEFT);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->MultiCell($W_UTIL, 4, $correlativo, 0, 'C', 0, 1);
$pdf->Ln(0.5);

// Línea separadora superior
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(0.5);

// FECHA - Negrita en "FECHA:"
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 5, 'FECHA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 5, date("d-m-Y H:i A", strtotime($this->data["BODY"]["fecha_emision"])), 0, 1, 'L');

// CLIENTE
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(17, 5, 'CLIENTE:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, $this->data["BODY"]["remitente_nombres"] . ' ' . $this->data["BODY"]["remitente_apellidos"], 0, 'L', 0, 1);
$pdf->Ln(-1);
// DNI Y CELULAR REMITENTE EN MISMA LÍNEA
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 5, $this->data["BODY"]["remitente_tp_docu"] . ': ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 5, $this->data["BODY"]["remitente_num_docu"], 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(8, 5, 'CEL:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 5, !empty($this->data["BODY"]["remitente_celular"]) ? $this->data["BODY"]["remitente_celular"] : 'S/N', 0, 1, 'L');

// Direccion Cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 4, 'DIRECCIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Usar la dirección completa combinada
$direccion_remitente = !empty($this->data["BODY"]["remitente_direccion_completa"])
    ? $this->data["BODY"]["remitente_direccion_completa"]
    : (!empty($this->data["BODY"]["remitente_direccion"])
        ? $this->data["BODY"]["remitente_direccion"]
        : 'S/D');

$pdf->MultiCell(0, 5, $direccion_remitente, 0, 'L', 0, 1);
$pdf->Ln(0.5);

$numero_doc_destinatario = $this->data["BODY"]["destinatario_num_docu"];
$destinatario = $this->data["BODY"]["destinatario_nombres"] . ' ' . $this->data["BODY"]["destinatario_apellidos"];
$obs_destinatario = $this->data["BODY"]["obs_destinatario"] ? ' (' . $this->data["BODY"]["obs_destinatario"] . ')' : '';

// === DESTINATARIO ===
$datos_destinatario = $this->data["HEADER"]["HEADER_EMPRESA"]['datos_destinatario'] ?? 0;

if ($datos_destinatario == 1) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(24.5, 5, 'DESTINATARIO:', 0, 'L', 0, 0);
    $pdf->SetFont('helvetica', '', 8);

    $destinatario = $this->data["BODY"]["destinatario_nombres"] . ' ' . $this->data["BODY"]["destinatario_apellidos"];
    $obs_destinatario = $this->data["BODY"]["obs_destinatario"] ? ' (' . $this->data["BODY"]["obs_destinatario"] . ')' : '';

    $pdf->MultiCell(0, 5, $destinatario . "\n" . ($numero_doc_destinatario == '00000000' ? $obs_destinatario : ''), 0, 'L', 0, 1);

    // DNI Y CELULAR DESTINATARIO EN MISMA LÍNEA
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 5, $this->data["BODY"]["destinatario_tp_docu"] . ': ', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(20, 5, $this->data["BODY"]["destinatario_num_docu"], 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(8, 5, 'CEL:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 5, !empty($this->data["BODY"]["destinatario_celular"]) ? $this->data["BODY"]["destinatario_celular"] : 'S/N', 0, 1, 'L');

    $pdf->Ln(-1); // Espacio adicional después de sección
} else {
    // Si no se muestra destinatario, solo dejar espacio mínimo
    $pdf->Ln(0);
}

// === ORIGEN/DESTINO ===
$origen_destino = $this->data["HEADER"]["HEADER_EMPRESA"]['origen_destino'] ?? 0;

if ($origen_destino == 1) {
    // Determinar si es entrega a domicilio
    $is_entrega_domicilio = isset($this->data["DESTINO"]["tipo"]) &&
        $this->data["DESTINO"]["tipo"] === 'ENTREGA A DOMICILIO';

    // Obtener la configuración de dirección completa
    $mostrar_direccion_completa = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_direccion_completa'] ?? 0;

    // MODIFICACIÓN: ORIGEN
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 5, 'ORIGEN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8.5);

    $terminal_origen = !empty($this->data["DESTINO"]["terminal_origen"]) ? $this->data["DESTINO"]["terminal_origen"] : 'S/D';
    $pdf->Cell(51, 5, strtoupper($terminal_origen), 0, 1, 'L');

    // Mostrar dirección completa solo si está activado Y origen_destino está activo
    if ($mostrar_direccion_completa == 1) {
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(17, 5, '', 0, 0, 'L');

        $direccion_origen = strtoupper(!empty($this->data["DESTINO"]["terminal_origen_direccion"]) ?
            $this->data["DESTINO"]["terminal_origen_direccion"] : 'S/D');

        // AÑADIR UBIGEO COMPLETO DEL ORIGEN
        $ubigeo_origen = '';
        if (!empty($this->data["DESTINO"]["ubigeo_origen_distri"])) {
            $ubigeo_origen = ' - ' . $this->data["DESTINO"]["ubigeo_origen_distri"] . ', ' .
                $this->data["DESTINO"]["ubigeo_origen_provi"] . ', ' .
                $this->data["DESTINO"]["ubigeo_origen_depa"];
        }

        $direccion_completa_origen = $direccion_origen . $ubigeo_origen;
        $pdf->MultiCell(0, 5, $direccion_completa_origen, 0, 'L', 0, 1);
    } else {
        $pdf->Ln(-1);
    }

    // MODIFICACIÓN: DESTINO
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 5, 'DESTINO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 9);

    // Determinar texto de destino
    if ($is_entrega_domicilio) {
        $destino_text = strtoupper(!empty($this->data["DESTINO"]["dir_destino_custom"]) ?
            $this->data["DESTINO"]["dir_destino_custom"] : 'S/D');
    } else {
        $destino_text = strtoupper(!empty($this->data["DESTINO"]["terminal_destino"]) ?
            $this->data["DESTINO"]["terminal_destino"] : 'S/D');
    }
    $pdf->Cell(51, 5, $destino_text, 0, 1, 'L');

    // Mostrar dirección completa solo si está activado Y origen_destino está activo
    if ($mostrar_direccion_completa == 1) {
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(17, 5, '', 0, 0, 'L');

        if ($is_entrega_domicilio) {
            // Para entrega a domicilio
            $direccion_destino = strtoupper(!empty($this->data["DESTINO"]["dir_destino_custom"]) ?
                $this->data["DESTINO"]["dir_destino_custom"] : 'S/D');

            // AÑADIR UBIGEO COMPLETO DEL DESTINO
            $ubigeo_destino = '';
            if (!empty($this->data["DESTINO"]["ubigeo_llegada_distri"])) {
                $ubigeo_destino = ' - ' . $this->data["DESTINO"]["ubigeo_llegada_distri"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_llegada_provi"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_llegada_depa"];
            }

            $direccion_completa_destino = $direccion_destino . $ubigeo_destino;
        } else {
            // Para terminal
            $direccion_destino = strtoupper(!empty($this->data["DESTINO"]["terminal_destino_direccion"]) ?
                $this->data["DESTINO"]["terminal_destino_direccion"] : 'S/D');

            // AÑADIR UBIGEO COMPLETO DEL DESTINO
            $ubigeo_destino = '';
            if (!empty($this->data["DESTINO"]["ubigeo_destino_distri"])) {
                $ubigeo_destino = ' - ' . $this->data["DESTINO"]["ubigeo_destino_distri"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_destino_provi"] . ', ' .
                    $this->data["DESTINO"]["ubigeo_destino_depa"];
            }

            $direccion_completa_destino = $direccion_destino . $ubigeo_destino;
        }

        $pdf->MultiCell(0, 5, $direccion_completa_destino, 0, 'L', 0, 1);
    } else {
        $pdf->Ln(-1);
    }

    // Espacio adicional después de ORIGEN/DESTINO
    $pdf->Ln(0.5);
} else {
    // Si ORIGEN/DESTINO está desactivado, solo dejar espacio mínimo
    $pdf->Ln(-1);
}

// === TRACKING ===
$mostrar_tracking = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_tracking'] ?? 0;
if ($mostrar_tracking == 1) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 5, 'TRACKING:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(51, 5, !empty($this->data["BODY"]["codigo_tracking"]) ? $this->data["BODY"]["codigo_tracking"] : 'S/T', 0, 1, 'L');
}

// MODIFICACIÓN: TIPO - Negrita en "TIPO:"
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 5, 'TIPO:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->Cell(51, 5, strtoupper($this->data["DESTINO"]["tipo"]), 0, 1, 'L');
$pdf->Ln(0.5);

// SECCIÓN DE DETRACCIÓN (solo si afecta_detraccion == 1)
if (isset($this->data["BODY"]["afecta_detraccion"]) && $this->data["BODY"]["afecta_detraccion"] == 1 && !empty($this->data["DATA_DETRACCION"])) {

    $detraccion = $this->data["DATA_DETRACCION"];

    $pdf->Ln(0.5); // Espacio pequeño antes de la sección

    // Línea separadora
    $pdf->SetLineWidth(0.1);
    $pdf->Cell(72, 0, '', 'T');
    $pdf->Ln(0.5);

    // Título DETRACCIÓN
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(72, 5, 'DETRACCION', 0, 1, 'C');
    $pdf->Ln(0.5);

    // Línea separadora
    $pdf->SetLineWidth(0.1);
    $pdf->Cell(72, 0, '', 'T');
    $pdf->Ln(0.5);

    // N. CTA DETRACCIONES - AHORA CON DATO REAL
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(32, 4, 'N. CTA DETRACCIONES:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);

    // Usar la cuenta de detracción desde empresa.nro_cuenta_BN
    $cuenta_detraccion = isset($detraccion['cuenta_detraccion'])
        ? $detraccion['cuenta_detraccion']
        : (isset($this->data["HEADER"]["HEADER_EMPRESA"]['nro_cuenta_BN'])
            ? $this->data["HEADER"]["HEADER_EMPRESA"]['nro_cuenta_BN']
            : 'POR DEFINIR');

    $pdf->Cell(0, 4, $cuenta_detraccion, 0, 1, 'L');

    // METODO DE PAGO
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(26, 4, 'METODO DE PAGO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->Cell(0, 4, isset($detraccion['medio_pago_descripcion']) ? $detraccion['medio_pago_descripcion'] : 'NO ESPECIFICADO', 0, 1, 'L');

    // TIPO DE OPERACIÓN - USAR MultiCell PARA TEXTO LARGO (MOVIDO AQUÍ)
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(25, 4, 'Tipo de Operacion:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);

    $tipo_operacion = '';
    if (isset($this->data["data_venta"]['codigo_tp_operacion_sunat']) && isset($this->data["data_venta"]['tipo_operacion_sunat'])) {
        $tipo_operacion = $this->data["data_venta"]['codigo_tp_operacion_sunat'] . ' ' . $this->data["data_venta"]['tipo_operacion_sunat'];
    } elseif (isset($detraccion['tipo_detraccion_descripcion'])) {
        $tipo_operacion = '1004 Operación Sujeta a Detracción - Servicios de Transporte Carga';
    } else {
        $tipo_operacion = '1004 Operación Sujeta a Detracción - Servicios de Transporte Carga';
    }

    // Obtener posición actual
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Usar MultiCell para texto largo (se ajusta automáticamente)
    $pdf->SetXY($x, $y);
    $pdf->MultiCell(56, 4, $tipo_operacion, 0, 'L', false, 1);

    // B/S SUJETO A DETRACCION - USAR MultiCell PARA TEXTO LARGO (MOVIDO DESPUÉS)
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(29, 4, 'B/S Sujeto Detraccion:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);

    $bien_servicio = isset($detraccion['tipo_detraccion_descripcion'])
        ? $detraccion['tipo_detraccion_codigo'] . ' ' . $detraccion['tipo_detraccion_descripcion']
        : '027 Servicio de transporte de bienes por vía terrestre';

    // Obtener posición actual
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Usar MultiCell para texto largo (se ajusta automáticamente)
    $pdf->SetXY($x, $y);
    $pdf->MultiCell(0, 4, $bien_servicio, 0, 'L', false, 1);

    // P. DETRACCION - Usar el valor decimal y convertirlo
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(24, 4, 'P. DETRACCION:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);

    if (isset($detraccion['porcentaje_decimal'])) {
        // Usar el valor decimal (0.0400) y convertirlo a porcentaje
        $porcentaje_valor = floatval($detraccion['porcentaje_decimal']) * 100;
        $porcentaje = number_format($porcentaje_valor, 2) . '%';
    } elseif (isset($detraccion['porcentaje_operacion'])) {
        // Usar el valor ya como porcentaje (4.0000)
        $porcentaje = number_format(floatval($detraccion['porcentaje_operacion']), 2) . '%';
    } else {
        $porcentaje = '4.00%';
    }
    $pdf->Cell(44, 4, $porcentaje, 0, 1, 'L');

    // MONTO DETRACCION
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell(30, 4, 'MONTO DETRACCION:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);
    $monto_detraccion = isset($detraccion['monto_detraccion']) ? 'S/. ' . number_format($detraccion['monto_detraccion'], 2) : 'S/. 0.00';
    $pdf->Cell(0, 4, $monto_detraccion, 0, 1, 'L');

    $pdf->Ln(1);
    // Línea separadora inferior
    $pdf->SetLineWidth(0.1);
    $pdf->Cell(72, 0, '', 'T');
    $pdf->Ln(2);
} else {
    // Si no hay detracción, solo el espacio normal
    $pdf->Ln(0.5);
}

// TABLA DE DETALLE AJUSTADA AL TICKET (ancho total: 72 mm)
$pdf->SetFont('helvetica', 'B', 7.7);

// Ajusta los anchos para incluir PESO
$pdf->Cell(8, 5, 'CANT', 1, 0, 'C');
$pdf->Cell(8, 5, 'UNID', 1, 0, 'C');
$pdf->Cell(23, 5, 'DESCRIPCION', 1, 0, 'C');  // Reducido de 28 a 20
$pdf->Cell(10, 5, 'PESO', 1, 0, 'C');         // Nueva columna PESO
$pdf->Cell(10, 5, 'P.UNIT', 1, 0, 'C');       // Reducido de 11 a 9
$pdf->Cell(10, 5, 'TOTAL', 1, 1, 'C');        // Reducido de 11 a 9

$suma_total = 0;

// Función para obtener las iniciales de la unidad de medida
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
        'SERVICIO' => 'ZZ'
    ];

    return $iniciales[$unidad] ?? 'PK'; // Default a 'PK' si no se encuentra
}

foreach ($this->data["DETALLE"] as $detalle) {
    $total_item = $detalle["cantidad"] * $detalle["precio"];

    // Obtener peso - asumiendo que viene en el detalle o en BODY
    // Si el peso está en BODY (global para toda la encomienda)
    $peso = isset($this->data["BODY"]["peso_encomienda"]) ?
        $this->data["BODY"]["peso_encomienda"] :
        (isset($detalle["peso"]) ? $detalle["peso"] : '0.00');

    // Si el peso está en KGM, muestra solo el número
    if ($peso > 0) {
        $peso_text = number_format($peso, 2);
    } else {
        $peso_text = '';
    }

    $descripcion = $detalle["descripcion"];
    $observacion = $detalle["obs"];
    $comprobante = !empty($detalle["serie"]) && !empty($detalle["correlativo"]) ? $detalle["serie"] . '-' . $detalle["correlativo"] : '';
    $guia = !empty($detalle["guia_serie"]) && !empty($detalle["guia_correlativo"]) ? $detalle["guia_serie"] . '-' . $detalle["guia_correlativo"] : '';

    // OBTENER LAS INICIALES DE LA UNIDAD DE MEDIDA
    $unidad_medida = isset($detalle["unid_medida"]) ? $detalle["unid_medida"] : 'UNIDAD';
    $iniciales_unidad = getInicialesUnidad($unidad_medida);

    $detalles = trim(
        ($comprobante != '' ? 'COMPROBANTE: ' . $comprobante : '') .
        ($guia != '' ? '   GUIA: ' . $guia : '') .
        ($observacion != '' ? '   OBS: ' . $observacion : '')
    );

    $descripcion_completa = $descripcion;

    if ($detalles !== '') {
        $descripcion_completa .= '<br/><span style="font-size:7pt;">(' . $detalles . ')</span>';
    }

    $pdf->SetFont('helvetica', '', 7);

    // Clonar el PDF temporal para medir altura real
    $temp_pdf = clone $pdf;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $temp_pdf->writeHTMLCell(23, 0, $x, $y, $descripcion_completa, 0, 1, false, true, 'L', true);
    $altura_real = $temp_pdf->GetY() - $y;

    // Garantizar altura mínima si es muy pequeño
    $altura_fila = max($altura_real, 5);

    // Imprimir celdas normales
    $pdf->Cell(8, $altura_fila, $detalle["cantidad"], 1, 0, 'C');
    $pdf->Cell(8, $altura_fila, $iniciales_unidad, 1, 0, 'C');

    $x_desc = $pdf->GetX();
    $y_desc = $pdf->GetY();

    $pdf->writeHTMLCell(23, $altura_fila, $x_desc, $y_desc, $descripcion_completa, 1, 0, false, true, 'L', true);

    $pdf->SetXY($x_desc + 23, $y_desc);
    $pdf->Cell(10, $altura_fila, $peso_text, 1, 0, 'C');  // Columna PESO
    // Celda PRECIO UNITARIO
    $x_precio = $pdf->GetX();
    $y_precio = $pdf->GetY();
    $precio_formateado = number_format($detalle["precio"], 2, '.', ',');
    $pdf->MultiCell(10, $altura_fila, $precio_formateado, 1, 'R', false, 0, $x_precio, $y_precio, true, 0, false, true, $altura_fila, 'M');

    // Avanzar X manualmente después del precio
    $pdf->SetXY($x_precio + 10, $y_precio);

    // Celda TOTAL
    $x_total = $pdf->GetX();
    $y_total = $pdf->GetY();
    $total_formateado = number_format($total_item, 2, '.', ',');
    $pdf->MultiCell(10, $altura_fila, $total_formateado, 1, 'R', false, 1, $x_total, $y_total, true, 0, false, true, $altura_fila, 'M');

    $suma_total += $total_item;
}

// Ajustar la posición del cursor después de la tabla de detalles
$pdf->Ln(2); // Agregar un espacio adicional si es necesario

$pdf->SetFont('helvetica', '', 8);

// Datos de totales con etiquetas cortas
$items_totales = [];

// Solo mostrar Gravado si es mayor a 0
if (isset($this->data["BODY"]["op_gravada"]) && floatval($this->data["BODY"]["op_gravada"]) > 0) {
    $items_totales['Gravado:'] = $this->data["BODY"]["op_gravada"];
}

// Solo mostrar IGV si es mayor a 0
if (isset($this->data["BODY"]["op_igv"]) && floatval($this->data["BODY"]["op_igv"]) > 0) {
    $items_totales['IGV:'] = $this->data["BODY"]["op_igv"];
}

// Solo mostrar Exonerado si es mayor a 0
if (isset($this->data["BODY"]["op_exonerada"]) && floatval($this->data["BODY"]["op_exonerada"]) > 0) {
    $items_totales['Exonerado:'] = $this->data["BODY"]["op_exonerada"];
}

// Solo mostrar Inafecto si es mayor a 0
if (isset($this->data["BODY"]["op_inafecta"]) && floatval($this->data["BODY"]["op_inafecta"]) > 0) {
    $items_totales['Inafecto:'] = $this->data["BODY"]["op_inafecta"];
}

// IMPORTE siempre se muestra (es el total)
$items_totales['IMPORTE:'] = $suma_total;

foreach ($items_totales as $label => $value) {
    $value = is_numeric($value) ? $value : 0;

    // Negrita solo en IMPORTE
    if ($label === 'IMPORTE:') {
        $pdf->SetFont('helvetica', 'B', 8);
    } else {
        $pdf->SetFont('helvetica', '', 8);
    }
    $pdf->SetX(72.1 - 1.5 - 53);

    // Mostrar la etiqueta (como "IMPORTE:") y los valores (como el monto)
    $pdf->Cell(18, 4, $label, 0, 0);  // Etiqueta
    $pdf->Cell(10, 4, 'S/. ', 0, 0);  // Moneda
    $pdf->Cell(25, 4, number_format($value, 2), 0, 1, 'R');
}

$pdf->Ln(0.5);
$pdf->SetFont('helvetica', 'B', 8);
$letras = 'SON: ';
$pdf->SetFont('helvetica', '', 8);
$letras .= numtoletras(number_format($suma_total, 2, '.', ''));
$pdf->MultiCell(72, 5, $letras, 0, 'L');
$pdf->Ln(0.5);

// ALTURA ACTUAL Y POSICIONAMIENTO
$posY_QR = $pdf->GetY();

// === COLUMNA IZQUIERDA: QR ===
if (!empty($this->data["BODY"]["cod_qr"])) {
    $qrPath = $this->data["BODY"]["cod_qr"];

    if (strpos($qrPath, 'private/empresa/') !== false && file_exists($qrPath)) {
        $pdf->Image($qrPath, 5, $posY_QR, 25, 25, '', '', '', false, 300, '', false, false, 0);
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, 5, $posY_QR, 25, 25, '', '', '', false, 300, '', false, false, 0);
    }
}

// ALTURA ACTUAL Y POSICIONAMIENTO
$posY_QR = $pdf->GetY();

// === COLUMNA IZQUIERDA: QR ===
if (!empty($this->data["BODY"]["cod_qr"])) {
    $qrPath = $this->data["BODY"]["cod_qr"];

    if (strpos($qrPath, 'private/empresa/') !== false && file_exists($qrPath)) {
        $pdf->Image($qrPath, 5, $posY_QR, 25, 25, '', '', '', false, 300, '', false, false, 0);
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, 5, $posY_QR, 25, 25, '', '', '', false, 300, '', false, false, 0);
    }
}

// === COLUMNA DERECHA: TODOS LOS DATOS ALINEADOS CON EL QR ===
$pdf->SetXY(32, $posY_QR); // Posicionar a la derecha del QR

// === FECHA Y HORA ===
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(21, 4, date("d-m-Y H:i A", strtotime($this->data["BODY"]["fecha_emision"])), 0, 1, 'L');

// === FORMA DE PAGO ===
$pdf->SetX(32);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(22, 4, 'Forma de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);

// Lógica:
$mostrar_forma_pago = '';
if (strtoupper($this->data["BODY"]["estado"]) == "PAGADO") {
    $mostrar_forma_pago = strtoupper($this->data["BODY"]["forma_pago"]);
} elseif (
    !empty($this->data["BODY"]["encomienda_pago"]) &&
    strtoupper($this->data["BODY"]["encomienda_pago"]) == "CREDITO"
) {
    $mostrar_forma_pago = 'CRÉDITO';
}

$pdf->MultiCell(0, 4, $mostrar_forma_pago, 0, 'L');

// === MÉTODO DE PAGO ===
$pdf->SetX(32);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(23, 4, 'Método de pago:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$x = $pdf->GetX();
$y = $pdf->GetY();

$metodo_pago = strtoupper($this->data["BODY"]["estado"]) == "PAGADO" ? strtoupper($this->data["BODY"]["medio_pago"]) : '---';

$pdf->SetXY($x, $y);
$pdf->MultiCell(20, 4, $metodo_pago, 0, 'L', false, 1);

// === REFERENCIA ===
$pdf->SetX(32);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(16, 4, 'Referencia:', 0, 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$x = $pdf->GetX();
$y = $pdf->GetY();

$referencia = !empty(trim($this->data["BODY"]["obs"])) ? $this->data["BODY"]["obs"] : '---';

$pdf->SetXY($x, $y);
$pdf->MultiCell(0, 4, $referencia, 0, 'L', false, 1);

// === CÓDIGO DE SEGURIDAD ===
$pdf->SetX(32);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 4, 'Código seguridad:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

$codigo_completo = $this->data["BODY"]["encomienda_pass"] ?? '';
$codigo_oculto = '';

// Función para ocultar dígitos (excepto el primero)
if (!empty($codigo_completo) && strlen($codigo_completo) > 1) {
    $primer_digito = substr($codigo_completo, 0, 1);
    $resto_oculto = str_repeat('x', strlen($codigo_completo) - 1);
    $codigo_oculto = $primer_digito . $resto_oculto;
} elseif (!empty($codigo_completo)) {
    // Si solo tiene un dígito
    $codigo_oculto = $codigo_completo;
}

$pdf->Cell(0, 4, $codigo_oculto, 0, 1, 'L');

// === VENDEDOR ===
$mostrar_vendedor = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_vendedor'] ?? 0;
if ($mostrar_vendedor == 1) {
    $pdf->SetX(32);
    $pdf->SetFont('helvetica', '', 8);

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $vendedor_text = trim(
        $this->data["BODY"]["vendedor_nombres"]
    );

    $pdf->SetXY($x, $y);
    $pdf->MultiCell(37, 4, $vendedor_text ?: '---', 0, 'L', false, 1);
}

$pdf->Ln(6);

// === VENTAS PAGADAS ===
if (isset($this->data["mostrar_doc_relacionados"]) && $this->data["mostrar_doc_relacionados"] === 1) {
    if (isset($this->data["ventas_pagadas"]) && !empty($this->data["ventas_pagadas"])) {

        $pdf->Ln(1);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(0, 5, 'COMPROBANTES RELACIONADOS:', 0, 'L', 0, 1);

        $pdf->SetFont('helvetica', '', 8);

        foreach ($this->data["ventas_pagadas"] as $venta) {
            $texto = $venta["serie"] . '-' . $venta["correlativo"];

            $pdf->MultiCell(0, 4, $texto, 0, 'L', 0, 1);
        }
    }
    $pdf->Ln(0);
}
// Calcular la altura real usada por los datos de la derecha
$altura_datos_derecha = $pdf->GetY() - $posY_QR;
$altura_a_usar = max($altura_datos_derecha, 20);
$pdf->SetY($posY_QR + $altura_a_usar);
$pdf->Ln(5.5);

//  CUENTA BANCARIA (SOLO SI HAY CONTENIDO REAL)
$nro_cuenta_raw = !empty($this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]["nro_cuenta_bancaria"]
    : '';

// Verificar si hay contenido HTML REAL (no solo etiquetas vacías)
$contenido_sin_html = trim(strip_tags($nro_cuenta_raw));
$hay_cuentas_personalizadas = !empty($contenido_sin_html);

if ($hay_cuentas_personalizadas) {
    $cuentas_html = html_entity_decode($nro_cuenta_raw, ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell($W_UTIL, 5, $pdf->GetX(), $pdf->GetY(), $cuentas_html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(1);
}

// DOCUMENTOS RELACIONADOS
$guia_rela = '';
$comprobante_rela = '';
$hayDocumentos = false;

// Validar guía de remisión (ambos campos necesarios)
if (!empty($this->data["BODY"]["guia_serie"]) && !empty($this->data["BODY"]["guia_correlativo"])) {
    $guia_rela = trim($this->data["BODY"]["guia_serie"]) . '-' . trim($this->data["BODY"]["guia_correlativo"]);
    if (!empty($guia_rela) && $guia_rela != '-') {
        $hayDocumentos = true;
    }
}

// Validar comprobante relacionado
if (
    $this->data["BODY"]["tp_comprobante_ref"] != '0' &&
    !empty($this->data["BODY"]["serie_ref"]) &&
    !empty($this->data["BODY"]["correlativo_ref"])
) {

    $comprobante_rela = trim($this->data["BODY"]["serie_ref"]) . '-' . trim($this->data["BODY"]["correlativo_ref"]);
    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $hayDocumentos = true;
    }
}

// Solo mostrar la sección si hay al menos un documento relacionado válido
if ($hayDocumentos) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell($W_UTIL, 5, 'DOCS RELACIONADOS', 0, 'C', false);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln(-1);
    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $pdf->MultiCell($W_UTIL, 5, 'Comprobante : ' . $comprobante_rela, 0, 'L', false);
    }
    $pdf->Ln(-1);
    if (!empty($guia_rela) && $guia_rela != '-') {
        $pdf->MultiCell($W_UTIL, 5, 'Guia remision remitente : ' . $guia_rela, 0, 'L', false);
    }
    $pdf->Ln(-1);
}

// BOLETA DE VENTA ELECTRONICA
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell($W_UTIL, 5, 'Usted puede consultar su CPE desde su Clave SOL o desde nuestra Página Web.', 0, 'L', false);
$pdf->Ln(1);

// CONDICIONES DE SERVICIO
$terminos_empresa = !empty($this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones']
    : '';

$hay_terminos_personalizados = false;
$terminos_raw = '';

if (isset($this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'])) {
    $terminos_raw = $this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'];

    // Verificar si no está vacío después de quitar espacios y etiquetas HTML
    $terminos_sin_html = strip_tags($terminos_raw);
    $terminos_sin_espacios = trim($terminos_sin_html);

    $hay_terminos_personalizados = !empty($terminos_sin_espacios);
}

$pdf->SetAutoPageBreak(true, 5);

if ($hay_terminos_personalizados) {
    // USAR TÉRMINOS PERSONALIZADOS - SIN TÍTULO
    $pdf->SetFont('helvetica', '', 8);

    // Decodificar entidades HTML
    $terminos_html = html_entity_decode($terminos_raw, ENT_QUOTES, 'UTF-8');

    // Usar writeHTMLCell para renderizar HTML
    $pdf->writeHTMLCell($W_UTIL, 5, 1.5, $pdf->GetY(), $terminos_html, 0, 1, 0, true, 'J', true);

    // Ajustar posición Y después del HTML
    $pdf->SetY($pdf->GetY() + 1);
} else {
    // USAR EL TEXTO POR DEFECTO - CON TÍTULO
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(72, 5, 'CONDICIONES DE SERVICIOS', 0, 'C', false);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(1.5, $pdf->GetY());

    // CORREGIR: URL_PAGE_WEB_COMPROBANTES debe estar definida
    $url_web = defined('URL_PAGE_WEB_COMPROBANTES') ? URL_PAGE_WEB_COMPROBANTES : 'www.tudominio.com';

    $pdf->MultiCell($W_UTIL, 5, 'Al recibir el presente DOCUMENTO acepto todos los términos y condiciones del contrato del servicio de transporte detallado en el letrero, banner y/o panel a la vista ubicados en el counter de ventas al momento de la compra, los cuales también se encuentran publicados en la página web ' . $url_web, 0, 'J', false, 1, '', '', true, 0, false, true, 0, 'T');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(1.5, $pdf->GetY() + 1);
    $pdf->MultiCell($W_UTIL, 5, 'Dichas condiciones del presente contrato se encuentran dentro del ámbito de negociación permitida por el Ministerio de Transporte y Comunicaciones.', 0, 'J', false, 1, '', '', true, 0, false, true, 0, 'T');
}

//URL 

$pdf->SetFont('helvetica', 'B', 8);
$url_web = defined('URL_PAGE_WEB_COMPROBANTES') ? URL_PAGE_WEB_COMPROBANTES : 'www.tudominio.com';
$pdf->MultiCell($W_UTIL, 5, $url_web, 0, 'J', false, 1, '', '', true, 0, false, true, 0, 'T');

error_log("Y antes de MultiCell largo: " . $pdf->GetY());

$pdf->IncludeJS("print(false);");

if ($this->data["HEADER"]["HEADER_EMPRESA"]["impresion_ecompleta"] == 1) {
    // =============================================
    // PRIMERO: AGREGAR COMANDO DE CORTE PARCIAL DESPUÉS DE NOTA DE VENTA
    // =============================================

    // Agregar un espacio al final de la nota de venta
    $pdf->Ln(5);

    // =============================================
    // SEGUNDO: TRANSPORTISTA EN NUEVA PÁGINA
    // =============================================

    // Forzar nueva página
    $pdf->AddPage();

    // Incluir transportista.php - USAR RUTA CORRECTA
    $es_interno = true; // Bandera para indicar que es una inclusión

    // NOTA: La ruta debe ser relativa desde nota_venta.php
    // Si están en la misma carpeta:
    $ruta_transportista = dirname(__FILE__) . '/../transportista.php';

    // Incluir el archivo transportista.php
    if (file_exists($ruta_transportista)) {
        ob_start();
        include $ruta_transportista;
        $output = ob_get_clean();

        // Si hubo algún error en la inclusión
        if (!empty($output)) {
            error_log("Output no esperado de transportista.php: " . substr($output, 0, 100));
        }
    } else {
        error_log("Error: No se encontró archivo transportista.php en: " . $ruta_transportista);
    }

    // Agregar comando de corte parcial después de transportista
    $pdf->Ln(5);

    // =============================================
    // TERCERO: ARCHIVO EN NUEVA PÁGINA
    // =============================================

    // Forzar nueva página
    $pdf->AddPage();

    // Ruta para archivo.php
    $ruta_archivo = dirname(__FILE__) . '/../archivo.php';

    // Incluir archivo.php
    if (file_exists($ruta_archivo)) {
        ob_start();
        include $ruta_archivo;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log("Output no esperado de archivo.php: " . substr($output, 0, 100));
        }
    } else {
        error_log("Error: No se encontró archivo archivo.php en: " . $ruta_archivo);
    }

    // Agregar comando de corte COMPLETO al final
    $pdf->Ln(5);

    // Para impresión automática cuando esté listo todo
    $pdf->IncludeJS("print(true);");
} else {
    // Si NO es impresión completa, solo imprimir nota de venta con corte completo
    $pdf->Ln(5);
    $pdf->IncludeJS("print(true);");
}

ob_clean();
$pdf->Output('ticket_completo.pdf', 'I');
