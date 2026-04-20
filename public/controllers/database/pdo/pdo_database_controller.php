<?php

declare(strict_types=1);

use DI\Container;
use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    /**
     * @var Container
     */
    $container = require PROJECT_ROOT . "/configurations/php_di/src/container_bootstrap.php";

    $database = $container->get("repository.database");
    $service = $container->get(DatabaseService::class);

    $exists = $service->exists($database);

    $action = $_GET["action"] ?? null;

    if ($action === null) {
        return;
    }

    switch ($action) {
        case "drop":
            if ($exists) {
                $service->drop($database);
            }
            break;
        case "create":
            if ($exists === false) {
                $service->create($database);
            }
            break;
        default:
            break;
    }

    $exists = $service->exists($database);

} catch (\Throwable $e) {
    print_r($e);
}
