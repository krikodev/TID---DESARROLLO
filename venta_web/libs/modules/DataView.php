<?php
Session::init();

class DataView extends Database
{
    protected $db;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function get_ubigeo()
    {
        $query = $this->connect()->prepare("SELECT * FROM ubigeo");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_ubigeo_terminal()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_terminal_sesion = Session::get("data_terminal")["id_terminal"];
            $query = $this->connect()->prepare("
            SELECT
            t.ubigeo,
            u.depa,
            u.provi,
            u.distri
            FROM terminal t
            INNER JOIN empresa e ON e.id_empresa=t.id_empresa
            LEFT JOIN ubigeo u ON u.cod_ubigeo = t.ubigeo 
            WHERE t.id_terminal = :id_terminal");
            $query->bindParam(":id_terminal", $id_terminal_sesion);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                return (array("success" => true, "message" => $data));
            } else {
                return (array("success" => false, "message" => null));
            }
        }
    }

    protected function get_paises()
    {
        $query = $this->connect()->prepare("SELECT * FROM paises");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_empresa()
    {
        $query = $this->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_venta_web()
    {
        $query = $this->connect()->prepare("SELECT venta_web FROM empresa");
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC)['venta_web'];
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }

    protected function get_terminal()
    {
        $query = $this->connect()->prepare("SELECT
            t.id_terminal,
            t.id_empresa,
            t.logo,
            t.nombre,
            t.ubigeo,
            t.direccion_fiscal,
            t.direccion_comercial,
            t.cod_domicilio_fiscal,
            t.celular,
            t.email,
            t.siteweb,
            t.fecha_registro,
            e.razon_social AS empresa
        FROM terminal t
        INNER JOIN empresa e ON e.id_empresa=t.id_empresa");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_terminal_origen()
    {
        $query = $this->connect()->prepare("SELECT
            t.id_terminal,
            t.id_empresa,
            t.logo,
            t.nombre,
            t.ubigeo,
            t.direccion_fiscal,
            t.direccion_comercial,
            t.cod_domicilio_fiscal,
            t.celular,
            t.email,
            t.siteweb,
            t.fecha_registro,
            e.razon_social AS empresa
        FROM terminal t
        INNER JOIN empresa e ON e.id_empresa=t.id_empresa");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_terminal_activo()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_terminal_sesion = Session::get("data_terminal")["id_terminal"];
            $query = $this->connect()->prepare("
            SELECT
            t.id_terminal,
            t.nombre,
            e.razon_social AS empresa
            FROM terminal t
            INNER JOIN empresa e ON e.id_empresa=t.id_empresa
            WHERE t.id_terminal = :id_terminal");
            $query->bindParam(":id_terminal", $id_terminal_sesion);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                return (array("success" => true, "message" => $data));
            } else {
                return (array("success" => false, "message" => null));
            }
        }
    }

    protected function get_tpDocu()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_docu");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_tpComprobante()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_comprobante");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => "No se encontraron registros"));
        }
    }

    protected function get_conductor()
    {
        $query = $this->connect()->prepare("
        SELECT 
            user.id_usuario,
            user.id_terminal,
            user.nombres,
            user.apellidos,
            tp_docu.descripcion AS tp_docu,
            user.num_docu,
            user.id_tp_usuario,
            tp_user.descripcion AS tp_usuario
        FROM usuario user 
        INNER JOIN tp_docu ON tp_docu.id_tp_docu=user.id_tp_docu
        INNER JOIN tp_usuario tp_user ON tp_user.id_tp_usuario=user.id_tp_usuario
        WHERE user.id_tp_usuario IN (4,10) AND user.id_usuario != 2
        ");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_pasajero()
    {
        $query = $this->connect()->prepare("
        SELECT 
        user.id_usuario,
        user.id_terminal,
        user.nombres,
        user.apellidos,
        tp_docu.descripcion AS tp_docu,
        user.num_docu,
        user.id_tp_usuario,
        tp_user.descripcion AS tp_usuario
    FROM usuario user 
    INNER JOIN tp_docu ON tp_docu.id_tp_docu=user.id_tp_docu
    INNER JOIN tp_usuario tp_user ON tp_user.id_tp_usuario=user.id_tp_usuario
    WHERE user.id_tp_usuario=5 AND tp_docu.descripcion = 'DNI' ORDER BY id_usuario DESC;
        ");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_cliente()
    {
        $query = $this->connect()->prepare("
        SELECT 
            user.id_usuario,
            user.id_terminal,
            user.nombres,
            user.apellidos,
            tp_docu.descripcion AS tp_docu,
            user.num_docu,
            user.id_tp_usuario,
            tp_user.descripcion AS tp_usuario
        FROM usuario user 
        INNER JOIN tp_docu ON tp_docu.id_tp_docu=user.id_tp_docu
        INNER JOIN tp_usuario tp_user ON tp_user.id_tp_usuario=user.id_tp_usuario
        WHERE user.id_tp_usuario=5 ORDER BY id_usuario DESC
        ");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_almacen()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_terminal_sesion = Session::get("data_terminal")["id_terminal"];
            $query = $this->connect()->prepare("SELECT * FROM almacen WHERE estado ='1' AND id_terminal=:id_terminal");
            $query->bindParam(":id_terminal", $id_terminal_sesion);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                return (array("success" => true, "message" => $data));
            } else {
                return (array("success" => false, "message" => null));
            }
        }
    }

    protected function get_vehiculo()
    {
        $query = $this->connect()->prepare("SELECT * FROM vehiculo WHERE estado='1'");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_personal()
    {
        $query = $this->connect()->prepare("
            SELECT 
                u.id_usuario,
                u.id_terminal,
                u.nombres,
                u.apellidos,
                u.num_docu,
                u.id_tp_usuario,
                tp_u.descripcion AS tp_usuario
            FROM usuario u 
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            WHERE tp_u.descripcion NOT IN('PASAJERO','PROVEEDOR','CLIENTE', 'SOPORTE', 'ADMINISTRADOR') AND id_usuario != 2");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => "No se encontraron registros"));
        }
    }

    protected function get_tpServicioPasaje()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_servicio_pasaje");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_formaPago()
    {
        $query = $this->connect()->prepare("SELECT * FROM forma_pago");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_medioPago()
    {
        $query = $this->connect()->prepare("SELECT * FROM medio_pago WHERE estado=1");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_producto()
    {
        $query = $this->connect()->prepare("SELECT * FROM ctg_encomienda");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_cajaUserSesion()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_usuario_sesion = Session::get("data_usuario")["id_usuario"];
            $query = $this->connect()->prepare("SELECT * FROM caja_chica WHERE id_usuario=$id_usuario_sesion AND estado=1");
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($data) {
                return (array("success" => true, "message" => $data));
            } else {
                return (array("success" => false, "message" => null));
            }
        }
    }

    protected function get_TpUserSesion()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_usuario_sesion = Session::get("data_usuario")["id_usuario"];
            $query = $this->connect()->prepare("SELECT id_tp_usuario FROM usuario WHERE id_usuario=:id_usuario");
            $query->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                return array("success" => true, "message" => $data['id_tp_usuario']);
            } else {
                return array("success" => false, "message" => null);
            }
        }
        return array("success" => false, "message" => "Sesión no inicializada");
    }

    // Funciones autonomas del sistema
    protected function upload_permisosUser()
    {
        if (isset($_SESSION[NAME_SESSION])) {
            $id_usuario = Session::get("data_usuario")["id_usuario"];
            $query = $this->connect()->prepare("SELECT * FROM permiso WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $id_usuario);
            $query->execute();
            $data_permisos = $query->fetch(PDO::FETCH_ASSOC);
            $query = $this->connect()->prepare("
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
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            INNER JOIN ubigeo ubi ON ubi.cod_ubigeo=u.ubigeo
            WHERE u.id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $id_usuario);
            $query->execute();
            $data_usuario = $query->fetch(PDO::FETCH_ASSOC);
            if ($data_permisos) {
                Session::set("data_permisos", $data_permisos);
                Session::set("data_usuario", $data_usuario);
            }
        }
    }
}
