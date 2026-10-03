<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Cupon extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["cupon/js/main.js"];
        $this->view->css = ["cupon/css/main.css"];
        $this->view->php = ["cupon/php/modal.php"];
        $this->view->render("cupon/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_cupon"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_precio_cupon()
    {
        echo json_encode($this->model->get_precio_cupon($_POST));
    }
}
