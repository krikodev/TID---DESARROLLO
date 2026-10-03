<?php
class Valorizacion extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $data_permisos = Session::get("data_permisos");

        if ($data_permisos["p_valorizacion"] == 1) {
            $this->view->css = ["valorizacion/css/main.css"];
            $this->view->js = ["valorizacion/js/main.js", "valorizacion/js/cliente.js"];
            $this->view->php = ["valorizacion/php/modal.php"];
            $this->view->render("valorizacion/index");
        } else {
            $this->view->render("error/index", true);
        }
    }
    public function dataTable()
    {
        // Recibir los filtros adicionales
        $filtros = [
            'fecha_inicio' => $_POST['filtro_fecha_inicio'] ?? '',
            'fecha_fin' => $_POST['filtro_fecha_fin'] ?? '',
            'estado' => $_POST['filtro_estado'] ?? ''
        ];

        // Combinar los datos originales con los filtros
        $data = array_merge($_POST, $filtros);
        echo json_encode($this->model->get_dataTable($data));
    }

    public function crud_register()
    {
        if ($_POST['id_valorizacion']) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function get_documentosxpagar()
    {
        echo json_encode($this->model->get_documentosxpagar($_POST));
    }

    public function get_fletes_cliente()
    {
        echo json_encode($this->model->get_fletes_cliente($_POST));
    }
    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }

    public function get_valorizacion()
    {
        echo json_encode($this->model->get_valorizacion($_POST));
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }
    public function impresion($data)
    {
        switch ($data[0]) {
            case 'valorizacion':
                $this->view->data = $this->model->getDataReporteValorizacion($data[1]);
                $this->view->render("valorizacion/impresion/ticket", true);
                break;
            case 'valorizacion_a4':
                $this->view->data = $this->model->getDataReporteValorizacion($data[1]);
                $this->view->render("valorizacion/impresion/a4", true);
                break;
            default:
                break;
        }
    }

    public function cerrar_valorizacion()
    {
        echo json_encode($this->model->cerrar_valorizacion($_POST));
    }

    public function pagar_documentos()
    {
        echo json_encode($this->model->pagar_documentos($_POST));
    }

    public function exportar_excel()
    {
        // Verificar permisos
        $data_permisos = Session::get("data_permisos");
        if (!isset($data_permisos["p_guia_transportista"]) || $data_permisos["p_guia_transportista"] != 1) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para exportar']);
            return;
        }

        // Obtener filtros del POST
        $fecha_inicio = $_POST['filtro_fecha_inicio'] ?? '';
        $fecha_fin = $_POST['filtro_fecha_fin'] ?? '';
        $estado = $_POST['filtro_estado'] ?? '';

        // Obtener los datos del modelo
        $datos = $this->model->getDatosExportacion($fecha_inicio, $fecha_fin, $estado);
        $empresa = $this->model->getEmpresa();

        // === SOLUCIÓN: Incluir la vista directamente con las variables ===
        // Establecer las variables en el ámbito global para la vista
        $this->view->datos = $datos;
        $this->view->empresa = $empresa;
        $this->view->fecha_inicio = $fecha_inicio;
        $this->view->fecha_fin = $fecha_fin;
        $this->view->estado = $estado;

        // Incluir manualmente el archivo de la vista
        $view_path = 'views/guia_transportista/php/excel_guiaT.php';

        // Verificar si el archivo existe
        if (file_exists($view_path)) {
            // Extraer las variables de $this->view para que estén disponibles en la vista
            if (isset($this->view)) {
                $view_vars = get_object_vars($this->view);
                extract($view_vars);
            }

            // Incluir la vista
            include $view_path;
        } else {
            error_log("ERROR: No se encuentra el archivo de vista: " . $view_path);
            echo "Error: No se encuentra el archivo de vista";
        }
        exit; // Importante: detener la ejecución después de incluir la vista
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }
}
