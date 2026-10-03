<?php
// Session::verify_permission('p_pasajero');

class Pasajero extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("pasajero/js/main.js");
        $this->view->php = array("pasajero/php/modal.php");
        $this->view->render("pasajero/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_pasajero"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function get_contactosForID()
    {
        echo json_encode($this->model->get_contactosForID($_POST));
    }

    public function get_pasajeros()
    {
        echo json_encode($this->get_pasajero());
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'historial':
                $this->view->data = $this->model->getDataHistorial($data[1]);
                $this->view->render('pasajero/impresion/historial', true);
                break;
            default:
                break;
        }
    }
    public function registrar_edad_usuario()
    {
        echo json_encode($this->model->registrar_edad_usuario($_POST));
    }

    public function registrar_celular_usuario()
    {
        echo json_encode($this->model->registrar_celular_usuario($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }

    public function exportarExcelPasajeros()
    {
        $this->view->terminal = $_GET['terminal_pasajeros'] ?? '';
        $this->view->tp_docu = $_GET['tp_docu_pasajeros'] ?? '';
        $this->view->estado = $_GET['estado_pasajeros'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportPasajeros(
            $this->view->terminal,
            $this->view->tp_docu,
            $this->view->estado,
            $this->view->search
        );
        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->render(
            'pasajero/impresion/formatos_excel/excel_pasajeros',
            true
        );
    }

    public function exportarPDFPasajeros()
    {
        $this->view->terminal = $_GET['terminal_pasajeros'] ?? '';
        $this->view->tp_docu = $_GET['tp_docu_pasajeros'] ?? '';
        $this->view->estado = $_GET['estado_pasajeros'] ?? '';
        $this->view->search = trim($_GET['search'] ?? '');

        $this->view->registros = $this->model->getDataExportPasajeros(
            $this->view->terminal,
            $this->view->tp_docu,
            $this->view->estado,
            $this->view->search
        );

        $this->view->empresa_info = $this->model->getEmpresaInfo();
        $this->view->render(
            'pasajero/impresion/formatos_A4/pasajerosA4',
            true
        );
    }
}
