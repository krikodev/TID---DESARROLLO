<?php
Session::init();

class LoginModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_empresa()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $data_empresa = $query->fetch(PDO::FETCH_ASSOC);

        if ($data_empresa) {
            // Normalizar la ruta del logo
            $logo = $data_empresa['logo'];

            // Quitar "../img/admin/" si existe
            $logo = str_replace(['../img/admin/', './img/admin/'], '', $logo);

            // Si no existe logo, asignar uno por defecto
            if (empty($logo)) {
                $logo = 'default_logo.png';
            }

            // Reemplazar el valor limpio
            $data_empresa['logo'] = $logo;
        } else {
            $data_empresa = ['logo' => 'default_logo.png'];
        }

        return $data_empresa;
    }

    public function log_in($dataForm)
    {
        $email = $dataForm["email"];
        $pass = $this->security->encryption($dataForm["pass"]);
        $query = $this->db->connect()->prepare("
        SELECT
            u.id_usuario,
            u.id_terminal,
            u.id_tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            u.fecha_nacimiento,
            u.estado_civil,
            u.genero,
            u.celular,
            u.email,
            u.contrasena,
            u.direccion,
            u.ubigeo,
            ubi.depa,
            ubi.provi,
            ubi.distri,
            u.id_tp_usuario,
            tp_u.descripcion AS tp_usuario,
            u.estado,
            u.fecha_registro
        FROM usuario u 
        INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario = u.id_tp_usuario
        INNER JOIN ubigeo ubi ON ubi.cod_ubigeo = u.ubigeo
        WHERE u.estado = 1 AND u.email = :email AND u.contrasena = :pass AND tp_u.tipo IN ('INTERNO', 'DESARROLLO')");
        $query->execute(['email' => $email, 'pass' => $pass]);
        $data_usuario = $query->fetch(PDO::FETCH_ASSOC);

        if ($data_usuario) {
            try {
                // Permisos
                $query = $this->db->connect()->prepare("SELECT * FROM permiso WHERE id_usuario = :id_usuario");
                $query->bindParam(":id_usuario", $data_usuario["id_usuario"]);
                $query->execute();
                $data_permisos = $query->fetch(PDO::FETCH_ASSOC);

                // Empresa (incluye logo)
                $query = $this->db->connect()->prepare("SELECT * FROM empresa");
                $query->execute();
                $data_empresa = $query->fetch(PDO::FETCH_ASSOC);
                // Validar logo
                if ($data_empresa && empty($data_empresa['logo'])) {
                    $data_empresa['logo'] = 'default_logo.png'; // Fallback
                }

                // Terminal
                $query = $this->db->connect()->prepare("SELECT * FROM terminal WHERE id_terminal = :id_terminal");
                $query->bindParam(":id_terminal", $data_usuario["id_terminal"]);
                $query->execute();
                $data_terminal = $query->fetch(PDO::FETCH_ASSOC);
                $data_terminal['c_selva'] = isset($data_terminal['c_selva']) ? $data_terminal['c_selva'] : 0;

                // Permiso para impresión
                $query = $this->db->connect()->prepare("SELECT estado, tipo FROM impresoras WHERE cod_usuario = :id_usuario");
                $query->bindParam(":id_usuario", $data_usuario["id_usuario"]);
                $query->execute();
                $data_imprimir = $query->fetch(PDO::FETCH_ASSOC);
                $permiso_imprimir = isset($data_imprimir['estado']) ? $data_imprimir['estado'] : 0;
                $tipo_impresora = isset($data_imprimir['tipo']) ? $data_imprimir['tipo'] : 0;

                // IGV por sesion
                $query =  $this->db->connect()->prepare("SELECT * FROM configuracion");
                $query->execute();
                $data_config = $query->fetch((PDO::FETCH_ASSOC));

                // Desencriptar contraseña
                $data_usuario["contrasena"] = $this->security->decryption($data_usuario["contrasena"]);

                // Guardar en sesión
                Session::set(NAME_SESSION, true);
                Session::set("data_usuario", $data_usuario);
                Session::set("data_empresa", $data_empresa);
                Session::set("data_terminal", $data_terminal);
                Session::set("data_permisos", $data_permisos);
                Session::set("p_imprimir", $permiso_imprimir);
                Session::set("tipo_impresora", $tipo_impresora);
                Session::set("data_config", $data_config);

                return array('success' => true, "message" => URL);
            } catch (Exception $e) {
                return array('success' => false, "message" => "Ha ocurrido un error. Comuníquese con el administrador.");
            }
        } else {
            return array('success' => false, "message" => "Credenciales incorrectas");
        }
    }
}
