<?php

    $finder = \PhpCsFixer\Finder::create()
        ->in(__DIR__)
        ->exclude(['bootstrap', 'storage', 'vendor'])
        ->name('*.php')
        ->ignoreDotFiles(true)
        ->ignoreVCS(true);

    $config = new PhpCsFixer\Config();

    return $config
        ->setRules([
            '@PSR2' => true,
            'array_syntax' => ['syntax' => 'short'],
            'ordered_imports' => ['sort_algorithm' => 'length'],
            'no_unused_imports' => true,
        ])
        ->setFinder($finder);