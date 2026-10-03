<?php

class ApiQuery
{
    private static $token = 'CtsuKdVzTkFmqdw01CDRXkXY1TGQ7FdpYUdMovlDU1jWnIDfBJ';

    final public static function reniec($docu)
    {
        $dni = $docu;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.net/api/dni/" . $dni . "?api_token=" . self::$token,
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
    }

    final public static function sunat($ruc)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.net/api/ruc/" . $ruc . "?api_token=" . self::$token,
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
    }

    final public static function generate_pass()
    {
        $pattern = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#_-";
        $shfl = str_shuffle($pattern);
        $pass = substr($shfl, 0, 10);
        echo json_encode($pass);
    }
}
