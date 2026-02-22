<?php

use Mvreisg\GamebaseBackend\Domain\Logs\Logger;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;

try {
    require_once "constants.php";
    require_once PROJECT_ROOT . "/vendor/autoload.php";

    DotenvEnvironment::load();
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
