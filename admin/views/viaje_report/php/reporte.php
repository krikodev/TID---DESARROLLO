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
    error_log("=== REPORTE VIAJES - Parámetros recibidos ===");
    error_log("Terminal Origen: " . $this->view->terminal_origen);
    error_log("Terminal Destino: " . $this->view->terminal_destino);
    error_log("Tipo Servicio: " . $this->view->tipo_servicio);
    error_log("Fecha Inicio: " . $this->view->fecha_inicio);
    error_log("Fecha Fin: " . $this->view->fecha_fin);

    // Obtener datos del modelo - ACTUALIZADO CON NUEVOS PARÁMETROS
    $datos = $this->model->getDatosExportacion(
        $this->view->fecha_inicio,
        $this->view->fecha_fin,
        $this->view->terminal_origen,    // Nuevo parámetro
        $this->view->terminal_destino,   // Nuevo parámetro
        $this->view->tipo_servicio
    );

    // Obtener información de la empresa (logo, nombre, etc.)
    $empresa = $this->model->getEmpresa();
    $logo_path = $empresa['logo'] ?? '';
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA';
    $ruc_empresa = $empresa['num_docu'] ?? '';

    // DEBUG - Verificar datos
    error_log("Datos obtenidos: " . count($datos) . " registros");
    error_log("Datos empresa: " . $nombre_empresa);
    error_log("Logo path: " . $logo_path);

    // Crear Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('ReporteViajes');

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

    // Obtener información de la empresa (logo, nombre, etc.)
    $empresa = $this->model->getEmpresa();
    $logo_path = $empresa['logo_absolute_path'] ?? '';
    $logo_exists = $empresa['logo_exists'] ?? false;
    $nombre_empresa = $empresa['nombre'] ?? 'EMPRESA';
    $ruc_empresa = $empresa['num_docu'] ?? '';

    // DEBUG - Verificar información del logo
    error_log("Logo path: " . $logo_path);
    error_log("Logo existe: " . ($logo_exists ? 'SÍ' : 'NO'));
    error_log("Nombre empresa: " . $nombre_empresa);

    // Agregar logo
    $logoRow = 1;
    $tiene_logo = false;

    if ($logo_exists && !empty($logo_path)) {
        try {
            $drawing = new Drawing();
            $drawing->setName('LogoEmpresa');
            $drawing->setDescription('Logo de la empresa');
            $drawing->setPath($logo_path);
            $drawing->setHeight(50); // Altura en pixels
            $drawing->setCoordinates('A' . $logoRow);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);

            // Ajustar altura de la fila del logo
            $sheet->getRowDimension($logoRow)->setRowHeight(50);
            $sheet->getColumnDimension('A')->setWidth(15); // Ancho para la columna del logo

            $tiene_logo = true;
            error_log("Logo insertado correctamente: " . $logo_path);

        } catch (Exception $e) {
            error_log("Error al insertar logo: " . $e->getMessage());
            $tiene_logo = false;
        }
    } else {
        error_log("Logo no disponible. Path: " . $logo_path);
        $tiene_logo = false;
    }

    // Título general - ACTUALIZADO PARA 21 COLUMNAS (T->U)
    if ($tiene_logo) {
        // Si hay logo, el título va desde la columna C
        $sheet->setCellValue('C1', $nombre_empresa . ' - REPORTE DE VIAJES - DETALLE DE PASAJES');
        $sheet->mergeCells('C1:U1'); // Cambiado a U (21 columnas)
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
            $sheet->mergeCells('C2:U2'); // Cambiado a U
            $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    } else {
        // Si no hay logo, título centrado completo
        $sheet->setCellValue('A1', $nombre_empresa . ' - REPORTE DE VIAJES - DETALLE DE PASAJES');
        $sheet->mergeCells('A1:U1'); // Cambiado a U
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Información de la empresa
        $info_empresa = [];
        if (!empty($ruc_empresa))
            $info_empresa[] = 'RUC: ' . $ruc_empresa;
        if (!empty($empresa['direccion']))
            $info_empresa[] = $empresa['direccion'];
        if (!empty($empresa['telefono']))
            $info_empresa[] = 'Tel: ' . $empresa['telefono'];

        if (!empty($info_empresa)) {
            $sheet->setCellValue('A2', implode(' - ', $info_empresa));
            $sheet->mergeCells('A2:U2'); // Cambiado a U
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $fila_actual = 3;
        } else {
            $fila_actual = 2;
        }
    }

    // Fecha de generación
    $sheet->setCellValue('A' . $fila_actual, 'Generado: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A' . $fila_actual . ':U' . $fila_actual); // Cambiado a U
    $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $fila_actual++;

    $fila_actual += 1; // Espacio después del encabezado

    // ============================================
    // INFORMACIÓN DE FILTROS - MODIFICADO PARA COLUMNAS FUSIONADAS A-B
    // ============================================

    // Función para obtener nombre de terminal por ID
    function getNombreTerminal($id_terminal, $terminales)
    {
        if (!$id_terminal)
            return '';
        foreach ($terminales['message'] ?? [] as $terminal) {
            if ($terminal['id_terminal'] == $id_terminal) {
                return $terminal['nombre'];
            }
        }
        return $id_terminal; // Si no se encuentra, devolver el ID
    }

    // Obtener terminales para mostrar nombres en lugar de IDs
    $terminales_data = $this->model->getTerminales();

    // Configurar ancho de columna A y B para los filtros
    $sheet->getColumnDimension('A')->setWidth(20); // Ancho para etiquetas de filtros
    $sheet->getColumnDimension('B')->setWidth(5);  // Ancho pequeño para dos puntos
    $sheet->getColumnDimension('C')->setWidth(40); // Ancho para valores de filtros

    // Período
    if ($this->view->fecha_inicio && $this->view->fecha_fin) {
        // Fusionar columnas A y B para la etiqueta
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'PERÍODO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Valor en columna C
        $sheet->setCellValue('C' . $fila_actual, $this->view->fecha_inicio . ' al ' . $this->view->fecha_fin);
        $fila_actual++;
    }

    // Terminal Origen - MEJORADO: mostrar nombre en lugar de ID
    if ($this->view->terminal_origen) {
        $nombre_origen = getNombreTerminal($this->view->terminal_origen, $terminales_data);
        
        // Fusionar columnas A y B para la etiqueta
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL ORIGEN:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Valor en columna C
        $sheet->setCellValue('C' . $fila_actual, $nombre_origen);
        $fila_actual++;
    }

    // Terminal Destino - MEJORADO: mostrar nombre en lugar de ID
    if ($this->view->terminal_destino) {
        $nombre_destino = getNombreTerminal($this->view->terminal_destino, $terminales_data);
        
        // Fusionar columnas A y B para la etiqueta
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TERMINAL DESTINO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Valor en columna C
        $sheet->setCellValue('C' . $fila_actual, $nombre_destino);
        $fila_actual++;
    }

    // Tipo de servicio - MEJORADO: mostrar descripción en lugar de ID
    if ($this->view->tipo_servicio) {
        $tipos_servicio = $this->model->getTiposServicioPasaje();
        $nombre_servicio = '';
        foreach ($tipos_servicio as $servicio) {
            if ($servicio['id_tp_servicio_pasaje'] == $this->view->tipo_servicio) {
                $nombre_servicio = $servicio['descripcion'];
                break;
            }
        }
        
        // Fusionar columnas A y B para la etiqueta
        $sheet->mergeCells('A' . $fila_actual . ':B' . $fila_actual);
        $sheet->setCellValue('A' . $fila_actual, 'TIPO SERVICIO:');
        $sheet->getStyle('A' . $fila_actual)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Valor en columna C
        $sheet->setCellValue('C' . $fila_actual, $nombre_servicio ?: $this->view->tipo_servicio);
        $fila_actual++;
    }

    // Restaurar anchos de columnas A, B para la tabla principal
    $sheet->getColumnDimension('A')->setWidth(12); // ASIENTO
    $sheet->getColumnDimension('B')->setWidth(18); // N° DOCUMENTO
    
    $fila_actual += 2; // Espacio antes de la tabla

    // ============================================
    // ENCABEZADOS DE LA TABLA - ACTUALIZADO PARA SEPARAR MARCA Y MODELO
    // ============================================

    $encabezados = [
        'A' => 'ASIENTO',
        'B' => 'N° DOCUMENTO',
        'C' => 'FECHA EMISIÓN',
        'D' => 'FECHA/HORA SALIDA',
        'E' => 'CLIENTE',
        'F' => 'DOCUMENTO CLIENTE',
        'G' => 'TIPO DOCUMENTO',
        'H' => 'T. GRAVADO',
        'I' => 'T. EXONERADO',
        'J' => 'T. INAFECTO',
        'K' => 'IGV',
        'L' => 'TOTAL',
        'M' => 'ESTADO',
        'N' => 'ESTADO SUNAT',
        'O' => 'ORIGEN',
        'P' => 'DESTINO',
        'Q' => 'MARCA',      // COLUMNA SEPARADA PARA MARCA
        'R' => 'MODELO',     // COLUMNA SEPARADA PARA MODELO
        'S' => 'PLACA',
        'T' => 'TIPO SERVICIO',
        'U' => 'PASAJERO'    // Cambiado de T a U
    ];

    // CONFIGURACIÓN DE ANCHOS DE COLUMNA - ACTUALIZADO
    $anchos_columnas = [
        'A' => 12,  // ASIENTO
        'B' => 18,  // N° DOCUMENTO
        'C' => 15,  // FECHA EMISIÓN
        'D' => 20,  // FECHA/HORA SALIDA
        'E' => 40,  // CLIENTE (ligeramente reducido por espacio)
        'F' => 18,  // DOCUMENTO CLIENTE
        'G' => 15,  // TIPO DOCUMENTO
        'H' => 12,  // T. GRAVADO
        'I' => 12,  // T. EXONERADO
        'J' => 12,  // T. INAFECTO
        'K' => 10,  // IGV
        'L' => 12,  // TOTAL
        'M' => 12,  // ESTADO
        'N' => 20,  // ESTADO SUNAT
        'O' => 25,  // ORIGEN
        'P' => 25,  // DESTINO
        'Q' => 20,  // MARCA
        'R' => 20,  // MODELO
        'S' => 12,  // PLACA
        'T' => 15,  // TIPO SERVICIO
        'U' => 40   // PASAJERO (ligeramente reducido por espacio)
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

    // ESPECIALMENTE PARA ENCABEZADOS LARGOS, AJUSTAR ALTURA
    $sheet->getRowDimension($fila_actual)->setRowHeight(35);

    // ============================================
    // LLENAR DATOS DE LA TABLA - ACTUALIZADO PARA SEPARAR MARCA Y MODELO
    // ============================================

    $fila_actual++;
    $total_gravado = 0;
    $total_exonerado = 0;
    $total_inafecto = 0;
    $total_igv = 0;
    $total_general = 0;

    if (!empty($datos)) {
        error_log("Procesando " . count($datos) . " registros para Excel");

        // Array de columnas que requieren wrap text (textos largos)
        $columnas_wrap_text = ['E', 'O', 'P', 'Q', 'R', 'U']; // CLIENTE, ORIGEN, DESTINO, MARCA, MODELO, PASAJERO

        foreach ($datos as $registro) {
            $sheet->setCellValue('A' . $fila_actual, $registro['asiento'] ?? '');
            $sheet->setCellValue('B' . $fila_actual, $registro['numero_documento'] ?? '');

            // Formatear fecha de emisión
            $fecha_emision = '';
            if (!empty($registro['fecha_emision'])) {
                try {
                    $fecha_emision = date('d/m/Y', strtotime($registro['fecha_emision']));
                } catch (Exception $e) {
                    $fecha_emision = $registro['fecha_emision'];
                }
            }
            $sheet->setCellValue('C' . $fila_actual, $fecha_emision);

            $sheet->setCellValue('D' . $fila_actual, $registro['fecha_hora_salida'] ?? '');

            // COLUMNAS CON TEXTO LARGO - APLICAR WRAP TEXT
            $sheet->setCellValue('E' . $fila_actual, $registro['cliente'] ?? '');  // CLIENTE

            $sheet->setCellValue('F' . $fila_actual, $registro['documento_cliente'] ?? '');
            $sheet->setCellValue('G' . $fila_actual, $registro['tipo_documento'] ?? '');
            $sheet->setCellValue('H' . $fila_actual, $registro['op_gravada'] ?? 0);
            $sheet->setCellValue('I' . $fila_actual, $registro['op_exonerada'] ?? 0);
            $sheet->setCellValue('J' . $fila_actual, $registro['op_inafecta'] ?? 0);
            $sheet->setCellValue('K' . $fila_actual, $registro['op_igv'] ?? 0);
            $sheet->setCellValue('L' . $fila_actual, $registro['op_total'] ?? 0);
            $sheet->setCellValue('M' . $fila_actual, $registro['estado'] ?? '');
            $sheet->setCellValue('N' . $fila_actual, $registro['estado_sunat'] ?? '');

            // Separar origen y destino
            $ruta = $registro['ruta'] ?? '';
            $partes = explode(' - ', $ruta);
            $sheet->setCellValue('O' . $fila_actual, $partes[0] ?? ''); // ORIGEN
            $sheet->setCellValue('P' . $fila_actual, $partes[1] ?? ''); // DESTINO

            // Extraer marca y modelo del campo vehiculo
            $vehiculo_completo = $registro['vehiculo'] ?? '';
            $placa = $registro['placa'] ?? '';
            
            // Inicializar variables para marca y modelo
            $marca = '';
            $modelo = '';
            
            // Intentar separar marca y modelo de diferentes formas
            if (!empty($vehiculo_completo)) {
                // Formato común: "Marca Modelo - Placa" o "Marca - Modelo - Placa"
                if (strpos($vehiculo_completo, ' - ') !== false) {
                    $partes_vehiculo = explode(' - ', $vehiculo_completo);
                    
                    // Si tenemos al menos 2 partes (marca/modelo y placa)
                    if (count($partes_vehiculo) >= 2) {
                        $marca_modelo_completo = $partes_vehiculo[0];
                        $placa_excel = $partes_vehiculo[1];  // Placa
                        
                        // Intentar separar marca y modelo dentro del primer segmento
                        // Buscar el primer espacio que separa marca de modelo
                        $primer_espacio = strpos($marca_modelo_completo, ' ');
                        if ($primer_espacio !== false) {
                            $marca = substr($marca_modelo_completo, 0, $primer_espacio);
                            $modelo = substr($marca_modelo_completo, $primer_espacio + 1);
                        } else {
                            // Si no hay espacio, poner todo en marca
                            $marca = $marca_modelo_completo;
                            $modelo = '';
                        }
                    } else {
                        $marca = $vehiculo_completo;
                        $modelo = '';
                        $placa_excel = $placa;
                    }
                } else {
                    // Si no hay separador, intentar separar por espacio
                    $primer_espacio = strpos($vehiculo_completo, ' ');
                    if ($primer_espacio !== false) {
                        $marca = substr($vehiculo_completo, 0, $primer_espacio);
                        $modelo = substr($vehiculo_completo, $primer_espacio + 1);
                    } else {
                        $marca = $vehiculo_completo;
                        $modelo = '';
                    }
                    $placa_excel = $placa;
                }
            } else {
                $placa_excel = $placa;
            }

            // Asignar valores a las columnas separadas
            $sheet->setCellValue('Q' . $fila_actual, $marca);      // MARCA
            $sheet->setCellValue('R' . $fila_actual, $modelo);     // MODELO
            $sheet->setCellValue('S' . $fila_actual, $placa_excel);  // PLACA

            $sheet->setCellValue('T' . $fila_actual, $registro['tipo_servicio'] ?? '');
            $sheet->setCellValue('U' . $fila_actual, $registro['pasajero'] ?? ''); // PASAJERO

            // Formato numérico para montos
            $sheet->getStyle('H' . $fila_actual . ':L' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');

            // APLICAR WRAP TEXT A COLUMNAS CON TEXTO LARGO
            foreach ($columnas_wrap_text as $columna) {
                $sheet->getStyle($columna . $fila_actual)->getAlignment()->setWrapText(true);
            }

            // Bordes para cada fila
            $sheet->getStyle('A' . $fila_actual . ':U' . $fila_actual)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            // Ajustar altura de fila automáticamente basado en contenido
            $textos_largos = [
                $registro['cliente'] ?? '',
                $partes[0] ?? '',  // origen
                $partes[1] ?? '',  // destino
                $marca,
                $modelo,
                $registro['pasajero'] ?? ''
            ];

            // Calcular si necesita más altura
            $max_lineas = 1;
            foreach ($textos_largos as $texto) {
                if (strlen($texto) > 40) { // Reducido a 40 por tener columnas más estrechas
                    $lineas = ceil(strlen($texto) / 40);
                    if ($lineas > $max_lineas) {
                        $max_lineas = $lineas;
                    }
                }
            }

            // Ajustar altura si hay textos largos
            if ($max_lineas > 1) {
                $altura_fila = 15 * $max_lineas;
                $sheet->getRowDimension($fila_actual)->setRowHeight($altura_fila);
            }

            // Acumular totales
            $total_gravado += floatval($registro['op_gravada'] ?? 0);
            $total_exonerado += floatval($registro['op_exonerada'] ?? 0);
            $total_inafecto += floatval($registro['op_inafecta'] ?? 0);
            $total_igv += floatval($registro['op_igv'] ?? 0);
            $total_general += floatval($registro['op_total'] ?? 0);

            $fila_actual++;
        }

        // Fila de totales - ACTUALIZADA A COLUMNA G (la 7ma columna)
        $sheet->setCellValue('G' . $fila_actual, 'TOTALES:');
        $sheet->getStyle('G' . $fila_actual)->getFont()->setBold(true);
        $sheet->setCellValue('H' . $fila_actual, $total_gravado);
        $sheet->setCellValue('I' . $fila_actual, $total_exonerado);
        $sheet->setCellValue('J' . $fila_actual, $total_inafecto);
        $sheet->setCellValue('K' . $fila_actual, $total_igv);
        $sheet->setCellValue('L' . $fila_actual, $total_general);

        // Formato para totales
        $sheet->getStyle('H' . $fila_actual . ':L' . $fila_actual)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H' . $fila_actual . ':L' . $fila_actual)->getFont()->setBold(true);

        // Bordes para fila de totales
        $sheet->getStyle('G' . $fila_actual . ':L' . $fila_actual)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2']
            ]
        ]);

    } else {
        // No hay datos
        error_log("No se encontraron datos para los filtros");
        $sheet->setCellValue('A' . $fila_actual, 'No se encontraron registros para los filtros seleccionados');
        $sheet->mergeCells('A' . $fila_actual . ':U' . $fila_actual); // Cambiado a U
        $sheet->getStyle('A' . $fila_actual)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $fila_actual)->getFont()->setItalic(true);
    }

    // Ajustar altura de fila para encabezados
    $fila_encabezados = $fila_actual - (empty($datos) ? 0 : count($datos)) - 1;
    $sheet->getRowDimension($fila_encabezados)->setRowHeight(25);

    // Ajustar altura de fila del logo si existe
    if ($tiene_logo) {
        $sheet->getRowDimension(1)->setRowHeight(50);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
    }

    // HABILITAR AUTO-FILTRO PARA MEJOR EXPERIENCIA DE USUARIO
    if (!empty($datos)) {
        $sheet->setAutoFilter('A' . $fila_encabezados . ':U' . ($fila_actual - 1)); // Cambiado a U
    }

    // ============================================
    // PREPARAR DESCARGA
    // ============================================

    $fecha_inicio_str = $this->view->fecha_inicio ? str_replace('-', '', $this->view->fecha_inicio) : 'todo';
    $fecha_fin_str = $this->view->fecha_fin ? str_replace('-', '', $this->view->fecha_fin) : 'todo';
    $filename = "reporte_viajes_{$fecha_inicio_str}_{$fecha_fin_str}.xlsx";

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