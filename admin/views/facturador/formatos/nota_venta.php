<?php
ob_start();
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

// =========================================================
// 1. TELÉFONO DE LA EMPRESA + CELULAR DE LA TERMINAL
// =========================================================
if (!empty($this->data['emisor']['telefono_empresa'])) {
    $pdf->SetFont('helvetica', '', 8);

    $telefono_completo = "Teléfono: " . $this->data['emisor']['telefono_empresa'];

    // Buscar el celular de la terminal específica que hizo el comprobante
    $id_terminal_venta = $this->data["data_venta"]["id_terminal"] ?? 0;
    $celular_encontrado = '';

    if (!empty($this->data["TERMINALES"]) && $id_terminal_venta > 0) {
        foreach ($this->data["TERMINALES"] as $terminal) {
            if (isset($terminal['id_terminal']) && $terminal['id_terminal'] == $id_terminal_venta) {
                if (!empty($terminal['celular'])) {
                    $celular_encontrado = ' - ' . $terminal['celular'];
                }
                break;
            }
        }
    }

    // Si no encuentra el celular de la terminal específica, intenta con el primer terminal
    if (empty($celular_encontrado) && !empty($this->data["TERMINALES"])) {
        $primer_terminal = reset($this->data["TERMINALES"]);
        if (!empty($primer_terminal['celular'])) {
            $celular_encontrado = ' - ' . $primer_terminal['celular'];
        }
    }

    $telefono_completo .= $celular_encontrado;
    $pdf->Cell($W_UTIL, 4, $telefono_completo, 0, 0.1, 'C');
}

// Frase empresa
if (!empty($this->data["emisor"]['frase_empresa'])) {
    // Solo agregar el contenido si la frase no está vacía
    $pdf->SetFont('helvetica', '', 6);
    $frase_empresa = html_entity_decode($this->data["emisor"]['frase_empresa'], ENT_QUOTES, 'UTF-8');
    $pdf->writeHTMLCell(72, 3.5, '', '', $frase_empresa, 0, 1, 0, true, 'C', true);

    // Ajuste del salto de línea después de la frase
    $pdf->Ln(0); // Ajustar este valor para que haya espacio después de la frase
} else {
    // No agregamos ningún salto de línea adicional si no hay frase
    $pdf->Ln(-1);
}

$pdf->Ln(1);
// Dirección fiscal (esto siempre se imprime)
$pdf->SetFont('helvetica', '', 6.5);
$pdf->MultiCell($W_UTIL, 4, 'DOM. FISCAL: ' . $this->data["emisor"]['direccion_fiscal'], 0, 'C', 0, 1);

// Línea separadora (fina)
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'B');
$pdf->Ln(5);

// TIPO DE COMPROBANTE
$pdf->SetFont('helvetica', 'B', TEXT_SIZE_COMPROBANTE_SERIE);
$pdf->MultiCell($W_UTIL, 4, strtoupper($this->data["data_venta"]["tp_comprobante"]), 0, 'C', 0, 1);
$pdf->Ln(2);

// SERIE Y CORRELATIVO
$correlativo = $this->data["data_venta"]["serie"] . "-" . str_pad($this->data["data_venta"]["correlativo"], 8, '0', STR_PAD_LEFT);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell($W_UTIL, 4, $correlativo, 0, 'C', 0, 1);
$pdf->Ln(2);

// Línea separadora superior
$pdf->SetLineWidth(0.1);
$pdf->Cell($W_UTIL, 0, '', 'T');
$pdf->Ln(2);

// DATOS CLIENTE - DESTINATARIO
$pdf->SetFont('helvetica', '', 8);

