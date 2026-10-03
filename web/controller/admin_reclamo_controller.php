<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/conexion_web.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/Exception.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/PHPMailer.php';
require_once __DIR__ . '/../../admin/public/plugins/phpMailer/SMTP.php';

// Variables para mensajes
$mensaje_exito = '';
$mensaje_error = '';
$reclamos = [];

try {
    $database = new Conexion();
    $conn = $database->getConnection();

    // Obtener todos los reclamos con información del terminal
    $query = "SELECT 
                r.*,
                t.nombre as nombre_terminal,
                t.direccion_fiscal,
                e.razon_social as razon_social_empresa
              FROM reclamos r
              INNER JOIN terminal t ON r.id_terminal = t.id_terminal
              INNER JOIN empresa e ON t.id_empresa = e.id_empresa
              ORDER BY r.fecha_creacion DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $reclamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje_error = "Error al cargar los reclamos: " . $e->getMessage();
}

// Procesar respuesta de reclamo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    
    if ($_POST['accion'] === 'responder') {
        try {
            $id_reclamo = intval($_POST['id_reclamo']);
            $estado_reclamo = htmlspecialchars($_POST['estado_reclamo']);
            $observaciones_proveedor = htmlspecialchars($_POST['observaciones_proveedor']);
            $fecha_respuesta = date('Y-m-d');

            // Actualizar el reclamo en la base de datos
            $updateQuery = "UPDATE reclamos SET 
                        estado_reclamo = :estado_reclamo,
                        fecha_respuesta_proveedor = :fecha_respuesta,
                        observaciones_proveedor = :observaciones_proveedor
                      WHERE id = :id_reclamo";

            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':estado_reclamo', $estado_reclamo);
            $updateStmt->bindParam(':fecha_respuesta', $fecha_respuesta);
            $updateStmt->bindParam(':observaciones_proveedor', $observaciones_proveedor);
            $updateStmt->bindParam(':id_reclamo', $id_reclamo);

            if ($updateStmt->execute()) {
                // Obtener datos del reclamo para enviar el correo
                $selectQuery = "SELECT 
                            r.*,
                            t.nombre as nombre_terminal,
                            e.razon_social as razon_social_empresa
                          FROM reclamos r
                          INNER JOIN terminal t ON r.id_terminal = t.id_terminal
                          INNER JOIN empresa e ON t.id_empresa = e.id_empresa
                          WHERE r.id = :id_reclamo";
                
                $selectStmt = $conn->prepare($selectQuery);
                $selectStmt->bindParam(':id_reclamo', $id_reclamo);
                $selectStmt->execute();
                $reclamo = $selectStmt->fetch(PDO::FETCH_ASSOC);

                // Enviar correo al consumidor
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
                    $mail->addAddress($reclamo['correo_consumidor']);

                    $mail->isHTML(true);
                    $mail->Subject = "Respuesta a su Reclamo #{$reclamo['numero_hoja_reclamacion']}";

                    // Estado con colores
                    $estado_color = '';
                    switch ($estado_reclamo) {
                        case 'En proceso':
                            $estado_color = '#FFA500';
                            break;
                        case 'Resuelto':
                            $estado_color = '#28A745';
                            break;
                        case 'Cerrado':
                            $estado_color = '#6C757D';
                            break;
                        default:
                            $estado_color = '#007BFF';
                    }

                    $mail->Body = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #ddd;'>
                        <div style='background:#D43415;color:white;padding:20px;text-align:center;'>
                            <h2 style='margin:0;'>RESPUESTA A SU RECLAMO</h2>
                        </div>
                        <div style='padding:30px;'>
                            <p style='font-size:16px;'>Estimado/a <strong>{$reclamo['nombre_consumidor']}</strong>,</p>
                            
                            <p>Le informamos que hemos procesado su reclamo y deseamos brindarle una respuesta.</p>
                            
                            <div style='background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0;'>
                                <p><strong>Nº de Reclamo:</strong> {$reclamo['numero_hoja_reclamacion']}</p>
                                <p><strong>Fecha de Reclamo:</strong> {$reclamo['fecha']}</p>
                                <p><strong>Sucursal:</strong> {$reclamo['nombre_terminal']}</p>
                                <p><strong>Estado:</strong> <span style='color:{$estado_color};font-weight:bold;'>{$estado_reclamo}</span></p>
                            </div>

                            <div style='border-left:4px solid #D43415;padding-left:15px;margin:20px 0;'>
                                <h3 style='color:#D43415;margin-top:0;'>Respuesta del Proveedor:</h3>
                                <p style='line-height:1.6;'>{$observaciones_proveedor}</p>
                            </div>

                            <p style='margin-top:30px;'>Si tiene alguna pregunta adicional, no dude en contactarnos.</p>
                            
                            <p>Atentamente,<br>
                            <strong>{$reclamo['razon_social_empresa']}</strong></p>
                        </div>
                        <div style='background:#f4f4f4;padding:15px;text-align:center;font-size:12px;color:#666;'>
                            <p style='margin:0;'>Este es un mensaje automático del Libro de Reclamaciones Electrónico</p>
                            <p style='margin:5px 0 0 0;'>Conforme al Decreto Legislativo N° 1308</p>
                        </div>
                    </div>";

                    $mail->send();
                    $mensaje_exito = "Respuesta enviada correctamente al consumidor.";
                } catch (Exception $e) {
                    $mensaje_exito = "Reclamo actualizado correctamente.";
                    $mensaje_error = "Nota: El correo no pudo enviarse. Error: " . $mail->ErrorInfo;
                }

                // Recargar los reclamos después de actualizar
                $reloadQuery = "SELECT 
                                r.*,
                                t.nombre as nombre_terminal,
                                t.direccion_fiscal,
                                e.razon_social as razon_social_empresa
                              FROM reclamos r
                              INNER JOIN terminal t ON r.id_terminal = t.id_terminal
                              INNER JOIN empresa e ON t.id_empresa = e.id_empresa
                              ORDER BY r.fecha_creacion DESC";
                
                $reloadStmt = $conn->prepare($reloadQuery);
                $reloadStmt->execute();
                $reclamos = $reloadStmt->fetchAll(PDO::FETCH_ASSOC);
                
            } else {
                $mensaje_error = "Error al actualizar el reclamo.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error en el procesamiento: " . $e->getMessage();
        }
    }
}

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$total_registros = count($reclamos);
$total_paginas = ceil($total_registros / $registros_por_pagina);
$offset = ($pagina_actual - 1) * $registros_por_pagina;
$reclamos_paginados = array_slice($reclamos, $offset, $registros_por_pagina);
?>