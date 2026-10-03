<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Programacion extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["programacion/css/main.css"];
        $this->view->js = ["programacion/js/main.js", "programacion/js/conductor.js"];
        $this->view->php = ["programacion/php/modal.php"];
        $this->view->render("programacion/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_programacion"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_allTerminalDestino()
    {
        echo json_encode($this->model->get_allTerminalDestino($_POST));
    }

    public function get_allTerminalesParaFiltro()
    {
        echo json_encode($this->model->get_allTerminalesParaFiltro());
    }

    public function get_personalProgramacion()
    {
        echo json_encode($this->model->get_personalProgramacion($_POST));
    }

    public function get_allTerminalRutaProgramacion()
    {
        echo json_encode($this->model->get_allTerminalRutaProgramacion($_POST));
    }

    public function get_allTerminalRutaDestino()
    {
        echo json_encode($this->model->get_allTerminalRutaDestino($_POST));
    }

    public function get_terminalRuta()
    {
        echo json_encode($this->model->get_terminalRuta($_POST));
    }

    public function get_terminalDRuta()
    {
        echo json_encode($this->model->get_terminalDRuta($_POST));
    }

    public function actualizar_program()
    {
        echo json_encode($this->model->actualizar_program($_POST));
    }

    public function cambiar_carro()
    {
        echo json_encode($this->model->cambiar_carro($_POST));
    }

    public function exportar_excel_programacion()
    {
        $this->view->fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $this->view->fecha_fin = $_GET['fecha_fin'] ?? '';
        $this->view->tipo_programacion = $_GET['tipo_programacion'] ?? '';
        $this->view->origen = $_GET['origen'] ?? '';
        $this->view->destino = $_GET['destino'] ?? '';
        $this->view->estado_programacion = $_GET['estado_programacion'] ?? '';
        $this->view->fecha_liquidacion = $_GET['fecha_liquidacion'] ?? '';
        $this->view->search = $_GET['search'] ?? '';

        $this->view->registros =
            $this->model->getDataExportProgramacion(
                $this->view->fecha_inicio,
                $this->view->fecha_fin,
                $this->view->tipo_programacion,
                $this->view->origen,
                $this->view->destino,
                $this->view->estado_programacion,
                $this->view->fecha_liquidacion,
                $this->view->search
            );

        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->render('programacion/impresion/formatos_excel/excel_programacion', true);
    }
}
