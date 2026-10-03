<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

class TCPDF_A4 extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    public $data;
    
    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 10);
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
    
    function Row($data, $lineHeight = 6, $border = 0, $fill = false, $align = 'L', $boldColumns = [])
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
            
            $this->MultiCell($w, $lineHeight, $data[$i], $border, $a, $fill, 0, '', '', true, 0, false, true, $lineHeight, 'M');
            
            if (in_array($i, $boldColumns)) {
                $this->SetFont('', '');
            }
            
            $this->SetXY($xBefore + $w, $yBefore);
        }
        
        $this->Ln($h);
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
// Crear PDF A4
$pdf = new TCPDF_A4();
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->data = $this->data; // Pasar datos a la clase

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// ========== HEADER PERSONALIZADO ==========
// Guardar posición Y actual para el header
$headerY = 10;

// Definir columnas para el layout
$columnaIzquierda = 10; // 15mm desde el borde izquierdo
$columnaDerecha = 150;  // 150mm desde el borde izquierdo (para la sección derecha)
$anchoColumnaIzq = 90;  // Ancho de la columna izquierda (logo + info empresa)
$anchoColumnaDer = 70;  // Ancho de la columna derecha (comprobante)

// ====== COLUMNA IZQUIERDA (Logo y datos de empresa) ======

// Logo - ajustable a diferentes formatos
if (!empty($pdf->data['emisor']["logo"]) && file_exists($pdf->data['emisor']["logo"])) {
    set_error_handler(function ($errno, $errstr) {
        if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
            return true;
        }
        return false;
    });
    
    // Obtener dimensiones de la imagen
    $imageInfo = @getimagesize($pdf->data['emisor']["logo"]);
    if ($imageInfo) {
        $imgWidth = $imageInfo[0];
        $imgHeight = $imageInfo[1];
        $aspectRatio = $imgHeight > 0 ? $imgWidth / $imgHeight : 1;
        
        // Definir altura máxima y calcular ancho proporcional
        $maxHeight = 25; // Altura máxima del logo
        $calculatedWidth = $maxHeight * $aspectRatio;
        
        // Limitar el ancho máximo
        $logoWidth = min($calculatedWidth, 50); // Máximo 50mm de ancho
        $logoHeight = $maxHeight;
        
        // Si es una imagen muy ancha, ajustar proporcionalmente
        if ($aspectRatio > 2) { // Si es muy ancha (rectangular horizontal)
            $logoWidth = 45;
            $logoHeight = 45 / $aspectRatio;
        }
        
        $pdf->Image($pdf->data['emisor']["logo"], $columnaIzquierda, $headerY, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0);
        
        // Posición X para los datos de empresa (al lado del logo)
        $datosEmpresaX = $columnaIzquierda + $logoWidth;
    } else {
        $datosEmpresaX = $columnaIzquierda;
    }
    restore_error_handler();
} else {
    $datosEmpresaX = $columnaIzquierda;
}

// Datos de la empresa (al lado del logo)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY($datosEmpresaX, $headerY);
$pdf->MultiCell(85, 5, $pdf->data["emisor"]["razon_social"], 0, 'L');

// RUC
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY($datosEmpresaX, $pdf->GetY());
$pdf->MultiCell(85, 5, 'RUC: ' . $pdf->data["emisor"]["num_docu"], 0, 'L');

