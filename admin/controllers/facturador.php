<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Facturador extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["facturador/js/main.js", "facturador/js/cliente.js"];
        $this->view->css = ["facturador/css/main.css"];
        $this->view->php = ["facturador/php/modal.php"];
        $this->view->render("facturador/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }

    public function crear_producto()
    {
        echo json_encode($this->model->crear_producto($_POST));
    }

    public function get_productosF()
    {
        echo json_encode($this->model->get_productosF());
    }

    public function generar_comprobante()
    {
        echo json_encode($this->model->generar_comprobante($_POST));
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $data_comprobante = $this->model->get_data_comprobante($data[1]);
                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/comprobante', true);
                } else {
                    $this->view->render("error/index", true);
                }
                break;
            case 'nota_venta':
                $data_comprobante = $this->model->get_data_comprobante($data[1]);

                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/nota_venta', true);
                } else {
                    $this->view->render("error/index", true);
                }
                break;
            case 'comprobante_a4':
                $data_comprobante = $this->model->get_data_comprobante($data[1]);

                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/A4/comprobanteA4', true);
                } else {
                    $this->view->render("error/index", true);
                }
                break;
            case 'nota_venta_a4':
                $data_comprobante = $this->model->get_data_comprobante($data[1]);

                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/A4/nota_ventaA4', true);
                } else {
                    $this->view->render("error/index", true);
                }
                break;
            default:
                break;
        }
    }

    public function reenviar_venta()
    {
        echo json_encode($this->model->reenviar_venta($_POST));
    }

    public function anular_venta()
    {
        echo json_encode($this->model->anular_venta($_POST));
    }

    public function get_items_comprobante()
    {
        echo json_encode($this->model->get_items_comprobante($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }
}
