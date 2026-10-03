<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
require_once('public/plugins/tc/tcpdf.php');
require_once('public/plugins/print/num_letras.php');

define("MAX_WIDTH_LOGO", 40); // Aumentado para un logo más grande
define("MAX_HEIGHT_LOGO", 30); // Aumentado para dar flexibilidad

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
        if (!is_array($data) || empty($data) || !isset($data["EMISOR"]) || !is_array($data["EMISOR"])) {
            error_log("Datos inválidos para generateTicket");
            $this->AddPage();
            $this->Ln(5);
            return;
        }

        $this->AddPage();

        // CABECERA
        $logoPath = $data["EMISOR"]["empresa_logo"] ?? '';
        if ($logoPath && file_exists($logoPath) && is_readable($logoPath)) {
            // Obtener dimensiones originales de la imagen
            $imageInfo = @getimagesize($logoPath);
            if ($imageInfo === false) {
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
                $this->Ln(5);
            }
        } else {
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
        $this->Ln(3);

        // Tipo de comprobante y serie
        $this->SetFont('helvetica', '', 11);
        $this->Cell(76, 4, $data["CABECERA"]["tp_comprobante"], 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('helvetica', 'B', 11);
        $serie_completa = $data["CABECERA"]["serie"] . " - " . str_pad($data["CABECERA"]["correlativo"], 8, "0", STR_PAD_LEFT);
        $this->Cell(76, 4, $serie_completa, 0, 1, 'C');
        $this->Ln(2);
        $this->Cell(76, 0, '', 'T');
        $this->Ln(2);

        // Determinar el tipo de comprobante
        $tipo_comprobante = $data["CABECERA"]["tp_comprobante"] ?? '';
        $es_factura = (stripos($tipo_comprobante, 'FACTURA') !== false);
        $es_boleta = (stripos($tipo_comprobante, 'BOLETA') !== false);

        // Datos del cliente - Solo para facturas
        if ($es_factura) {
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(18, 4, 'CLIENTE:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            // Nombre completo en MultiCell para manejar texto largo
            $nombreCompleto = ($data["CLIENTE"]["cliente_nombres"] ?? '') . " " . ($data["CLIENTE"]["cliente_apellidos"] ?? '');
            if (trim($nombreCompleto) == '') {
                $nombreCompleto = 'CONSUMIDOR FINAL';
            }
            $this->MultiCell(69, 4, $nombreCompleto, 0, 'L');

            // Segunda línea: Documento y celular
            $this->SetFont('helvetica', 'B', 8);
            $tipo_doc = $data["CLIENTE"]["cliente_tp_docu"] ?? 'DOC';
            $docLabel = ($tipo_doc == 'RUC' ? 'RUC:' : ($tipo_doc == 'DNI' ? 'DNI:' : $tipo_doc . ':'));
            $this->Cell(14, 4, $docLabel, 0, 0);
            $this->SetFont('helvetica', '', 8);
            $num_doc = $data["CLIENTE"]["cliente_num_docu"] ?? '--------';
            $this->Cell(22, 4, $num_doc, 0, 0);

            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(10, 4, 'CEL:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $cliente_celular = isset($data["CLIENTE"]["cliente_celular"]) && !empty($data["CLIENTE"]["cliente_celular"])
                ? $data["CLIENTE"]["cliente_celular"] : '---';
            $this->Cell(30, 4, $cliente_celular, 0, 1);

            // Tercera línea: Dirección
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(18, 4, 'DIRECCION:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $direccion = !empty($data["CLIENTE"]["cliente_direccion"]) ? $data["CLIENTE"]["cliente_direccion"] : 'S/D';
            // Usar MultiCell para dirección que puede ser larga
            $this->MultiCell(69, 4, $direccion, 0, 'L');
            $this->Ln(0.5); // Espacio después de la dirección
        }

        // Datos del pasajero - Siempre se muestran (tanto en factura como en boleta)
        if (!empty($data["CLIENTE"]["pasajero_nombres"]) || !empty($data["CLIENTE"]["pasajero_apellidos"])) {

            // Si es boleta y no hay pasajero, mostrar datos del cliente como pasajero
            $mostrar_como_pasajero = false;
            $nombrePasajero = '';
            $tipo_doc_pasajero = '';
            $num_doc_pasajero = '';
            $edad_pasajero = '---';
            $nacionalidad_pasajero = '---';
            $celular_pasajero = '---';

            if (!empty($data["CLIENTE"]["pasajero_nombres"]) || !empty($data["CLIENTE"]["pasajero_apellidos"])) {
                // Hay datos de pasajero en la BD
                $nombrePasajero = ($data["CLIENTE"]["pasajero_nombres"] ?? '') . " " . ($data["CLIENTE"]["pasajero_apellidos"] ?? '');
                $tipo_doc_pasajero = $data["CLIENTE"]["pasajero_tp_docu"] ?? 'DNI';
                $num_doc_pasajero = $data["CLIENTE"]["pasajero_num_docu"] ?? '--------';
                $nacionalidad_pasajero = $data["CLIENTE"]["pasajero_nacionalidad"] ?? '---';
                $celular_pasajero = isset($data["CLIENTE"]["pasajero_celular"]) && !empty($data["CLIENTE"]["pasajero_celular"])
                    ? $data["CLIENTE"]["pasajero_celular"] : '---';

                // Calcular edad
                if (!empty($data["CLIENTE"]["pasajero_fecha_nacimiento"])) {
                    $fecha_nac = explode('-', $data["CLIENTE"]["pasajero_fecha_nacimiento"]);
                    if (isset($fecha_nac[0]) && $fecha_nac[0] != "00" && $fecha_nac[0] != "") {
                        $edad_pasajero = date("Y") - $fecha_nac[0];
                    }
                }
            } elseif ($es_boleta && !empty($data["CLIENTE"]["cliente_nombres"])) {
                // Es boleta y no hay pasajero específico, usar datos del cliente como pasajero
                $mostrar_como_pasajero = true;
                $nombrePasajero = ($data["CLIENTE"]["cliente_nombres"] ?? '') . " " . ($data["CLIENTE"]["cliente_apellidos"] ?? '');
                $tipo_doc_pasajero = $data["CLIENTE"]["cliente_tp_docu"] ?? 'DNI';
                $num_doc_pasajero = $data["CLIENTE"]["cliente_num_docu"] ?? '--------';
                $nacionalidad_pasajero = 'PERUANA'; // Valor por defecto
                $celular_pasajero = isset($data["CLIENTE"]["cliente_celular"]) && !empty($data["CLIENTE"]["cliente_celular"])
                    ? $data["CLIENTE"]["cliente_celular"] : '---';

                // Calcular edad del cliente
                if (!empty($data["CLIENTE"]["cliente_fecha_nacimiento"])) {
                    $fecha_nac = explode('-', $data["CLIENTE"]["cliente_fecha_nacimiento"]);
                    if (isset($fecha_nac[0]) && $fecha_nac[0] != "00" && $fecha_nac[0] != "") {
                        $edad_pasajero = date("Y") - $fecha_nac[0];
                    }
                }
            }

            // Mostrar datos del pasajero si tenemos información
            if (!empty(trim($nombrePasajero))) {
                // Título PASAJERO (con indicador si es el cliente en boleta)
                $this->SetFont('helvetica', 'B', 8);
                if ($mostrar_como_pasajero) {
                    $this->Cell(18, 4, 'PASAJERO:', 0, 0);
                } else {
                    $this->Cell(18, 4, 'PASAJERO:', 0, 0);
                }
                $this->SetFont('helvetica', '', 8);
                $this->MultiCell(69, 4, $nombrePasajero, 0, 'L');

                // Línea de documento, edad y nacionalidad
                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(10, 4, substr($tipo_doc_pasajero, 0, 3) . ':', 0, 0); // Mostrar solo 3 caracteres del tipo doc
                $this->SetFont('helvetica', '', 8);
                $this->Cell(17, 4, $num_doc_pasajero, 0, 0);

                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(11, 4, 'Edad:', 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(10, 4, $edad_pasajero, 0, 0);

                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(13, 4, 'Nac:', 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(17, 4, substr($nacionalidad_pasajero, 0, 8), 0, 1); // Limitar nacionalidad

                // Celular del pasajero
                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(21, 4, 'CELULAR:', 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(55, 4, $celular_pasajero, 0, 1);
            }
        } elseif ($es_boleta && !empty($data["CLIENTE"]["cliente_nombres"])) {
            // Caso especial: Es boleta y no hay pasajero, mostrar cliente como pasajero
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(18, 4, 'PASAJERO:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $nombreCliente = ($data["CLIENTE"]["cliente_nombres"] ?? '') . " " . ($data["CLIENTE"]["cliente_apellidos"] ?? '');
            $this->MultiCell(69, 4, $nombreCliente, 0, 'L');

            $this->SetFont('helvetica', 'B', 8);
            $tipo_doc = $data["CLIENTE"]["cliente_tp_docu"] ?? 'DNI';
            $this->Cell(10, 4, substr($tipo_doc, 0, 3) . ':', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(17, 4, $data["CLIENTE"]["cliente_num_docu"] ?? '--------', 0, 0);

            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(11, 4, 'Edad:', 0, 0);
            $this->SetFont('helvetica', '', 8);

            // Calcular edad del cliente
            $edad_cliente = '---';
            if (!empty($data["CLIENTE"]["cliente_fecha_nacimiento"])) {
                $fecha_nac = explode('-', $data["CLIENTE"]["cliente_fecha_nacimiento"]);
                if (isset($fecha_nac[0]) && $fecha_nac[0] != "00" && $fecha_nac[0] != "") {
                    $edad_cliente = date("Y") - $fecha_nac[0];
                }
            }
            $this->Cell(10, 4, $edad_cliente, 0, 0);

            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(13, 4, 'Nac:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $nacionalidad = $data["CLIENTE"]["cliente_nacionalidad"] ?? 'PERUANA';
            $this->Cell(17, 4, substr($nacionalidad, 0, 8), 0, 1);

            // Celular
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(21, 4, 'CELULAR:', 0, 0);
            $this->SetFont('helvetica', '', 8);
            $celular = isset($data["CLIENTE"]["cliente_celular"]) && !empty($data["CLIENTE"]["cliente_celular"])
                ? $data["CLIENTE"]["cliente_celular"] : '---';
            $this->Cell(55, 4, $celular, 0, 1);
        }

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
        $destino_lines = $this->MultiCell(35, 4, strtoupper($data["CABECERA"]["terminal_destino"]), 0, 'L', 0, 1);

        // Guardar posición después de DESTINO
        $afterDestinoY = $this->GetY();

        // FECHA/HORA - Ahora como parte de la columna izquierda
        $this->SetX(2); // Volver al margen izquierdo
        $this->Ln(0.5);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(20, 4, 'FECHA/HORA:', 0, 0);
        $this->SetFont('helvetica', 'B', 11); // Fuente un poco más pequeña para la fecha
        $fecha = date("d-m-Y", strtotime($data["CABECERA"]["programacion_fecha_salida"]));
        $hora = date("h:i A", strtotime($data["CABECERA"]["programacion_hora_salida"]));
        $fecha_hora_completa = $fecha . " " . $hora;
        $fecha_hora_lines = $this->MultiCell(25, 4, $fecha_hora_completa, 0, 'L', 0, 1);

        // Guardar la Y final de la columna izquierda
        $leftColumnY = $this->GetY();

        // --- COLUMNA DERECHA: Asiento/Piso (más grande) ---
        $this->SetXY(53, $startY); // Posicionar en columna derecha

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
        $startX = 53 + (($availableWidth - $total_width) / 2);

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

        // Importes
        $this->SetFont('helvetica', '', 9);
        $this->Cell(19, 4, 'Exonerado:', 0, 0);
        $this->Cell(18, 4, 'S/. ' . $data["CABECERA"]["op_exonerada"], 0, 1);

        //$this->Cell(19, 4, 'Grabado:', 0, 0);
        //$this->Cell(18, 4, 'S/. 0.00', 0, 1);

        //$this->Cell(19, 4, 'IGV:', 0, 0);
        //$this->Cell(18, 4, 'S/. 0.00', 0, 1);

        $this->Cell(19, 4, 'IMPORTE:', 0, 0);
        $this->Cell(18, 4, 'S/. ' . $data["CABECERA"]["total"], 0, 1);

        $this->Ln(2);

        // Información de IGV
        $this->Ln(-15);
        $this->Cell(37, 3, '', 0, 0);
        $this->SetFont('helvetica', '', 7.5);
        $this->MultiCell(39, 3, 'Servicios exonerados del impuesto General a las ventas', 0, 'C');
        $this->Cell(37, 3, '', 0, 0);
        $this->MultiCell(39, 3, 'https://www.sunat.gob.pe/legistacion/igv/ley/apendice2.pdf', 0, 'C');
        $this->Ln(2);

        // Monto en letras
        $this->SetFont('helvetica', '', 9);
        $this->Cell(10, 4, 'SON:', 0, 0);
        $this->SetFont('helvetica', 'B', 9);
        $this->MultiCell(66, 4, numtoletras($data["CABECERA"]["total"]), 0, 'L');
        $this->Ln(1);

        // Código QR
        if ($data["CABECERA"]["cod_qr"]) {
            if (strpos($data["CABECERA"]["cod_qr"], 'private/empresa/') !== false) {
                if (file_exists($data["CABECERA"]["cod_qr"])) {
                    $this->Image($data["CABECERA"]["cod_qr"], 05, $this->GetY(), 25, 25);
                }
            } elseif (strpos($data["CABECERA"]["cod_qr"], 'https://') !== false) {
                $urlQR = str_replace(' ', '%20', $data['CABECERA']['cod_qr']);
                $this->Image($urlQR, 05, $this->GetY(), 25, 25);
            }
        }

        // Información adicional
        $this->Cell(29, 15, '', 0, 0, 'C');
        $this->SetFont('helvetica', '', 7);
        $this->MultiCell(44, 3, 'Resoluciones de Superintendencia N.182-2016-SUNAT/ N.318-2017-SUNAT', 0, 'C');

        $this->Cell(29, 3, '', 0, 0);
        $this->MultiCell(44, 3, 'Usted puede consultar su Factura Electronica desde su clave SOL', 0, 'C');

        // Información de pago y vendedor
        $this->SetFont('helvetica', '', 7);
        $this->Cell(29, 4, '', 0, 0);
        $this->Cell(19, 4, 'Forma de pago:', 0, 0);
        $this->Cell(23, 4, strtoupper($data["CABECERA"]["forma_pago"]), 0, 1);

        $this->Cell(29, 4, '', 0, 0);
        $this->Cell(19, 4, 'Metodo de pago:', 0, 0);
        $this->Cell(23, 4, strtoupper($data["CABECERA"]["medio_pago"]), 0, 1);

        $this->Cell(29, 4, '', 0, 0);
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
        $this->SetFont('helvetica', 'B', 8);

        // Verificar si existe información del SOAT
        if (!empty($data['CABECERA']['soat'])) {
            // Mostrar información del SOAT - TEXTO COMPLETO CON LA COMPAÑÍA
            $this->MultiCell(
                76,
                4,
                'Servicio cubierto por el Seguro de la compañía: '
                    . $data['CABECERA']['soat']
                    . (!empty($data['CABECERA']['num_poliza'])
                        ? ' - Póliza N°: ' . $data['CABECERA']['num_poliza']
                        : ''),
                0,
                'L'
            );
        }

        $this->Ln(1);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(76, 4, URL_PAGE_WEB_COMPROBANTES, 0, 1, 'L');
        $this->Ln(1);

        $this->SetLineStyle([
            'width' => 0.1,
            'cap'   => 'butt',
            'join'  => 'miter',
            'dash'  => '1,1',
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
        ob_clean();
        return $this->Output('ticket.pdf', 'I');
    }
}

$ticket = new TICKET_TCPDF();
$ticket->generateTicket($this->data);
