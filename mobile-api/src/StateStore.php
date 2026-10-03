<?php
declare(strict_types=1);
namespace TidApi;

// Estado privado de la API. Nunca crea tablas en la BD del sistema general.
final class StateStore
{
    private \PDO $pdo;
    public function __construct(string $path)
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) { throw new \RuntimeException('State directory unavailable'); }
        $this->pdo = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
        $this->pdo->exec('PRAGMA busy_timeout = 5000');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS sessions (token_hash TEXT PRIMARY KEY, tenant TEXT NOT NULL, user_id INTEGER NOT NULL, expires INTEGER NOT NULL)');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS limits (bucket TEXT PRIMARY KEY, count INTEGER NOT NULL, reset_at INTEGER NOT NULL)');
        $this->pdo->prepare('DELETE FROM sessions WHERE expires <= ?')->execute([time()]);
        $this->pdo->prepare('DELETE FROM limits WHERE reset_at <= ?')->execute([time()]);
        if (is_file($path)) { chmod($path, 0600); }
    }
    public function throttle(string $bucket, int $limit, int $window): void
    {
        $now = time();
        $stmt = $this->pdo->prepare('INSERT INTO limits(bucket, count, reset_at) VALUES (?, 1, ?) ON CONFLICT(bucket) DO UPDATE SET count = CASE WHEN reset_at <= ? THEN 1 ELSE count + 1 END, reset_at = CASE WHEN reset_at <= ? THEN excluded.reset_at ELSE reset_at END RETURNING count');
        $stmt->execute([hash('sha256', $bucket), $now + $window, $now, $now]);
        $count = $stmt->fetchColumn();
        $stmt->closeCursor();
        if ((int)$count > $limit) { throw new ApiException(429, 'RATE_LIMITED', 'Demasiados intentos. Espera antes de volver a intentarlo.'); }
    }
    public function issue(string $tenant, int $userId, int $ttl): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + $ttl;
        $this->pdo->prepare('INSERT INTO sessions VALUES (?, ?, ?, ?)')->execute([hash('sha256', $token), $tenant, $userId, $expires]);
        return ['access_token' => $token, 'token_type' => 'Bearer', 'expires_at' => gmdate('Y-m-d\TH:i:s\Z', $expires)];
    }
    public function user(string $token, string $tenant): int
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) { throw new ApiException(401, 'UNAUTHENTICATED', 'Inicia sesión nuevamente.'); }
        $stmt = $this->pdo->prepare('SELECT user_id FROM sessions WHERE token_hash = ? AND tenant = ? AND expires > ?');
        $stmt->execute([hash('sha256', $token), $tenant, time()]);
        $id = $stmt->fetchColumn();
        if ($id === false) { throw new ApiException(401, 'UNAUTHENTICATED', 'La sesión venció o no pertenece a esta empresa.'); }
        return (int)$id;
    }
    public function revoke(string $token): void { $this->pdo->prepare('DELETE FROM sessions WHERE token_hash = ?')->execute([hash('sha256', $token)]); }
}
