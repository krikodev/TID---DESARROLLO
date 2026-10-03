<?php
require './vendor/autoload.php';
require_once('public/plugins/print/num_letras.php');

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

$logFile = __DIR__ . '/error_log.txt';
$data = $this->data;
$nombre_pc = $data['IMPRESORA']['nombre_pc'];
$nombre_impresora = $data['IMPRESORA']['nombre_impresora'];
$imagen = EscposImage::load(__DIR__ . "/logoLibertadores.png", false);
try {
    // Crear una instancia de la impresora
    $connector = new WindowsPrintConnector("smb://" . $nombre_pc . "/" . $nombre_impresora);
    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text("===============================================\n");
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->bitImage($imagen);
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode($data['EMISOR']['empresa_razon_social']) . "\n");
    $printer->text("RUC: " . utf8_decode($data['EMISOR']['empresa_ruc']) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    for ($i = 0; $i < count($data["TERMINALES"]); $i++) {
        $nombre = utf8_decode($data['TERMINALES'][$i]['nombre']);
        $direccion = utf8_decode($data['TERMINALES'][$i]['direccion_fiscal']);
        $celular = utf8_decode($data['TERMINALES'][$i]['celular']);
        $printer->text("$nombre: $direccion - Cel: $celular\n");
    }
    $printer->text(utf8_decode('DOM. FISCAL' . ': ' . $data['EMISOR']['direccion']) . "\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text("-----------------------------------------------\n");

    $printer->text(utf8_decode($data["CABECERA"]["tp_comprobante"]) . "\n");

    $printer->setEmphasis(true);

    $serie = utf8_decode($data["CABECERA"]["serie"]);
    $correlativo = utf8_decode($data["CABECERA"]["correlativo"]);

    $correlativoFormateado = substr("00000000", 0, -strlen($correlativo)) . $correlativo;
    $printer->text("$serie - $correlativoFormateado\n");

    $printer->setEmphasis(false);

    $printer->text("-----------------------------------------------\n");
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $edad_cliente = explode('-', $data["CLIENTE"]["cliente_fecha_nacimiento"])[0] != "00" ? date("Y") - explode("-", $data["CLIENTE"]["cliente_fecha_nacimiento"])[0] : '---';
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode('PASAJERO:') . "    ");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_nombres"] . " " . $data["CLIENTE"]["cliente_apellidos"]) . "\n");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_tp_docu"]) . ':  ');
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text($data["CLIENTE"]["cliente_num_docu"] . "            ");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode('Edad:' . " "));
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text($edad_cliente . "             ");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_nacionalidad"]) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text("-----------------------------------------------\n");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text("SERVICIO DE TRANSPORTE DE PASAJEROS" . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text(utf8_decode('Unidad medida:') . "    " . 'Servicio' . "               " . utf8_decode('Cantidad:') . "   " . '1' . "\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text(utf8_decode('ORIGEN:') . " " . utf8_decode(strtoupper($data["CABECERA"]["terminal_origen"])) . "\n");
    $printer->text(utf8_decode('DESTINO:') . " " . utf8_decode(strtoupper($data["CABECERA"]["terminal_destino"])));
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode('   Asiento:  '));
    $printer->setTextSize(2, 2);
    $printer->setEmphasis(true); // Activa negrita
    $printer->text($data["ITEMS"][0]["num_asiento"] . ' - ' . trim($data["ITEMS"][0]["piso"]) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text("Fecha-Hora de viaje: ");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(date("d-m-Y", strtotime($data["CABECERA"]["programacion_fecha_salida"])) . " " . date("H:i A", strtotime($data["CABECERA"]["programacion_hora_salida"])) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode('IMPORTE:    '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text('S/. ' . $data["CABECERA"]["total"] . "  " . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode('SON:        '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(numtoletras($data["CABECERA"]["total"]) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode('Forma de pago:  ') . utf8_decode(strtoupper($data["CABECERA"]["forma_pago"])) .  "          " . utf8_decode('Metodo de pago:  ') . utf8_decode(strtoupper($data["CABECERA"]["medio_pago"])) .  "\n");
    $printer->text(utf8_decode('F.H Emision:  ') . date("d-m-Y H:i A", strtotime($data["CABECERA"]["fecha_emision"])) .  "\n");
    $printer->text(utf8_decode('Vendedor(a):  ') . utf8_decode($data["CABECERA"]["vendedor_nombres"] . " " . $data["CABECERA"]["vendedor_apellidos"]) .  "\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text("-----------------------------------------------\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true); // Activa negrita
    $printer->text("CONDICIONES DE SERVICIOS DE VIAJE" . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode("Al recibir el presente DOCUMENTO acepto todos los terminos y\ncondiciones del contrato del servicio de transporte detallado en\nel letrero, banner y/o panel a la vista ubicados en el counter\nde ventas al momento de la compra, los cuales tambien se encuentran publicados en la pagina web.") . "\n");
    $printer->text(utf8_decode("Todo pasajero tiene derecho a 15 Kg. de equipaje, de exceder\ndebera pagar la diferencia de peso segun tarifa de la empresa.") . "\n");
    $printer->text(utf8_decode(URL_PAGE_WEB_COMPROBANTES));
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->text("\n");
    $printer->text("\n");
    $printer->text("-----------------------------------------------\n");
    $printer->cut(Printer::CUT_PARTIAL);
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("CORTE CON LA MANO EN LA LINEA" . "\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode('DE:') . "  " .  utf8_decode(strtoupper($data["CABECERA"]["terminal_origen"])) . "     " . utf8_decode('A:') . "  " . utf8_decode(strtoupper($data["CABECERA"]["terminal_destino"])) . "      " . $data["CABECERA"]["vehiculo_placa"] . "\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->setEmphasis(true); // Activa negrita
    $printer->text($data["CABECERA"]["serie"] . " - " . substr("00000000", 0, -strlen($data["CABECERA"]["correlativo"])) . $data["CABECERA"]["correlativo"] . '   S/' . $data["CABECERA"]["op_exonerada"] . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text(utf8_decode('PASAJERO:') . "  " . utf8_decode($data["CLIENTE"]["cliente_nombres"] . " " . $data["CLIENTE"]["cliente_apellidos"]) . utf8_decode('           ASIENTO:  '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text($data["ITEMS"][0]["num_asiento"] . ' - ' . trim($data["ITEMS"][0]["piso"]) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode('Fecha-Viaje: ') . date("d-m-Y", strtotime($data["CABECERA"]["programacion_fecha_salida"])) . '                Hora-Viaje: ' . date("H:i A", strtotime($data["CABECERA"]["programacion_hora_salida"])) . "\n");
    $printer->text(utf8_decode('F.H emision: ') . date('d-m-Y H:i A', strtotime($data["CABECERA"]["fecha_emision"])) .  "\n");
    $printer->text(utf8_decode('Usuario:     ') . utf8_decode($data["CABECERA"]["vendedor_nombres"] . " " . $data["CABECERA"]["vendedor_apellidos"]) . "\n");
    $printer->text("\n");
    $printer->cut();
    $printer->close();

    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'message' => 'Impresion correcta.'
    ]);
} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . PHP_EOL;
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
    echo "No se pudo imprimir en esta impresora: " . $e->getMessage() . "\n";
}
