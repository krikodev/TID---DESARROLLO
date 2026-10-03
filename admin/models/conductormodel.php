<?php

use function PHPSTORM_META\map;

class ConductorModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {

            $search = normalizar_estado($data['search']['value'] ?? '');

            $terminal_conductor = $data['terminal_conductor'] ?? '';
            $categoria_conductor = $data['categoria_conductor'] ?? '';
            $estado_civil_conductor = $data['estado_civil_conductor'] ?? '';
            $tp_usuario_conductor = $data['tp_usuario_conductor'] ?? '';
            $estado_conductor = $data['estado_conductor'] ?? '';

            $extraWhere = "";
            $extraParams = [];

            if ($search === '0' || $search === '1') {
                $searchColumns  = ["u.estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns  = ["t.nombre", "u.num_docu", "u.nombres", "u.apellidos", "CONCAT(u.nombres, ' ', u.apellidos)", "dt_u.licencia", "dt_u.categoria", "u.estado_civil", "u.celular", "u.direccion", "u.ubigeo", "tp_u.descripcion"];
                $data['search']['value'] = $search;
            }

            if ($terminal_conductor !== '') {
                $extraWhere .= " AND u.id_terminal = ?";
                $extraParams[] = $terminal_conductor;
            }
            if ($categoria_conductor !== '') {
                $extraWhere .= " AND dt_u.categoria = ?";
                $extraParams[] = $categoria_conductor;
            }
            if ($estado_civil_conductor !== '') {
                $extraWhere .= " AND u.estado_civil = ?";
                $extraParams[] = $estado_civil_conductor;
            }
            if ($tp_usuario_conductor !== '') {
                $extraWhere .= " AND u.id_tp_usuario = ?";
                $extraParams[] = $tp_usuario_conductor;
            }
            if ($estado_conductor !== '') {
                $extraWhere .= " AND u.estado = ?";
                $extraParams[] = $estado_conductor;
            }

            $selectFields   = "u.id_usuario,
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
            u.contrasena,
            u.direccion,
            u.ubigeo,
            ub.depa AS ubi_depa,
            ub.provi AS ubi_provi,
            ub.distri AS ubi_distri,
            u.id_tp_usuario,
            u.estado_sunat,
            u.condicion_sunat,
            tp_u.descripcion AS tp_usuario,
            tp_u.tipo AS tp_usuario_tipo,
            u.estado,
            dt_u.licencia AS licencia,
            dt_u.categoria AS ctg_licencia
            ";
            $baseQuery      = "FROM usuario u
            INNER JOIN terminal t ON t.id_terminal=u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu=u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo=u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            LEFT JOIN dt_conductor dt_u ON dt_u.id_usuario=u.id_usuario
            WHERE tp_u.tipo='EXTERNO' AND tp_u.descripcion IN ('CONDUCTOR', 'COPILOTO') AND u.id_usuario != 2
            ";
            $orderBy        = "id_usuario DESC";

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

    public function add_register($data)
    {
        try {
            //Consultando si ya creo al usuario con este tipo de usuario
            $query = $this->db->connect()->prepare("
                        SELECT id_usuario FROM usuario WHERE num_docu = :num_docu AND id_tp_usuario = :id_tp_usuario 
                        ");
            $query->bindParam(':id_tp_usuario', $data['tp_usuario']);
            $query->bindParam(':num_docu', $data['num_docu']);
            $query->execute();
            $id_usu = $query->fetch(PDO::FETCH_ASSOC);
            if (isset($id_usu['id_usuario'])) {
                return array('success' => false, 'message' => 'El usuario ya existe y esta registrado');
            }

            $query =  $this->db->connect()->prepare("INSERT INTO usuario (
                id_terminal, id_tp_docu, num_docu, nombres, apellidos, fecha_nacimiento, estado_civil, genero, direccion, ubigeo, celular, email,
                id_tp_usuario, estado_sunat, condicion_sunat, estado) 
                VALUES(
                :id_terminal, :id_tp_docu, :num_docu, :nombres, :apellidos, :fecha_nacimiento, :estado_civil, :genero, :direccion, :ubigeo, :celular, :email,
                :id_tp_usuario, :estado_sunat, :condicion_sunat, :estado)");
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["fecha_nacimiento"]);
            $query->bindParam(':estado_civil', $data["estado_civil"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':id_tp_usuario', $data["tp_usuario"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();

            $query = $this->db->connect()->prepare("SELECT id_usuario FROM usuario WHERE num_docu = :num_docu");

            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->execute();

            $data["id_conductor"] = $query->fetch(PDO::FETCH_ASSOC)["id_usuario"];

            $this->register_dtConductor($data);

            return array(
                'success' => true,
                'message' => 'Registro creado con éxito',
                'conductor' => array(
                    'id_usuario' => $data['id_conductor'],
                    'nombres'    => $data['nombres'],
                    'apellidos'  => $data['apellidos'],
                    'num_docu'   => $data['num_docu']
                )
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos", $e);
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.", $e);
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query =  $this->db->connect()->prepare("UPDATE usuario SET 
            id_terminal=:id_terminal,
            id_tp_docu=:id_tp_docu,
            num_docu=:num_docu,
            nombres=:nombres,
            apellidos=:apellidos,
            fecha_nacimiento=:fecha_nacimiento,
            estado_civil=:estado_civil,
            genero=:genero,
            direccion=:direccion,
            ubigeo=:ubigeo,
            celular=:celular,
            email=:email,
            id_tp_usuario=:id_tp_usuario,
            estado_sunat=:estado_sunat,
            condicion_sunat=:condicion_sunat,
            estado=:estado
            WHERE id_usuario=:id_conductor");
            $query->bindParam(':id_conductor', $data["id_conductor"]);
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':fecha_nacimiento', $data["fecha_nacimiento"]);
            $query->bindParam(':estado_civil', $data["estado_civil"]);
            $query->bindParam(':genero', $data["genero"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':id_tp_usuario', $data["tp_usuario"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            $this->register_dtConductor($data);
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

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM dt_conductor WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_conductor"]);
            $query->execute();

            $query = $this->db->connect()->prepare("DELETE FROM usuario WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_conductor"]);
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

    public function register_licencias($data)
    {
        $query =  $this->db->connect()->prepare("DELETE FROM dt_conductor WHERE id_usuario=:id_usuario");
        $query->bindParam(':id_usuario', $data["id_conductor"]);
        $query->execute();

        $register = function ($licencias) use ($data) {
            $query =  $this->db->connect()->prepare("INSERT INTO dt_conductor
            (id_usuario, licencia, categoria) VALUES(:id_usuario, :licencia, :categoria)");
            $query->bindParam(':id_usuario', $data["id_conductor"]);
            $query->bindParam(':licencia', $licencias[0]);
            $query->bindParam(':categoria', $licencias[1]);
            $query->execute();
        };
        array_map($register, $data["licencias"]);
    }

    public function register_dtConductor($data)
    {
        $query =  $this->db->connect()->prepare("SELECT * FROM dt_conductor WHERE id_usuario=:id_usuario");
        $query->bindParam(':id_usuario', $data["id_conductor"]);
        $query->execute();
        $reply = $query->fetch(PDO::FETCH_ASSOC);
        if ($reply) {
            $query =  $this->db->connect()->prepare("UPDATE dt_conductor SET 
                licencia=:licencia,
                categoria=:categoria
                WHERE id_usuario=:id_usuario
            ");
        } else {
            $query =  $this->db->connect()->prepare("INSERT INTO dt_conductor (id_usuario, licencia, categoria)
            VALUES(:id_usuario, :licencia, :categoria)");
        }
        $query->bindParam(':id_usuario', $data["id_conductor"]);
        $query->bindParam(':licencia', $data["licencia"]);
        $query->bindParam(':categoria', $data["ctg_licencia"]);
        $query->execute();
    }

    public function getDataExportConductor($data)
    {
        try {

            $terminal = $data['terminal_conductor'] ?? '';
            $categoria = $data['categoria_conductor'] ?? '';
            $estado_civil = $data['estado_civil_conductor'] ?? '';
            $tp_usuario = $data['tp_usuario_conductor'] ?? '';
            $estado = $data['estado_conductor'] ?? '';
            $search = $data['search'] ?? '';

            $where = [
                "tp_u.tipo = 'EXTERNO'",
                "tp_u.descripcion IN ('CONDUCTOR', 'COPILOTO')",
                "u.id_usuario != 2"
            ];

            $params = [];

            if (!empty($terminal)) {
                $where[] = "u.id_terminal = :terminal";
                $params[':terminal'] = $terminal;
            }

            if (!empty($categoria)) {
                $where[] = "dt_u.categoria = :categoria";
                $params[':categoria'] = $categoria;
            }

            if (!empty($estado_civil)) {
                $where[] = "u.estado_civil = :estado_civil";
                $params[':estado_civil'] = $estado_civil;
            }

            if (!empty($tp_usuario)) {
                $where[] = "u.id_tp_usuario = :tp_usuario";
                $params[':tp_usuario'] = $tp_usuario;
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
                OR u.num_docu LIKE :search_num_docu
                OR u.nombres LIKE :search_nombres
                OR u.apellidos LIKE :search_apellidos
                OR CONCAT(u.nombres, ' ', u.apellidos) LIKE :search_nombre_completo
                OR dt_u.licencia LIKE :search_licencia
                OR dt_u.categoria LIKE :search_categoria
                OR u.estado_civil LIKE :search_estado_civil
                OR u.celular LIKE :search_celular
                OR u.direccion LIKE :search_direccion
                OR u.ubigeo LIKE :search_ubigeo
                OR tp_u.descripcion LIKE :search_tp_usuario
            )";

                $params[':search_terminal'] = $searchValue;
                $params[':search_num_docu'] = $searchValue;
                $params[':search_nombres'] = $searchValue;
                $params[':search_apellidos'] = $searchValue;
                $params[':search_nombre_completo'] = $searchValue;
                $params[':search_licencia'] = $searchValue;
                $params[':search_categoria'] = $searchValue;
                $params[':search_estado_civil'] = $searchValue;
                $params[':search_celular'] = $searchValue;
                $params[':search_direccion'] = $searchValue;
                $params[':search_ubigeo'] = $searchValue;
                $params[':search_tp_usuario'] = $searchValue;
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            t.nombre AS terminal,
            u.num_docu,
            CONCAT(u.nombres, ' ', u.apellidos) AS personal,
            dt_u.licencia,
            dt_u.categoria,
            u.estado_civil,
            u.celular,
            u.direccion,
            u.ubigeo,
            tp_u.descripcion AS tp_usuario,
            u.estado
        FROM usuario u
        INNER JOIN terminal t ON t.id_terminal = u.id_terminal
        INNER JOIN ubigeo ub ON ub.cod_ubigeo = u.ubigeo
        INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario = u.id_tp_usuario
        LEFT JOIN dt_conductor dt_u ON dt_u.id_usuario = u.id_usuario
        WHERE $whereClause
        ORDER BY u.id_usuario DESC";

            $query = $this->db->connect()->prepare($sql);

            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }

            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {

            error_log(
                "Error en getDataExportConductor: " .
                    $e->getMessage()
            );

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
}
