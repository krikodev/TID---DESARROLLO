<?php
// ============================================================================
// ARCHIVO: admin/views/flete/ticket/a4.php
// GENERA TICKET DE FLETE (formato 80mm) - CON EXTRACCIÓN FLEXIBLE
// ============================================================================

// Configuración básica
ini_set('memory_limit', '2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// Incluir librerías necesarias
require_once('public/plugins/tc/tcpdf.php');

// Constantes
define("TCK_ANCHO_MM", 80);
define("TCK_MARGEN", 3);
define("TCK_ALTO_MEDICION", 2000);

define("TCK_SIZE_TITULO", 11);
define("TCK_SIZE_NORMAL", 8);
define("TCK_SIZE_REDUCIDO", 7);

// ========================================================================
// Clase base
// ========================================================================
class TCPDF_Ticket extends TCPDF
{
    public function __construct($altoPaginaMm)
    {
        parent::__construct('P', 'mm', [TCK_ANCHO_MM, $altoPaginaMm], true, 'UTF-8', false);
        $this->SetMargins(TCK_MARGEN, TCK_MARGEN, TCK_MARGEN);
        $this->SetAutoPageBreak(false, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetCellPadding(0);
    }

    public function anchoUtil()
    {
        return $this->getPageWidth() - TCK_MARGEN - TCK_MARGEN;
    }
}

// ========================================================================
// Helpers
// ========================================================================

function tck_v($arr, $key, $default = '')
{
    if (!is_array($arr)) return $default;
    return (isset($arr[$key]) && $arr[$key] !== null && $arr[$key] !== '') ? $arr[$key] : $default;
}

function tck_money($valor)
{
    return 'S/ ' . number_format((float)$valor, 2);
}

function tck_fecha($valor)
{
    $valor = (string)($valor ?? '');
    if ($valor === '') return '-';
    $ts = strtotime($valor);
    return ($ts === false) ? $valor : date('d/m/Y', $ts);
}

function tck_hora($valor)
{
    $valor = (string)($valor ?? '');
    if ($valor === '') return '-';
    $ts = strtotime($valor);
    return ($ts === false) ? $valor : date('h:i A', $ts);
}

// ========================================================================
// Dibujo de elementos
// ========================================================================

function tck_separador($pdf)
{
    $pdf->SetFont('helvetica', '', TCK_SIZE_REDUCIDO);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineStyle(['width' => 0.1, 'dash' => '1,1']);
    $y = $pdf->GetY() + 1;
    $pdf->Line(TCK_MARGEN, $y, TCK_MARGEN + $pdf->anchoUtil(), $y);
    $pdf->SetLineStyle(['dash' => 0]);
    $pdf->SetY($y + 1.5);
}

function tck_tituloBloque($pdf, $texto)
{
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_NORMAL);
    $pdf->Cell($pdf->anchoUtil(), 4.5, mb_strtoupper($texto), 0, 1, 'C');
}

function tck_linea($pdf, $etiqueta, $valor, $negritaEtiqueta = true)
{
    $pdf->SetFont('helvetica', $negritaEtiqueta ? 'B' : '', TCK_SIZE_NORMAL);
    $anchoEtiqueta = $pdf->GetStringWidth($etiqueta . ' ') + 1;
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoEtiqueta, 4, $etiqueta, 0, 0, 'L');
    $pdf->SetFont('helvetica', '', TCK_SIZE_NORMAL);
    $pdf->Cell($pdf->anchoUtil() - $anchoEtiqueta, 4, (string)$valor, 0, 1, 'L');
}

function tck_bloqueMulti($pdf, $etiqueta, $valor, $negritaEtiqueta = true)
{
    if ($etiqueta !== '') {
        $pdf->SetFont('helvetica', $negritaEtiqueta ? 'B' : '', TCK_SIZE_NORMAL);
        $pdf->SetX(TCK_MARGEN);
        $pdf->Cell($pdf->anchoUtil(), 4, $etiqueta, 0, 1, 'L');
    }
    $pdf->SetFont('helvetica', '', TCK_SIZE_NORMAL);
    $ancho = $pdf->anchoUtil();
    $alto = $pdf->getStringHeight($ancho, (string)$valor);
    $pdf->SetX(TCK_MARGEN);
    $pdf->MultiCell($ancho, $alto, (string)$valor, 0, 'L', false, 1, TCK_MARGEN, $pdf->GetY());
}

