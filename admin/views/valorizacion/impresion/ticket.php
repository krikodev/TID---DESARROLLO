<?php

$es_inclusion = isset($es_interno) && $es_interno === true;

if (!$es_inclusion) {

    ini_set('memory_limit', '2048M');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('America/Lima');

    require_once('public/plugins/print/num_letras.php');
    require_once('public/plugins/tc/tcpdf.php');

    define("TEXT_SIZE_TITULO_T", 10);
    define("TEXT_SIZE_NORMAL_T", 7.5);
    define("TEXT_SIZE_CHICO_T", 6.5);

    // Ancho de ticket en mm (58 u 80 son los estándares más comunes)
    define("TICKET_ANCHO", 80);

    class TCPDF_Ticket extends TCPDF
    {
        protected $cMargin = 0.5;

        public function __construct($ancho = TICKET_ANCHO, $alto = 300)
        {
            // Alto "grande" de arranque: al final se recorta a la altura real con setPageFormat
            parent::__construct('P', 'mm', array($ancho, $alto), true, 'UTF-8', false);
            $this->SetMargins(3, 3, 3);
            $this->SetAutoPageBreak(false, 3);
        }

        function AutoPrint($dialog = false)
        {
            $param = ($dialog ? 'true' : 'false');
            $this->IncludeJS("print($param);");
        }

        // Línea punteada / separador
        function LineaSeparadora($caracter = '-')
        {
            $ancho = $this->getPageWidth() - $this->lMargin - $this->rMargin;
            $anchoChar = $this->GetStringWidth($caracter);
            $repeticiones = (int) floor($ancho / $anchoChar);
            $this->SetX($this->lMargin);
            $this->Cell($ancho, 3, str_repeat($caracter, $repeticiones), 0, 1, 'C');
        }

        // Fila de 2 columnas: etiqueta a la izquierda, valor a la derecha (alineado)
        function FilaEtiquetaValor($etiqueta, $valor, $size = TEXT_SIZE_NORMAL_T)
        {
            $ancho = $this->getPageWidth() - $this->lMargin - $this->rMargin;
            $this->SetFont('helvetica', '', $size);
            $this->SetX($this->lMargin);
            $this->Cell($ancho * 0.4, 3.6, $etiqueta, 0, 0, 'L');
            $this->Cell($ancho * 0.6, 3.6, $valor, 0, 1, 'R');
        }

        // Texto centrado
        function TextoCentrado($texto, $size = TEXT_SIZE_NORMAL_T, $bold = '', $h = 4)
        {
            $ancho = $this->getPageWidth() - $this->lMargin - $this->rMargin;
            $this->SetFont('helvetica', $bold, $size);
            $this->SetX($this->lMargin);
            $this->MultiCell($ancho, $h, $texto, 0, 'C', false, 1);
        }

        // Fila de tabla con anchos proporcionales al ancho de ticket, altura automática
        function FilaTicket($data, $widths, $aligns, $lineHeight = 3.3, $bold = false)
        {
            $nb = 1;
            for ($i = 0; $i < count($data); $i++) {
                $lineas = $this->calcularNumLineas((string) $data[$i], $widths[$i]);
                $nb = max($nb, $lineas);
            }
            $h = $lineHeight * $nb;

            $this->SetFont('helvetica', $bold ? 'B' : '', TEXT_SIZE_CHICO_T);
            $x = $this->lMargin;
            $y = $this->GetY();
            $this->SetXY($x, $y);

            for ($i = 0; $i < count($data); $i++) {
                $this->MultiCell($widths[$i], $h, $data[$i], 0, $aligns[$i], false, 0, '', '', true, 0, false, true, $h, 'M');
            }
            $this->SetXY($x, $y + $h);
        }

        // Método propio (NO usar el nombre getNumLines, ya existe en TCPDF con otra firma)
        function calcularNumLineas($txt, $w)
        {
            $this->SetFont('helvetica', '', TEXT_SIZE_CHICO_T);
            $alturaTexto = $this->getStringHeight($w, $txt);
            $alturaUnaLinea = $this->getStringHeight($w, 'X');
            if ($alturaUnaLinea <= 0) {
                return 1;
            }
            $lines = $alturaTexto / $alturaUnaLinea;
            return max(1, (int) ceil($lines));
        }
    }

    $data = $this->data;

    // ---------- PASADA 1 (MEDICIÓN) ----------
    // Generamos el contenido en un PDF descartable, alto, solo para saber
    // cuánto ocupa realmente el ticket. No se muestra al usuario.
    $pdfMedicion = new TCPDF_Ticket(TICKET_ANCHO, 1000);
    $pdfMedicion->setPrintHeader(false);
    $pdfMedicion->SetPrintFooter(false);
    $pdfMedicion->AddPage();
    generarValorizacionTicket($pdfMedicion, $data);
    $altoFinal = $pdfMedicion->GetY() + 5;
    unset($pdfMedicion);

    // ---------- PASADA 2 (DEFINITIVA) ----------
    // Creamos el PDF real ya con el alto exacto desde el inicio. Esto evita
    // redimensionar la página después de dibujar el contenido (lo cual deja
    // el contenido fuera del área visible y el ticket sale en blanco).
    $pdf = new TCPDF_Ticket(TICKET_ANCHO, $altoFinal);
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    generarValorizacionTicket($pdf, $data);

    $pdf->AutoPrint(false);

    ob_clean();
    $pdf->Output('valorizacion_ticket.pdf', 'I');
    exit;
}

