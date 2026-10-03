<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Egresos extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["egresos/js/main.js"];
        $this->view->php = ["egresos/php/modal.php"];
        $this->view->render("egresos/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_egreso"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function add_egreso_programacion()
    {
        echo json_encode($this->model->add_egreso_progrmacion($_POST));
    }
}
