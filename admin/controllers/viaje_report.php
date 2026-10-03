<?php
class Viaje_Report extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Cargar el modelo manualmente
        $modelPath = 'models/viaje_report_model.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            $this->model = new Viaje_Report_Model();
        } else {
            error_log("Error: No se pudo encontrar el modelo en: " . $modelPath);
        }
    }

    public function render()
    {
        $this->view->css = array("viaje_report/css/main.css");
        $this->view->js = array("viaje_report/js/main.js");

        try {
            // Verificar si el modelo se cargó correctamente
            if ($this->model === null) {
                throw new Exception("El modelo no se pudo cargar");
            }

            $this->view->terminal = $this->model->getTerminales();
            $this->view->tipos_servicio = $this->model->getTiposServicioPasaje();
        } catch (Exception $e) {
            error_log("Error al obtener datos: " . $e->getMessage());
            $this->view->terminal = ['success' => false, 'message' => 'Error al cargar datos.'];
            $this->view->tipos_servicio = [];
        }

        $this->view->render("viaje_report/index");
    }

    public function dataTable()
    {
        try {
            if ($this->model === null) {
                throw new Exception("El modelo no está disponible");
            }

            $data = $this->model->getDataTable($_POST);
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
        }
    }

    public function exportacion()
    {
        try {
            if ($this->model === null) {
                throw new Exception("El modelo no está disponible");
            }

            $terminal_origen = isset($_POST['terminal_origen']) ? trim($_POST['terminal_origen']) : '';
            $terminal_destino = isset($_POST['terminal_destino']) ? trim($_POST['terminal_destino']) : '';
            $tipo_servicio = isset($_POST['tipo_servicio']) ? trim($_POST['tipo_servicio']) : '';
            $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
            $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';

            // Obtener datos de la empresa (logo, nombre, etc.)
            $empresa = $this->model->getEmpresa();

            $datos = $this->model->getDatosExportacion(
            $fecha_inicio,
            $fecha_fin,
            $terminal_origen,
            $terminal_destino,
            $tipo_servicio
            );

            $this->view->terminal_origen = $terminal_origen;
            $this->view->terminal_destino = $terminal_destino;
            $this->view->tipo_servicio = $tipo_servicio;
            $this->view->fecha_inicio = $fecha_inicio;
            $this->view->fecha_fin = $fecha_fin;
            $this->view->empresa = $empresa;
            $this->view->datos = $datos;

            // Incluir el archivo de reporte Excel
            require 'views/viaje_report/php/reporte.php';
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar exportación: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
