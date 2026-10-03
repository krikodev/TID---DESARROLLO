<?php
// ============================================================================
// ARCHIVO: admin/views/flete/impresion/a4.php
// GENERA PDF DE FLETE (ORDEN DE SERVICIO)
// ============================================================================

// Configuración básica
ini_set('memory_limit', '2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');

// Incluir librerías necesarias
require_once('public/plugins/print/num_letras.php');
require_once('public/plugins/tc/tcpdf.php');

// Constantes
define("TEXT_SIZE_TITULO", 16);
define("TEXT_SIZE_NORMAL", 9);
define("TEXT_SIZE_REDUCIDO_COT", 8);
define("COLOR_AZUL_R", 21);
define("COLOR_AZUL_G", 40);
define("COLOR_AZUL_B", 82);
define("COLOR_NARANJA_R", 243);
define("COLOR_NARANJA_G", 146);
define("COLOR_NARANJA_B", 0);
define("COLOR_ROJO_R", 178);
define("COLOR_ROJO_G", 34);
define("COLOR_ROJO_B", 34);

// ============================================================================
// CLASE PERSONALIZADA (renombrada para evitar conflicto con autoload)
// ============================================================================
class TCPDF_FletePDF extends TCPDF
{
    protected $widths;
    protected $aligns;
    protected $cMargin = 1;

    // Datos completos (se cargan desde el constructor o desde el exterior)
    protected $data = [];

