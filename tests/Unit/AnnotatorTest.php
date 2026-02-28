<?php

declare(strict_types=1);

use PestCoverageAnnotator\Annotator;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->annotator = new Annotator;
    $this->output = new BufferedOutput;
});

it('outputs uncovered methods for mixed coverage', function (): void {
    $report = $this->annotator->annotate(
        __DIR__.'/../Fixtures/coverage-mixed.xml',
        $this->output,
    );

    $text = $this->output->fetch();

    expect($text)->toContain('Fully Uncovered Classes')
        ->and($text)->toContain('App\\Services\\InvoiceService')
        ->and($text)->toContain('cancel()')
        ->and($text)->toContain('refund()')
        ->and($text)->toContain('generateInvoice()')
        ->and($text)->toContain('Partially Covered Classes')
        ->and($text)->toContain('App\\Services\\PaymentService')
        ->and($text)->toContain('Summary')
        ->and($text)->not->toContain('Some\\Package\\Helper');
});

it('shows fully covered classes when option enabled', function (): void {
    $this->annotator->annotate(
        __DIR__.'/../Fixtures/coverage-mixed.xml',
        $this->output,
        showCovered: true,
    );

    $text = $this->output->fetch();

    expect($text)->toContain('Fully Covered Classes')
        ->and($text)->toContain('App\\Models\\User')
        ->and($text)->toContain('Fully Covered');
});

it('does not show fully covered section by default', function (): void {
    $this->annotator->annotate(
        __DIR__.'/../Fixtures/coverage-all-covered.xml',
        $this->output,
    );

    $text = $this->output->fetch();

    expect($text)->not->toContain('Fully Covered Classes')
        ->and($text)->toContain('Summary');
});

it('shows message for empty coverage', function (): void {
    $this->annotator->annotate(
        __DIR__.'/../Fixtures/coverage-empty.xml',
        $this->output,
    );

    $text = $this->output->fetch();

    expect($text)->toContain('No classes found');
});

it('returns correct report data', function (): void {
    $report = $this->annotator->annotate(
        __DIR__.'/../Fixtures/coverage-mixed.xml',
        $this->output,
    );

    expect($report->totalClasses())->toBe(3)
        ->and($report->totalFullyCovered())->toBe(1)
        ->and($report->totalUncovered())->toBe(1)
        ->and($report->totalPartiallyCovered())->toBe(1);
});
