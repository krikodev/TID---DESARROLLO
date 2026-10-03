<?php

class Tp_Servicio_PasajeModel extends Model
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
                $searchColumns = ["descripcion"];
                $data['search']['value'] = $search;
            }
            
            $selectFields   = "*";
            $baseQuery      = "FROM tp_servicio_pasaje";
            $orderBy        = "id_tp_servicio_pasaje DESC";

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
            $query =  $this->db->connect()->prepare("INSERT INTO tp_servicio_pasaje (
                descripcion, estado) 
                VALUES(
                :descripcion, :estado)");
            $query->bindParam(':descripcion', $data["descripcion"]);
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
            $query =  $this->db->connect()->prepare("UPDATE tp_servicio_pasaje SET 
            descripcion=:descripcion,
            estado=:estado
            WHERE id_tp_servicio_pasaje=:id_tp_servicio_pasaje");
            $query->bindParam(':id_tp_servicio_pasaje', $data["id_tp_servicio_pasaje"]);
            $query->bindParam(':descripcion', $data["descripcion"]);
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
            $query = $this->db->connect()->prepare("DELETE FROM tp_servicio_pasaje WHERE id_tp_servicio_pasaje=:id_tp_servicio_pasaje");
            $query->bindParam(":id_tp_servicio_pasaje", $data["id_tp_servicio_pasaje"]);
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
