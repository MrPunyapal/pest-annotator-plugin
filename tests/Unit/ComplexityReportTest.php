<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassComplexity;
use PestAnnotator\Data\ComplexityReport;
use PestAnnotator\Data\MethodComplexity;

it('finds all high risk methods across classes', function (): void {
    $report = new ComplexityReport([
        'App\\Services\\A' => new ClassComplexity(
            className: 'App\\Services\\A',
            filePath: '/app/Services/A.php',
            methods: [
                'risky' => new MethodComplexity('risky', 15, 10.0),
            ],
        ),
        'App\\Services\\B' => new ClassComplexity(
            className: 'App\\Services\\B',
            filePath: '/app/Services/B.php',
            methods: [
                'safe' => new MethodComplexity('safe', 2, 100.0),
            ],
        ),
    ]);

    expect($report->highRiskMethods())->toHaveCount(1)
        ->and($report->highRiskMethods())->toHaveKey('App\\Services\\A::risky');
});

it('calculates average and max complexity', function (): void {
    $report = new ComplexityReport([
        'App\\A' => new ClassComplexity(
            className: 'App\\A',
            filePath: '/a.php',
            methods: [
                'm1' => new MethodComplexity('m1', 4, 100.0),
                'm2' => new MethodComplexity('m2', 8, 50.0),
            ],
        ),
    ]);

    expect($report->averageComplexity())->toBe(6.0)
        ->and($report->maxComplexity())->toBe(8)
        ->and($report->totalClasses())->toBe(1);
});

it('handles empty report', function (): void {
    $report = new ComplexityReport([]);

    expect($report->averageComplexity())->toBe(0.0)
        ->and($report->maxComplexity())->toBe(0)
        ->and($report->totalClasses())->toBe(0)
        ->and($report->highRiskMethods())->toBe([]);
});
