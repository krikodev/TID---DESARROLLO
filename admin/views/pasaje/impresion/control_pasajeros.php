<?php

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Lima');

require_once('public/plugins/tc/tcpdf.php');

class TCPDF_A4_ControlPasajeros extends TCPDF
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

    /**
     * Método personalizado para calcular líneas sin usar getNumLines
     */
    function calcularLineas($texto, $ancho, $tamano = 8)
    {
        if (empty($texto))
            return 1;

        $this->SetFont('helvetica', '', $tamano);
        $anchoTexto = $this->GetStringWidth($texto);
        $anchoDisponible = $ancho - 4; // Restamos márgenes

        if ($anchoDisponible <= 0)
            return 1;

        return max(1, ceil($anchoTexto / $anchoDisponible));
    }
}

// Crear PDF A4
$pdf = new TCPDF_A4_ControlPasajeros();
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->data = $this->data; // Pasar datos a la clase

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// ========== ENCABEZADO CON LOGO ==========
$headerY = 10;
$columnaIzquierda = 10;

// Logo - ajustable a diferentes formatos
$logoExiste = false;
$logoWidth = 0;
$logoHeight = 0;

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
        $maxHeight = 20; // Altura máxima del logo
        $calculatedWidth = $maxHeight * $aspectRatio;

        // Limitar el ancho máximo
        $logoWidth = min($calculatedWidth, 40); // Máximo 40mm de ancho
        $logoHeight = $maxHeight;

        // Si es una imagen muy ancha, ajustar proporcionalmente
        if ($aspectRatio > 2) { // Si es muy ancha (rectangular horizontal)
            $logoWidth = 30;
            $logoHeight = 30 / $aspectRatio;
        }

        $pdf->Image($pdf->data['emisor']["logo"], $columnaIzquierda, $headerY, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0);
        $logoExiste = true;
    }
    restore_error_handler();
}

// ========== TÍTULO ==========
if ($logoExiste) {
    // El título va a la derecha del logo
    $pdf->SetXY($columnaIzquierda + $logoWidth + 13, $headerY);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'CONTROL DE ABORDAJE DE PASAJEROS', 0, 1, 'L');
} else {
    // Si no hay logo, centrar
    $pdf->SetXY($columnaIzquierda + $logoWidth + 13, $headerY);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'CONTROL DE ABORDAJE DE PASAJEROS', 0, 1, 'C');
}
$pdf->Ln(3);

// ========== DATOS DE LA PROGRAMACIÓN (DISEÑO COMPACTO CON CUADRO) ==========
$pdf->SetFont('helvetica', '', 8);

// Configuración del cuadro - AHORA A LA DERECHA DEL LOGO
$margenIzquierdo = 10;
if ($logoExiste) {
    // Si hay logo, el cuadro comienza después del logo + un margen
    $margenIzquierdo = $columnaIzquierda + $logoWidth + 5;
    // El ancho del cuadro será el espacio restante hasta el margen derecho
    $anchoCuadro = 190 - ($logoWidth + 5); // 190 es ancho total A4 menos márgenes
} else {
    $margenIzquierdo = 10;
    $anchoCuadro = 190;
}

$margenSuperior = $pdf->GetY();
$padding = 2; // Espacio interior del cuadro

// Guardar posición inicial para dibujar el cuadro después
$yInicio = $margenSuperior;

// Definir anchos para el diseño de 3 líneas (ajustados al nuevo ancho)
$anchoEtiqueta = 28; // Reducido para adaptarse al espacio
$anchoDato = 30;
$anchoSeparador = 2;

