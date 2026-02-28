<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodCoverage;

it('categorizes classes correctly', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: [
                'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
                'isAdmin' => new MethodCoverage('isAdmin', 20, 25, 2, 2, 'public'),
            ],
        ),
        'App\\Services\\InvoiceService' => new ClassCoverage(
            className: 'App\\Services\\InvoiceService',
            filePath: '/app/Services/InvoiceService.php',
            methods: [
                'cancel' => new MethodCoverage('cancel', 10, 20, 5, 0, 'public'),
                'refund' => new MethodCoverage('refund', 25, 40, 8, 0, 'public'),
            ],
        ),
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: [
                'charge' => new MethodCoverage('charge', 10, 20, 5, 5, 'public'),
                'refund' => new MethodCoverage('refund', 25, 40, 8, 0, 'public'),
            ],
        ),
    ]);

    expect($report->totalClasses())->toBe(3)
        ->and($report->totalFullyCovered())->toBe(1)
        ->and($report->totalUncovered())->toBe(1)
        ->and($report->totalPartiallyCovered())->toBe(1)
        ->and($report->fullyCoveredClasses())->toHaveKey('App\\Models\\User')
        ->and($report->fullyUncoveredClasses())->toHaveKey('App\\Services\\InvoiceService')
        ->and($report->partiallyCoveredClasses())->toHaveKey('App\\Services\\PaymentService');
});

it('handles empty report', function (): void {
    $report = new CoverageReport([]);

    expect($report->totalClasses())->toBe(0)
        ->and($report->totalFullyCovered())->toBe(0)
        ->and($report->totalUncovered())->toBe(0)
        ->and($report->totalPartiallyCovered())->toBe(0);
});
