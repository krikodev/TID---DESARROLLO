<?php
require './vendor/autoload.php';
require_once('public/plugins/print/num_letras.php');

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
// Define la ruta del archivo de log
$logFile = __DIR__ . '/error_log.txt';

//$data = json_decode($_GET['data'],true);
$data = $this->data;
$nombre_pc = $data['IMPRESORA']['nombre_pc'];
$nombre_impresora = $data['IMPRESORA']['nombre_impresora'];
//$imagen = EscposImage::load("logoLibertadores.png", false);
$imagen = EscposImage::load(__DIR__ . "/logoLibertadores.png", false);
try {
    // Crear una instancia de la impresora
    $connector = new WindowsPrintConnector("smb://".$nombre_pc."/".$nombre_impresora);
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
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_tp_docu"]) . ':' . '         ');
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text($data["CLIENTE"]["cliente_num_docu"] . "\n");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_tp_docu"] == "DNI" ? 'CLIENTE:' : 'R. SOCIAL:') . '     ');
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_nombres"]) . " " . utf8_decode($data["CLIENTE"]["cliente_apellidos"]) . "\n");
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(utf8_decode('DIRECCION:') . '   ');
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode($data["CLIENTE"]["cliente_direccion"]) . "\n");
    if ($data["CLIENTE"]["pasajero_nombres"]) {
        $edad_cliente = explode('-', $data["CLIENTE"]["pasajero_fecha_nacimiento"])[0] != "00" ? date("Y") - explode("-", $data["CLIENTE"]["pasajero_fecha_nacimiento"])[0] : '---';
        $printer->setEmphasis(true); // Activa negrita
        $printer->text(utf8_decode('PASAJERO:') . "    ");
        $printer->setEmphasis(false); // Desactiva negrita
        $printer->text(utf8_decode($data["CLIENTE"]["pasajero_nombres"] . " " . $data["CLIENTE"]["pasajero_apellidos"]) . "\n");
        $printer->setEmphasis(true); // Activa negrita
        $printer->text(utf8_decode($data["CLIENTE"]["pasajero_tp_docu"]) . ':  ');
        $printer->setEmphasis(false); // Desactiva negrita
        $printer->text($data["CLIENTE"]["cliente_num_docu"] . "            ");
        $printer->setEmphasis(true); // Activa negrita
        $printer->text(utf8_decode('Edad:' . " "));
        $printer->setEmphasis(false); // Desactiva negrita
        $printer->text($edad_cliente . "             ");
        $printer->setEmphasis(true); // Activa negrita
        $printer->text(utf8_decode($data["CLIENTE"]["pasajero_nacionalidad"]) . "\n");
        $printer->setEmphasis(false); // Desactiva negrita
    }
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
    $printer->text(utf8_decode('Exonerado:  '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text('S/. ' . $data["CABECERA"]["op_exonerada"] . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text(utf8_decode('Gravado:    '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text('S/. 0.00' . "  ");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text('Servicios exonerados del impuesto General' . "\n");
    $printer->text(utf8_decode('IGV:        '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text('S/. 0.00' . "  ");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text('a las ventas https://www.sunat.gob.pe/' . "\n");
    $printer->text(utf8_decode('IMPORTE:    '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text('S/. ' . $data["CABECERA"]["total"] . "  ");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text('legistacion/igv/ley/apendice2.pdf' . "\n");
    $printer->text(utf8_decode('SON:        '));
    $printer->setEmphasis(true); // Activa negrita
    $printer->text(numtoletras($data["CABECERA"]["total"]) . "\n");
    $printer->setEmphasis(false); // Desactiva negrita
    $printer->text("\n");
    if ($data["CABECERA"]["cod_qr"] && file_exists($data["CABECERA"]["cod_qr"])) {
        $width = 180; // Ancho en píxeles del QR
        $height = 180; // Alto en píxeles del QR
        $imagePath = dirname(__DIR__, 4) . "/" . $data["CABECERA"]["cod_qr"];

        // Cargar la imagen original
        $qr = imagecreatefrompng($imagePath);

        // Obtener el tamaño original de la imagen
        $originalWidth = imagesx($qr);
        $originalHeight = imagesy($qr);

        // Crear una nueva imagen más grande (añadir espacio en blanco alrededor)
        $canvasWidth = 570; // Ancho del papel térmico (ajusta según tu impresora)
        $canvasHeight = $height; // Mantener la altura original de la imagen
        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        // Rellenar el lienzo con blanco (el fondo)
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // Copiar la imagen QR redimensionada al centro del nuevo lienzo
        $xOffset = ($canvasWidth - $width) / 2; // Calcular el espacio para centrar la imagen
        imagecopyresampled($canvas, $qr, $xOffset, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        // Guardar la imagen en un archivo temporal
        $tempImagePath = dirname(__DIR__, 4) . "/temp_qr_centered.png";
        imagepng($canvas, $tempImagePath);

        // Liberar memoria
        imagedestroy($qr);
        imagedestroy($canvas);

        // Imprimir la imagen redimensionada centrada
        if (file_exists($tempImagePath)) {
            $qrImage = EscposImage::load($tempImagePath, false);
            $printer->bitImage($qrImage); // No necesitas setJustification si la imagen ya está centrada
            unlink($tempImagePath); // Eliminar la imagen temporal
        }
    }
    $printer->selectPrintMode(Printer::MODE_FONT_B);
    $printer->text("Resoluciones de Superintendencia N.182-2016-SUNAT/ N.318-2017-\nSUNAT\n");
    $printer->text("Usted puede consultar su Factura Electronica desde su clave SOL\n");
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
    $printer->text(utf8_decode('PASAJERO:') . "  " . utf8_decode($data["CLIENTE"]["pasajero_nombres"] . " " . $data["CLIENTE"]["pasajero_apellidos"]) . utf8_decode('           ASIENTO:  '));
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
    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'success',
        'message' => "No se pudo imprimir en esta impresora: " . $e->getMessage() . "\n"
    ]);
}
