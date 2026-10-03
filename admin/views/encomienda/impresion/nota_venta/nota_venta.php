<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php'); // Ya se incluye TCPDF correctamente
require_once('public/plugins/print/num_letras.php');

define("WIDTH_LOGO", 25);
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
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->MultiCell(72, 5, $this->data["HEADER"]["HEADER_EMPRESA"]["razon_social"], 0, 'C');
$pdf->Cell(72, 4, "RUC: " . $this->data["HEADER"]["HEADER_EMPRESA"]["num_docu"], 0, 1, 'C');
$pdf->Ln(1);

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
        $pdf->Ln(0.1); 
    }
}

// Frase empresa
if (!empty($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'])) {
    $pdf->SetFont('helvetica', '', 7);
    $frase_empresa = html_entity_decode($this->data["HEADER"]["HEADER_EMPRESA"]['frase_empresa'], ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell(72, 3.5, '', '', $frase_empresa, 0, 1, 0, true, 'C', true);
    $pdf->Ln(-3);
} else {
    $pdf->Ln(-1);
}

// TIPO COMPROBANTE SERIE - EN UNA SOLA LÍNEA
$pdf->Cell($W_UTIL, 0, '', 'B', 1);
$pdf->Ln(1); // Reducir espacio

// Crear el texto completo en una línea
$texto_completo = "NOTA DE VENTA - " .
    $this->data["BODY"]["serie"] . "-" .
    str_pad($this->data["BODY"]["correlativo"], 8, '0', STR_PAD_LEFT);

$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell(72, 4, $texto_completo, 0, 'C', false, 1);
$pdf->Ln(1); // Salto de línea después

$pdf->Cell($W_UTIL, 0, '', 'T', 1);
$pdf->Ln(-3);

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
$pdf->Cell(17, 5, 'DIRECCIÓN:', 0, 0, 'L');
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
    
    $pdf->Ln(-1);
} else {
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
    $pdf->SetFont('helvetica', 'B', 9.5);

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

$pdf->Ln(1);

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
    $pdf->Cell(10, $altura_fila, number_format($detalle["precio"], 2), 1, 0, 'R');
    $pdf->Cell(10, $altura_fila, number_format($total_item, 2), 1, 1, 'R');

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
    // Ajuste manual para alinear a la derecha dentro del ticket de 72.1mm
    // Usamos 72.1 - 1.5 para el margen derecho y el ancho total de las celdas (19+18+16 = 53mm)
    $pdf->SetX(72.1 - 1.5 - 53);  // Ancho total - margen derecho - ancho de todas las celdas

    // Mostrar la etiqueta (como "IMPORTE:") y los valores (como el monto)
    $pdf->Cell(18, 4, $label, 0, 0);  // Etiqueta
    $pdf->Cell(10, 4, 'S/. ', 0, 0);  // Moneda
    $pdf->Cell(25, 4, number_format($value, 2), 0, 1, 'R');  // Importe, alineado a la derecha
}

$pdf->Ln(0.5);
$pdf->SetFont('helvetica', 'B', 8);
$letras = 'SON: ';
$pdf->SetFont('helvetica', '', 8);
$letras .= numtoletras(number_format($suma_total, 2, '.', ''));
$pdf->MultiCell(72, 5, $letras, 0, 'L');
$pdf->Ln(-2);

// INFORMACIÓN DE PAGO Y SEGURIDAD (COMPACTADA)
$pdf->SetFont('helvetica', '', 8);

// Obtener estado real del pago
$estado_real = '';
if (isset($this->data["BODY"]["encomienda_pago"])) {
    $estado_real = $this->data["BODY"]["encomienda_pago"];
} elseif (isset($this->data["DESTINO"]["tipo_envio"])) {
    $estado_real = $this->data["DESTINO"]["tipo_envio"];
} elseif (isset($this->data["BODY"]["estado"])) {
    $estado_real = $this->data["BODY"]["estado"];
}

// Convertir a mayúsculas
$estado_real = strtoupper(trim($estado_real));

// --- LÍNEA 1: Estado de Pago ---
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(21, 5, 'Estado de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, $estado_real, 0, 1, 'L');
$pdf->Ln(-1);

// --- LÍNEA 2: Forma de Pago ---
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(21, 5, 'Forma de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8.5);

if ($estado_real == "PAGADO") {
    $forma_pago_texto = strtoupper($this->data["BODY"]["forma_pago"] ?? 'CONTADO');
} else {
    $forma_pago_texto = '----';
}

$pdf->Cell(0, 5, $forma_pago_texto, 0, 1, 'L');
$pdf->Ln(-1);

// --- LÍNEA 3: Método de Pago ---
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(22, 5, 'Método de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 8);

if ($estado_real == "PAGADO") {
    $medio_pago_texto = strtoupper($this->data["BODY"]["medio_pago"] ?? 'EFECTIVO');
} else {
    $medio_pago_texto = '----';
}

$pdf->Cell(0, 5, $medio_pago_texto, 0, 1, 'L');
$pdf->Ln(-1);
// --- LÍNEA 3: Referencia + Código de Seguridad ---
// Guardar posición Y actual
$y_actual = $pdf->GetY();

// Referencia (lado izquierdo)
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 5, 'Referencia:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

$referencia = !empty(trim($this->data["BODY"]["obs"])) ? $this->data["BODY"]["obs"] : '---';
// Limitar longitud de referencia para que no se solape
if (strlen($referencia) > 14) {
    $referencia = substr($referencia, 0, 14) . '...';
}

$pdf->MultiCell(14, 5, $referencia, 0, 'L', 0, 0);
// Código de seguridad (lado derecho)
// Calcular posición X para el código de seguridad
$codigo_seguridad_x = 35; // Posición fija para alinear

// Preparar código oculto
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

// Posicionar para código de seguridad
$pdf->SetXY($codigo_seguridad_x, $y_actual);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, 'Código seguridad:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 5, $codigo_oculto, 0, 1, 'L');

// Ajustar posición Y para siguiente elemento
$pdf->SetY($y_actual + 5);

// FECHA Y VENDEDOR EN LA MISMA LÍNEA
$pdf->SetFont('helvetica', '', 8);

// Fecha siempre se muestra
$pdf->Cell(30, 5, date("d-m-Y", strtotime($this->data["BODY"]["fecha_emision"])) . " " . date("H:i A", strtotime($this->data["BODY"]["fecha_emision"])), 0, 0, 'L');

// Obtener el valor del checkbox (debe estar definido antes)
$mostrar_vendedor = $this->data["HEADER"]["HEADER_EMPRESA"]['mostrar_vendedor'] ?? 0;

// Solo mostrar el vendedor si el checkbox está activado (valor = 1)
if ($mostrar_vendedor == 1) {
    // Posicionar para el vendedor (ajustar la posición según tu diseño)
    $pdf->SetX(35); // Ajusta este valor según necesites

    // MultiCell para el nombre del vendedor
    $pdf->MultiCell(30, 5, $this->data["BODY"]["vendedor_nombres"], 0, 'L');

    // Agregar un espacio después del nombre
    $pdf->Ln(1);
} else {
    // Si no se muestra el vendedor, solo agregar un salto de línea para mantener el formato
    $pdf->Ln(6); // Ajusta este valor según la altura que ocuparía el vendedor
}

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

    if (!empty($comprobante_rela) && $comprobante_rela != '-') {
        $pdf->MultiCell($W_UTIL, 5, 'Comprobante : ' . $comprobante_rela, 0, 'L', false);
    }
    if (!empty($guia_rela) && $guia_rela != '-') {
        $pdf->MultiCell($W_UTIL, 5, 'Guia remision remitente : ' . $guia_rela, 0, 'L', false);
    }

    // Espacio normal después de la sección (2mm)
    $pdf->Ln(-1);
}

// CONDICIONES DE SERVICIO
// OBTENER Y PROCESAR TÉRMINOS
$terminos_empresa = !empty($this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones'])
    ? $this->data["HEADER"]["HEADER_EMPRESA"]['terminos_condiciones']
    : '';

// Verificar si hay texto válido (no vacío, no solo espacios)
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


ob_end_clean();
$pdf->Output('ticket.pdf', 'I');