// PRIMERA LÍNEA: FECHA + HORA + VEHÍCULO (adaptado al ancho disponible)
// Posicionar dentro del cuadro (con padding)
$pdf->SetXY($margenIzquierdo + $padding, $margenSuperior + $padding);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(20, 4, 'FECHA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$fecha = date('d/m/Y', strtotime($pdf->data['programacion']['fecha_salida']));
$pdf->Cell(23, 4, $fecha, 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(12, 4, 'HORA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$hora = date('H:i', strtotime($pdf->data['programacion']['hora_salida']));
$pdf->Cell(13, 4, $hora, 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(17, 4, 'VEHÍCULO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$vehiculo = $pdf->data['programacion']['vehiculo_placa'] . ' - ' . $pdf->data['programacion']['vehiculo_marca'];

$pdf->MultiCell(0, 4, $vehiculo, 0, 'L');

// SEGUNDA LÍNEA: ORIGEN y DESTINO
// Obtener la posición Y actual después de la primera línea
$yActual = $pdf->GetY();

$pdf->SetXY($margenIzquierdo + $padding, $yActual);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(20, 4, 'ORIGEN:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$origen = $pdf->data['programacion']['terminal_origen'];
// Calcular ancho para origen (aproximadamente 40% del espacio disponible)
$anchoOrigen = ($anchoCuadro - ($padding * 2) - 20 - 30) * 0.4;
$pdf->MultiCell($anchoOrigen, 4, $origen, 0, 'L');

// Posicionar para DESTINO
$yDestino = $pdf->GetY();
$pdf->SetXY($margenIzquierdo + $padding + 20 + $anchoOrigen + 10, $yActual);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(16, 4, 'DESTINO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$destino = $pdf->data['programacion']['terminal_destino'];
$anchoDestino = $anchoCuadro - ($padding * 2) - 20 - $anchoOrigen - 10 - 22 - 5;
$pdf->MultiCell($anchoDestino, 4, $destino, 0, 'L');

// Ajustar la Y actual para la tercera línea (usar la Y más alta entre origen y destino)
$yActual = max($yDestino, $pdf->GetY());

// TERCERA LÍNEA: CONDUCTOR y LICENCIA
$pdf->SetXY($margenIzquierdo + $padding, $yActual);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(20, 4, 'CONDUCTOR:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$conductor_nombre = $pdf->data['programacion']['conductor_nombre'] ?: 'NO ASIGNADO';
$pdf->Cell(70, 4, $conductor_nombre, 0, 0, 'L');

$pdf->Cell($anchoSeparador, 4, '', 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(15, 4, 'LICENCIA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$licencia = $pdf->data['programacion']['conductor_licencia'] ?: 'NO REGISTRA';
$anchoLicencia = $anchoCuadro - ($padding * 2) - 20 - 50 - $anchoSeparador - 20 - 5;
$pdf->Cell($anchoLicencia, 4, $licencia, 0, 1, 'L');

// Obtener la Y final después de todas las líneas
$yFinal = $pdf->GetY() + $padding;

// DIBUJAR EL CUADRO (con borde más grueso)
$pdf->SetLineWidth(0.3); // Línea más gruesa para el cuadro
$pdf->Rect($margenIzquierdo, $yInicio, $anchoCuadro, $yFinal - $yInicio);

// Establecer la posición Y después del cuadro con un pequeño espacio
$pdf->SetY($yFinal + 2);

// LÍNEA DIVISORA MÁS GRUESA
$pdf->SetLineWidth(0.5); // Línea más gruesa (0.5mm)
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(0.2); // Restaurar al valor por defecto
$pdf->Ln(2);

// ========== TABLA DE ASIENTOS (TODOS LOS ASIENTOS DEL VEHÍCULO) ==========
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 8, 'DISTRIBUCIÓN DE ASIENTOS', 0, 1, 'C');
$pdf->Ln(2);

// Configuración de los cuadros de asientos
$anchoPagina = 190;
$margenEntreAsientos = 3;
$anchoAsiento = ($anchoPagina - (3 * $margenEntreAsientos)) / 4;

$altoBaseAsiento = 12;
$posXInicial = 10;
$posYInicial = $pdf->GetY();
$margenInferior = 20; // Margen inferior de seguridad

if (empty($pdf->data['asientos'])) {
    $pdf->Cell(190, 20, 'No hay asientos configurados para este vehículo', 1, 1, 'C');
} else {
    $asientos = $pdf->data['asientos'];

    // SOLO MOSTRAR: VENDIDO / DISPONIBLE
    foreach ($asientos as &$asiento) {

        $estado = strtoupper(trim($asiento['estado'] ?? ''));

        if ($estado === 'VENDIDO') {
            $asiento['estado'] = 'VENDIDO';
        } else {
            $asiento['estado'] = 'DISPONIBLE';
            $asiento['tiene_venta'] = false;
            $asiento['tiene_reserva'] = false;
            $asiento['venta'] = null;
            $asiento['reserva'] = null;
        }
    }

    unset($asiento);

    $totalAsientos = count($asientos);

    $indiceGlobal = 0;
    $paginaActual = 1;
    $continuar = true;

    // Procesar todos los asientos
    while ($indiceGlobal < $totalAsientos && $continuar) {

        // Reiniciar posición Y para esta página
        $posYInicial = $pdf->GetY();
        $espacioRestante = $pdf->getPageHeight() - $posYInicial - $margenInferior;

        error_log("Espacio disponible en página: {$espacioRestante}mm");

        // PRIMERA PASADA: Calcular alturas para los asientos que quepan en esta página
        $asientosPagina = [];
        $alturasAsientos = [];
        $i = 0;
        $alturaAcumulada = 0;
        $filasCompletas = 0;

        // Calcular cuántos asientos pueden entrar en esta página
        while (($indiceGlobal + $i) < $totalAsientos) {
            $asiento = $asientos[$indiceGlobal + $i];
            $tieneVenta = $asiento['tiene_venta'];
            $tieneReserva = $asiento['tiene_reserva'];

            // Calcular altura necesaria para este asiento
            $alturaNecesaria = $altoBaseAsiento;

            // PRIMERO verificar si es reserva
            if ($tieneReserva) {
                $alturaNecesaria += 5; // Información de reserva
            }
            // SOLO si no es reserva, verificar si es venta
            elseif ($tieneVenta) {
                $venta = $asiento['venta'];

                // DATOS DEL PASAJERO
                $nombreCompleto = trim(($venta['pasajero_apellidos'] ?? '') . ' ' . ($venta['pasajero_nombres'] ?? ''));
                $lineasNombre = $pdf->calcularLineas($nombreCompleto, $anchoAsiento - 4, 8);

                // Nombre del pasajero
                $alturaNecesaria += $lineasNombre * 3.9;

                $tieneNino = ($venta['c_nino'] == 1 && !empty($venta['nino_nombres']));

                // MENOR
                if ($tieneNino) {
                    $nombreNino = trim(($venta['nino_nombres'] ?? '') . ' ' . ($venta['nino_apellidos'] ?? ''));
                    $lineasNino = $pdf->calcularLineas('MENOR: ' . $nombreNino, $anchoAsiento - 4);
                    $alturaNecesaria += 0.5;
                    $alturaNecesaria += $lineasNino * 3.2;
                    if (!empty($venta['nino_dni'])) {
                        $alturaNecesaria += 3;
                    }
                }
                // VENDEDOR
                $alturaNecesaria += 3.5;
            }

            // Determinar la fila de este asiento
            $filaAsiento = floor($i / 4);

            // Actualizar altura máxima para esta fila
            if (!isset($alturasAsientos[$filaAsiento])) {
                $alturasAsientos[$filaAsiento] = $alturaNecesaria;
            } else {
                $alturasAsientos[$filaAsiento] = max($alturasAsientos[$filaAsiento], $alturaNecesaria);
            }

            // Calcular altura total de las filas hasta ahora
            $alturaFilasActuales = 0;
            $filasConsideradas = $filaAsiento + 1;
            for ($f = 0; $f < $filasConsideradas; $f++) {
                $alturaFilasActuales += $alturasAsientos[$f] + ($f > 0 ? 2 : 0);
            }

            // Verificar si cabe en la página
            if ($alturaFilasActuales <= $espacioRestante) {
                $asientosPagina[] = $asiento;
                $i++;
                error_log("Asiento {$asiento['num_asiento']} cabe en página. Altura necesaria: {$alturaNecesaria}mm");
            } else {
                // No cabe más en esta página
                error_log("Asiento {$asiento['num_asiento']} no cabe más en esta página. Altura necesaria fila {$filaAsiento}: {$alturasAsientos[$filaAsiento]}mm, Altura acumulada: {$alturaFilasActuales}mm, Espacio: {$espacioRestante}mm");

                // Eliminar la última fila si no cabe completa
                if ($filaAsiento > 0 && ($i % 4 != 0)) {
                    // La fila no está completa, eliminamos los asientos de esta fila
                    $asientosARemover = $i % 4;
                    for ($r = 0; $r < $asientosARemover; $r++) {
                        array_pop($asientosPagina);
                        $i--;
                    }
                    error_log("Eliminando {$asientosARemover} asientos de la fila incompleta");
                }
                break;
            }
        }

        $asientosEnEstaPagina = count($asientosPagina);
        error_log("Asientos que caben en página {$paginaActual}: {$asientosEnEstaPagina}");

        if ($asientosEnEstaPagina == 0) {
            error_log("ERROR: No cabe ningún asiento en la página, forzando salida");
            $continuar = false;
            break;
        }

        $filasEnPagina = ceil($asientosEnEstaPagina / 4);

        // SEGUNDA PASADA: Dibujar los asientos
        $asientosDibujados = 0;
        $vistosEnPagina = [];
        for ($i = 0; $i < $asientosEnEstaPagina; $i++) {
            $asiento = $asientosPagina[$i];
            $fila = floor($i / 4);
            $columna = $i % 4;

            $altoAsiento = $alturasAsientos[$fila];

            // Calcular posY para este asiento
            $posY = $posYInicial;
            for ($f = 0; $f < $fila; $f++) {
                $posY += $alturasAsientos[$f] + 2;
            }

            $posX = $posXInicial + ($columna * ($anchoAsiento + $margenEntreAsientos));

            $pdf->SetXY($posX, $posY);

            // Determinar colores según estado - PRIORIDAD: Reserva > Venta
            $tieneVenta = $asiento['tiene_venta'];
            $tieneReserva = $asiento['tiene_reserva'];
            $estado = $asiento['estado'];

            error_log("Dibujando asiento {$asiento['num_asiento']} - Estado: {$estado} - Venta: " . ($tieneVenta ? 'SÍ' : 'NO') . " - Reserva: " . ($tieneReserva ? 'SÍ' : 'NO'));

            // Dibujar borde del asiento - MÁS GRUESO
            $pdf->SetLineWidth(0.3); // Línea más gruesa para los bordes
            $pdf->RoundedRect($posX, $posY, $anchoAsiento, $altoAsiento, 2, '1111', 'D');

            // Fondo según estado - CORREGIDO: priorizar reserva sobre venta (PRIMERO RESERVA, LUEGO VENTA)
            if ($tieneReserva) {
                $pdf->SetFillColor(255, 255, 200); // Amarillo claro para reserva
            } elseif ($tieneVenta) {
                if ($estado == 'ANULADO') {
                    $pdf->SetFillColor(255, 200, 200); // Rojo claro para anulado
                } else {
                    $pdf->SetFillColor(230, 255, 230); // Verde claro para vendido
                }
            } else {
                $pdf->SetFillColor(245, 245, 250); // Gris claro para disponible
            }

            $pdf->SetLineWidth(0.3);
            $pdf->RoundedRect($posX, $posY, $anchoAsiento, $altoAsiento, 2, '1111', 'F');

            // Restaurar grosor de línea para el resto
            $pdf->SetFillColor(0, 0, 0);
            $pdf->SetLineWidth(0.3);
            $pdf->RoundedRect($posX, $posY, $anchoAsiento, $altoAsiento, 2, '1111', 'D');

            // ENCABEZADO DE LA TARJETA
            // Número de asiento
            $pdf->SetXY($posX + 2, $posY + 1);
            $pdf->SetFont('helvetica', 'B', 12);

            if ($tieneVenta) {
                $pdf->SetTextColor(0, 100, 0);
            } elseif ($tieneReserva) {
                $pdf->SetTextColor(160, 120, 0);
            } else {
                $pdf->SetTextColor(40, 80, 160);
            }

            $pdf->Cell(15, 6, $asiento['num_asiento'], 0, 0, 'L');


            if ($tieneVenta) {
                $venta = $asiento['venta'];
                $dni = trim($venta['pasajero_dni'] ?? '');
                $dni = $dni !== '' ? $dni : '-';
                $pdf->SetFont('helvetica', '', 6.5);
                $pdf->SetTextColor(60, 60, 60);
                $pdf->SetXY($posX + 25, $posY + 2);
                $pdf->Cell($anchoAsiento - 17, 3, 'DNI: ' . $dni, 0, 0, 'L');
            }

            // DATOS RÁPIDOS: DNI / CEL / EDAD
            if ($tieneVenta) {

                $venta = $asiento['venta'];

                // Datos del pasajero
                $dni = trim($venta['pasajero_dni'] ?? '');
                $celular = trim($venta['pasajero_celular'] ?? '');
                $edad = trim($venta['pasajero_edad'] ?? '');

                // Valores por defecto
                $dni = $dni !== '' ? $dni : '-';
                $celular = $celular !== '' ? $celular : '-';
                $edad = $edad !== '' ? $edad : '-';

                // CONFIGURACIÓN
                $pdf->SetTextColor(60, 60, 60);
                $pdf->SetFont('helvetica', '', 6.5);

                // CELULAR
                $pdf->SetXY($posX + 2, $posY + 6);
                $pdf->Cell(($anchoAsiento - 4) * 0.62, 3, 'CEL: ' . $celular, 0, 0, 'L');

                // EDAD
                $pdf->SetXY($posX + 2 + (($anchoAsiento - 4) * 0.67), $posY + 6);

                $pdf->Cell(($anchoAsiento - 4) * 0.38, 3, 'EDAD: ' . $edad, 0, 0, 'L');
            }

            // LÍNEA SEPARADORA
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);

            if ($tieneVenta) {
                $pdf->Line($posX + 2, $posY + 10, $posX + $anchoAsiento - 2, $posY + 10);
            } else {
                $pdf->Line($posX + 2, $posY + 8, $posX + $anchoAsiento - 2, $posY + 8);
            }

            // INFORMACIÓN DEBAJO DE LA LÍNEA
            $pdf->SetTextColor(0, 0, 0);
            // Comenzar SIEMPRE debajo de la línea separadora
            $yActual = $posY + 11;
            if ($tieneReserva) {
                // ===== ASIENTO RESERVADO - SIN DATOS DE VENTA =====
                $reserva = $asiento['reserva'];

                $pdf->SetXY($posX + 2, $yActual);
                $yActual = $pdf->GetY();

                $pdf->SetXY($posX + 2, $yActual);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell($anchoAsiento - 4, 4, 'RESERVA TEMPORAL', 0, 1, 'C');
            } elseif ($tieneVenta) {

                $venta = $asiento['venta'];

                $tieneNino = ($venta['c_nino'] == 1 && !empty($venta['nino_nombres']));

                // PASAJERO
                $nombreCompleto = trim(($venta['pasajero_apellidos'] ?? '') . ' ' . ($venta['pasajero_nombres'] ?? ''));
                $pdf->SetXY($posX + 2, $yActual);
                $pdf->SetFont('helvetica', 'B', 7.5);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->MultiCell($anchoAsiento - 4, 3.5, $nombreCompleto, 0, 'L', false, 1, '', '', true);
                $yActual = $pdf->GetY();

                // MENOR
                if ($tieneNino) {
                    $yActual += 1;
                    $nombreNino = trim(($venta['nino_nombres'] ?? '') . ' ' . ($venta['nino_apellidos'] ?? ''));
                    $pdf->SetXY($posX + 2, $yActual);
                    $pdf->SetFont('helvetica', 'B', 7);
                    $pdf->SetTextColor(0, 0, 0);

                    $pdf->MultiCell($anchoAsiento - 4, 3.5, 'MENOR: ' . $nombreNino, 0, 'L', false, 1, '', '', true);
                    $yActual = $pdf->GetY();

                    // DNI y edad del menor
                    if (!empty($venta['nino_dni'])) {
                        $dniNino = 'DNI: ' . $venta['nino_dni'];
                        if (!empty($venta['nino_edad'])) {
                            $dniNino .= ' | EDAD: ' . $venta['nino_edad'] . ' años';
                        }

                        $pdf->SetXY($posX + 2, $yActual);
                        $pdf->SetFont('helvetica', '', 6.8);
                        $pdf->Cell($anchoAsiento - 4, 3, $dniNino, 0, 1, 'L');
                        $yActual = $pdf->GetY();
                    }
                }

                // VENDEDOR
                $nombreVendedor = trim($venta['nombre_vendedor'] ?? 'VENTA WEB');
                if ($nombreVendedor !== 'VENTA WEB') {
                    $nombreVendedor = explode(' ', $nombreVendedor)[0];
                }
                $pdf->SetXY($posX + 2, $yActual);
                $pdf->SetFont('helvetica', '', 6);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell($anchoAsiento - 4, 3.5, 'VENDEDOR: ' . $nombreVendedor, 0, 'L', false, 1, '', '', true);
                
                $yActual = $pdf->GetY();
            } else {
                // Asiento disponible - OCUPA MÁS ESPACIO
                $espacioVertical = $altoAsiento - 10; // Usar el espacio disponible
                $pdf->SetXY($posX + 2, $posY + ($espacioVertical / 2));
            }
        }

        error_log("Asientos dibujados en página {$paginaActual}: {$asientosEnEstaPagina}");

        // Actualizar contadores para la siguiente página
        $indiceGlobal += $asientosEnEstaPagina;
        $paginaActual++;

        // Si aún quedan asientos, preparar nueva página
        if ($indiceGlobal < $totalAsientos) {
            $pdf->AddPage();

            error_log("Preparando página {$paginaActual} para asientos desde índice {$indiceGlobal} (Asiento: " . $asientos[$indiceGlobal]['num_asiento'] . ")");
        }
    }
}

// Generar PDF
$nombre_archivo = 'CONTROL_PASAJEROS_' . $pdf->data['programacion']['id_programacion'] . '_' . date('Ymd') . '.pdf';
ob_end_clean();
$pdf->Output($nombre_archivo, 'I');
