<?php

require_once dirname(__DIR__) . '/constants.php';
require_once PROJECT_ROOT . '/bootstrap.php';

use Defuse\Crypto\Key;

$key = Key::createNewRandomKey();

$key = $key->saveToAsciiSafeString();

print_r(PHP_EOL);
print_r('copy the value inside the parenthesis to DEFUSE_PHP_ENCRYPTION_KEY: ');
print_r(PHP_EOL);
print_r('(' . $key . ')');
print_r(PHP_EOL);