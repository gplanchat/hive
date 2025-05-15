<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src/',
        __DIR__.'/tests/',
    ])
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP84Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit91Migration:risky' => true,
        '@PSR1' => true,
        '@PER-CS2.0' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'ternary_to_elvis_operator' => true,
        'set_type_to_cast' => true,
        'self_accessor' => true,
        'psr_autoloading' => true,
        'php_unit_test_annotation' => ['style' => 'annotation'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_construct' => true,
        'no_useless_sprintf' => true,
        'no_homoglyph_names' => true,
        'native_function_invocation' => true,
        'native_constant_invocation' => true,
        'modernize_types_casting' => true,
        'logical_operators' => true,
        'is_null' => true,
        'function_to_constant' => true,
        'fopen_flag_order' => true,
        'error_suppression' => true,
        'ereg_to_preg' => true,
        'dir_constant' => true,
        'ordered_attributes' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile('var/.php-cs-fixer.cache') // forward compatibility with 3.x line
;
