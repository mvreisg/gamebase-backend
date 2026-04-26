<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . "/constants.php";
require_once PROJECT_ROOT . "/bootstrap.php";

$output = [];
$returnCode = 0;

$root = escapeshellarg(PROJECT_ROOT);

$command = "bash $root/configurations/phinx/startup/phinx_startup.sh 2>&1";

exec($command, $output, $returnCode);
