<?php
declare(strict_types=1);
ini_set('display_errors', '0');
require dirname(__DIR__) . '/src/bootstrap.php';
try {
    $config = \TidApi\Config::load(dirname(__DIR__) . '/config/local.php');
    $request = \TidApi\Request::fromGlobals();
    (new \TidApi\Application($config))->handle($request)->send();
} catch (\TidApi\ApiException $e) {
    (new \TidApi\Response($e->status, ['success' => false, 'code' => $e->errorCode, 'message' => $e->getMessage()]))->send();
} catch (\Throwable $e) {
    error_log('TID API bootstrap exception=' . get_class($e) . ' code=' . $e->getCode());
    (new \TidApi\Response(503, ['success' => false, 'code' => 'SERVICE_UNAVAILABLE', 'message' => 'La API no está disponible. Revisa las extensiones PHP y la carpeta de estado.']))->send();
}
