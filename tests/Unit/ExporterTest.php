<?php

declare(strict_types=1);

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodCoverage;
use PestAnnotator\Renderers\HtmlExporter;
use PestAnnotator\Renderers\JsonExporter;
use PestAnnotator\Renderers\MarkdownExporter;

beforeEach(function (): void {
    $this->report = new CoverageReport([
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

    $this->tempDir = sys_get_temp_dir().'/pest-annotator-test-'.uniqid();
    mkdir($this->tempDir, 0777, true);
});

afterEach(function (): void {
    $files = glob($this->tempDir.'/*');
    if ($files !== false) {
        foreach ($files as $file) {
            unlink($file);
        }
    }

    rmdir($this->tempDir);
});

it('exports JSON report', function (): void {
    $path = $this->tempDir.'/report.json';

    $exporter = new JsonExporter;
    $exporter->export($this->report, $path);

    expect(file_exists($path))->toBeTrue();

    $data = json_decode(file_get_contents($path), true);

    expect($data['summary']['totalClasses'])->toBe(2)
        ->and($data['summary']['fullyCovered'])->toBe(1)
        ->and($data['summary']['fullyUncovered'])->toBe(1)
        ->and($data['classes'])->toHaveKey('App\\Models\\User')
        ->and($data['classes']['App\\Models\\User']['coverage'])->toBe(100)
        ->and($data['classes']['App\\Services\\Broken']['coverage'])->toBe(0);
});

it('exports Markdown report', function (): void {
    $path = $this->tempDir.'/report.md';

    $exporter = new MarkdownExporter;
    $exporter->export($this->report, $path);

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);

    expect($content)->toContain('# Coverage Annotator Report')
        ->and($content)->toContain('## Summary')
        ->and($content)->toContain('| Total Classes | 2 |')
        ->and($content)->toContain('Fully Uncovered Classes')
        ->and($content)->toContain('App\\Services\\Broken')
        ->and($content)->toContain('Fully Covered Classes')
        ->and($content)->toContain('App\\Models\\User');
});

it('exports HTML report', function (): void {
    $path = $this->tempDir.'/report.html';

    $exporter = new HtmlExporter;
    $exporter->export($this->report, $path);

    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);

    expect($content)->toContain('Coverage Annotator Report')
        ->and($content)->toContain('App\\Models\\User')
        ->and($content)->toContain('App\\Services\\Broken')
        ->and($content)->toContain('<table>');
});
