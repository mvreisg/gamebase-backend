<?php

declare(strict_types=1);

use Defuse\Crypto\Key;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $key = Key::createNewRandomKey()->saveToAsciiSafeString();
} catch (\Throwable $e) {
    print_r($e);
}
