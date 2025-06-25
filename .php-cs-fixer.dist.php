<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);

return (new PhpCsFixer\Config())    
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true        
    ])
    ->setFinder($finder);