<?php
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#'))
            continue;
        putenv($line);
    }
}
define('NAME_BUSINESS', 'TECNOLOGÍA, INFORMÁTICA Y DESARROLLO');

#database
define('DB_TYPE', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'transporte');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

#path
define('URL', 'http://localhost/tid-transporte/admin/');
define('URL_WEB', 'http://localhost/tid-transporte/web/');
define('URL_VENTA_WEB', 'http://localhost/tid-transporte/venta_web/');
define('LIBS', 'libs/');
define('LIBS_WEB', 'libs/');
define('URL_IMAGEN', 'http://localhost/tid-transporte/img/');
define('URL_IMAGEN_WEB', 'http://localhost/tid-transporte/img/web/');
define('URL_IMAGEN_ADMIN', 'http://localhost/tid-transporte/img/admin/');
define('URL_IMAGEN_VENTA_WEB', 'http://localhost/tid-transporte/img/venta_web/');

#variables
define('NAME_SESSION', "loggedInTransporte");
define('MINIFY_JS', TRUE);
define('MINIFY_CSS', TRUE);

#config
define("URL_PAGE_WEB", "http://localhost/tid-transporte");
define("URL_PAGE_WEB_COMPROBANTES", "http://localhost/tid-transporte");

define("API_URL", "https://demoapisunat.tid.com.pe/");
define("API_TOKEN", "CtsuKdVzTkFmqdw01CDRXkXY1TGQ7FdpYUdMovlDU1jWnIDfBJ");

#Parametros de culqui
define("SECRET_KEY_CULQUI", "sk_test_Nm3rW15dCs4EBWEP");
//Datos de la pasarela de pagos CULQUI
// define('PUBLIC_KEY', '{PUBLIC KEY}'); // Llave pública del comercio (pk_test_xxxxxxxxx)
// define('SECRET_KEY', '{SECRET KEY}'); //Llave secreta del comercio (sk_test_xxxxxxxxx)
// define('RSA_ID', '{RSA_ID}'); //Id de la llave RSA
// define('RSA_PUBLIC_KEY', '{RSA_PUBLIC_KEY}'); //Llave pública RSA que sirve para encriptar el payload de los servicios
// define('ACTIVE_ENCRYPT', true);