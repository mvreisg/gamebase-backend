<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use GD75\DoubleQuoteFixer\DoubleQuoteFixer;
use Mvreisg\GamebaseBackend\Infrastructure\Logs\Logger;

try {
    $finder = Finder::create()->in(__DIR__);
    $config = new Config();

    return $config
        ->registerCustomFixers([
            new DoubleQuoteFixer()
        ])
        ->setRules([
            "@PSR12" => true,
            "no_unused_imports" => true,
            "GD75/double_quote_fixer" => true,
        ])
        ->setFinder($finder);
} catch (\Throwable $e) {
    Logger::logAppError($e);
}
