<?php
// Session::verify_permission('p_conductores');

class Conductor extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("conductor/js/main.js");
        $this->view->php = array("conductor/php/modal.php");
        $this->view->render("conductor/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_conductor"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function exportarExcelConductor()
    {
        $this->view->terminal = $_GET['terminal_conductor'] ?? '';
        $this->view->categoria = $_GET['categoria_conductor'] ?? '';
        $this->view->estado_civil = $_GET['estado_civil_conductor'] ?? '';
        $this->view->tp_usuario = $_GET['tp_usuario_conductor'] ?? '';
        $this->view->estado = $_GET['estado_conductor'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportConductor([
            'terminal_conductor' => $this->view->terminal,
            'categoria_conductor' => $this->view->categoria,
            'estado_civil_conductor' => $this->view->estado_civil,
            'tp_usuario_conductor' => $this->view->tp_usuario,
            'estado_conductor' => $this->view->estado,
            'search' => $this->view->search
        ]);
        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->render(
            'conductor/impresion/formatos_excel/excel_conductor',
            true
        );
    }

    public function exportarPDFConductor()
    {
        $this->view->terminal = $_GET['terminal_conductor'] ?? '';
        $this->view->categoria = $_GET['categoria_conductor'] ?? '';
        $this->view->estado_civil = $_GET['estado_civil_conductor'] ?? '';
        $this->view->tp_usuario = $_GET['tp_usuario_conductor'] ?? '';
        $this->view->estado = $_GET['estado_conductor'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportConductor([
            'terminal_conductor' => $this->view->terminal,
            'categoria_conductor' => $this->view->categoria,
            'estado_civil_conductor' => $this->view->estado_civil,
            'tp_usuario_conductor' => $this->view->tp_usuario,
            'estado_conductor' => $this->view->estado,
            'search' => $this->view->search
        ]);
        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->render(
            'conductor/impresion/formatos_A4/conductorA4',
            true
        );
    }
}
