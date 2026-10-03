<?php
// Session::verify_permission('p_personal');

class Personal extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("personal/css/main.css");
        $this->view->js = array("personal/js/main.js");
        $this->view->php = array("personal/php/modal.php");
        $this->view->render("personal/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_personal"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_otrosDatos()
    {
        echo json_encode($this->model->get_otrosDatos($_POST));
    }

    public function send_email()
    {
        echo json_encode($this->model->send_email($_POST));
    }
    public function get_comisiones()
    {
        echo json_encode($this->model->getComisiones());
    }

    public function get_tipos_usuario()
    {
        echo json_encode($this->model->getTiposUsuario());
    }

    public function get_personalxterminal()
    {
        echo json_encode($this->model->get_personalxterminal($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }

    public function exportarExcelPersonal()
    {
        $this->view->terminal = $_GET['terminal_personal'] ?? '';
        $this->view->tp_usuario = $_GET['tp_usuario_personal'] ?? '';
        $this->view->genero = $_GET['genero_personal'] ?? '';
        $this->view->estado = $_GET['estado_personal'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportPersonal([
            'terminal_personal' => $this->view->terminal,
            'tp_usuario_personal' => $this->view->tp_usuario,
            'genero_personal' => $this->view->genero,
            'estado_personal' => $this->view->estado,
            'search' => $this->view->search
        ]);

        $this->view->empresa_info = $this->model->getEmpresaInfo();

        $this->view->render(
            'personal/impresion/formatos_excel/excel_personal',
            true
        );
    }

    public function exportarPDFPersonal()
    {
        $this->view->terminal = $_GET['terminal_personal'] ?? '';
        $this->view->tp_usuario = $_GET['tp_usuario_personal'] ?? '';
        $this->view->genero = $_GET['genero_personal'] ?? '';
        $this->view->estado = $_GET['estado_personal'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportPersonal([
            'terminal_personal' => $this->view->terminal,
            'tp_usuario_personal' => $this->view->tp_usuario,
            'genero_personal' => $this->view->genero,
            'estado_personal' => $this->view->estado,
            'search' => $this->view->search
        ]);

        $this->view->empresa_info = $this->model->getEmpresaInfo();

        $this->view->render(
            'personal/impresion/formatos_A4/personalA4',
            true
        );
    }
}
