<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude('node_modules')
    ->exclude('vendor')
    ->exclude('tmp')
    ->exclude('uploads')
    ->exclude('Security/tmp')
    // Exclure les fichiers legacy pendant migration
    ->notPath('Security/sanitize.php')
    ->notPath('Security/csrf.php')
    ->notPath('Security/validate.php')
    ->notPath('Security/room_tokens.php')
    ->notPath('Security/validate_room_token.php')
    ->notPath('Security/init.php')
    ->notPath('Security/load_env.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_align' => false,
        'yoda_style' => false,
        'phpdoc_order' => true,
        // 'class_attributes_separation' => ['elements' => ['property', 'method']],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'no_extra_blank_lines' => true,
        // 'blank_line_before_statement' => ['statements' => ['return', 'try', 'if', 'else', 'elseif', 'switch', 'while', 'do', 'for', 'foreach', 'break', 'continue']],
        'single_quote' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_trailing_whitespace' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        // 'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_only'], // deprecated in newer versions
        'phpdoc_indent' => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(true);
