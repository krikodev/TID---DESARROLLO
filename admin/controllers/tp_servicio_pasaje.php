<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Tp_Servicio_Pasaje extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("tp_servicio_pasaje/js/main.js");
        $this->view->php = array("tp_servicio_pasaje/php/modal.php");
        $this->view->render("tp_servicio_pasaje/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_tp_servicio_pasaje"]) {
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
