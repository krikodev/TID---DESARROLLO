<?php

class EgresosModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $id_caja_chica = $data['id_caja_chica'] ? $data['id_caja_chica'] : 0;
            $selectFields   = "
            e.id,
            cj.referencia,
            e.tp_comprobante,
            e.serie,
            e.correlativo,
            e.monto,
            e.concepto";
            $baseQuery      = "
            FROM egreso_caja e
            LEFT JOIN caja_chica cj ON cj.id_caja_chica=e.cod_caja_chica
            WHERE e.cod_caja_chica = $id_caja_chica";
            $searchColumns = [
                "cj.referencia",
                "e.serie",
                "e.tp_comprobante",
                "e.correlativo",
                "e.monto",
                "e.concepto"
            ];

            $orderBy        = "e.id DESC";

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
            $conn = $this->db->connect();
            if (!empty($data['egresos']) && $data['egresos'] !== '[]') {
                $egresos = json_decode($data['egresos'], true);

                foreach ($egresos as $egreso) {
                    $query2 = $conn->prepare("
                        INSERT INTO egreso_caja
                        (cod_caja_chica, tp_comprobante, serie, correlativo, monto, concepto) 
                        VALUES 
                        (:cod_caja_chica, :tp_comprobante, :serie, :correlativo, :monto, :concepto)
                    ");

                    $query2->bindParam(":cod_caja_chica", $data['id_caja']);
                    $query2->bindParam(":tp_comprobante", $egreso[0]);
                    $query2->bindParam(":serie", $egreso[1]);
                    $query2->bindParam(":correlativo", $egreso[2]);
                    $query2->bindParam(":monto", $egreso[3]);
                    $query2->bindParam(":concepto", $egreso[4]);

                    if (!$query2->execute()) {
                        throw new Exception("Error al insertar egreso de egreso");
                    }
                }
            }

            return ['success' => true, "message" => "Registro creado con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $conn = $this->db->connect();
            $query = $conn->prepare("
            UPDATE egreso_caja
            SET tp_comprobante = :tp_comprobante,
                serie          = :serie,
                correlativo    = :correlativo,
                monto          = :monto,
                concepto       = :concepto
            WHERE id = :id
            ");

            $query->bindParam(":id",             $data['id_egreso']);
            $query->bindParam(":tp_comprobante", $data['tp_comprobante'][0]);
            $query->bindParam(":serie",          $data['serie'][0]);
            $query->bindParam(":correlativo",    $data['correlativo'][0]);
            $query->bindParam(":monto",          $data['monto'][0]);
            $query->bindParam(":concepto",       $data['concepto'][0]);

            if (!$query->execute()) {
                throw new Exception("Error al actualizar egreso");
            }

            return ['success' => true, "message" => "Egreso actualizado con éxito"];
        } catch (PDOException $e) {
            return ['success' => false, "message" => "Ha ocurrido un error, intentalo más tarde."];
        } catch (Exception $e) {
            return ['success' => false, "message" => $e->getMessage()];
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM egreso_caja WHERE id=:id_egreso");
            $query->bindParam(":id_egreso", $data["id_egreso"]);
            $query->execute();
            return ['success' => true, "message" => "Registro eliminado con éxito"];
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "El registro se encuentra protegido"];
                    break;
                default:
                    return ['success' => false, "message" => "Ha ocurrido un error, intentalo mas tarde."];
                    break;
            }
        }
    }

    public function add_egreso_programacion($data)
    {
        $conn = $this->db->connect();
        // Insertar detalles de PASAJES si existen
        if (isset($data['egresos']) && $data['egresos'] != '[]' && !empty($data['egresos'])) {
            $egresos = json_decode($data['egresos']);
            foreach ($egresos as $egreso) {
                $query = $conn->prepare("INSERT INTO egreso (cod_programacion, tp_comprobante, serie, correlativo, monto, concepto) VALUES (:cod_programacion, :tp_comprobante, :serie, :correlativo, :monto, :concepto)");
                $query->bindParam(":cod_programacion", $data["id_programacion"]);
                $query->bindParam(":tp_comprobante", $egreso[0]);
                $query->bindParam(":serie", $egreso[1]);
                $query->bindParam(":correlativo", $egreso[2]);
                $query->bindParam(":monto", $egreso[3]);
                $query->bindParam(":concepto", $egreso[4]);
                $query->execute();
            }
        }
    }
}