// =========================================================
// 1. TELÉFONO DE LA EMPRESA + CELULAR DE LA TERMINAL
// =========================================================
if (!empty($pdf->data['emisor']['telefono_empresa'])) {
    $pdf->SetFont('helvetica', '', 8);
    
    $telefono_completo = "Teléfono: " . $pdf->data['emisor']['telefono_empresa'];
    
    // Buscar el celular de la terminal específica que hizo el comprobante
    $id_terminal_venta = $pdf->data["data_venta"]["id_terminal"] ?? 0;
    $celular_encontrado = '';
    
    if (!empty($pdf->data["TERMINALES"]) && $id_terminal_venta > 0) {
        foreach ($pdf->data["TERMINALES"] as $terminal) {
            if (isset($terminal['id_terminal']) && $terminal['id_terminal'] == $id_terminal_venta) {
                if (!empty($terminal['celular'])) {
                    $celular_encontrado = ' - ' . $terminal['celular'];
                }
                break;
            }
        }
    }
    
    // Si no encuentra el celular de la terminal específica, intenta con el primer terminal
    if (empty($celular_encontrado) && !empty($pdf->data["TERMINALES"])) {
        $primer_terminal = reset($pdf->data["TERMINALES"]);
        if (!empty($primer_terminal['celular'])) {
            $celular_encontrado = ' - ' . $primer_terminal['celular'];
        }
    }
    
    $telefono_completo .= $celular_encontrado;
    $pdf->SetXY($datosEmpresaX, $pdf->GetY());
    $pdf->MultiCell(85, 4, $telefono_completo, 0, 'L');
}

// Domicilio Fiscal
$pdf->SetFont('helvetica', '', 7);
$pdf->SetXY($datosEmpresaX, $pdf->GetY());
$pdf->MultiCell(85, 4, 'DOM. FISCAL: ' . $pdf->data["emisor"]["direccion_fiscal"], 0, 'L');

// ====== COLUMNA DERECHA (Comprobante en recuadro) ======

// Definir posición y tamaño del recuadro
$cuadroX = $columnaDerecha;
$cuadroY = $headerY;
$cuadroAncho = 50;
$cuadroAlto = 20; // Altura estimada del recuadro

// Dibujar recuadro
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);
$pdf->Rect($cuadroX, $cuadroY, $cuadroAncho, $cuadroAlto);

// Título del comprobante dentro del recuadro (centrado)
$pdf->SetFont('helvetica', 'B', 9);
$tituloComprobante = strtoupper($pdf->data["data_venta"]["tp_comprobante"]);
$pdf->SetXY($cuadroX, $cuadroY + 3);
$pdf->MultiCell($cuadroAncho, 4, $tituloComprobante, 0, 'C');

// Guardar posición Y después del título
$posYDespuesTitulo = $pdf->GetY();

// Dibujar línea divisoria debajo del título
$pdf->SetLineWidth(0.2); // Línea más delgada
$pdf->SetDrawColor(150, 150, 150); // Color gris para la línea divisoria
$pdf->Line($cuadroX + 5, $posYDespuesTitulo, $cuadroX + $cuadroAncho - 5, $posYDespuesTitulo);

// Número de serie y correlativo
$correlativo = $pdf->data["data_venta"]["serie"] . "-" . str_pad($pdf->data["data_venta"]["correlativo"], 8, '0', STR_PAD_LEFT);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY($cuadroX, $posYDespuesTitulo + 1); // +1 para separar de la línea
$pdf->MultiCell($cuadroAncho, 5, $correlativo, 0, 'C');

// ====== LÍNEA SEPARADORA ======
// Calcular la posición Y más baja entre ambas columnas
$posicionFinalIzq = $pdf->GetY();
$posicionFinalDer = $cuadroY + $cuadroAlto;
$posicionMasBaja = max($posicionFinalIzq, $posicionFinalDer) + 5;

// Dibujar línea separadora principal
$pdf->SetLineWidth(0.3);
$pdf->SetDrawColor(0, 0, 0); // Volver a color negro
$pdf->Line(10, $posicionMasBaja, 200, $posicionMasBaja);
$pdf->SetY($posicionMasBaja + 1);

// ========== INFORMACIÓN DEL CLIENTE ==========
$pdf->SetFont('helvetica', '', 10);

// Guardar posición Y inicial
$posYInicial = $pdf->GetY();

