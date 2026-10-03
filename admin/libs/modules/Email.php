<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'public/plugins/phpMailer/Exception.php';
require 'public/plugins/phpMailer/PHPMailer.php';
require 'public/plugins/phpMailer/SMTP.php';

define("SMTP_DEBUG", 0);
define("HOST", "smtp.gmail.com; smtp.live.com");            //Cambiar esto al subir al servidor
define("SMT_AUTH", true);                                   //protocolo de uso para enviar
define("SMTP_SECURE", PHPMailer::ENCRYPTION_SMTPS);         //Cambiar esto al subir al servidor (ssl ,tlc)
define("PORT", 465);                                        //Cambiar esto al subir al servidor (465 recomendado)
define("CHAR_SET", "UTF-8");                                //587

class Email
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }

    public function config($dataSystem)
    {
        $this->mail->SMTPDebug = 0;                                 //Enable verbose debug output
        $this->mail->isSMTP();                                      //protocolo de uso para enviar
        $this->mail->Host       = $dataSystem["host"];              //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                             //Enable SMTP authentication
        $this->mail->Username   = $dataSystem["email_send"];        //SMTP username
        $this->mail->Password   = $dataSystem["pass_send"];         //SMTP password
        $this->mail->SMTPSecure = $dataSystem["smtp_secure"];;
        $this->mail->Port       = $dataSystem["port"];
        $this->mail->CharSet       = "UTF-8";

        $this->mail->setFrom($dataSystem["email_send"], "SUPERA LABORAL");
    }

    public function send_email_userpass($dataForm, $dataSystem)
    {
        try {
            //Configuración
            $this->config($dataSystem);
            //Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $dataForm["asunto"];    //asunto

            //Cuerpo del mensaje
            $message = $dataForm["message"];
            if (isset($dataForm["user"])) {
                $user = $dataForm["user"];
                $pass = $dataForm["pass"];
                $credenciales = $message . "<br>" . "Sus credenciales de acceso son:" . "<br>" . "Usuario: " . $user . "<br>" . "Contraseña: " . $pass . "<br>" . "Se recomienda que cambie su contraseña por motivos de seguridad";
            } else {
                $credenciales = $message;
            }
            $rutTemplate = "public/css/plantilla.css";
            $fileTemplate = fopen($rutTemplate, "r");
            $readTemplate = fread($fileTemplate, filesize($rutTemplate));
            fclose($fileTemplate);
            $html = file_get_contents("views/templates/plantilla_email.php");
            $incss  = str_replace('<style id="estilo"></style>', "<style>$readTemplate</style>", $html);
            $cuerpo = str_replace('<p id="mensaje"></p>', $credenciales, $incss);
            $this->mail->Body = $cuerpo;

            //Realizando el envío del correo electronico
            $this->mail->addAddress($dataForm["email"]);
            $replySend = $this->mail->send();
            if ($replySend) {
                return array("success" => true, "message" => "Correo enviado con éxito");
            } else {
                return array("success" => true, "message" => "No se pudo enviar el correo electrónico");
            }
        } catch (Exception $e) {
            // echo json_encode(array("success" => false, "message" => $e));
            echo json_encode(array("success" => false, "message" => "Ha ocurrido un error, intentalo mas tarde"));
        }
    }

    public function send_email_basic($data, $dataSystem)
    {
        try {
            //Configuración
            $this->config($dataSystem);
            //Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $data["asunto"];    //asunto

            if (isset($data["url_file"])) {
                $this->mail->addAttachment($data["url_file"]);
            }
            $this->mail->Body = $this->template($data);

            //Realizando el envío del correo electronico
            $this->mail->addAddress($dataSystem["email_send"]);
            $replySend = $this->mail->send();
            if ($replySend) {
                echo json_encode(array("success" => true, "message" => "Correo enviado con éxito"));
            } else {
                echo json_encode(array("success" => true, "message" => "No se pudo enviar el correo electrónico"));
            }
        } catch (Exception $e) {
            echo json_encode(array("success" => false, "message" => "Ha ocurrido un error, intentalo mas tarde"));
        }
    }

    public function template($data)
    {
        return "
        <head>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC' crossorigin='anonymous'>
        </head>

        <body>
            <div class='container-fluid'>
                <div class='row g-2'>
                    <div class='col-md-12'>
                        <div class='card-header'>
                            <h3 style='background:#0640a4; font-family:Arial; color:white; padding:2vw 0px; text-align:center'>SUPERA LABORAL</h3>
                        </div>
                    </div>
                    <div class='col-md-12'>
                        <div class='card-body'>
                            <div style='font-size:16px; font-family:Arial'>" . $data[1] . "</div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js' integrity='sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM' crossorigin='anonymous'></script>
        ";
    }

    public function deleteFilesDirectory()
    {
        $files = glob('public/file/file_upload/*'); //obtenemos todos los nombres de los ficheros
        $pictures = glob('public/image/image_upload/*'); //obtenemos todos los nombres de los ficheros
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file); //elimino el fichero
        }
        foreach ($pictures as $file) {
            if (is_file($file))
                unlink($file); //elimino el fichero
        }
    }
}
