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
    // ============================================
    // LAS VARIABLES VIENEN DIRECTAMENTE DEL EXTRACT EN EL CONTROLADOR
    // ============================================
    
    $datos = isset($datos) ? $datos : [];
    $empresa = isset($empresa) ? $empresa : [];
    $fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
    $fecha_fin = isset($fecha_fin) ? $fecha_fin : '';

    // DEBUG
    error_log("=== REPORTE RESUMENES - Vista Excel ===");
    error_log("Fecha Inicio: " . $fecha_inicio);
    error_log("Fecha Fin: " . $fecha_fin);
    error_log("Cantidad de datos: " . (is_array($datos) ? count($datos) : 0));

    // ============================================
    // CONSTRUIR RUTA DEL LOGO
    // ============================================
    
    $logo_path = '';
    $logo_exists = false;
    
    if (!empty($empresa) && is_array($empresa) && !empty($empresa['logo'])) {
        $rutas_posibles = [
            $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/admin/public/' . $empresa['logo'],
            __DIR__ . '/../../../public/' . $empresa['logo'],
            __DIR__ . '/../../../../public/' . $empresa['logo']
        ];
        
        foreach ($rutas_posibles as $ruta) {
            if (file_exists($ruta)) {
                $logo_path = $ruta;
                $logo_exists = true;
                break;
            }
        }
    }
    
    $nombre_empresa = (!empty($empresa) && is_array($empresa) && !empty($empresa['nombre'])) ? $empresa['nombre'] : 'EMPRESA';
    $ruc_empresa = (!empty($empresa) && is_array($empresa)) ? ($empresa['num_docu'] ?? '') : '';
    $direccion_empresa = (!empty($empresa) && is_array($empresa)) ? ($empresa['direccion'] ?? '') : '';

    // ============================================
    // CREAR SPREADSHEET
    // ============================================
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resumenes');

    // Configuración para impresión
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    // ============================================
    // ENCABEZADO CON LOGO E INFORMACIÓN DE EMPRESA
    // ============================================

    $fila_actual = 1;

    // Agregar logo si existe
    $tiene_logo = false;
    if ($logo_exists && !empty($logo_path)) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa');
            $drawing->setDescription('Logo de la empresa');
            $drawing->setPath($logo_path);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A' . $fila_actual);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);

            $sheet->getRowDimension($fila_actual)->setRowHeight(50);
            $sheet->getColumnDimension('A')->setWidth(15);
            $tiene_logo = true;
        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    }

    // Título del reporte
    if ($tiene_logo) {
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE RESÚMENES');
        $sheet->mergeCells('C1:G1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('C2', implode(' - ', $info_empresa));
            $sheet->mergeCells('C2:G2');
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE RESÚMENES');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info_empresa = [];
        if (!empty($ruc_empresa)) $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($direccion_empresa)) $info_empresa[] = $direccion_empresa;

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':G' . $fila_actual);
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $fila_actual++;

    $fila_actual += 1;

    // ============================================
    // INFORMACIÓN DE FILTROS APLICADOS
    // ============================================

    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(5);
    $sheet->getColumnDimension('C')->setWidth(40);

    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        
        $fecha_inicio_formateada = date('d/m/Y', strtotime($fecha_inicio));
        $fecha_fin_formateada = date('d/m/Y', strtotime($fecha_fin));
        $sheet->setCellValue('C' . $fila_actual, $fecha_inicio_formateada . ' al ' . $fecha_fin_formateada);
        $fila_actual++;
    }

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS DE LA TABLA
    // ============================================

    $encabezados = [
        'A' => 'FECHA ENVÍO',
        'B' => 'FECHA REFERENCIA',
        'C' => 'IDENTIFICADOR',
        'D' => 'TICKET',
        'E' => 'MENSAJE SUNAT',
        'F' => 'CÓDIGO SUNAT',
        'G' => 'ESTADO'
    ];

    $anchos_columnas = [
        'A' => 15,
        'B' => 15,
        'C' => 25,
        'D' => 20,
        'E' => 40,
        'F' => 15,
        'G' => 15
    ];

    // Aplicar anchos de columna
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

    $sheet->getRowDimension($fila_actual)->setRowHeight(30);
    $fila_actual++;

    // ============================================
    // LLENAR DATOS
    // ============================================

    if (!empty($datos) && is_array($datos)) {
        foreach ($datos as $registro) {
            if (!is_array($registro)) {
                continue;
            }
            
            // Fecha envío
            $fecha_envio = !empty($registro['fecha_envio']) ? 
                date('d/m/Y', strtotime($registro['fecha_envio'])) : '';
            $sheet->setCellValue('A' . $fila_actual, $fecha_envio);
            
            // Fecha referencia
            $fecha_referencia = !empty($registro['fecha_referencia']) ? 
                date('d/m/Y', strtotime($registro['fecha_referencia'])) : '';
            $sheet->setCellValue('B' . $fila_actual, $fecha_referencia);
            
            $sheet->setCellValue('C' . $fila_actual, $registro['identificador'] ?? $registro['nombre_xml'] ?? '---');
            $sheet->setCellValue('D' . $fila_actual, $registro['ticket'] ?? '---');
            $sheet->setCellValue('E' . $fila_actual, $registro['mensaje_sunat'] ?? '---');
            $sheet->setCellValue('F' . $fila_actual, $registro['codigo_sunat'] ?? '---');
            
            // Estado basado en código SUNAT
            $estado = $registro['estado'] ?? '';
            if (empty($estado) && isset($registro['codigo_sunat'])) {
                $estado = ($registro['codigo_sunat'] == '0') ? 'ACEPTADO' : 'PENDIENTE';
            }
            $sheet->setCellValue('G' . $fila_actual, $estado);

            // Wrap text para mensaje SUNAT
            $sheet->getStyle('E' . $fila_actual)->getAlignment()->setWrapText(true);

            // Bordes para cada fila
            $sheet->getStyle('A' . $fila_actual . ':G' . $fila_actual)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $fila_actual++;
        }
    } else {
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':G' . $fila_actual);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // ============================================
    // PREPARAR DESCARGA
    // ============================================

    $fecha_actual = date('Ymd_His');
    $filename = "resumenes_{$fecha_actual}.xlsx";

    // Limpiar buffer antes de enviar headers
    ob_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Error en exportación Excel Resumenes: " . $e->getMessage());
    
    ob_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte Excel: ' . $e->getMessage()
    ]);
    exit;
}
?>