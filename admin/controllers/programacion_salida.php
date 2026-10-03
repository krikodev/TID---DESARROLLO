<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Programacion_salida extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["programacion_salida/css/main.css"];
        $this->view->js = ["programacion_salida/js/main.js", "programacion_salida/js/conductor.js"];
        $this->view->php = ["programacion_salida/php/modal.php"];
        $this->view->render("programacion_salida/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_salida"]) {
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
}
