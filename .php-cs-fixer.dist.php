<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'blank_line_after_opening_tag' => false,
        'blank_lines_before_namespace' => [
            'min_line_breaks' => 1, 'max_line_breaks' => 1
        ],
        'single_line_empty_body' => true,
        'class_definition' => [
            'single_line' => true,
        ],
        'no_empty_statement' => true,
        'array_syntax' => ['syntax' => 'short'],
        'strict_param' => true,
    ])->setFinder($finder);
