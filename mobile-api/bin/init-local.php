<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$path = dirname(__DIR__) . '/config/local.php';
if (is_file($path)) { fwrite(STDERR, "local.php ya existe; edítalo sin sobrescribirlo.\n"); exit(1); }
foreach (['pdo_mysql', 'pdo_sqlite', 'openssl'] as $extension) {
    if (!extension_loaded($extension)) { fwrite(STDERR, "Falta extensión PHP: $extension\n"); exit(1); }
}
$securityPath = dirname(__DIR__, 2) . '/admin/libs/modules/Security.php';
if (!is_file($securityPath)) { fwrite(STDERR, "No se encontró Security.php del sistema general.\n"); exit(1); }
// Recupera la compatibilidad local sin publicar las claves en el nuevo código.
require $securityPath;
$config = [
    'environment' => 'local', 'timezone' => 'America/Lima',
    'allowed_origins' => ['http://localhost:5173'],
    'session_ttl' => 28800, 'login_limit' => 10, 'login_window' => 900,
    'state_path' => dirname(__DIR__) . '/storage/api.sqlite',
    'tenants' => [
        'tid.net.pe' => [
            'name' => 'TID',
            'db' => ['host' => '127.0.0.1', 'port' => (int)(getenv('TID_DB_PORT') ?: 3306), 'name' => getenv('TID_DB_NAME') ?: 'transporte', 'user' => getenv('TID_DB_USER') ?: 'tid_api', 'password' => getenv('TID_DB_PASSWORD') ?: ''],
            'legacy_key' => SECRET_KEY, 'legacy_iv' => SECRET_IV, 'scope' => 'terminal',
        ],
    ],
];
$content = "<?php\ndeclare(strict_types=1);\nreturn " . var_export($config, true) . ";\n";
$handle = fopen($path, 'x');
if ($handle === false || fwrite($handle, $content) !== strlen($content)) { fwrite(STDERR, "No se pudo crear configuración.\n"); exit(1); }
fclose($handle);
chmod($path, 0600);
echo "Configuración local creada para tid.net.pe. Ajusta BD y credenciales en config/local.php.\n";
