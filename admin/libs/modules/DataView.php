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
            e.razon_social AS empresa,
            t.direccion_fiscal,
            t.direccion_comercial,
            t.ubigeo
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

    protected function get_comision_empresa()
    {
        $query = $this->connect()->prepare("SELECT porcentaje_empresa FROM configuracion");
        $query->execute();
        $data = $query->fetchColumn();
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_configuracion_encomienda()
    {
        $query = $this->connect()->prepare("SELECT * FROM configuracion_encomienda");
        $query->execute();
        $data = $query->fetch();
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_configuracion_pasaje()
    {
        $query = $this->connect()->prepare("SELECT * FROM configuracion_pasaje");
        $query->execute();
        $data = $query->fetch();
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }


    protected function get_monedas()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_moneda");
        $query->execute();
        $data = $query->fetchAll();
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }


    protected function get_configuracion_general()
    {
        $query = $this->connect()->prepare("SELECT * FROM configuracion");
        $query->execute();
        $data = $query->fetch();
        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_permisos_mods()
    {
        $query = $this->connect()->prepare("
        SELECT
            id_modulo,
            id_modulo_padre,
            campo_permiso,
            nombre,
            icono,
            tipo,
            visible_menu,
            estado
        FROM modulo
        WHERE estado = 1
        AND visible_menu = 1
        AND (
            tipo = 'MENU'
            OR (
                tipo = 'MODULO'
                AND campo_permiso IS NOT NULL
                AND campo_permiso <> ''
            )
        )
        ORDER BY orden
        ");

        $query->execute();

        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        $modulos = [];

        foreach ($resultado as $row) {

            // Los módulos se indexan por el campo_permiso
            if ($row["tipo"] == "MODULO") {
                $modulos[$row["campo_permiso"]] = $row;
            }
            // Los menús por su nombre (o clave si luego la agregas)
            else {
                $modulos[strtoupper($row["nombre"])] = $row;
            }
        }

        return $modulos;
    }

    public function get_modulos_disponibles()
    {
        $query = $this->connect()->prepare("
        SELECT
            id_modulo,
            id_modulo_padre,
            campo_permiso,
            nombre,
            icono,
            tipo
        FROM modulo
        WHERE
        estado = 1
        AND visible_menu = 1
        AND (
        tipo = 'MENU'
        OR (
            tipo = 'MODULO'
            AND campo_permiso IS NOT NULL
            AND campo_permiso <> ''
           )
        )
        ORDER BY orden
        ");

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    protected function get_series_manifiest()
    {
        $query = $this->connect()->prepare("
            SELECT id_serie_manifiesto, 
            serie, correlativo, 
            numero_autorizacion, estado
            FROM serie_manifiesto
            ORDER BY serie ASC
        ");

        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => "No se encontraron registros"];
        }
    }

    protected function get_series_cotizaciones()
    {
        $query = $this->connect()->prepare("
            SELECT
            c.id_cotizacion,
            CONCAT(c.serie, '-', c.correlativo) AS numero,
            CONCAT(cl.nombres, ' ', apellidos) AS cliente
            FROM cotizacion c
            LEFT JOIN usuario cl ON c.id_cliente = cl.id_usuario
            ORDER BY correlativo ASC
        ");

        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => "No se encontraron registros"];
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

    protected function get_rucs()
    {
        $query = $this->connect()->prepare("
        SELECT 
        num_docu,
        num_docu_encomienda
        FROM empresa
        ");
        $query->execute();
        $data_empresa = $query->fetch(PDO::FETCH_ASSOC);

        $rucs = [];
        $rucs[] = [
            'id' => 1,
            'num_docu' => $data_empresa['num_docu']
        ];

        $rucs[] = [
            'id' => 2,
            'num_docu' => $data_empresa['num_docu_encomienda']
        ];

        if ($rucs) {
            return ["success" => true, "message" => $rucs];
        } else {
            return ["success" => false, "message" => null];
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

    protected function get_proveedor()
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
        WHERE user.id_tp_usuario IN (6) AND user.id_usuario != 2
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
        $query = $this->connect()->prepare("SELECT * FROM medio_pago WHERE estado=1 AND descripcion NOT LIKE '%CULQUI%'");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_tp_operacion_venta()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_operacion_venta WHERE activo=1");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_tp_medio_pago()
    {
        $query = $this->connect()->prepare("SELECT * FROM tp_medio_pago");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return (array("success" => true, "message" => $data));
        } else {
            return (array("success" => false, "message" => null));
        }
    }

    protected function get_config_vehiculo()
    {
        $query = $this->connect()->prepare("SELECT * FROM config_vehiculo  WHERE activo=1");
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

    protected function get_productos()
    {
        $query = $this->connect()->prepare("SELECT * FROM producto WHERE estado = 1");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_unidad_servicio()
    {
        $query = $this->connect()->prepare("SELECT * FROM tipo_unidad_transporte WHERE estado = 1");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_bancos()
    {
        $query = $this->connect()->prepare("SELECT * FROM banco WHERE estado = 'ACTIVO'");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }


    protected function get_afectaciones()
    {
        $query = $this->connect()->prepare("SELECT * FROM afectaciones_igv WHERE id NOT IN (4,5)");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return ["success" => true, "message" => $data];
        } else {
            return ["success" => false, "message" => null];
        }
    }

    protected function get_unidad_medida()
    {
        $query = $this->connect()->prepare("SELECT * FROM unidad_medida");
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
