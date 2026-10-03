<?php
// Limpiar buffer de salida
ob_start();

// Incluir PhpSpreadsheet
require __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    // ACCEDER A LAS VARIABLES A TRAVÉS DE $this
    $fecha_inicio = $this->fecha_inicio ?? '';
    $fecha_fin = $this->fecha_fin ?? '';
    $tipo = $this->tipo ?? '';
    $origen = $this->origen ?? '';
    $destino = $this->destino ?? '';
    
    $datos = $this->registros ?? [];
    $empresa = $this->empresa_info ?? [];
    $terminales = $this->terminales ?? [];
    
    // Verificar si hay datos
    if (empty($datos)) {
        error_log("¡ADVERTENCIA! No se encontraron registros con los filtros aplicados");
    }

    // Extraer datos de empresa con nombres correctos
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa = $empresa['ruc'] ?? '';
    $direccion_empresa = $empresa['direccion'] ?? '';
    $telefono_empresa = $empresa['telefono'] ?? '';
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    
    // Verificación adicional del logo
    if (!empty($logo_path) && file_exists($logo_path)) {
        $logo_exists = true;
        error_log("Logo verificado en ruta: " . $logo_path);
    } else {
        $logo_exists = false;
        error_log("Logo NO encontrado en ruta: " . $logo_path);
    }
    
    error_log("Nombre empresa: " . $nombre_empresa);
    error_log("RUC: " . $ruc_empresa);
    error_log("Logo path: " . $logo_path);
    error_log("Logo exists: " . ($logo_exists ? 'SÍ' : 'NO'));

    // Crear Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Comprobantes');

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
    $tiene_logo = false;

    // Intentar insertar logo si existe
    if ($logo_exists && !empty($logo_path)) {
        try {
            // Verificar que el archivo sea una imagen válida
            if (file_exists($logo_path) && is_file($logo_path)) {
                $image_info = @getimagesize($logo_path);
                if ($image_info !== false) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo ' . $nombre_empresa);
                    $drawing->setDescription('Logo de la empresa');
                    $drawing->setPath($logo_path);
                    $drawing->setHeight(60); // Aumentado un poco para mejor visibilidad
                    $drawing->setCoordinates('A' . $fila_actual);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);

                    $sheet->getRowDimension($fila_actual)->setRowHeight(60);
                    $sheet->getColumnDimension('A')->setWidth(18); // Un poco más ancho
                    $tiene_logo = true;
                    error_log("Logo insertado correctamente en Excel");
                } else {
                    error_log("El archivo no es una imagen válida: " . $logo_path);
                }
            } else {
                error_log("El archivo no existe o no es accesible: " . $logo_path);
            }
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    }

    // Título del reporte
    $ultima_columna = 'P';

    // Título principal
    $titulo = $nombre_empresa;
    if (!empty($ruc_empresa)) {
        $titulo .= " - RUC: $ruc_empresa";
    }
    $titulo .= " - REPORTE DE COMPROBANTES (FACTURAS Y BOLETAS)";

    if ($tiene_logo) {
        // Con logo - el título empieza en columna C
        $sheet->setCellValue('C1', $titulo);
        $sheet->mergeCells('C1:' . $ultima_columna . '1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C1')->getAlignment()->setWrapText(true);

        // Información adicional de la empresa
        $info_empresa = [];
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;
        if (!empty($telefono_empresa)) $info_empresa[] = 'Tel: ' . $telefono_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:' . $ultima_columna . '2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        // Sin logo - el título empieza en columna A
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:' . $ultima_columna . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);

        $info_empresa = [];
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;
        if (!empty($telefono_empresa)) $info_empresa[] = 'Tel: ' . $telefono_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:' . $ultima_columna . '2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':' . $ultima_columna . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $fila_actual++;
    $fila_actual += 1; // Espacio

    // ============================================
    // INFORMACIÓN DE FILTROS
    // ============================================
    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    // Período
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        $fecha_inicio_formateada = date('d/m/Y', strtotime($fecha_inicio));
        $fecha_fin_formateada = date('d/m/Y', strtotime($fecha_fin));
        $sheet->setCellValue('C' . $fila_actual, $fecha_inicio_formateada . ' al ' . $fecha_fin_formateada);
        $fila_actual++;
    }

    // Tipo de comprobante
    if (!empty($tipo)) {
        $tipo_texto = $tipo == '1' ? 'FACTURA' : ($tipo == '3' ? 'BOLETA' : '');
        if (!empty($tipo_texto)) {
            $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
            $sheet->setCellValue('A' . $fila_actual, 'TIPO COMPROBANTE:');
            $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
            $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C' . $fila_actual, $tipo_texto);
            $fila_actual++;
        }
    }

    // Terminal Origen - Necesitas pasar los nombres desde el controlador
    // O crear una función helper simple aquí
    if (!empty($origen)) {
        $nombre_origen = '';
        foreach ($terminales as $term) {
            if ($term['id_terminal'] == $origen) {
                $nombre_origen = $term['nombre'];
                break;
            }
        }
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen ?: $origen);
        $fila_actual++;
    }

    // Terminal Destino
    if (!empty($destino)) {
        $nombre_destino = '';
        foreach ($terminales as $term) {
            if ($term['id_terminal'] == $destino) {
                $nombre_destino = $term['nombre'];
                break;
            }
        }
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino ?: $destino);
        $fila_actual++;
    }

    $fila_actual += 2; // Espacio antes de la tabla

    // ============================================
    // ENCABEZADOS DE LA TABLA
    // ============================================
    $encabezados = [
        'A' => 'N° COMPROBANTE',
        'B' => 'FECHA EMISIÓN',
        'C' => 'REMITENTE',
        'D' => 'DOC. REMITENTE',
        'E' => 'DESTINATARIO',
        'F' => 'DOC. DESTINATARIO',
        'G' => 'ORIGEN',
        'H' => 'DESTINO',
        'I' => 'FECHA SALIDA',
        'J' => 'ESTADO ENVÍO',
        'K' => 'ESTADO SUNAT',
        'L' => 'T. GRAVADO',
        'M' => 'T. EXONERADO',
        'N' => 'T. INAFECTO',
        'O' => 'IGV',
        'P' => 'TOTAL'
    ];

    $anchos_columnas = [
        'A' => 18, // N° COMPROBANTE
        'B' => 15, // FECHA EMISIÓN
        'C' => 35, // REMITENTE
        'D' => 18, // DOC. REMITENTE
        'E' => 35, // DESTINATARIO
        'F' => 18, // DOC. DESTINATARIO
        'G' => 25, // ORIGEN
        'H' => 25, // DESTINO
        'I' => 15, // FECHA SALIDA
        'J' => 15, // ESTADO ENVÍO
        'K' => 15, // ESTADO SUNAT
        'L' => 12, // T. GRAVADO
        'M' => 12, // T. EXONERADO
        'N' => 12, // T. INAFECTO
        'O' => 10, // IGV
        'P' => 12  // TOTAL
    ];

    // Aplicar anchos de columna
    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    // Aplicar estilos a encabezados
    $estilo_encabezado = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E86C1']],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER]
    ];

    foreach ($encabezados as $columna => $titulo) {
        $sheet->setCellValue($columna . $fila_actual, $titulo);
        $sheet->getStyle($columna . $fila_actual)->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(30);
    $fila_actual++;

    // ============================================
    // LLENAR DATOS DE LA TABLA
    // ============================================
    $total_gravado = 0;
    $total_exonerado = 0;
    $total_inafecto = 0;
    $total_igv = 0;
    $total_general = 0;

    $columnas_wrap_text = ['C', 'E', 'G', 'H']; // REMITENTE, DESTINATARIO, ORIGEN, DESTINO

    if (!empty($datos)) {
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila_actual, $row['numero_comprobante'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $row['fecha_emision'] ?? '');
            $sheet->setCellValue('C' . $fila_actual, ($row['remitente_nombres'] ?? '') . ' ' . ($row['remitente_apellidos'] ?? ''));
            $sheet->setCellValue('D' . $fila_actual, $row['remitente_num_docu'] ?? '');
            $sheet->setCellValue('E' . $fila_actual, ($row['destinatario_nombres'] ?? '') . ' ' . ($row['destinatario_apellidos'] ?? ''));
            $sheet->setCellValue('F' . $fila_actual, $row['destinatario_num_docu'] ?? '');
            $sheet->setCellValue('G' . $fila_actual, $row['terminal_origen'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $row['terminal_destino'] ?? '');
            $sheet->setCellValue('I' . $fila_actual, $row['fecha_salida'] ?? '');
            $sheet->setCellValue('J' . $fila_actual, $row['estado_envio'] ?? '');
            $sheet->setCellValue('K' . $fila_actual, $row['estado_sunat'] ?? '');
            $sheet->setCellValue('L' . $fila_actual, $row['op_gravada'] ?? 0);
            $sheet->setCellValue('M' . $fila_actual, $row['op_exonerada'] ?? 0);
            $sheet->setCellValue('N' . $fila_actual, $row['op_inafecta'] ?? 0);
            $sheet->setCellValue('O' . $fila_actual, $row['op_igv'] ?? 0);
            $sheet->setCellValue('P' . $fila_actual, $row['total'] ?? 0);

            // Formato numérico
            $sheet->getStyle('L' . $fila_actual . ':P' . $fila_actual)
                  ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

            // Wrap text para columnas largas
            foreach ($columnas_wrap_text as $columna) {
                $sheet->getStyle($columna . $fila_actual)->getAlignment()->setWrapText(true);
            }

            // Bordes
            $sheet->getStyle('A' . $fila_actual . ':P' . $fila_actual)
                  ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            // Acumular totales
            $total_gravado += floatval($row['op_gravada'] ?? 0);
            $total_exonerado += floatval($row['op_exonerada'] ?? 0);
            $total_inafecto += floatval($row['op_inafecta'] ?? 0);
            $total_igv += floatval($row['op_igv'] ?? 0);
            $total_general += floatval($row['total'] ?? 0);

            $fila_actual++;
        }

        // Fila de totales
        $sheet->setCellValue('K' . $fila_actual, 'TOTALES:');
        $sheet->getStyle('K' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('L' . $fila_actual, $total_gravado);
        $sheet->setCellValue('M' . $fila_actual, $total_exonerado);
        $sheet->setCellValue('N' . $fila_actual, $total_inafecto);
        $sheet->setCellValue('O' . $fila_actual, $total_igv);
        $sheet->setCellValue('P' . $fila_actual, $total_general);

        $sheet->getStyle('L' . $fila_actual . ':P' . $fila_actual)
              ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $sheet->getStyle('L' . $fila_actual . ':P' . $fila_actual)->getFont()->setBold(true);
        
        $sheet->getStyle('K' . $fila_actual . ':P' . $fila_actual)
              ->applyFromArray([
                  'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
              ]);
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':P' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // Auto-filtro
    $fila_encabezados = $fila_actual - (empty($datos) ? 0 : count($datos)) - 1;
    $sheet->setAutoFilter('A' . $fila_encabezados . ':P' . ($fila_actual - 1));

    // ============================================
    // PREPARAR DESCARGA
    // ============================================
    $fecha_inicio_str = $fecha_inicio ? str_replace('-', '', $fecha_inicio) : 'todo';
    $fecha_fin_str = $fecha_fin ? str_replace('-', '', $fecha_fin) : 'todo';
    $filename = "comprobantes_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

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
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte Excel: ' . $e->getMessage()
    ]);
    exit;
}