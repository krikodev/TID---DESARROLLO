<?php
// Session::verify_permission('p_caja_chica');

class Caja_Chica extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("caja_chica/js/main.js");
        $this->view->php = array("caja_chica/php/modal.php");
        $this->view->render("caja_chica/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->getDataTable($_POST));
    }

    public function crud_register()
    {
        if ($_POST["id_caja_chica"]) {
            echo json_encode($this->model->edit_register($_POST));
        } else {
            echo json_encode($this->model->add_register($_POST));
        }
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'cierre':
                $this->view->data = $this->model->getDataReporteCierre($data[1]);
                $this->view->render('caja_chica/impresion/cierre_a4', true);
                break;

            case 'cierre_ticket':
                $this->view->data = $this->model->getDataReporteCierre($data[1]);
                $this->view->render('caja_chica/impresion/cierre_ticket', true);
                break;
            case 'liquidacion_terminal':
                $this->view->data = $this->model->getDataReporteLiquidacionTerminal($data[1], $data[2]);
                $this->view->render('caja_chica/impresion/liquidacion_terminal', true);
                break;
            case 'liquidacion_usuario':
                $this->view->data = $this->model->getDataReporteLiquidacionUsuario($data[1]);
                $this->view->render('caja_chica/impresion/liquidacion_usuario', true);
                break;
            case 'reporte_caja_cerrada_a4':
                $this->view->data = $this->model->getDataReporteCajaCerradas($data[1], $data[2], $data[3]);
                $this->view->render('caja_chica/impresion/rc_cxcerradas_a4', true);
                break;
            case 'reporte_caja_cerrada_ticket':
                $this->view->data = $this->model->getDataReporteCajaCerradas($data[1], $data[2], $data[3]);
                $this->view->render('caja_chica/impresion/rc_cxcerradas_ticket', true);
                break;
            default:
                break;
        }
    }

    public function delete_register()
    {
        echo json_encode($this->model->delete_register($_POST));
    }

    public function cerrar_caja()
    {
        echo json_encode($this->model->cerrar_caja($_POST));
    }

    public function consultarCajasLiquidacionTerminal()
    {
        echo json_encode($this->model->consultarCajasLiquidacionTerminal($_POST));
    }

    public function buscar_cajas_cerradas_reporte()
    {
        echo json_encode($this->model->buscar_cajas_cerradas_reporte($_POST));
    }
}
