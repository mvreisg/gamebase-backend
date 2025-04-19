<?php

use PhpCsFixer\ConfigInterface;

$finder = \PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['bootstrap', 'storage', 'vendor'])
    ->ignoreDotFiles(false)
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setLineEnding("\n")
    ->setRules([
        '@PSR12' => true,
        'no_mixed_echo_print' => [
            'use' => 'print'
        ],
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_whitespace_before_comma_in_array' => true,
        'return_to_yield_from' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true
        ],
        'braces_position' => [
            'allow_single_line_anonymous_functions' => false,
            'allow_single_line_empty_anonymous_classes' => false,
            'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'no_trailing_comma_in_singleline' =>  [
            'elements' => [
                'arguments',
                'array',
                'array_destructuring',
                'group_import'
            ]
        ],
        'single_line_empty_body' => true,
        'class_reference_name_casing' => true,
        'constant_case' => [
            'case' => 'lower'
        ],
        'integer_literal_case' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'cast_spaces' => [
            'space' => 'none'
        ],
        'lowercase_cast' => true,
        'no_short_bool_cast' => true,
        'line_ending' => true,
        'array_indentation' => true,
        'indentation_type' => true,
        'method_chaining_indentation' => true,
        'statement_indentation' => [
            'stick_comment_to_next_continuous_control_statement' => false
        ]
    ])
    ->setFinder($finder);
