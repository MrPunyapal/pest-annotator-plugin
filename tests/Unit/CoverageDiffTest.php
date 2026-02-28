<?php

declare(strict_types=1);

use PestAnnotator\Data\CoverageDiff;

it('detects changes', function (): void {
    $diff = new CoverageDiff(
        regressedClasses: ['App\\A' => ['from' => 80.0, 'to' => 50.0]],
        improvedClasses: ['App\\B' => ['from' => 50.0, 'to' => 90.0]],
        newClasses: ['App\\C' => 75.0],
        removedClasses: ['App\\D' => 100.0],
    );

    expect($diff->hasChanges())->toBeTrue()
        ->and($diff->totalRegressions())->toBe(1)
        ->and($diff->totalImprovements())->toBe(1)
        ->and($diff->totalNew())->toBe(1)
        ->and($diff->totalRemoved())->toBe(1);
});

it('reports no changes when empty', function (): void {
    $diff = new CoverageDiff(
        regressedClasses: [],
        improvedClasses: [],
        newClasses: [],
        removedClasses: [],
    );

    expect($diff->hasChanges())->toBeFalse()
        ->and($diff->totalRegressions())->toBe(0)
        ->and($diff->totalImprovements())->toBe(0)
        ->and($diff->totalNew())->toBe(0)
        ->and($diff->totalRemoved())->toBe(0);
});
