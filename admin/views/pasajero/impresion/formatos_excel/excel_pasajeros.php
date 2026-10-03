<?php

ob_start();

require __DIR__ . '/../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

try {

    $datos = $this->registros ?? [];
    $empresa = $this->empresa_info ?? [];

    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA DE TRANSPORTES';
    $ruc_empresa = $empresa['ruc'] ?? '';
    $direccion_empresa = $empresa['direccion'] ?? '';
    $telefono_empresa = $empresa['telefono'] ?? '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setTitle('Pasajeros');

    // Configuración de impresión
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

    $sheet->getPageMargins()->setTop(0.5);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setBottom(0.5);

    // ============================================
    // ENCABEZADO
    // ============================================

    $ultima_columna = 'K';

    $titulo = $nombre_empresa;

    if (!empty($ruc_empresa)) {
        $titulo .= " - RUC: $ruc_empresa";
    }

    $titulo .= " - REPORTE DE PASAJEROS";

    $sheet->setCellValue('A1', $titulo);
    $sheet->mergeCells('A1:' . $ultima_columna . '1');

    $sheet->getStyle('A1')->getFont()
        ->setBold(true)
        ->setSize(14);

    $sheet->getStyle('A1')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);

    $fila_actual = 2;

    // Información de empresa
    $info_empresa = [];

    if (!empty($direccion_empresa)) {
        $info_empresa[] = $direccion_empresa;
    }

    if (!empty($telefono_empresa)) {
        $info_empresa[] = 'Tel: ' . $telefono_empresa;
    }

    if (!empty($info_empresa)) {

        $sheet->setCellValue(
            'A' . $fila_actual,
            implode(' - ', $info_empresa)
        );

        $sheet->mergeCells(
            'A' . $fila_actual . ':' . $ultima_columna . $fila_actual
        );

        $sheet->getStyle('A' . $fila_actual)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $fila_actual++;
    }

    // Fecha de generación
    $sheet->setCellValue(
        'A' . $fila_actual,
        'Generado: ' . date('d/m/Y H:i:s')
    );

    $sheet->mergeCells(
        'A' . $fila_actual . ':' . $ultima_columna . $fila_actual
    );

    $sheet->getStyle('A' . $fila_actual)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila_actual += 2;

    // ============================================
    // ENCABEZADOS
    // ============================================

    $encabezados = [
        'A' => 'TERMINAL',
        'B' => 'PERSONAL',
        'C' => 'GÉNERO',
        'D' => 'TIPO DOCUMENTO',
        'E' => 'NRO. DOCUMENTO',
        'F' => 'FECHA NACIMIENTO',
        'G' => 'NACIONALIDAD',
        'H' => 'CELULAR',
        'I' => 'DIRECCIÓN',
        'J' => 'UBIGEO',
        'K' => 'ESTADO'
    ];

    $estilo_encabezado = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2E86C1']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ]
    ];

    foreach ($encabezados as $columna => $titulo_columna) {

        $sheet->setCellValue(
            $columna . $fila_actual,
            $titulo_columna
        );

        $sheet->getStyle(
            $columna . $fila_actual
        )->applyFromArray($estilo_encabezado);
    }

    $sheet->getRowDimension($fila_actual)->setRowHeight(30);

    $fila_actual++;

    // ============================================
    // ANCHOS
    // ============================================

    $anchos_columnas = [
        'A' => 22,
        'B' => 35,
        'C' => 15,
        'D' => 22,
        'E' => 18,
        'F' => 18,
        'G' => 18,
        'H' => 15,
        'I' => 35,
        'J' => 15,
        'K' => 18
    ];

    foreach ($anchos_columnas as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    // ============================================
    // DATOS
    // ============================================

    if (!empty($datos)) {

        foreach ($datos as $row) {

            $sheet->setCellValue(
                'A' . $fila_actual,
                $row['terminal'] ?? ''
            );

            $sheet->setCellValue(
                'B' . $fila_actual,
                trim(
                    ($row['nombres'] ?? '') . ' ' .
                        ($row['apellidos'] ?? '')
                )
            );

            $sheet->setCellValue(
                'C' . $fila_actual,
                $row['genero'] ?? ''
            );

            $sheet->setCellValue(
                'D' . $fila_actual,
                $row['tp_docu'] ?? ''
            );

            $sheet->setCellValue(
                'E' . $fila_actual,
                $row['num_docu'] ?? ''
            );

            $sheet->setCellValue(
                'F' . $fila_actual,
                $row['fecha_nacimiento'] ?? ''
            );

            $sheet->setCellValue(
                'G' . $fila_actual,
                $row['nacionalidad'] ?? ''
            );

            $sheet->setCellValue(
                'H' . $fila_actual,
                $row['celular'] ?? 'S/N'
            );

            $sheet->setCellValue(
                'I' . $fila_actual,
                $row['direccion'] ?? ''
            );

            $sheet->setCellValue(
                'J' . $fila_actual,
                $row['ubigeo'] ?? ''
            );

            $estado_texto = ($row['estado'] ?? 0)
                ? 'Habilitado'
                : 'Deshabilitado';

            $sheet->setCellValue(
                'K' . $fila_actual,
                $estado_texto
            );

            // Wrap text
            foreach (['A', 'B', 'D', 'G', 'I'] as $columna) {

                $sheet->getStyle(
                    $columna . $fila_actual
                )->getAlignment()->setWrapText(true);
            }

            // Bordes
            $sheet->getStyle(
                'A' . $fila_actual . ':K' . $fila_actual
            )->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ]);

            $fila_actual++;
        }
    } else {

        $sheet->setCellValue(
            'A' . $fila_actual,
            'No se encontraron registros para los filtros seleccionados'
        );

        $sheet->mergeCells(
            'A' . $fila_actual . ':K' . $fila_actual
        );

        $sheet->getStyle('A' . $fila_actual)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $fila_actual)
            ->getFont()
            ->setItalic(true);
    }

    // Auto-filtro
    $fila_encabezados = $fila_actual -
        (empty($datos) ? 0 : count($datos)) - 1;

    $sheet->setAutoFilter(
        'A' . $fila_encabezados . ':K' . ($fila_actual - 1)
    );

    // ============================================
    // DESCARGA
    // ============================================

    $filename = "pasajeros_" . date('Ymd_His') . ".xlsx";

    ob_clean();

    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    header(
        'Content-Disposition: attachment;filename="' . $filename . '"'
    );

    header('Cache-Control: max-age=0');

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
