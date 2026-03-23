<?php

declare(strict_types=1);

use Defuse\Crypto\Key;

try {
    require_once dirname(__DIR__, 4) . "/constants.php";
    require_once PROJECT_ROOT . "/bootstrap.php";

    $key = Key::createNewRandomKey();

    $key = $key->saveToAsciiSafeString();
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
    <h1 class="m-1">PHP Defuse Encryption Key</h1>
<?php
require_once PROJECT_ROOT . "/public/components/nav.php";
?>
<div class="m-1">
    <span class="fw-semibold">Key:</span>
<?php
    echo "<pre style=\"background-color: gray;\" class=\"d-inline-flex\">$key</pre>";
?>
</div>
<?php
require_once PROJECT_ROOT . "/public/components/js.php";
?>
</body>
</html>