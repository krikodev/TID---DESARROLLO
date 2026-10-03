<?php
class Comprobantes_Report extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("comprobantes_report/css/main.css");
        $this->view->js = array("comprobantes_report/js/main.js");
        $this->view->php = array("comprobantes_report/php/modal.php");
        try {
            $this->view->terminal = $this->model->getTerminales();
        } catch (Exception $e) {
            error_log("Error al obtener terminales: " . $e->getMessage());
            $this->view->terminal = ['success' => false, 'message' => 'Error al cargar terminales.'];
        }
        $this->view->render("comprobantes_report/index");
    }

    public function dataTable()
    {
        try {
            $data = $this->model->getDataTable($_POST);
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['estado' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
        }
    }

    public function exportacion()
    {
        try {
            $terminal = isset($_POST['terminal']) ? trim($_POST['terminal']) : '';
            $tp_comprobante = isset($_POST['tp_comprobante']) ? trim($_POST['tp_comprobante']) : 'TODO';
            $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
            $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';

            $this->view->terminal = $terminal;
            $this->view->tp_comprobante = $tp_comprobante;
            $this->view->fecha_inicio = $fecha_inicio;
            $this->view->fecha_fin = $fecha_fin;

            require 'views/comprobantes_report/php/pagos.php';
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['estado' => false, 'message' => 'Error al generar exportación: ' . $e->getMessage()]);
            exit;
        }
    }
}