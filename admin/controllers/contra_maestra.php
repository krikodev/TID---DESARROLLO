<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Contra_Maestra extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["contra_maestra/js/main.js"];
        $this->view->css = ["contra_maestra/css/main.css"];
        $this->view->php = ["contra_maestra/php/modal.php"];
        $this->view->render("contra_maestra/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function generar_contrasena_maestra()
    {
        echo json_encode($this->model->generar_contrasena_maestra());
    }

    public function crud_register()
    {
        if ($_POST["id_serie"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            if ($_POST['tp_comprobante'] == "7" || $_POST['tp_comprobante'] == "8") {
                echo json_encode($this->model->add_register_notas($_POST));
            } else {
                echo json_encode($this->model->add_register($_POST));
            }
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }
}
