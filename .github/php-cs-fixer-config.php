<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(dirname(__DIR__)) // up one dir
    ->exclude('vendor')
    ->name('*.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        // imports
        'no_unused_imports' => true,
        'ordered_imports' => true,

        // braces & spaces
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'case', 'do', 'if', 'switch', 'try',
            ],
        ],
        'braces_position' => false,
        /*
        'braces_position' => [
            'control_structures_opening_brace' => 'next_line', // 'next_line' would be ideal, but it's not available
            'functions_opening_brace' => 'next_line', // 'next_line' would be ideal, but it's not available
        ],
        */
        'control_structure_continuation_position' => [
            'position' => 'next_line',
        ],
        'indentation_type' => true,
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'switch', 'use',
            ],
        ],
        'no_multiple_statements_per_line' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_whitespace_in_blank_line' => true,
        'no_whitespace_before_comma_in_array' => true,
        'single_space_around_construct' => [
            'constructs_followed_by_a_single_space' => [
                'abstract', 'as', 'attribute', 'break', 'case', 'class', 'clone', 'comment', 'const', 'const_import',
                'continue', 'do', 'echo', 'else', 'elseif', 'enum', 'extends', 'final', 'function', 'function_import',
                'global', 'goto', 'implements', 'instanceof', 'insteadof', 'interface', 'match', 'named_argument',
                'namespace', 'new', 'open_tag_with_echo', 'php_doc', 'php_open', 'private', 'protected', 'public',
                'readonly', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'type_colon', 'use', 'use_lambda',
                'use_trait', 'var', 'yield', 'yield_from'
            ]
        ],
        'whitespace_after_comma_in_array' => [
            'ensure_single_space' => true
        ],

        // syntax
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'increment_style' => [
            'style' => 'post',
        ],
        'no_empty_statement' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],

        // why is that even an option? PHP, you're so weird
        'semicolon_after_instruction' => true,
        'standardize_not_equals' => true,
        'switch_case_semicolon_to_colon' => true,

        // misc
        'no_closing_tag' => true,
    ])
    ->setFinder($finder);
