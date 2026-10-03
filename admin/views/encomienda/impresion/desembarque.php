<?php
ini_set('memory_limit', '1024M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// Incluye la clase para convertir números a letras
require_once('public/plugins/print/num_letras.php');
// Incluye tu clase personalizada para CellFit adaptada a TCPDF
require_once('public/plugins/tc/tcpdf.php');

define("WIDTH_LOGO", 25);
define("HEIGHT_LOGO", 20);
define("TEXT_SIZE_TITULO", 15);
define("TEXT_SIZE_BODY", 8.5);
define("TEXT_WIDTH_SIZE_BODY", 40);

class TCPDF_CellFit extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 0.5;

    public function __construct()
    {
        parent::__construct('L', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetMargins(5, 10, 5);
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

    function Row($data, $lineHeight = 5, $align = 'L', $boldColumns = [], $drawBorders = true, $forceSingleLine = false)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i] ?? 0, $data[$i] ?? ''));
        }
        if ($forceSingleLine) {
            $nb = 1;
        }
        $h = $lineHeight * $nb;
        error_log("Row: Altura calculada h=$h, nb=$nb, lineHeight=$lineHeight, forceSingleLine=$forceSingleLine, data=" . json_encode($data));

        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
        }

        $x = $this->GetX();
        $y = $this->GetY();

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i] ?? 10;
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : $align;

            if (in_array($i, $boldColumns)) {
                $this->SetFont('', 'B');
            }

            $xBefore = $this->GetX();
            $yBefore = $this->GetY();

            $this->MultiCell(
                $w,
                $h,
                $data[$i] ?? '',
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

    function NbLines($w, $txt)
    {
        if (!is_numeric($w)) {
            error_log("NbLines: Valor de w no numérico: " . var_export($w, true));
            return 1;
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

            $l += $this->GetStringWidth($c);
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

// Nueva función para calcular precio unitario (CON IGV) y total correctamente (por PRODUCTO)
function calcularPrecioUnitarioYTotal($encomienda)
{
    $op_gravada = floatval($encomienda["encomienda_precio_op_gravada"] ?? 0.00);
    $op_exonerada = floatval($encomienda["encomienda_precio_op_exonerada"] ?? 0.00);
    $op_inafecta = floatval($encomienda["encomienda_precio_op_inafecta"] ?? 0.00);
    $cantidad = floatval($encomienda["encomienda_cantidad"] ?? 1);
    $igv_rate = 1.18;

    // Determinar el subtotal base sin IGV para el PRODUCTO (prioridad: gravada > exonerada > inafecta)
    $subtotal_sin_igv = 0.00;
    $es_gravada = false;
    if ($op_gravada > 0) {
        $subtotal_sin_igv = $op_gravada;
        $es_gravada = true;
    } elseif ($op_exonerada > 0) {
        $subtotal_sin_igv = $op_exonerada;
    } elseif ($op_inafecta > 0) {
        $subtotal_sin_igv = $op_inafecta;
    }

    // Precio unitario sin IGV (para cálculos internos)
    $precio_unitario_sin_igv = ($cantidad > 0) ? round($subtotal_sin_igv / $cantidad, 2) : $subtotal_sin_igv;

    // Precio unitario CON IGV (para mostrar en PDF)
    $precio_unitario_con_igv = $es_gravada ? round($precio_unitario_sin_igv * $igv_rate, 2) : $precio_unitario_sin_igv;

    // Total por PRODUCTO: subtotal_sin_igv * (1.18 si gravada, sino 1), redondeado
    $total = $es_gravada ? round($subtotal_sin_igv * $igv_rate, 2) : $subtotal_sin_igv;

    // Logging para depuración
    error_log("Encomienda ID: " . ($encomienda['id_encomienda'] ?? 'N/A') .
        ", Subtotal sin IGV: $subtotal_sin_igv, Cantidad: $cantidad, " .
        "Precio Unitario sin IGV: $precio_unitario_sin_igv, Precio Unitario con IGV: $precio_unitario_con_igv, " .
        "Total (producto): $total, Es gravada: " . ($es_gravada ? 'Sí' : 'No'));

    return [
        'precio_unitario' => $precio_unitario_con_igv, // E.g., 20.00 o 10.00 (CON IGV)
        'total' => $total // E.g., 20.00 o 10.00 (con IGV si aplica)
    ];
}

// Simulación de data sucia
$data = $this->data;

// Verifica que la sección "BODY" exista en los datos
if (!isset($data["BODY"]) || !is_array($data["BODY"])) {
    error_log("Error: Los datos no contienen un cuerpo válido.");
    throw new Exception("Los datos no contienen un cuerpo válido.");
}

// Agrupar datos por terminal de destino
$encomiendas_por_terminal = [];
foreach ($data["BODY"] as $encomienda) {
    if (!isset($encomienda["terminal_destino"])) {
        error_log("Encomienda sin terminal_destino: " . json_encode($encomienda));
        continue;
    }

    $terminal_destino = $encomienda["terminal_destino"];
    if (!isset($encomiendas_por_terminal[$terminal_destino])) {
        $encomiendas_por_terminal[$terminal_destino] = [];
    }
    $encomiendas_por_terminal[$terminal_destino][] = $encomienda;
}

// Código para generar el PDF
$pdf = new TCPDF_CellFit('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetMargins(5, 10, 5);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();
$mostrar_nombre_terminal = false;

// Función para imprimir el encabezado
function imprimirEncabezado($pdf, $data, $terminal_actual)
{
    // Logo
    $logoPath = Session::get("data_empresa")["logo"];
    if (!empty($logoPath) && file_exists($logoPath)) {
        $bgWidth = 72.1 - 3;
        $bgX = 10;
        $bgY = 5;

        set_error_handler(function ($errno, $errstr) {
            if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) {
                return true;
            }
            return false;
        });

        list($imgWidth, $imgHeight) = getimagesize($logoPath);
        $scale = min(35 / $imgWidth, 25 / $imgHeight);
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        $xLogo = $bgX + 2;
        $yLogo = $bgY + (25 - $finalHeight) / 2;

        $pdf->Image(
            $logoPath,
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
        $pdf->Ln(25);
    } else {
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Logo no disponible', 0, 1, 'L');
    }

    // Dirección de la empresa
    $pdf->SetFont('helvetica', '', 5.5);
    $pdf->SetY(5);
    $pdf->SetLeftMargin(10);

    // Obtener configuración de la empresa
    $empresaModel = new EmpresaModel();
    $configEmpresa = $empresaModel->get_data();
    $mostrarTodasTerminales = $configEmpresa['success'] ? $configEmpresa['message']['m_terminalesManifi'] == 1 : false;

    // Determinar qué terminales mostrar
    $terminalesAMostrar = [];
    if ($mostrarTodasTerminales) {
        // Mostrar todas las terminales
        $terminalesAMostrar = $data["TERMINALES"];
    } else {
        // Mostrar solo terminal de origen
        $terminalOrigen = $data["HEADER"]["terminal_origen"] ?? '';
        foreach ($data["TERMINALES"] as $terminal) {
            if (trim($terminal['nombre']) === trim($terminalOrigen)) {
                $terminalesAMostrar = [$terminal];
                break;
            }
        }
        // Si no se encuentra la terminal de origen, mostrar la primera
        if (empty($terminalesAMostrar) && !empty($data["TERMINALES"])) {
            $terminalesAMostrar = [reset($data["TERMINALES"])];
        }
    }

    foreach ($terminalesAMostrar as $terminal) {
        $pdf->Cell(40);
        $pdf->SetWidths([160]);

        $nombre = trim($terminal['nombre']);
        $direccion_fiscal = trim($terminal['direccion_fiscal']);
        $ubigeo = trim($terminal['ubigeo']);
        $celular = trim($terminal['celular']);

        $pdf->Row([
            strtoupper($nombre) . ': ' .
            $direccion_fiscal .
            ' - ' . $ubigeo .
            ' - CELULAR: ' . $celular
        ], 3.5, 'L', [], false);
    }

    // RUC y título
    $pdf->Ln(-23);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(228);
    $pdf->SetWidths([50]);

    $ruc = Session::get("data_empresa")["num_docu"];
    $pdf->Row(["RUC: " . $ruc], 4, 'C');

    $pdf->Ln(1);
    $pdf->Cell(228);
    $pdf->SetWidths([50]);
    $titulo = "MANIFIESTO DE DESEMBARQUE DE ENCOMIENDA";
    $pdf->Row([$titulo], 8, 'C');
    $pdf->Ln(1);
    $pdf->Cell(228);
    $pdf->SetWidths([50]);
    $nro_linea = "NRO: ____________________";
    $pdf->Row([$nro_linea], 5, 'L');

    // Información del conductor + hora de salida
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetWidths([25, 70, 25, 60, 20, 50]);

    $conductor = ($data["HEADER"]["conductor_nombres"] ?? '') . " " . ($data["HEADER"]["conductor_apellidos"] ?? '');
    $licencia = $data["HEADER"]["conductor_licencia"] ?? '---';

    $pdf->Row([
        "CONDUCTOR:",
        $conductor,
        "LICENCIA:",
        $licencia,
        "PLACA:",
        $data["HEADER"]["vehiculo_placa"] ?? '---',
    ], 5, 'L', [], false);

    // Segunda fila: Placa + Origen + Destino
    $pdf->SetWidths([25, 70, 25, 60, 20, 50]);
    $origen = $data["HEADER"]["terminal_origen"] ?? '---';
    $destino = strtoupper($data["HEADER"]["terminal_destino"] ?? '---');
    $fechaSalida = $data["HEADER"]["fecha_salida"] ?? '';
    if ($fechaSalida && $fechaSalida !== '0000-00-00') {
        $fechaSalida = DateTime::createFromFormat('Y-m-d', $fechaSalida);
        $fechaSalida = $fechaSalida ? $fechaSalida->format('d-m-Y H:i A') : 'Fecha inválida';
    } else {
        $fechaSalida = 'Fecha no disponible';
    }

    $pdf->Row([
        "ORIGEN:",
        $origen,
        "DESTINO:",
        $destino,
        "SALIDA:",
        $fechaSalida,
    ], 5, 'L', [], false);

    $pdf->Ln(2);

    // ENCABEZADO DE LA TABLA (14 columnas, ajustado para menor altura)
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetWidths([15, 25, 25, 17, 20, 20, 10, 25, 14, 20, 19, 15, 15, 23, 16]);
    $pdf->Row([
        'FECHA',
        'REMITENTE',
        'DESTINATARIO',
        'N. COMPRO.',
        'T. ORIGEN',
        'T. DESTINO',
        'CANT',
        'DESCRIPCION',
        'P. UNIT.',
        'FORMA PAGO',
        'MEDIO PAGO',
        'ESTADO',      
        'TOTAL',
        'OBSERVACION',
        'FIRMA',
    ], 4, 'C', [], true, true);
    $pdf->Ln(0.1);
}

foreach ($encomiendas_por_terminal as $terminal => $encomiendas) {
    imprimirEncabezado($pdf, $data, $terminal);

    // Imprimir datos de encomiendas
    foreach ($encomiendas as $encomienda) {
        error_log("Encomienda procesada: " . json_encode($encomienda));

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetWidths([15, 25, 25, 17, 20, 20, 10, 25, 14, 20, 19, 15, 15, 23, 16]);
        // Formatear la fecha de salida
        $fechaSalida = $encomienda["fecha_salida"] ?? '';
        if ($fechaSalida && $fechaSalida !== '0000-00-00') {
            $fechaSalida = DateTime::createFromFormat('Y-m-d', $fechaSalida);
            $fechaSalida = $fechaSalida ? $fechaSalida->format('d-m-Y') : '---';
        } else {
            $fechaSalida = '---';
        }

        // Calcular precio unitario y total corregidos
        $totals = calcularPrecioUnitarioYTotal($encomienda);
        $precio_unitario = $totals['precio_unitario']; // CON IGV (e.g., 20.00, 10.00)
        $total_producto = $totals['total']; // Con IGV, corregido (e.g., 20.00, 10.00)

        // Preparar textos sin truncar
        $remitente = trim($encomienda["remitente_nombres"] ?? '') . " " . trim($encomienda["remitente_apellidos"] ?? '');
        $destinatario = trim($encomienda["destinatario_nombres"] ?? '') . " " . trim($encomienda["destinatario_apellidos"] ?? '');
        $descripcion = trim($encomienda["detalle"] ?? '') . (!empty($encomienda["obs"]) ? ' (' . trim($encomienda["obs"]) . ')' : '');

        $estadoPago = $encomienda["estado_pago"] ?? 'NO ESPECIFICADO';
        $estadoPagoMap = [
            'PAGADO' => 'PAGADO',
            'PENDIENTE' => 'PENDIENTE',
            'NO ESPECIFICADO' => '---',
            'DEBE' => 'DEBE',
            'CANCELADO' => 'CANCELADO'
        ];
        $estadoPagoMostrar = $estadoPagoMap[$estadoPago] ?? $estadoPago;

        // Preparar datos para la fila (14 elementos)
        $rowData = [
            $fechaSalida,
            $remitente,
            $destinatario,
            ($encomienda["venta_serie"] ?? '') . "-" . str_pad($encomienda["venta_correlativo"] ?? '0', 8, "0", STR_PAD_LEFT),
            $encomienda["terminal_origen"] ?? '---',
            $encomienda["terminal_destino"] ?? '---',
            $encomienda["encomienda_cantidad"] ?? '0',
            $descripcion,
            number_format($precio_unitario, 2, '.', ''), // ← PRECIO UNITARIO CON IGV
            $encomienda["forma_pago"] ?? '---',
            $encomienda["medio_pago"] ?? '---',
            $estadoPagoMostrar,
            number_format($total_producto, 2, '.', ''), // ← TOTAL con IGV, corregido
            '',
            ''
        ];

        if ($rowData[1] == " ") {
            $rowData[1] = '---';
        }
        if ($rowData[2] == " ") {
            $rowData[2] = '---';
        }

        if (count($rowData) !== 14) {
            error_log("Error: El array de datos para Row no tiene 14 elementos: " . json_encode($rowData));
        }

        $pdf->Row($rowData, 3.2, 'C');
    }
}

// Verificar espacio para las firmas (altura total estimada: ~12 mm)
$signaturesHeight = 12; // 5 mm (fecha) + 3 mm (espacio) + 4 mm (líneas + textos)
if ($pdf->GetY() + $signaturesHeight > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
    $pdf->AddPage();
}

// Agregar espacio reducido después de la tabla
$pdf->Ln(12);

// Coordenadas base para firmas
$y = $pdf->GetY();
$pageWidth = $pdf->getPageWidth();
$fechaWidth = 80;
$firmaWidth = 40;
$spacing = 10;
$xFecha = $pageWidth - ($fechaWidth + 2 * $firmaWidth + 2 * $spacing + 10);
$xFirmaReceptor = $xFecha + $fechaWidth + $spacing;
$xFirmaResponsable = $xFirmaReceptor + $firmaWidth + $spacing;

error_log("Firmas: y=$y, pageHeight=" . $pdf->getPageHeight() . ", breakMargin=" . $pdf->getBreakMargin());

// Imprimir firmas como un bloque
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetXY($xFecha, $y);
$pdf->Cell($fechaWidth, 5, 'FECHA DE DESEMBARQUE: ____________________', 0, 0, 'L');
$pdf->SetXY($xFirmaReceptor, $y);
$pdf->Cell($firmaWidth, 0, '', 'T');
$pdf->SetXY($xFirmaReceptor, $y + 3);
$pdf->Cell($firmaWidth, 5, 'FIRMA DEL RECEPTOR', 0, 0, 'C');
$pdf->SetXY($xFirmaResponsable, $y);
$pdf->Cell($firmaWidth, 0, '', 'T');
$pdf->SetXY($xFirmaResponsable, $y + 3);
$pdf->Cell($firmaWidth, 5, 'FIRMA DEL RESPONSABLE', 0, 0, 'C');

$pdf->Output('reporte.pdf', 'I');