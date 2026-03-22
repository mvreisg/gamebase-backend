<?php

declare(strict_types=1);

use DI\Container;
use Mvreisg\GamebaseBackend\Infrastructure\Logs\Logger;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php-di/container_bootstrap.php";

    $database = $container->get("repository.database");
    $adapter = $container->get("repository.adapter");
    $host = $container->get("repository.host");
    $username = $container->get("repository.username");
    $password = $container->get("repository.password");
    $dsn = "$adapter:host=$host;";
    $pdo = new \PDO(
        $dsn,
        $username,
        $password,
        [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database;");
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
