<?php

class PasajeroModel extends Model
{
    private $usersDetalleInterno;

    function __construct()
    {
        parent::__construct();
        $this->usersDetalleInterno = array("INSTRUCTOR", "ESPECIALISTA");
    }

    public function getDataTable($data)
    {
        try {
            $search = normalizar_estado($data['search']['value'] ?? '');
            $terminal_pasajeros = $data['terminal_pasajeros'] ?? '';
            $tp_docu_pasajeros = $data['tp_docu_pasajeros'] ?? '';
            $estado_pasajeros = $data['estado_pasajeros'] ?? '';
            $extraWhere = "";
            $extraParams = [];

            if ($search === '0' || $search === '1') {
                $searchColumns = ["u.estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns = ["t.nombre", "u.nombres", "u.apellidos", "CONCAT(u.nombres, ' ', u.apellidos)", "tp_d.descripcion", "u.num_docu", "u.celular", "u.fecha_nacimiento", "u.nacionalidad", "u.direccion", "u.ubigeo"];
                $data['search']['value'] = $search;
            }

            if ($terminal_pasajeros !== '') {
                $extraWhere .= " AND u.id_terminal = ?";
                $extraParams[] = $terminal_pasajeros;
            }
            if ($tp_docu_pasajeros !== '') {
                $extraWhere .= " AND u.id_tp_docu = ?";
                $extraParams[] = $tp_docu_pasajeros;
            }
            if ($estado_pasajeros !== '') {
                $extraWhere .= " AND u.estado = ?";
                $extraParams[] = $estado_pasajeros;
            }

            $selectFields = "u.id_usuario,
            u.id_terminal,
            t.nombre AS terminal,
            t.tipo AS terminal_tipo,
            u.id_tp_docu,
            tp_d.descripcion AS tp_docu,
            u.num_docu,
            u.nombres,
            u.apellidos,
            u.fecha_nacimiento,
            u.estado_civil,
            u.genero,
            u.celular,
            u.email,
            u.direccion,
            u.ubigeo,
            ub.depa AS ubi_depa,
            ub.provi AS ubi_provi,
            ub.distri AS ubi_distri,
            u.id_tp_usuario,
            tp_u.descripcion AS tp_usuario,
            tp_u.tipo AS tp_usuario_tipo,
            u.nacionalidad,
            u.estado_sunat,
            u.condicion_sunat,
            u.estado
            ";
            $baseQuery = "FROM usuario u
            INNER JOIN terminal t ON t.id_terminal=u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu=u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo=u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            WHERE tp_u.tipo='EXTERNO' AND tp_u.descripcion IN ('PASAJERO')
            ";
            $orderBy = "id_usuario DESC";

            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy,
                $extraWhere,
                $extraParams
            );
        } catch (PDOException $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error en la consulta: " . $e->getMessage()
            ];
        }
    }

    public function getDataExportPasajeros($terminal, $tp_docu, $estado, $search = '')
    {
        try {
            $where = [
                "tp_u.tipo = 'EXTERNO'",
                "tp_u.descripcion IN ('PASAJERO')"
            ];
            $params = [];
            if (!empty($terminal)) {
                $where[] = "u.id_terminal = :terminal";
                $params[':terminal'] = $terminal;
            }
            if (!empty($tp_docu)) {
                $where[] = "u.id_tp_docu = :tp_docu";
                $params[':tp_docu'] = $tp_docu;
            }
            if ($estado !== '') {
                $where[] = "u.estado = :estado";
                $params[':estado'] = $estado;
            }
            if (!empty($search)) {
                $search = trim($search);
                $searchValue = '%' . $search . '%';

                $where[] = "(
                    t.nombre LIKE :search_terminal
                    OR u.nombres LIKE :search_nombres
                    OR u.apellidos LIKE :search_apellidos
                    OR CONCAT(u.nombres, ' ', u.apellidos) LIKE :search_nombre_completo
                    OR tp_d.descripcion LIKE :search_tp_docu
                    OR u.num_docu LIKE :search_num_docu
                    OR u.celular LIKE :search_celular
                    OR u.fecha_nacimiento LIKE :search_fecha_nacimiento
                    OR u.nacionalidad LIKE :search_nacionalidad
                    OR u.direccion LIKE :search_direccion
                    OR u.ubigeo LIKE :search_ubigeo
                )";

                $params[':search_terminal'] = $searchValue;
                $params[':search_nombres'] = $searchValue;
                $params[':search_apellidos'] = $searchValue;
                $params[':search_nombre_completo'] = $searchValue;
                $params[':search_tp_docu'] = $searchValue;
                $params[':search_num_docu'] = $searchValue;
                $params[':search_celular'] = $searchValue;
                $params[':search_fecha_nacimiento'] = $searchValue;
                $params[':search_nacionalidad'] = $searchValue;
                $params[':search_direccion'] = $searchValue;
                $params[':search_ubigeo'] = $searchValue;
            }

            $whereClause = implode(' AND ', $where);

            $sql =
                "SELECT
                t.nombre AS terminal,
                tp_d.descripcion AS tp_docu,
                u.num_docu,
                u.nombres,
                u.apellidos,
                u.genero,
                u.fecha_nacimiento,
                u.nacionalidad,
                u.celular,
                u.direccion,
                u.ubigeo,
                u.estado
            FROM usuario u
            INNER JOIN terminal t ON t.id_terminal = u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu = u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo = u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario = u.id_tp_usuario
            WHERE $whereClause
            ORDER BY u.id_usuario DESC";
            $query = $this->db->connect()->prepare($sql);
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDataExportPasajeros: " . $e->getMessage());
            return [];
        }
    }

    public function getEmpresaInfo()
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT 
                e.id_empresa,
                e.razon_social AS nombre,
                e.num_docu AS ruc,
                e.direccion_fiscal AS direccion,
                e.logo
            FROM empresa e
            WHERE e.id_empresa = :id_empresa
            LIMIT 1
        ");

            $id_empresa = $this->id_empresa_sesion ?? 1;
            $query->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $query->execute();

            $empresa = $query->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                // Valores por defecto si no hay empresa
                return [
                    'id_empresa' => $id_empresa,
                    'nombre' => 'EMPRESA DE TRANSPORTES',
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'logo' => '',
                    'logo_exists' => false,
                    'logo_absolute_path' => ''
                ];
            }

            // Construir la ruta absoluta del logo
            $logo_absolute_path = '';
            $logo_exists = false;

            if (!empty($empresa['logo'])) {

                $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin/' . $empresa['logo'];

                if (file_exists($logo_absolute_path)) {
                    $logo_exists = true;
                } else {
                    // Intenta con otra ruta posible
                    $logo_absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/tid-transporte/img/admin' . $empresa['logo'];
                    $logo_exists = file_exists($logo_absolute_path);
                }
            }

            // Agregar los campos calculados
            $empresa['logo_exists'] = $logo_exists;
            $empresa['logo_absolute_path'] = $logo_exists ? $logo_absolute_path : '';

            return $empresa;
        } catch (PDOException $e) {
            error_log("Error en getEmpresaInfo: " . $e->getMessage());
            return [
                'id_empresa' => $this->id_empresa_sesion ?? 1,
                'nombre' => 'EMPRESA DE TRANSPORTES',
                'ruc' => '',
                'direccion' => '',
                'telefono' => '',
                'logo' => '',
                'logo_absolute_path' => '',
                'logo_exists' => false
            ];
        }
    }

    public function add_register($data)
    {
        $nombre = trim($data["nombres"]);
        try {
            //Consultando si ya creo al usuario con este tipo de usuario
            $tp_u = 5;
            $query = $this->db->connect()->prepare("
                        SELECT id_usuario FROM usuario WHERE num_docu = :num_docu AND id_tp_usuario = :id_tp_usuario 
                        ");
            $query->bindParam(':id_tp_usuario', $tp_u);
            $query->bindParam(':num_docu', $data['num_docu']);
            $query->execute();
            $id_usu = $query->fetch(PDO::FETCH_ASSOC);
            if (isset($id_usu['id_usuario'])) {
                return array('success' => false, 'message' => 'El usuario ya existe y esta registrado');
            }

            $query = $this->db->connect()->prepare("INSERT INTO usuario (
                id_terminal, id_tp_docu, num_docu, nombres, apellidos, fecha_nacimiento, direccion, ubigeo, genero, celular, email,
                id_tp_usuario, nacionalidad, estado_sunat, condicion_sunat, estado) 
                VALUES(
                :id_terminal, :id_tp_docu, :num_docu, :nombres, :apellidos, :fecha_nacimiento, :direccion, :ubigeo, :genero, :celular, :email,
                5, :nacionalidad, :estado_sunat, :condicion_sunat, :estado)");
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $nombre);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["fecha_nacimiento"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':nacionalidad', $data["nacionalidad"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();

            $query = $this->db->connect()->prepare("SELECT id_usuario FROM usuario WHERE num_docu=:num_docu");
            $query->bindParam(":num_docu", $data["num_docu"]);
            $query->execute();
            $data["id_pasajero"] = $query->fetch(PDO::FETCH_ASSOC)["id_usuario"];
            $data["contactos"] = json_decode($data["contactos"]);
            $this->register_contactos($data);
            return array('success' => true, "message" => "Registro creado con éxito", "usuario" => $data['num_docu']);
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("UPDATE usuario SET 
            id_terminal=:id_terminal,
            id_tp_docu=:tp_docu,
            num_docu=:num_docu,
            nombres=:nombres,
            apellidos=:apellidos,
            fecha_nacimiento=:fecha_nacimiento,
            genero=:genero,
            direccion=:direccion,
            ubigeo=:ubigeo,
            celular=:celular,
            email=:email,
            nacionalidad=:nacionalidad,
            estado_sunat=:estado_sunat,
            condicion_sunat=:condicion_sunat,
            estado=:estado
            WHERE id_usuario=:id_usuario");
            $query->bindParam(':id_usuario', $data["id_pasajero"]);
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["fecha_nacimiento"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':nacionalidad', $data["nacionalidad"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            $data["contactos"] = json_decode($data["contactos"]);
            $this->register_contactos($data);
            return array('success' => true, "message" => "Registro modificado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function register_contactos($data)
    {
        $query = $this->db->connect()->prepare("DELETE FROM contacto_cliente WHERE id_usuario=:id_usuario");
        $query->bindParam(':id_usuario', $data["id_pasajero"]);
        $query->execute();

        $register = function ($contactos) use ($data) {
            $query = $this->db->connect()->prepare("INSERT INTO contacto_cliente
            (id_usuario, nombres, apellidos, celular, observacion) VALUES(:id_usuario, :nombres, :apellidos, :celular, :observacion)");
            $query->bindParam(':id_usuario', $data["id_pasajero"]);
            $query->bindParam(':nombres', $contactos[0]);
            $query->bindParam(':apellidos', $contactos[1]);
            $query->bindParam(':celular', $contactos[2]);
            $query->bindParam(':observacion', $contactos[3]);
            $query->execute();
        };
        array_map($register, $data["contactos"]);
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM contacto_cliente WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_pasajero"]);
            $query->execute();

            $query = $this->db->connect()->prepare("DELETE FROM usuario WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_pasajero"]);
            $query->execute();
            return array('success' => true, "message" => "Registro eliminado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "El registro se encuentra protegido");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function get_contactosForID($data)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM contacto_cliente WHERE id_usuario=:id_usuario");
        $query->bindParam(":id_usuario", $data["id_pasajero"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function get_pasajero($data)
    {
        $search = $data["search"];
        $query = $this->db->connect()->prepare("SELECT * FROM usuario WHERE nombres LIKE '%$search%' OR apellidos LIKE '%$search%' OR num_docu LIKE '%$search%'");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function getDataHistorial($id_pasajero)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $empresa = $query->fetch(PDO::FETCH_ASSOC);
        //Obtener datos del cliente
        $query = $this->db->connect()->prepare("SELECT
            u.nombres AS cliente_nombres,
            u.apellidos AS cliente_apellidos,
            u.id_tp_docu AS cliente_id_tp_docu,
            tp_d_c.descripcion AS cliente_tp_docu,
            u.num_docu AS cliente_num_docu,
            u.direccion AS cliente_direccion,
            u.ubigeo AS cliente_ubigeo,
            u.fecha_nacimiento AS cliente_fecha_nacimiento,
            u.nacionalidad AS cliente_nacionalidad
        FROM usuario u
        LEFT JOIN tp_docu tp_d_c ON tp_d_c.id_tp_docu=u.id_tp_docu
        WHERE u.id_usuario = :id_pasajero");
        $query->bindParam(":id_pasajero", $id_pasajero);
        $query->execute();
        $cliente = $query->fetch(PDO::FETCH_ASSOC);

        //Obtener todos los viajes que pudo haber hecho
        $query = $this->db->connect()->prepare("SELECT
            vd.nombres AS vendedor_nombres,
            vd.apellidos AS vendedor_apellidos,
            v.serie,
            v.correlativo,
            v.fecha_emision,
            date_format(v.fecha_emision, '%Y-%m-%d') AS fecha_emision_format,
            date_format(v.fecha_emision, '%H:%i:%s') AS hora_emision_format,
            v.id_forma_pago,
            f_p.descripcion AS forma_pago,
            v.id_medio_pago,
            m_p.descripcion AS medio_pago,
            v.fecha_vencimiento,
            v.id_tp_moneda,
            tp_m.codigo AS tp_moneda_codigo,
            tp_m.descripcion AS tp_moneda_descripcion,
            v.id_tp_comprobante,
            v.op_igv,
            v.op_gravada,
            v.op_exonerada,
            v.op_inafecta,
            v.total,
            v.descuento,
            CONCAT(c.nombres, ' ',c.apellidos) AS cliente,
            tp_c.descripcion AS tp_comprobante,
            tp_c.codigo AS tp_comprobante_codigo,
            p.fecha_salida AS programacion_fecha_salida,
            p.hora_salida AS programacion_hora_salida,
            vh.placa AS vehiculo_placa,
            v.estado,
            v.fecha_registro,
            date_format(v.fecha_registro, '%Y-%m-%d') AS fecha_registro_format,
            date_format(v.fecha_registro, '%H:%i:%s') AS hora_registro_format,
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino
        FROM venta v
        LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=v.id_tp_comprobante
        LEFT JOIN forma_pago f_p ON f_p.id_forma_pago= v.id_forma_pago
        LEFT JOIN medio_pago m_p ON m_p.id_medio_pago= v.id_medio_pago
        LEFT JOIN dt_venta dt_v ON dt_v.id_venta= v.id_venta
        LEFT JOIN programacion p ON p.id_programacion=dt_v.id_programacion
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo
        LEFT JOIN tp_moneda tp_m ON tp_m.id_tp_moneda=v.id_tp_moneda
        LEFT JOIN usuario vd ON vd.id_usuario=v.id_vendedor
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino
        LEFT JOIN usuario c ON v.id_cliente = c.id_usuario
        WHERE dt_v.estado_asiento != 'ANULADO' AND dt_v.id_pasajero = :id_pasajero;");
        $query->bindParam(":id_pasajero", $id_pasajero);
        $query->execute();
        $viajes = $query->fetchAll(PDO::FETCH_ASSOC);

        return array(
            "empresa" => $empresa,
            "cliente" => $cliente,
            "viajes" => $viajes
        );
    }

    public function registrar_edad_usuario($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare('
            UPDATE usuario 
            SET fecha_nacimiento = :fecha_nacimiento 
            WHERE id_usuario = :id_usuario
           ');

            $query->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
            $query->bindParam(':id_usuario', $data['id_usuario_edad']);

            $query->execute();

            return ['success' => true, 'message' => 'Se registró con éxito la edad.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ops... hubo un problema'];
        }
    }

    public function registrar_celular_usuario($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare('
            UPDATE usuario 
            SET celular = :celular 
            WHERE id_usuario = :id_usuario
           ');

            $query->bindParam(':celular', $data['celular']);
            $query->bindParam(':id_usuario', $data['id_usuario_celular']);

            $query->execute();

            return ['success' => true, 'message' => 'Se registró con éxito el número de celular.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ops... hubo un problema'];
        }
    }

    public function buscar_nacionalidad($q)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT 
            cod_pais, 
            nombre_pais, 
            abrev,
            gentilicio
        FROM paises
        WHERE nombre_pais LIKE ?
        LIMIT 5
        ");

        $query->execute(["%$q%"]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