// FECHA - Negrita en "FECHA:"
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(25, 5, 'FECHA EMISION:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 5, date("d-m-Y H:i A", strtotime($this->data["data_venta"]["fecha_emision"])), 0, 1, 'L');

// DNI REMITENTE - Negrita en tipo de documento
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(18, 5, $this->data["cliente"]["tipo_doc_descripcion"] . ': ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(51, 5, $this->data["cliente"]["num_docu"], 0, 1, 'L');

// CLIENTE
$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(18, 5, 'CLIENTE:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$cliente_nombres = $this->data["cliente"]["nombres_cliente"];
$pdf->MultiCell(0, 5, $cliente_nombres, 0, 'L', 0, 1);

// DIRECCIÓN - USANDO DIRECCIÓN COMPLETA CON UBIGEO
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(18, 5, 'DIRECCIÓN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);

// Usar la dirección completa con ubigeo si existe, sino usar la dirección simple
$direccion_cliente = !empty($this->data['cliente']['direccion_completa'])
    ? $this->data['cliente']['direccion_completa']
    : (!empty($this->data['cliente']['direccion'])
        ? $this->data['cliente']['direccion']
        : 'S/D');

// Limpiar la dirección (eliminar múltiples espacios)
$direccion_cliente = trim(preg_replace('/\s+/', ' ', $direccion_cliente));
$pdf->MultiCell(0, 5, $direccion_cliente, 0, 'L', 0, 1);
$pdf->Ln(1);

// TABLA DE DETALLE AJUSTADA AL TICKET (ancho total: 72 mm)
$pdf->SetFont('helvetica', 'B', 7);
$pdf->Cell(8, 5, 'CANT', 1, 0, 'C');
$pdf->Cell(8, 5, 'UNID', 1, 0, 'C');
$pdf->Cell(28, 5, 'DESCRIPCION', 1, 0, 'C');
$pdf->Cell(11, 5, 'P.UNIT', 1, 0, 'C');
$pdf->Cell(11, 5, 'TOTAL', 1, 1, 'C');

$suma_total = 0;

foreach ($this->data["productos"] as $producto) {
    $cantidad_producto = $producto['cantidad'];
    $unidad_medida = $producto['unidad_medida'];
    $nombre = $producto['nombre'];
    $descripcion = isset($producto['descripcion']) ? $producto['descripcion'] : '';
    $precio_unitario = $producto['precio_unitario'];
    $total_producto = $producto['importe_total'];

    // COMBINAR NOMBRE Y DESCRIPCIÓN (similar al módulo de encomienda)
    $descripcion_completa = $nombre;

    if (!empty($descripcion)) {
        // Si hay descripción, agregarla en una línea más pequeña
        $descripcion_completa .= '<br/><span style="font-size:5pt;">' . $descripcion . '</span>';
    }

    $pdf->SetFont('helvetica', '', 7);

    // Clonar el PDF temporal para medir altura real
    $temp_pdf = clone $pdf;

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // MEDIR CON EL HTML COMPLETO (igual que en encomienda)
    $temp_pdf->writeHTMLCell(25, 0, $x, $y, $descripcion_completa, 0, 1, false, true, 'L', true);
    $altura_real = $temp_pdf->GetY() - $y;

    // Garantizar altura mínima si es muy pequeño
    $altura_fila = max($altura_real, 5);

    // Imprimir celdas normales
    $pdf->Cell(8, $altura_fila, $cantidad_producto, 1, 0, 'C');
    $pdf->Cell(8, $altura_fila, $unidad_medida, 1, 0, 'C');

    $x_desc = $pdf->GetX();
    $y_desc = $pdf->GetY();

    // ESCRIBIR EL HTML COMPLETO (igual que en encomienda)
    $pdf->writeHTMLCell(28, $altura_fila, $x_desc, $y_desc, $descripcion_completa, 1, 0, false, true, 'L', true);

    $pdf->SetXY($x_desc + 28, $y_desc);
    $pdf->Cell(11, $altura_fila, number_format($precio_unitario, 2), 1, 0, 'R');
    $pdf->Cell(11, $altura_fila, number_format($total_producto, 2), 1, 1, 'R');

    $suma_total += $total_producto;
}

// Ajustar la posición del cursor después de la tabla de detalles
$pdf->Ln(2); // Agregar un espacio adicional si es necesario

$pdf->SetFont('helvetica', '', 8);

// Datos de totales con etiquetas cortas
$items_totales = [
    'Gravado:' => $this->data["data_venta"]["op_gravada"],
    'IGV:' => $this->data["data_venta"]["op_igv"],
    'Exonerado:' => $this->data["data_venta"]["op_exonerada"],
    'Inafecto:' => $this->data["data_venta"]["op_inafecta"],
    'IMPORTE:' => $suma_total,
];

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
    $pdf->Cell(23, 4, number_format($value, 2), 0, 1, 'R');  // Importe, alineado a la derecha
}

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 8);
$letras = 'SON: ';
$pdf->SetFont('helvetica', '', 8);
$letras .= numtoletras(number_format($suma_total, 2, '.', ''));
$pdf->MultiCell(72, 5, $letras, 0, 'L');
$pdf->Ln(1);

// Observacion
if (!empty($this->data["data_venta"]["obs"])) {
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(18, 5, 'Observaciones:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->MultiCell(50, 5, $this->data["data_venta"]["obs"], 0, 'J', 0, 1);
}

// Forma de pago
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(21, 5, 'Forma de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->Cell(23, 5, strtoupper($this->data["data_venta"]["forma_pago"]), 0, 1, 'L');

// Método de pago
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(21, 5, 'Método de pago:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(23, 5, strtoupper($this->data["data_venta"]["medio_pago"]), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->MultiCell(23, 5, 'VENDEDOR:', 0, 'L', 0, 0);
$pdf->SetFont('helvetica', '', 8);
$vendedor_nombres = $this->data["data_venta"]["vendedor_nombres"] . ' ' . $this->data["data_venta"]["vendedor_apellidos"];
$pdf->MultiCell(0, 5, $vendedor_nombres, 0, 'L', 0, 1);
$pdf->Ln(-1);

// CUENTA BANCARIA
$cuenta_bancaria_raw = !empty($this->data['emisor']['nro_cuenta_bancaria']) 
    ? $this->data['emisor']['nro_cuenta_bancaria'] 
    : '';

// Verificar si hay contenido REAL (ignorando etiquetas vacías y valores por defecto)
$contenido_sin_html = trim(strip_tags($cuenta_bancaria_raw));
$hay_contenido_real = !empty($contenido_sin_html);

if ($hay_contenido_real) {
    // Decodificar entidades HTML
    $cuentas_html = html_entity_decode($cuenta_bancaria_raw, ENT_QUOTES, 'UTF-8');
    
    // Verificar si tiene formato HTML
    $tiene_html = ($cuentas_html != strip_tags($cuentas_html));
    $current_x = $pdf->GetX();
    $current_y = $pdf->GetY();
    
    if ($tiene_html) {
        $pdf->SetFont('helvetica', '', 7);
        // Ancho disponible: 
        $pdf->writeHTMLCell(0, 0, $current_x, $current_y, $cuentas_html, 0, 1, 0, true, 'L', true);
    } else {
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->MultiCell(0, 4, $cuentas_html, 0, 'L', 0, 1);
    }
}

// NOTA DE VENTA(Negrita)
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->MultiCell($W_UTIL, 5, 'Representación impresa de ' . $this->data['data_venta']['tp_comprobante'], 0, 'C', false);
// Mensaje adicional (alineado a la izquierda)
$pdf->SetFont('helvetica', '', 7.5);
$pdf->MultiCell($W_UTIL, 5, 'Usted puede consultar su CPE desde su Clave SOL o desde nuestra Página Web.', 0, 'L', false);
// Espaciado
$pdf->Ln(2);

// =========================================================
// 3. TÉRMINOS Y CONDICIONES DEL FACTURADOR
// =========================================================
if (!empty($this->data['emisor']['termscond_facturador'])) {

    // Decodificar entidades HTML
    $terminos_html = html_entity_decode($this->data['emisor']['termscond_facturador'], ENT_QUOTES, 'UTF-8');

    // Usar writeHTMLCell para renderizar HTML
    $pdf->SetFont('helvetica', '', 7.5);  // Fuente más pequeña para términos y condiciones
    $pdf->writeHTMLCell($W_UTIL, 5, 1.5, $pdf->GetY(), $terminos_html, 0, 1, 0, true, 'J', true);

    $pdf->Ln(2);
}

$pdf->SetAutoPageBreak(true, 5); // Ya lo tienes en tu clase
ob_end_clean();
$pdf->Output('ticket.pdf', 'I');
