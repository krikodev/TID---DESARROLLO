<?php

class VehiculoModel extends Model
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
                $searchColumns = ["v.estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns = ["v.descripcion", "v.placa", "v.marca", "v.modelo", "v.soat", "v.num_piso", "v.num_asiento", "v.n_mtc"];
                $data['search']['value'] = $search;
            }

            $selectFields = "v.id_vehiculo,
            v.descripcion,
            v.placa,
            v.marca,
            v.modelo,
            v.soat,
            v.file_tarjeta_propiedad,
            v.serie_motor,
            v.num_ejes,
            v.num_piso,
            v.frontal,
            v.trasero,
            v.width,
            v.height,
            v.num_asiento,
            v.tuc,
            v.num_poliza,
            v.n_mtc,
            v.estado";
            $baseQuery = "FROM vehiculo v";
            $orderBy = "id_vehiculo DESC";

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
            if ($_FILES["tarjeta_propiedad"]["error"] == 0) {
                $reply = $this->upload->upload_basic(
                    $_FILES["tarjeta_propiedad"],
                    "private/vehiculos/" . $data["placa"] . "/",
                    "tarjeta_propiedad_" . $data["terminal"] . ".pdf"
                );

                if ($reply["success"]) {
                    $tarjeta_propiedad = $reply["message"];
                } else {
                    return array(
                        'success' => false,
                        "message" => "Ha ocurrido un error al subir el archivo"
                    );
                }
            } else {
                $tarjeta_propiedad = $data["tarjeta_propiedad_before"];
            }

            $query = $conn->prepare("INSERT INTO vehiculo (
            descripcion, placa, marca, modelo, soat, file_tarjeta_propiedad, serie_motor, num_ejes, num_piso,
            width, height, num_asiento, tuc, num_poliza, estado, n_mtc
        ) VALUES (
            :descripcion, :placa, :marca, :modelo, :soat, :file_tarjeta_propiedad, :serie_motor, :num_ejes, :num_piso,
            '300px', '400px', :num_asiento, :tuc, :num_poliza, :estado, :n_mtc
        )");

            $query->bindParam(':descripcion', $data["descripcion"]);
            $query->bindParam(':placa', $data["placa"]);
            $query->bindParam(':marca', $data["marca"]);
            $query->bindParam(':modelo', $data["modelo"]);
            $query->bindParam(':soat', $data["soat"]);
            $query->bindParam(':file_tarjeta_propiedad', $tarjeta_propiedad);
            $query->bindParam(':serie_motor', $data["serie_motor"]);
            $query->bindParam(':num_ejes', $data["num_ejes"]);
            $query->bindParam(':num_piso', $data["num_piso"]);
            $query->bindParam(':num_asiento', $data["num_asiento"]);
            $query->bindParam(':tuc', $data["tuc"]);
            $query->bindParam(':num_poliza', $data["num_poliza"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->bindParam(':n_mtc', $data["n_mtc"]);

            $query->execute();

            // ID del vehículo recién creado
            $id_vehiculo = $conn->lastInsertId();

            return array(
                'success' => true,
                "message" => "Registro creado con éxito",
                "id_vehiculo" => $id_vehiculo,
                "vehiculo" => array(
                    "id_vehiculo" => $id_vehiculo,
                    "descripcion" => $data["descripcion"],
                    "placa" => $data["placa"],
                    "num_piso" => $data["num_piso"]
                )
            );
        } catch (PDOException $e) {
            switch ($e->getCode()) {
                case '23000':
                    return array(
                        'success' => false,
                        "message" => "Ya existe un registro con estos datos"
                    );

                default:
                    return array(
                        'success' => false,
                        "message" => $e->getMessage()
                    );
            }
        }
    }

    public function edit_register($data)
    {
        try {
            if ($_FILES["tarjeta_propiedad"]["error"] == 0) {
                $reply = $this->upload->upload_basic($_FILES["tarjeta_propiedad"], "private/vehiculos/" . $data["placa"] . "/", "tarjeta_propiedad_" . $data["terminal"] . ".pdf");
                if ($reply["success"]) {
                    $tarjeta_propiedad = $reply["message"];
                } else {
                    return array('success' => false, "message" => "Ha ocurrido un error al subir el archivo");
                    exit;
                }
            } else {
                $tarjeta_propiedad = $data["tarjeta_propiedad_before"];
            }

            $query = $this->db->connect()->prepare("UPDATE vehiculo SET 
            descripcion=:descripcion,
            placa=:placa,
            marca=:marca,
            modelo=:modelo,
            soat=:soat,
            file_tarjeta_propiedad=:file_tarjeta_propiedad,
            serie_motor=:serie_motor,
            num_ejes=:num_ejes,
            num_piso=:num_piso,
            num_asiento=:num_asiento,
            tuc=:tuc,
            num_poliza=:num_poliza,
            estado=:estado,
            n_mtc=:n_mtc
            WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(':id_vehiculo', $data["id_vehiculo"]);
            $query->bindParam(':descripcion', $data["descripcion"]);
            $query->bindParam(':placa', $data["placa"]);
            $query->bindParam(':marca', $data["marca"]);
            $query->bindParam(':modelo', $data["modelo"]);
            $query->bindParam(':soat', $data["soat"]);
            $query->bindParam(':file_tarjeta_propiedad', $tarjeta_propiedad);
            $query->bindParam(':serie_motor', $data["serie_motor"]);
            $query->bindParam(':num_ejes', $data["num_ejes"]);
            $query->bindParam(':num_piso', $data["num_piso"]);
            $query->bindParam(':num_asiento', $data["num_asiento"]);
            $query->bindParam(':tuc', $data["tuc"]);
            $query->bindParam(':num_poliza', $data["num_poliza"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->bindParam(':n_mtc', $data["n_mtc"]);
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
            $query = $this->db->connect()->prepare("DELETE FROM vehiculo WHERE id_vehiculo=:id_vehiculo");
            $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
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

    public function config_vehiculo($data)
    {
        try {
            $pdo = $this->db->connect();
            $pdo->beginTransaction();

            // return $data;
            $vehiculo_objs = json_decode($data["vehiculo_objs"], true) ?? [];

            $query = $pdo->prepare("UPDATE vehiculo SET height = :height, height_piso2 = :height2 WHERE id_vehiculo = :id_vehiculo");
            $query->execute([
                ":id_vehiculo" => $data["id_vehiculo"],
                ":height" => $data["vehiculo_height_piso1"],
                ":height2" => $data["vehiculo_height_piso2"]
            ]);

            $query = $pdo->prepare("SELECT id_obj_vehiculo FROM obj_vehiculo WHERE id_vehiculo = :id_vehiculo");
            $query->execute([":id_vehiculo" => $data["id_vehiculo"]]);
            $all_obj_db = $query->fetchAll(PDO::FETCH_COLUMN, 0);

            $existing_ids = array_column($vehiculo_objs, 'id_obj_vehiculo');

            // Insertar o actualizar objetos
            foreach ($vehiculo_objs as $obj) {
                if (!empty($obj["id_obj_vehiculo"]) && in_array($obj["id_obj_vehiculo"], $all_obj_db)) {
                    $query = $pdo->prepare("UPDATE obj_vehiculo SET
                        piso = :piso, left_obj = :left_obj, top_obj = :top_obj, rotate_obj = :rotate_obj,
                        icon_obj = :icon_obj, text_obj = :text_obj, tp_obj = :tp_obj, tp_asiento = :tp_asiento, 
                        id_sesionpersonal = :id_sesionpersonal
                        WHERE id_obj_vehiculo = :id_obj_vehiculo AND id_vehiculo = :id_vehiculo");

                    $query->execute([
                        ":id_obj_vehiculo" => $obj["id_obj_vehiculo"],
                        ":id_vehiculo" => $data["id_vehiculo"],
                        ":piso" => $obj["piso"],
                        ":left_obj" => $obj["left_obj"],
                        ":top_obj" => $obj["top_obj"],
                        ":rotate_obj" => $obj["rotate_obj"],
                        ":icon_obj" => $obj["icon_obj"],
                        ":text_obj" => $obj["text_obj"],
                        ":tp_obj" => $obj["tp_obj"],
                        ":tp_asiento" => $obj["tp_asiento"],
                        ":id_sesionpersonal" => $this->id_usuario_sesion
                    ]);
                } else {
                    $query = $pdo->prepare("INSERT INTO obj_vehiculo (
                        id_vehiculo, piso, left_obj, top_obj, rotate_obj, icon_obj, text_obj, tp_obj, tp_asiento, id_sesionpersonal
                    ) VALUES (
                        :id_vehiculo, :piso, :left_obj, :top_obj, :rotate_obj, :icon_obj, :text_obj, :tp_obj, :tp_asiento, :id_sesionpersonal
                    )");

                    $query->execute([
                        ":id_vehiculo" => $data["id_vehiculo"],
                        ":piso" => $obj["piso"],
                        ":left_obj" => $obj["left_obj"],
                        ":top_obj" => $obj["top_obj"],
                        ":rotate_obj" => $obj["rotate_obj"],
                        ":icon_obj" => $obj["icon_obj"],
                        ":text_obj" => $obj["text_obj"],
                        ":tp_obj" => $obj["tp_obj"],
                        ":tp_asiento" => $obj["tp_asiento"],
                        ":id_sesionpersonal" => $this->id_usuario_sesion
                    ]);
                }
            }

            $ids_to_delete = array_diff($all_obj_db, $existing_ids);
            if (!empty($ids_to_delete)) {
                $placeholders = implode(",", array_fill(0, count($ids_to_delete), "?"));
                $query = $pdo->prepare("DELETE FROM obj_vehiculo WHERE id_obj_vehiculo IN ($placeholders)");
                $query->execute(array_values($ids_to_delete));
            }

            $pdo->commit();

            return ["success" => true, "message" => "Registro actualizado con éxito"];
        } catch (Exception $e) {
            $pdo->rollBack();
            if ($e->getCode() == "23000") {
                return [
                    "success" => false,
                    "message" => "No se puede eliminar o modificar este objeto porque tiene registros asociados."
                ];
            } else {
                return ["success" => false, "message" => "Error: " . $e->getMessage()];
            }
        }
    }

    public function get_obj_vehiculo($data)
    {
        $query = $this->db->connect()->prepare("SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo");
        $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($reply) {
            return array("success" => true, "message" => $reply);
        } else {
            return array("success" => false, "message" => null);
        }
    }

    public function get_n_asientos($data)
    {
        if (empty($data['id_vehiculo'])) {
            return ["success" => false, "message" => "Falta el id_vehiculo"];
        }
        if (empty($data['id_programacion'])) {
            return ["success" => false, "message" => "Falta el id_programacion"];
        }

        $conn = $this->db->connect();

        // 1) Capacidad total del vehículo actual (informativo)
        $query = $conn->prepare("
        SELECT COUNT(*) AS cantidad_asientos
        FROM obj_vehiculo
        WHERE id_vehiculo = :id_vehiculo
        AND tp_obj = 'asiento'
        ");
        $query->bindParam(":id_vehiculo", $data["id_vehiculo"]);
        $query->execute();
        $asientos = $query->fetch(PDO::FETCH_ASSOC);
        $capacidad_actual = (int) ($asientos['cantidad_asientos'] ?? 0);

        if ($capacidad_actual === 0) {
            return ["success" => false, "message" => "El vehículo actual no tiene asientos registrados o no existe"];
        }

        // 2) Cuántos asientos están REALMENTE vendidos en esta programación
        //    (esto es lo que de verdad limita a qué vehículos podemos cambiarnos)
        $asientos_vendidos = $this->obtener_asientos_vendidos($data, $conn);
        $cantidad_vendidos = count($asientos_vendidos);

        // 3) Buscar vehículos donde quepan los vendidos, sin importar si tienen
        //    más o menos capacidad total que el vehículo actual
        $query_vehiculos = $conn->prepare("
        SELECT v.id_vehiculo, v.placa, COUNT(*) AS cantidad_asientos
        FROM obj_vehiculo AS o_v
        INNER JOIN vehiculo AS v ON o_v.id_vehiculo = v.id_vehiculo
        WHERE o_v.tp_obj = 'asiento'
        AND o_v.id_vehiculo != :id_vehiculo
        GROUP BY v.id_vehiculo, v.placa
        HAVING COUNT(*) >= :cantidad_vendidos
        ORDER BY cantidad_asientos ASC
        ");
        $query_vehiculos->bindParam(":id_vehiculo", $data["id_vehiculo"]);
        $query_vehiculos->bindValue(":cantidad_vendidos", $cantidad_vendidos, PDO::PARAM_INT);
        $query_vehiculos->execute();
        $vehiculos = $query_vehiculos->fetchAll(PDO::FETCH_ASSOC);

        if (!$vehiculos) {
            return [
                "success" => false,
                "message" => "No se encontró ningún vehículo con capacidad para los {$cantidad_vendidos} asientos vendidos"
            ];
        }

        // Marcamos cuáles vehículos son más chicos que el actual, para que el
        // frontend pueda avisar "este tiene menos capacidad total" si quiere
        foreach ($vehiculos as &$v) {
            $v['menor_capacidad_que_actual'] = ((int) $v['cantidad_asientos']) < $capacidad_actual;
        }
        unset($v);

        return [
            "success" => true,
            "capacidad_actual" => $capacidad_actual,
            "asientos_vendidos" => $cantidad_vendidos,
            "vehiculos" => $vehiculos
        ];
    }

    public function obtener_asientos_vendidos($data, $conn = null)
    {
        if ($conn === null) {
            $conn = $this->db->connect();
        }
        $query = $conn->prepare("
        SELECT po.id_obj_vehiculo AS id_asiento, ov.text_obj AS nombre_asiento
        FROM programacion_obj po
        JOIN obj_vehiculo ov ON po.id_obj_vehiculo = ov.id_obj_vehiculo
        WHERE po.id_programacion = :id_programacion AND po.estado = 'VENDIDO'
       ");
        $query->bindParam(':id_programacion', $data["id_programacion"]);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as &$resultado) {
            $resultado['nombre_asiento'] = trim($resultado['nombre_asiento']);
        }

        return $resultados;
    }
}
