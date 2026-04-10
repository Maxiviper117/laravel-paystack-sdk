<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config/paystack.php',
    ])
    ->withPhpVersion(80300)
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
    )
    ->withSkip([
        __DIR__ . '/tests/ArchTest.php',
        // This package uses named arguments in internal wiring; constructor promotion can silently rename parameters.
        ClassPropertyAssignToConstructorPromotionRector::class,
    ])
    ->withImportNames(
        importNames: true,
        importDocBlockNames: true,
        removeUnusedImports: true,
    )
    ->withParallel(
        timeoutSeconds: 120,
        maxNumberOfProcess: getenv('CI') ? 1 : 4,
        jobSize: 20,
    )
    ->withCache(
        cacheDirectory: __DIR__ . '/build/rector',
    );
