<?php
// Verificar si se está incluyendo desde nota_venta.php
$es_inclusion = isset($es_interno) && $es_interno === true;

// Si NO es una inclusión, crear el PDF como siempre
if (!$es_inclusion) {

    ini_set('memory_limit', '2048M');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('America/Lima');

    require_once('public/plugins/print/num_letras.php');
    require_once('public/plugins/tc/tcpdf.php');

    define("TEXT_SIZE_COMPROBANTE_SERIE", 10);
    define("TEXT_SIZE_REDUCIDO", 8);

    class TCPDF_CellFit extends TCPDF
    {
        protected $widths;
        protected $aligns;
        protected $cMargin = 1;

        public function __construct()
        {
            parent::__construct('P', 'mm', array(72.1, 297), true, 'UTF-8', false);
            $this->SetMargins(1.5, 2, 1.5);
            $this->SetAutoPageBreak(true, 2);
        }

        function AutoPrint($dialog = false)
        {
            $param = ($dialog ? 'true' : 'false');
            $script = "print($param);";
            $this->IncludeJS($script);
        }

        function SetWidths($w)
        {
            $this->widths = $w;
        }

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

                if (in_array($i, $boldColumns))
                    $this->SetFont('', 'B');

                $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false, 0, '', '', true, 0, false, true, $lineHeight, 'M');

                if (in_array($i, $boldColumns))
                    $this->SetFont('', '');

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
                if ($c == ' ')
                    $sep = $i;

                $l += $this->GetStringWidth($c);
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j)
                            $i++;
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

    // Validación inicial
    if (!isset($this->data["DESTINO"]) || !is_array($this->data["DESTINO"])) {
        error_log("Error: \$this->data['DESTINO'] no está definido o no es un arreglo");
        $this->data["DESTINO"] = [
            'terminal_origen' => 'S/D',
            'terminal_destino' => 'S/D',
            'tipo' => 'S/D'
        ];
    }

    if (!function_exists('getInicialesUnidad')) {
        function getInicialesUnidad($unidad)
        {
            $unidad = strtoupper(trim($unidad));

            $iniciales = [
                'UNIDAD' => 'UN',
                'CAJA' => 'BX',
                'GALON' => 'GLL',
                'KILOS' => 'KGM',
                'FRASCO' => 'JR',
                'ENVASE' => 'CH',
                'SACO' => 'SA',
                'LATA' => 'CA',
                'SERVICIO' => 'ZZ'
            ];

            return $iniciales[$unidad] ?? 'PK';
        }
    }

    // Crear PDF con márgenes reducidos
    $pdf = new TCPDF_CellFit();
    $pdf->setPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    // Llamar a la función que genera el contenido
    generarSeccionTransportista($pdf, $this->data, 'TRANSPORTISTA');

    // Autoimpresión
    $pdf->AutoPrint(false);

    // Generar PDF
    ob_clean();
    $pdf->Output('ticket_transportista.pdf', 'I');
    exit;
}

// =============================================
// FUNCIÓN PARA GENERAR LA SECCIÓN (REUTILIZABLE)
// =============================================

