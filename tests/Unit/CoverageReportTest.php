<?php

declare(strict_types=1);

use PestCoverageAnnotator\Data\ClassCoverage;
use PestCoverageAnnotator\Data\CoverageReport;

it('categorizes classes correctly', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: ['getFullName' => true, 'isAdmin' => true],
        ),
        'App\\Services\\InvoiceService' => new ClassCoverage(
            className: 'App\\Services\\InvoiceService',
            filePath: '/app/Services/InvoiceService.php',
            methods: ['cancel' => false, 'refund' => false],
        ),
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: ['charge' => true, 'refund' => false],
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
