<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodCoverage;
use PestAnnotator\Support\DiffCalculator;

it('detects regressions and improvements', function (): void {
    $baseline = [
        'App\\A' => 100.0,
        'App\\B' => 50.0,
        'App\\C' => 80.0,
    ];

    $current = new CoverageReport([
        'App\\A' => new ClassCoverage(
            className: 'App\\A',
            filePath: '/a.php',
            methods: [
                'handle' => new MethodCoverage('handle', 10, 20, 5, 3, 'public'),
                'process' => new MethodCoverage('process', 25, 35, 4, 0, 'public'),
            ],
        ),
        'App\\B' => new ClassCoverage(
            className: 'App\\B',
            filePath: '/b.php',
            methods: [
                'run' => new MethodCoverage('run', 10, 20, 5, 5, 'public'),
            ],
        ),
        'App\\D' => new ClassCoverage(
            className: 'App\\D',
            filePath: '/d.php',
            methods: [
                'exec' => new MethodCoverage('exec', 10, 20, 5, 5, 'public'),
            ],
        ),
    ]);

    $calculator = new DiffCalculator;
    $diff = $calculator->calculate($baseline, $current);

    expect($diff->totalRegressions())->toBe(1)
        ->and($diff->regressedClasses)->toHaveKey('App\\A')
        ->and($diff->totalImprovements())->toBe(1)
        ->and($diff->improvedClasses)->toHaveKey('App\\B')
        ->and($diff->totalNew())->toBe(1)
        ->and($diff->newClasses)->toHaveKey('App\\D')
        ->and($diff->totalRemoved())->toBe(1)
        ->and($diff->removedClasses)->toHaveKey('App\\C');
});

it('reports no changes when coverage is identical', function (): void {
    $baseline = ['App\\A' => 100.0];

    $current = new CoverageReport([
        'App\\A' => new ClassCoverage(
            className: 'App\\A',
            filePath: '/a.php',
            methods: [
                'handle' => new MethodCoverage('handle', 10, 20, 5, 5, 'public'),
            ],
        ),
    ]);

    $calculator = new DiffCalculator;
    $diff = $calculator->calculate($baseline, $current);

    expect($diff->hasChanges())->toBeFalse();
});
