<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create();
// No ->in() needed as we're passing files directly

return (new PhpCsFixer\Config())
    ->setRules([
        // imports
        'ordered_imports' => true,
        'no_unused_imports' => true,

        // arrays
        'array_syntax' => 'short',
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        'ensure_single_space' => true,

        // braces & spaces
        'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
    ])
    ->setFinder($finder);
