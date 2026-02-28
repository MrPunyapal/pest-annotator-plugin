<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassTypeCoverage;
use PestAnnotator\Data\MissingTypeInfo;
use PestAnnotator\Data\TypeCoverageReport;
use PestAnnotator\Renderers\TypeCoverageRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->output = new BufferedOutput;
});

it('renders type coverage sections', function (): void {
    $report = new TypeCoverageReport([
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
        'App\\Services\\PaymentService' => new ClassTypeCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            totalDeclarations: 10,
            typedDeclarations: 7,
            missingTypes: [
                new MissingTypeInfo('return', 'process', 15, 'process'),
            ],
        ),
        'App\\Models\\User' => new ClassTypeCoverage(
            className: 'App\\Models\\User',
            filePath: '/app/Models/User.php',
            totalDeclarations: 5,
            typedDeclarations: 5,
            missingTypes: [],
        ),
    ]);

    $renderer = new TypeCoverageRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('Type Coverage')
        ->and($text)->toContain('Untyped Classes')
        ->and($text)->toContain('OldClass')
        ->and($text)->toContain('Partially Typed Classes')
        ->and($text)->toContain('PaymentService')
        ->and($text)->toContain('Fully Typed Classes')
        ->and($text)->toContain('User')
        ->and($text)->toContain('Type Coverage Summary');
});

it('shows method details when showMethods enabled', function (): void {
    $report = new TypeCoverageReport([
        'App\\Services\\PaymentService' => new ClassTypeCoverage(
            className: 'App\\Services\\PaymentService',
            filePath: '/app/Services/PaymentService.php',
            totalDeclarations: 5,
            typedDeclarations: 3,
            missingTypes: [
                new MissingTypeInfo('return', 'process', 15, 'process'),
                new MissingTypeInfo('param', '$amount', 20, 'charge'),
            ],
        ),
    ]);

    $renderer = new TypeCoverageRenderer(showMethods: true);
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('return')
        ->and($text)->toContain('process')
        ->and($text)->toContain('param')
        ->and($text)->toContain('$amount');
});

it('handles empty report', function (): void {
    $report = new TypeCoverageReport([]);

    $renderer = new TypeCoverageRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toBe('');
});
