<?php
date_default_timezone_set('America/Lima');

class Contra_MaestraModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getDataTable($data)
    {
        try {
            $selectFields = "
            con.*,
            CONCAT(u_g.nombres, ' ', u_g.apellidos) AS genero_usuario,
            CONCAT(u_u.nombres, ' ', u_u.apellidos) AS uso_usuario,
            CONCAT(v.serie, '-', v.correlativo) AS encomienda
            ";
            $baseQuery = "
            FROM encomienda_clave_maestra con
            LEFT JOIN usuario u_g ON u_g.id_usuario=con.id_usuario_genero
            LEFT JOIN usuario u_u ON u_u.id_usuario=con.id_usuario_uso
            LEFT JOIN dt_venta dt_v ON dt_v.id_encomienda=con.id_encomienda
            LEFT JOIN venta v ON v.id_venta=dt_v.id_venta";
            $searchColumns = [
                "u_g.nombres",
                "u_g.apellidos",
                "u_u.nombres",
                "u_u.apellidos",
                "con.estado",
                "v.serie",
                "v.correlativo",
                "con.observacion"
            ];

            $orderBy = "con.id_clave_maestra DESC";

            $result = $this->runDataTableQuery(
                $data,
                $baseQuery,
                $searchColumns,
                $selectFields,
                $orderBy
            );

            foreach ($result["data"] as &$row) {
                $row["clave"] = $this->security->decryption($row["clave"]);

                $row["fecha_generacion"] = !empty($row["fecha_generacion"])
                    ? formato_fecha_hora_12($row["fecha_generacion"])
                    : null;

                $row["fecha_uso"] = !empty($row["fecha_uso"])
                    ? formato_fecha_hora_12($row["fecha_uso"])
                    : null;

                $row["fecha_vencimiento"] = !empty($row["fecha_vencimiento"])
                    ? formato_fecha_hora_12($row["fecha_vencimiento"])
                    : null;
            }
            unset($row);

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

    public function generar_contrasena_maestra()
    {
        try {
            $conn = $this->db->connect();

            $estado = 'ACTIVA';
            $fecha_generacion = date('Y-m-d H:i:s');
            $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+7 days'));

            // Clave de 4 caracteres: números y letras
            $clave = $this->security->encryption($this->generarClaveUnica($conn));

            $query = $conn->prepare("
            INSERT INTO encomienda_clave_maestra 
            (clave, estado, id_usuario_genero, fecha_generacion, fecha_vencimiento) 
            VALUES 
            (:clave, :estado, :id_usuario_genero, :fecha_generacion, :fecha_vencimiento)
            ");

            $query->execute([
                ':clave' => $clave,
                ':estado' => $estado,
                ':id_usuario_genero' => $this->id_usuario_sesion,
                ':fecha_generacion' => $fecha_generacion,
                ':fecha_vencimiento' => $fecha_vencimiento
            ]);

            return [
                'success' => true,
                'message' => 'Contraseña creada con éxito',
                'clave' => $clave
            ];

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return [
                    'success' => false,
                    'message' => 'Ya existe un registro con estos datos'
                ];
            }

            return [
                'success' => false,
                'message' => 'Ha ocurrido un error, inténtalo más tarde.'
            ];
        }
    }

    private function generarClaveUnica(PDO $conn): string
    {
        $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $numeros = '23456789';

        do {
            $clave = '';

            for ($i = 0; $i < 3; $i++) {
                $clave .= $letras[random_int(0, strlen($letras) - 1)];
            }

            for ($i = 0; $i < 3; $i++) {
                $clave .= $numeros[random_int(0, strlen($numeros) - 1)];
            }

            $clave = str_shuffle($clave);

            $query = $conn->prepare("
            SELECT COUNT(*)
            FROM encomienda_clave_maestra
            WHERE clave = :clave
        ");

            $query->execute([
                ':clave' => $clave
            ]);

            $existe = (int) $query->fetchColumn();

        } while ($existe > 0);

        return $clave;
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
            $query = $this->db->connect()->prepare("UPDATE serie SET 
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
            $query = $this->db->connect()->prepare("UPDATE encomienda_clave_maestra SET estado = 'ANULADA' WHERE id_clave_maestra=:id_clave_maestra");
            $query->bindParam(":id_clave_maestra", $data["id_clave_maestra"]);
            $query->execute();
            return ['success' => true, "message" => "Registro anulado con éxito"];
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
}
