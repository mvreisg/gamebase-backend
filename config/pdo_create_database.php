<?php

use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\Options\PdoRepositoryConnectionOptions;
use Mvreisg\GamebaseBackend\Infrastructure\Connections\Pdo\PdoRepositoryConnection;
use Mvreisg\GamebaseBackend\Infrastructure\Logs\Logger;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";
    require_once PROJECT_ROOT . "/vendor/autoload.php";

    DotenvEnvironment::load();
    $databaseName = DotenvEnvironment::get("REPOSITORY_DATABASE");
    $connection = PdoRepositoryConnection::make(new PdoRepositoryConnectionOptions(false));
    $connection
        ->get()
        ->exec(
            "CREATE DATABASE IF NOT EXISTS $databaseName;"
        );
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
