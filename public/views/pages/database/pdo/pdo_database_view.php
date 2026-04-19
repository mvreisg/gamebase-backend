<?php

declare(strict_types=1);

try {
    require_once dirname(__DIR__, 5) . "/constants.php";
    require_once PROJECT_ROOT . "/public/controllers/database/pdo/pdo_database_controller.php";
} catch (\Throwable $e) {
    print_r($e);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
require_once PROJECT_ROOT . "/public/views/components/head.php";
?>
</head>
<body>
    <h1 class="m-1">PDO Database</h1>
<?php
require_once PROJECT_ROOT . "/public/views/components/nav.php";
?>
<div class="m-1">
    <span class="fw-semibold"><?php echo $database?></span> status:
<?php
if ($exists) {
    echo "<span class=\"fw-semibold\" style=\"color: lime\">exists.</span>";
    echo "<a style=\"color: red\" class=\"fw-semibold mt-1 ms-1\" href=\"{$baseUrl}{$items["PDO Database"]}?action=drop\">Drop</a>";
} else {
    echo "<span class=\"fw-semibold\" style=\"color: red\">unexistant.</span>";
    echo "<a style=\"color: lime\" class=\"fw-semibold mt-1 ms-1\" href=\"{$baseUrl}{$items["PDO Database"]}?action=create\">Create</a>";
}
?>
</div>
<?php
require_once PROJECT_ROOT . "/public/views/components/js.php";
?>
</body>
</html>
