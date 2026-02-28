<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Renderers\CoverageRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->renderer = new CoverageRenderer;
    $this->output = new BufferedOutput;
});

it('renders uncovered and partially covered classes', function (): void {
    $report = new CoverageReport([
        'App\\Services\\InvoiceService' => new ClassCoverage(
            className: 'App\\Services\\InvoiceService',
            filePath: '/app/Services/InvoiceService.php',
            methods: ['cancel' => false, 'refund' => false, 'generateInvoice' => false],
        ),
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: ['charge' => true, 'validate' => true, 'refund' => false],
        ),
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: ['getFullName' => true, 'isAdmin' => true],
        ),
    ]);

    $this->renderer->render($report, $this->output);
    $text = $this->output->fetch();

    expect($text)->toContain('Fully Uncovered Classes')
        ->and($text)->toContain('App\\Services\\InvoiceService')
        ->and($text)->toContain('cancel()')
        ->and($text)->toContain('refund()')
        ->and($text)->toContain('generateInvoice()')
        ->and($text)->toContain('Partially Covered Classes')
        ->and($text)->toContain('App\\Services\\PaymentService')
        ->and($text)->toContain('Summary')
        ->and($text)->toContain('Total Classes:');
});

it('renders summary with correct counts', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: ['getFullName' => true],
        ),
        'App\\Services\\Broken' => new ClassCoverage(
            className: 'App\\Services\\Broken',
            filePath: '/app/Services/Broken.php',
            methods: ['handle' => false],
        ),
    ]);

    $this->renderer->render($report, $this->output);
    $text = $this->output->fetch();

    expect($text)->toContain('Total Classes:')
        ->and($text)->toContain('Fully Covered:')
        ->and($text)->toContain('Fully Uncovered:');
});

it('skips uncovered section when all classes are covered', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: ['getFullName' => true, 'isAdmin' => true],
        ),
    ]);

    $this->renderer->render($report, $this->output);
    $text = $this->output->fetch();

    expect($text)->not->toContain('Fully Uncovered Classes')
        ->and($text)->not->toContain('Partially Covered Classes')
        ->and($text)->toContain('Summary');
});

it('shows coverage percentage and method details', function (): void {
    $report = new CoverageReport([
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: ['charge' => true, 'validate' => true, 'refund' => false],
        ),
    ]);

    $this->renderer->render($report, $this->output);
    $text = $this->output->fetch();

    expect($text)->toContain('66.7%')
        ->and($text)->toContain('Uncovered: refund()')
        ->and($text)->toContain('Covered: charge(), validate()');
});
