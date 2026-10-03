<?php
declare(strict_types=1);
namespace TidApi;

final class Passwords
{
    public static function verify(string $password, string $stored, array $tenant): bool
    {
        if ((password_get_info($stored)['algoName'] ?? 'unknown') !== 'unknown') { return password_verify($password, $stored); }
        if (empty($tenant['legacy_key']) || empty($tenant['legacy_iv'])) { throw new ApiException(503, 'CONFIGURATION_ERROR', 'Falta configurar la compatibilidad de contraseñas.'); }
        $decoded = base64_decode($stored, true);
        if ($decoded === false) { return false; }
        // Coincide exactamente con admin/libs/modules/Security.php; no altera usuarios.
        $plain = openssl_decrypt($decoded, 'AES-256-CBC', hash('sha256', $tenant['legacy_key']), 0, substr(hash('sha256', $tenant['legacy_iv']), 0, 16));
        return $plain !== false && hash_equals($plain, $password);
    }
}
