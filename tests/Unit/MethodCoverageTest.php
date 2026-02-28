<?php

declare(strict_types=1);

use PestAnnotator\Data\MethodCoverage;

it('reports covered when executed lines > 0', function (): void {
    $method = new MethodCoverage('handle', 10, 30, 10, 7, 'public');

    expect($method->isCovered())->toBeTrue()
        ->and($method->coveragePercentage())->toBe(70.0)
        ->and($method->label())->toBe('handle():L10-30');
});

it('reports uncovered when executed lines is 0', function (): void {
    $method = new MethodCoverage('process', 40, 60, 8, 0, 'private');

    expect($method->isCovered())->toBeFalse()
        ->and($method->coveragePercentage())->toBe(0.0)
        ->and($method->label())->toBe('process():L40-60');
});

it('reports 100% when no executable lines', function (): void {
    $method = new MethodCoverage('empty', 5, 5, 0, 0, 'public');

    expect($method->coveragePercentage())->toBe(100.0);
});
