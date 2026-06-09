<?php

declare(strict_types=1);

use Manifesto\Core\EnvLoader;

/**
 * THE single application config. Values come from .env (XAMPP)
 * or real environment variables (Docker) — never hardcode here.
 */
return [
    'app' => [
        'name'  => 'Manifesto',
        'debug' => EnvLoader::get('APP_DEBUG', '1') === '1',
    ],
    'db' => [
        'host' => EnvLoader::get('DB_HOST', '127.0.0.1'),
        'port' => EnvLoader::get('DB_PORT', '3306'),
        'name' => EnvLoader::get('DB_NAME', 'manifesto'),
        'user' => EnvLoader::get('DB_USER', 'root'),
        'pass' => EnvLoader::get('DB_PASS', ''),
    ],
    'paths' => [
        'logs'      => dirname(__DIR__) . '/storage/logs',
        'generated' => dirname(__DIR__) . '/storage/generated',
    ],
];
