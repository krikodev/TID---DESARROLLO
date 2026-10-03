<?php
// Session::verify_permission('p_empresa');
class Empresa extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("empresa/css/main.css");
        $this->view->js = array("empresa/js/main.js");
        $this->view->render("empresa/index");
    }

    public function get_data()
    {
        echo json_encode($this->model->get_data());
    }

    public function crud_register()
    {
        echo json_encode($this->model->register($_POST));
    }
}
