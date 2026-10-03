<?php

class SerieModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $selectFields   = "s.id_serie,
            s.id_terminal,
            t.nombre AS terminal_nombre,
            t.tipo AS terminal_tipo,
            t.cod_domicilio_fiscal,
            s.id_tp_comprobante,
            tp_c.descripcion AS tp_comprobante,
            s.serie,
            s.correlativo";
            $baseQuery      = "
            FROM serie s
            LEFT JOIN terminal t ON t.id_terminal=s.id_terminal
            LEFT JOIN tp_comprobante tp_c ON tp_c.id_tp_comprobante=s.id_tp_comprobante";
            $searchColumns = [
                "t.nombre",
                "t.cod_domicilio_fiscal",
                "tp_c.descripcion",
                "s.serie",
                "s.correlativo"
            ];

            $orderBy        = "s.id_serie DESC";

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
            $query =  $this->db->connect()->prepare("INSERT INTO serie (
                id_terminal, id_tp_comprobante, serie, correlativo) 
                VALUES(
                :id_terminal, :id_tp_comprobante, :serie, :correlativo)");
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':serie', $data["serie"]);
            $query->bindParam(':correlativo', $data["correlativo"]);
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


    public function add_register_notas($data)
    {
        try {
            $query = $this->db->connect()->prepare("INSERT INTO serie (
            id_terminal, id_tp_comprobante, serie, correlativo) 
            VALUES(
            :id_terminal, :id_tp_comprobante, :serie, :correlativo)");

            $successCount = 0;

            // Obtener el id_terminal y tp_comprobante que son comunes a todos los registros
            $idTerminal = $data["terminal"];
            $tpComprobante = $data["tp_comprobante"];

            // Iterar sobre los campos específicos de serie y correlativo
            for ($i = 2; $i <= 3; $i++) {
                $serieKey = "serie" . $i;
                $correlativoKey = "correlativo" . $i;

                if (isset($data[$serieKey]) && isset($data[$correlativoKey])) {
                    $serie = $data[$serieKey];
                    $correlativo = $data[$correlativoKey];

                    // Asignar parámetros dentro del bucle
                    $query->bindParam(':id_terminal', $idTerminal);
                    $query->bindParam(':id_tp_comprobante', $tpComprobante);
                    $query->bindParam(':serie', $serie);
                    $query->bindParam(':correlativo', $correlativo);

                    // Ejecutar la consulta dentro del bucle
                    $query->execute();

                    // Verificar si la inserción fue exitosa
                    if ($query->rowCount() > 0) {
                        $successCount++;
                    }

                    // Limpiar los parámetros para la próxima iteración
                    $query->closeCursor();
                }
            }

            if ($successCount > 0) {
                return array('success' => true, "message" => "Registros creados con éxito");
            } else {
                return array('success' => false, "message" => "No se proporcionaron datos para la inserción");
            }
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array('success' => false, "message" => "Ya existe un registro con estos datos");
                    break;
                default:
                    return array('success' => false, "message" => "Ha ocurrido un error, inténtalo más tarde.");
                    break;
            }
        }
    }

    public function edit_register($data)
    {
        try {
            $query =  $this->db->connect()->prepare("UPDATE serie SET 
            id_terminal=:id_terminal,
            id_tp_comprobante=:id_tp_comprobante,
            serie=:serie,
            correlativo=:correlativo
            WHERE id_serie=:id_serie");
            $query->bindParam(':id_serie', $data["id_serie"]);
            $query->bindParam(':id_terminal', $data["terminal"]);
            $query->bindParam(':id_tp_comprobante', $data["tp_comprobante"]);
            $query->bindParam(':serie', $data["serie"]);
            $query->bindParam(':correlativo', $data["correlativo"]);
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
            $query = $this->db->connect()->prepare("DELETE FROM serie WHERE id_serie=:id_serie");
            $query->bindParam(":id_serie", $data["id_serie"]);
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
