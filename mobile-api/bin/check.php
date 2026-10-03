<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/src/bootstrap.php';
try {
    $config = \TidApi\Config::load(dirname(__DIR__) . '/config/local.php');
    foreach ($config['tenants'] as $domain => $tenant) {
        (new \TidApi\Repository(\TidApi\Connection::mysql($tenant), $tenant))->health();
        echo $domain . ": conexión y esquema correctos (solo lectura).\n";
    }
} catch (\Throwable $e) {
    fwrite(STDERR, 'No se pudo validar la API. Tipo: ' . get_class($e) . '. Revisa conexión, permisos SELECT y columnas requeridas en README.' . "\n"); exit(1);
}
