<?php
declare(strict_types=1);
namespace TidApi;

final class Connection
{
    public static function mysql(array $tenant): \PDO
    {
        $db = $tenant['db'];
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $db['name']) || !preg_match('/^[a-zA-Z0-9.:-]+$/D', $db['host'])) {
            throw new ApiException(503, 'CONFIGURATION_ERROR', 'Configuración inválida de conexión.');
        }
        $pdo = new \PDO('mysql:host=' . $db['host'] . ';port=' . (int)($db['port'] ?? 3306) . ';dbname=' . $db['name'] . ';charset=utf8mb4', $db['user'], $db['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->exec('SET SESSION TRANSACTION READ ONLY');
        return $pdo;
    }
}