// ========================================================================
// Contenido del ticket (con extracción flexible)
// ========================================================================

function tck_dibujarContenido($pdf, $data)
{
    // --- Extracción flexible: si existe 'data' anidado, úsalo; sino, usa $data directamente ---
    $payload = tck_v($data, 'data', $data); // si no tiene 'data', se asume que $data ya es el payload
    $empresa = tck_v($payload, 'empresa', []);
    $flete = tck_v($payload, 'flete', []);

    $anchoUtil = $pdf->anchoUtil();
    $pdf->SetY(TCK_MARGEN);

    // --- LOGO (con cálculo de altura para evitar superposición) ---
    $logo = tck_v($empresa, 'logo', '');
    if (!empty($logo) && file_exists($logo)) {
        $anchoLogo = 20;
        $xLogo = TCK_MARGEN + (($anchoUtil - $anchoLogo) / 2);
        list($imgWidth, $imgHeight) = @getimagesize($logo);
        if ($imgWidth > 0 && $imgHeight > 0) {
            $altoLogo = ($anchoLogo / $imgWidth) * $imgHeight;
        } else {
            $altoLogo = 10;
        }
        $pdf->Image($logo, $xLogo, $pdf->GetY(), $anchoLogo, 0, '', '', '', false, 300);
        $pdf->SetY($pdf->GetY() + $altoLogo + 1);
    } else {
        $pdf->Ln(2);
    }

    // --- DATOS DE LA EMPRESA ---
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $razonSocial = tck_v($empresa, 'razon_social', '');
    if ($razonSocial !== '') {
        $pdf->MultiCell($anchoUtil, 4, $razonSocial, 0, 'C');
    }

    $pdf->SetFont('helvetica', '', TCK_SIZE_REDUCIDO);
    $ruc = tck_v($empresa, 'ruc', '');
    if ($ruc !== '') {
        $pdf->SetX(TCK_MARGEN);
        $pdf->Cell($anchoUtil, 3.5, 'RUC: ' . $ruc, 0, 1, 'C');
    }
    $direccion = tck_v($empresa, 'direccion', '');
    if ($direccion !== '') {
        $pdf->SetX(TCK_MARGEN);
        $pdf->MultiCell($anchoUtil, 3.5, $direccion, 0, 'C');
    }
    $telefono = tck_v($empresa, 'telefono', '');
    if ($telefono !== '') {
        $pdf->SetX(TCK_MARGEN);
        $pdf->Cell($anchoUtil, 3.5, 'Telf: ' . $telefono, 0, 1, 'C');
    }

    tck_separador($pdf);

    // --- TÍTULO ---
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_TITULO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 5, 'FLETE / SERVICIO', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4.5, 'N° ' . tck_v($flete, 'numero_flete', '-'), 0, 1, 'C');
    $pdf->Ln(1);

    // --- FECHA / HORA / ESTADO / OPERACIÓN ---
    tck_linea($pdf, 'Fecha:', tck_fecha(tck_v($flete, 'fecha_salida', '')));
    tck_linea($pdf, 'Hora:', tck_hora(tck_v($flete, 'hora_salida', '')));
    tck_linea($pdf, 'Estado:', mb_strtoupper(tck_v($flete, 'estado', '-')));
    tck_linea($pdf, 'Operación:', mb_strtoupper(tck_v($flete, 'tipo_operacion', '-')));

    tck_separador($pdf);

    // --- CLIENTE ---
    tck_tituloBloque($pdf, 'Cliente');
    tck_bloqueMulti($pdf, 'Cliente:', tck_v($flete, 'cliente_nombres', '-'));
    tck_linea($pdf, 'Documento:', tck_v($flete, 'cliente_num_docu', '-'));

    $idCotizacion = tck_v($flete, 'id_cotizacion', '');
    if ($idCotizacion !== '') {
        tck_linea($pdf, 'Cotización:', 'N° ' . $idCotizacion);
    }

    $condicion = mb_strtoupper(tck_v($flete, 'condicion_pago', '-'));
    tck_linea($pdf, 'Pago:', $condicion);

    if (strtoupper(tck_v($flete, 'condicion_pago', '')) === 'CREDITO') {
        tck_linea($pdf, 'Días crédito:', tck_v($flete, 'dias_credito', '-'));
        tck_linea($pdf, 'Vencimiento:', tck_fecha(tck_v($flete, 'fecha_vencimiento', '')));
    }

    tck_separador($pdf);

    // --- RUTA ---
    tck_tituloBloque($pdf, 'Ruta');

    $pdf->SetFont('helvetica', 'B', TCK_SIZE_REDUCIDO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 3.5, 'ORIGEN', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $pdf->MultiCell($anchoUtil, 4, tck_v($flete, 'origen', '-'), 0, 'L');
    tck_bloqueMulti($pdf, '', tck_v($flete, 'direccion_origen', '-'), false);

    $pdf->SetFont('helvetica', '', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, '        |', 0, 1, 'L');
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, '        v', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', TCK_SIZE_REDUCIDO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 3.5, 'DESTINO', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $pdf->MultiCell($anchoUtil, 4, tck_v($flete, 'destino', '-'), 0, 'L');
    tck_bloqueMulti($pdf, '', tck_v($flete, 'direccion_destino', '-'), false);

    tck_separador($pdf);

    // --- TRANSPORTE ---
    $tipo = strtoupper(tck_v($flete, 'tipo_operacion', ''));
    tck_tituloBloque($pdf, 'Transporte');

    if ($tipo === 'PROPIO') {
        tck_bloqueMulti($pdf, 'Vehículo:', tck_v($flete, 'vehiculo_descripcion', '-'));
        tck_linea($pdf, 'Placa:', tck_v($flete, 'vehiculo_placa', '-'));
        tck_bloqueMulti($pdf, 'Conductor:', tck_v($flete, 'conductor_nombres', '-'));
        tck_linea($pdf, 'Doc.:', tck_v($flete, 'conductor_num_docu', '-'));
        tck_linea($pdf, 'Licencia:', tck_v($flete, 'conductor_licencia', '-'));
        tck_linea($pdf, 'Categoría:', tck_v($flete, 'conductor_categoria', '-'));
    } elseif ($tipo === 'TERCERIZADO') {
        tck_bloqueMulti($pdf, 'Transportista:', tck_v($flete, 'proveedor_nombres', '-'));
        tck_linea($pdf, 'Documento:', tck_v($flete, 'proveedor_num_docu', '-'));
        tck_bloqueMulti($pdf, 'Vehículo:', tck_v($flete, 'vehiculo_tercerizado_descripcion', '-'));
        tck_linea($pdf, 'Placa:', tck_v($flete, 'vehiculo_tercerizado_placa', '-'));
        tck_bloqueMulti($pdf, 'Conductor:', tck_v($flete, 'conductor_tercerizado_nombres', '-'));
        tck_linea($pdf, 'Doc.:', tck_v($flete, 'conductor_tercerizado_num_docu', '-'));
        tck_linea($pdf, 'Licencia:', tck_v($flete, 'conductor_tercerizado_licencia', '-'));
        tck_linea($pdf, 'Categoría:', tck_v($flete, 'conductor_tercerizado_categoria', '-'));
    } elseif ($tipo === 'MIXTO') {
        $huboDato = false;
        $camposMixto = [
            ['Vehículo propio:', tck_v($flete, 'vehiculo_descripcion', '')],
            ['Placa propia:', tck_v($flete, 'vehiculo_placa', '')],
            ['Conductor propio:', tck_v($flete, 'conductor_nombres', '')],
            ['Transportista tercero:', tck_v($flete, 'proveedor_nombres', '')],
            ['Vehículo tercero:', tck_v($flete, 'vehiculo_tercerizado_descripcion', '')],
            ['Placa tercero:', tck_v($flete, 'vehiculo_tercerizado_placa', '')],
            ['Conductor tercero:', tck_v($flete, 'conductor_tercerizado_nombres', '')],
        ];
        foreach ($camposMixto as $campo) {
            if ($campo[1] !== '') {
                tck_bloqueMulti($pdf, $campo[0], $campo[1]);
                $huboDato = true;
            }
        }
        if (!$huboDato) {
            $pdf->SetFont('helvetica', 'I', TCK_SIZE_REDUCIDO);
            $pdf->SetX(TCK_MARGEN);
            $pdf->Cell($anchoUtil, 4, 'Sin datos de transporte registrados.', 0, 1, 'L');
        }
    } else {
        $pdf->SetFont('helvetica', 'I', TCK_SIZE_REDUCIDO);
        $pdf->SetX(TCK_MARGEN);
        $pdf->Cell($anchoUtil, 4, 'Tipo de operación no especificado.', 0, 1, 'L');
    }

    tck_separador($pdf);

    // --- RESUMEN ---
    tck_tituloBloque($pdf, 'Resumen');

    $incluyeIgv = tck_v($flete, 'incluye_igv', false);
    $incluyeIgvTexto = ($incluyeIgv === true || $incluyeIgv === 1 || $incluyeIgv === '1') ? 'Sí' : 'No';

    $pdf->SetFont('helvetica', '', TCK_SIZE_NORMAL);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil * 0.5, 4.5, 'Subtotal:', 0, 0, 'L');
    $pdf->Cell($anchoUtil * 0.5, 4.5, tck_money(tck_v($flete, 'subtotal', 0)), 0, 1, 'R');

    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil * 0.5, 4.5, 'IGV:', 0, 0, 'L');
    $pdf->Cell($anchoUtil * 0.5, 4.5, tck_money(tck_v($flete, 'igv', 0)), 0, 1, 'R');

    $pdf->SetFont('helvetica', 'B', TCK_SIZE_TITULO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil * 0.5, 6, 'TOTAL:', 0, 0, 'L');
    $pdf->Cell($anchoUtil * 0.5, 6, tck_money(tck_v($flete, 'total', 0)), 0, 1, 'R');

    $pdf->SetFont('helvetica', '', TCK_SIZE_REDUCIDO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, 'Incluye IGV: ' . $incluyeIgvTexto, 0, 1, 'L');

    // --- OBSERVACIONES ---
    $observacion = trim((string)tck_v($flete, 'observacion', ''));
    if ($observacion !== '') {
        tck_separador($pdf);
        tck_tituloBloque($pdf, 'Observaciones');
        tck_bloqueMulti($pdf, '', $observacion, false);
    }

    tck_separador($pdf);

    // --- CONFORMIDAD ---
    tck_tituloBloque($pdf, 'Conformidad');
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', '', TCK_SIZE_NORMAL);

    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, 'Firma: ________________________', 0, 1, 'L');
    $pdf->Ln(1);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, 'Nombre: _______________________', 0, 1, 'L');
    $pdf->Ln(1);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, 'DNI: __________________________', 0, 1, 'L');

    tck_separador($pdf);

    // --- PIE ---
    $pdf->SetFont('helvetica', 'B', TCK_SIZE_REDUCIDO);
    $pdf->SetX(TCK_MARGEN);
    $pdf->Cell($anchoUtil, 4, 'Gracias por confiar en nosotros', 0, 1, 'C');

    $pdf->Ln(2);
}

