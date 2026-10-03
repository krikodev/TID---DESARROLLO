<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Medio_pago extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("medio_pago/js/main.js");
        $this->view->php = array("medio_pago/php/modal.php");
        $this->view->render("medio_pago/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_medio_pago"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }
}
