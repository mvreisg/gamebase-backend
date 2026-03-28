<?php

use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

try {
    require_once "constants.php";
    require_once PROJECT_ROOT . "/vendor/autoload.php";

    DotenvEnvironment::load();
} catch (\Throwable $e) {
    print_r($e);
}
