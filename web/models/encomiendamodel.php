<?php

date_default_timezone_set('America/Lima');

class EncomiendaModel extends Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function buscar_traking($data)
    {
        $codigo = $data["trackingCode"];
        $sql = "SELECT
                    CONCAT(u_remitente.nombres, ' ', u_remitente.apellidos) AS remitente,
                    u_remitente.num_docu AS documentoRem,
                    CONCAT(u_destinatario.nombres, ' ', u_destinatario.apellidos) AS destinatario,
                    u_destinatario.num_docu AS documentoDes,
                    tv.nombre AS terminal,
                    t_o.nombre AS origen,
                    t_d.nombre AS destino,
                    e.fecha_salida,
                    v.fecha_registro,
                    e.fecha_entrega,
                    e.estado,
                    pe.descripcion AS producto,
                    pe.obs AS observacion,
                    e.codigo AS traking
                FROM encomienda e
                INNER JOIN dt_venta dv ON e.id_encomienda = dv.id_encomienda
                INNER JOIN venta v ON dv.id_venta = v.id_venta
                INNER JOIN usuario u_remitente ON e.id_remitente = u_remitente.id_usuario
                INNER JOIN usuario u_destinatario ON e.id_destinatario = u_destinatario.id_usuario
                INNER JOIN terminal tv ON v.id_terminal = tv.id_terminal
                INNER JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
                INNER JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
                INNER JOIN producto_encomienda pe ON e.id_encomienda = pe.id_encomienda
                WHERE e.codigo = :codigo";
        $query = $this->db->connect()->prepare($sql);
        $query->bindParam(":codigo", $codigo);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
