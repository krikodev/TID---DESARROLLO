<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Pasaje extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("pasaje/css/main.css", "pasaje/css/venta.css");
        $this->view->js = array(
            "pasaje/js/main.js",
            "pasaje/js/pasajero.js",
            "pasaje/js/conductor.js",
            "pasaje/js/impresion.js",
            "pasaje/js/programacion.js"
        );
        $this->view->php = array("pasaje/php/modal.php");
        $this->view->render("pasaje/index");
    }


    public function getDataComprobante()
    {
        $dataComprobante = json_encode($this->model->getDataComprobante($_POST["id_venta"]));
        $respuesta = $this->model->conversor_data($dataComprobante);
        $resp = json_encode($respuesta, true);
        echo ($resp);
    }

    // Pasaje
    public function get_dataTable()
    {
        echo json_encode($this->model->get_dataTable($_POST));
    }

    public function get_dataTableResumen()
    {
        $this->model->get_dataTableResumen();
    }

    public function impresion_rapida()
    {
        echo json_encode($this->model->impresion_rapida());
    }

    public function get_dataTableReenvio()
    {
        $this->model->get_dataTableReenvio($_POST);
    }

    public function set_ventaPasaje()
    {
        if ($_POST["id_ventaPasaje"]) {
            echo json_encode($this->model->pagar_reservaPasaje($_POST));
        } else {
            echo json_encode($this->model->set_ventaPasaje($_POST));
        }
    }

    public function cambiar_asiento()
    {
        echo json_encode($this->model->cambiar_asiento($_POST));
    }

    public function set_estadoProcesoAsiento()
    {
        echo json_encode($this->model->set_estadoProcesoAsiento($_POST));
    }

    public function verificar_asiento()
    {
        echo json_encode($this->model->verificar_asiento($_POST));
    }

    public function verificador_asientos()
    {
        echo json_encode($this->model->verificador_asientos($_POST));
    }

    public function reserva_grupal()
    {
        echo json_encode($this->model->reserva_grupal($_POST));
    }

    public function liberar_reservas()
    {
        echo json_encode($this->model->liberar_reservas($_POST));
    }

    public function delete_estadoProcesoAsiento()
    {
        echo json_encode($this->model->delete_estadoProcesoAsiento($_POST));
    }

    public function liberar_asiento()
    {
        echo json_encode($this->model->liberar_asiento($_POST));
    }

    public function liberar_asientos()
    {
        echo json_encode($this->model->liberar_asientos($_POST));
    }

    public function anular_venta()
    {
        echo json_encode($this->model->anular_venta($_POST));
    }
    public function reenviar_venta()
    {
        echo json_encode($this->model->reenviar_venta($_POST));
    }
    public function consultar_cdr()
    {
        echo json_encode($this->model->consultar_cdr($_POST));
    }

    public function consultar_impresora()
    {
        echo json_encode($this->model->consultar_impresora());
    }

    public function reenvio_porResumen()
    {
        echo json_encode($this->model->reenvio_porResumen($_POST));
    }

    public function set_postponerPasaje()
    {
        echo json_encode($this->model->set_postponerPasaje($_POST));
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $impresion_directa = Session::get("p_imprimir");

                if ($impresion_directa == 1 && isset($data[2]) != '3') {
                    $datos = $this->model->getDataComprobante($data[1]);
                    if ($datos['IMPRESORA']['tipo'] == 'bluetooth') {
                        // Si la impresora es Bluetooth, se prepara el formato para impresión
                        $datos['tipo_impresion'] = 1;
                        $datos['tipo_impresora'] = $datos['IMPRESORA']['tipo'] ?? '';
                        $datos['ip_celular'] = $datos['IMPRESORA']['ip_pc'] ?? '';
                        header('Content-Type: application/json');

                        echo json_encode([
                            'status' => true,
                            'data' => $datos
                        ]);
                    } else {
                        // Si es una impresora de red o local, se prepara el formato para impresión web
                        $datos['IMPRESORA']['ip_pc'] = $datos['IMPRESORA']['ip_pc'] ?: 'localhost';
                        $datos['tipo_impresion'] = 1;
                        $datos['tipo_impresora'] = $datos['IMPRESORA']['tipo'] ?? '';

                        // Genera el link completo para la impresora
                        $link = 'http://' . $datos['IMPRESORA']['ip_pc'] . '/impresion/index.php?data=' . urlencode(json_encode($datos));

                        // Devuelve la URL en formato JSON
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'success',
                            'link' => $link,
                            'data' => $datos
                        ]);
                    }
                } else {
                    $this->view->data = $this->model->getDataComprobante($data[1]);
                    $this->view->render('pasaje/impresion/comprobante/comprobante_nuevo', true);
                }
                break;
            case 'nota_venta':
                $impresion_directa = Session::get("p_imprimir");
                if ($impresion_directa == 1 && isset($data[2]) != '3') {
                    $datos = $this->model->getDataComprobante($data[1]);
                    if ($datos['IMPRESORA']['tipo'] == 'bluetooth') {
                        // Si la impresora es Bluetooth, se prepara el formato para impresión
                        $datos['tipo_impresion'] = 1;
                        $datos['tipo_impresora'] = $datos['IMPRESORA']['tipo'] ?? '';
                        $datos['ip_celular'] = $datos['IMPRESORA']['ip_pc'] ?? '';
                        header('Content-Type: application/json');

                        echo json_encode([
                            'status' => true,
                            'data' => $datos
                        ]);
                    } else {
                        $datos['tipo_impresion'] = 2;
                        $datos['tipo_impresora'] = $datos['IMPRESORA']['tipo'] ?? '';

                        // Genera el link completo para la impresora
                        $link = 'http://' . $datos['IMPRESORA']['ip_pc'] . '/impresion/index.php?data=' . urlencode(json_encode($datos));

                        // Devuelve la URL en formato JSON
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'success',
                            'link' => $link,
                            'data' => $datos
                        ]);
                    }
                } else {
                    $this->view->data = $this->model->getDataComprobante($data[1]);
                    $this->view->render('pasaje/impresion/nota_venta/nota_ventanuevo', true);
                }
                break;
            case 'manifiesto':
                $condicion = isset($_POST['condicion']) ? $_POST['condicion'] : 0;
                $preview = isset($_POST['preview']) && $_POST['preview'] == '1';

                $data = $this->model->getDataReporteManifiesto($data[1], $condicion);

                if ($data['estado'] == 1 && !$preview) {
                    echo json_encode($data['estado']);
                } else {
                    $this->view->data = $data;
                    $this->view->render('pasaje/impresion/manifiesto', true);
                }
                break;
            case 'manifiesto_sunat':
                $preview = isset($_POST['preview']) && $_POST['preview'] == '1';

                $data = $this->model->getDataReporteManifiesto_sunat($data[1]);

                if ($data['estado'] == 1 && !$preview) {
                    echo json_encode($data['estado']);
                } else {
                    $this->view->data = $data;
                    $this->view->render('pasaje/impresion/manifiesto_sunat', true);
                }
                break;
            case 'liquidacion_vehiculo':
                $this->view->data = $this->model->getDataReporteLiquidacionVehiculo($data[1]);
                $this->view->render('pasaje/impresion/liquidacion/liquidacion_vehiculo', true);
                break;
            case 'liquidacion_p_usuario':
                $this->view->data = $this->model->getDataReporteLiquidacionPUsuario($data[1]);
                $this->view->render('pasaje/impresion/liquidacion/liquidacion_detallado', true);
                break;
            case 'liquidacion_usuario':
                $this->view->data = $this->model->getDataReporteLiquidacionUsuario($data[1], $data[2]);
                $this->view->render('pasaje/impresion/liquidacion/liquidacion_usuario', true);
                break;
            case 'liquidacion_usuarioticket':
                if (!isset($data[1]) || !isset($data[2])) {
                    http_response_code(400);
                    die("Parámetros insuficientes");
                }

                $id_programacion = $data[1];
                $id_usuario = $data[2];

                try {
                    // Obtener datos del modelo
                    $datos_liquidacion = $this->model->getDataReporteLiquidacionUsuario($id_programacion, $id_usuario);

                    if (empty($datos_liquidacion)) {
                        http_response_code(404);
                        die("No se encontraron datos para la liquidación");
                    }

                    $GLOBALS['liquidacion_data'] = $datos_liquidacion;

                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    $view_path = 'views/pasaje/impresion/liquidacion/liquidacion_usuarioticket.php';

                    if (!file_exists($view_path)) {
                        die("Error interno: Vista no encontrada");
                    }

                    include($view_path);
                    exit;
                } catch (Exception $e) {
                    http_response_code(500);
                    die("Error al generar liquidación: " . $e->getMessage());
                }
                break;
            case 'liquidacion_terminal':
                $this->view->data = $this->model->getDataReporteLiquidacionTerminal($data[1], $data[2]);
                $this->view->render('pasaje/impresion/liquidacion/liquidacion_terminal', true);
                break;
            case 'control_pasajeros':
                // Obtener los datos de los pasajeros
                $data_pasajeros = $this->model->getDataControlPasajeros($data[1]);

                if ($data_pasajeros['success']) {
                    $this->view->data = $data_pasajeros;
                    $this->view->render('pasaje/impresion/control_pasajeros', true);
                } else {
                    echo json_encode(['success' => false, 'message' => $data_pasajeros['message']]);
                }
                break;
            default:
                break;
        }
    }

    public function envio_impresion($data)
    {
        // Construir la URL con la IP de la impresora
        $url = 'http://' . $data['IMPRESORA']['ip_pc'] . '/impresion/';
        // Inicializar cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        // Enviar los datos en formato JSON
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        // Ejecutar la solicitud
        $response = curl_exec($curl);

        // Comprobar si hubo errores en la ejecución
        if ($response === false) {
            $error_msg = curl_error($curl); // Obtener mensaje de error
            curl_close($curl); // Cerrar cURL antes de devolver el error
            return array('success' => false, 'message' => 'Error en la solicitud: ' . $error_msg);
        } else {
            curl_close($curl); // Cerrar cURL antes de devolver la respuesta
            return array('success' => true, 'message' => $response);
        }
    }

    public function getDataVenta()
    {
        echo json_encode($this->model->getDataVenta($_POST));
    }

    public function get_allTerminalDestinoForOrigen()
    {
        echo json_encode($this->model->get_allTerminalDestinoForOrigen($_POST));
    }

    public function get_programacionesForIdTerminal()
    {
        echo json_encode($this->model->get_programacionesForIdTerminal($_POST));
    }

    public function get_vehiculoForID()
    {
        echo json_encode($this->model->get_vehiculoForID($_POST));
    }

    public function get_asientosForID()
    {
        echo json_encode($this->model->get_asientosForID($_POST));
    }

    public function get_dataAsiento()
    {
        echo json_encode($this->model->get_dataAsiento($_POST));
    }

    public function get_DRutas()
    {
        echo json_encode($this->model->get_DestinoRutas($_POST));
    }

    public function desbloquear_asiento()
    {
        echo json_encode($this->model->desbloquear_asiento($_POST));
    }

    public function get_clientes()
    {
        if (!empty($_GET['tp_doc'])) {
            echo json_encode($this->model->mostrar_clientes($_GET['tp_doc']));
        } else {
            echo json_encode($this->model->get_clientes());
        }
    }

    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }

    public function liquidar_vehiculo()
    {
        echo json_encode($this->model->liquidar_vehiculo($_POST));
    }

    public function liquidar_terminal_v()
    {
        echo json_encode($this->model->liquidar_terminal_v($_POST));
    }

    public function cliente_get()
    {
        echo json_encode($this->model->cliente_get($_POST));
    }

    public function get_resumenVendedores()
    {
        if (isset($_POST['id_programacion']) && !empty($_POST['id_programacion'])) {
            $id_programacion = intval($_POST['id_programacion']);
            $resultado = $this->model->get_resumenVendedores($id_programacion);
            echo json_encode($resultado);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ID de programación no proporcionado'
            ]);
        }
    }

    public function get_resumenDestinos()
    {
        if (isset($_POST['id_programacion']) && !empty($_POST['id_programacion'])) {
            $id_programacion = intval($_POST['id_programacion']);
            $resultado = $this->model->get_resumenDestinos($id_programacion);
            echo json_encode($resultado);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ID de programación no proporcionado'
            ]);
        }
    }

    public function reservar_todos()
    {
        echo json_encode($this->model->reservar_todos($_POST));
    }
    public function marcarAbordaje()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_dt_venta = isset($_POST['id_dt_venta']) ? intval($_POST['id_dt_venta']) : 0;
            $id_venta = isset($_POST['id_venta']) ? intval($_POST['id_venta']) : 0;

            if ($id_dt_venta > 0) {
                echo json_encode($this->model->marcarAbordaje($id_dt_venta, $id_venta));
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de detalle de venta no proporcionado']);
            }
        }
    }

    public function liquidar_usuario_p()
    {
        echo json_encode($this->model->liquidar_usuario_p($_POST));
    }

    public function validar_edad_cliente()
    {
        if (!isset($_POST['id_cliente']) || empty($_POST['id_cliente'])) {
            echo json_encode(['success' => false, 'message' => 'ID de cliente no proporcionado']);
            return;
        }

        $resultado = $this->model->validarEdadCliente($_POST['id_cliente']);
        echo json_encode($resultado);
    }

    public function actualizar_edad_cliente()
    {
        if (!isset($_POST['id_cliente']) || !isset($_POST['edad'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $resultado = $this->model->actualizarFechaNacimientoCliente(
            $_POST['id_cliente'],
            $_POST['edad']
        );
        echo json_encode($resultado);
    }

    public function postponer_venta()
    {
        echo json_encode($this->model->postponer_venta($_POST));
    }

    public function buscar_pasajeros()
    {
        echo json_encode($this->model->buscar_pasajeros($_POST));
    }

    public function revisar_pasajero()
    {
        echo json_encode($this->model->revisar_pasajero($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }

    public function desbloquear_asientos()
    {
        echo json_encode($this->model->desbloquear_asientos($_POST));
    }
}
