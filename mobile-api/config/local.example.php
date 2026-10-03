<?php
declare(strict_types=1);
// Copiar como local.php. Este archivo es privado y está fuera de public/.
return [
    'environment' => 'local', // production exige HTTPS
    'timezone' => 'America/Lima',
    'allowed_origins' => ['http://localhost:5173'], // Flutter web: puerto fijo
    'session_ttl' => 28800,
    'login_limit' => 10,
    'login_window' => 900,
    'state_path' => dirname(__DIR__) . '/storage/api.sqlite',
    'tenants' => [
        'tid.net.pe' => [
            'name' => 'TID',
            'db' => ['host' => '127.0.0.1', 'port' => 3306, 'name' => 'transporte', 'user' => 'tid_api', 'password' => 'CONFIGURAR'],
            'legacy_key' => 'COPIAR_SECRET_KEY_DEL_SISTEMA',
            'legacy_iv' => 'COPIAR_SECRET_IV_DEL_SISTEMA',
            'scope' => 'terminal',
        ],
        'jrcargo.com.pe' => [
            'name' => 'JR Cargo',
            'db' => ['host' => '127.0.0.1', 'port' => 3306, 'name' => 'transporte_jrcargo', 'user' => 'jrcargo_api', 'password' => 'CONFIGURAR'],
            'legacy_key' => 'COPIAR_SECRET_KEY_DEL_OTRO_SISTEMA',
            'legacy_iv' => 'COPIAR_SECRET_IV_DEL_OTRO_SISTEMA',
            'scope' => 'terminal',
        ],
    ],
];
