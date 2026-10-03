<?php

class Encomienda extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // public function render()
    // {
    //     $this->view->css = array("carrito/css/main.css");
    //     $this->view->js = array("carrito/js/main.js");
    //     $this->view->render("carrito/index", true);
    // }

    public function buscar_traking()
    {
        echo json_encode($this->model->buscar_traking($_POST));
    }

    public function confirmacion()
    {
        $this->view->css = array("carrito/css/main.css");
        $this->view->js = array("carrito/js/main.js");
        $this->view->render("carrito/confirmacion", true);
    }

    public function crear_cargo_unico()
    {
        echo json_encode($this->model->crear_cargo_unico($_POST));
    }
}
