<?php
class Guia_transportista extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $data_permisos = Session::get("data_permisos");

        if ($data_permisos["p_guia_transportista"] == 1) {
            $this->view->css = ["guia_transportista/css/main.css"];
            $this->view->js = ["guia_transportista/js/main.js", "guia_transportista/js/cliente.js"];
            $this->view->php = ["guia_transportista/php/modal.php"];
            $this->view->render("guia_transportista/index");
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

    public function get_guiasxpagar()
    {
        echo json_encode($this->model->get_guiasxpagar($_POST));
    }

    public function pagar_guias_bloque()
    {
        echo json_encode($this->model->pagar_guias_bloque($_POST));
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

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $data_comprobante = $this->model->get_data_comprobante($data[1]);


                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/comprobante', true);
                } else {
                    $this->view->render("error/index", true);
                }
                break;
            default:
                break;
        }
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }
}
