<?php
ob_start();
require_once('public/plugins/tc/tcpdf.php');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

class ManifestoTCPDF extends TCPDF
{
    private $datos;

    public function __construct($data)
    {
        parent::__construct('P', 'mm', 'A4');
        $this->datos = $data;
        $this->SetMargins(2, 10, 2);
        $this->SetAutoPageBreak(true, 10);
    }

    private function limpiarTerminal(string $texto): string
    {
        // Eliminar la palabra "TERMINAL"
        $limpio = str_ireplace('terminal', '', $texto);

        // Si "AGENCIA" está al inicio, eliminarla
        $limpio = preg_replace('/^\s*AGENCIA\s*-?\s*/i', '', $limpio);

        // Si "AGENCIA" aparece después, eliminarla y todo lo que venga después
        $limpio = preg_replace('/\s*-?\s*AGENCIA.*$/i', '', $limpio);

        // Eliminar espacios duplicados
        $limpio = preg_replace('/\s+/', ' ', $limpio);

        return trim($limpio);
    }

    public function Footer()
    {
        $this->SetY(-8);
        $this->SetFont('helvetica', '', 7);
        $this->Cell(0, 4, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }

    public function Header()
    {
        // Logo
        if (!empty($this->datos["HEADER"]["HEADER_EMPRESA"]["logo"]) && file_exists($this->datos["HEADER"]["HEADER_EMPRESA"]["logo"])) {
            // Obtener las dimensiones originales de la imagen
            list($width, $height) = getimagesize($this->datos["HEADER"]["HEADER_EMPRESA"]["logo"]);

            // Definir un tamaño máximo (en mm) para el logo
            $maxWidth = 35; // Aumentado para que el logo sea más grande
            $maxHeight = 25; // Aumentado para dar flexibilidad

            // Calcular la relación de aspecto
            $aspectRatio = $width / $height;

            // Ajustar dimensiones manteniendo la proporción
            if ($aspectRatio > 1) {
                // Imagen más ancha que alta (rectangular horizontal)
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $aspectRatio;
            } else {
                // Imagen más alta que ancha (rectangular vertical) o cuadrada
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $aspectRatio;
            }

            // Asegurar que no se excedan los límites máximos
            if ($newWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $aspectRatio;
            }
            if ($newHeight > $maxHeight) {
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $aspectRatio;
            }

            // Insertar la imagen con las dimensiones ajustadas
            try {
                $this->Image($this->datos["HEADER"]["HEADER_EMPRESA"]["logo"], 2, 5, $newWidth, $newHeight);
            } catch (Exception $e) {
                error_log("Error al cargar el logo: " . $e->getMessage());
                $this->SetXY(2, 5);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(30, 20, "Logo no disponible", 0, 1, 'C');
            }
        }

        $this->SetXY(30, 10); // Adjusted X position to start closer to logo
        $this->SetFont('Helvetica', 'B', 7);
        $this->Cell(140, 4, $this->datos["HEADER"]["HEADER_EMPRESA"]["razon_social"], 0, 1, 'C');

        // Fiscal Address
        $this->SetXY(30, 14); // Adjusted Y position for better spacing
        $this->SetFont('Helvetica', '', 5.5);
        $this->Cell(140, 3, "DOM. FISCAL: " . strtoupper(trim($this->datos["HEADER"]["HEADER_EMPRESA"]["direccion_fiscal"]) . ' ' . $this->datos["HEADER"]["HEADER_EMPRESA"]["ubigeo"]), 0, 1, 'C');

        $this->SetFont('Helvetica', '', 5);
        //Val
        foreach ($this->datos["HEADER"]["TERMINALES"] as $terminal) {
            $nombre = $terminal['nombre'] ?? 'N/A';
            $direccion = $terminal['direccion_fiscal'] ?? 'N/A';
            $celular = $terminal['celular'] ?? 'N/A';

            $texto = sprintf(
                "%s: %s - Cel: %s",
                $nombre,
                $direccion,
                $celular
            );

            $this->SetX(30); // Posicion inicial
            $this->MultiCell(140, 1, $texto, 0, 'C');
        }

        // RUC and Title
        $this->SetXY(162, 4);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(43, 6, "RUC: " . $this->datos["HEADER"]["HEADER_EMPRESA"]["num_docu"], 1, 1, 'C');
        $this->SetX(162);
        $this->Cell(43, 6, "MANIFIESTO DE PASAJEROS", 1, 1, 'C');
        $this->SetX(162);
        $correlativo = str_pad(
            $this->datos['HEADER']['MANIFIESTO']['correlativo'],
            5,
            '0',
            STR_PAD_LEFT
        );
        $this->Cell(43, 6, "Nro " . $this->datos['HEADER']['MANIFIESTO']['serie'] . ' - ' . $correlativo, 1, 1, 'C');
        $this->SetY(20);
    }

    public function generateManifesto()
    {
        $this->setPrintHeader(true);
        $this->AddPage();

        // Posicionar el número de autorización SUNAT en la esquina superior derecha
        $this->SetFont('helvetica', '', 7);
        $currentY = $this->GetY(); // Guardar la posición Y actual
        $this->SetXY(161, 23); // Posición fija para el texto SUNAT
        $this->MultiCell(46, 0, "SUNAT NUMERO DE AUTORIZACIÓN DE IMPRESION: " . $this->datos['numero_autorizacion_sunat'], 0, 1);
        $this->SetY($currentY); // Restaurar la posición Y original

        // Driver and Vehicle Details
        $this->Ln(14);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(21, 0, "CONDUCTOR: ", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(90, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["conductor_nombres"] . " " . $this->datos["HEADER"]["HEADER_DETALLE"]["conductor_apellidos"], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(20, 0, "LICENCIA:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(46, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["conductor_licencia"], 0, 1);

        // Copilots
        if (!empty($this->datos["HEADER"]["COPILOTOS"])) {
            foreach ($this->datos["HEADER"]["COPILOTOS"] as $copiloto) {
                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(21, 0, "COPILOTO: ", 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(90, 0, $copiloto["copiloto_nombres"] . " " . $copiloto["copiloto_apellidos"], 0, 0);
                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(20, 0, "LICENCIA:", 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(46, 0, $copiloto["copiloto_licencia"], 0, 1);
            }
        } else {
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(21, 0, "COPILOTO: ", 0, 0);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(90, 0, "", 0, 0);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(20, 0, "LICENCIA:", 0, 0);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(46, 0, "", 0, 1);
        }

        // Assistants
        if (!empty($this->datos["HEADER"]["AYUDANTES"])) {
            foreach ($this->datos["HEADER"]["AYUDANTES"] as $ayudante) {
                $this->SetFont('helvetica', 'B', 8);
                $this->Cell(28, 0, "AYUDANTE: ", 0, 0);
                $this->SetFont('helvetica', '', 8);
                $this->Cell(0, 0, $ayudante["ayudante_nombres"] . " " . $ayudante["ayudante_apellidos"] . ' - ' . $ayudante['ayudante_doc'], 0, 1);
            }
        } else {
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(28, 0, "AYUDANTE: ", 0, 0);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(0, 0, "", 0, 1);
        }

        // Vehicle Details
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(28, 0, "PLACA:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(20, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["vehiculo_placa"], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(20, 0, "MARCA:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(30, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["vehiculo_marca"], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(60, 0, "TARJETA UNICA DE CIRCULACION:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(36, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["vehiculo_tuc"], 0, 1);

        // Travel Details
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(28, 0, "LUGAR ORIGEN:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(48, 0, strtoupper($this->datos["HEADER"]["HEADER_DETALLE"]["terminal_origen"]), 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(30, 0, "LUGAR DESTINO:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(49, 0, strtoupper($this->datos["HEADER"]["HEADER_DETALLE"]["terminal_destino"]), 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(25, 0, "FECHA VIAJE:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(25, 0, date("d-m-Y", strtotime($this->datos["HEADER"]["HEADER_DETALLE"]["programacion_fecha_salida"])), 0, 1);

        // Additional Details
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(35, 0, "CANTIDAD ASIENTOS:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(8, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["vehiculo_cantidad_asiento"], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(40, 0, "CANTIDAD EMBARCADOS:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(8, 0, count($this->datos["DETALLE"]), 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(24, 0, "NRO DE POLIZA:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(40, 0, $this->datos["HEADER"]["HEADER_DETALLE"]["vehiculo_num_poliza"], 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(25, 0, "HORA VIAJE:", 0, 0);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(25, 0, date("H:i A", strtotime($this->datos["HEADER"]["HEADER_DETALLE"]["programacion_hora_salida"])), 0, 1);

        // ===== Definición de anchos de columna (usar SIEMPRE estas variables) =====
        $colAsiento = 10;
        $colPasajero = 50;
        $colEdad = 7;
        $colPais = 16;
        $colDoc = 6;
        $colNDoc = 16;
        $colOD = 35;  // Origen-Destino
        $colBoleto = 20;  // Nro de Boleto
        $colImporte = 13;
        $colObs = 33;  // Observaciones
        // Total = 190 mm

        $lineHeight = 3.2;

        // ===== Passenger Table Header =====
        $this->Ln(2);
        $this->SetFont('helvetica', 'B', 7);
        $this->Cell($colAsiento, 1, 'Asiento', 1, 0, 'C');
        $this->Cell($colPasajero, 1, 'Pasajero', 1, 0, 'C');
        $this->Cell($colEdad, 1, 'Edad', 1, 0, 'C');
        $this->Cell($colPais, 1, 'País', 1, 0, 'C');
        $this->Cell($colDoc, 1, 'DOC', 1, 0, 'C');
        $this->Cell($colNDoc, 1, 'N. DOC', 1, 0, 'C');
        $this->Cell($colOD, 1, 'Origen-Destino', 1, 0, 'C');
        $this->Cell($colBoleto, 1, 'N. De Boleto', 1, 0, 'C');
        $this->Cell($colImporte, 1, 'Importe', 1, 0, 'C');
        $this->Cell($colObs, 1, 'Observaciones', 1, 1, 'C');

        // ===== Passenger Details =====
        $importe_total = 0.0;
        $this->SetFont('helvetica', '', 7);

        foreach ($this->datos["DETALLE"] as $pasajero) {
            $anio_nacimiento = (int) explode("-", $pasajero["cliente_fecha_nacimiento"])[0];
            $edad_cliente = date("Y") - $anio_nacimiento;
            $nombreCompleto = $pasajero["cliente_nombres"] . " " . $pasajero["cliente_apellidos"];
            $origenDestino = $this->limpiarTerminal($pasajero["terminal_origen"]) . '-' . $this->limpiarTerminal($pasajero["terminal_destino"]);
            $observaciones = $pasajero['c_nino'] == 1
                ? "Viaja con menor (ver abajo)"
                : ($pasajero["venta_obs"] ?: $pasajero['obs_pasajero']);

            if (!empty($pasajero['estado_pospuesto'])) {
                $observaciones = "SALDO POSP. APLICADO" .
                    (!empty($observaciones) ? " - " . $observaciones : "");
            }

            // --- Calcular altura real de la fila usando getStringHeight (más preciso que getNumLines) ---
            $hNombre = $this->getStringHeight($colPasajero, $nombreCompleto);
            $hOD = $this->getStringHeight($colOD, $origenDestino);
            $hObs = $this->getStringHeight($colObs, $observaciones);

            $rowHeight = max($hNombre, $hOD, $hObs, $lineHeight + 1); // +1 de colchón mínimo

            // --- Calcular altura de la fila del menor ---
            $rowHeightMenor = 0;

            // --- Si es fila con menor, sumar de antemano la altura de la segunda línea ---
            $rowHeightMenor = 0;
            if ($pasajero['c_nino'] == 1) {
                $anio_nino = '';
                $edad_nino = '';
                if (!empty($pasajero['nino_fecha_nacimiento'])) {
                    $anio_nino = (int) explode("-", $pasajero["nino_fecha_nacimiento"])[0];
                    $edad_nino = date("Y") - $anio_nino;
                }
                $nino_nombre = trim(($pasajero['nino_nombres'] ?? '') . ' ' . ($pasajero['nino_apellidos'] ?? ''));
                $nino_doc = $pasajero['nino_num_docu'] ?? '-';
                $descripcion_menor = "Menor de " . $pasajero["cliente_nombres"] . " - " . $edad_nino . " años";

                $rowHeightMenor = max(
                    $this->getStringHeight($colPasajero, $nino_nombre),
                    $this->getStringHeight($colObs, $descripcion_menor),
                    $lineHeight + 1
                );
            }

            // --- Salto de página si no entra la fila completa ---
            if ($this->GetY() + $rowHeight + $rowHeightMenor > 280) {
                $this->AddPage();

                // Separación entre el Header y la tabla
                $this->SetY(25);

                // Repetir título de la tabla
                $this->SetFont('helvetica', 'B', 7);

                $this->Cell($colAsiento, 3, 'Asiento', 1, 0, 'C');
                $this->Cell($colPasajero, 3, 'Pasajero', 1, 0, 'C');
                $this->Cell($colEdad, 3, 'Edad', 1, 0, 'C');
                $this->Cell($colPais, 3, 'País', 1, 0, 'C');
                $this->Cell($colDoc, 3, 'DOC', 1, 0, 'C');
                $this->Cell($colNDoc, 3, 'N. DOC', 1, 0, 'C');
                $this->Cell($colOD, 3, 'Origen-Destino', 1, 0, 'C');
                $this->Cell($colBoleto, 3, 'N. De Boleto', 1, 0, 'C');
                $this->Cell($colImporte, 3, 'Importe', 1, 0, 'C');
                $this->Cell($colObs, 3, 'Observaciones', 1, 1, 'C');

                $this->SetFont('helvetica', '', 7);
            }


            // --- Fila del pasajero principal ---
            $x = $this->GetX();
            $y = $this->GetY();

            $this->SetXY($x, $y);
            $this->Cell($colAsiento, $rowHeight, $pasajero["num_asiento"], 0, 0, 'C');

            $this->SetXY($x + $colAsiento, $y);
            $this->MultiCell($colPasajero, $rowHeight, $nombreCompleto, 0, 'L', false, 0, '', '', true, 0, false, true, $rowHeight, 'M');

            $this->SetXY($x + $colAsiento + $colPasajero, $y);
            $this->Cell($colEdad, $rowHeight, $edad_cliente > 100 ? '' : $edad_cliente, 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad, $y);
            $this->Cell($colPais, $rowHeight, $pasajero['cliente_nacionalidad'], 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais, $y);
            $this->Cell($colDoc, $rowHeight, $pasajero["cliente_tp_docu"], 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc, $y);
            $this->Cell($colNDoc, $rowHeight, $pasajero["cliente_num_docu"], 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc, $y);
            $this->MultiCell($colOD, $rowHeight, $origenDestino, 0, 'C', false, 0, '', '', true, 0, false, true, 0, 'M');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD, $y);
            $this->Cell($colBoleto, $rowHeight, $pasajero["venta_serie"] . " - " . str_pad($pasajero["venta_correlativo"] ?? '', 8, "0", STR_PAD_LEFT), 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD + $colBoleto, $y);
            $this->Cell($colImporte, $rowHeight, 'S/ ' . number_format((float) ($pasajero["importe"] ?? 0), 2), 0, 0, 'C');

            $this->SetXY($x + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD + $colBoleto + $colImporte, $y);
            $this->MultiCell($colObs, $rowHeight, $observaciones, 0, 'C', false, 0, '', '', true, 0, false, true, $rowHeight, 'M');

            $this->SetXY($x, $y + $rowHeight);

            // --- Fila adicional del menor (si aplica) ---
            if ($pasajero['c_nino'] == 1) {
                $x2 = $this->GetX();
                $y2 = $this->GetY();

                $this->SetXY($x2, $y2);
                $this->Cell($colAsiento, $rowHeightMenor, '-', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento, $y2);
                $this->MultiCell($colPasajero, $rowHeightMenor, $nino_nombre, 0, 'L', false, 0, '', '', true, 0, false, true, $rowHeightMenor, 'M');

                $this->SetXY($x2 + $colAsiento + $colPasajero, $y2);
                $this->Cell($colEdad, $rowHeightMenor, $edad_nino, 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad, $y2);
                $this->Cell($colPais, $rowHeightMenor, '-', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais, $y2);
                $this->Cell($colDoc, $rowHeightMenor, 'DNI', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc, $y2);
                $this->Cell($colNDoc, $rowHeightMenor, $nino_doc, 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc, $y2);
                $this->Cell($colOD, $rowHeightMenor, '', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD, $y2);
                $this->Cell($colBoleto, $rowHeightMenor, '-', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD + $colBoleto, $y2);
                $this->Cell($colImporte, $rowHeightMenor, '-', 0, 0, 'C');

                $this->SetXY($x2 + $colAsiento + $colPasajero + $colEdad + $colPais + $colDoc + $colNDoc + $colOD + $colBoleto + $colImporte, $y2);
                $this->MultiCell($colObs, $rowHeightMenor, $descripcion_menor, 0, 'L', false, 0, '', '', true, 0, false, true, $rowHeightMenor, 'M');

                $this->SetXY($x2, $y2 + $rowHeightMenor);
            }


            // ===== Acumular importe total =====

            $importe_total += (float) ($pasajero["importe"] ?? 0);
        }


        // Signatures
        $this->Ln(8);
        $this->SetFont('helvetica', 'B', 7.5);
        $this->Cell(64.6, 0, '___________________________', 0, 0, 'C');
        $this->Cell(64.6, 0, '___________________________', 0, 0, 'C');
        $this->Cell(64.6, 0, '___________________________', 0, 1, 'C');

        $this->Cell(64.6, 0, 'V. B. EMPRESA', 0, 0, 'C');
        $this->Cell(64.6, 0, 'CONDUCTOR', 0, 0, 'C');

        return $this->Output('manifiesto.pdf', 'I');
        ob_end_clean();
    }
}

// Usage example:
$pdf = new ManifestoTCPDF($this->data);
$pdf->generateManifesto();
