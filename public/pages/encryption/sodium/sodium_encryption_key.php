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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
require_once PROJECT_ROOT . "/public/components/head.php";
?>
</head>
<body>
    <h1 class="m-1">Sodium Encryption Key</h1>
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
