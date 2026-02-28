<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;

it('reports fully covered when all methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Models\\User',
        filePath: '/var/www/app/Models/User.php',
        methods: ['getFullName' => true, 'isAdmin' => true],
    );

    expect($class->isFullyCovered())->toBeTrue()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->uncoveredMethods())->toBe([])
        ->and($class->coveredMethods())->toBe(['getFullName', 'isAdmin'])
        ->and($class->coveragePercentage())->toBe(100.0);
});

it('reports fully uncovered when no methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Services\\InvoiceService',
        filePath: '/var/www/app/Services/InvoiceService.php',
        methods: ['cancel' => false, 'refund' => false, 'generateInvoice' => false],
    );

    expect($class->isFullyCovered())->toBeFalse()
        ->and($class->isFullyUncovered())->toBeTrue()
        ->and($class->uncoveredMethods())->toBe(['cancel', 'refund', 'generateInvoice'])
        ->and($class->coveredMethods())->toBe([])
        ->and($class->coveragePercentage())->toBe(0.0);
});

it('reports partial coverage when some methods are covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Services\\PaymentService',
        filePath: '/var/www/app/Services/PaymentService.php',
        methods: ['charge' => true, 'validate' => true, 'refund' => false],
    );

    expect($class->isFullyCovered())->toBeFalse()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->uncoveredMethods())->toBe(['refund'])
        ->and($class->coveredMethods())->toBe(['charge', 'validate'])
        ->and($class->coveragePercentage())->toBe(66.7);
});

it('treats empty methods as fully covered', function (): void {
    $class = new ClassCoverage(
        className: 'App\\Models\\EmptyModel',
        filePath: '/var/www/app/Models/EmptyModel.php',
        methods: [],
    );

    expect($class->isFullyCovered())->toBeTrue()
        ->and($class->isFullyUncovered())->toBeFalse()
        ->and($class->coveragePercentage())->toBe(100.0);
});
