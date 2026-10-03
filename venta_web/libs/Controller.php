<?php
require("libs/modules/Validation.php");
require("libs/modules/DataView.php");

class Controller extends DataView
{
    public $validate, $view, $model;

    function __construct()
    {
        parent::__construct();
        // functiones auto ejecutables del sistema
        $this->upload_permisosUser();
        // instancias
        $this->validate = new Validation();
        $this->view = new View();
        $this->view->tp_comprobante = $this->get_tpComprobante();
        $this->view->ubigeo = $this->get_ubigeo();
        $this->view->ubigeo_terminal = $this->get_ubigeo_terminal();
        $this->view->empresa = $this->get_empresa();
        $this->view->almacen = $this->get_almacen();
        $this->view->terminal = $this->get_terminal();
        $this->view->terminal_origen = $this->get_terminal_origen();
        $this->view->tp_docu = $this->get_tpDocu();
        $this->view->vehiculo = $this->get_vehiculo();
        $this->view->conductor = $this->get_conductor();
        $this->view->personal = $this->get_personal();
        $this->view->cliente = $this->get_cliente();
        $this->view->pasajero = $this->get_pasajero();
        $this->view->tp_servicio_pasaje = $this->get_tpServicioPasaje();
        $this->view->forma_pago = $this->get_formaPago();
        $this->view->medio_pago = $this->get_medioPago();
        $this->view->producto = $this->get_producto();
        $this->view->caja_userSesion = $this->get_cajaUserSesion();
        $result = $this->get_TpUserSesion();
        $this->view->tp_usuario_sesion = $result['success'] ? $result['message'] : null;
        $this->view->venta_web =  $this->get_venta_web();;
        $this->view->terminal_activo = $this->get_terminal_activo();
    }


    function loadModel($model)
    {
        $url = "models/" . $model . "model.php";
        $url_lib = "models/libs/" . $model . "model.php";
        if (file_exists($url)) {
            require $url;
            $modelName = $model . "Model";
            $this->model = new $modelName();
        }
        if (file_exists($url_lib)) {
            require $url_lib;
            $modelName = $model . "Model";
            $this->model = new $modelName();
        }
    }
}
