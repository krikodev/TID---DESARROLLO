<?php

class ProveedorModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $search = normalizar_estado($data['search']['value'] ?? '');

            $terminal_proveedor = $data['terminal_proveedor'] ?? '';
            $tp_docu_proveedor = $data['tp_docu_proveedor'] ?? '';
            $estado_proveedor = $data['estado_proveedor'] ?? '';

            $extraWhere = "";
            $extraParams = [];

            if ($search === '0' || $search === '1') {
                $searchColumns  = ["u.estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns  = ["t.nombre", "u.nombres", "u.apellidos", "CONCAT(u.nombres, ' ', u.apellidos)", "tp_d.descripcion", "u.num_docu", "u.celular", "u.email", "u.direccion", "u.ubigeo"];
                $data['search']['value'] = $search;
            }

            if ($terminal_proveedor !== '') {
                $extraWhere .= " AND u.id_terminal = ?";
                $extraParams[] = $terminal_proveedor;
            }
            if ($tp_docu_proveedor !== '') {
                $extraWhere .= " AND u.id_tp_docu = ?";
                $extraParams[] = $tp_docu_proveedor;
            }
            if ($estado_proveedor !== '') {
                $extraWhere .= " AND u.estado = ?";
                $extraParams[] = $estado_proveedor;
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
            u.estado
            ";
            $baseQuery      = "FROM usuario u
            INNER JOIN terminal t ON t.id_terminal=u.id_terminal
            INNER JOIN tp_docu tp_d ON tp_d.id_tp_docu=u.id_tp_docu
            INNER JOIN ubigeo ub ON ub.cod_ubigeo=u.ubigeo
            INNER JOIN tp_usuario tp_u ON tp_u.id_tp_usuario=u.id_tp_usuario
            WHERE tp_u.tipo='EXTERNO' AND tp_u.descripcion IN ('PROVEEDOR')
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

            $conn = $this->db->connect();

            $query = $conn->prepare("
            INSERT INTO usuario (
                id_terminal,
                id_tp_docu,
                num_docu,
                nombres,
                apellidos,
                direccion,
                ubigeo,
                celular,
                email,
                id_tp_usuario,
                estado_sunat,
                condicion_sunat,
                estado
            ) 
            VALUES (
                :id_terminal,
                :id_tp_docu,
                :num_docu,
                :nombres,
                :apellidos,
                :direccion,
                :ubigeo,
                :celular,
                :email,
                6,
                :estado_sunat,
                :condicion_sunat,
                :estado
            )
        ");

            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);

            $query->execute();

            // ID del proveedor recién creado
            $id_usuario = $conn->lastInsertId();

            return array(
                'success' => true,
                'message' => 'Registro creado con éxito',
                'proveedor' => array(
                    'id_usuario' => $id_usuario,
                    'num_docu'   => $data['num_docu'],
                    'nombres'    => $data['nombres'],
                    'apellidos'  => $data['apellidos'] ?? ''
                )
            );
        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':

                    return array(
                        'success' => false,
                        'message' => 'Ya existe un registro con estos datos'
                    );

                default:

                    return array(
                        'success' => false,
                        'message' => 'Ha ocurrido un error, inténtalo más tarde.'
                    );
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
            direccion=:direccion,
            ubigeo=:ubigeo,
            celular=:celular,
            email=:email,
            estado_sunat=:estado_sunat,
            condicion_sunat=:condicion_sunat,
            estado=:estado
            WHERE id_usuario=:id_proveedor");
            $query->bindParam(':id_proveedor', $data["id_proveedor"]);
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_docu', $data["tp_docu"]);
            $query->bindParam(':num_docu', $data["num_docu"]);
            $query->bindParam(':nombres', $data["nombres"]);
            $query->bindParam(':apellidos', $data["apellidos"]);
            $query->bindParam(':direccion', $data["direccion"]);
            $query->bindParam(':ubigeo', $data["ubigeo"]);
            $query->bindParam(':celular', $data["celular"]);
            $query->bindParam(':email', $data["email"]);
            $query->bindParam(':estado_sunat', $data["estado_sunat"]);
            $query->bindParam(':condicion_sunat', $data["condicion_sunat"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            return array('success' => true, "message" => "Registro modificado con éxito");
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => $e->getMessage());
                    return array('success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde.");
                    break;
            }
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM usuario WHERE id_usuario=:id_usuario");
            $query->bindParam(":id_usuario", $data["id_proveedor"]);
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

    public function getDataExportProveedor($data)
    {
        try {
            $terminal = $data['terminal_proveedor'] ?? '';
            $tp_docu = $data['tp_docu_proveedor'] ?? '';
            $estado = $data['estado_proveedor'] ?? '';
            $search = $data['search'] ?? '';

            $where = [
                "tp_u.tipo = 'EXTERNO'",
                "tp_u.descripcion = 'PROVEEDOR'"
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
                OR u.email LIKE :search_email
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
                $params[':search_email'] = $searchValue;
                $params[':search_direccion'] = $searchValue;
                $params[':search_ubigeo'] = $searchValue;
            }

            $whereClause = implode(' AND ', $where);

            $sql = "SELECT
            t.nombre AS terminal,
            CONCAT(u.nombres, ' ', u.apellidos) AS proveedor,
            tp_d.descripcion AS tp_docu,
            u.num_docu,
            u.celular,
            u.email,
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

            error_log("Error en getDataExportProveedor: " . $e->getMessage());

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
