<?php

declare(strict_types=1);

use DI\Container;

try {
    require_once "constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php_di/phinx/container_bootstrap.php";

    return [
        "paths" => [
            "migrations" => "%%PHINX_CONFIG_DIR%%/configurations/phinx/migrations",
            "seeds" => "%%PHINX_CONFIG_DIR%%/configurations/phinx/seeds"
        ],
        "environments" => [
            "default_migration_table" => "phinxlog",
            "default_environment" => "development",
            "production" => [
                "adapter" => $container->get("repository.adapter"),
                "host" => $container->get("repository.host"),
                "name" => $container->get("repository.database"),
                "user" => $container->get("repository.username"),
                "pass" => $container->get("repository.password"),
                "port" => $container->get("repository.port"),
                "charset" => $container->get("repository.charset"),
            ],
            "development" => [
                "adapter" => $container->get("repository.adapter"),
                "host" => $container->get("repository.host"),
                "name" => $container->get("repository.database"),
                "user" => $container->get("repository.username"),
                "pass" => $container->get("repository.password"),
                "port" => $container->get("repository.port"),
                "charset" => $container->get("repository.charset"),
            ],
            "testing" => [
                "adapter" => $container->get("repository.adapter"),
                "host" => $container->get("repository.host"),
                "name" => $container->get("repository.database"),
                "user" => $container->get("repository.username"),
                "pass" => $container->get("repository.password"),
                "port" => $container->get("repository.port"),
                "charset" => $container->get("repository.charset"),
            ]
        ],
        "version_order" => "creation"
    ];
} catch (\Throwable $e) {
    throw $e;
}
