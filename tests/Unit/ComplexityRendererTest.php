<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassComplexity;
use PestAnnotator\Data\ComplexityReport;
use PestAnnotator\Data\MethodComplexity;
use PestAnnotator\Renderers\ComplexityRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->output = new BufferedOutput;
});

it('renders complexity analysis', function (): void {
    $report = new ComplexityReport([
        'App\\Services\\Complex' => new ClassComplexity(
            className: 'App\\Services\\Complex',
            filePath: '/app/Services/Complex.php',
            methods: [
                'process' => new MethodComplexity('process', 15, 10.0),
                'simple' => new MethodComplexity('simple', 2, 100.0),
            ],
        ),
    ]);

    $renderer = new ComplexityRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('Complexity Analysis')
        ->and($text)->toContain('App\\Services\\Complex')
        ->and($text)->toContain('process()')
        ->and($text)->toContain('Complexity Summary');
});

it('shows warning for high risk methods', function (): void {
    $report = new ComplexityReport([
        'App\\Risky' => new ClassComplexity(
            className: 'App\\Risky',
            filePath: '/app/Risky.php',
            methods: [
                'dangerous' => new MethodComplexity('dangerous', 20, 0.0),
            ],
        ),
    ]);

    $renderer = new ComplexityRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('⚠')
        ->and($text)->toContain('dangerous()');
});

it('handles empty report', function (): void {
    $report = new ComplexityReport([]);

    $renderer = new ComplexityRenderer;
    $renderer->render($report, $this->output);

    $text = $this->output->fetch();

    expect($text)->toBe('');
});
