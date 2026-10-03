<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por categoria encomienda

class Categoria_Encomienda extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("categoria_encomienda/js/main.js");
        $this->view->php = array("categoria_encomienda/php/modal.php");
        $this->view->render("categoria_encomienda/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_ctg_encomienda"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function buscar()
    {
        $q = $_GET["q"] ?? "";
        echo json_encode($this->model->buscar_productos($q));
    }
}
