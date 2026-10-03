<?php

class Config_ModulosModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        $searchColumns = [
            "m.nombre",
            "m.clave",
            "m.controlador",
            "m.campo_permiso",
            "mp.nombre",
            "m.tipo"
        ];

        try {

            $selectFields = "
            m.id_modulo,
            m.nombre,
            m.clave,
            m.controlador,
            m.campo_permiso,
            m.icono,
            m.tipo,
            m.orden,
            m.visible_menu,
            m.estado,
            mp.nombre AS modulo_padre
            ";

            $baseQuery = "
            FROM modulo m
            LEFT JOIN modulo mp
                ON mp.id_modulo = m.id_modulo_padre
            ";

            $orderBy = "m.id_modulo_padre ASC, m.orden ASC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            foreach ($result['data'] as &$row) {

                if ($row['tipo'] == 'MENU') {
                    $row['controlador'] = '-';
                }

                if (empty($row['modulo_padre'])) {
                    $row['modulo_padre'] = '-';
                }

            }

            return $result;

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
            // Verificar si ya existe la clave
            $query = $conn->prepare("
            SELECT id_modulo
            FROM modulo
            WHERE clave = :clave
            ");
            $query->bindParam(':clave', $data['clave']);
            $query->execute();

            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un módulo con esa clave.'
                ];
            }

            // Verificar nombre
            $query = $conn->prepare("
            SELECT id_modulo
            FROM modulo
            WHERE nombre = :nombre
            ");
            $query->bindParam(':nombre', $data['nombre']);
            $query->execute();

            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un módulo con ese nombre.'
                ];
            }

            // Si es un menú no tiene controlador
            if ($data["tipo"] == "MENU") {
                $data["controlador"] = null;
            }

            // Si no tiene padre
            $idPadre = !empty($data["id_modulo_padre"])
                ? $data["id_modulo_padre"]
                : null;

            $query = $conn->prepare("
            INSERT INTO modulo
            (
                id_modulo_padre,
                nombre,
                descripcion,
                clave,
                controlador,
                campo_permiso,
                icono,
                tipo,
                orden,
                visible_menu,
                estado
            )
            VALUES
            (
                :id_modulo_padre,
                :nombre,
                :descripcion,
                :clave,
                :controlador,
                :campo_permiso,
                :icono,
                :tipo,
                :orden,
                :visible_menu,
                :estado
            )
           ");

            $query->bindParam(':id_modulo_padre', $idPadre);
            $query->bindParam(':nombre', $data['nombre']);
            $query->bindParam(':descripcion', $data['descripcion']);
            $query->bindParam(':clave', $data['clave']);
            $query->bindParam(':controlador', $data['controlador']);
            $query->bindParam(':campo_permiso', $data['campo_permiso']);
            $query->bindParam(':icono', $data['icono']);
            $query->bindParam(':tipo', $data['tipo']);
            $query->bindParam(':orden', $data['orden']);
            $query->bindParam(':visible_menu', $data['visible_menu']);
            $query->bindParam(':estado', $data['estado']);

            $query->execute();

            return [
                'success' => true,
                'message' => 'Módulo creado correctamente.'
            ];

        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        'message' => 'Ya existe un registro con esos datos.'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => 'Ha ocurrido un error.',
                        'error' => $e->getMessage()
                    ];
            }

        }
    }

    public function edit_register($data)
    {
        try {
            $conn = $this->db->connect();

            // Verificar clave duplicada
            $query = $conn->prepare("
            SELECT id_modulo
            FROM modulo
            WHERE clave = :clave
            AND id_modulo <> :id_modulo
            ");
            $query->bindParam(':clave', $data['clave']);
            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->execute();

            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otro módulo con esa clave.'
                ];
            }

            // Verificar nombre duplicado
            $query = $conn->prepare("
            SELECT id_modulo
            FROM modulo
            WHERE nombre = :nombre
            AND id_modulo <> :id_modulo
            ");
            $query->bindParam(':nombre', $data['nombre']);
            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->execute();

            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otro módulo con ese nombre.'
                ];
            }

            // Si es menú no tiene controlador
            if ($data["tipo"] == "MENU") {
                $data["controlador"] = null;
            }

            // Padre
            $idPadre = !empty($data["id_modulo_padre"])
                ? $data["id_modulo_padre"]
                : null;

            $query = $conn->prepare("
            UPDATE modulo SET
                id_modulo_padre = :id_modulo_padre,
                nombre = :nombre,
                descripcion = :descripcion,
                clave = :clave,
                controlador = :controlador,
                campo_permiso = :campo_permiso,
                icono = :icono,
                tipo = :tipo,
                orden = :orden,
                visible_menu = :visible_menu,
                estado = :estado
            WHERE id_modulo = :id_modulo
            ");

            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->bindParam(':id_modulo_padre', $idPadre);
            $query->bindParam(':nombre', $data['nombre']);
            $query->bindParam(':descripcion', $data['descripcion']);
            $query->bindParam(':clave', $data['clave']);
            $query->bindParam(':controlador', $data['controlador']);
            $query->bindParam(':campo_permiso', $data['campo_permiso']);
            $query->bindParam(':icono', $data['icono']);
            $query->bindParam(':tipo', $data['tipo']);
            $query->bindParam(':orden', $data['orden']);
            $query->bindParam(':visible_menu', $data['visible_menu']);
            $query->bindParam(':estado', $data['estado']);

            $query->execute();

            return [
                'success' => true,
                'message' => 'Módulo actualizado correctamente.'
            ];

        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        'message' => 'Ya existe un registro con esos datos.'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => 'Ha ocurrido un error, inténtalo más tarde.',
                        'error' => $e->getMessage()
                    ];
            }

        }
    }

    public function get_register($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
            SELECT *
            FROM modulo
            WHERE id_modulo = :id_modulo
            ");
            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return [
                    'success' => false,
                    'message' => 'El módulo no existe.'
                ];
            }

            return [
                'success' => true,
                'data' => $row
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Ha ocurrido un error.',
                'error' => $e->getMessage()
            ];
        }
    }

    public function get_padres($excludeId = null)
    {
        try {
            $conn = $this->db->connect();

            $sql = "
            SELECT id_modulo, nombre
            FROM modulo
            WHERE tipo = 'MENU'
            AND estado = 1
            ";

            if (!empty($excludeId)) {
                $sql .= " AND id_modulo <> :id_modulo";
            }

            $sql .= " ORDER BY orden ASC";

            $query = $conn->prepare($sql);

            if (!empty($excludeId)) {
                $query->bindParam(':id_modulo', $excludeId);
            }

            $query->execute();

            return [
                'success' => true,
                'data' => $query->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete_register($data)
    {
        try {
            $conn = $this->db->connect();

            // No permitir eliminar un módulo que tiene submódulos hijos
            $query = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM modulo
            WHERE id_modulo_padre = :id_modulo
            ");
            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->execute();
            $check = $query->fetch(PDO::FETCH_ASSOC);

            if ($check['total'] > 0) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar: este módulo tiene submódulos asociados.'
                ];
            }

            $query = $conn->prepare("
            DELETE FROM modulo
            WHERE id_modulo = :id_modulo
            ");
            $query->bindParam(':id_modulo', $data['id_modulo']);
            $query->execute();

            return [
                'success' => true,
                'message' => 'Módulo eliminado correctamente.'
            ];

        } catch (PDOException $e) {

            switch ($e->getCode()) {

                case '23000':
                    return [
                        'success' => false,
                        'message' => 'No se puede eliminar: el módulo está en uso.'
                    ];

                default:
                    return [
                        'success' => false,
                        'message' => 'Ha ocurrido un error al eliminar.',
                        'error' => $e->getMessage()
                    ];
            }
        }
    }
}
