<?php
Session::init();

class Dashboard extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["dashboard/js/main.js", "dashboard/js/graficos.js"];
        $this->view->css = ["dashboard/css/main.css"];
        $this->view->render("dashboard/index");
    }

    public function get_info_dashboard()
    {
        echo json_encode($this->model->get_info_dashboard($_POST));
    }

    public function dataTableProgramacion()
    {
        echo json_encode($this->model->dataTableProgramacion($_POST));
    }

    public function totales_meses()
    {
        echo json_encode($this->model->totales_meses($_POST));
    }

    public function ventas_por_vendedores()
    {
        echo json_encode($this->model->ventas_por_vendedores($_POST));
    }

    public function ventas_por_terminales()
    {
        echo json_encode($this->model->ventas_por_terminales($_POST));
    }

    public function obtenerResumenMensual()
    {
        echo json_encode($this->model->obtenerResumenMensual($_POST));
    }

    public function logout()
    {
        Session::destroy();
        $url_logout = URL . "login";
        echo "<script> window.location.href='$url_logout' </script>";
    }
}
