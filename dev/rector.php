<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (
    RectorConfig $rectorConfig
): void {
    $rootPath = realpath(__DIR__ . '/..') . '/';

    $rectorConfig->cacheDirectory($rootPath . 'var/cache/rector');
    $rectorConfig->containerCacheDirectory($rootPath . 'var/cache');

    $rectorConfig->paths(
        [
            $rootPath . 'src',
            $rootPath . 'tests',
        ]
    );
    $rectorConfig->skip(
        [
            ArraySpreadInsteadOfArrayMergeRector::class,
            CatchExceptionNameMatchingTypeRector::class,
            CountArrayToEmptyArrayComparisonRector::class,
            EncapsedStringsToSprintfRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            SimplifyIfElseToTernaryRector::class,
        ]
    );
    $rectorConfig->sets(
        [
            LevelSetList::UP_TO_PHP_83,
            SetList::CODE_QUALITY,
            SetList::CODING_STYLE,
            SetList::DEAD_CODE,
            SetList::EARLY_RETURN,
        ]
    );
};
