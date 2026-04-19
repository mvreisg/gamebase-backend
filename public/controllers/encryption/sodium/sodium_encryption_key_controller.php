<?php

declare(strict_types=1);

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $key = sodium_crypto_secretbox_keygen();
    $key = sodium_bin2hex($key);
} catch (\Throwable $e) {
    print_r($e);
}
