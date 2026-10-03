<?php
declare(strict_types=1);
// Exclusivamente para tests HTTP, nunca incluir en public/ ni usar en producción.
require dirname(__DIR__) . '/src/bootstrap.php';
require __DIR__ . '/Fixtures.php';
$state = getenv('TID_API_TEST_STATE');
if (!$state) { http_response_code(500); exit; }
$tenant = ['name' => 'TID', 'db' => ['host' => 'localhost', 'name' => 'tid'], 'scope' => 'terminal', 'legacy_key' => 'test-key', 'legacy_iv' => 'test-iv'];
$config = ['environment' => 'local', 'timezone' => 'America/Lima', 'allowed_origins' => ['http://localhost:5173'], 'state_path' => $state, 'login_limit' => 100, 'tenants' => ['tid.net.pe' => $tenant, 'jrcargo.com.pe' => array_replace($tenant, ['name' => 'JR', 'db' => ['host' => 'localhost', 'name' => 'jr']])]];
$_SERVER['SCRIPT_NAME'] = '/index.php';
try {
    $request = TidApi\Request::fromGlobals();
    $app = new TidApi\Application($config, static function (array $tenant): PDO {
        $db = fixture($tenant['name']); $db->exec('PRAGMA query_only = ON'); return $db;
    });
    $app->handle($request)->send();
} catch (TidApi\ApiException $e) {
    (new TidApi\Response($e->status, ['success' => false, 'message' => $e->getMessage(), 'code' => $e->errorCode]))->send();
}
