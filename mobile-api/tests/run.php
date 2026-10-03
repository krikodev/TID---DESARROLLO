<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
require __DIR__ . '/Fixtures.php';
use TidApi\{Application, Request, Config, Passwords};

$count = 0;
function check(bool $condition, string $name): void
{
    global $count;
    $count++;
    if (!$condition) { fwrite(STDERR, "FAIL $name\n"); exit(1); }
    echo "PASS $name\n";
}
$private = sys_get_temp_dir() . '/tid-api-test-' . bin2hex(random_bytes(6));
mkdir($private, 0700);
register_shutdown_function(static function () use ($private): void {
    foreach (glob($private . '/*') ?: [] as $file) { unlink($file); }
    rmdir($private);
});
$tenant = ['name' => 'TID', 'db' => ['host' => 'localhost', 'name' => 'tid'], 'legacy_key' => 'test-key', 'legacy_iv' => 'test-iv', 'scope' => 'terminal'];
$config = ['environment' => 'local', 'timezone' => 'America/Lima', 'allowed_origins' => ['http://localhost:5173'], 'state_path' => $private . '/state.sqlite', 'session_ttl' => 28800, 'login_limit' => 100, 'login_window' => 900, 'tenants' => ['tid.net.pe' => $tenant, 'jrcargo.com.pe' => array_replace($tenant, ['name' => 'JR', 'db' => ['host' => 'localhost', 'name' => 'jr']])]];
file_put_contents($private . '/config.php', '<?php return ' . var_export($config, true) . ';');
check(count(Config::load($private . '/config.php')['tenants']) === 2, 'Config válida, dos bases separadas');
$bad = $config; $bad['tenants']['jrcargo.com.pe']['db'] = $tenant['db'];
file_put_contents($private . '/bad.php', '<?php return ' . var_export($bad, true) . ';');
try { Config::load($private . '/bad.php'); check(false, 'Rechaza la BD compartida'); }
catch (TidApi\ApiException $e) { check($e->errorCode === 'TENANT_DATABASE_CONFLICT', 'Rechaza la BD compartida'); }
$dbs = ['tid' => fixture('TID'), 'jr' => fixture('JR')];
$snapshot = static fn(PDO $db) => serialize($db->query('SELECT * FROM usuario')->fetchAll()) . serialize($db->query('SELECT * FROM encomienda')->fetchAll());
$before = $snapshot($dbs['tid']);
foreach ($dbs as $db) { $db->exec('PRAGMA query_only = ON'); }
$app = new Application($config, static fn(array $tenant): PDO => $dbs[$tenant['db']['name']]);
function callApi(Application $app, string $path, string $method = 'GET', array $data = [], string $token = '', string $domain = 'tid.net.pe', array $extra = []): TidApi\Response
{
    return $app->handle(new Request($method, $path, ['X-Company-Domain' => $domain, 'Authorization' => 'Bearer ' . $token] + $extra, $data));
}
check(callApi($app, '/v1/health')->body['read_only'] === true, 'Health verifica esquema y empresa');
check(callApi($app, '/v1/health', domain: 'desconocido.pe')->status === 404, 'Empresa desconocida');
check(callApi($app, '/v1/health', domain: 'https://tid.net.pe')->status === 422, 'Dominio con protocolo inválido');
check(callApi($app, '/v1/health', method: 'OPTIONS', domain: '')->status === 204, 'OPTIONS funciona sin empresa ni token');
check(callApi($app, '/v1/health', extra: ['Origin' => 'http://localhost:5173'])->headers['Access-Control-Allow-Origin'] === 'http://localhost:5173', 'CORS origen permitido');
check(callApi($app, '/v1/health', extra: ['Origin' => 'https://evil.example'])->status === 403, 'CORS origen rechazado');
check(callApi($app, '/v1/auth/login', 'GET')->status === 405, 'Método incorrecto');
check(callApi($app, '/v1/auth/login', 'POST', ['email' => 'operador@example.com', 'password' => 'p4ss'])->status === 401, 'No recorta contraseña con espacios');
$login = callApi($app, '/v1/auth/login', 'POST', ['email' => 'operador@example.com', 'password' => ' p4ss ']);
check($login->status === 200 && $login->body['user']['nombres'] === 'TID', 'Login AES compatible con el sistema general');
check(!isset($login->body['user']['contrasena']), 'No expone contraseña');
$token = $login->body['access_token'];
$stateDb = new PDO('sqlite:' . $config['state_path']);
check($stateDb->query('SELECT token_hash FROM sessions')->fetchColumn() === hash('sha256', $token), 'Guarda solo hash del token');
$stateDb = null;
check(strlen($token) === 64, 'Token aleatorio de 256 bits');
check(callApi($app, '/v1/auth/login', 'POST', ['email' => 'cliente@example.com', 'password' => ' p4ss '])->status === 401, 'Cliente externo no accede');
check(callApi($app, '/v1/auth/login', 'POST', ['email' => 'inactivo@example.com', 'password' => ' p4ss '])->status === 401, 'Usuario inactivo no accede');
check(callApi($app, '/v1/auth/login', 'POST', ['email' => 'missing@example.com', 'password' => 'x'])->status === 401, 'Usuario desconocido');
check(callApi($app, '/v1/auth/me', token: $token)->status === 200, 'Sesión autenticada');
check(callApi($app, '/v1/auth/me', token: $token, domain: 'jrcargo.com.pe')->status === 401, 'Token TID no accede a JR');
$jrLogin = callApi($app, '/v1/auth/login', 'POST', ['email' => 'operador@example.com', 'password' => ' p4ss '], domain: 'jrcargo.com.pe');
check($jrLogin->body['user']['nombres'] === 'JR', 'Mismo correo accede a cuenta de su propia empresa');
check(callApi($app, '/v1/encomiendas/dni', data: ['dni' => '22222222'])->status === 401, 'Consulta requiere autenticación');
$pending = callApi($app, '/v1/encomiendas/dni', data: ['dni' => '22222222'], token: $token);
check(array_column($pending->body['data'], 'tracking') === ['BLOQUE', 'PENDIENTE'], 'DNI: pagadas, en destino, terminal del usuario');
check(count($pending->body['data']) === 2, 'Dos productos no duplican la encomienda');
check(callApi($app, '/v1/encomiendas/dni', data: ['dni' => "1' OR 1=1"], token: $token)->status === 422, 'DNI malicioso rechazado');
check(callApi($app, '/v1/encomiendas/dni', data: ['dni' => '99999999'], token: $token)->body['data'] === [], 'Sin resultados devuelve lista vacía');
$page = callApi($app, '/v1/encomiendas/dni', data: ['dni' => '22222222', 'limit' => '1'], token: $token);
check(count($page->body['data']) === 1 && $page->body['pagination']['has_more'], 'Paginación estable');
check(callApi($app, '/v1/encomiendas/dni', data: ['dni' => '22222222', 'limit' => '0'], token: $token)->status === 422, 'Paginación inválida rechazada');
$summary = callApi($app, '/v1/resumen', data: ['fecha' => '2026-10-03'], token: $token);
check($summary->body['entregados'] === 1 && $summary->body['pendientes'] === 2, 'Resumen real con cifras del terminal');
$history = callApi($app, '/v1/encomiendas/historial', data: ['fecha' => '2026-10-03'], token: $token);
check(array_column($history->body['data'], 'tracking') === ['ENTREGADO'], 'Historial por día y terminal');
check(callApi($app, '/v1/encomiendas/historial', data: ['fecha' => '2026-02-30'], token: $token)->status === 422, 'Fecha imposible rechazada');
check(callApi($app, '/v1/encomiendas/tracking', data: ['codigo_tracking' => "' OR 1=1 --"], token: $token)->body['data'] === [], 'Tracking SQL parametrizado');
check(callApi($app, '/v1/encomiendas/tracking', data: ['codigo_tracking' => 'OTRA-TERMINAL'], token: $token)->body['data'] === [], 'Tracking restringido por terminal');
$receipt = callApi($app, '/v1/encomiendas/comprobante', data: ['serie' => 'B001', 'correlativo' => '123', 'fecha' => '2026-10-03'], token: $token);
check(count($receipt->body['data']) === 2, 'Comprobante consulta sin duplicados de dt_venta');
check(count(callApi($app, '/v1/encomiendas/imagenes', data: ['codigo_tracking' => 'ENTREGADO'], token: $token)->body['imagenes']) === 2, 'Imágenes existentes desde evidencia');
check(callApi($app, '/v1/encomiendas/entregar', 'POST', token: $token)->status === 404, 'No existe endpoint de escritura');
check($snapshot($dbs['tid']) === $before, 'No se modificó ningún usuario ni encomienda');
check(Passwords::verify('hash-password', password_hash('hash-password', PASSWORD_DEFAULT), $tenant), 'Admite hashes modernos sin cambiar usuarios');
$dbs['tid']->exec('PRAGMA query_only = OFF');
$dbs['tid']->exec('UPDATE usuario SET estado = 0 WHERE id_usuario = 1');
$dbs['tid']->exec('PRAGMA query_only = ON');
check(callApi($app, '/v1/auth/me', token: $token)->status === 401, 'Revoca acceso cuando el usuario es desactivado');
$jrToken = $jrLogin->body['access_token'];
check(callApi($app, '/v1/auth/logout', 'POST', token: $jrToken, domain: 'jrcargo.com.pe')->status === 200, 'Logout correcto');
check(callApi($app, '/v1/auth/me', token: $jrToken, domain: 'jrcargo.com.pe')->status === 401, 'Logout revoca token en servidor');
$config['state_path'] = $private . '/limited.sqlite'; $config['login_limit'] = 2;
$limited = new Application($config, static fn(array $tenant): PDO => $dbs['jr']);
for ($i = 0; $i < 2; $i++) { callApi($limited, '/v1/auth/login', 'POST', ['email' => 'bad@example.com', 'password' => 'x']); }
check(callApi($limited, '/v1/auth/login', 'POST', ['email' => 'bad@example.com', 'password' => 'x'])->status === 429, 'Limita fuerza bruta');
$config['state_path'] = $private . '/missing-column.sqlite';
$withoutEvidence = fixture('TID', false); $withoutEvidence->exec('PRAGMA query_only = ON');
$noEvidence = new Application($config, static fn(): PDO => $withoutEvidence);
check(callApi($noEvidence, '/v1/health')->status === 200, 'Esquema sin fotos_evidencia no rompe la API');
$noLogin = callApi($noEvidence, '/v1/auth/login', 'POST', ['email' => 'operador@example.com', 'password' => ' p4ss ']);
check(callApi($noEvidence, '/v1/encomiendas/historial', data: ['fecha' => '2026-10-03'], token: $noLogin->body['access_token'])->body['data'][0]['fotos_evidencia'] === null, 'Evidencia opcional devuelve null');
$config['state_path'] = $private . '/prod.sqlite'; $config['environment'] = 'production';
$prod = new Application($config, static fn(): PDO => $withoutEvidence);
check(callApi($prod, '/v1/health')->status === 426, 'Producción requiere HTTPS');
$config['state_path'] = $private . '/expired.sqlite'; $config['environment'] = 'local'; $config['session_ttl'] = -1;
$expired = new Application($config, static fn(): PDO => $withoutEvidence);
$expiredToken = callApi($expired, '/v1/auth/login', 'POST', ['email' => 'operador@example.com', 'password' => ' p4ss '])->body['access_token'];
check(callApi($expired, '/v1/auth/me', token: $expiredToken)->status === 401, 'Token vencido rechazado');
// Parsing sintáctico de todos los PHP nuevos, sin ejecutar configuración privada.
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__))) as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') { token_get_all(file_get_contents($file->getPathname()), TOKEN_PARSE); }
}
echo "OK: $count verificaciones. SQL ejecutado sobre fixtures SQLite en solo lectura; MySQL real requiere bin/check.php.\n";
