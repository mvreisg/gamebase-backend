<?php
$base = "http://{$_SERVER["HTTP_HOST"]}:{$_SERVER["SERVER_PORT"]}/";
$items = [
    "Home" => "pages/index.php",
    "PDO Create Database" => "pages/database/pdo/pdo_create_database.php",
    "PDO Drop Database" => "pages/database/pdo/pdo_drop_database.php",
    "Get PHP Defuse Encryption Key" => "pages/encryption/php_defuse/php_defuse_encryption_key.php",
    "Get Sodium Encryption Key" => "pages/encryption/sodium/sodium_encryption_key.php"
];
?>
<div>
<?php
foreach ($items as $title => $item) {
    echo "<p><a href=\"$base$item\">$title</a>";
}
?>
</div>