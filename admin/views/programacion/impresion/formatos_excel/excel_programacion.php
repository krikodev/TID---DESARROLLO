<?php

// Limpiar buffer de salida
ob_start();

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
    // RECIBIR VARIABLES DEL CONTROLLER
    $fecha_inicio =
        $this->fecha_inicio ?? '';

    $fecha_fin =
        $this->fecha_fin ?? '';

    $tipo_programacion =
        $this->tipo_programacion ?? '';

    $origen =
        $this->origen ?? '';

    $destino =
        $this->destino ?? '';

    $estado_programacion =
        $this->estado_programacion ?? '';

    $fecha_liquidacion =
        $this->fecha_liquidacion ?? '';

    $search =
        $this->search ?? '';

    $datos =
        $this->registros ?? [];

    $empresa =
        $this->empresa_info ?? [];

    // INFORMACIÓN DE EMPRESA
    $nombre_empresa =
        $empresa['nombre'] ??
        'EMPRESA DE TRANSPORTES';

    $ruc_empresa =
        $empresa['ruc'] ?? '';

    $direccion_empresa =
        $empresa['direccion'] ?? '';

    $telefono_empresa =
        $empresa['telefono'] ?? '';

    $logo_path =
        $empresa['logo_absolute_path'] ?? '';

    $logo_exists =
        $empresa['logo_exists'] ?? false;

    // VERIFICAR LOGO
    if (
        !empty($logo_path) &&
        file_exists($logo_path)
    ) {

        $logo_exists = true;
    } else {

        $logo_exists = false;
    }

    // CREAR SPREADSHEET
    $spreadsheet =
        new Spreadsheet();

    $sheet =
        $spreadsheet->getActiveSheet();

    $sheet->setTitle(
        'Programaciones'
    );

    // CONFIGURACIÓN DE IMPRESIÓN
    $sheet->getPageSetup()
        ->setOrientation(
            PageSetup::ORIENTATION_LANDSCAPE
        );

    $sheet->getPageSetup()
        ->setPaperSize(
            PageSetup::PAPERSIZE_A4
        );

    $sheet->getPageMargins()
        ->setTop(0.5);

    $sheet->getPageMargins()
        ->setRight(0.25);

    $sheet->getPageMargins()
        ->setLeft(0.25);

    $sheet->getPageMargins()
        ->setBottom(0.5);

    // ENCABEZADO
    $fila_actual = 1;
    $tiene_logo = false;

    // INSERTAR LOGO
    if (
        $logo_exists &&
        !empty($logo_path)
    ) {

        try {

            if (
                file_exists($logo_path) &&
                is_file($logo_path)
            ) {

                $image_info =
                    @getimagesize($logo_path);

                if (
                    $image_info !== false
                ) {

                    $drawing =
                        new Drawing();

                    $drawing->setName(
                        'Logo ' .
                            $nombre_empresa
                    );

                    $drawing->setDescription(
                        'Logo de la empresa'
                    );

                    $drawing->setPath(
                        $logo_path
                    );

                    $drawing->setHeight(60);

                    $drawing->setCoordinates(
                        'A' . $fila_actual
                    );

                    $drawing->setOffsetX(5);

                    $drawing->setOffsetY(5);

                    $drawing->setWorksheet(
                        $sheet
                    );

                    $sheet
                        ->getRowDimension(
                            $fila_actual
                        )
                        ->setRowHeight(60);

                    $sheet
                        ->getColumnDimension('A')
                        ->setWidth(18);

                    $tiene_logo = true;
                }
            }
        } catch (Exception $e) {

            $tiene_logo = false;
        }
    }

    // CONFIGURACIÓN DEL TÍTULO
    $ultima_columna = 'N';


    $titulo =
        $nombre_empresa;


    if (!empty($ruc_empresa)) {

        $titulo .=
            " - RUC: " .
            $ruc_empresa;
    }

    $titulo .=
        " - REPORTE DE PROGRAMACIONES";

    // TÍTULO CON LOGO
    if ($tiene_logo) {

        $sheet->setCellValue(
            'C1',
            $titulo
        );

        $sheet->mergeCells(
            'C1:' .
                $ultima_columna .
                '1'
        );

        $sheet
            ->getStyle('C1')
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet
            ->getStyle('C1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('C1')
            ->getAlignment()
            ->setWrapText(true);


        // Información de empresa
        $info_empresa = [];


        if (!empty($direccion_empresa)) {

            $info_empresa[] =
                $direccion_empresa;
        }


        if (!empty($telefono_empresa)) {

            $info_empresa[] =
                'Tel: ' .
                $telefono_empresa;
        }


        if (!empty($info_empresa)) {

            $sheet->setCellValue(
                'C2',
                implode(
                    ' - ',
                    $info_empresa
                )
            );

            $sheet->mergeCells(
                'C2:' .
                    $ultima_columna .
                    '2'
            );

            $sheet
                ->getStyle('C2')
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

            $fila_actual = 3;
        } else {

            $fila_actual = 2;
        }
    } else {

        // TÍTULO
        $sheet->setCellValue(
            'A1',
            $titulo
        );

        $sheet->mergeCells(
            'A1:' .
                $ultima_columna .
                '1'
        );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet
            ->getStyle('A1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('A1')
            ->getAlignment()
            ->setWrapText(true);


        $info_empresa = [];


        if (!empty($direccion_empresa)) {

            $info_empresa[] =
                $direccion_empresa;
        }


        if (!empty($telefono_empresa)) {

            $info_empresa[] =
                'Tel: ' .
                $telefono_empresa;
        }


        if (!empty($info_empresa)) {

            $sheet->setCellValue(
                'A2',
                implode(
                    ' - ',
                    $info_empresa
                )
            );

            $sheet->mergeCells(
                'A2:' .
                    $ultima_columna .
                    '2'
            );

            $sheet
                ->getStyle('A2')
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

            $fila_actual = 3;
        } else {

            $fila_actual = 2;
        }
    }

    // FECHA DE GENERACIÓN
    $sheet->setCellValue(
        'A' . $fila_actual,
        'Generado: ' .
            date('d/m/Y H:i:s')
    );

    $sheet->mergeCells(
        'A' .
            $fila_actual .
            ':' .
            $ultima_columna .
            $fila_actual
    );

    $sheet
        ->getStyle(
            'A' . $fila_actual
        )
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );

    $fila_actual++;

    $fila_actual += 1;


    // INFORMACIÓN DE FILTROS
    $sheet
        ->getColumnDimension('A')
        ->setWidth(22);

    $sheet
        ->getColumnDimension('B')
        ->setWidth(5);

    $sheet
        ->getColumnDimension('C')
        ->setWidth(35);

    // PERÍODO
    if (
        !empty($fecha_inicio) &&
        !empty($fecha_fin)
    ) {

        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'PERÍODO:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);


        $fecha_inicio_formateada =
            date(
                'd/m/Y',
                strtotime($fecha_inicio)
            );

        $fecha_fin_formateada =
            date(
                'd/m/Y',
                strtotime($fecha_fin)
            );


        $sheet->setCellValue(
            'C' . $fila_actual,
            $fecha_inicio_formateada .
                ' al ' .
                $fecha_fin_formateada
        );

        $fila_actual++;
    }


    // TIPO DE PROGRAMACIÓN
    if (!empty($tipo_programacion)) {

        $tipo_texto =
            $tipo_programacion == '1'
            ? 'PASAJES'
            : (
                $tipo_programacion == '2'
                ? 'ENCOMIENDAS'
                : ''
            );


        if (!empty($tipo_texto)) {

            $sheet->mergeCells(
                'A' .
                    $fila_actual .
                    ':B' .
                    $fila_actual
            );

            $sheet->setCellValue(
                'A' . $fila_actual,
                'TIPO PROGRAMACIÓN:'
            );

            $sheet
                ->getStyle(
                    'A' . $fila_actual
                )
                ->getFont()
                ->setBold(true);

            $sheet->setCellValue(
                'C' . $fila_actual,
                $tipo_texto
            );

            $fila_actual++;
        }
    }


    // ORIGEN
    if (!empty($origen)) {

        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'TERMINAL ORIGEN:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'C' . $fila_actual,
            $origen
        );

        $fila_actual++;
    }

    // DESTINO
    if (!empty($destino)) {

        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'TERMINAL DESTINO:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'C' . $fila_actual,
            $destino
        );

        $fila_actual++;
    }

    // ESTADO
    if ($estado_programacion !== '') {

        $estado_texto =
            $estado_programacion == '1'
            ? 'HABILITADO'
            : 'DESHABILITADO';


        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'ESTADO:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'C' . $fila_actual,
            $estado_texto
        );

        $fila_actual++;
    }

    // FECHA DE LIQUIDACIÓN
    if (!empty($fecha_liquidacion)) {

        $fecha_liquidacion_formateada =
            date(
                'd/m/Y',
                strtotime($fecha_liquidacion)
            );


        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'FECHA LIQUIDACIÓN:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'C' . $fila_actual,
            $fecha_liquidacion_formateada
        );

        $fila_actual++;
    }

    // BÚSQUEDA
    if (!empty($search)) {

        $sheet->mergeCells(
            'A' .
                $fila_actual .
                ':B' .
                $fila_actual
        );

        $sheet->setCellValue(
            'A' . $fila_actual,
            'BÚSQUEDA:'
        );

        $sheet
            ->getStyle(
                'A' . $fila_actual
            )
            ->getFont()
            ->setBold(true);

        $sheet->setCellValue(
            'C' . $fila_actual,
            $search
        );

        $fila_actual++;
    }
    $fila_actual += 2;

    // ENCABEZADOS
    $encabezados = [
        'A' => 'FECHA SALIDA',
        'B' => 'HORA SALIDA',
        'C' => 'TIPO',
        'D' => 'ORIGEN',
        'E' => 'DESTINO',
        'F' => 'VEHÍCULO',
        'G' => 'PLACA',
        'H' => 'CONDUCTOR',
        'I' => 'DOC. CONDUCTOR',
        'J' => 'PRECIO 1° PISO',
        'K' => 'PRECIO 2° PISO',
        'L' => 'ESTADO',
        'M' => 'LIQUIDACIÓN',
        'N' => 'FECHA LIQUIDACIÓN',
        'O' => 'HORA LIQUIDACIÓN'
    ];

    // ANCHOS
    $anchos_columnas = [
        'A' => 15,
        'B' => 15,
        'C' => 18,
        'D' => 28,
        'E' => 28,
        'F' => 25,
        'G' => 18,
        'H' => 30,
        'I' => 18,
        'J' => 18,
        'K' => 18,
        'L' => 18,
        'M' => 18,
        'N' => 18,
        'O' => 15
    ];

    foreach (
        $anchos_columnas
        as $columna => $ancho
    ) {
        $sheet
            ->getColumnDimension($columna)
            ->setWidth($ancho);
    }


    // ============================================
    // ESTILO DE ENCABEZADOS
    // ============================================

    $estilo_encabezado = [

        'font' => [

            'bold' => true,

            'color' => [
                'rgb' => 'FFFFFF'
            ]
        ],

        'fill' => [

            'fillType' =>
            Fill::FILL_SOLID,

            'startColor' => [
                'rgb' => '2E86C1'
            ]
        ],

        'borders' => [

            'allBorders' => [

                'borderStyle' =>
                Border::BORDER_THIN
            ]
        ],

        'alignment' => [

            'horizontal' =>
            Alignment::HORIZONTAL_CENTER,

            'vertical' =>
            Alignment::VERTICAL_CENTER,

            'wrapText' => true
        ]
    ];


    foreach (
        $encabezados
        as $columna => $titulo
    ) {

        $sheet->setCellValue(
            $columna .
                $fila_actual,
            $titulo
        );

        $sheet
            ->getStyle(
                $columna .
                    $fila_actual
            )
            ->applyFromArray(
                $estilo_encabezado
            );
    }


    $sheet
        ->getRowDimension($fila_actual)
        ->setRowHeight(30);


    $fila_encabezados =
        $fila_actual;

    $fila_actual++;


    // ============================================
    // DATOS
    // ============================================

    if (!empty($datos)) {

        foreach ($datos as $row) {

            // ========================================
            // FECHA SALIDA
            // ========================================

            $fecha_salida =
                $row['fecha_salida'] ?? '';

            if (!empty($fecha_salida)) {

                $fecha_salida =
                    date(
                        'd/m/Y',
                        strtotime($fecha_salida)
                    );
            }

            $sheet->setCellValue(
                'A' . $fila_actual,
                $fecha_salida
            );


            // ========================================
            // HORA SALIDA
            // ========================================

            $hora_salida =
                $row['hora_salida_format'] ?? '';

            $sheet->setCellValue(
                'B' . $fila_actual,
                $hora_salida
            );


            // ========================================
            // TIPO
            // ========================================

            $sheet->setCellValue(
                'C' . $fila_actual,
                $row['tipo_programacion_nombre'] ?? ''
            );


            // ========================================
            // ORIGEN
            // ========================================

            $sheet->setCellValue(
                'D' . $fila_actual,
                $row['terminal_origen_nombre'] ?? ''
            );


            // ========================================
            // DESTINO
            // ========================================

            $sheet->setCellValue(
                'E' . $fila_actual,
                $row['terminal_destino_nombre'] ?? ''
            );


            // ========================================
            // VEHÍCULO
            // ========================================

            $sheet->setCellValue(
                'F' . $fila_actual,
                $row['vehiculo_descripcion'] ?? ''
            );


            // ========================================
            // PLACA
            // ========================================

            $sheet->setCellValue(
                'G' . $fila_actual,
                $row['vehiculo_placa'] ?? ''
            );


            // ========================================
            // CONDUCTOR
            // ========================================

            $sheet->setCellValue(
                'H' . $fila_actual,
                $row['conductor_nombres'] ?? ''
            );


            // ========================================
            // DOCUMENTO CONDUCTOR
            // ========================================

            $sheet->setCellValue(
                'I' . $fila_actual,
                $row['conductor_num_docu'] ?? ''
            );


            // ========================================
            // PRECIO PRIMER PISO
            // ========================================

            $sheet->setCellValue(
                'J' . $fila_actual,
                floatval(
                    $row['precio_primer_piso'] ?? 0
                )
            );


            // ========================================
            // PRECIO SEGUNDO PISO
            // ========================================

            $sheet->setCellValue(
                'K' . $fila_actual,
                floatval(
                    $row['precio_segundo_piso'] ?? 0
                )
            );


            // ========================================
            // ESTADO
            // ========================================

            $sheet->setCellValue(
                'L' . $fila_actual,
                $row['estado_nombre'] ?? ''
            );


            // ========================================
            // LIQUIDACIÓN
            // ========================================

            $sheet->setCellValue(
                'M' . $fila_actual,
                $row['liquidado_nombre'] ?? ''
            );


            // ========================================
            // FECHA LIQUIDACIÓN
            // ========================================

            $fecha_liquidacion_row =
                $row['fecha_liquidacion'] ?? '';

            if (!empty($fecha_liquidacion_row)) {

                $fecha_liquidacion_row =
                    date(
                        'd/m/Y',
                        strtotime($fecha_liquidacion_row)
                    );
            }

            $sheet->setCellValue(
                'N' . $fila_actual,
                $fecha_liquidacion_row
            );

            // HORA LIQUIDACIÓN
            $sheet->setCellValue(
                'O' . $fila_actual,
                $row['hora_liquidacion'] ?? ''
            );

            // FORMATO MONETARIO
            $sheet
                ->getStyle(
                    'J' .
                        $fila_actual .
                        ':K' .
                        $fila_actual
                )
                ->getNumberFormat()
                ->setFormatCode(
                    NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
                );

            // WRAP TEXT
            foreach (
                ['C', 'D', 'E', 'F', 'H', 'L', 'M', 'O']
                as $columna
            ) {

                $sheet
                    ->getStyle(
                        $columna .
                            $fila_actual
                    )
                    ->getAlignment()
                    ->setWrapText(true);
            }

            // BORDES
            $sheet->getStyle('A' . $fila_actual . ':O' . $fila_actual)
                ->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                            Border::BORDER_THIN
                        ]
                    ]
                ]);
            $fila_actual++;
        }

        // FECHA DE LIQUIDACIÓN
        if (!empty($fecha_liquidacion)) {
        }
    } else {
        // SIN DATOS
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    // AUTO-FILTRO
    $sheet->setAutoFilter('A' . $fila_encabezados . ':O' . ($fila_actual - 1));

    // CONGELAR ENCABEZADOS
    $sheet->freezePane('A' . ($fila_encabezados + 1));

    // CONFIGURAR PÁGINA
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getPageSetup()->setHorizontalCentered(false);

    // PREPARAR NOMBRE DEL ARCHIVO
    $fecha_inicio_str = $fecha_inicio ? str_replace('-', '', $fecha_inicio) : 'todo';
    $fecha_fin_str = $fecha_fin ? str_replace('-', '', $fecha_fin) : 'todo';
    $filename = "reporte_programaciones_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

    // PREPARAR DESCARGA
    ob_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
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
        'message' =>
        'Error al generar reporte Excel: ' .
            $e->getMessage()
    ]);
    exit;
}
