<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassTypeCoverage;
use PestAnnotator\Data\MissingTypeInfo;
use PestAnnotator\Data\TypeCoverageReport;

it('categorizes typed classes correctly', function (): void {
    $report = new TypeCoverageReport([
        'App\\Models\\User' => new ClassTypeCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            totalDeclarations: 5,
            typedDeclarations: 5,
            missingTypes: [],
        ),
        'App\\Services\\PaymentService' => new ClassTypeCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            totalDeclarations: 10,
            typedDeclarations: 7,
            missingTypes: [
                new MissingTypeInfo('return', 'process', 15, 'process'),
                new MissingTypeInfo('param', '$amount', 20, 'charge'),
                new MissingTypeInfo('property', '$status', 5, 'App\\Services\\PaymentService'),
            ],
        ),
        'App\\Legacy\\OldClass' => new ClassTypeCoverage(
            className: 'App\\Legacy\\OldClass',
            filePath: '/app/Legacy/OldClass.php',
            totalDeclarations: 4,
            typedDeclarations: 0,
            missingTypes: [
                new MissingTypeInfo('return', 'handle', 10, 'handle'),
                new MissingTypeInfo('param', '$data', 10, 'handle'),
                new MissingTypeInfo('return', 'process', 20, 'process'),
                new MissingTypeInfo('param', '$input', 20, 'process'),
            ],
        ),
    ]);

    expect($report->totalClasses())->toBe(3)
        ->and($report->totalFullyTyped())->toBe(1)
        ->and($report->totalPartiallyTyped())->toBe(1)
        ->and($report->totalUntyped())->toBe(1)
        ->and($report->fullyTypedClasses())->toHaveKey('App\\Models\\User')
        ->and($report->partiallyTypedClasses())->toHaveKey('App\\Services\\PaymentService')
        ->and($report->untypedClasses())->toHaveKey('App\\Legacy\\OldClass');
});

it('calculates overall percentage', function (): void {
    $report = new TypeCoverageReport([
        'App\\Models\\User' => new ClassTypeCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            totalDeclarations: 10,
            typedDeclarations: 8,
            missingTypes: [
                new MissingTypeInfo('return', 'foo', 1, 'foo'),
                new MissingTypeInfo('param', '$x', 2, 'foo'),
            ],
        ),
    ]);

    expect($report->overallPercentage())->toBe(80.0);
});

it('handles empty report', function (): void {
    $report = new TypeCoverageReport([]);

    expect($report->totalClasses())->toBe(0)
        ->and($report->overallPercentage())->toBe(100.0);
});
