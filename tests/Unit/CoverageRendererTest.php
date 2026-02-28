<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodCoverage;
use PestAnnotator\Renderers\CoverageRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->output = new BufferedOutput;
});

it('renders uncovered and partially covered classes', function (): void {
    $report = new CoverageReport([
        'App\\Services\\InvoiceService' => new ClassCoverage(
            className: 'App\\Services\\InvoiceService',
            filePath: '/app/Services/InvoiceService.php',
            methods: [
                'cancel' => new MethodCoverage('cancel', 10, 20, 5, 0, 'public'),
                'refund' => new MethodCoverage('refund', 25, 40, 8, 0, 'public'),
                'generateInvoice' => new MethodCoverage('generateInvoice', 45, 70, 12, 0, 'public'),
            ],
        ),
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: [
                'charge' => new MethodCoverage('charge', 10, 20, 5, 5, 'public'),
                'validate' => new MethodCoverage('validate', 25, 35, 4, 4, 'public'),
                'refund' => new MethodCoverage('refund', 40, 55, 6, 0, 'public'),
            ],
        ),
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: [
                'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
                'isAdmin' => new MethodCoverage('isAdmin', 20, 25, 2, 2, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('Fully Uncovered Classes')
        ->and($text)->toContain('App\\Services\\InvoiceService')
        ->and($text)->toContain('0/3 methods')
        ->and($text)->toContain('Partially Covered Classes')
        ->and($text)->toContain('App\\Services\\PaymentService')
        ->and($text)->toContain('2/3 methods')
        ->and($text)->toContain('Summary')
        ->and($text)->toContain('Total Classes:')
        ->and($text)->not->toContain('Fully Covered Classes');
});

it('renders summary with correct counts', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: [
                'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
            ],
        ),
        'App\\Services\\Broken' => new ClassCoverage(
            className: 'App\\Services\\Broken',
            filePath: '/app/Services/Broken.php',
            methods: [
                'handle' => new MethodCoverage('handle', 10, 30, 10, 0, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer;
    $renderer->render($report, $this->output);

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
            methods: [
                'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
                'isAdmin' => new MethodCoverage('isAdmin', 20, 25, 2, 2, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->not->toContain('Fully Uncovered Classes')
        ->and($text)->not->toContain('Partially Covered Classes')
        ->and($text)->toContain('Summary');
});

it('shows progress bar and line coverage', function (): void {
    $report = new CoverageReport([
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: [
                'charge' => new MethodCoverage('charge', 10, 20, 5, 5, 'public'),
                'validate' => new MethodCoverage('validate', 25, 35, 4, 4, 'public'),
                'refund' => new MethodCoverage('refund', 40, 55, 6, 0, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('66.7%')
        ->and($text)->toContain('2/3 methods')
        ->and($text)->toContain('60.0% lines');
});

it('shows method details with line numbers when showMethods enabled', function (): void {
    $report = new CoverageReport([
        'App\\Services\\PaymentService' => new ClassCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            methods: [
                'charge' => new MethodCoverage('charge', 10, 20, 5, 5, 'public'),
                'refund' => new MethodCoverage('refund', 40, 55, 6, 0, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer(showMethods: true);
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('refund()')
        ->and($text)->toContain('L40-55')
        ->and($text)->toContain('0/6 lines');
});

it('shows covered methods when showCovered enabled', function (): void {
    $report = new CoverageReport([
        'App\\Models\\User' => new ClassCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            methods: [
                'getFullName' => new MethodCoverage('getFullName', 10, 15, 3, 3, 'public'),
                'isAdmin' => new MethodCoverage('isAdmin', 20, 25, 2, 2, 'public'),
            ],
        ),
    ]);

    $renderer = new CoverageRenderer(showCovered: true);
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('Fully Covered Classes')
        ->and($text)->toContain('App\\Models\\User')
        ->and($text)->toContain('100.0%');
});
