<?php

declare(strict_types=1);

use DI\Container;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php_di/src/container_bootstrap.php";

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

    function exists($pdo, $database)
    {
        $stmt = $pdo->prepare("
            SELECT SCHEMA_NAME
            FROM INFORMATION_SCHEMA.SCHEMATA
            WHERE SCHEMA_NAME = :dbname
        ");

        $stmt->execute(["dbname" => $database]);

        return (bool) $stmt->fetch();
    }

    $exists = exists($pdo, $database);
    //$result = $pdo->exec("DROP DATABASE $database;");
    $rawQueries = $_SERVER["QUERY_STRING"];
    if ($rawQueries !== "") {
        $rawQueries = explode("&", $rawQueries);
        $queries = [];
        foreach ($rawQueries as $query) {
            $values = explode("=", $query);
            $queries[$values[0]] = $values[1];
        }
        if (isset($queries["action"])) {
            $action = $queries["action"];
            switch ($action) {
                case "drop":
                    if ($exists) {
                        $pdo->exec("DROP DATABASE `$database`");
                    }
                    break;
                case "create":
                    if ($exists === false) {
                        $pdo->exec("CREATE DATABASE `$database`");
                    }
                    break;
                default:
                    break;
            }
            $exists = exists($pdo, $database);
        }
    }
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
    <h1 class="m-1">PDO Database</h1>
<?php
require_once PROJECT_ROOT . "/public/components/nav.php";
?>
<div class="m-1">
    <span class="fw-semibold"><?php echo $database?></span> status:
<?php
if ($exists) {
    echo "<span class=\"fw-semibold\" style=\"color: lime\">exists.</span>";
    echo "<a style=\"color: red\" class=\"fw-semibold mt-1 ms-1\" href=\"{$baseUrl}{$items["PDO Database"]}?action=drop\">Drop</a>";
} else {
    echo "<span class=\"fw-semibold\" style=\"color: red\">unexistant.</span>";
    echo "<a style=\"color: lime\" class=\"fw-semibold mt-1 ms-1\" href=\"{$baseUrl}{$items["PDO Database"]}?action=create\">Create</a>";
}
?>
</div>
<?php
require_once PROJECT_ROOT . "/public/components/js.php";
?>
</body>
</html>
