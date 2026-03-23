<?php
$baseUrl = "http://{$_SERVER["HTTP_HOST"]}:{$_SERVER["SERVER_PORT"]}";
$items = [
    "Home" => "/pages/index.php",
    "PDO Database" => "/pages/database/pdo/pdo_database.php",
    "Get PHP Defuse Encryption Key" => "/pages/encryption/php_defuse/php_defuse_encryption_key.php",
    "Get Sodium Encryption Key" => "/pages/encryption/sodium/sodium_encryption_key.php"
];
?>
<div>
<?php
foreach ($items as $title => $item) {
    echo "<a class=\"m-1\" href=\"$baseUrl$item\">$title</a>";
}
?>
</div>