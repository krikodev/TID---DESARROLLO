<?php
require_once("../controller/conexion.php");

$conexion = new Conexion();
$conn = $conexion->getConnection();

$origen = isset($_POST["origen"]) ? $_POST["origen"] : null;
$destino = isset($_POST["destino"]) ? $_POST["destino"] : null;
$fechaIda = isset($_POST["fechaIda"]) ? $_POST["fechaIda"] : null;

$id_vehiculo = isset($_POST["id_vehiculo"]) ? $_POST["id_vehiculo"] : null;
$id_programacion = isset($_POST["id_programacion"]) ? $_POST["id_programacion"] : null;


switch ($_POST["accion"]) {
    case 'getProgramaciones':
        $query = "
        SELECT 

        p.*,
        t_d.nombre AS nombre_destino,
        t_o.nombre AS nombre_origen,
        t_p.descripcion AS descripcion,
        v_n.num_asiento AS num_asientos
        FROM programacion p
        INNER JOIN terminal t_d ON t_d.id_terminal = p.id_terminal_destino
        INNER JOIN terminal t_o ON t_o.id_terminal = p.id_terminal_origen
        INNER JOIN tp_servicio_pasaje t_p ON p.id_tp_servicio_pasaje = t_p.id_tp_servicio_pasaje 
        INNER JOIN vehiculo v_n ON p.id_vehiculo = v_n.id_vehiculo
        WHERE id_terminal_origen=:id_terminal_origen AND
        id_terminal_destino=:id_terminal_destino AND
        fecha_salida=:fecha_salida";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_terminal_origen", $origen);
        $stmt->bindParam(":id_terminal_destino", $destino);
        $stmt->bindParam(":fecha_salida", $fechaIda);
        $stmt->execute();
        $programacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($programacion) {
            echo json_encode(array("success" => true, "message" => $programacion));
        } else {
            echo json_encode(array("success" => false, "message" => null));
        }
        break;
    case 'getVehiculo':
        // echo json_encode(array("success" => false, "message" => $_POST));
        // exit;
        //    Consulta para obtener los datos del vehiculo
        $query = "SELECT * FROM vehiculo WHERE id_vehiculo=:id_vehiculo";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_vehiculo", $id_vehiculo);
        $stmt->execute();
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Consulta para obtener los objetos del vehiculo
        $query = "SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_vehiculo", $id_vehiculo);
        $stmt->execute();
        $objs_vehiculo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT * FROM programacion WHERE id_programacion=:id_programacion";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_programacion", $id_programacion);
        $stmt->execute();
        $programacion = $stmt->fetch(PDO::FETCH_ASSOC);


        $query = "SELECT * FROM programacion_obj WHERE id_programacion=:id_programacion";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_programacion", $id_programacion);
        $stmt->execute();
        $objs_programacion = $stmt->fetchAll(PDO::FETCH_ASSOC);




        for ($i = 0; $i < count($objs_vehiculo); $i++) {
            for ($x = 0; $x < count($objs_programacion); $x++) {
                if ($objs_vehiculo[$i]["id_obj_vehiculo"] == $objs_programacion[$x]["id_obj_vehiculo"] && in_array($objs_programacion[$x]["estado"], ['RESERVADO', 'RESERVADO_WEB', 'VENDIDO'])) {
                    $objs_vehiculo[$i]["estado_asiento"] = $objs_programacion[$x]["estado"];
                    $objs_vehiculo[$i]["id_venta"] = $objs_programacion[$x]["id_venta"];
                }
            }
            if ($objs_vehiculo[$i]["tp_asiento"] == 'premium') {
                $objs_vehiculo[$i]["precio"] = $programacion["precio_primer_piso"];
            } else {
                $objs_vehiculo[$i]["precio"] = $programacion["precio_segundo_piso"];
            }
        }

        if ($vehiculo) {
            echo json_encode(
                array(
                    "success" => true,
                    "message" => array(
                        'vehiculo' => $vehiculo,
                        'objs_vehiculo' => $objs_vehiculo,
                        'objs_programacion' => $objs_programacion
                    )
                )
            );
        } else {
            echo json_encode(array("success" => false, "message" => null));
        }
        break;

    default:
        break;
}



// $id_vehiculo = $_POST["id_vehiculo"];
// $id_programacion = $_POST["id_programacion"];

// if ($id_vehiculo && $id_programacion) {

//     // Consulta para obtener los datos del vehiculo
//     $query = "SELECT * FROM vehiculo WHERE id_vehiculo=:id_vehiculo";
//     $stmt = $conn->prepare($query);
//     $stmt->bindParam(":id_vehiculo", $id_vehiculo);
//     $stmt->execute();
//     $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

//     // Consulta para obtener los objetos del vehiculo
//     $query = "SELECT * FROM obj_vehiculo WHERE id_vehiculo=:id_vehiculo";
//     $stmt = $conn->prepare($query);
//     $stmt->bindParam(":id_vehiculo", $id_vehiculo);
//     $stmt->execute();
//     $objs_vehiculo = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $query = "SELECT * FROM programacion WHERE id_programacion=:id_programacion";
//     $stmt = $conn->prepare($query);
//     $stmt->bindParam(":id_programacion", $id_programacion);
//     $stmt->execute();
//     $programacion = $stmt->fetch(PDO::FETCH_ASSOC);


//     $query = "SELECT * FROM programacion_obj WHERE id_programacion=:id_programacion";
//     $stmt = $conn->prepare($query);
//     $stmt->bindParam(":id_programacion", $id_programacion);
//     $stmt->execute();
//     $objs_programacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $precio_asiento = array(
//         "premium" => $programacion["precio_primer_piso"],
//         "normal" => $programacion["precio_segundo_piso"]
//     );


//     for ($i = 0; $i < count($objs_vehiculo); $i++) {
//         for ($x = 0; $x < count($objs_programacion); $x++) {
//             if ($objs_vehiculo[$i]["id_obj_vehiculo"] == $objs_programacion[$x]["id_obj_vehiculo"] && in_array($objs_programacion[$x]["estado"], ['RESERVADO', 'VENDIDO'])) {
//                 $objs_vehiculo[$i]["estado_asiento"] = $objs_programacion[$x]["estado"];
//                 $objs_vehiculo[$i]["id_venta"] = $objs_programacion[$x]["id_venta"];
//             }
//         }
//         if ($objs_vehiculo[$i]["tp_asiento"] == 'premium') {
//             $objs_vehiculo[$i]["precio"] = $programacion["precio_primer_piso"];
//         } else {
//             $objs_vehiculo[$i]["precio"] = $programacion["precio_segundo_piso"];
//         }

//         // $tp_asiento = $objs_vehiculo[$i]["tp_asiento"];
//         // $objs_vehiculo[$i]["precio"] = $tp_asiento;
//     }

//     if ($vehiculo) {
//         echo json_encode(
//             array(
//                 "success" => true,
//                 "message" => array(
//                     'vehiculo' => $vehiculo,
//                     'objs_vehiculo' => $objs_vehiculo
//                 )
//             )
//         );
//     } else {
//         echo json_encode(array("success" => false, "message" => null));
//     }
// } else {
//     echo json_encode("Datos invalidos");
// }
