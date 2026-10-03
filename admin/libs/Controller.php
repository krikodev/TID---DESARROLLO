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
        $this->view->paises = $this->get_paises();
        $this->view->empresa = $this->get_empresa();
        $this->view->almacen = $this->get_almacen();
        $this->view->terminal = $this->get_terminal();
        $this->view->terminal_origen = $this->get_terminal_origen();
        $this->view->tp_docu = $this->get_tpDocu();
        $this->view->vehiculo = $this->get_vehiculo();
        $this->view->conductor = $this->get_conductor();
        $this->view->proveedor = $this->get_proveedor();
        $this->view->personal = $this->get_personal();
        $this->view->cliente = $this->get_cliente();
        $this->view->pasajero = $this->get_pasajero();
        $this->view->tp_servicio_pasaje = $this->get_tpServicioPasaje();
        $this->view->forma_pago = $this->get_formaPago();
        $this->view->medio_pago = $this->get_medioPago();
        $this->view->tp_operacion_venta = $this->get_tp_operacion_venta();
        $this->view->tp_medio_pago = $this->get_tp_medio_pago();
        $this->view->config_vehiculo = $this->get_config_vehiculo();
        $this->view->producto = $this->get_producto();
        $this->view->productos = $this->get_productos();
        $this->view->unidad_servicio = $this->get_unidad_servicio();
        $this->view->bancos = $this->get_bancos();
        $this->view->afectaciones = $this->get_afectaciones();
        $this->view->unidad_medida = $this->get_unidad_medida();
        $this->view->caja_userSesion = $this->get_cajaUserSesion();
        $result = $this->get_TpUserSesion();
        $this->view->tp_usuario_sesion = $result['success'] ? $result['message'] : null;
        $this->view->terminal_activo = $this->get_terminal_activo();
        $this->view->comision_empresa = $this->get_comision_empresa();
        $this->view->rucs = $this->get_rucs();
        $this->view->config_encomienda = $this->get_configuracion_encomienda();
        $this->view->config_pasaje = $this->get_configuracion_pasaje();
        $this->view->config_general = $this->get_configuracion_general();
        $this->view->permisos_mods = $this->get_permisos_mods();
        $this->view->modulos = $this->get_modulos_disponibles();
        $this->view->series_manifiesto = $this->get_series_manifiest();
        $this->view->cotizaciones = $this->get_series_cotizaciones();
        $this->view->monedas = $this->get_monedas();
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
