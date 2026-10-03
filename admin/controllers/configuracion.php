<?php
$data_permisos = Session::get("data_permisos");
$data_permisos["p_config"] ? '' : header('location: ' . URL . 'error');

class Configuracion extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["configuracion/css/main.css"];
        $this->view->js = ["configuracion/js/main.js"];
        $this->view->render("configuracion/index");
    }

    public function config_general()
    {
        $this->view->js = ["configuracion/js/config_general.js"];
        $this->view->css = ["configuracion/css/config_general.css"];
        $this->view->render("configuracion/php/config_general");
    }

    public function config_encomienda()
    {
        $this->view->js = ["configuracion/js/config_encomienda.js"];
        $this->view->css = ["configuracion/css/config_encomienda.css"];
        $this->view->render("configuracion/php/config_encomienda");
    }

    public function config_pasaje()
    {
        $this->view->js = ["configuracion/js/config_pasaje.js"];
        $this->view->css = ["configuracion/css/config_pasaje.css"];
        $this->view->render("configuracion/php/config_pasaje");
    }

    public function config_facturador()
    {
        $this->view->js = ["configuracion/js/config_facturador.js"];
        $this->view->render("configuracion/php/config_facturador");
    }

    public function config_cotizacion()
    {
        $this->view->js = ["configuracion/js/config_cotizacion.js"];
        $this->view->render("configuracion/php/config_cotizacion");
    }

    public function get_data()
    {
        echo json_encode($this->model->get_data());
    }

    public function register()
    {
        echo json_encode($this->model->register($_POST));
    }

    public function register_config_encomienda()
    {
        echo json_encode($this->model->register_config_encomienda($_POST));
    }

    public function register_config_pasaje()
    {
        echo json_encode($this->model->register_config_pasaje($_POST));
    }

    public function get_series_manifiesto()
    {
        echo json_encode($this->model->get_series_manifiesto());
    }

    public function add_serie_manifiesto()
    {
        echo json_encode($this->model->add_serie_manifiesto($_POST));
    }

    public function update_serie_manifiesto()
    {
        echo json_encode($this->model->update_serie_manifiesto($_POST));
    }

    public function delete_serie_manifiesto()
    {
        echo json_encode($this->model->delete_serie_manifiesto($_POST['id_serie_manifiesto']));
    }

    public function register_config_facturador()
    {
        echo json_encode($this->model->register_config_facturador($_POST));
    }

    public function register_config_cotizacion()
    {
        echo json_encode($this->model->register_config_cotizacion($_POST));
    }

    public function get_data_config_encomienda()
    {
        echo json_encode($this->model->get_data_config_encomienda($_POST));
    }

    public function get_data_config_pasaje()
    {
        echo json_encode($this->model->get_data_config_pasaje());
    }

    public function get_data_config_facturador()
    {
        echo json_encode($this->model->get_data_config_facturador());
    }

    public function get_data_config_cotizacion()
    {
        echo json_encode($this->model->get_data_config_cotizacion());
    }

    // ── Exportación de campos ─────────────────────────────────

    public function get_campos_disponibles($data)
    {
        echo json_encode($this->model->get_campos_disponibles($data[0]));
    }

    public function get_config_campos($data)
    {
        echo json_encode($this->model->get_config_campos($data[0], $data[1]));
    }

    public function save_config_campos()
    {
        echo json_encode($this->model->save_config_campos($_POST));
    }
}
