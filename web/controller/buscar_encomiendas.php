<?php
require_once("../libs/conexion_web.php");

$serie = $_POST["serie"];
$correlativo = $_POST["correlativo"];
$fecha_emision = $_POST["fecha"];

$conexion = new Conexion();
$conn = $conexion->getConnection();

$sql = "SELECT
        CONCAT(u_remitente.nombres, ' ', u_remitente.apellidos) AS remitente,
        u_remitente.num_docu AS documentoRem,
        CONCAT(u_destinatario.nombres, ' ', u_destinatario.apellidos) AS destinatario,
        u_destinatario.num_docu AS documentoDes,
        tv.nombre AS terminal,
        t_o.nombre AS origen,
        t_d.nombre AS destino,
        v.fecha_registro,
        e.fecha_salida,
        e.fecha_entrega,
        e.estado,
        pe.descripcion AS producto,
        pe.obs AS observacion,
        e.codigo AS traking,
        e.fotos_evidencia 
FROM venta v
INNER JOIN dt_venta dv ON v.id_venta = dv.id_venta
INNER JOIN encomienda e ON dv.id_encomienda = e.id_encomienda
INNER JOIN usuario u_remitente ON e.id_remitente = u_remitente.id_usuario
INNER JOIN usuario u_destinatario ON e.id_destinatario = u_destinatario.id_usuario
INNER JOIN terminal tv ON v.id_terminal = tv.id_terminal
LEFT JOIN terminal t_o ON t_o.id_terminal = e.id_terminal_origen
LEFT JOIN terminal t_d ON t_d.id_terminal = e.id_terminal_destino
INNER JOIN producto_encomienda pe ON e.id_encomienda = pe.id_encomienda
WHERE v.serie = :serie AND v.correlativo = :correlativo AND DATE(v.fecha_emision) = :fecha";

$query=$conn->prepare($sql);
$query->bindParam(":serie",$serie);
$query->bindParam(":correlativo",$correlativo);
$query->bindParam(":fecha", $fecha_emision);
$query->execute();
$resultado = $query->fetchAll(PDO::FETCH_ASSOC);

foreach($resultado as $data){
    $btnFotos = "<span class='badge bg-secondary'>Sin fotos</span>";
    if (!empty($data['fotos_evidencia'])) {
        $fotosArray = explode(',', $data['fotos_evidencia']);
        $jsonFotos = htmlspecialchars(json_encode($fotosArray), ENT_QUOTES, 'UTF-8');
        $btnFotos = "<button type='button' class='btn btn-sm btn-info text-white' onclick='abrirCarruselFotos($jsonFotos)'>🖼️ Ver Evidencia</button>";
    }

    echo "<tr>
        <td>".$data['documentoRem']."</td> 
        <td>".$data['remitente']."</td>
        <td>".$data['documentoDes']."</td> 
        <td>".$data['destinatario']."</td> 
        <td>".$data['terminal']."</td>
        <td>".$data['origen']."</td>
        <td>".$data['destino']."</td>
        <td>".$data['fecha_salida']."</td>
        <td>".$data['fecha_registro']."</td>
        <td>".$data['fecha_entrega']."</td>
        <td><b>".$data['estado']."</b></td>
        <td>".$data['producto']."</td>
        <td>".$data['observacion']."</td>
        <td>".$data['traking']."</td>
        <td>".$btnFotos."</td> </tr>";
}
?>