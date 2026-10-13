<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withRules([
        DeclareStrictTypesFixer::class,
        NoUnusedImportsFixer::class,
        SingleLineEmptyBodyFixer::class,
    ])
    ->withPreparedSets(
        psr12: true,
        common: true,
        cleanCode: true,
        standaloneLine: true,
    );
