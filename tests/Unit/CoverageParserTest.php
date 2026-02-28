<?php

declare(strict_types=1);

use PestCoverageAnnotator\Parsers\CoverageParser;

beforeEach(function (): void {
    $this->parser = new CoverageParser;
});

it('parses a mixed coverage report correctly', function (): void {
    $report = $this->parser->parse(__DIR__.'/../Fixtures/coverage-mixed.xml');

    expect($report->totalClasses())->toBe(3)
        ->and($report->totalFullyCovered())->toBe(1)
        ->and($report->totalUncovered())->toBe(1)
        ->and($report->totalPartiallyCovered())->toBe(1);

    $invoiceService = $report->classes['App\\Services\\InvoiceService'];
    expect($invoiceService->isFullyUncovered())->toBeTrue()
        ->and($invoiceService->uncoveredMethods())->toBe(['cancel', 'refund', 'generateInvoice']);

    $paymentService = $report->classes['App\\Services\\PaymentService'];
    expect($paymentService->isFullyCovered())->toBeFalse()
        ->and($paymentService->isFullyUncovered())->toBeFalse()
        ->and($paymentService->coveredMethods())->toBe(['charge', 'validate'])
        ->and($paymentService->uncoveredMethods())->toBe(['refund']);

    $user = $report->classes['App\\Models\\User'];
    expect($user->isFullyCovered())->toBeTrue()
        ->and($user->coveredMethods())->toBe(['getFullName', 'isAdmin']);
});

it('excludes vendor files by default', function (): void {
    $report = $this->parser->parse(__DIR__.'/../Fixtures/coverage-mixed.xml');

    expect($report->classes)->not->toHaveKey('Some\\Package\\Helper');
});

it('parses all-covered report', function (): void {
    $report = $this->parser->parse(__DIR__.'/../Fixtures/coverage-all-covered.xml');

    expect($report->totalClasses())->toBe(2)
        ->and($report->totalFullyCovered())->toBe(2)
        ->and($report->totalUncovered())->toBe(0)
        ->and($report->totalPartiallyCovered())->toBe(0);
});

it('parses all-uncovered report', function (): void {
    $report = $this->parser->parse(__DIR__.'/../Fixtures/coverage-all-uncovered.xml');

    expect($report->totalClasses())->toBe(2)
        ->and($report->totalFullyCovered())->toBe(0)
        ->and($report->totalUncovered())->toBe(2);
});

it('handles empty coverage report', function (): void {
    $report = $this->parser->parse(__DIR__.'/../Fixtures/coverage-empty.xml');

    expect($report->totalClasses())->toBe(0);
});

it('throws on non-existent file', function (): void {
    $this->parser->parse('/non/existent/file.xml');
})->throws(InvalidArgumentException::class, 'Coverage file not found');

it('supports custom include prefixes', function (): void {
    $report = $this->parser->parse(
        __DIR__.'/../Fixtures/coverage-mixed.xml',
        ['vendor/'],
    );

    expect($report->totalClasses())->toBe(1)
        ->and($report->classes)->toHaveKey('Some\\Package\\Helper');
});

it('handles windows-style paths', function (): void {
    $report = $this->parser->parse(
        __DIR__.'/../Fixtures/coverage-windows-paths.xml',
    );

    expect($report->totalClasses())->toBe(1)
        ->and($report->classes)->toHaveKey('App\\Services\\WindowsService');
});
