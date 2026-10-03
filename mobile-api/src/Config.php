<?php
declare(strict_types=1);
namespace TidApi;

final class Config
{
    public static function load(string $path): array
    {
        if (!is_file($path)) { throw new ApiException(503, 'CONFIGURATION_ERROR', 'La API necesita configuración. Ejecuta php bin/init-local.php.'); }
        $config = require $path;
        if (!is_array($config) || empty($config['tenants']) || empty($config['state_path']) || !in_array($config['environment'] ?? '', ['local', 'production'], true)) {
            throw new ApiException(503, 'CONFIGURATION_ERROR', 'Configuración inválida de API.');
        }
        $connections = [];
        foreach ($config['tenants'] as $domain => $tenant) {
            if (self::domain((string)$domain) !== $domain || !in_array($tenant['scope'] ?? '', ['terminal', 'company'], true)) {
                throw new ApiException(503, 'CONFIGURATION_ERROR', 'Configuración inválida de empresa.');
            }
            $db = $tenant['db'] ?? [];
            $identity = ($db['host'] ?? '') . ':' . ($db['port'] ?? 3306) . '/' . ($db['name'] ?? '');
            if (empty($db['name']) || isset($connections[$identity])) {
                throw new ApiException(503, 'TENANT_DATABASE_CONFLICT', 'Cada empresa debe tener su propia base de datos.');
            }
            $connections[$identity] = true;
        }
        return $config;
    }
    public static function domain(string $value): string
    {
        $value = strtolower(trim($value));
        if (!preg_match('/^(?=.{1,253}$)[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)+$/D', $value)) {
            throw new ApiException(422, 'INVALID_COMPANY_DOMAIN', 'Ingresa solo el dominio de la empresa, sin https ni rutas.');
        }
        return $value;
    }
}
