<?php

namespace Mvreisg\GamebaseBackend;

use Dotenv\Dotenv;
use Throwable;

try {
    include_once dirname(__DIR__) . '/vendor/autoload.php';

    Dotenv::createImmutable(dirname(__DIR__))->load();
} catch (Throwable $e) {
    print_r('Ocorreu um erro! Código ' . $e->getCode());
}