/**
 * Genera el TICKET de VALORIZACIÓN (formato angosto, ideal impresora térmica 80mm)
 * a partir de $data['EMPRESA'], $data['CABECERA'], $data['BODY']
 */
function generarValorizacionTicket($pdf, $data)
{
    $empresa = $data['EMPRESA'];
    $cabecera = $data['CABECERA'];
    $body = $data['BODY'];

    $anchoUtil = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    if (!empty($empresa["logo"]) && file_exists($empresa["logo"])) {
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
        list($imgWidth, $imgHeight) = getimagesize($empresa["logo"]);
        $scale = min(35 / $imgWidth, 25 / $imgHeight);
        $finalWidth = $imgWidth * $scale;
        $finalHeight = $imgHeight * $scale;

        // Centrar la imagen en el rectángulo
        $xLogo = (72.1 - $finalWidth) / 2;
        $yLogo = $bgY + (25 - $finalHeight) / 2 - 8;

        // Insertar imagen
        $pdf->Image(
            $empresa["logo"],
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

        $pdf->Ln(15); // Saltar después de la cabecera
    }
    // ---------- ENCABEZADO ----------
    $pdf->TextoCentrado($empresa['nombre_comercial'], 11, 'B', 4.5);
    $pdf->TextoCentrado('RUC: ' . $empresa['ruc'], TEXT_SIZE_NORMAL_T);

    $direccionCompleta = trim($empresa['direccion'] . ' - ' . $empresa['distrito'] . ' - ' . $empresa['provincia']);
    $pdf->TextoCentrado($direccionCompleta, TEXT_SIZE_CHICO_T);

    $pdf->Ln(1);
    $pdf->LineaSeparadora('=');

    $pdf->TextoCentrado('VALORIZACIÓN DE SERVICIOS', TEXT_SIZE_NORMAL_T, 'B');
    $nroDocumento = $cabecera['serie'] . '-' . str_pad($cabecera['id'], 6, '0', STR_PAD_LEFT);
    $pdf->TextoCentrado($nroDocumento, TEXT_SIZE_NORMAL_T, 'B');

    $pdf->LineaSeparadora('=');

    // ---------- DATOS DEL CLIENTE ----------
    $fechaFormateada = date('d-m-Y', strtotime($cabecera['fecha']));
    $pdf->FilaEtiquetaValor('FECHA:', $fechaFormateada);
    $pdf->FilaEtiquetaValor('CLIENTE:', '');
    $pdf->SetFont('helvetica', '', TEXT_SIZE_NORMAL_T);
    $pdf->SetX($pdf->getMargins()['left']);
    $pdf->MultiCell($anchoUtil, 3.4, $cabecera['cliente'], 0, 'L');
    $pdf->FilaEtiquetaValor('RUC/DNI:', $cabecera['num_docu']);

    $pdf->LineaSeparadora('-');

    // ---------- DETALLE (BODY) ----------
    // Se muestra cada encomienda de forma compacta, apilando los datos en vez de columnas anchas
    $contador = 1;
    foreach ($body as $item) {
        $fechaItem = date('d-m-Y', strtotime($item['fecha_salida']));

        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_CHICO_T);
        $pdf->SetX($pdf->getMargins()['left']);
        // $pdf->Cell($anchoUtil, 3.2, $contador . '. ' . $item['documento'], 0, 1, 'L');

        $pdf->SetFont('helvetica', '', TEXT_SIZE_CHICO_T);
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->MultiCell($anchoUtil, 3, 'Fecha: ' . date('d-m-Y', strtotime($item['fecha_salida'])), 0, 'L');
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->MultiCell($anchoUtil, 3, 'De: ' . $item['origen'], 0, 'L');
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->MultiCell($anchoUtil, 3, 'Hacia: ' . $item['destino'], 0, 'L');
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->MultiCell($anchoUtil, 3, 'Vehiculo: ' . $item['vehiculo'], 0, 'L');
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->Cell($anchoUtil * 0.6, 3, 'Conductor: ' . $item['conductor'], 0, 0, 'L');
        $pdf->Cell($anchoUtil * 0.4, 3, 'S/ ' . number_format((float) $item['precio'], 2), 0, 1, 'R');
        $pdf->LineaSeparadora('-');

        $pdf->Ln(1);
        $contador++;
    }

    $pdf->LineaSeparadora('-');

    // ---------- TOTALES ----------
    $pdf->FilaEtiquetaValor('SUBTOTAL:', 'S/ ' . number_format((float) $cabecera['subtotal'], 2));
    $pdf->FilaEtiquetaValor('IGV:', 'S/ ' . number_format((float) $cabecera['igv'], 2));

    $pdf->SetFont('helvetica', 'B', TEXT_SIZE_TITULO_T);
    $pdf->SetX($pdf->getMargins()['left']);
    $pdf->Cell($anchoUtil * 0.4, 5, 'TOTAL:', 0, 0, 'L');
    $pdf->Cell($anchoUtil * 0.6, 5, 'S/ ' . number_format((float) $cabecera['total'], 2), 0, 1, 'R');

    $pdf->LineaSeparadora('=');

    // ---------- OBSERVACIÓN ----------
    if (!empty($cabecera['observacion'])) {
        $pdf->SetFont('helvetica', 'B', TEXT_SIZE_CHICO_T);
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->Cell($anchoUtil, 3, 'OBSERVACIÓN:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', TEXT_SIZE_CHICO_T);
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->MultiCell($anchoUtil, 3, $cabecera['observacion'], 0, 'L');
        $pdf->Ln(1);
        $pdf->LineaSeparadora('-');
    }

    // ---------- PIE / FIRMA ----------
    $pdf->Ln(6);
    $pdf->SetX($pdf->getMargins()['left']);
    $pdf->Cell($anchoUtil, 0, '', 'T', 1, 'C');
    $pdf->TextoCentrado('Firma del Responsable', TEXT_SIZE_CHICO_T);

    $pdf->Ln(2);
    $pdf->TextoCentrado('¡Gracias por su preferencia!', TEXT_SIZE_CHICO_T);
}