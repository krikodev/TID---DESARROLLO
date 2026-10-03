<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/conexion_web.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/Exception.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/PHPMailer.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/SMTP.php';

// Inicializar variables
$terminales = [];
$mensaje_exito = '';
$mensaje_error = '';
$numero_generado = ''; // Para mostrar el número generado

try {
    $database = new Conexion();
    $conn = $database->getConnection();

    // Obtener terminales con datos de empresa mediante INNER JOIN
    $query = "SELECT 
                t.id_terminal,
                t.nombre as nombre_terminal,
                t.direccion_fiscal,
                t.celular,
                t.email,
                e.num_docu as ruc,
                e.razon_social
              FROM terminal t
              INNER JOIN empresa e ON t.id_empresa = e.id_empresa
              ORDER BY t.nombre";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $terminales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje_error = "Error al cargar los datos: " . $e->getMessage();
}

// Procesar el formulario al enviarse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $id_terminal = intval($_POST['id_terminal']);
        $tipo_persona = htmlspecialchars($_POST['tipo_persona']);
        $tipo_documento = htmlspecialchars($_POST['tipo_documento']);
        $dni_consumidor = htmlspecialchars($_POST['numero_documento']);
        $nombre_consumidor = htmlspecialchars($_POST['nombres']);
        $apellidos = htmlspecialchars($_POST['apellidos']);
        $correo_consumidor = htmlspecialchars($_POST['email']);
        $telefono_consumidor = htmlspecialchars($_POST['celular']);
        $domicilio_consumidor = htmlspecialchars($_POST['direccion']);
        $distrito = htmlspecialchars($_POST['distrito']);
        $provincia = htmlspecialchars($_POST['provincia']);
        $departamento = htmlspecialchars($_POST['departamento']);
        $fecha_hecho = htmlspecialchars($_POST['fecha_hecho']);
        $tipo_reclamo = htmlspecialchars($_POST['tipo_reclamo']);
        $producto_servicio = htmlspecialchars($_POST['producto_servicio']);
        $monto_reclamado = floatval($_POST['monto']);
        $detalle_reclamo = htmlspecialchars($_POST['detalle_reclamo']);
        $pedido_consumidor = htmlspecialchars($_POST['pedido']);
        $nombre_padre_madre = isset($_POST['nombre_padre_madre']) ? htmlspecialchars($_POST['nombre_padre_madre']) : null;
        $ruc_consumidor = isset($_POST['ruc_consumidor']) ? htmlspecialchars($_POST['ruc_consumidor']) : null;
        $razon_social_consumidor = isset($_POST['razon_social_consumidor']) ? htmlspecialchars($_POST['razon_social_consumidor']) : null;

        // Concatenar nombre completo
        $nombre_completo = $nombre_consumidor . ' ' . $apellidos;

        // Si es persona jurídica, usar RUC como documento
        if ($tipo_persona === 'Persona Jurídica') {
            $dni_consumidor = $ruc_consumidor;
            $nombre_completo = $razon_social_consumidor;
        }

        // Insertar en la base de datos SIN el número de hoja (se generará después)
        $query = "INSERT INTO reclamos (
                    id_terminal,
                    fecha,
                    tipo_persona,
                    tipo_documento,
                    dni_consumidor,
                    nombre_consumidor,
                    correo_consumidor,
                    telefono_consumidor,
                    domicilio_consumidor,
                    distrito,
                    provincia,
                    departamento,
                    monto_reclamado,
                    descripcion_producto_servicio,
                    tipo_reclamo,
                    detalles_reclamo,
                    pedido_consumidor,
                    nombre_padre_madre,
                    ruc_consumidor,
                    razon_social_consumidor,
                    estado_reclamo
                  ) VALUES (
                    :id_terminal,
                    :fecha,
                    :tipo_persona,
                    :tipo_documento,
                    :dni_consumidor,
                    :nombre_consumidor,
                    :correo_consumidor,
                    :telefono_consumidor,
                    :domicilio_consumidor,
                    :distrito,
                    :provincia,
                    :departamento,
                    :monto_reclamado,
                    :descripcion_producto_servicio,
                    :tipo_reclamo,
                    :detalles_reclamo,
                    :pedido_consumidor,
                    :nombre_padre_madre,
                    :ruc_consumidor,
                    :razon_social_consumidor,
                    'Pendiente'
                  )";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_terminal', $id_terminal);
        $stmt->bindParam(':fecha', $fecha_hecho);
        $stmt->bindParam(':tipo_persona', $tipo_persona);
        $stmt->bindParam(':tipo_documento', $tipo_documento);
        $stmt->bindParam(':dni_consumidor', $dni_consumidor);
        $stmt->bindParam(':nombre_consumidor', $nombre_completo);
        $stmt->bindParam(':correo_consumidor', $correo_consumidor);
        $stmt->bindParam(':telefono_consumidor', $telefono_consumidor);
        $stmt->bindParam(':domicilio_consumidor', $domicilio_consumidor);
        $stmt->bindParam(':distrito', $distrito);
        $stmt->bindParam(':provincia', $provincia);
        $stmt->bindParam(':departamento', $departamento);
        $stmt->bindParam(':monto_reclamado', $monto_reclamado);
        $stmt->bindParam(':descripcion_producto_servicio', $producto_servicio);
        $stmt->bindParam(':tipo_reclamo', $tipo_reclamo);
        $stmt->bindParam(':detalles_reclamo', $detalle_reclamo);
        $stmt->bindParam(':pedido_consumidor', $pedido_consumidor);
        $stmt->bindParam(':nombre_padre_madre', $nombre_padre_madre);
        $stmt->bindParam(':ruc_consumidor', $ruc_consumidor);
        $stmt->bindParam(':razon_social_consumidor', $razon_social_consumidor);

        if ($stmt->execute()) {
            // Obtener el ID generado automáticamente
            $id_generado = $conn->lastInsertId();
            
            // Generar el número de hoja con formato: REC-AÑO-ID
            $año = date('Y');
            $numero_hoja_reclamacion = "REC-{$año}-" . str_pad($id_generado, 5, '0', STR_PAD_LEFT);
            
            // Actualizar el registro con el número de hoja
            $updateQuery = "UPDATE reclamos SET numero_hoja_reclamacion = :numero WHERE id = :id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':numero', $numero_hoja_reclamacion);
            $updateStmt->bindParam(':id', $id_generado);
            $updateStmt->execute();

            $numero_generado = $numero_hoja_reclamacion;

            // Enviar correo electrónico 
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tidhuancayo@gmail.com';
                $mail->Password   = 'kwlsnpafmnknsjna';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('no-reply@tidhuancayo.com', 'Libro de Reclamaciones');
                $mail->addAddress('tidhuancayo@gmail.com');

                $mail->isHTML(true);
                $mail->Subject = "Nuevo Reclamo #$numero_hoja_reclamacion";

                $tipo = $tipo_persona === 'Persona Jurídica' ? 'Jurídica' : 'Natural';
                $doc = $tipo_persona === 'Persona Jurídica' ? "RUC: $ruc_consumidor" : "DNI: $dni_consumidor";
                $terminal_nombre = '';
                foreach ($terminales as $t) {
                    if ($t['id_terminal'] == $id_terminal) {
                        $terminal_nombre = $t['nombre_terminal'];
                        break;
                    }
                }

                $mail->Body = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #ddd;'>
                    <div style='background:#D43415;color:white;padding:15px;text-align:center;'>
                        <h2>NUEVO RECLAMO</h2>
                    </div>
                    <div style='padding:20px;'>
                        <p><strong>Nº Reclamo:</strong> $numero_hoja_reclamacion</p>
                        <p><strong>Fecha:</strong> $fecha_hecho</p>
                        <hr>
                        <h3>Datos del Cliente</h3>
                        <p><strong>Tipo:</strong> $tipo</p>
                        <p><strong>Documento:</strong> $doc</p>
                        <p><strong>Nombre:</strong> $nombre_completo</p>
                        <p><strong>Email:</strong> $correo_consumidor</p>
                        <p><strong>Celular:</strong> $telefono_consumidor</p>
                        <hr>
                        <h3>Detalles del Reclamo</h3>
                        <p><strong>Sucursal:</strong> $terminal_nombre</p>
                        <p><strong>Tipo:</strong> $tipo_reclamo</p>
                        <p><strong>Producto/Servicio:</strong> $producto_servicio</p>
                        <p><strong>Monto:</strong> S/ $monto_reclamado</p>
                        <p><strong>Detalle:</strong> $detalle_reclamo</p>
                        <p><strong>Pedido:</strong> $pedido_consumidor</p>
                    </div>
                    <div style='background:#f4f4f4;padding:10px;text-align:center;font-size:12px;'>
                        Libro de Reclamaciones Electrónico
                    </div>
                </div>";

                $mail->send();
                $mensaje_exito = "¡Reclamo #$numero_hoja_reclamacion registrado y enviado exitosamente!";
            } catch (Exception $e) {
                $mensaje_exito = "Reclamo #$numero_hoja_reclamacion registrado exitosamente.";
                $mensaje_error = "Nota: No se pudo enviar el correo de notificación.";
            }
        } else {
            $mensaje_error = "Error al registrar el reclamo.";
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error en el registro: " . $e->getMessage();
    }
}
?>