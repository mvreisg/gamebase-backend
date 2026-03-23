<?php

declare(strict_types=1);

use DI\Container;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php_di/container_bootstrap.php";

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
    $result = $pdo->exec("CREATE DATABASE IF NOT EXISTS $database;");

} catch (\Throwable $e) {
    print_r($e);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
require_once PROJECT_ROOT . "/public/components/head.php";
?>
</head>
<body>
    <h1>PDO Create Database</h1>
<?php
require_once PROJECT_ROOT . "/public/components/nav.php";
?>
<div>
    <p>
        Creation status: 
<?php
if ($result) {
    print_r("created");
} else {
    print_r("error");
}
?>
    </p>
</div>
<?php
require_once PROJECT_ROOT . "/public/components/js.php";
?>
</body>
</html>