    public function __construct($data = [])
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 10);
        $this->data = is_array($data) ? $data : [];
    }

    // ================================================================
    // HELPERS
    // ================================================================

    protected function v($arr, $key, $default = '')
    {
        if (!is_array($arr)) return $default;
        return (isset($arr[$key]) && $arr[$key] !== null && $arr[$key] !== '') ? $arr[$key] : $default;
    }

    protected function getEmpresa()
    {
        $payload = $this->v($this->data, 'data', []);
        return $this->v($payload, 'empresa', []);
    }

    protected function getFlete()
    {
        $payload = $this->v($this->data, 'data', []);
        return $this->v($payload, 'flete', []);
    }

    protected function money($valor)
    {
        return 'S/ ' . number_format((float)$valor, 2);
    }

    protected function getMarginsSafe()
    {
        if (method_exists($this, 'getMargins')) {
            return $this->getMargins();
        }
        return ['left' => 10, 'right' => 10, 'top' => 10, 'bottom' => 10];
    }

    protected function checkSpace($alturaNecesaria)
    {
        $limite = $this->getPageHeight() - $this->getBreakMargin();
        if (($this->GetY() + $alturaNecesaria) > $limite) {
            $this->AddPage();
            return true;
        }
        return false;
    }

    protected function sectionTitle($texto, $colorR = COLOR_AZUL_R, $colorG = COLOR_AZUL_G, $colorB = COLOR_AZUL_B)
    {
        $this->checkSpace(9);
        $margenes = $this->getMarginsSafe();
        $anchoUtil = $this->getPageWidth() - $margenes['left'] - $margenes['right'];
        $this->SetFillColor($colorR, $colorG, $colorB);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
        $this->Cell($anchoUtil, 6, '  ' . mb_strtoupper($texto), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }

    protected function labelValue($x, $y, $anchoEtiqueta, $anchoValor, $etiqueta, $valor, $fontSize = TEXT_SIZE_NORMAL)
    {
        $this->SetXY($x, $y);
        $this->SetFont('helvetica', 'B', $fontSize);
        $this->Cell($anchoEtiqueta, 5, $etiqueta, 0, 0, 'L');
        $this->SetFont('helvetica', '', $fontSize);
        $this->Cell($anchoValor, 5, (string)$valor, 0, 0, 'L');
        return $this->GetY() + 5;
    }

    // ================================================================
    // CABECERA
    // ================================================================

    protected function dibujarCabecera($empresa, $flete)
    {
        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $margenDer = $margenes['right'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenDer;

        $yInicio = $this->GetY();

        $anchoLogo = 28;
        $anchoDatosEmpresa = 90;
        $anchoRecuadro = $anchoUtil - $anchoLogo - $anchoDatosEmpresa;

        $xLogo = $margenIzq;
        $xDatos = $margenIzq + $anchoLogo + 2;
        $xRecuadro = $margenIzq + $anchoLogo + $anchoDatosEmpresa + 2;

        // --- Logo ---
        $logo = $this->v($empresa, 'logo', '');
        if (!empty($logo) && file_exists($logo)) {
            set_error_handler(function ($errno, $errstr) {
                if (strpos($errstr, 'iCCP: known incorrect sRGB profile') !== false) return true;
                return false;
            });
            $this->Image($logo, $xLogo, $yInicio, $anchoLogo, 0, '', '', '', false, 300);
            restore_error_handler();
        }

        // --- Datos empresa ---
        $this->SetXY($xDatos, $yInicio);
        $this->SetFont('helvetica', 'B', 11);
        $this->MultiCell($anchoDatosEmpresa, 5, $this->v($empresa, 'razon_social', ''), 0, 'L');

        $this->SetX($xDatos);
        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $ruc = $this->v($empresa, 'ruc', '');
        if ($ruc) {
            $this->Cell($anchoDatosEmpresa, 4, 'RUC: ' . $ruc, 0, 1, 'L');
            $this->SetX($xDatos);
        }
        $dir = $this->v($empresa, 'direccion', '');
        if ($dir) {
            $this->MultiCell($anchoDatosEmpresa, 4, $dir, 0, 'L');
            $this->SetX($xDatos);
        }
        $tel = $this->v($empresa, 'telefono', $this->v($empresa, 'celular', ''));
        if ($tel) {
            $this->Cell($anchoDatosEmpresa, 4, 'Telf: ' . $tel, 0, 1, 'L');
        }

        $yFinDatos = $this->GetY();

        // --- Recuadro ---
        $alturaRecuadro = 26;
        $this->SetDrawColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
        $this->SetLineWidth(0.4);
        $this->Rect($xRecuadro, $yInicio, $anchoRecuadro, $alturaRecuadro, 'D');

        $this->SetFillColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY($xRecuadro, $yInicio);
        $this->Cell($anchoRecuadro, 7, 'FLETE / ORDEN DE SERVICIO', 0, 1, 'C', true);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetXY($xRecuadro, $yInicio + 7);
        $this->Cell($anchoRecuadro, 6, 'N° ' . $this->v($flete, 'numero_flete', '-'), 0, 1, 'C');

        $estado = mb_strtoupper($this->v($flete, 'estado', '-'));
        $this->SetFont('helvetica', 'B', 9);
        $this->SetXY($xRecuadro, $yInicio + 13);
        $this->Cell($anchoRecuadro, 6, 'Estado: ' . $estado, 0, 1, 'C');

        $yFinRecuadro = $yInicio + $alturaRecuadro;
        $yFinal = max($yFinDatos, $yFinRecuadro) + 3;
        $this->SetY($yFinal);

        $this->SetDrawColor(COLOR_NARANJA_R, COLOR_NARANJA_G, COLOR_NARANJA_B);
        $this->SetLineWidth(0.6);
        $this->Line($margenIzq, $this->GetY(), $margenIzq + $anchoUtil, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0, 0, 0);
        $this->Ln(3);
    }

    // ================================================================
    // DATOS CLIENTE
    // ================================================================

    protected function dibujarDatosCliente($flete)
    {
        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $mitad = $anchoUtil / 2;

        $this->sectionTitle('Datos del Cliente');
        $y0 = $this->GetY();

        $y1 = $this->labelValue($margenIzq, $y0, 22, $mitad - 22, 'Cliente:', $this->v($flete, 'cliente_nombres', '-'));
        $y2 = $this->labelValue($margenIzq, $y1, 22, $mitad - 22, 'Documento:', $this->v($flete, 'cliente_num_docu', '-'));

        $idCotizacion = $this->v($flete, 'id_cotizacion', '');
        $yIzq = $y2;
        if ($idCotizacion) {
            $yIzq = $this->labelValue($margenIzq, $yIzq, 22, $mitad - 22, 'Cotización:', 'N° ' . $idCotizacion);
        }

        $xDer = $margenIzq + $mitad;
        $condicion = mb_strtoupper($this->v($flete, 'condicion_pago', '-'));
        $yDer = $this->labelValue($xDer, $y0, 32, $mitad - 32, 'Condición de pago:', $condicion);
        $yDer = $this->labelValue($xDer, $yDer, 32, $mitad - 32, 'Estado de pago:', mb_strtoupper($this->v($flete, 'estado_pago', '-')));

        if (strtoupper($this->v($flete, 'condicion_pago', '')) === 'CREDITO') {
            $diasCredito = $this->v($flete, 'dias_credito', '-');
            $fechaVenc = $this->v($flete, 'fecha_vencimiento_format', $this->v($flete, 'fecha_vencimiento', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 32, $mitad - 32, 'Días de crédito:', $diasCredito);
            $yDer = $this->labelValue($xDer, $yDer, 32, $mitad - 32, 'Vence:', $fechaVenc);
        }

        $this->SetY(max($yIzq, $yDer) + 2);
    }

    // ================================================================
    // DATOS SERVICIO
    // ================================================================

    protected function dibujarDatosServicio($flete)
    {
        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $tercio = $anchoUtil / 3;

        $this->sectionTitle('Datos del Servicio');
        $y0 = $this->GetY();

        $fechaSalida = $this->v($flete, 'fecha_salida_format', $this->v($flete, 'fecha_salida', '-'));
        $horaSalida = $this->v($flete, 'hora_salida_format', $this->v($flete, 'hora_salida', '-'));
        $this->labelValue($margenIzq, $y0, 22, $tercio - 22, 'Salida:', $fechaSalida);
        $this->labelValue($margenIzq + $tercio, $y0, 15, $tercio - 15, 'Hora:', $horaSalida);
        $tipoOperacion = mb_strtoupper($this->v($flete, 'tipo_operacion', '-'));
        $this->labelValue($margenIzq + (2 * $tercio), $y0, 15, $tercio - 15, 'Tipo:', $tipoOperacion);
        $this->SetY($y0 + 6);

        // Origen / Destino
        $mitad = $anchoUtil / 2;
        $xIzq = $margenIzq;
        $xDer = $margenIzq + $mitad + 2;
        $anchoCol = $mitad - 2;

        $direccionOrigen = $this->v($flete, 'direccion_origen', '-');
        $direccionDestino = $this->v($flete, 'direccion_destino', '-');

        $alturaOrigenTxt = $this->getStringHeight($anchoCol - 2, $direccionOrigen);
        $alturaDestinoTxt = $this->getStringHeight($anchoCol - 2, $direccionDestino);
        $alturaBloque = max($alturaOrigenTxt, $alturaDestinoTxt) + 14;

        $this->checkSpace($alturaBloque);
        $yBloque = $this->GetY();

        $this->SetFillColor(245, 245, 245);
        $this->Rect($xIzq, $yBloque, $anchoCol, $alturaBloque, 'F');
        $this->Rect($xDer, $yBloque, $anchoCol, $alturaBloque, 'F');

        // Origen
        $this->SetXY($xIzq + 2, $yBloque + 1.5);
        $this->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
        $this->SetTextColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
        $this->Cell($anchoCol - 4, 5, 'ORIGEN', 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetX($xIzq + 2);
        $this->SetFont('helvetica', 'B', TEXT_SIZE_REDUCIDO_COT);
        $this->Cell($anchoCol - 4, 4, $this->v($flete, 'origen', '-'), 0, 1, 'L');
        $this->SetXY($xIzq + 2, $this->GetY());
        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $this->MultiCell($anchoCol - 4, $alturaOrigenTxt, $direccionOrigen, 0, 'L', false, 1, $xIzq + 2, $this->GetY());

        // Destino
        $this->SetXY($xDer + 2, $yBloque + 1.5);
        $this->SetFont('helvetica', 'B', TEXT_SIZE_NORMAL);
        $this->SetTextColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
        $this->Cell($anchoCol - 4, 5, 'DESTINO', 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        $this->SetX($xDer + 2);
        $this->SetFont('helvetica', 'B', TEXT_SIZE_REDUCIDO_COT);
        $this->Cell($anchoCol - 4, 4, $this->v($flete, 'destino', '-'), 0, 1, 'L');
        $this->SetXY($xDer + 2, $this->GetY());
        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $this->MultiCell($anchoCol - 4, $alturaDestinoTxt, $direccionDestino, 0, 'L', false, 1, $xDer + 2, $this->GetY());

        $this->SetY($yBloque + $alturaBloque + 3);
    }

    // ================================================================
    // DATOS TRANSPORTE
    // ================================================================

    protected function dibujarDatosTransporte($flete)
    {
        $tipo = strtoupper($this->v($flete, 'tipo_operacion', ''));
        $this->sectionTitle('Datos de Transporte (' . ($tipo ?: '-') . ')');

        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $mitad = $anchoUtil / 2;
        $xDer = $margenIzq + $mitad;
        $y0 = $this->GetY();

        if ($tipo === 'PROPIO') {
            $yIzq = $this->labelValue($margenIzq, $y0, 22, $mitad - 22, 'Vehículo:', $this->v($flete, 'vehiculo_descripcion', '-'));
            $yIzq = $this->labelValue($margenIzq, $yIzq, 22, $mitad - 22, 'Placa:', $this->v($flete, 'vehiculo_placa', '-'));
            $yDer = $this->labelValue($xDer, $y0, 24, $mitad - 24, 'Conductor:', $this->v($flete, 'conductor_nombres', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Documento:', $this->v($flete, 'conductor_num_docu', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Licencia:', $this->v($flete, 'conductor_licencia', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Categoría:', $this->v($flete, 'conductor_categoria', '-'));
            $this->SetY(max($yIzq, $yDer) + 2);
        } elseif ($tipo === 'TERCERIZADO') {
            $yIzq = $this->labelValue($margenIzq, $y0, 26, $mitad - 26, 'Transportista:', $this->v($flete, 'proveedor_nombres', '-'));
            $yIzq = $this->labelValue($margenIzq, $yIzq, 26, $mitad - 26, 'Documento:', $this->v($flete, 'proveedor_num_docu', '-'));
            $yIzq = $this->labelValue($margenIzq, $yIzq, 26, $mitad - 26, 'Vehículo:', $this->v($flete, 'vehiculo_tercerizado_descripcion', '-'));
            $yIzq = $this->labelValue($margenIzq, $yIzq, 26, $mitad - 26, 'Placa:', $this->v($flete, 'vehiculo_tercerizado_placa', '-'));
            $yDer = $this->labelValue($xDer, $y0, 24, $mitad - 24, 'Conductor:', $this->v($flete, 'conductor_tercerizado_nombres', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Documento:', $this->v($flete, 'conductor_tercerizado_num_docu', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Licencia:', $this->v($flete, 'conductor_tercerizado_licencia', '-'));
            $yDer = $this->labelValue($xDer, $yDer, 24, $mitad - 24, 'Categoría:', $this->v($flete, 'conductor_tercerizado_categoria', '-'));
            $this->SetY(max($yIzq, $yDer) + 2);
        } elseif ($tipo === 'MIXTO') {
            $y = $y0;
            $camposPropios = [
                'Vehículo propio' => $this->v($flete, 'vehiculo_descripcion', ''),
                'Placa propia' => $this->v($flete, 'vehiculo_placa', ''),
                'Conductor propio' => $this->v($flete, 'conductor_nombres', ''),
            ];
            $camposTerceros = [
                'Transportista tercero' => $this->v($flete, 'proveedor_nombres', ''),
                'Vehículo tercero' => $this->v($flete, 'vehiculo_tercerizado_descripcion', ''),
                'Placa tercero' => $this->v($flete, 'vehiculo_tercerizado_placa', ''),
                'Conductor tercero' => $this->v($flete, 'conductor_tercerizado_nombres', ''),
            ];
            foreach ($camposPropios as $etiqueta => $valor) {
                if ($valor) $y = $this->labelValue($margenIzq, $y, 32, $mitad - 32, $etiqueta . ':', $valor);
            }
            $yDer = $y0;
            foreach ($camposTerceros as $etiqueta => $valor) {
                if ($valor) $yDer = $this->labelValue($xDer, $yDer, 34, $mitad - 34, $etiqueta . ':', $valor);
            }
            if ($y === $y0 && $yDer === $y0) {
                $this->SetFont('helvetica', 'I', TEXT_SIZE_REDUCIDO_COT);
                $this->Cell($anchoUtil, 5, 'Sin datos de transporte registrados.', 0, 1, 'L');
                $y = $this->GetY();
            }
            $this->SetY(max($y, $yDer) + 2);
        } else {
            $this->SetFont('helvetica', 'I', TEXT_SIZE_REDUCIDO_COT);
            $this->Cell($anchoUtil, 5, 'Tipo de operación no especificado.', 0, 1, 'L');
            $this->Ln(1);
        }
    }

    // ================================================================
    // RESUMEN ECONÓMICO
    // ================================================================

    protected function dibujarResumenEconomico($flete)
    {
        $this->checkSpace(32);
        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $anchoCuadro = 75;
        $xCuadro = $margenIzq + $anchoUtil - $anchoCuadro;
        $y = $this->GetY();

        $incluyeIgv = $this->v($flete, 'incluye_igv', false);
        $incluyeIgvTexto = ($incluyeIgv === true || $incluyeIgv === 1 || $incluyeIgv === '1') ? 'Sí' : 'No';
        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $this->SetXY($xCuadro, $y);
        $this->Cell($anchoCuadro, 5, 'Incluye IGV: ' . $incluyeIgvTexto, 0, 1, 'R');
        $y = $this->GetY() + 1;

        $filaAlto = 6;
        $anchoEtiqueta = 40;
        $anchoValor = $anchoCuadro - $anchoEtiqueta;
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.2);

        $this->SetXY($xCuadro, $y);
        $this->SetFont('helvetica', '', TEXT_SIZE_NORMAL);
        $this->Cell($anchoEtiqueta, $filaAlto, 'Subtotal', 'B', 0, 'L');
        $this->Cell($anchoValor, $filaAlto, $this->money($this->v($flete, 'subtotal', 0)), 'B', 1, 'R');
        $y = $this->GetY();

        $this->SetXY($xCuadro, $y);
        $this->Cell($anchoEtiqueta, $filaAlto, 'IGV', 'B', 0, 'L');
        $this->Cell($anchoValor, $filaAlto, $this->money($this->v($flete, 'igv', 0)), 'B', 1, 'R');
        $y = $this->GetY();

        $altoTotal = 9;
        $this->SetXY($xCuadro, $y);
        $this->SetFillColor(COLOR_AZUL_R, COLOR_AZUL_G, COLOR_AZUL_B);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell($anchoEtiqueta, $altoTotal, ' TOTAL', 0, 0, 'L', true);
        $this->Cell($anchoValor, $altoTotal, $this->money($this->v($flete, 'total', 0)) . ' ', 0, 1, 'R', true);
        $this->SetTextColor(0, 0, 0);

        $total = $this->v($flete, 'total', 0);
        if ($total > 0) {
            $this->SetY($this->GetY() + 1);
            $this->SetFont('helvetica', '', 7.5);
            $this->SetX($xCuadro);
            $this->MultiCell($anchoCuadro, 3.5, 'SON: ' . numtoletras(number_format((float)$total, 2, '.', ''), $this->v($flete, 'codigo_moneda')), 0, 'R', false, 1, $xCuadro, $this->GetY());
        }
        $this->SetY($this->GetY() + 4);
    }

    // ================================================================
    // OBSERVACIONES
    // ================================================================

    protected function dibujarObservaciones($flete)
    {
        $observacion = trim((string)$this->v($flete, 'observacion', ''));
        if ($observacion === '') return;

        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $alturaTexto = $this->getStringHeight($anchoUtil - 4, $observacion);
        $this->checkSpace($alturaTexto + 12);
        $this->sectionTitle('Observaciones', 120, 120, 120);
        $y = $this->GetY();
        $this->SetDrawColor(200, 200, 200);
        $this->Rect($margenIzq, $y, $anchoUtil, $alturaTexto + 4, 'D');
        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $this->MultiCell($anchoUtil - 4, $alturaTexto, $observacion, 0, 'L', false, 1, $margenIzq + 2, $y + 2);
        $this->SetY($y + $alturaTexto + 4 + 3);
    }

    // ================================================================
    // FIRMAS
    // ================================================================

    protected function dibujarFirmas()
    {
        $this->checkSpace(34);
        $margenes = $this->getMarginsSafe();
        $margenIzq = $margenes['left'];
        $anchoUtil = $this->getPageWidth() - $margenIzq - $margenes['right'];
        $mitad = $anchoUtil / 2;
        $xDer = $margenIzq + $mitad;

        $this->Ln(6);
        $y = $this->GetY();
        $anchoFirma = $mitad - 6;

        $this->Line($margenIzq, $y, $margenIzq + $anchoFirma, $y);
        $this->Line($xDer, $y, $xDer + $anchoFirma, $y);

        $this->SetFont('helvetica', 'B', TEXT_SIZE_REDUCIDO_COT);
        $this->SetXY($margenIzq, $y + 1);
        $this->Cell($anchoFirma, 4, 'CONFORMIDAD DEL CLIENTE', 0, 1, 'C');
        $this->SetXY($xDer, $y + 1);
        $this->Cell($anchoFirma, 4, 'RESPONSABLE / EMPRESA', 0, 1, 'C');

        $this->SetFont('helvetica', '', TEXT_SIZE_REDUCIDO_COT);
        $this->SetXY($margenIzq, $y + 6);
        $this->Cell($anchoFirma, 5, 'Nombre: _______________________', 0, 1, 'L');
        $this->SetXY($margenIzq, $y + 11);
        $this->Cell($anchoFirma, 5, 'DNI: __________________________', 0, 1, 'L');
        $this->SetXY($margenIzq, $y + 16);
        $this->Cell($anchoFirma, 5, 'Firma: _________________________', 0, 1, 'L');

        $this->SetXY($xDer, $y + 6);
        $this->Cell($anchoFirma, 5, 'Nombre: _______________________', 0, 1, 'L');
        $this->SetXY($xDer, $y + 11);
        $this->Cell($anchoFirma, 5, 'Firma: _________________________', 0, 1, 'L');
    }

    // ================================================================
    // MÉTODO PRINCIPAL
    // ================================================================

    public function generarFlete()
    {
        $empresa = $this->getEmpresa();
        $flete = $this->getFlete();

        $this->SetCreator('Sistema de Transporte');
        $this->SetAuthor($this->v($empresa, 'razon_social', ''));
        $this->SetTitle('Flete N° ' . $this->v($flete, 'id_flete', ''));
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        $this->AddPage();
        $this->SetFont('helvetica', '', TEXT_SIZE_NORMAL);

        $this->dibujarCabecera($empresa, $flete);
        $this->dibujarDatosCliente($flete);
        $this->dibujarDatosServicio($flete);
        $this->dibujarDatosTransporte($flete);
        $this->dibujarResumenEconomico($flete);
        $this->dibujarObservaciones($flete);
        $this->dibujarFirmas();

        $this->IncludeJS("print(false);");
    }
}

// ============================================================================
// GENERACIÓN DEL PDF (USA $this->data DIRECTAMENTE)
// ============================================================================

// Verificar que los datos existan en el ámbito de la vista
if (isset($this->data) && is_array($this->data)) {
    // Limpiar buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    try {
        $pdf = new TCPDF_FletePDF($this->data);
        $pdf->generarFlete();

        $idFlete = $this->data['data']['flete']['id_flete'] ?? 'orden';
        $pdf->Output('flete_' . $idFlete . '.pdf', 'I');
        exit;
    } catch (Exception $e) {
        echo "Error al generar el PDF: " . $e->getMessage();
        exit;
    }
} else {
    echo "Error: No se recibieron datos para generar el flete.";
    exit;
}
