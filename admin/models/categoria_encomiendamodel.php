<?php

class Categoria_EncomiendaModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $selectFields   = "*";
            $baseQuery      = "FROM ctg_encomienda";
            $searchColumns  = ["descripcion", "precio", "afectacion", "codigo"];
            $orderBy        = "id_ctg_encomienda DESC";

            return $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
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
            $db = $this->db->connect();
            $db->beginTransaction();

            $query = $db->prepare("
            INSERT INTO ctg_encomienda (descripcion, precio, afectacion)
            VALUES (:descripcion, :precio, :afectacion)
            ");
            $query->bindParam(':descripcion', $data["descripcion"]);
            $query->bindParam(':precio', $data["precio"]);
            $query->bindParam(':afectacion', $data["afectacion"]);
            $query->execute();

            $id_insert = $db->lastInsertId();
            $nuevoCodigo = 'PRD' . str_pad($id_insert, 4, '0', STR_PAD_LEFT);

            $query = $db->prepare("
            UPDATE ctg_encomienda
            SET codigo = :codigo
            WHERE id_ctg_encomienda = :id_ctg_encomienda
            ");
            $query->bindParam(':codigo', $nuevoCodigo);
            $query->bindParam(':id_ctg_encomienda', $id_insert);
            $query->execute();

            $db->commit();

            return [
                'success' => true,
                'message' => 'Registro creado con éxito',
                'data' => [
                    'id_ctg_encomienda' => (int)$id_insert,
                    'descripcion' => $data["descripcion"],
                    'precio' => $data["precio"],
                    'codigo' => $nuevoCodigo
                ],
                'codigo_generado' => $nuevoCodigo
            ];
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo más tarde."];
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query =  $this->db->connect()->prepare("UPDATE ctg_encomienda SET descripcion=:descripcion, precio=:precio, afectacion=:afectacion WHERE id_ctg_encomienda=:id_ctg_encomienda");
            $query->bindParam(':id_ctg_encomienda', $data["id_ctg_encomienda"]);
            $query->bindParam(':descripcion', $data["descripcion"]);
            $query->bindParam(':precio', $data["precio"]);
            $query->bindParam(':afectacion', $data["afectacion"]);
            $query->execute();
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
            $query = $this->db->connect()->prepare("DELETE FROM ctg_encomienda WHERE id_ctg_encomienda=:id_ctg_encomienda");
            $query->bindParam(":id_ctg_encomienda", $data["id_ctg_encomienda"]);
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
    public function buscar_productos($q)
    {
        $conn = $this->db->connect();

        $query = $conn->prepare("
        SELECT id_ctg_encomienda, descripcion, precio
        FROM ctg_encomienda
        WHERE descripcion LIKE ?
        LIMIT 20
    ");

        $query->execute(["%$q%"]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
