<?php
// Session::verify_permission('p_personal');

class Proveedor extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("proveedor/js/main.js");
        $this->view->php = array("proveedor/php/modal.php");
        $this->view->render("proveedor/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_proveedor"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function exportarExcelProveedor()
    {
        $this->view->terminal = $_GET['terminal_proveedor'] ?? '';
        $this->view->tp_docu = $_GET['tp_docu_proveedor'] ?? '';
        $this->view->estado = $_GET['estado_proveedor'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportProveedor([
            'terminal_proveedor' => $this->view->terminal,
            'tp_docu_proveedor' => $this->view->tp_docu,
            'estado_proveedor' => $this->view->estado,
            'search' => $this->view->search
        ]);

        $this->view->empresa_info = $this->model->getEmpresaInfo();

        $this->view->render(
            'proveedor/impresion/formatos_excel/excel_proveedor',
            true
        );
    }

    public function exportarPDFProveedor()
    {
        $this->view->terminal = $_GET['terminal_proveedor'] ?? '';
        $this->view->tp_docu = $_GET['tp_docu_proveedor'] ?? '';
        $this->view->estado = $_GET['estado_proveedor'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportProveedor([
            'terminal_proveedor' => $this->view->terminal,
            'tp_docu_proveedor' => $this->view->tp_docu,
            'estado_proveedor' => $this->view->estado,
            'search' => $this->view->search
        ]);

        $this->view->empresa_info = $this->model->getEmpresaInfo();

        $this->view->render(
            'proveedor/impresion/formatos_A4/proveedorA4',
            true
        );
    }
}
