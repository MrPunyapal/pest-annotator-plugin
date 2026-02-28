<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassTypeCoverage;
use PestAnnotator\Data\MissingTypeInfo;

it('reports fully typed when no missing types', function (): void {
    $class = new ClassTypeCoverage(
        className: 'App\\Models\\User',
        filePath: '/app/Models/User.php',
        totalDeclarations: 5,
        typedDeclarations: 5,
        missingTypes: [],
    );

    expect($class->isFullyTyped())->toBeTrue()
        ->and($class->coveragePercentage())->toBe(100.0)
        ->and($class->missingCount())->toBe(0);
});

it('reports partial type coverage', function (): void {
    $class = new ClassTypeCoverage(
        className: 'App\\Services\\PaymentService',
        filePath: '/app/Services/PaymentService.php',
        totalDeclarations: 10,
        typedDeclarations: 7,
        missingTypes: [
            new MissingTypeInfo('return', 'process', 15, 'process'),
            new MissingTypeInfo('param', '$amount', 20, 'charge'),
            new MissingTypeInfo('property', '$status', 5, 'App\\Services\\PaymentService'),
        ],
    );

    expect($class->isFullyTyped())->toBeFalse()
        ->and($class->coveragePercentage())->toBe(70.0)
        ->and($class->missingCount())->toBe(3)
        ->and($class->missingReturnTypes())->toHaveCount(1)
        ->and($class->missingParamTypes())->toHaveCount(1)
        ->and($class->missingPropertyTypes())->toHaveCount(1);
});

it('handles zero declarations', function (): void {
    $class = new ClassTypeCoverage(
        className: 'App\\Models\\Empty',
        filePath: '/app/Models/Empty.php',
        totalDeclarations: 0,
        typedDeclarations: 0,
        missingTypes: [],
    );

    expect($class->coveragePercentage())->toBe(100.0);
});
