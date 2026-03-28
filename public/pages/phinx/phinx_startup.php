<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . "/constants.php";
require_once PROJECT_ROOT . "/bootstrap.php";

$output = [];
$returnCode = 0;

$root = escapeshellarg(PROJECT_ROOT);

$command = "
    cd $root &&
    bash $root/startup.sh 2>&1
";

exec($command, $output, $returnCode);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
require_once PROJECT_ROOT . "/public/components/head.php";
?>
</head>
<body>
    <h1 class="m-1">PDO Database</h1>
<?php
require_once PROJECT_ROOT . "/public/components/nav.php";
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
require_once PROJECT_ROOT . "/public/components/js.php";
?>
</body>
</html>
