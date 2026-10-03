<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Nota_Venta extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->css = ["nota_venta/css/main.css"];
        $this->view->js = ["nota_venta/js/main.js", "nota_venta/js/cliente.js"];
        $this->view->php = ["nota_venta/php/modal.php"];
        $this->view->render("nota_venta/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->get_dataTable($_POST));
    }

    public function get_notasxpagar()
    {
        echo json_encode($this->model->get_notasxpagar($_POST));
    }

    public function pagar_notas_bloque()
    {
        echo json_encode($this->model->pagar_notas_bloque($_POST));
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

    // public function exportar_excel()
    // {
    //     try {
    //         // Obtener filtros del POST
    //         $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    //         $fecha_fin = $_POST['fecha_fin'] ?? null;
    //         $tipo = $_POST['tipo'] ?? null;
    //         $estado = $_POST['estado'] ?? null;

    //         // Obtener datos directamente del modelo
    //         $datos = $this->model->getDatosExportacionNotasVenta(
    //             $fecha_inicio,
    //             $fecha_fin,
    //             $tipo,
    //             $estado
    //         );

    //         // Obtener información de la empresa
    //         $empresa = $this->model->getEmpresa();

    //         // Guardar en variables globales
    //         $GLOBALS['export_datos'] = $datos;
    //         $GLOBALS['export_empresa'] = $empresa;
    //         $GLOBALS['export_fecha_inicio'] = $fecha_inicio;
    //         $GLOBALS['export_fecha_fin'] = $fecha_fin;
    //         $GLOBALS['export_tipo'] = $tipo;
    //         $GLOBALS['export_estado'] = $estado;

    //         // Incluir directamente la vista sin usar el render del framework
    //         include 'views/nota_venta/php/excel_notasventa.php';
    //         exit;
    //     } catch (Exception $e) {
    //         echo json_encode([
    //             'success' => false,
    //             'message' => 'Error al exportar: ' . $e->getMessage()
    //         ]);
    //     }
    // }

    public function exportar_excel()
    {
        $config_id = isset($_POST['config_id']) ? (int)$_POST['config_id'] : 1;

        $datos = $this->model->getDatosExportacionNotasVenta(
            $_POST['fecha_inicio'] ?? null,
            $_POST['fecha_fin']    ?? null,
            $_POST['tipo']         ?? null,
            $_POST['estado']       ?? null,
            $config_id,
            $_POST['search']       ?? ''
        );

        $GLOBALS['export_datos']        = $datos;
        $GLOBALS['export_empresa']      = $this->model->getEmpresa();
        $GLOBALS['export_fecha_inicio'] = $_POST['fecha_inicio'] ?? null;
        $GLOBALS['export_fecha_fin']    = $_POST['fecha_fin']    ?? null;
        $GLOBALS['export_tipo']         = $_POST['tipo']         ?? null;
        $GLOBALS['export_estado']       = $_POST['estado']       ?? null;

        // $GLOBALS['export_tiene_config'] = !empty($config_id);
        $GLOBALS['export_tiene_config'] = false;

        require 'views/nota_venta/php/excel_notasventa.php';
    }

    public function reenviar_venta()
    {
        echo json_encode($this->model->reenviar_venta($_POST));
    }

    public function buscar_nacionalidad()
    {
        $q = $_GET["q"] ?? "";

        echo json_encode($this->model->buscar_nacionalidad($q));
    }
}
