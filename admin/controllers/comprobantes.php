<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

use PhpOffice\PhpSpreadsheet\Calculation\TextData\Search;

class Comprobantes extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = array("comprobantes/css/main.css");
        $this->view->js = array("comprobantes/js/main.js");
        $this->view->php = array("comprobantes/php/modal.php");
        $this->view->render("comprobantes/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->get_dataTable($_POST));
    }

    public function get_dataTableResumen()
    {
        echo json_encode($this->model->get_dataTableResumen($_POST));
    }

    public function get_dataTableReenvio()
    {
        echo json_encode($this->model->get_dataTableReenvio($_POST));
    }

    public function get_comprobante_n()
    {
        $id_venta = intval($_POST['id_venta']);
        echo json_encode($this->model->get_comprobante_nota($id_venta));
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('pasaje/impresion/comprobante/comprobante_nuevo', true);
                break;
            case 'nota_venta':
                $this->view->data = $this->model->getDataComprobante($data[1]);
                $this->view->render('pasaje/impresion/nota_venta/nota_ventanuevo', true);
                break;
            default:
                break;
        }
    }

    public function exportarExcel()
    {
        // Obtener parámetros de POST O GET (para soportar window.location.href)
        $fecha_inicio = $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? '';
        $tp_comprobante = $_POST['tp_comprobante'] ?? $_GET['tp_comprobante'] ?? '';
        $tipo_venta = $_POST['tipo_venta'] ?? $_GET['tipo_venta'] ?? '';
        $search = $_POST['search'] ?? $_GET['search'] ?? '';
        

        // LOG PARA DEPURACIÓN
        error_log("=== EXPORTAR EXCEL DESDE CONTROLADOR ===");
        error_log("Fecha Inicio: " . $fecha_inicio);
        error_log("Fecha Fin: " . $fecha_fin);
        error_log("Tipo Comprobante: " . $tp_comprobante);
        error_log("Tipo Venta: " . $tipo_venta);
        error_log("Search: " . $search);

        // Obtener los datos
        $datos = $this->model->getDataExportComprobantes(
            $fecha_inicio,
            $fecha_fin,
            $tp_comprobante,
            $tipo_venta,
            $search
        );

        $empresa = $this->model->getEmpresa();

        // LOG PARA VERIFICAR DATOS
        error_log("Datos obtenidos: " . (is_array($datos) ? count($datos) : 0) . " registros");

        // EXTRAER VARIABLES PARA LA VISTA (SOLUCIÓN AL PROBLEMA)
        extract([
            'datos' => $datos,
            'empresa' => $empresa,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'tp_comprobante' => $tp_comprobante,
            'tipo_venta' => $tipo_venta,
            'search' => $search
        ]);

        // Limpiar cualquier salida previa
        ob_clean();

        // Incluir la vista directamente
        include 'views/comprobantes/php/excel_comprobantes.php';
        exit;
    }

    public function exportarExcelResumenes()
    {
        // Obtener parámetros de POST O GET
        $fecha_inicio = $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? '';

        // LOG PARA DEPURACIÓN
        error_log("=== EXPORTAR EXCEL RESUMENES DESDE CONTROLADOR ===");
        error_log("Fecha Inicio: " . $fecha_inicio);
        error_log("Fecha Fin: " . $fecha_fin);

        // Obtener los datos
        $datos = $this->model->getDataExportResumenes($fecha_inicio, $fecha_fin);
        $empresa = $this->model->getEmpresa();

        // LOG PARA VERIFICAR DATOS
        error_log("Datos obtenidos: " . (is_array($datos) ? count($datos) : 0) . " registros");

        // EXTRAER VARIABLES PARA LA VISTA
        extract([
            'datos' => $datos,
            'empresa' => $empresa,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);

        // Limpiar cualquier salida previa
        ob_clean();

        // Incluir la vista directamente
        include 'views/comprobantes/php/excel_resumenes.php';
        exit;
    }

    public function getSelectMotivos()
    {
        echo json_encode($this->model->motivos_notas());
    }

    public function setNotas($data)
    {
        if ($data == "07") {
            echo json_encode($this->model->setNotaCredito($data));
        } else {
            echo json_encode($this->model->setNotaDevito($data));
        }
    }
    public function get_serieForTpComprobante()
    {
        echo json_encode($this->model->get_serieForTpComprobante($_POST));
    }
    public function set_notas()
    {
        echo json_encode($this->model->set_data_notas($_POST));
    }
    public function get_items_n()
    {
        echo json_encode($this->model->get_items_encomienda($_POST));
    }

    public function pagar_comprobante()
    {
        echo json_encode($this->model->pagar_comprobante($_POST));
    }

    public function detalle_pago_cuota()
    {
        echo json_encode($this->model->detalle_pago_cuota($_POST));
    }
}
