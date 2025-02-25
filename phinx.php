<?php

include_once __DIR__ . '/vendor/autoload.php';

try {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
} catch (Throwable $e) {
    print_r('Erro ao carregar o .env em phinx.php');
}

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => '',
            'host' => '',
            'name' => '',
            'user' => '',
            'pass' => '',
            'port' => '',
            'charset' => '',
        ],
        'development' => [
            'adapter' => $_SERVER['DATABASE_ADAPTER'],
            'host' => $_SERVER['DATABASE_HOST'],
            'name' => $_SERVER['DATABASE_NAME'],
            'user' => $_SERVER['DATABASE_USERNAME'],
            'pass' => $_SERVER['DATABASE_PASSWORD'],
            'port' => $_SERVER['DATABASE_PORT'],
            'charset' => $_SERVER['DATABASE_CHARSET'],
        ],
        'testing' => [
            'adapter' => '',
            'host' => '',
            'name' => '',
            'user' => '',
            'pass' => '',
            'port' => '',
            'charset' => '',
        ]
    ],
    'version_order' => 'creation'
];
