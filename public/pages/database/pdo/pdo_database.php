<?php

declare(strict_types=1);

use DI\Container;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\DatabaseRepositoryInterface;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php_di/src/container_bootstrap.php";

    $database = $container->get("repository.database");
    $repository = $container->get(DatabaseRepositoryInterface::class);

    $exists = $repository->exists($database);
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
                        $repository->drop($database);
                    }
                    break;
                case "create":
                    if ($exists === false) {
                        $repository->create($database);
                    }
                    break;
                default:
                    break;
            }
            $exists = $repository->exists($database);
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
