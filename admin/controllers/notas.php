<?php
// Session::verify_permission('p_inventario_almacen'); //cambiar perimso por medio de pago

class Notas extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $this->view->js = array("notas/js/main.js");
        $this->view->css = array("notas/css/main.css");
        $this->view->render("notas/index");
    }

    public function dataTable()
    {
        echo json_encode($this->model->get_dataTable($_POST));
    }
    public function getTipoNota($id)
    {
        $id = (int) $id;
        $nota = $this->model->getTipoNotaById($id);

        if ($nota) {
            echo json_encode([
                'success' => true,
                'tipo_nota' => $nota['tipo_nota'],
                'tipo_documento' => $nota['tipo_documento'],
                'codigo_comprobante' => $nota['codigo_comprobante'],
                'desc_comprobante' => $nota['desc_comprobante'],
                'serie' => $nota['serie'] ?? '',
                'correlativo' => $nota['correlativo'] ?? ''
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Nota no encontrada'
            ]);
        }
    }

    // Método para impresión en formato Ticket
    public function impresion($data)
    {
        switch ($data[0]) {
            case 'nota_credito':
                $data_nota = $this->model->getNotaCompleta($data[1]);

                if ($data_nota['success']) {
                    // Asignar los datos estructurados como en facturador
                    $this->view->data = $data_nota['message'];

                    // También mantener compatibilidad con las variables actuales
                    $this->view->nota = $data_nota['message']['data_nota'] ?? [];
                    $this->view->detalles = $data_nota['message']['detalles'] ?? [];
                    $this->view->vendedor = $data_nota['message']['vendedor'] ?? null;

                    $this->view->render('notas/impresion/nota_credito', true);
                } else {
                    // Manejar error
                    echo "Error: " . $data_nota['message'];
                    // o redirigir a error
                    // $this->view->render("error/index", true);
                }
                break;

            case 'nota_debito':
                $data_nota = $this->model->getNotaCompleta($data[1]);

                if ($data_nota['success']) {
                    // Asignar los datos estructurados como en facturador
                    $this->view->data = $data_nota['message'];

                    // También mantener compatibilidad con las variables actuales
                    $this->view->nota = $data_nota['message']['data_nota'] ?? [];
                    $this->view->detalles = $data_nota['message']['detalles'] ?? [];
                    $this->view->vendedor = $data_nota['message']['vendedor'] ?? null;

                    $this->view->render('notas/impresion/nota_debito', true);
                } else {
                    // Manejar error
                    echo "Error: " . $data_nota['message'];
                }
                break;

            default:
                header("Location: " . URL . "notas");
                break;
        }
    }

    public function impresion_a4($data)
    {
        switch ($data[0]) {
            case 'nota_creditoA4':
                $data_nota = $this->model->getNotaCompleta($data[1]);

                if ($data_nota['success']) {
                    $this->view->data = $data_nota['message'];
                    $this->view->nota = $data_nota['message']['data_nota'] ?? [];
                    $this->view->detalles = $data_nota['message']['detalles'] ?? [];
                    $this->view->vendedor = $data_nota['message']['vendedor'] ?? null;

                    $this->view->render('notas/impresion/A4/nota_creditoA4', true);
                } else {
                    echo "Error: " . $data_nota['message'];
                }
                break;

            case 'nota_debitoA4':
                $data_nota = $this->model->getNotaCompleta($data[1]);

                if ($data_nota['success']) {
                    $this->view->data = $data_nota['message'];
                    $this->view->nota = $data_nota['message']['data_nota'] ?? [];
                    $this->view->detalles = $data_nota['message']['detalles'] ?? [];
                    $this->view->vendedor = $data_nota['message']['vendedor'] ?? null;

                    $this->view->render('notas/impresion/A4/nota_debitoA4', true);
                } else {
                    echo "Error: " . $data_nota['message'];
                }
                break;

            default:
                header("Location: " . URL . "notas");
                break;
        }
    }

    // Método auxiliar para determinar la vista correcta
    private function determinarVista($tipoSolicitado, $tipoNota, $esA4 = false)
    {
        // Si se solicita específicamente 'nota', determinar automáticamente
        if ($tipoSolicitado === 'nota') {
            // $tipoNota ya debería ser 'credito' o 'debito' según lo que retorne el modelo
            $tipoBase = ($tipoNota === 'debito') ? 'nota_debito' : 'nota_credito';
        } else {
            // Si se especifica directamente el tipo
            $tipoBase = $tipoSolicitado;
        }

        // Agregar sufijo A4 si corresponde
        if ($esA4) {
            $tipoBase .= 'A4';
        }

        return $tipoBase;
    }
}
