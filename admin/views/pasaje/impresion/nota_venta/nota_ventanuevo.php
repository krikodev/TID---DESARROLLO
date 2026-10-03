<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start(); // Capturar cualquier salida
require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("MAX_WIDTH_LOGO", 40); // Aumentado para un logo más grande
define("MAX_HEIGHT_LOGO", 30); // Aumentado para dar flexibilidad

// Función auxiliar para limpiar y cargar imágenes (PNG o JPG)
function cleanImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        error_log("Imagen no encontrada o no legible: $filePath");
        return false;
    }

    // Obtener información de la imagen
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo === false) {
        error_log("No se pudo obtener información de la imagen: $filePath");
        return false;
    }

    $mime = $imageInfo['mime'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $imgWidth = $imageInfo[0];
    $imgHeight = $imageInfo[1];

    // Calcular escala para mantener proporciones
    $scale = min(MAX_WIDTH_LOGO / $imgWidth, MAX_HEIGHT_LOGO / $imgHeight);
    $finalWidth = $imgWidth * $scale;
    $finalHeight = $imgHeight * $scale;

    // Asegurar que el ancho no exceda el ancho útil del ticket (76 mm)
    if ($finalWidth > 76) {
        $finalWidth = 76;
        $finalHeight = $finalWidth * ($imgHeight / $imgWidth);
    }

    // Procesar según el tipo de imagen
    if ($mime === 'image/png' || $extension === 'png') {
        // Cargar PNG con soporte para transparencia
        $img = @imagecreatefrompng($filePath);
        if (!$img) {
            error_log("Error al cargar PNG: $filePath");
            return false;
        }

        // Crear nueva imagen con soporte para transparencia
        $new = imagecreatetruecolor($imgWidth, $imgHeight);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
        imagefilledrectangle($new, 0, 0, $imgWidth, $imgHeight, $transparent);

        // Copiar el contenido original
        imagecopy($new, $img, 0, 0, 0, 0, $imgWidth, $imgHeight);

        // Capturar como string
        ob_start();
        imagepng($new, null, 0);
        $data = ob_get_clean();

        imagedestroy($img);
        imagedestroy($new);

        return ['data' => $data, 'type' => 'PNG', 'width' => $finalWidth, 'height' => $finalHeight];
    } elseif ($mime === 'image/jpeg' || $extension === 'jpg' || $extension === 'jpeg') {
        // JPGs no necesitan reprocesamiento
        return ['data' => $filePath, 'type' => 'JPG', 'width' => $finalWidth, 'height' => $finalHeight];
    } else {
        error_log("Formato de imagen no soportado: $mime ($filePath)");
        return false;
    }
}

class TICKET_TCPDF extends TCPDF
{
    public function __construct()
    {
        parent::__construct('P', 'mm', array(80, 350), true, 'UTF-8');
        $this->SetMargins(2, 0, 0);
        $this->SetAutoPageBreak(true, 0);
        $this->SetPrintHeader(false);
        $this->SetPrintFooter(false);
        $this->SetFont('helvetica', '', 8); // Fuente por defecto
    }

    private function truncarTexto($texto, $anchoMm)
    {
        if (empty($texto)) {
            return '';
        }

        // Si ya cabe completo, se devuelve tal cual
        if ($this->GetStringWidth($texto) <= $anchoMm) {
            return $texto;
        }

        $sufijo = '...';
        $truncado = $texto;

        // Va quitando caracteres del final hasta que quepa junto con "..."
        while ($this->GetStringWidth($truncado . $sufijo) > $anchoMm && mb_strlen($truncado) > 0) {
            $truncado = mb_substr($truncado, 0, -1);
        }

        return $truncado . $sufijo;
    }

    function f_12_horas($hora_24)
    {
        $hora = strtotime($hora_24);
        return date("g:i A", $hora);
    }


