<?php
// Session::init();
// $data_permisos = Session::get("data_permisos");
// $data_permisos["reportes"] ? '' : header('location: ' . URL . 'error');

class Reportes extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/main.js", "reportes/js/config.js");
        $this->view->render("reportes/index");
    }

    public function venta_general()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->series = $this->getdata_series();
        $this->view->clientes = $this->getdata_clientes();
        $this->view->usuarios = $this->getdata_usuarios();
        $this->view->tpagos = $this->getdata_tpPago();
        $this->view->cjchicas = $this->getdata_cjchica();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/venta_general.js", "reportes/js/config.js");
        $this->view->render("reportes/php/venta_general");
    }

    public function usuarios()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->sucursales = $this->getdata_sucursal();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/usuarios.js", "reportes/js/config.js");
        $this->view->render("reportes/php/usuarios");
    }

    public function clientes()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->sucursales = $this->getdata_sucursal();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/clientes.js", "reportes/js/config.js");
        $this->view->render("reportes/php/clientes");
    }

    public function sucursales()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->sucursales = $this->getdata_sucursal();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/sucursales.js", "reportes/js/config.js");
        $this->view->render("reportes/php/sucursales");
    }

    public function tarifas()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->sucursales = $this->getdata_sucursal();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/tarifas.js", "reportes/js/config.js");
        $this->view->render("reportes/php/tarifas");
    }
    public function caja_chica()
    {
        $this->view->tpDocus = $this->getdata_tpDocu();
        $this->view->sucursales = $this->getdata_sucursal();
        $this->view->usuarios = $this->getdata_usuarios();
        $this->view->css = array("reportes/css/main.css");
        $this->view->js = array("reportes/js/caja_chica.js", "reportes/js/config.js");
        $this->view->render("reportes/php/caja_chica");
    }

    //
    public function getVentaGeneral()
    {
        echo json_encode($this->model->getVentaGeneral($_POST));
    }

    public function getUsuarios()
    {
        echo json_encode($this->model->getUsuarios($_POST));
    }

    public function getClientes()
    {
        echo json_encode($this->model->getClientes($_POST));
    }

    public function getSucursales()
    {
        echo json_encode($this->model->getSucursales($_POST));
    }

    public function getTarifas()
    {
        echo json_encode($this->model->getTarifas($_POST));
    }
    public function getCajaChicas()
    {
        echo json_encode($this->model->getCajaChicas($_POST));
    }
}
