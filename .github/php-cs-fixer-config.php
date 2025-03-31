<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(dirname(__DIR__)) // up one dir
    ->exclude('vendor')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        // imports
        'ordered_imports' => true,
        'no_unused_imports' => true,

        // arrays
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true
        ],

        // braces & spaces
        'braces_position' => [
            'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'method_chaining_indentation' => true,
        'blank_line_before_statement' => [
            'case', 'do', 'if', 'switch', 'try',
        ],

        // misc
        'standardize_not_equals' => true,
        'increment_style' => [
            'style' => 'post',
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'no_closing_tag' => true,
    ])
    ->setFinder($finder);
