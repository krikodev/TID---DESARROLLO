<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Vehiculo extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("vehiculo/css/main.css");
        $this->view->js = array("vehiculo/js/config.js", "vehiculo/js/main.js");
        $this->view->php = array("vehiculo/php/modal.php");
        $this->view->render("vehiculo/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_vehiculo"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }


    public function config_vehiculo()
    {
        echo json_encode($this->model->config_vehiculo($_POST));
    }

    public function get_obj_vehiculo()
    {
        echo json_encode($this->model->get_obj_vehiculo($_POST));
    }

    public function get_n_asientos()
    {
        echo json_encode($this->model->get_n_asientos($_POST));
    }
}
