<?php

use function PHPSTORM_META\map;

date_default_timezone_set("America/Lima");

class HomeModel extends Model
{

    function __construct()
    {
        parent::__construct();
    }

    public function dataTableProgramacion()
    {
        $query = $this->db->connect()->prepare("
        SELECT
            t_o.nombre AS terminal_origen,
            t_d.nombre AS terminal_destino,
            vh.placa AS vehiculo_placa,
            p.fecha_salida AS fecha_salida,
            p.hora_salida AS hora_salida
        FROM programacion p
        LEFT JOIN terminal t_o ON t_o.id_terminal=p.id_terminal_origen
        LEFT JOIN terminal t_d ON t_d.id_terminal=p.id_terminal_destino 
        LEFT JOIN vehiculo vh ON vh.id_vehiculo=p.id_vehiculo 
        WHERE p.estado=1");
        $query->execute();
        $reply = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('data' => $reply));
    }

    public function get_info_home()
    {
        $query = $this->db->connect()->prepare("SELECT * FROM empresa");
        $query->execute();
        $empresa = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db->connect()->prepare("
        SELECT
            IF(SUM(v.op_exonerada),SUM(v.op_exonerada),0.00) AS amount_register
        FROM venta v
        INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio= dt_v.id_tp_servicio
        WHERE tp_s.descripcion ='PASAJE' AND v.id_terminal=:id_terminal AND v.estado NOT IN ('ANULADO', 'CANCELADO')");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $total_venta_pasaje = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

        $query = $this->db->connect()->prepare("
        SELECT
            IF(SUM(v.op_gravada),SUM(v.op_gravada),0.00) AS amount_register
        FROM venta v
        INNER JOIN dt_venta dt_v ON dt_v.id_venta=v.id_venta
        INNER JOIN tp_servicio tp_s ON tp_s.id_tp_servicio= dt_v.id_tp_servicio
        WHERE tp_s.descripcion ='ENCOMIENDA' AND v.id_terminal=:id_terminal AND v.estado NOT IN ('ANULADO', 'CANCELADO')");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $total_venta_encomienda = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

        $query = $this->db->connect()->prepare("
        SELECT
            COUNT(*) AS amount_register
        FROM programacion p
        WHERE p.estado=1 AND p.id_terminal_origen=:id_terminal");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $total_programaciones = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

        $query = $this->db->connect()->prepare("
        SELECT
            COUNT(*) AS amount_register
        FROM usuario u
        WHERE u.estado=1 AND u.id_terminal=:id_terminal AND u.id_tp_usuario NOT IN (6, 7)");
        $query->bindParam(":id_terminal", $this->id_terminal_sesion);
        $query->execute();
        $total_personal = $query->fetch(PDO::FETCH_ASSOC)["amount_register"];

        $data = array(
            "cantidad_registro" => [
                [
                    'nombre' => 'Venta Pasaje',
                    'amount' => "S/ " . $total_venta_pasaje,
                ],
                [
                    'nombre' => 'Venta Encomienda',
                    'amount' => "S/" . $total_venta_encomienda,
                ],
                [
                    'nombre' => 'Programaciones',
                    'amount' => $total_programaciones,
                ],
                [
                    'nombre' => 'Personal',
                    'amount' => $total_personal,
                ]
            ],
            "empresa" => $empresa
        );
        return $data;
    }

    public function buscar_programaciones($data)
    {
        try {
            $query = $this->db->connect()->prepare("
            SELECT p.* 
            FROM programacion p
            WHERE p.id_terminal_origen = :origen 
            AND p.id_terminal_destino = :destino 
            AND p.fecha_salida = :fechaIda
            AND p.estado = 1
            ");

            $query->bindParam(":origen", $data['origen']);
            $query->bindParam(":destino", $data['destino']);
            $query->bindParam(":fechaIda", $data['fecha_ida']);

            $query->execute();
            $reply = $query->fetchAll(PDO::FETCH_ASSOC);

            return [
                "existe" => !empty($reply),
                "data" => $reply ?: []
            ];
        } catch (PDOException $e) {
            return [
                "existe" => false,
                "error" => "Error en la base de datos: " . $e->getMessage()
            ];
        }
    }   
}