// ============================================
// FILA 1: CLIENTE Y DOCUMENTO
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(18, 5, 'CLIENTE:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Datos del cliente
$cliente_nombre = $pdf->data["cliente"]["nombres_cliente"];
$pdf->MultiCell(95, 5, $cliente_nombre, 0, 'L', 0, 0);

// Guardar posición Y después del MultiCell para ajuste
$y_despues_cliente = $pdf->GetY();

// Determinar si es DNI o RUC
$esDNI = ($pdf->data["cliente"]["tipo_doc_descripcion"] == 'DNI');
$labelDocumento = $esDNI ? 'DNI:' : 'RUC:';

// DNI/RUC del cliente
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(28, 5, $labelDocumento, 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(25, 5, $pdf->data["cliente"]["num_docu"], 0, 1, 'L');

// Ajustar posición si el MultiCell fue más alto
if ($y_despues_cliente > ($pdf->GetY() - 5)) {
    $pdf->SetY($y_despues_cliente);
}

// ============================================
// FILA 2: DIRECCIÓN Y FECHA DE EMISIÓN
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(18, 5, 'DIRECCIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

$direccion_cliente = !empty($pdf->data['cliente']['direccion_completa']) 
    ? $pdf->data['cliente']['direccion_completa'] 
    : (!empty($pdf->data['cliente']['direccion']) 
        ? $pdf->data['cliente']['direccion'] 
        : 'S/D');

$direccion_cliente = trim(preg_replace('/\s+/', ' ', $direccion_cliente));

// Guardar posición Y antes de mostrar la dirección
$y_antes_direccion = $pdf->GetY();

// Usar MultiCell para la dirección completa
$pdf->MultiCell(95, 5, $direccion_cliente, 0, 'L', 0, 0);

// Guardar posición Y después del MultiCell para ajuste
$y_despues_direccion = $pdf->GetY();

// Guardar posición Y después del MultiCell para ajuste
$y_despues_direccion = $pdf->GetY();

// FECHA DE EMISIÓN
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(28, 5, 'FECHA EMISIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$fechaEmision = date("d-m-Y H:i A", strtotime($pdf->data["data_venta"]["fecha_emision"]));
$pdf->Cell(0, 5, $fechaEmision, 0, 1, 'L');

// Ajustar posición si el MultiCell fue más alto
if ($y_despues_direccion > ($pdf->GetY() - 5)) {
    $pdf->SetY($y_despues_direccion);
}

// ====== LÍNEA SEPARADORA ======
$posYFinal = $pdf->GetY();
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $posYFinal + 2, 200, $posYFinal + 2);
$pdf->SetY($posYFinal + 7);
$pdf->Ln(-3);

// SECCIÓN DE DETRACCIÓN (si aplica)
if (isset($pdf->data["data_venta"]["afecta_detraccion"]) && $pdf->data["data_venta"]["afecta_detraccion"] == 1 && !empty($pdf->data["data_detraccion"])) {
    $detraccion = $pdf->data["data_detraccion"];
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(255, 228, 225); // Color rosa claro
    $pdf->Cell(0, 8, 'INFORMACIÓN DE DETRACCIÓN', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 9); // Cambiado a tamaño 9
    $pdf->SetFillColor(255, 255, 255);
    
    // ====== FILA 1: CUENTA, PORCENTAJE Y MONTO (EN UNA SOLA LÍNEA) ======
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'Cuenta de Detracción:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    // Cuenta de detracción
    $cuenta_detraccion = isset($detraccion['cuenta_detraccion'])
        ? $detraccion['cuenta_detraccion']
        : (isset($pdf->data["emisor"]['nro_cuenta_BN'])
            ? $pdf->data["emisor"]['nro_cuenta_BN']
            : 'POR DEFINIR');
    $pdf->Cell(45, 6, $cuenta_detraccion, 0, 0, 'L');
    
    // Porcentaje
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'Porcentaje Detracción:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    if (isset($detraccion['porcentaje_decimal'])) {
        $porcentaje_valor = floatval($detraccion['porcentaje_decimal']) * 100;
        $porcentaje = number_format($porcentaje_valor, 2) . '%';
    } elseif (isset($detraccion['porcentaje_operacion'])) {
        $porcentaje = number_format(floatval($detraccion['porcentaje_operacion']), 2) . '%';
    } else {
        $porcentaje = '4.00%';
    }
    $pdf->Cell(15, 6, $porcentaje, 0, 0, 'L');
    
    // Monto detracción
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(30, 6, 'Monto Detracción:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $monto_detraccion = isset($detraccion['monto_detraccion']) ? 'S/. ' . number_format($detraccion['monto_detraccion'], 2) : 'S/. 0.00';
    $pdf->Cell(0, 6, $monto_detraccion, 0, 1, 'L');
    
    // ====== FILA 2: MÉTODO DE PAGO ======
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'Método de Pago:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $metodo_pago = isset($detraccion['medio_pago_descripcion']) ? $detraccion['medio_pago_descripcion'] : 'NO ESPECIFICADO';
    $pdf->MultiCell(0, 6, $metodo_pago, 0, 'L', false, 1);
    
    // ====== FILA 3: TIPO DE OPERACIÓN ======
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'Tipo de Operación:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $tipo_operacion = '';
    if (isset($pdf->data["data_venta"]['codigo_tp_operacion_sunat']) && isset($pdf->data["data_venta"]['tipo_operacion_sunat'])) {
        $tipo_operacion = $pdf->data["data_venta"]['codigo_tp_operacion_sunat'] . ' ' . $pdf->data["data_venta"]['tipo_operacion_sunat'];
    } elseif (isset($detraccion['tipo_detraccion_descripcion'])) {
        $tipo_operacion = '1004 Operación Sujeta a Detracción - Servicios de Transporte Carga';
    } else {
        $tipo_operacion = '1004 Operación Sujeta a Detracción - Servicios de Transporte Carga';
    }
    $pdf->MultiCell(0, 6, $tipo_operacion, 0, 'L', false, 1);
    
    // ====== FILA 4: B/S SUJETO DETRACCIÓN ======
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'B/S Sujeto Detracción:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $bien_servicio = isset($detraccion['tipo_detraccion_descripcion'])
        ? $detraccion['tipo_detraccion_codigo'] . ' ' . $detraccion['tipo_detraccion_descripcion']
        : '027 Servicio de transporte de bienes por vía terrestre';
    $pdf->MultiCell(0, 6, $bien_servicio, 0, 'L', false, 1);
    
    $pdf->Ln(2);
}

// DETALLE DE PRODUCTOS/SERVICIOS - VERSIÓN CON MEDICIÓN DE ALTURA
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(220, 220, 220);

// Encabezado de la tabla (suma total: 180)
$pdf->Cell(15, 6, 'ITEM', 1, 0, 'C', true);       // 15
$pdf->Cell(15, 6, 'CANT', 1, 0, 'C', true);       // 15
$pdf->Cell(15, 6, 'UNID', 1, 0, 'C', true);       // 15
$pdf->Cell(90, 6, 'DESCRIPCIÓN', 1, 0, 'C', true); // 90
$pdf->Cell(30, 6, 'P.UNIT', 1, 0, 'C', true);     // 30
$pdf->Cell(25, 6, 'TOTAL', 1, 1, 'C', true);      // 25
// TOTAL: 15+15+15+90+30+25 = 190

// Contenido de la tabla
$pdf->SetFont('helvetica', '', 8);

$item = 1;
$suma_total = 0;

foreach ($pdf->data["productos"] as $producto) {
    $cantidad = $producto['cantidad'];
    $unidad = $producto['unidad_medida'];
    $nombre = $producto['nombre'];
    $descripcion = isset($producto['descripcion']) ? $producto['descripcion'] : '';
    $precio_unitario = $producto['precio_unitario'];
    $total_producto = $producto['importe_total'];
    
    // Descripción completa
    $descripcion_completa = $nombre;
    if (!empty($descripcion)) {
        $descripcion_completa .= "\n" . $descripcion;
    }
    
    // Medir altura necesaria para la descripción (ancho ajustado a 90)
    $altura_descripcion = $pdf->getStringHeight(90, $descripcion_completa, true, true, 5, 2);
    $altura_fila = max($altura_descripcion, 5); // Altura mínima de 5
    
    // Guardar posición inicial
    $x_inicial = $pdf->GetX();
    $y_inicial = $pdf->GetY();
    
    // ITEM (15)
    $pdf->MultiCell(15, $altura_fila, $item, 1, 'C', false, 0);
    
    // CANT (15)
    $pdf->MultiCell(15, $altura_fila, $cantidad, 1, 'C', false, 0);
    
    // UNID (15)
    $pdf->MultiCell(15, $altura_fila, $unidad, 1, 'C', false, 0);
    
    // DESCRIPCIÓN (90)
    $pdf->MultiCell(90, $altura_fila, $descripcion_completa, 1, 'L', false, 0);
    
    // P.UNIT (30)
    $pdf->MultiCell(30, $altura_fila, number_format($precio_unitario, 2), 1, 'R', false, 0);
    
    // TOTAL (25)
    $pdf->MultiCell(25, $altura_fila, number_format($total_producto, 2), 1, 'R', false, 1);
    
    $suma_total += $total_producto;
    $item++;
}

$pdf->Ln(2);

// RESUMEN DE TOTALES
$pdf->SetFont('helvetica', '', 8);
// Quitado el título 'RESUMEN DE TOTALES'

// Definir anchos para la tabla (total 190)
$anchoEtiqueta = 135;    // Para las etiquetas
$anchoMoneda = 30;      // Para "S/. "
$anchoValor = 25;       // Para el valor numérico
$anchoTotal = $anchoEtiqueta + $anchoMoneda + $anchoValor; // 115

// Posicionar a la derecha para que la tabla mida 190
$margenIzquierdo = 200 - $anchoTotal; 

// Datos de totales con etiquetas cortas
$items_totales = [
    'Gravado:' => $pdf->data["data_venta"]["op_gravada"],
    'IGV:' => $pdf->data["data_venta"]["op_igv"],
    'ICBPER:' => $pdf->data["data_venta"]["icbper"],
    'Exonerado:' => $pdf->data["data_venta"]["op_exonerada"],
    'Inafecto:' => $pdf->data["data_venta"]["op_inafecta"],
    'IMPORTE:' => $pdf->data["data_venta"]["total"],
];

foreach ($items_totales as $label => $value) {
    $value = is_numeric($value) ? $value : 0;
    
    // Mostrar todos los items (eliminada la condición de $value > 0)
    // Negrita solo en IMPORTE
    if ($label === 'IMPORTE:') {
        $pdf->SetFont('helvetica', 'B', 8);
    } else {
        $pdf->SetFont('helvetica', '', 8);
    }
    
    // Posicionar a la derecha
    $pdf->SetX($margenIzquierdo);
    
    // Mostrar la etiqueta
    $pdf->Cell($anchoEtiqueta, 5, $label, 0, 0, 'R');
    
    // Mostrar moneda
    $pdf->Cell($anchoMoneda, 5, 'S/.', 0, 0, 'R');
    
    // Mostrar valor (alineado a la derecha)
    $pdf->Cell($anchoValor, 5, number_format($value, 2), 0, 1, 'R');
}

$pdf->Ln(2);

// TOTAL EN LETRAS
$pdf->SetFont('helvetica', 'B', 8); // Reducido a tamaño 8
$letras = 'SON: ' . numtoletras(number_format($pdf->data["data_venta"]["total"], 2, '.', ''));
$pdf->MultiCell(0, 4, $letras, 0, 'L'); // Altura reducida a 4

$pdf->Ln(4);

// ========== SECCIÓN FINAL (QR + INFORMACIÓN ADICIONAL) ==========

// Calcular posición para la sección
$seccionY = $pdf->GetY();

// Verificar si hay QR para mostrar
$mostrarQR = !empty($pdf->data["data_venta"]["cod_qr"]);
$qrSize = 40; // Tamaño del QR
$espacioEntreColumnas = 10; // Espacio entre QR y texto

// ====== COLUMNA IZQUIERDA: CÓDIGO QR ======
if ($mostrarQR) {
    $qrPath = $pdf->data["data_venta"]["cod_qr"];
    $xQR = 10; // Margen izquierdo
    $yQR = $seccionY;
    
    // Verificar y mostrar QR
    $qrValido = false;
    if (strpos($qrPath, 'private/empresa/') !== false && file_exists($qrPath)) {
        $pdf->Image($qrPath, $xQR, $yQR, $qrSize, $qrSize, '', '', '', false, 300, '', false, false, 0);
        $qrValido = true;
    } elseif (strpos($qrPath, 'https://') !== false) {
        $urlQR = str_replace(' ', '%20', $qrPath);
        $pdf->Image($urlQR, $xQR, $yQR, $qrSize, $qrSize, '', '', '', false, 300, '', false, false, 0);
        $qrValido = true;
    }
    
    if ($qrValido) {
        // Título pequeño del QR (opcional)
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetXY($xQR, $yQR - 4);
        $pdf->Cell($qrSize, 4, 'CÓDIGO QR', 0, 0, 'C');
    }
}

// ====== COLUMNA DERECHA: INFORMACIÓN ADICIONAL ======
$columnaDerechaX = $mostrarQR ? (10 + $qrSize + $espacioEntreColumnas) : 10;
$pdf->SetY($seccionY);
$pdf->SetX($columnaDerechaX);

// Configurar anchos para mejor alineación
$anchoEtiqueta = 30;
$anchoContenido = 130;
$lineHeight = 4;

// OBSERVACIONES (si existen)
if (!empty($pdf->data["data_venta"]["obs"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell($anchoEtiqueta, $lineHeight, 'OBSERVACIONES:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    
    // Guardar posición Y actual para ajustar
    $yAntesObs = $pdf->GetY();
    $xContenido = $pdf->GetX();
    
    // Mostrar observaciones con MultiCell
    $pdf->MultiCell($anchoContenido, $lineHeight, $pdf->data["data_venta"]["obs"], 0, 'L', false, 1);
    
    // Ajustar posición si el MultiCell fue más alto
    $yDespuesObs = $pdf->GetY();
    if ($yDespuesObs > ($yAntesObs + $lineHeight)) {
        // Si el QR existe, necesitamos ajustar su posición también
        if ($mostrarQR) {
            // Calcular diferencia de altura
            $diffAltura = $yDespuesObs - ($yAntesObs + $lineHeight);
            
            // Redibujar QR más abajo si es necesario
            // (Esto es complejo en TCPDF, mejor mantener el diseño simple)
            // Por ahora, solo ajustamos el texto
        }
    }
} else {
    $pdf->Ln($lineHeight);
}

// FORMA DE PAGO
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetX($columnaDerechaX);
$pdf->Cell($anchoEtiqueta, $lineHeight, 'FORMA DE PAGO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell($anchoContenido, $lineHeight, strtoupper($pdf->data["data_venta"]["forma_pago"]), 0, 'L', false, 1);

// MÉTODO DE PAGO
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetX($columnaDerechaX);
$pdf->Cell($anchoEtiqueta, $lineHeight, 'MÉTODO DE PAGO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell($anchoContenido, $lineHeight, strtoupper($pdf->data["data_venta"]["medio_pago"]), 0, 'L', false, 1);

// VENDEDOR
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetX($columnaDerechaX);
$pdf->Cell($anchoEtiqueta, $lineHeight, 'VENDEDOR:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$vendedor = $pdf->data["data_venta"]["vendedor_nombres"] . ' ' . $pdf->data["data_venta"]["vendedor_apellidos"];
$pdf->MultiCell($anchoContenido, $lineHeight, $vendedor, 0, 'L', false, 1);

// HASH CDR (si existe)
if (!empty($pdf->data["data_venta"]["hash_cdr"])) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetX($columnaDerechaX);
    $pdf->Cell($anchoEtiqueta, $lineHeight, 'HASH CDR:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    
    // Para hash largo, podemos usar fuente más pequeña o permitir wrap
    $hash = $pdf->data["data_venta"]["hash_cdr"];
    $pdf->SetFont('helvetica', '', 7); // Fuente más pequeña para hash
    $pdf->MultiCell($anchoContenido, $lineHeight, $hash, 0, 'L', false, 1);
    $pdf->SetFont('helvetica', '', 8); // Volver al tamaño normal
}

// FECHA DE EMISIÓN
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetX($columnaDerechaX);
$pdf->Cell($anchoEtiqueta, $lineHeight, 'FECHA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$fechaEmision = date("d-m-Y H:i A", strtotime($pdf->data["data_venta"]["fecha_emision"]));
$pdf->MultiCell($anchoContenido, $lineHeight, $fechaEmision, 0, 'L', false, 1);

// CUENTA BANCARIA 
$cuenta_raw = isset($pdf->data['emisor']['nro_cuenta_bancaria']) 
    ? $pdf->data['emisor']['nro_cuenta_bancaria'] 
    : '';

// Verificar si hay contenido HTML REAL (no solo etiquetas vacías o espacios)
$contenido_sin_html = trim(strip_tags($cuenta_raw));
$hay_contenido_real = !empty($contenido_sin_html) && $cuenta_raw !== 'S/C' && $cuenta_raw !== 's/c';

if ($hay_contenido_real) {
    // Decodificar entidades HTML
    $cuentas_html = html_entity_decode($cuenta_raw, ENT_QUOTES, 'UTF-8');
    
    // Verificar si tiene formato HTML
    $tiene_html = ($cuentas_html != strip_tags($cuentas_html));
    
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetX($columnaDerechaX);
    $pdf->Cell($anchoEtiqueta, $lineHeight, 'CUENTA BANCARIA:', 0, 0, 'L');
    
    if ($tiene_html) {
        // Modo HTML completo - permite negritas, colores, etc.
        $current_x = $pdf->GetX();
        $current_y = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 8);
        // Usar writeHTMLCell para renderizar HTML en el ancho disponible
        $pdf->writeHTMLCell(0, 0, $current_x, $current_y, $cuentas_html, 0, 1, 0, true, 'L', true);
    } else {
        // Texto plano con soporte multi-línea
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(0, $lineHeight, $cuentas_html, 0, 'L', false, 1);
    }
}

// ====== AJUSTAR POSICIÓN FINAL ======
// Si hay QR, asegurarnos que la columna derecha no sea más baja que el QR
if ($mostrarQR) {
    $alturaColumnaDerecha = $pdf->GetY() - $seccionY;
    $alturaQR = $qrSize;
    
    // Si el texto es más alto que el QR, mover el texto siguiente más abajo
    if ($alturaColumnaDerecha > $alturaQR) {
        // Ya está ajustado automáticamente por MultiCell
    } else {
        // Si el QR es más alto, ajustamos la posición Y para continuar
        $nuevaY = $seccionY + $alturaQR + 5;
        if ($pdf->GetY() < $nuevaY) {
            $pdf->SetY($nuevaY);
        }
    }
}

// MENSAJE FINAL
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(0, 5, 'Representación impresa de ' . $pdf->data['data_venta']['tp_comprobante'], 0, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 5, 'Usted puede consultar su CPE desde su Clave SOL o desde nuestra Página Web.', 0, 'L');

// =========================================================
// 3. TÉRMINOS Y CONDICIONES DEL FACTURADOR
// =========================================================
if (!empty($pdf->data['emisor']['termscond_facturador'])) {
    $pdf->SetFont('helvetica', 'B', 9);  // Negrita para el título
    $pdf->Cell(0, 6, 'TÉRMINOS Y CONDICIONES', 0, 1, 'C');
    
    // Dibujar una línea decorativa (opcional)
    $pdf->SetLineWidth(0.2);
    $pdf->SetDrawColor(150, 150, 150);
    $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
    $pdf->Ln(2);
    
    // Decodificar entidades HTML
    $terminos_html = html_entity_decode($pdf->data['emisor']['termscond_facturador'], ENT_QUOTES, 'UTF-8');
    
    // Usar writeHTMLCell para renderizar HTML
    $pdf->SetFont('helvetica', '', 7);  // Fuente más pequeña para términos y condiciones
    $pdf->writeHTMLCell(190, 5, 10, $pdf->GetY(), $terminos_html, 0, 1, 0, true, 'J', true);
    
    $pdf->Ln(3);
}

// Generar PDF
$nombre_archivo = $pdf->data["data_venta"]["tp_comprobante"] . '_' . $correlativo . '.pdf';
ob_end_clean();
$pdf->Output($nombre_archivo, 'I');