<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . "/constants.php";
require_once PROJECT_ROOT . "/public/views/components/Head.php";
require_once PROJECT_ROOT . "/public/views/components/Nav.php";
require_once PROJECT_ROOT . "/public/views/components/JavaScript.php";
require_once PROJECT_ROOT . "/public/controllers/phinx/phinx_startup_controller.php";
?>
<!DOCTYPE html>
<html lang="en">
<?php
echo Head::get("Gamebase-Backend");
?>
<body>
    <h1 class="m-1">PDO Database</h1>
<?php
echo Nav::get(Nav::$ITEMS);
?>
<div class="m-1">  
    Status:   
<?php
echo $returnCode === 0
    ? "<span class=\"fw-semibold\" style=\"color: lime\">OK</span>"
    : "<span class=\"fw-semibold\" style=\"color: red\">Error!</span>";

echo "<pre>";
echo implode("\n", $output);
echo "\nExit code: $returnCode";
echo "</pre>";
?>
</div>
<?php
echo JavaScript::get();
?>
</body>
</html>
