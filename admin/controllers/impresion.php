<?php

class Impresion extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("impresion/css/main.css");
        $this->view->js = array("impresion/js/main.js");
        $this->view->render("impresion/index");
    }

    public function get_data()
    {
        echo json_encode($this->model->get_data());
    }

    public function register()
    {
        echo json_encode($this->model->register($_POST));
    }
}
