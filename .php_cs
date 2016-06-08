<?php

$header = <<<EOF
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->fixers([
        'short_array_syntax',
        'ordered_use',
        'php_unit_construct',
        'php_unit_strict',
        'strict'
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__.'/src')
    )
;