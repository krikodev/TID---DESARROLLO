<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Flete extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["flete/css/main.css"];
        $this->view->js = ["flete/js/main.js", "flete/js/conductor.js", "flete/js/cliente.js", "flete/js/vehiculo.js", "flete/js/proveedor.js"];
        $this->view->php = ["flete/php/modal.php"];
        $this->view->render("flete/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_flete"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_salidas()
    {
        echo json_encode($this->model->get_salidas($_POST));
    }

    public function get_cotizacion_datos()
    {
        echo json_encode($this->model->get_cotizacion_datos($_POST));
    }

    public function impresion($data)
    {

        switch ($data[0]) {
            case 'ticket':
                $this->view->data = $this->model->get_flete_data($data[1]);
                $this->view->render("flete/impresion/ticket", true);
                break;
            case 'a4':
                $this->view->data = $this->model->get_flete_data($data[1]);
                $this->view->render("flete/impresion/a4", true);
                break;
            case 'orden_servicio_a4':
                $this->view->data = $this->model->get_data_orden_servicio($data[1]);
                $this->view->render("flete/impresion/orden_servicio_a4", true);
                break;
            case 'orden_servicio_ticket':
                $this->view->data = $this->model->get_data_orden_servicio($data[1]);
                $this->view->render("flete/impresion/orden_servicio_ticket", true);
                break;
            default:
                break;
        }
    }

    public function cambiar_estado()
    {
        echo json_encode($this->model->cambiar_estado_flete($_POST));
    }

    public function validar_facturacion()
    {
        $id_flete = (int) ($_POST['id_flete'] ?? 0);

        if ($id_flete <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'El flete no es válido.'
            ]);

            return;
        }
        echo json_encode($this->model->validar_flete_facturacion($id_flete));
    }

    public function get_pago_tercerizado()
    {
        echo json_encode($this->model->get_pago_tercerizado($_POST));
    }

    public function get_historial_pago_tercerizado()
    {
        echo json_encode($this->model->get_historial_pago_tercerizado($_POST));
    }

    public function registrar_pago_tercerizado()
    {
        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'success' => false,
                    'message' =>
                    'Método no permitido.'
                ]);
                return;
            }

            $response = $this->model->registrar_pago_tercerizado($_POST);
            echo json_encode($response);
        } catch (Throwable $e) {

            echo json_encode([
                'success' => false,
                'message' =>
                $e->getMessage()
            ]);
        }
    }

    public function facturar_flete()
    {

        try {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

                echo json_encode([
                    "success" => false,
                    "message" =>
                    "Método de petición no permitido."
                ]);

                return;
            }


            // ==============================================
            // DATA
            // ==============================================

            $data = $_POST;


            // ==============================================
            // VALIDACIÓN BÁSICA
            // ==============================================

            $idFlete =
                (int) (
                    $data["id_flete"]
                    ?? 0
                );


            if ($idFlete <= 0) {

                echo json_encode([
                    "success" => false,
                    "message" =>
                    "No se recibió un flete válido."
                ]);

                return;
            }


            // ==============================================
            // MODELO
            // ==============================================

            $result =
                $this->model->facturar_flete(
                    $data
                );


            echo json_encode(
                $result,
                JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (Throwable $e) {

            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function get_comprobante_pendiente()
    {
        try {
            $idVenta = (int) ($_POST["id_venta"] ?? 0);
            if ($idVenta <= 0) {
                echo json_encode([
                    "success" => false,
                    "message" => "El comprobante seleccionado no es válido."
                ]);
                return;
            }
            $result =
                $this->model->get_comprobante_pendiente($idVenta);
            echo json_encode(
                $result,
                JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (Throwable $e) {
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function guardar_vista_previa()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido.'
            ]);

            return;
        }


        $response =
            $this->model->guardar_vista_previa(
                $_POST
            );


        echo json_encode(
            $response,
            JSON_UNESCAPED_UNICODE
        );
    }

    public function enviar_sunat()
    {
        try {

            if (
                $_SERVER['REQUEST_METHOD'] !== 'POST'
            ) {

                throw new Exception(
                    "Método no permitido."
                );
            }


            $response =
                $this->model->enviar_comprobante_sunat(
                    $_POST
                );


            echo json_encode(
                $response,
                JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
            );
        } catch (Throwable $e) {

            echo json_encode([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function editar_pago_tercerizado()
    {
        echo json_encode($this->model->editar_pago_tercerizado($_POST));
    }
    
    public function eliminar_pago_tercerizado()
    {
        echo json_encode($this->model->eliminar_pago_tercerizado($_POST));
    }
}
