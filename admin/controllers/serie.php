<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Serie extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("serie/js/main.js");
        $this->view->php = array("serie/php/modal.php");
        $this->view->render("serie/index");
    }

    public function dataTable()
    {
       echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_serie"]) {
                echo json_encode($this->model->edit_register($_POST));
        } else {
            if($_POST['tp_comprobante'] == "7" || $_POST['tp_comprobante'] == "8"){
                echo json_encode($this->model->add_register_notas($_POST));
            }else{
                echo json_encode($this->model->add_register($_POST));
            }
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }
}
