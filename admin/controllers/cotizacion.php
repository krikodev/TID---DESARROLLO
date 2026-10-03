<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Cotizacion extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["cotizacion/css/main.css"];
        $this->view->js = ["cotizacion/js/main.js", "cotizacion/js/cliente.js"];
        $this->view->php = ["cotizacion/php/modal.php"];
        $this->view->render("cotizacion/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST['id_cotizacion']) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function get_numero_cotizacion()
    {
        echo json_encode($this->model->get_numero_cotizacion());
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'cotizacion':
                $this->view->data = $this->model->get_cotizacion_data($data[1]);
                // var_dump(json_encode($this->view->data)); die;
                $this->view->render("cotizacion/impresion/ticket", true);
                break;
            case 'cotizacion_a4':
                $this->view->data = $this->model->get_cotizacion_data($data[1]);
                $this->view->render("cotizacion/impresion/a4", true);
                break;
            default:
                break;
        }
    }

    public function buscar_productos_cotizacion()
    {
        echo json_encode($this->model->buscar_productos_cotizacion($_POST));
    }

    public function crear_servicio()
    {
        echo json_encode($this->model->crear_servicio($_POST));
    }

    public function get_servicios()
    {
        echo json_encode($this->model->get_servicios($_POST));
    }

    public function aceptar_cotizacion()
    {
        echo json_encode($this->model->aceptar_cotizacion($_POST));
    }

    public function convertir_cotizacion()
    {
        echo json_encode($this->model->convertir_cotizacion($_POST));
    }

    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }

    public function getCotizacion()
    {
        echo json_encode($this->model->getCotizacion($_POST));
    }

    public function getDetalle()
    {
        echo json_encode($this->model->getDetalle());
    }
}
