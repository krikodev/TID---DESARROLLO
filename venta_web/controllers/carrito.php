<?php

// Session::init();

class Carrito extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("carrito/css/main.css", "carrito/css/loader.css", "carrito/css/swalt.css", "carrito/css/form_pasajero.css", "carrito/css/pagos.css");
        $this->view->js = array("carrito/js/main.js", "carrito/js/buscador.js");
        $this->view->render("carrito/index");
    }

    public function get_programaciones()
    {
        echo json_encode($this->model->get_programaciones($_POST));
    }

    public function getVehiculo()
    {
        echo json_encode($this->model->getVehiculo($_POST));
    }

    public function datos()
    {
        $this->view->css = array("carrito/css/main.css");
        $this->view->js = array("carrito/js/main.js");
        $this->view->render("carrito/datos", true);
    }

    public function pago()
    {
        $this->view->css = array("carrito/css/main.css");
        $this->view->js = array("carrito/js/main.js");
        $this->view->render("carrito/pago", true);
    }

    public function registro_usuario()
    {
        echo json_encode($this->model->registro_usuario($_POST));
    }

    public function confirmacion()
    {
        $this->view->css = array("carrito/css/main.css");
        $this->view->js = array("carrito/js/main.js");
        $this->view->render("carrito/confirmacion", true);
    }

    public function crear_nueva_orden()
    {
        echo json_encode($this->model->crear_nueva_orden($_POST));
    }

    public function crear_cargo_unico()
    {
        echo json_encode($this->model->crear_cargo_unico($_POST));
    }

    public function crear_cargo_yape_codigo()
    {
        echo json_encode($this->model->crear_orden_yape($_POST));
    }

    public function concretar_venta()
    {
        echo json_encode($this->model->concretar_venta($_POST));
    }
}
