<?php

declare(strict_types=1);

use PestCoverageAnnotator\Annotator;
use Symfony\Component\Console\Output\BufferedOutput;

describe('Annotator', function (): void {
    it('outputs uncovered methods for mixed coverage', function (): void {
        $annotator = new Annotator;
        $output = new BufferedOutput;

        $report = $annotator->annotate(
            __DIR__.'/../Fixtures/coverage-mixed.xml',
            $output,
        );

        $text = $output->fetch();

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
        $annotator = new Annotator;
        $output = new BufferedOutput;

        $annotator->annotate(
            __DIR__.'/../Fixtures/coverage-mixed.xml',
            $output,
            showCovered: true,
        );

        $text = $output->fetch();

        expect($text)->toContain('Fully Covered Classes')
            ->and($text)->toContain('App\\Models\\User')
            ->and($text)->toContain('Fully Covered');
    });

    it('does not show fully covered section by default', function (): void {
        $annotator = new Annotator;
        $output = new BufferedOutput;

        $annotator->annotate(
            __DIR__.'/../Fixtures/coverage-all-covered.xml',
            $output,
        );

        $text = $output->fetch();

        expect($text)->not->toContain('Fully Covered Classes')
            ->and($text)->toContain('Summary');
    });

    it('shows message for empty coverage', function (): void {
        $annotator = new Annotator;
        $output = new BufferedOutput;

        $annotator->annotate(
            __DIR__.'/../Fixtures/coverage-empty.xml',
            $output,
        );

        $text = $output->fetch();

        expect($text)->toContain('No classes found');
    });

    it('returns correct report data', function (): void {
        $annotator = new Annotator;
        $output = new BufferedOutput;

        $report = $annotator->annotate(
            __DIR__.'/../Fixtures/coverage-mixed.xml',
            $output,
        );

        expect($report->totalClasses())->toBe(3)
            ->and($report->totalFullyCovered())->toBe(1)
            ->and($report->totalUncovered())->toBe(1)
            ->and($report->totalPartiallyCovered())->toBe(1);
    });
});
