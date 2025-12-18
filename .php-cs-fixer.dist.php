<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use GD75\DoubleQuoteFixer\DoubleQuoteFixer;

$finder = (new Finder())
    ->in(__DIR__);

return (new Config())
    ->setCacheFile(__DIR__."/.php-cs-fixer.cache")
    ->registerCustomFixers([
        new DoubleQuoteFixer()
    ])
    ->setRules([
        "@PSR12" => true,
        "no_unused_imports" => true,
        "GD75/double_quote_fixer" => true,
    ])
    ->setFinder($finder);
