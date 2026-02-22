<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Domain\Logs\Logger;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

try {
    require_once "constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    return [
        "paths" => [
            "migrations" => "%%PHINX_CONFIG_DIR%%/db/migrations",
            "seeds" => "%%PHINX_CONFIG_DIR%%/db/seeds"
        ],
        "environments" => [
            "default_migration_table" => "phinxlog",
            "default_environment" => "development",
            "production" => [
                "adapter" => DotenvEnvironment::get("REPOSITORY_ADAPTER"),
                "host" => DotenvEnvironment::get("REPOSITORY_HOST"),
                "name" => DotenvEnvironment::get("REPOSITORY_DATABASE"),
                "user" => DotenvEnvironment::get("REPOSITORY_USERNAME"),
                "pass" => DotenvEnvironment::get("REPOSITORY_PASSWORD"),
                "port" => DotenvEnvironment::get("REPOSITORY_PORT"),
                "charset" => DotenvEnvironment::get("REPOSITORY_CHARSET"),
            ],
            "development" => [
                "adapter" => DotenvEnvironment::get("REPOSITORY_ADAPTER"),
                "host" => DotenvEnvironment::get("REPOSITORY_HOST"),
                "name" => DotenvEnvironment::get("REPOSITORY_DATABASE"),
                "user" => DotenvEnvironment::get("REPOSITORY_USERNAME"),
                "pass" => DotenvEnvironment::get("REPOSITORY_PASSWORD"),
                "port" => DotenvEnvironment::get("REPOSITORY_PORT"),
                "charset" => DotenvEnvironment::get("REPOSITORY_CHARSET"),
            ],
            "testing" => [
                "adapter" => DotenvEnvironment::get("REPOSITORY_ADAPTER"),
                "host" => DotenvEnvironment::get("REPOSITORY_HOST"),
                "name" => DotenvEnvironment::get("REPOSITORY_DATABASE"),
                "user" => DotenvEnvironment::get("REPOSITORY_USERNAME"),
                "pass" => DotenvEnvironment::get("REPOSITORY_PASSWORD"),
                "port" => DotenvEnvironment::get("REPOSITORY_PORT"),
                "charset" => DotenvEnvironment::get("REPOSITORY_CHARSET"),
            ]
        ],
        "version_order" => "creation"
    ];
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
