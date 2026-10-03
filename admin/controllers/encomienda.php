<?php

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorSVG;

class Encomienda extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->terminal_destino = $this->model->get_allTerminalDestino();
        $this->view->css = ["encomienda/css/main.css"];
        $this->view->js = [
            "encomienda/js/ctg_encomienda.js",
            "encomienda/js/reporte.js",
            "encomienda/js/pasajero.js",
            "encomienda/js/programacion_salida.js",
            "encomienda/js/main.js"
        ];
        $this->view->php = ["encomienda/php/modal.php"];
        $this->view->render("encomienda/index");
    }


    public function get_dataTable()
    {
        echo json_encode($this->model->get_dataTable($_POST));
    }

    public function crud_encomienda()
    {
        if ($_POST["id_venta"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function get_dataTableReenvio()
    {
        $this->model->get_dataTableReenvio($_POST);
    }

    public function get_dataTableGuias()
    {
        echo json_encode($this->model->get_dataTableGuias($_POST));
    }

    public function get_dataTableEmbarcaciones()
    {
        echo json_encode($this->model->get_dataTableEmbarcaciones($_POST));
    }

    public function get_dataTableEmbarcacionesGrupales()
    {
        echo json_encode($this->model->get_dataTableEmbarcacionesGrupales($_POST));
    }

    public function get_dataTableDesembarques()
    {
        echo json_encode($this->model->get_dataTableDesembarques($_POST));
    }

    public function get_dataTableResumen()
    {
        $this->model->get_dataTableResumen();
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/comprobante/comprobante', true);
                break;
            case 'nota_venta':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/nota_venta/nota_venta', true);
                break;
            case 'transportista':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/transportista', true);
                break;
            case 'archivo':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/archivo', true);
                break;
            case 'comprobanteA4':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/formatos_A4/comprobanteA4', true);
                break;
            case 'nota_ventaA4':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('encomienda/impresion/formatos_A4/nota_ventaA4', true);
                break;
            case 'embarque':
                if (!isset($data[1]) || !is_numeric($data[1])) {
                    die("Error: ID de programación debe ser un valor numérico válido.");
                }
                $id_embarcacion = $this->model->getIdEmbarcacionByProgramacion($data[1]);
                if (!$id_embarcacion) {
                    die("Error: No se encontró una embarcación asociada a la programación {$data[1]}");
                }
                $this->view->data = $this->model->getDataReporteEmbarqueH($id_embarcacion);
                $this->view->render('encomienda/impresion/embarque', true);
                break;
            case 'embarque_ruta':
                $this->view->data = $this->model->getDataReporteEmbarqueRuta($data[1]);
                $this->view->render('encomienda/impresion/embarque', true);
                break;
            case 'embarque_grupal':
                $id_programacion = $data[1];
                $id_grupo_embarcacion = $data[2];
                $this->view->data = $this->model->getDataReporteEmbarqueGrupal($id_grupo_embarcacion, $id_programacion);
                // echo json_encode($this->view->data);
                // exit;
                $this->view->render('encomienda/impresion/embarque_grupal', true);
                break;
            case 'embarqueH':
                if (!isset($data[1]) || !is_numeric($data[1])) {
                    die("Error: ID de embarcación debe ser un valor numérico válido.");
                }
                $this->view->data = $this->model->getDataReporteEmbarqueH($data[1]);
                $this->view->render('encomienda/impresion/embarque', true);
                break;
            case 'desembarque':
                $this->view->data = $this->model->getDataReporteDesembarque($data[1]);
                $this->view->render('encomienda/impresion/desembarque', true);
                break;
            case 'desembarqueH':
                $this->view->data = $this->model->getDataReporteDesembarqueH($data[1]);
                $this->view->render('encomienda/impresion/desembarqueH', true);
            case 'reportes':
                if ($data[1] == "general") {
                    if (!isset($data[3]) && !isset($data[4])) {
                        echo "Ha ocurrido un error rellene los datos correctamente para generar el reporte.";
                        exit;
                    }
                    $this->view->data = $this->model->getDataReporteGeneral($data[2], $data[3], $data[4]);
                    $this->view->render('encomienda/impresion/reportes/general', true);
                } else if ($data[1] == "origen_destino") {
                    $this->view->data = $this->model->getDataReporteOrigenDestino($data[2]);
                    $this->view->render('encomienda/impresion/reportes/origen_destino', true);
                } else if ($data[1] == "vehiculo") {
                    $this->view->data = $this->model->getDataReporteVehiculo($data[2]);
                    $this->view->render('encomienda/impresion/reportes/vehiculo', true);
                } else if ($data[1] == "cliente") {
                    $this->view->data = $this->model->getDataReporteCliente($data[2], $data[3]);
                    if ($this->view->data["HEADER"]) {
                        $this->view->render('encomienda/impresion/reportes/cliente', true);
                    }
                }
                break;
            case 'guia_remision':
                $this->view->data = $this->model->get_data_guia($data[1]);
                $this->view->render('encomienda/impresion/grt_ticket', true);
                break;
            case 'guia_remisionA4':
                $this->view->data = $this->model->get_data_guia($data[1]);
                $this->view->render('encomienda/impresion/guia_remision', true);
                break;
            case 'guia_grupal':
                $this->view->data = $this->model->get_data_guia_grupal($data[1]);
                $this->view->render('encomienda/impresion/guia_grupal', true);
                break;
            case 'rotulo':

                $respuesta = $this->model->get_data_rotulo($data[1]);

                if (!$respuesta["success"]) die($respuesta["message"]);

                $info = $respuesta["data"];

                // ── Barcode por cada rótulo ────────────────────────
                $generator = new BarcodeGeneratorSVG();

                foreach ($info["rotulos"] as $index => &$rotulo) {
                    $posicion         = $index + 1;
                    $codigo           = $info["numero"] . "-" . $posicion;
                    $rotulo["codigo"] = $codigo;
                    $rotulo["barcode"] = $generator->getBarcode(
                        $codigo,
                        $generator::TYPE_CODE_128
                    );
                    $rotulo["posicion"] = $posicion;
                }
                unset($rotulo); // limpia referencia del foreach

                // ── QR ────────────────────────────────────────────
                $textoQR  = "N: {$info["numero"]}\n";
                $textoQR .= "Tracking: {$info["tracking"]}\n";
                $textoQR .= "Origen: {$info["origen"]}\n";
                $textoQR .= "Destino: {$info["destino"]}\n";
                $textoQR .= "Destinatario: {$info["destinatario"]}\n";

                ob_start();
                QRcode::png($textoQR, null, QR_ECLEVEL_L, 5);
                $info["qr"] = "data:image/png;base64," . base64_encode(ob_get_clean());

                $this->view->data = ["success" => true, "data" => $info];
                $this->view->render('encomienda/impresion/rotulo', true);

                break;
            default:
                break;
        }
    }

    // Agrega estos métodos al final de la clase Encomienda en encomienda.php

    public function exportar_excel_comprobante()
    {
        // Asignar directamente a $this->view
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->tipo = $_GET['tipo'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->estado_envio = $_GET['estado_envio'] ?? '';
        $this->view->estado_venta = $_GET['estado_venta'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        // Obtener datos
        $this->view->registros = $this->model->getDataExportComprobante(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->tipo,
            $this->view->origen,
            $this->view->destino,
            $this->view->estado_envio,
            $this->view->estado_venta,
            $this->view->search
        );

        // Información adicional
        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->terminales = $this->model->getTerminalesParaFiltro();

        // Renderizar
        $this->view->render('encomienda/impresion/formatos_excel/excel_comprobante', true);
    }

    public function exportar_excel_notaventa()
    {
        // Asignar directamente a $this->view
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->estado_envio = $_GET['estado_envio'] ?? '';
        $this->view->estado_venta = $_GET['estado_venta'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        // Obtener datos
        $this->view->data = $this->model->getDataExportNotaVenta(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->origen,
            $this->view->destino,
            $this->view->estado_envio,
            $this->view->estado_venta,
            $this->view->search
        );

        // Información adicional
        $this->view->empresa_info = $this->model->getEmpresaInfo();

        $this->view->nombre_origen = '';
        $this->view->nombre_destino = '';

        if (!empty($this->view->origen)) {
            $this->view->nombre_origen = $this->model->getNombreTerminal($this->view->origen);
        }

        if (!empty($this->view->destino)) {
            $this->view->nombre_destino = $this->model->getNombreTerminal($this->view->destino);
        }
        // Renderizar
        $this->view->render('encomienda/impresion/formatos_excel/excel_notaventa', true);
    }

    public function exportar_excel_guiasT()
    {
        try {
            // Obtener filtros
            $fecha_inicio = $_GET['fecha_inicio'] ?? '';
            $fecha_fin = $_GET['fecha_fin'] ?? '';
            $origen = $_GET['origen'] ?? '';
            $destino = $_GET['destino'] ?? '';
            $estado_envio = $_GET['estado_envio'] ?? '';
            $estado_venta = $_GET['estado_venta'] ?? '';
            $search = trim($_GET['search'] ?? '');

            // Obtener datos principales
            $data = $this->model->getDataExportGuiasT(
                $fecha_inicio, 
                $fecha_fin, 
                $origen, 
                $destino, 
                $estado_envio, 
                $estado_venta, 
                $search
            );

            // Obtener información de empresa
            $empresa_info = $this->model->getEmpresaInfo();

            // Obtener nombres de terminales para los filtros
            $terminales_info = [];
            if (!empty($origen)) {
                $terminales_info['origen'] = $this->model->getNombreTerminal($origen);
            }
            if (!empty($destino)) {
                $terminales_info['destino'] = $this->model->getNombreTerminal($destino);
            }

            // PASAR LOS DATOS COMO PROPIEDADES DE VIEW
            $this->view->data = $data;
            $this->view->empresa_info = $empresa_info;
            $this->view->terminales_info = $terminales_info;
            $this->view->fecha_inicio = $fecha_inicio;
            $this->view->fecha_fin = $fecha_fin;
            $this->view->origen = $origen;
            $this->view->destino = $destino;
            $this->view->estado_envio = $estado_envio;
            $this->view->estado_venta = $estado_venta;
            $this->view->search = $search;

            // Incluir la vista directamente en lugar de usar render
            $vista_path = 'views/encomienda/impresion/formatos_excel/excel_guiasT.php';

            // Verificar que la vista existe
            if (!file_exists($vista_path)) {
                $vista_path = __DIR__ . '/../views/encomienda/impresion/formatos_excel/excel_guiasT.php';
            }

            if (!file_exists($vista_path)) {
                throw new Exception("No se encontró la vista: " . $vista_path);
            }

            // Incluir la vista - las variables de $this->view estarán disponibles como $this->view->propiedad
            include $vista_path;
            exit;
        } catch (Exception $e) {
            error_log("Error en exportar_excel_guiasT: " . $e->getMessage());
            echo "Error al generar el reporte: " . $e->getMessage();
        }
    }

    public function exportar_excel_embarcaciones()
    {
        // Asignar parámetros
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->id_vehiculo = $_GET['id_vehiculo'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        // DEBUG 1: Parámetros que llegan
        error_log("fecha_inicio: " . $this->view->fecha_inicio);
        error_log("fecha_fin: " . $this->view->fecha_fin);
        error_log("origen: " . $this->view->origen);
        error_log("destino: " . $this->view->destino);

        // Obtener datos del modelo
        $this->view->data = $this->model->getDataExportEmbarcaciones(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->origen,
            $this->view->destino,
            $this->view->id_vehiculo,
            $this->view->search
        );

        // DEBUG 2: Resultados del modelo
        error_log("Registros encontrados: " . count($this->view->data));

        // DEBUG 3: Verificar datos sin filtros
        $todos_los_datos = $this->model->getDataExportEmbarcaciones('', '', '', '', '');
        error_log("Total de registros sin filtros: " . count($todos_los_datos));

        // Obtener información de empresa
        $this->view->empresa_info = $this->model->getEmpresaInfo();

        // Resolver nombres de terminales
        $this->view->nombre_origen = '';
        if (!empty($this->view->origen)) {
            $this->view->nombre_origen = $this->model->getNombreTerminal($this->view->origen);
        }

        $this->view->nombre_destino = '';
        if (!empty($this->view->destino)) {
            $this->view->nombre_destino = $this->model->getNombreTerminal($this->view->destino);
        }

        // DEBUG 4: Verificar que la vista existe
        $vista_path = 'views/encomienda/impresion/formatos_excel/excel_embarcaciones.php';
        if (file_exists($vista_path)) {
            error_log("Vista encontrada en: " . $vista_path);
        } else {
            error_log("VISTA NO ENCONTRADA en: " . $vista_path);
        }

        $this->view->render('encomienda/impresion/formatos_excel/excel_embarcaciones', true);
    }

    public function exportar_excel_embarcacionGrupal()
    {
        // Asignar parámetros de filtro
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->id_vehiculo = $_GET['id_vehiculo'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        // Obtener TODOS los datos en el controlador
        $this->view->data = $this->model->getDataExportEmbarcacionGrupal(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->origen,
            $this->view->destino,
            $this->view->id_vehiculo,
            $this->view->search
        );

        // Obtener información de empresa (desde el modelo, pero asignada a la vista)
        $this->view->empresa_info = $this->model->getEmpresaInfo();

        // Resolver nombres de terminales AQUÍ, no en la vista
        $this->view->nombre_origen = '';
        if (!empty($this->view->origen)) {
            $this->view->nombre_origen = $this->model->getNombreTerminal($this->view->origen);
        }

        $this->view->nombre_destino = '';
        if (!empty($this->view->destino)) {
            $this->view->nombre_destino = $this->model->getNombreTerminal($this->view->destino);
        }

        // Renderizar la vista
        $this->view->render('encomienda/impresion/formatos_excel/excel_embarcacionGrupal', true);
    }

    public function exportar_excel_desembarque()
    {
        // Asignar parámetros de filtro
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->id_vehiculo = $_GET['id_vehiculo'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        // Obtener datos del modelo
        $this->view->data = $this->model->getDataExportDesembarque(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->origen,
            $this->view->destino,
            $this->view->id_vehiculo,
            $this->view->search
        );

        // Obtener información de empresa
        $this->view->empresa_info = $this->model->getEmpresaInfo();

        // IMPORTANTE: Resolver nombres de terminales AQUÍ en el controlador
        $this->view->nombre_origen = '';
        if (!empty($this->view->origen)) {
            $this->view->nombre_origen = $this->model->getNombreTerminal($this->view->origen);
        }

        $this->view->nombre_destino = '';
        if (!empty($this->view->destino)) {
            $this->view->nombre_destino = $this->model->getNombreTerminal($this->view->destino);
        }

        // Obtener terminales para filtros (opcional, si los necesitas)
        $this->view->terminales = $this->model->getTerminalesParaFiltro();

        // Renderizar la vista
        $this->view->render('encomienda/impresion/formatos_excel/excel_desembarque', true);
    }

    public function exportar_excel_guiasRT()
    {
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->partida = $_GET['partida'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->vehiculo_placa = $_GET['vehiculo_placa'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->data = $this->model->getDataExportGuiasRT(
            $this->view->fecha_inicio,
            $this->view->fecha_fin,
            $this->view->partida,
            $this->view->destino,
            $this->view->vehiculo_placa,
            $this->view->search
        );

        // IMPORTANTE: Agregar información de empresa y terminales
        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->terminales = $this->model->getTerminalesParaFiltro();

        $this->view->render('encomienda/impresion/formatos_excel/excel_guiasRT', true);
    }


    public function get_allTerminales()
    {
        echo json_encode($this->model->get_allTerminales());
    }

    public function get_allTerminalesParaFiltro()
    {
        echo json_encode($this->model->get_allTerminalesParaFiltro());
    }

    public function anularVenta()
    {
        echo json_encode($this->model->anularVenta($_POST));
    }

    public function crear_guia_embarque()
    {
        echo json_encode($this->model->crear_guia_embarque($_POST));
    }

    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }

    public function get_ctgEncomienda()
    {
        echo json_encode($this->model->get_ctgEncomienda());
    }

    public function consultar_cdr()
    {
        echo json_encode($this->model->consultar_cdr($_POST));
    }

    public function get_clientes()
    {
        echo json_encode($this->model->get_clientes());
    }

    public function permisos_modal()
    {
        echo json_encode($this->model->permisos_modal());
    }

    public function reenviar_venta()
    {
        echo json_encode($this->model->reenviar_venta($_POST));
    }

    public function reenvio_porResumen()
    {

        // Obtener el contenido JSON del cuerpo de la solicitud
        $json = file_get_contents('php://input');

        // Decodificar el JSON en un array asociativo
        $data = json_decode($json, true);
        $IdArray = $data['ids'];
        $respuesta = $this->model->reenvio_porResumen($IdArray);
        echo json_encode($respuesta);
    }

    // entregar
    public function getEncomiendas()
    {
        echo json_encode($this->model->getEncomiendas($_POST));
    }

    public function getProductosEncomienda()
    {
        echo json_encode($this->model->getProductosEncomienda($_POST));
    }

    public function entregarEncomienda()
    {
        echo json_encode($this->model->entregarEncomienda($_POST));
    }

    public function pagarEncomienda()
    {
        echo json_encode($this->model->pagarEncomienda($_POST));
    }

    // EMBARCAR
    public function get_dataTableEncomiendaEmbarcar()
    {
        echo json_encode($this->model->get_dataTableEncomiendaEmbarcar($_POST));
    }

    public function get_terminalDestinoEmbarcar()
    {
        echo json_encode($this->model->get_terminalDestinoEmbarcar());
    }

    public function get_programacionEmbarcar()
    {
        echo json_encode($this->model->get_programacionEmbarcar($_POST));
    }

    public function embarcar_encomiendas()
    {
        echo json_encode($this->model->embarcar_encomiendas($_POST));
    }

    // Desembarcar
    public function get_dataTableEncomiendaDesembarcar()
    {
        echo json_encode($this->model->get_dataTableEncomiendaDesembarcar($_POST));
    }

    public function desembarcar_encomiendas()
    {
        echo json_encode($this->model->desembarcar_encomiendas($_POST));
    }

    // Reporte
    public function get_terminalDestinoReporte()
    {
        echo json_encode($this->model->get_terminalDestinoReporte($_POST));
    }

    public function get_programacionReporte()
    {
        echo json_encode($this->model->get_programacionReporte($_POST));
    }

    public function get_programacionTerminalOrigenReporte()
    {
        echo json_encode($this->model->get_programacionTerminalOrigenReporte($_POST));
    }

    public function get_productosEncomienda()
    {
        echo json_encode($this->model->get_productosEncomienda($_POST));
    }

    public function buscar_ubigeo()
    {
        echo json_encode($this->model->buscar_ubigeo($_POST));
    }

    public function buscar_ruta_anexo2()
    {
        echo json_encode($this->model->buscar_ruta_anexo2($_POST));
    }

    public function convert_NXC()
    {
        echo json_encode($this->model->convert_NXC($_POST));
    }

    public function ExCrear_guia()
    {
        echo json_encode($this->model->ExCrear_guia($_POST));
    }

    public function registrar_receptor()
    {
        echo json_encode($this->model->registrar_receptor($_POST));
    }

    public function consultar_cuentaD()
    {
        echo json_encode($this->model->consultar_cuentaD());
    }

    public function get_dataTable_grt()
    {
        echo json_encode($this->model->get_dataTable_grt($_POST));
    }

    public function consultar_comprobanteSUNAT()
    {
        echo json_encode($this->model->consultar_comprobanteSUNAT($_POST));
    }

    public function anular_GRT()
    {
        echo json_encode($this->model->anular_GRT($_POST));
    }

    public function get_encomiendas_embarque()
    {
        echo json_encode($this->model->get_encomiendas_embarque($_POST));
    }

    public function quitar_encomiendas_embarque()
    {
        echo json_encode($this->model->quitar_encomiendas_embarque($_POST));
    }

    public function get_registro_comprobante()
    {
        echo json_encode($this->model->get_registro_comprobante($_POST));
    }

    public function marcar_destino_erroneo()
    {
        echo json_encode($this->model->marcar_destino_erroneo($_POST));
    }

    public function buscar_encomienda_d_erroneo()
    {
        echo json_encode($this->model->buscar_encomienda_d_erroneo($_POST));
    }

    public function obtener_timeline_encomienda()
    {
        echo json_encode($this->model->obtener_timeline_encomienda($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }
}
