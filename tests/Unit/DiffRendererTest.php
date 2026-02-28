<?php

declare(strict_types=1);

use PestAnnotator\Data\CoverageDiff;
use PestAnnotator\Renderers\DiffRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->output = new BufferedOutput;
});

it('renders diff with all change types', function (): void {
    $diff = new CoverageDiff(
        regressedClasses: ['App\\A' => ['from' => 80.0, 'to' => 50.0]],
        improvedClasses: ['App\\B' => ['from' => 50.0, 'to' => 90.0]],
        newClasses: ['App\\C' => 75.0],
        removedClasses: ['App\\D' => 100.0],
    );

    $renderer = new DiffRenderer;
    $renderer->render($diff, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('Coverage Diff')
        ->and($text)->toContain('Regressions')
        ->and($text)->toContain('App\\A')
        ->and($text)->toContain('80.0%')
        ->and($text)->toContain('50.0%')
        ->and($text)->toContain('Improvements')
        ->and($text)->toContain('App\\B')
        ->and($text)->toContain('New Classes')
        ->and($text)->toContain('App\\C')
        ->and($text)->toContain('Removed Classes')
        ->and($text)->toContain('App\\D')
        ->and($text)->toContain('Diff Summary');
});

it('renders no changes message', function (): void {
    $diff = new CoverageDiff(
        regressedClasses: [],
        improvedClasses: [],
        newClasses: [],
        removedClasses: [],
    );

    $renderer = new DiffRenderer;
    $renderer->render($diff, $this->output);

    $text = $this->output->fetch();

    expect($text)->toContain('No changes detected');
});