// ========================================================================
// Generador con alto dinámico
// ========================================================================

function tck_generarTicketFlete($data)
{
    // Pasada 1: medición
    $pdfMedicion = new TCPDF_Ticket(TCK_ALTO_MEDICION);
    $pdfMedicion->AddPage();
    tck_dibujarContenido($pdfMedicion, $data);
    $alturaContenido = $pdfMedicion->GetY();

    // Pasada 2: PDF final con el alto exacto
    $alturaFinal = $alturaContenido + TCK_MARGEN;
    $pdf = new TCPDF_Ticket($alturaFinal);
    $pdf->SetCreator('Sistema de Transporte');
    $pdf->SetTitle('Ticket Flete N° ' . tck_v(tck_v($data, 'flete', []), 'id_flete', ''));
    $pdf->AddPage();
    tck_dibujarContenido($pdf, $data);

    return $pdf;
}

// ============================================================================
// GENERACIÓN DEL PDF (SOLO SI SE LLAMA DIRECTAMENTE Y EXISTE $this->data)
// ============================================================================

if (isset($this->data) && is_array($this->data)) {
    // Limpiar cualquier salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }

    try {
        $pdf = tck_generarTicketFlete($this->data);
        $idFlete = $this->data['data']['flete']['id_flete'] ?? $this->data['flete']['id_flete'] ?? 'orden';
        $pdf->Output('ticket_flete_' . $idFlete . '.pdf', 'I');
        exit;
    } catch (Exception $e) {
        echo "Error al generar el ticket: " . $e->getMessage();
        exit;
    }
} else {
    echo "Error: No se recibieron datos para generar el ticket.";
    exit;
}