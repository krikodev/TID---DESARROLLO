<?php
require_once("../controller/conexion.php");

$conexion = new Conexion();
$conn = $conexion->getConnection();
$token = API_TOKEN;

switch ($_POST["tp_docu"]) {
    case '1':
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.net/api/dni/" . $_POST["num_docu"] . "?api_token=$token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $reply = json_decode($response);
            echo json_encode($reply);
        }
        break;
    case "6":
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.net/api/ruc/" . $_POST["num_docu"] . "?api_token=$token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $reply = json_decode($response);
            echo json_encode($reply);
        }
        break;
}
