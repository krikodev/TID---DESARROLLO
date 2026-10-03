<?php
// Session::verify_permission('p_personal');

class Config_modulos extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["config_modulos/css/main.css"];
        $this->view->js = ["config_modulos/js/main.js"];
        $this->view->php = ["config_modulos/php/modal.php"];
        $this->view->render("config_modulos/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_modulo"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_register()
    {
        echo json_encode($this->model->get_register($_POST));
    }

    public function get_padres()
    {
        $excludeId = $_POST['id_modulo'] ?? null;
        echo json_encode($this->model->get_padres($excludeId));
    }
}
