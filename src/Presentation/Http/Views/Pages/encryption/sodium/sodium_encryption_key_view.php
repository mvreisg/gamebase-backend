<?php

declare(strict_types=1);

try {
    require_once dirname(__DIR__, 5) . "/constants.php";
    require_once PROJECT_ROOT . "/public/views/components/Head.php";
    require_once PROJECT_ROOT . "/public/views/components/Nav.php";
    require_once PROJECT_ROOT . "/public/views/components/JavaScript.php";
    require_once PROJECT_ROOT . "/public/controllers/encryption/sodium/sodium_encryption_key_controller.php";
} catch (\Throwable $e) {
    print_r($e);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
echo Head::get("Gamebase-Backend");
?>
<body>
    <h1 class="m-1">Sodium Encryption Key</h1>
<?php
echo Nav::get(Nav::$ITEMS);
?>
<div class="m-1">
    <span class="fw-semibold">Key:</span>
<?php
    echo "<pre id=\"key-text\" style=\"background-color: gray;\" class=\"d-inline-flex\">$key</pre>";
?>
</div>
<button id="key-button">Copy to clipboard</button>
<?php
echo JavaScript::get();
?>
</body>
</html>
