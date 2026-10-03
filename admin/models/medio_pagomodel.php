<?php

class Medio_PagoModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {

            $search = normalizar_estado($data['search']['value'] ?? '');

            if ($search === '0' || $search === '1') {
                $searchColumns = ["estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns = ["descripcion", "fecha_registro"];
                $data['search']['value'] = $search;
            }


            $selectFields   = "id_medio_pago, descripcion, estado, DATE(fecha_registro) AS fecha_registro";
            $baseQuery      = "FROM medio_pago";
            $orderBy        = "id_medio_pago DESC";

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
            $descripcion = strtoupper($data["descripcion"]);
            $query =  $this->db->connect()->prepare("INSERT INTO medio_pago (
                descripcion, estado) 
                VALUES(
                :descripcion, :estado)");
            $query->bindParam(':descripcion', $descripcion);
            $query->bindParam(':estado', $data["estado"]);
            $query->execute();
            return array('success' => true, "message" => "Registro creado con éxito");
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
            $descripcion = strtoupper($data["descripcion"]);
            $query =  $this->db->connect()->prepare("UPDATE medio_pago SET 
            descripcion=:descripcion,
            estado=:estado
            WHERE id_medio_pago=:id_medio_pago");
            $query->bindParam(':id_medio_pago', $data["id_medio_pago"]);
            $query->bindParam(':descripcion', $descripcion);
            $query->bindParam(':estado', $data["estado"]);
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
            $query = $this->db->connect()->prepare("DELETE FROM medio_pago WHERE id_medio_pago=:id_medio_pago");
            $query->bindParam(":id_medio_pago", $data["id_medio_pago"]);
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
}
