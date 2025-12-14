<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        // TODO check if this option will break the code
        //'single_quote' => false,
    ])
    ->setFinder($finder);
