<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Cuenta_bnk extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["cuenta_bnk/css/main.css"];
        $this->view->js = ["cuenta_bnk/js/main.js"];
        $this->view->php = ["cuenta_bnk/php/modal.php"];
        $this->view->render("cuenta_bnk/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_cuenta"]) {
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
