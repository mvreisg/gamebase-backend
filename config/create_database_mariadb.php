<?php

require_once dirname(__DIR__) . "/constants.php";
require_once PROJECT_ROOT . "/bootstrap.php";
require_once PROJECT_ROOT . "/vendor/autoload.php";

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Connections\MariaDBRepositoryConnection;

try {
    DotenvEnvironment::load();
    $databaseName = DotenvEnvironment::get("REPOSITORY_DATABASE");
    $connection = MariaDBRepositoryConnection::getRaw();
    $connection->exec("CREATE DATABASE IF NOT EXISTS $databaseName;");
} catch (\Throwable $e) {
    print_r($e->getMessage());
}
