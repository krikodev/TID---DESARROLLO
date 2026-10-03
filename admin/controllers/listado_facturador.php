<?php
// Session::verify_permission('p_facturador'); // Descomentar si necesitas verificar permisos

class Listado_Facturador extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = ["listado_facturador/js/main.js"];
        $this->view->css = ["listado_facturador/css/main.css"];
        $this->view->render("listado_facturador/index");
    }

    public function dataTable()
    {
        $response = $this->model->getDataTable($_POST);
        echo json_encode($response);
        exit;
    }

    public function impresion($data)
    {
        switch ($data[0]) {
            case 'comprobante':
                $this->view->data = $this->model->get_data_comprobante($data[1]);
                $this->view->render('facturador/formatos/comprobante', true);
                break;
            case 'nota_venta':
                $this->view->data = $this->model->get_data_comprobante($data[1]);
                $this->view->render('facturador/formatos/nota_venta', true);
                break;
            default:
                // Redirigir a error o página principal
                header("Location: " . URL . "listado_facturador");
                break;
        }
    }

    public function impresion_a4($data)
    {
        switch ($data[0]) {
            case 'comprobanteA4':
                // Usar el método específico para formato A4 (igual que Facturador)
                $data_comprobante = $this->model->get_data_comprobante_facturador($data[1]);

                if ($data_comprobante['success']) {
                    // Asignar los datos EXACTAMENTE como lo hace Facturador
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/A4/comprobanteA4', true);
                } else {
                    // Redirigir si hay error
                    header("Location: " . URL . "listado_facturador");
                    exit;
                }
                break;

            case 'nota_ventaA4':
                $data_comprobante = $this->model->get_data_comprobante_facturador($data[1]);

                if ($data_comprobante['success']) {
                    $this->view->data = $data_comprobante['message'];
                    $this->view->render('facturador/formatos/A4/nota_ventaA4', true);
                } else {
                    header("Location: " . URL . "listado_facturador");
                    exit;
                }
                break;

            default:
                header("Location: " . URL . "listado_facturador");
                break;
        }
    }

    public function reenviar_sunat()
    {
        echo json_encode($this->model->reenviar_sunat($_POST));
    }

    public function get_comprobante_nota()
    {
        $id_venta = $_POST['id_venta'] ?? 0;
        echo json_encode($this->model->get_comprobante_nota($id_venta));
        exit;
    }

    public function get_items_encomienda()
    {
        $id_venta = $_POST['id_venta'] ?? 0;
        echo json_encode($this->model->get_items_encomienda($id_venta));
        exit;
    }

    public function get_motivos_nota()
    {
        echo json_encode($this->model->motivos_notas());
        exit;
    }

    public function get_serie_for_nota()
    {
        $tp_comprobante = $_POST['tp_comprobante'] ?? '';
        echo json_encode($this->model->get_serieForTpComprobante(['tp_comprobante' => $tp_comprobante]));
        exit;
    }

    public function crear_nota()
    {
        // Si los datos vienen como FormData (del JavaScript actual)
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            // Si vienen como JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
        }

        if (empty($data)) {
            echo json_encode([
                'success' => false,
                'message' => 'No se recibieron datos'
            ]);
            exit;
        }

        // Debug: Ver qué datos llegan
        error_log("Datos recibidos para crear nota: " . print_r($data, true));

        // Convertir productos si vienen como string
        if (isset($data['productos']) && is_string($data['productos'])) {
            $data['productos'] = json_decode($data['productos'], true);
        }

        $response = $this->model->set_data_notas($data);
        echo json_encode($response);
        exit;
    }

    public function anular()
    {
        try {
            $id_comprobante = $_POST['id_comprobante'] ?? ($_POST['id_venta'] ?? 0);

            if (!$id_comprobante) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de comprobante no proporcionado'
                ]);
                exit;
            }

            // Motivo fijo ya que no quieres pedirlo
            $motivo = 'Anulación solicitada por el usuario';

            // Llamar al modelo con el ID correcto
            $response = $this->model->anular_comprobante($id_comprobante, $motivo);

            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function anular_comprobante()
    {
        echo json_encode($this->model->anular_comprobante($_POST));
    }

    public function get_detalle_comprobante()
    {
        echo json_encode($this->model->get_detalle_comprobante($_POST));
    }

    public function get_url_xml()
    {
        $id_comprobante = $_POST['id_comprobante'] ?? 0;

        try {
            $query = $this->db->connect()->prepare("
            SELECT file_xml 
            FROM venta 
            WHERE id_venta = :id_comprobante
        ");
            $query->bindParam(':id_comprobante', $id_comprobante);
            $query->execute();
            $comprobante = $query->fetch(PDO::FETCH_ASSOC);

            if (!$comprobante || empty($comprobante['file_xml'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró archivo XML'
                ]);
                exit;
            }

            // Construir URL directa
            $file_name = $comprobante['file_xml'];
            $file_url = 'https://demoapisunat.tid.com.pe/facturacion/xml/' . $file_name;

            echo json_encode([
                'success' => true,
                'url' => $file_url
            ]);

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function get_url_cdr()
    {
        $id_comprobante = $_POST['id_comprobante'] ?? 0;

        try {
            $query = $this->db->connect()->prepare("
            SELECT file_cdr 
            FROM venta 
            WHERE id_venta = :id_comprobante
        ");
            $query->bindParam(':id_comprobante', $id_comprobante);
            $query->execute();
            $comprobante = $query->fetch(PDO::FETCH_ASSOC);

            if (!$comprobante || empty($comprobante['file_cdr'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró archivo CDR'
                ]);
                exit;
            }

            // Construir URL directa
            $file_name = $comprobante['file_cdr'];
            $file_url = 'https://demoapisunat.tid.com.pe/facturacion/cdr/' . $file_name;

            echo json_encode([
                'success' => true,
                'url' => $file_url
            ]);

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function exportar_excel()
    {
        try {
            // Obtener datos POST (filtros)
            $data = $_POST;

            // Obtener los datos directamente desde el modelo AQUÍ en el controlador
            $datos = $this->model->getDataTableExport($data);
            $empresa = $this->model->getEmpresa();

            // Pasar los datos a la vista a través de view
            $this->view->datos_excel = $datos;
            $this->view->empresa_excel = $empresa;
            $this->view->filtros_excel = $data; // Guardar filtros para mostrarlos

            // Renderizar la vista de exportación
            $this->view->render('listado_facturador/formato/exportacion', true);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ]);
        }
    }

}