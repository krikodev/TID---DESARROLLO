<?php
declare(strict_types=1);
foreach (['Http', 'Config', 'Connection', 'StateStore', 'Passwords', 'Repository', 'Application'] as $class) {
    require_once __DIR__ . '/' . $class . '.php';
}
