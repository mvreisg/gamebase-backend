<?php

use Mvreisg\GamebaseBackend\Domain\Logs\Logger;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";
    require_once PROJECT_ROOT . "/vendor/autoload.php";

    DotenvEnvironment::load();
    $databaseName = DotenvEnvironment::get("REPOSITORY_DATABASE");
    $connection = MariaDBRepositoryConnection::getRaw();
    $connection->exec("CREATE DATABASE IF NOT EXISTS $databaseName;");
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