function generarSeccionTransportista($pdf, $data, $titulo_final = 'TRANSPORTISTA')
{

    // NOTA: $pdf ya debe estar creado cuando se llama desde nota_venta.php
    if (!defined('TEXT_SIZE_REDUCIDO')) {
        define('TEXT_SIZE_REDUCIDO', 9);
    }

    $pdf->Ln(1.5);
    $pdf->SetLineWidth(0.2);
    $pdf->Cell(72, 0, '', 'T');
    $pdf->Ln(1.5);

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->MultiCell(72, 4, $data["HEADER"]["HEADER_EMPRESA"]["razon_social"] . ' - ' . $titulo_final, 0, 'C', 0, 1, '', '', true);
    $pdf->Ln(0.5);

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(72, 3, "Referencia:", 0, 1, 'L');
    $pdf->Ln(0.5);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->MultiCell(72, 3, $data["BODY"]["tp_comprobante"], 0, 'C', 0, 1, '', '', true);
    $pdf->Ln(1);

    // CORREGIR: Usar str_pad en lugar de substr para el correlativo
    $correlativo = $data["BODY"]["serie"] . "-" . str_pad($data["BODY"]["correlativo"], 8, '0', STR_PAD_LEFT);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(72, 3, $correlativo, 0, 1, 'C');
    $pdf->Ln(1);

    // DATOS CLIENTE - DESTINATARIO (TRANSPORTISTA)
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(17, 3, 'FECHA:', 0, 0);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(51, 3, date("d-m-Y H:i A", strtotime($data["BODY"]["fecha_emision"])), 0, 'L');
    $pdf->Ln(0.1);

    // CLIENTE
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(17, 3, 'CLIENTE:', 0, 'L', 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3, $data["BODY"]["remitente_nombres"] . ' ' . $data["BODY"]["remitente_apellidos"], 0, 'L', 0, 1);
    $pdf->Ln(0.1);

    // DNI Y CELULAR REMITENTE
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 3, $data["BODY"]["remitente_tp_docu"] . ': ', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(20, 3, $data["BODY"]["remitente_num_docu"], 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(8, 3, 'CEL:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 3, !empty($data["BODY"]["remitente_celular"]) ? $data["BODY"]["remitente_celular"] : 'S/N', 0, 1, 'L');
    $pdf->Ln(0.1);

    // Direccion Cliente
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(17, 3, 'DIRECCIÓN:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);

    // Usar la dirección completa combinada
    $direccion_remitente = !empty($data["BODY"]["remitente_direccion_completa"])
        ? $data["BODY"]["remitente_direccion_completa"]
        : (!empty($data["BODY"]["remitente_direccion"])
            ? $data["BODY"]["remitente_direccion"]
            : 'S/D');

    $pdf->MultiCell(0, 3, $direccion_remitente, 0, 'L', 0, 1);
    $pdf->Ln(0.1);

    $numero_doc_destinatario = $data["BODY"]["destinatario_num_docu"];
    $destinatario = $data["BODY"]["destinatario_nombres"] . ' ' . $data["BODY"]["destinatario_apellidos"];
    $obs_destinatario = $data["BODY"]["obs_destinatario"] ? ' (' . $data["BODY"]["obs_destinatario"] . ')' : '';

    // === DESTINATARIO ===
    $datos_destinatario = $data["HEADER"]["HEADER_EMPRESA"]['datos_destinatario'] ?? 0;

    if ($datos_destinatario == 1) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->MultiCell(24.2, 3, 'DESTINATARIO:', 0, 'L', 0, 0);
        $pdf->SetFont('helvetica', '', 8);

        $destinatario = $data["BODY"]["destinatario_nombres"] . ' ' . $data["BODY"]["destinatario_apellidos"];
        $obs_destinatario = $data["BODY"]["obs_destinatario"] ? ' (' . $data["BODY"]["obs_destinatario"] . ')' : '';

        $pdf->MultiCell(0, 3, $destinatario . "\n" . ($numero_doc_destinatario == '00000000' ? $obs_destinatario : ''), 0, 'L', 0, 1);

        // DNI Y CELULAR DESTINATARIO EN MISMA LÍNEA
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(17, 3, $data["BODY"]["destinatario_tp_docu"] . ': ', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(20, 3, $data["BODY"]["destinatario_num_docu"], 0, 0, 'L');

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(8, 3, 'CEL:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 3, !empty($data["BODY"]["destinatario_celular"]) ? $data["BODY"]["destinatario_celular"] : 'S/N', 0, 1, 'L');

        $pdf->Ln(0.1); // Espacio adicional después de sección
    } else {
        // Si no se muestra destinatario, solo dejar espacio mínimo
        $pdf->Ln(0.1);
    }

    // === ORIGEN/DESTINO ===
    $origen_destino = $data["HEADER"]["HEADER_EMPRESA"]['origen_destino'] ?? 0;

    if ($origen_destino == 1) {
        // Determinar si es entrega a domicilio
        $is_entrega_domicilio = isset($data["DESTINO"]["tipo"]) &&
            $data["DESTINO"]["tipo"] === 'ENTREGA A DOMICILIO';

        // Obtener la configuración de dirección completa
        $mostrar_direccion_completa = $data["HEADER"]["HEADER_EMPRESA"]['mostrar_direccion_completa'] ?? 0;

        // MODIFICACIÓN: ORIGEN
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(17, 3, 'ORIGEN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8.5);

        $terminal_origen = !empty($data["DESTINO"]["terminal_origen"]) ? $data["DESTINO"]["terminal_origen"] : 'S/D';
        $pdf->Cell(51, 3, strtoupper($terminal_origen), 0, 1, 'L');

        // Mostrar dirección completa solo si está activado Y origen_destino está activo
        if ($mostrar_direccion_completa == 1) {
            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->Cell(17, 5, '', 0, 0, 'L');

            $direccion_origen = strtoupper(!empty($data["DESTINO"]["terminal_origen_direccion"]) ?
                $data["DESTINO"]["terminal_origen_direccion"] : 'S/D');
            // AÑADIR UBIGEO COMPLETO DEL ORIGEN
            $ubigeo_origen = '';
            if (!empty($data["DESTINO"]["ubigeo_origen_distri"])) {
                $ubigeo_origen = ' - ' . $data["DESTINO"]["ubigeo_origen_distri"] . ', ' .
                    $data["DESTINO"]["ubigeo_origen_provi"] . ', ' .
                    $data["DESTINO"]["ubigeo_origen_depa"];
            }

            $direccion_completa_origen = $direccion_origen . $ubigeo_origen;
            $pdf->MultiCell(51, 3, $direccion_completa_origen, 0, 'L', 0, 1);
        } else {
            $pdf->Ln(-0.5);
        }

        // MODIFICACIÓN: DESTINO
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(17, 3, 'DESTINO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);

        // Determinar texto de destino
        if ($is_entrega_domicilio) {
            $destino_text = strtoupper(!empty($data["DESTINO"]["dir_destino_custom"]) ?
                $data["DESTINO"]["dir_destino_custom"] : 'S/D');
        } else {
            $destino_text = strtoupper(!empty($data["DESTINO"]["terminal_destino"]) ?
                $data["DESTINO"]["terminal_destino"] : 'S/D');
        }
        $pdf->Cell(51, 3, $destino_text, 0, 1, 'L');

        // Mostrar dirección completa solo si está activado Y origen_destino está activo
        if ($mostrar_direccion_completa == 1) {
            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->Cell(17, 3, '', 0, 0, 'L');

            if ($is_entrega_domicilio) {
                // Para entrega a domicilio
                $direccion_destino = strtoupper(!empty($data["DESTINO"]["dir_destino_custom"]) ?
                    $data["DESTINO"]["dir_destino_custom"] : 'S/D');

                // AÑADIR UBIGEO COMPLETO DEL DESTINO
                $ubigeo_destino = '';
                if (!empty($data["DESTINO"]["ubigeo_llegada_distri"])) {
                    $ubigeo_destino = ' - ' . $data["DESTINO"]["ubigeo_llegada_distri"] . ', ' .
                        $data["DESTINO"]["ubigeo_llegada_provi"] . ', ' .
                        $data["DESTINO"]["ubigeo_llegada_depa"];
                }

                $direccion_completa_destino = $direccion_destino . $ubigeo_destino;
            } else {
                // Para terminal
                $direccion_destino = strtoupper(!empty($data["DESTINO"]["terminal_destino_direccion"]) ?
                    $data["DESTINO"]["terminal_destino_direccion"] : 'S/D');

                // AÑADIR UBIGEO COMPLETO DEL DESTINO
                $ubigeo_destino = '';
                if (!empty($data["DESTINO"]["ubigeo_destino_distri"])) {
                    $ubigeo_destino = ' - ' . $data["DESTINO"]["ubigeo_destino_distri"] . ', ' .
                        $data["DESTINO"]["ubigeo_destino_provi"] . ', ' .
                        $data["DESTINO"]["ubigeo_destino_depa"];
                }

                $direccion_completa_destino = $direccion_destino . $ubigeo_destino;
            }

            $pdf->MultiCell(51, 3, $direccion_completa_destino, 0, 'L', 0, 1);
        }
    } else {
        // Si ORIGEN/DESTINO está desactivado, solo dejar espacio mínimo
        $pdf->Ln(-0.5);
    }

    // TIPO
    $is_entrega_domicilio = isset($data["DESTINO"]["tipo"]) && $data["DESTINO"]["tipo"] === 'ENTREGA A DOMICILIO';

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(17, 3, 'TIPO:', 0, 0);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->MultiCell(51, 3, isset($data["DESTINO"]["tipo"]) ? strtoupper($data["DESTINO"]["tipo"]) : 'S/D', 0, 'L');
    $pdf->Ln(0.1);

    // TRACKING
    $mostrar_tracking = $data["HEADER"]["HEADER_EMPRESA"]['mostrar_tracking'] ?? 0;
    if ($mostrar_tracking == 1) {
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(17, 3, 'TRACKING:', 0, 0);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->MultiCell(51, 3, !empty($data["BODY"]["codigo_tracking"]) ? $data["BODY"]["codigo_tracking"] : 'S/T', 0, 'L');
        $pdf->Ln(0.5);
    } else {
        $pdf->Ln(1);
    }

    // TABLA CON PESO (NUEVO)
    $pdf->Ln(0.5);
    $pdf->Cell(70, 0, '', 'T');
    $pdf->Ln(0.1);

    $pdf->SetFont('helvetica', 'B', 7.7);
    $pdf->Cell(8, 4, 'CANT', 0, 0, 'C');
    $pdf->Cell(8, 4, 'UNID', 0, 0, 'C');
    $pdf->Cell(23, 4, 'DESCRIPCION', 0, 0, 'C');
    $pdf->Cell(10, 4, 'PESO', 0, 0, 'C');        // NUEVA COLUMNA PESO
    $pdf->Cell(10, 4, 'P.UNIT', 0, 0, 'C');
    $pdf->Cell(10, 4, 'TOTAL', 0, 1, 'C');

    $pdf->Ln(0.1);
    $pdf->Cell(70, 0, '', 'T');
    $pdf->Ln(0.1);

    $suma_total = 0;

    foreach ($data["DETALLE"] as $detalle) {
        $total_item = $detalle["cantidad"] * $detalle["precio"];
        $suma_total += $total_item;

        // Obtener peso
        $peso = isset($data["BODY"]["peso_encomienda"]) ?
            $data["BODY"]["peso_encomienda"] :
            (isset($detalle["peso"]) ? $detalle["peso"] : '0.00');

        $peso_text = ($peso > 0) ? number_format($peso, 2) : '';

        $descripcion = $detalle["descripcion"];
        $observacion = $detalle["obs"];
        $comprobante = !empty($detalle["serie"]) && !empty($detalle["correlativo"]) ?
            $detalle["serie"] . '-' . $detalle["correlativo"] : '';
        $guia = !empty($detalle["guia_serie"]) && !empty($detalle["guia_correlativo"]) ?
            $detalle["guia_serie"] . '-' . $detalle["guia_correlativo"] : '';

        $unidad_medida = isset($detalle["unid_medida"]) ? $detalle["unid_medida"] : 'UNIDAD';
        $iniciales_unidad = getInicialesUnidad($unidad_medida);

        $detalles = trim(
            ($comprobante != '' ? 'COMP: ' . $comprobante : '') .
            ($guia != '' ? '   GUIA: ' . $guia : '') .
            ($observacion != '' ? '   OBS: ' . $observacion : '')
        );

        $descripcion_completa = $descripcion;
        if ($detalles !== '') {
            $descripcion_completa .= '<br/><span style="font-size:7pt;">(' . $detalles . ')</span>';
        }

        $pdf->SetFont('helvetica', '', 7.7);

        // Calcular altura de la fila
        $temp_pdf = clone $pdf;
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $temp_pdf->writeHTMLCell(23, 0, $x + 14, $y, $descripcion_completa, 0, 1, false, true, 'L', true);
        $altura_real = $temp_pdf->GetY() - $y;
        $altura_fila = max($altura_real, 4);

        // Imprimir celdas
        $pdf->Cell(8, $altura_fila, $detalle["cantidad"], 0, 0, 'C');
        $pdf->Cell(8, $altura_fila, $iniciales_unidad, 0, 0, 'C');

        $x_desc = $pdf->GetX();
        $y_desc = $pdf->GetY();

        $pdf->writeHTMLCell(23, $altura_fila, $x_desc, $y_desc, $descripcion_completa, 0, 0, false, true, 'L', true);

        $pdf->SetXY($x_desc + 23, $y_desc);

        // Nueva columna PESO
        $pdf->Cell(10, $altura_fila, $peso_text, 0, 0, 'C');

        $pdf->Cell(10, $altura_fila, number_format($detalle["precio"], 2), 0, 0, 'R');
        $pdf->Cell(10, $altura_fila, number_format($total_item, 2), 0, 1, 'R');
    }

    $pdf->Ln(1);
    $pdf->Cell(70, 0, '', 'T');
    $pdf->Ln(1);

    // TOTAL
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(48, 4, date("d-m-Y H:i A", strtotime($data["BODY"]["fecha_emision"])), 0, 0, 'L');
    $pdf->Cell(10, 4, 'TOTAL S/.', 0, 0, 'R');
    $pdf->Cell(12, 4, number_format($suma_total, 2), 0, 1, 'R');
    $pdf->Ln(1);

    // VENDEDOR
    $mostrar_vendedor = $data["HEADER"]["HEADER_EMPRESA"]['mostrar_vendedor'] ?? 0;
    if ($mostrar_vendedor == 1) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetXY($x, $y);
        $pdf->Cell(20, 2.5, 'VENDEDOR:', 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 8);

        $vendedor_text = trim(
            $data["BODY"]["vendedor_nombres"]
        );

        $pdf->SetXY($x + 20, $y);
        $pdf->MultiCell(52, 2.5, $vendedor_text ?: '---', 0, 'L', false, 1);
    }

    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->MultiCell(72, 2, 'SON: ' . substr(numtoletras(number_format($suma_total, 2, '.', '')), 0, 50), 0, 'L');
    $pdf->Ln(0.3);

    // INFORMACIÓN DE PAGO
    $estado_pago = '';
    if (isset($data["BODY"]["encomienda_pago"])) {
        $estado_pago = $data["BODY"]["encomienda_pago"];
    } elseif (isset($data["DESTINO"]["tipo_envio"])) {
        $estado_pago = $data["DESTINO"]["tipo_envio"];
    } elseif (isset($data["BODY"]["estado"])) {
        $estado_pago = $data["BODY"]["estado"];
    }

    // LÓGICA FORMA DE PAGO
    $forma_pago = '----';
    if (!empty($data["BODY"]["encomienda_pago"]) && strtoupper($data["BODY"]["encomienda_pago"]) == "CREDITO") {
        $forma_pago = 'CRÉDITO';
        $estado_pago = 'PAGADO';
    } elseif ($estado_pago == "PAGADO" && !empty($data["BODY"]["forma_pago"])) {
        $forma_pago = strtoupper($data["BODY"]["forma_pago"]);
    } elseif (!empty($data["BODY"]["forma_pago"])) {
        $forma_pago = strtoupper($data["BODY"]["forma_pago"]);
    }

    // LÓGICA MÉTODO DE PAGO
    $medio_pago = '----';
    if (!empty($data["BODY"]["encomienda_pago"]) && strtoupper($data["BODY"]["encomienda_pago"]) == "CREDITO") {
        $medio_pago = 'CRÉDITO';
    } elseif ($estado_pago == "PAGADO" && !empty($data["BODY"]["medio_pago"])) {
        $medio_pago = strtoupper($data["BODY"]["medio_pago"]);
    } elseif (!empty($data["BODY"]["medio_pago"])) {
        $medio_pago = strtoupper($data["BODY"]["medio_pago"]);
    }

    $referencia = !empty(trim($data["BODY"]["obs"])) ? $data["BODY"]["obs"] : '---';

    // INFORMACIÓN DE PAGO
    $estado_pago = '';
    if (isset($data["BODY"]["encomienda_pago"])) {
        $estado_pago = $data["BODY"]["encomienda_pago"];
    } elseif (isset($data["DESTINO"]["tipo_envio"])) {
        $estado_pago = $data["DESTINO"]["tipo_envio"];
    } elseif (isset($data["BODY"]["estado"])) {
        $estado_pago = $data["BODY"]["estado"];
    }

    // LÓGICA FORMA DE PAGO
    $forma_pago = '----';
    if (!empty($data["BODY"]["encomienda_pago"]) && strtoupper($data["BODY"]["encomienda_pago"]) == "CREDITO") {
        $forma_pago = 'CRÉDITO';
        // NOTA: NO sobrescribir $estado_pago para mantener valores como "PAGO EN DESTINO"
    } elseif ($estado_pago == "PAGADO" && !empty($data["BODY"]["forma_pago"])) {
        $forma_pago = strtoupper($data["BODY"]["forma_pago"]);
    }

    // LÓGICA MÉTODO DE PAGO
    $medio_pago = '----';
    if (!empty($data["BODY"]["encomienda_pago"]) && strtoupper($data["BODY"]["encomienda_pago"]) == "CREDITO") {
        $medio_pago = 'CRÉDITO';
    } elseif ($estado_pago == "PAGADO" && !empty($data["BODY"]["medio_pago"])) {
        $medio_pago = strtoupper($data["BODY"]["medio_pago"]);
    } elseif (!empty($data["BODY"]["medio_pago"])) {
        $medio_pago = strtoupper($data["BODY"]["medio_pago"]);
    }

    $referencia = !empty(trim($data["BODY"]["obs"])) ? $data["BODY"]["obs"] : '---';

    // LÍNEA 1: ESTADO Y FORMA
    $pdf->SetFont('Helvetica', 'B', 8);

    // Guardar posición inicial
    $y_linea1 = $pdf->GetY();
    $x_inicio = $pdf->GetX();

    // --- ESTADO (columna izquierda) ---
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(12, 2, 'Estado:', 0, 0, 'L');

    // Guardar posición antes de MultiCell
    $x_estado_valor = $pdf->GetX();
    $y_estado_valor = $pdf->GetY();

    // Usar MultiCell para el valor del estado
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(30, 2, $estado_pago, 0, 'L', 0, 0);

    // Obtener la altura que ocupó el estado
    $altura_estado = $pdf->GetY() - $y_estado_valor;

    // --- FORMA (columna derecha) - Posicionar en la misma línea inicial ---
    $pdf->SetXY($x_inicio + 40, $y_linea1); // 40mm desde el inicio (ajustable)

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(10, 2, 'Forma:', 0, 0, 'L');

    $x_forma_valor = $pdf->GetX();
    $y_forma_valor = $pdf->GetY();

    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(25, 2, $forma_pago, 0, 'L', 0, 1);

    // Obtener la altura que ocupó la forma
    $altura_forma = $pdf->GetY() - $y_forma_valor;

    // Calcular la altura máxima de la primera línea
    $altura_linea1 = max($altura_estado, $altura_forma, 2);

    // Ajustar la posición Y después de la primera línea
    $pdf->SetY($y_linea1 + $altura_linea1);

    // LÍNEA 2: MÉTODO Y REFERENCIA
    $y_linea2 = $pdf->GetY();
    $x_inicio = $pdf->GetX();

    // --- MÉTODO (columna izquierda) ---
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(12, 2, 'Método:', 0, 0, 'L');

    $x_metodo_valor = $pdf->GetX();
    $y_metodo_valor = $pdf->GetY();

    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(25, 2, $medio_pago, 0, 'L', 0, 0);

    $altura_metodo = $pdf->GetY() - $y_metodo_valor;

    // --- REFERENCIA (columna derecha) ---
    $pdf->SetXY($x_inicio + 40, $y_linea2);

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->Cell(8, 2, 'Ref:', 0, 0, 'L');

    $x_ref_valor = $pdf->GetX();
    $y_ref_valor = $pdf->GetY();

    $pdf->SetFont('Helvetica', '', 8);

    // Ancho disponible para la referencia (72mm - margen derecho 1.5 - posición X)
    $ancho_ref = 72 - 1.5 - $pdf->GetX();
    $pdf->MultiCell($ancho_ref, 2, $referencia, 0, 'L', 0, 1);

    $altura_ref = $pdf->GetY() - $y_ref_valor;

    // Calcular la altura máxima de la segunda línea
    $altura_linea2 = max($altura_metodo, $altura_ref, 2);

    // Ajustar la posición Y después de la segunda línea
    $pdf->SetY($y_linea2 + $altura_linea2);

    // Agregar espacio después de la sección de pago
    $pdf->Ln(2);

    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(72, 3, 'USTED ESTA ACEPTANDO LAS CONDICIONES DE ENVIO, DEL CPE QUE SE LE ENTREGO.', 0, 'L');

    $pdf->Ln(15);

    // FIRMA Y DNI
    $pdf->Ln(0.5);
    $ancho_firma = 25;
    $espacio_entre_firmas = 4;
    $inicio_x_firmas = (72 - (2 * $ancho_firma + $espacio_entre_firmas)) / 2;

    $pdf->SetX($inicio_x_firmas);
    $pdf->Cell($ancho_firma, 0, '', 'T');
    $pdf->Cell($espacio_entre_firmas);
    $pdf->Cell($ancho_firma, 0, '', 'T');

    $pdf->Ln(0.5);
    $pdf->SetX($inicio_x_firmas);
    $pdf->SetFont('Helvetica', '', 7.5);
    $pdf->Cell($ancho_firma, 2, 'FIRMA CONSIGNADO', 0, 0, 'C');
    $pdf->Cell($espacio_entre_firmas);
    $pdf->Cell($ancho_firma, 2, 'DNI CONSIGNADO', 0, 1, 'C');

    $pdf->Ln(3);
    $pdf->SetFont('Helvetica', 'B', TEXT_SIZE_REDUCIDO);
    $pdf->MultiCell(72, 3, $titulo_final, 0, 'C');
}

if ($es_inclusion) {
    generarSeccionTransportista($pdf, $this->data, 'TRANSPORTISTA');
}