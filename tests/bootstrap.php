<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests;

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/vendor/autoload.php";

    DotenvEnvironment::load();
} catch (\Throwable $e) {
    print_r("Error! " . $e->getMessage());
}
