<?php

use Mvreisg\GamebaseBackend\Domain\Logs\Logger;

try {
    require_once dirname(__DIR__) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $key = sodium_crypto_secretbox_keygen();
    $key = sodium_bin2hex($key);

    print_r(PHP_EOL);
    print_r("copy the value inside the parenthesis to SODIUM_CRYPTO_SECRETBOX_KEY: ");
    print_r(PHP_EOL);
    print_r("(" . $key . ")");
    print_r(PHP_EOL);
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
