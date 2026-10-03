<?php
// Limpiar buffer de salida
ob_start();

// Incluir PhpSpreadsheet
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // DEBUG SIMPLE - Verificar parámetros
    error_log("=== REPORTE GUÍAS TRANSPORTISTA - Parámetros recibidos ===");
    error_log("Fecha Inicio: " . ($this->view->fecha_inicio ?? 'vacío'));
    error_log("Fecha Fin: " . ($this->view->fecha_fin ?? 'vacío'));
    error_log("Estado: " . ($this->view->estado ?? 'vacío'));

    // Obtener datos desde la vista (pasados por el controlador)
    $datos = $this->view->datos ?? [];
    $empresa = $this->view->empresa ?? [];
    
    // DEBUG - Verificar datos
    error_log("Datos obtenidos: " . count($datos) . " registros");

    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTE';
    $ruc_empresa = $empresa['num_docu'] ?? '';

    // Crear Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('GuiasTransportista');

    // Configuración para impresión
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setTop(0.5);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setBottom(0.5);

    // ============================================
    // ENCABEZADO CON LOGO E INFORMACIÓN DE EMPRESA
    // ============================================

    $fila_actual = 1;

    // Agregar logo
    $logoRow = 1;
    $tiene_logo = false;

    if ($logo_exists && !empty($logo_path) && file_exists($logo_path)) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa');
            $drawing->setDescription('Logo de la empresa');
            $drawing->setPath($logo_path);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A' . $logoRow);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);

            $sheet->getRowDimension($logoRow)->setRowHeight(50);
            $sheet->getColumnDimension('A')->setWidth(15);

            $tiene_logo = true;
            error_log("Logo insertado correctamente: " . $logo_path);
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    } else {
        error_log("Logo no disponible o no existe: " . $logo_path);
    }

    // Título general - 11 columnas (A a K)
    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE GUÍAS TRANSPORTISTA');
        $sheet->mergeCells('C1:K1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Información de la empresa debajo del título
        $info_empresa = [];
        if (!empty($ruc_empresa))
            $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($empresa['direccion']))
            $info_empresa[] = $empresa['direccion'];
        if (!empty($empresa['telefono']))
            $info_empresa[] = 'Tel: ' . $empresa['telefono'];

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:K2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE GUÍAS TRANSPORTISTA');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa))
            $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($empresa['direccion']))
            $info_empresa[] = $empresa['direccion'];
        if (!empty($empresa['telefono']))
            $info_empresa[] = 'Tel: ' . $empresa['telefono'];

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:K2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':K' . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $fila_actual++;

    $fila_actual += 1;

    // ============================================
    // INFORMACIÓN DE FILTROS
    // ============================================

    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    // Período
    if (!empty($this->view->fecha_inicio) && !empty($this->view->fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        $sheet->setCellValue('C' . $fila_actual, $this->view->fecha_inicio . ' al ' . $this->view->fecha_fin);
        $fila_actual++;
    }

    // Estado
    if (!empty($this->view->estado)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'ESTADO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        $estado_texto = '';
        switch ($this->view->estado) {
            case 'PAGADO':
                $estado_texto = 'Pagado';
                break;
            case 'PENDIENTE':
                $estado_texto = 'Pendiente';
                break;
            case 'ANULADO':
                $estado_texto = 'Anulado';
                break;
            default:
                $estado_texto = $this->view->estado;
        }
        $sheet->setCellValue('C' . $fila_actual, $estado_texto);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE LA TABLA
    // ============================================

    $encabezados = [
        'A' => 'F. EMISIÓN',
        'B' => 'CLIENTE',
        'C' => 'DOCUMENTO',
        'D' => 'N° GUÍA',
        'E' => 'MONEDA',
        'F' => 'TOTAL',
        'G' => 'ESTADO',
        'H' => 'COMPROBANTE PAGO',
        'I' => 'PROGRAMACIÓN',
        'J' => 'TIPO SERVICIO',
        'K' => 'VEHÍCULO'
    ];

    // CONFIGURACIÓN DE ANCHOS DE COLUMNA
    $anchos_columnas = [
        'A' => 12,  // F. EMISIÓN
        'B' => 40,  // CLIENTE
        'C' => 18,  // DOCUMENTO
        'D' => 18,  // N° GUÍA
        'E' => 8,   // MONEDA
        'F' => 12,  // TOTAL
        'G' => 12,  // ESTADO
        'H' => 18,  // COMPROBANTE PAGO
        'I' => 20,  // PROGRAMACIÓN
        'J' => 20,  // TIPO SERVICIO
        'K' => 25   // VEHÍCULO
    ];

    // APLICAR ANCHOS DE COLUMNA
    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    // Aplicar estilos a encabezados
    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);

        $estilo_encabezado = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
        ];

        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(35);

    // ============================================
    // LLENAR DATOS DE LA TABLA
    // ============================================

    $fila_actual++;
    $total_general = 0;

    if (!empty($datos)) {
        error_log("Procesando " . count($datos) . " registros para Excel");

        $columnas_wrap_text = ['B', 'I', 'J', 'K']; // CLIENTE, PROGRAMACIÓN, TIPO SERVICIO, VEHÍCULO

        foreach ($datos as $registro) {
            // Formatear fecha de emisión
            $fecha_emision = '';
            if (!empty($registro['fecha_emision'])) {
                try {
                    $fecha_emision = date('d/m/Y', strtotime($registro['fecha_emision']));
                } catch (Exception $e) {
                    $fecha_emision = $registro['fecha_emision'];
                }
            }
            $sheet->setCellValue('A' . $fila_actual, $fecha_emision);

            // CLIENTE (con wrap text)
            $cliente_nombre = trim(($registro['cliente_nombres'] ?? '') . ' ' . ($registro['cliente_apellidos'] ?? ''));
            $sheet->setCellValue('B' . $fila_actual, $cliente_nombre);

            // DOCUMENTO CLIENTE
            $sheet->setCellValue('C' . $fila_actual, $registro['cliente_num_docu'] ?? '');

            // N° GUÍA
            $numero_guia = '';
            if (!empty($registro['serie']) && !empty($registro['correlativo'])) {
                $numero_guia = $registro['serie'] . '-' . $registro['correlativo'];
            }
            $sheet->setCellValue('D' . $fila_actual, $numero_guia);

            // MONEDA
            $sheet->setCellValue('E' . $fila_actual, $registro['tp_moneda_codigo'] ?? '');

            // TOTAL
            $total = floatval($registro['total'] ?? 0);
            $sheet->setCellValue('F' . $fila_actual, $total);
            $total_general += $total;

            // ESTADO
            $sheet->setCellValue('G' . $fila_actual, $registro['estado'] ?? '');

            // COMPROBANTE PAGO
            $sheet->setCellValue('H' . $fila_actual, $registro['pago_e'] ?? '');

            // PROGRAMACIÓN (fecha y hora)
            $programacion = '';
            if (!empty($registro['programacion_fecha_salida'])) {
                $programacion = $registro['programacion_fecha_salida'];
                if (!empty($registro['programacion_hora_salida'])) {
                    $programacion .= ' ' . $registro['programacion_hora_salida'];
                }
            }
            $sheet->setCellValue('I' . $fila_actual, $programacion);

            // TIPO SERVICIO
            $sheet->setCellValue('J' . $fila_actual, $registro['tp_servicio'] ?? '');

            // VEHÍCULO
            $vehiculo_info = '';
            if (!empty($registro['marca']) || !empty($registro['modelo'])) {
                $vehiculo_info = trim(($registro['marca'] ?? '') . ' ' . ($registro['modelo'] ?? ''));
                if (!empty($registro['placa'])) {
                    $vehiculo_info .= ' - ' . $registro['placa'];
                }
            } elseif (!empty($registro['placa'])) {
                $vehiculo_info = $registro['placa'];
            }
            $sheet->setCellValue('K' . $fila_actual, $vehiculo_info);

            // Formato numérico para total
            $sheet->getStyle('F' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');

            // APLICAR WRAP TEXT A COLUMNAS CON TEXTO LARGO
            foreach ($columnas_wrap_text as $columna) {
                $sheet->getStyle($columna . $fila_actual)->getAlignment()->setWrapText(true);
            }

            // Bordes para cada fila
            $sheet->getStyle('A' . $fila_actual . ':K' . $fila_actual)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            // Ajustar altura de fila automáticamente
            $textos_largos = [
                $cliente_nombre,
                $programacion,
                $registro['tp_servicio'] ?? '',
                $vehiculo_info
            ];

            $max_lineas = 1;
            foreach ($textos_largos as $texto) {
                if (strlen($texto) > 40) {
                    $lineas = ceil(strlen($texto) / 40);
                    if ($lineas > $max_lineas) {
                        $max_lineas = $lineas;
                    }
                }
            }

            if ($max_lineas > 1) {
                $altura_fila = 15 * $max_lineas;
                $sheet->getRowDimension($fila_actual)->setRowHeight($altura_fila);
            }

            $fila_actual++;
        }

        // Fila de totales
        $sheet->setCellValue('E' . $fila_actual, 'TOTAL:');
        $sheet->getStyle('E' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('E' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        $sheet->setCellValue('F' . $fila_actual, $total_general);
        $sheet->getStyle('F' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F' . $fila_actual)->getFont()->setBold(true);

        // Bordes para fila de totales
        $sheet->getStyle('E' . $fila_actual . ':F' . $fila_actual)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
        ]);

    } else {
        // No hay datos
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':K' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // HABILITAR AUTO-FILTRO
    if (!empty($datos)) {
        $fila_encabezados = $fila_actual - count($datos) - 1;
        $sheet->setAutoFilter('A' . $fila_encabezados . ':K' . ($fila_actual - 1));
    }

    // ============================================
    // PREPARAR DESCARGA
    // ============================================

    $fecha_inicio_str = !empty($this->view->fecha_inicio) ? str_replace('-', '', $this->view->fecha_inicio) : 'todo';
    $fecha_fin_str = !empty($this->view->fecha_fin) ? str_replace('-', '', $this->view->fecha_fin) : 'todo';
    $filename = "guias_transportista_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    // Limpiar buffer antes de enviar headers
    ob_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Limpiar buffer en caso de error
    ob_clean();

    // En caso de error, devolver JSON con error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte Excel: ' . $e->getMessage()
    ]);
    exit;
}