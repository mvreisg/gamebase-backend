<?php

declare(strict_types=1);

try {
    require_once "constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    return [
        "paths" => [
            "migrations" => "%%PHINX_CONFIG_DIR%%/configurations/phinx/migrations",
            "seeds" => "%%PHINX_CONFIG_DIR%%/configurations/phinx/seeds"
        ],
        "environments" => [
            "default_migration_table" => "phinxlog",
            "default_environment" => "development",
            "production" => [
                "adapter" => $_ENV["REPOSITORY_ADAPTER"],
                "host" => $_ENV["REPOSITORY_HOST"], // mariadb
                "name" => $_ENV["REPOSITORY_DATABASE"],
                "user" => $_ENV["REPOSITORY_USERNAME"],
                "pass" => $_ENV["REPOSITORY_PASSWORD"],
                "port" => $_ENV["REPOSITORY_PORT"],
                "charset" => $_ENV["REPOSITORY_CHARSET"],
            ],
            "development" => [
                "adapter" => $_ENV["REPOSITORY_ADAPTER"],
                "host" => $_ENV["REPOSITORY_HOST"], // mariadb
                "name" => $_ENV["REPOSITORY_DATABASE"],
                "user" => $_ENV["REPOSITORY_USERNAME"],
                "pass" => $_ENV["REPOSITORY_PASSWORD"],
                "port" => $_ENV["REPOSITORY_PORT"],
                "charset" => $_ENV["REPOSITORY_CHARSET"],
            ],
            "testing" => [
                "adapter" => $_ENV["REPOSITORY_ADAPTER"],
                "host" => $_ENV["REPOSITORY_HOST"], // mariadb
                "name" => $_ENV["REPOSITORY_DATABASE"],
                "user" => $_ENV["REPOSITORY_USERNAME"],
                "pass" => $_ENV["REPOSITORY_PASSWORD"],
                "port" => $_ENV["REPOSITORY_PORT"],
                "charset" => $_ENV["REPOSITORY_CHARSET"],
            ]
        ],
        "version_order" => "creation"
    ];
} catch (\Throwable $e) {
    throw $e;
}
