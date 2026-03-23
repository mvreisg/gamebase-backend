<?php

declare(strict_types=1);

use DI\ContainerBuilder;

try {
    require_once dirname(__DIR__, 2) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $builder = new ContainerBuilder();
    $builder->addDefinitions(PROJECT_ROOT . "/configurations/php_di/definitions.php");
    $container = $builder->build();

    return $container;
} catch (\Throwable $e) {
    throw $e;
}