    public function generateTicket($data)
    {
        if (!is_array($data) || empty($data)) {
            error_log("Datos inválidos para generateTicket");
            return;
        }

        $this->AddPage();

        // CABECERA
        $logoPath = $data["EMISOR"]["empresa_logo"] ?? '';
        if ($logoPath && file_exists($logoPath) && is_readable($logoPath)) {
            // Obtener dimensiones originales de la imagen
            $imageInfo = @getimagesize($logoPath);
            if ($imageInfo === false) {
                error_log("No se pudo obtener información de la imagen: $logoPath");
                $this->Ln(5);
                return;
            }

            $imgWidth = $imageInfo[0];
            $imgHeight = $imageInfo[1];
            $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $type = ($extension === 'jpg' || $extension === 'jpeg') ? 'JPG' : 'PNG';

            // Calcular escala para mantener proporciones
            $scale = min(MAX_WIDTH_LOGO / $imgWidth, MAX_HEIGHT_LOGO / $imgHeight);
            $finalWidth = $imgWidth * $scale;
            $finalHeight = $imgHeight * $scale;

            // Asegurar que el ancho no exceda el ancho útil del ticket (76 mm)
            if ($finalWidth > 76) {
                $finalWidth = 76;
                $finalHeight = $finalWidth * ($imgHeight / $imgWidth);
            }

            // Centrar la imagen
            $x = ($this->getPageWidth() - $finalWidth) / 2;

            // Insertar la imagen
            try {
                $this->Image($logoPath, $x, 5, $finalWidth, $finalHeight, $type);
                $this->Ln($finalHeight + 2); // Aumentado el espacio después del logo
            } catch (Exception $e) {
                error_log("Error al cargar la imagen: " . $e->getMessage());
                $this->Ln(5);
            }
        } else {
            error_log("Ruta del logo no válida o no encontrada: $logoPath");
            $this->Ln(5); // Espacio si no hay logo
        }

        // Datos de la empresa
        $this->SetFont('helvetica', '', 9);
        $this->MultiCell(76, 5, "", 0, 'C');
        $this->SetFont('helvetica', 'B', 9);
        $this->MultiCell(76, 5, $data["EMISOR"]["empresa_razon_social"], 0, 'C');
        $this->Cell(76, 4, "RUC: " . $data["EMISOR"]["empresa_ruc"], 0, 1, 'C');
        $this->Ln(0.5);

        // NUEVA LÍNEA: Teléfono de la empresa y número de terminal
        $this->SetFont('helvetica', '', 7);
        $telefono_empresa = isset($data["EMISOR"]["empresa_telefono"]) ? $data["EMISOR"]["empresa_telefono"] : '---';
        $terminal_celular = isset($data["EMISOR"]["terminal_celular"]) ? $data["EMISOR"]["terminal_celular"] : '---';
        $this->Cell(76, 4, "TELÉFONO: " . $telefono_empresa . " - " . $terminal_celular, 0, 1, 'C');

        // Dirección fiscal
        $this->SetFont('helvetica', '', 6);
        $this->MultiCell(76, 3.5, 'DOM. FISCAL: ' . $data['EMISOR']['direccion'], 0, 'C');
        $this->Ln(1);

        //Frase
        if ($data['EMISOR']['frase_empresa']) {
            $this->SetFont('helvetica', '', 6);
            $frase_empresa = html_entity_decode($data['EMISOR']['frase_empresa'], ENT_QUOTES, 'UTF-8');
            $this->writeHTMLCell(76, 3.5, '', '', $frase_empresa, 0, 1, 0, true, 'C', true);
            $this->Ln(1);
        }

        // Terminales
        foreach ($data["TERMINALES"] as $terminal) {
            $this->MultiCell(76, 3.5, $terminal['nombre'] . ': ' . $terminal['direccion_fiscal'] . '- Cel: ' . $terminal['celular'], 0, 'C');
            $this->Ln(1);
        }

        $this->Ln(-4);
        // Línea separadora
        $this->Cell(76, 0, '', 'B');
        $this->Ln(4);

        // Tipo de comprobante y serie
        $this->SetFont('helvetica', '', 11);
        $this->Cell(76, 4, 'NOTA DE VENTA', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('helvetica', 'B', 11);
        $serie_completa = $data["CABECERA"]["serie"] . " - " . str_pad($data["CABECERA"]["correlativo"], 8, "0", STR_PAD_LEFT);
        $this->Cell(76, 4, $serie_completa, 0, 1, 'C');
        $this->Ln(2);
        $this->Cell(76, 0, '', 'T');
        $this->Ln(2);

        // Datos del cliente - Nueva estructura
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(15, 4, 'CLIENTE:', 0, 0);
        $this->SetFont('helvetica', '', 8);
        $nombre_completo = $data["CLIENTE"]["cliente_nombres"] . " " . $data["CLIENTE"]["cliente_apellidos"];
        $this->MultiCell(69, 4, $nombre_completo, 0, 1);

        // Segunda línea: DNI, CEL y Edad
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(8, 4, 'DNI:', 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(15, 4, $data["CLIENTE"]["cliente_num_docu"], 0, 0);

        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(7, 4, 'CEL:', 0, 0);
        $this->SetFont('helvetica', '', 8);
        $cliente_celular = isset($data["CLIENTE"]["cliente_celular"]) ? $data["CLIENTE"]["cliente_celular"] : '---';
        $this->Cell(18, 4, $cliente_celular, 0, 0);

        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(8, 4, 'Edad:', 0, 0);
        $this->SetFont('helvetica', '', 8);
        // Mejorar cálculo de edad
        $edad_cliente = '---';
        if (!empty($data["CLIENTE"]["cliente_fecha_nacimiento"]) && $data["CLIENTE"]["cliente_fecha_nacimiento"] != "0000-00-00") {
            $fecha_nac = new DateTime($data["CLIENTE"]["cliente_fecha_nacimiento"]);
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha_nac)->y;
            $edad_cliente = $edad > 0 ? $edad : '---';
        }
        $this->Cell(12, 4, $edad_cliente, 0, 1);

        //Tercera linea nacionalidad
        $this->SetFont('helvetica', '', 8);
        $nacionalidad = isset($data["CLIENTE"]["cliente_nacionalidad"]) ? $data["CLIENTE"]["cliente_nacionalidad"] : '---';
        $this->Cell(21, 4, $nacionalidad, 0, 1);


        $this->Ln(2);
        $this->Cell(76, 0, '', 'T');
        $this->Ln(2);

        // Información del servicio
        $this->SetFont('helvetica', '', 9);
        $this->Cell(28, 4, 'Unidad medida:', 0, 0);
        $this->Cell(18.5, 4, 'Servicio', 0, 0);
        $this->Cell(19.5, 4, 'Cantidad:', 0, 0);
        $this->Cell(10, 4, '1', 0, 1);

        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(76, 4, 'SERVICIO DE TRANSPORTE DE PASAJEROS', 0, 1, 'C');

        // ORIGEN Y DESTINO con Asiento/Piso a la derecha (VERSIÓN CON MULTICELL EN AMBAS COLUMNAS)
        $this->SetFont('helvetica', 'B', 8);

        // Guardar la posición Y inicial
        $startY = $this->GetY();
        $maxHeight = 0; // Para tracking de la altura máxima

        // --- COLUMNA IZQUIERDA: ORIGEN, DESTINO y FECHA/HORA (con MultiCell) ---
        $this->SetXY(2, $startY); // X=2 es el margen izquierdo

        // ORIGEN con MultiCell
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(14, 4, 'ORIGEN:', 0, 0);
        $this->SetFont('helvetica', 'B', 10);
        $origen_lines = $this->MultiCell(30, 4, strtoupper($data["CABECERA"]["terminal_origen"]), 0, 'L', 0, 1);

        // Guardar posición después de ORIGEN
        $afterOrigenY = $this->GetY();

        // DESTINO con MultiCell
        $this->SetX(2); // Volver al margen izquierdo
        $this->Ln(0.5);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(14, 4, 'DESTINO:', 0, 0);
        $this->SetFont('helvetica', 'B', 12);
        $destino_lines = $this->MultiCell(30, 4, strtoupper($data["CABECERA"]["terminal_destino"]), 0, 'L', 0, 1);

        // Guardar posición después de DESTINO
        $afterDestinoY = $this->GetY();

        // FECHA/HORA - Ahora como parte de la columna izquierda
        $this->SetX(2); // Volver al margen izquierdo
        $this->Ln(0.5);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(20, 4, 'FECHA/HORA:', 0, 0);
        $this->SetFont('helvetica', 'B', 11); // Fuente un poco más pequeña para la fecha
        $fecha = date("d-m-Y", strtotime($data["CABECERA"]["programacion_fecha_salida"]));
        $hora = $this->f_12_horas($data["CABECERA"]["programacion_hora_salida"]);
        $fecha_hora_completa = $fecha . " " . $hora;
        $fecha_hora_lines = $this->MultiCell(25, 4, $fecha_hora_completa, 0, 'L', 0, 1);

        // Guardar la Y final de la columna izquierda
        $leftColumnY = $this->GetY();

        // --- COLUMNA DERECHA: Asiento/Piso (más grande) ---
        $this->SetXY(43, $startY); // Posicionar en columna derecha

        // Etiqueta Asiento/Piso
        $this->SetFont('helvetica', 'B', 8);
        $this->MultiCell(29, 5, 'ASIENTO/PISO', 0, 'C', 0, 1);

        // Espacio entre etiqueta y contenido
        $this->Ln(-3);

        // Obtener valores
        $num_asiento = isset($data["ITEMS"][0]["num_asiento"]) ? (string) $data["ITEMS"][0]["num_asiento"] : '---';
        $piso = isset($data["ITEMS"][0]["piso"]) ? trim($data["ITEMS"][0]["piso"]) : '';

        // --- UNA SOLA LÍNEA: Asiento grande + guión + piso pequeño ---
        $this->SetFont('helvetica', 'B', 36); // Tamaño base para el asiento

        // Ajustar tamaño según dígitos del asiento
        if (strlen($num_asiento) <= 1) {
            $this->SetFont('helvetica', 'B', 38); // Más grande para 1 dígito
        } elseif (strlen($num_asiento) >= 3) {
            $this->SetFont('helvetica', 'B', 30); // Un poco más pequeño para 3+ dígitos
        }

        // Ancho del asiento
        $asiento_width = $this->GetStringWidth($num_asiento);

        // Configurar fuente para el guión y piso
        $this->SetFont('helvetica', 'B', 14); // Fuente mediana para guión y piso
        $guion_width = $this->GetStringWidth(' - ');
        $piso_width = $this->GetStringWidth($piso);

        // Ancho total de la línea completa
        $total_width = $asiento_width + $guion_width + $piso_width;
        $availableWidth = 29; // Ancho de columna derecha

        // Calcular posición X para centrar TODO el conjunto
        $startX = 43 + (($availableWidth - $total_width) / 2);

        // Posicionar para el asiento
        $this->SetX($startX);
        $this->SetFont('helvetica', 'B', 36); // Restaurar tamaño grande para asiento
        if (strlen($num_asiento) <= 1) {
            $this->SetFont('helvetica', 'B', 38);
        } elseif (strlen($num_asiento) >= 3) {
            $this->SetFont('helvetica', 'B', 30);
        }

        // Dibujar asiento
        $this->Cell($asiento_width, 10, $num_asiento, 0, 0, 'L');
        // Dibujar guión
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell($guion_width, 10, ' - ', 0, 0, 'C');

        // Dibujar piso
        $this->SetFont('helvetica', 'B', 14);
        if (empty($piso)) {
            $piso = '?';
        }
        $this->Cell($piso_width, 10, $piso, 0, 1, 'L'); // 1 para salto de línea después de esta celda

        // Si hay más de un asiento (múltiples pasajeros), mostrar indicador
        if (count($data["ITEMS"]) > 1) {
            $this->Ln(1);
            $this->SetFont('helvetica', 'B', 8);
            $this->SetX(43);
            $this->Cell(29, 4, '(+' . (count($data["ITEMS"]) - 1) . ' asiento)', 0, 1, 'C');
        }

        // Guardar Y de columna derecha
        $rightColumnY = $this->GetY();

        // Establecer la Y final como la mayor entre ambas columnas
        $this->SetY(max($leftColumnY, $rightColumnY));

        // Espacio después de las columnas
        $this->Ln(1);

        $this->SetFont('helvetica', '', 9);
        $this->Cell(19, 4, 'IMPORTE:', 0, 0);
        $this->Cell(18, 4, 'S/. ' . $data["CABECERA"]["total"], 0, 1);

        // Monto en letras
        $this->SetFont('helvetica', '', 9);
        $this->Cell(10, 4, 'SON:', 0, 0);
        $this->SetFont('helvetica', 'B', 9);
        $this->MultiCell(66, 4, numtoletras($data["CABECERA"]["total"]), 0, 'L');
        $this->Ln(1);

        // Información de pago y vendedor
        $this->SetFont('helvetica', '', 7);
        $this->Cell(21, 4, 'Forma de pago:', 0, 0);
        $this->Cell(23, 4, strtoupper($data["CABECERA"]["forma_pago"]), 0, 1);

        $this->Cell(21, 4, 'Metodo de pago:', 0, 0);
        $this->Cell(23, 4, strtoupper($data["CABECERA"]["medio_pago"]), 0, 1);

        $this->Cell(17, 4, 'F.H Emision:', 0, 0);
        $this->Cell(27, 4, date("d-m-Y H:i A", strtotime($data["CABECERA"]["fecha_emision"])), 0, 1);

        $this->Cell(17, 4, 'Vendedor(a):', 0, 0);
        $this->MultiCell(27, 4, $data["CABECERA"]["vendedor_nombres"], 0, 1);

        $empresa_cuenta_raw = isset($data["EMISOR"]["empresa_cuenta_bancaria"])
            ? $data["EMISOR"]["empresa_cuenta_bancaria"]
            : '';

        // Verificar si hay contenido HTML REAL (no solo etiquetas vacías o espacios)
        $contenido_sin_html = trim(strip_tags($empresa_cuenta_raw));
        $hay_contenido_real = !empty($contenido_sin_html);

        if ($hay_contenido_real) {
            // Decodificar entidades HTML
            $cuentas_html = html_entity_decode($empresa_cuenta_raw, ENT_QUOTES, 'UTF-8');

            // Verificar si tiene formato HTML
            $tiene_html = ($cuentas_html != strip_tags($cuentas_html));

            if ($tiene_html) {
                // Modo HTML completo (negritas, colores, tamaños, etc.)
                $current_x = $this->GetX();
                $current_y = $this->GetY();
                $this->SetFont('helvetica', '', 7);
                // Usar writeHTMLCell para renderizar HTML en el ancho disponible (aproximadamente 26mm)
                $this->writeHTMLCell(0, 0, $current_x, $current_y, $cuentas_html, 0, 1, 0, true, 'L', true);
            } else {
                // Texto plano, usar MultiCell
                $this->SetFont('helvetica', '', 7);
                $this->MultiCell(0, 4, $cuentas_html, 0, 'L', 0, 1);
            }
            // Restaurar fuente por defecto
            $this->SetFont('helvetica', '', 7);
        }

        // Línea separadora
        $this->Ln(1);
        $this->Cell(76, 0, '', 'T');
        $this->Ln(1);

        $this->SetFont('helvetica', '', 9);
        $terminos_pasaje = html_entity_decode($data['EMISOR']['terminos_condiciones_pasaje'], ENT_QUOTES, 'UTF-8');
        $this->writeHTMLCell(76, 3.5, '', '', $terminos_pasaje, 0, 1, 0, true, 'L', true);
        // Información del SOAT y póliza
        $this->Ln(1);
        $this->SetFont('helvetica', '', 8);

        // Verificar si existe información del SOAT
        if (!empty($data['CABECERA']['soat'])) {
            // Mostrar información del SOAT - TEXTO COMPLETO CON LA COMPAÑÍA
            $this->MultiCell(76, 4, 'Servicio cubierto por el Seguro de la compañía: ' . $data['CABECERA']['soat'], 0, 'L');
        }

        // Mostrar número de póliza si existe
        if (!empty($data['CABECERA']['num_poliza'])) {

            $this->MultiCell(76, 4, 'Póliza N°: ' . $data['CABECERA']['num_poliza'], 0, 'L');
        }
        $this->Ln(1);

        $this->Cell(76, 4, URL_PAGE_WEB_COMPROBANTES, 0, 1, 'L');
        $this->Ln(1);

        $this->SetLineStyle([
            'width' => 0.1,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => '1,1',
            'color' => [0, 0, 0]
        ]);
        $y = $this->GetY();
        $this->Line($this->GetMargins()['left'], $y, $this->getPageWidth() - $this->GetMargins()['right'], $y);
        $this->Ln(1);
        //Ticket
        //Utilizando Row3
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(76, 4, 'CORTE EN LA LINEA', 0, 1, 'C');
        $this->Ln(1);

        // ----------------PRIMERA OPCION DE TAQUITO------------
        // $this->SetFont('helvetica', 'B', 8);
        // $this->Cell(6, 4, 'DE: ', 0, 0);
        // $this->SetFont('helvetica', '', 8);
        // $this->MultiCell(
        //     17,
        //     4,
        //     $data["CABECERA"]["terminal_origen"],
        //     0,
        //     'L',
        //     false,
        //     0,        // $ln = 0 -> se queda a la derecha, misma línea
        //     '',
        //     '',
        //     true,
        //     0,
        //     false,
        //     true,     // autopadding
        //     4,        // $maxh -> fuerza altura máxima de 1 línea
        //     'M',
        //     true      // $fitcell -> auto-ajusta el font size para que quepa en 1 línea
        // );

        // $this->SetFont('helvetica', 'B', 8);
        // $this->Cell(5, 4, 'A: ', 0, 0);
        // $this->SetFont('helvetica', '', 8);
        // $this->MultiCell(
        //     17,
        //     4,
        //     $data["CABECERA"]["terminal_destino"],
        //     0,
        //     'L',
        //     false,
        //     0,
        //     '',
        //     '',
        //     true,
        //     0,
        //     false,
        //     true,
        //     4,
        //     'M',
        //     true
        // );

        // // Placa: toma el resto del ancho disponible y cierra la línea
        // $this->SetFont('helvetica', '', 8);
        // $this->Cell(0, 4, $data["CABECERA"]["vehiculo_placa"], 0, 1, 'R');

        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(6, 4, 'DE: ', 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(25, 4, $this->truncarTexto($data["CABECERA"]["terminal_origen"], 23), 0, 0);

        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(5, 4, 'A: ', 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(25, 4, $this->truncarTexto($data["CABECERA"]["terminal_destino"], 23), 0, 0);

        $this->Cell(20, 4, $data["CABECERA"]["vehiculo_placa"], 0, 1, 'L');


        //Utilizando Cell (antes Row3)
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(45, 4, $data["CABECERA"]["serie"] . " - " . substr("00000000", 0, -strlen($data["CABECERA"]["correlativo"])) . $data["CABECERA"]["correlativo"], 0, 0, 'L');
        $this->Cell(7, 4, 'S/', 0, 0, 'L');
        $this->Cell(24, 4, $data["CABECERA"]["op_exonerada"], 0, 1, 'R');
        $this->Ln(0.1);

        //Utilizando Cell (antes Row4)
        $this->SetFont('helvetica', '', 7.5);
        $this->Cell(18, 4, 'PASAJERO:', 0, 0, 'L');
        $this->Cell(58, 4, $data["CLIENTE"]["cliente_nombres"] . " " . $data["CLIENTE"]["cliente_apellidos"], 0, 1, 'L');
        $this->Ln(0.1);

        $this->Cell(18, 4, 'ASIENTO:', 0, 0, 'L');
        $this->Cell(58, 4, trim($data["ITEMS"][0]["num_asiento"]) . ' - ' . trim($data["ITEMS"][0]["piso"]), 0, 1, 'L');
        $this->Ln(0.1);

        $this->Cell(18, 4, 'Fecha-Viaje:', 0, 0, 'L');
        $this->Cell(20, 4, date("d-m-Y", strtotime($data["CABECERA"]["programacion_fecha_salida"])), 0, 0, 'L');
        $this->Cell(20, 4, 'Hora-Viaje:', 0, 0, 'L');
        $this->Cell(18, 4, $this->f_12_horas($data["CABECERA"]["programacion_hora_salida"]), 0, 1, 'L');
        $this->Ln(0.1);

        $this->Cell(18, 4, 'F.H emision:', 0, 0, 'L');
        $this->Cell(30, 4, date('d-m-Y H:i A', strtotime($data["CABECERA"]["fecha_emision"])), 0, 1, 'L');
        $this->Ln(0.1);

        $this->Cell(18, 4, 'Usuario:', 0, 0, 'L');
        $this->Cell(58, 4, $data["CABECERA"]["vendedor_nombres"] . " " . $data["CABECERA"]["vendedor_apellidos"], 0, 1, 'L');
        $this->Ln(0.1);

        // Generar el PDF
        ob_clean(); // Limpiar cualquier salida previa
        return $this->Output('ticket.pdf', 'I');
    }
}

$ticket = new TICKET_TCPDF();
$ticket->generateTicket($this->data);
