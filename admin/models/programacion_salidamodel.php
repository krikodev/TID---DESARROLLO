<?php
date_default_timezone_set('America/Lima');
class Programacion_salidaModel extends Model
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
                $searchColumns = ["p.estado"];
                $data['search']['value'] = $search;
            } else {
                $searchColumns = [
                    "u_ori.distri",
                    "u_desti.distri",
                    "c.nombres",
                    "c.apellidos",
                    "CONCAT(c.nombres, ' ', c.apellidos)",
                    "CONCAT(c.nombres, ' ', c.apellidos, ' ', c.num_docu)",
                    "v.placa",
                    "p.fecha_salida",
                    "p.hora_salida",
                    "p.direccion_origen",
                    "p."
                ];
                $data['search']['value'] = $search;
            }

            $selectFields = "
            p.id_salida,
            u_ori.distri AS origen,
            p.direccion_origen,
            p.ubigeo_origen,
            p.ubigeo_destino,
            p.direccion_destino,
            u_desti.distri AS destino,
            p.id_vehiculo,
            v.descripcion AS vehiculo_descripcion,
            v.num_piso AS vehiculo_piso,
            v.placa AS vehiculo_placa,
            p.id_conductor,
            CONCAT(c.nombres, ' ', c.apellidos) AS conductor_nombres,
            c.num_docu AS conductor_num_docu,
            p.fecha_salida,
            p.hora_salida,
            p.precio,
            p.observacion,
            p.estado";
            $baseQuery = "FROM programacion_salida p
            LEFT JOIN vehiculo v ON v.id_vehiculo=p.id_vehiculo
            LEFT JOIN ubigeo u_ori ON u_ori.cod_ubigeo =p.ubigeo_origen
            LEFT JOIN ubigeo u_desti ON u_desti.cod_ubigeo =p.ubigeo_destino
            LEFT JOIN usuario c ON c.id_usuario=p.id_conductor";
            $orderBy = "p.fecha_salida DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            foreach ($result['data'] as &$registro) {
                $registro['tp_usuario'] = $this->tp_usuario_sesion ?? null;
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
            $conn->beginTransaction();

            $data["estado"] = $data['estado'] ?? 1;
            if (empty($data["conductor"])) {
                $data["conductor"] = "2";
            } else {
                $query = $conn->prepare("
                SELECT id_salida FROM programacion_salida 
                WHERE id_conductor = :id_conductor 
                AND fecha_salida = :fecha_salida 
                AND estado = 1 
                ");
                $query->bindParam(':id_conductor', $data["conductor"]);
                $query->bindParam(':fecha_salida', $data["fecha_salida"]);
                $query->execute();
                $conductor_programado = $query->fetchColumn();

                if ($conductor_programado) {
                    throw new Exception('El conductor seleccionado ya tiene una programacion para salida de hoy.');
                }
            }

            $query = $conn->prepare("
            SELECT id_salida FROM programacion_salida 
            WHERE id_vehiculo = :id_vehiculo 
            AND fecha_salida = :fecha_salida 
            AND estado = 1 
            ");
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->execute();
            $vehiculo_programado = $query->fetchColumn();

            if ($vehiculo_programado) {
                throw new Exception('El vehiculo seleccionado ya tiene una programacion para salida de hoy.');
            }

            $query = $conn->prepare("
            INSERT INTO programacion_salida (
               ubigeo_origen, direccion_origen, ubigeo_destino, direccion_destino, 
               id_vehiculo, id_conductor, fecha_salida, precio, hora_salida, estado, id_sesionpersonal, observacion
            ) VALUES (
                :ubigeo_origen, :direccion_origen, :ubigeo_destino, :direccion_destino, :id_vehiculo, :id_conductor, 
                :fecha_salida, :precio, :hora_salida, :estado, :id_sesionpersonal, :observacion
            )
            ");
            $query->bindParam(':ubigeo_origen', $data["ubigeo_origen"]);
            $query->bindParam(':direccion_origen', $data["direccion_origen"]);
            $query->bindParam(':ubigeo_destino', $data["ubigeo_destino"]);
            $query->bindParam(':direccion_destino', $data["direccion_destino"]);
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':id_conductor', $data["conductor"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->bindParam(':precio', $data["precio"]);
            $query->bindParam(':hora_salida', $data["hora_salida"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->bindParam(':id_sesionpersonal', $this->id_usuario_sesion);
            $query->bindParam(':observacion', $data['observacion']);
            $query->execute();

            $id_salida = $conn->lastInsertId();

            $conn->commit();
            return ['success' => true, "message" => "Registro creado con éxito", "id_salida" => $id_salida];

        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            switch ($e->getCode()) {
                case '23000':
                    return ['success' => false, "message" => "Ya existe un registro con estos datos", $e->getMessage()];
                default:
                    return ['success' => false, "message" => $e->getMessage()];
            }
        }
    }

    public function edit_register($data)
    {
        try {

            if (empty($data["id_salida"])) {
                throw new Exception("El id de salida es requerido.");
            }

            $conn = $this->db->connect();
            $conn->beginTransaction();

            if (empty($data["conductor"])) {
                $data["conductor"] = "2";
            } else {

                $query = $conn->prepare("
                SELECT id_salida 
                FROM programacion_salida 
                WHERE id_salida != :id_salida
                AND id_conductor = :id_conductor 
                AND fecha_salida = :fecha_salida 
                AND estado = 1
                ");

                $query->bindParam(':id_salida', $data["id_salida"]);
                $query->bindParam(':id_conductor', $data["conductor"]);
                $query->bindParam(':fecha_salida', $data["fecha_salida"]);
                $query->execute();

                $conductor_programado = $query->fetchColumn();

                if ($conductor_programado) {
                    throw new Exception('El conductor seleccionado ya tiene una programacion para salida de hoy.');
                }
            }

            $query = $conn->prepare("
            SELECT id_salida 
            FROM programacion_salida 
            WHERE id_salida != :id_salida
            AND id_vehiculo = :id_vehiculo 
            AND fecha_salida = :fecha_salida 
            AND estado = 1
            ");

            $query->bindParam(':id_salida', $data["id_salida"]);
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->execute();

            $vehiculo_programado = $query->fetchColumn();

            if ($vehiculo_programado) {
                throw new Exception('El vehiculo seleccionado ya tiene una programacion para salida de hoy.');
            }

            $query = $conn->prepare("
            UPDATE programacion_salida SET
                ubigeo_origen    = :ubigeo_origen,
                direccion_origen = :direccion_origen,
                ubigeo_destino   = :ubigeo_destino,
                direccion_destino= :direccion_destino,
                id_vehiculo      = :id_vehiculo,
                id_conductor     = :id_conductor,
                fecha_salida     = :fecha_salida,
                precio           = :precio,
                hora_salida      = :hora_salida,
                estado           = :estado,
                observacion      = :observacion
            WHERE id_salida = :id_salida
            ");

            $query->bindParam(':id_salida', $data["id_salida"]);
            $query->bindParam(':ubigeo_origen', $data["ubigeo_origen"]);
            $query->bindParam(':direccion_origen', $data["direccion_origen"]);
            $query->bindParam(':ubigeo_destino', $data["ubigeo_destino"]);
            $query->bindParam(':direccion_destino', $data["direccion_destino"]);
            $query->bindParam(':id_vehiculo', $data["vehiculo"]);
            $query->bindParam(':id_conductor', $data["conductor"]);
            $query->bindParam(':fecha_salida', $data["fecha_salida"]);
            $query->bindParam(':precio', $data["precio"]);
            $query->bindParam(':hora_salida', $data["hora_salida"]);
            $query->bindParam(':estado', $data["estado"]);
            $query->bindParam(':observacion', $data["observacion"]);

            $query->execute();

            $conn->commit();

            return [
                'success' => true,
                "message" => "Registro modificado con éxito"
            ];

        } catch (Exception $e) {

            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }

            switch ($e->getCode()) {
                case '23000':
                    return [
                        'success' => false,
                        "message" => "Ya existe un registro con estos datos"
                    ];

                default:
                    return [
                        'success' => false,
                        "message" => $e->getMessage()
                    ];
            }
        }
    }

    public function delete_register($data)
    {
        try {
            $query = $this->db->connect()->prepare("DELETE FROM programacion_salida WHERE id_salida=:id_salida");
            $query->bindParam(":id_salida", $data["id_salida"]);
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

    public function get_salidas($data)
    {
        try {
            $conn = $this->db->connect();

            $query = $conn->prepare("
                SELECT 
                p.*,
                u_o.distri AS origen,
                u_d.distri AS destino,
                v.placa,
                CONCAT(c.nombres,' ',c.apellidos) AS conductor
                FROM programacion_salida p
                LEFT JOIN vehiculo v ON v.id_vehiculo = p.id_vehiculo
                LEFT JOIN ubigeo u_o ON u_o.cod_ubigeo = p.ubigeo_origen
                LEFT JOIN ubigeo u_d ON u_d.cod_ubigeo = p.ubigeo_destino
                LEFT JOIN usuario c ON c.id_usuario = p.id_conductor
                WHERE  p.ubigeo_destino = :ubigeo_destino
                AND p.fecha_salida >= CURDATE()
                ORDER BY p.fecha_salida ASC, p.hora_salida ASC
            ");

            $query->bindParam(':ubigeo_destino', $data["ubigeo_destino"]);
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $data];

        } catch (PDOException $e) {
            return ['success' => false, "message" => "Error en el servidor: " . $e->getMessage()];
        }
    }
}
