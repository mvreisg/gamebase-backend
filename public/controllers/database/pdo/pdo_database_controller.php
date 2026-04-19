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
