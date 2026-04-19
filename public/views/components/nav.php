<?php
declare(strict_types=1);
$baseUrl = "http://{$_SERVER["HTTP_HOST"]}";
$items = [
    "Home" => "/views/pages/index.php",
    "PDO Database" => "/views/pages/database/pdo/pdo_database_view.php",
    "Phinx Startup" => "/views/pages/phinx/phinx_startup_view.php",
    "Get PHP Defuse Encryption Key" => "/views/pages/encryption/php_defuse/php_defuse_encryption_key_view.php",
    "Get Sodium Encryption Key" => "/views/pages/encryption/sodium/sodium_encryption_key_view.php"
];
?>
<div>
<?php
foreach ($items as $title => $item) {
    echo "<a class=\"m-1\" href=\"{$baseUrl}{$item}\">$title</a>";
}
?>
</div>