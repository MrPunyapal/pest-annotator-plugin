<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassComplexity;
use PestAnnotator\Data\MethodComplexity;

it('calculates max complexity', function (): void {
    $class = new ClassComplexity(
        className: 'App\\Services\\PaymentService',
        filePath: '/app/Services/PaymentService.php',
        methods: [
            'charge' => new MethodComplexity('charge', 5, 80.0),
            'validate' => new MethodComplexity('validate', 2, 100.0),
            'refund' => new MethodComplexity('refund', 12, 0.0),
        ],
    );

    expect($class->maxComplexity())->toBe(12)
        ->and($class->averageComplexity())->toBe(6.3)
        ->and($class->totalComplexity())->toBe(19);
});

it('finds high risk methods', function (): void {
    $class = new ClassComplexity(
        className: 'App\\Services\\Complex',
        filePath: '/app/Services/Complex.php',
        methods: [
            'simple' => new MethodComplexity('simple', 2, 100.0),
            'dangerous' => new MethodComplexity('dangerous', 15, 10.0),
            'risky' => new MethodComplexity('risky', 10, 30.0),
        ],
    );

    $highRisk = $class->highRiskMethods();

    expect($highRisk)->toHaveCount(2)
        ->and($highRisk)->toHaveKey('dangerous')
        ->and($highRisk)->toHaveKey('risky');
});

it('handles empty methods', function (): void {
    $class = new ClassComplexity(
        className: 'App\\Models\\Empty',
        filePath: '/app/Models/Empty.php',
        methods: [],
    );

    expect($class->maxComplexity())->toBe(0)
        ->and($class->averageComplexity())->toBe(0.0)
        ->and($class->totalComplexity())->toBe(0)
        ->and($class->highRiskMethods())->toBe([]);
});
