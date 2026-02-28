<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\MethodCoverage;

it('reports fully covered when all methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Models\\User',
        filePath: '/var/www/app/Models/User.php',
        methods: [
            'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
            'isAdmin' => new MethodCoverage('isAdmin', 20, 25, 2, 2, 'public'),
        ],
    );

    expect($class->isFullyCovered())->toBeTrue()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->uncoveredMethods())->toBe([])
        ->and($class->coveredMethods())->toHaveCount(2)
        ->and($class->coveragePercentage())->toBe(100.0)
        ->and($class->lineCoveragePercentage())->toBe(100.0);
});

it('reports fully uncovered when no methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Services\\InvoiceService',
        filePath: '/var/www/app/Services/InvoiceService.php',
        methods: [
            'cancel' => new MethodCoverage('cancel', 10, 20, 5, 0, 'public'),
            'refund' => new MethodCoverage('refund', 25, 40, 8, 0, 'public'),
            'generateInvoice' => new MethodCoverage('generateInvoice', 45, 70, 12, 0, 'public'),
        ],
    );

    expect($class->isFullyCovered())->toBeFalse()
        ->and($class->isFullyUncovered())->toBeTrue()
        ->and($class->uncoveredMethods())->toHaveCount(3)
        ->and($class->coveredMethods())->toBe([])
        ->and($class->coveragePercentage())->toBe(0.0)
        ->and($class->lineCoveragePercentage())->toBe(0.0);
});

it('reports partial coverage when some methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Services\\PaymentService',
        filePath: '/var/www/app/Services/PaymentService.php',
        methods: [
            'charge' => new MethodCoverage('charge', 10, 20, 5, 5, 'public'),
            'validate' => new MethodCoverage('validate', 25, 35, 4, 4, 'public'),
            'refund' => new MethodCoverage('refund', 40, 55, 6, 0, 'public'),
        ],
    );

    expect($class->isFullyCovered())->toBeFalse()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->uncoveredMethods())->toHaveCount(1)
        ->and($class->coveredMethods())->toHaveCount(2)
        ->and($class->coveragePercentage())->toBe(66.7)
        ->and($class->lineCoveragePercentage())->toBe(60.0);
});

it('treats empty methods as fully covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Models\\EmptyModel',
        filePath: '/var/www/app/Models/EmptyModel.php',
        methods: [],
    );

    expect($class->isFullyCovered())->toBeTrue()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->coveragePercentage())->toBe(100.0)
        ->and($class->lineCoveragePercentage())->toBe(100.0);
});
