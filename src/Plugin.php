<?php

declare(strict_types=1);

namespace PestAnnotator;

use Pest\Contracts\Plugins\AddsOutput;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Support\Coverage;
use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Renderers\ComplexityRenderer;
use PestAnnotator\Renderers\CoverageRenderer;
use PestAnnotator\Renderers\DiffRenderer;
use PestAnnotator\Renderers\HtmlExporter;
use PestAnnotator\Renderers\JsonExporter;
use PestAnnotator\Renderers\MarkdownExporter;
use PestAnnotator\Renderers\TypeCoverageRenderer;
use PestAnnotator\Support\ArgumentParser;
use PestAnnotator\Support\BaselineManager;
use PestAnnotator\Support\ComplexityAnalyzer;
use PestAnnotator\Support\CoverageAnalyzer;
use PestAnnotator\Support\DiffCalculator;
use PestAnnotator\Support\TypeCoverageAnalyzer;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class Plugin implements AddsOutput, HandlesArguments
{
    use HandleArguments;

    private ArgumentParser $parser;

    public function __construct(
        private OutputInterface $output,
    ) {
        $this->parser = new ArgumentParser;
    }

    /** @param array<int, string> $arguments */
    public function handleArguments(array $arguments): array
    {
        return $this->parser->parse($arguments);
    }

    public function addOutput(int $exitCode): int
    {
        if (! $this->parser->isCoverageEnabled() || ! $this->parser->isAnnotateEnabled() || $exitCode !== 0) {
            return $exitCode;
        }

        if (isset($_SERVER['PARATEST'])) {
            return $exitCode;
        }

        $coveragePath = Coverage::getPath();

        if (! file_exists($coveragePath)) {
            return $exitCode;
        }

        /** @var CodeCoverage $codeCoverage */
        $codeCoverage = require $coveragePath;

        $analyzer = new CoverageAnalyzer;
        $report = $analyzer->analyze($codeCoverage);

        $report = $this->applyFilters($report);

        if ($report->totalClasses() === 0) {
            return $exitCode;
        }

        $this->renderCoverage($report);
        $this->renderTypeCoverage($report);
        $this->renderComplexity($report);
        $this->handleBaseline($report);
        $this->renderDiff($report);
        $this->export($report);

        return $this->enforceThreshold($report, $exitCode);
    }

    private function applyFilters(CoverageReport $report): CoverageReport
    {
        if ($this->parser->getNamespaceFilter() !== null) {
            $report = $report->filterByNamespace($this->parser->getNamespaceFilter());
        }

        if ($this->parser->getNamespaceExclude() !== null) {
            return $report->excludeNamespace($this->parser->getNamespaceExclude());
        }

        return $report;
    }

    private function renderCoverage(CoverageReport $report): void
    {
        $renderer = new CoverageRenderer(
            showMethods: $this->parser->shouldShowMethods(),
            showCovered: $this->parser->shouldShowCovered(),
        );
        $renderer->render($report, $this->output);
    }

    private function renderTypeCoverage(CoverageReport $report): void
    {
        if (! $this->parser->shouldShowTypes()) {
            return;
        }

        $filePaths = array_values(array_unique(array_map(
            static fn (ClassCoverage $class): string => $class->filePath,
            $report->classes,
        )));

        $analyzer = new TypeCoverageAnalyzer;
        $typeReport = $analyzer->analyze($filePaths);

        $renderer = new TypeCoverageRenderer(
            showMethods: $this->parser->shouldShowMethods(),
        );
        $renderer->render($typeReport, $this->output);
    }

    private function renderComplexity(CoverageReport $report): void
    {
        if (! $this->parser->shouldShowComplexity()) {
            return;
        }

        $filePaths = array_values(array_unique(array_map(
            static fn (ClassCoverage $class): string => $class->filePath,
            $report->classes,
        )));

        $analyzer = new ComplexityAnalyzer;
        $complexityReport = $analyzer->analyze($filePaths, $report);

        $renderer = new ComplexityRenderer;
        $renderer->render($complexityReport, $this->output);
    }

    private function handleBaseline(CoverageReport $report): void
    {
        if (! $this->parser->shouldSaveBaseline()) {
            return;
        }

        $manager = new BaselineManager;
        $manager->save($report);

        $this->output->writeln('  <fg=green;options=bold>Baseline saved successfully.</>');
        $this->output->writeln('');
    }

    private function renderDiff(CoverageReport $report): void
    {
        if (! $this->parser->shouldShowDiff()) {
            return;
        }

        $manager = new BaselineManager;

        if (! $manager->exists()) {
            $this->output->writeln('  <fg=yellow>No baseline found. Run with --annotate-save-baseline first.</>');
            $this->output->writeln('');

            return;
        }

        $baseline = $manager->load();
        $calculator = new DiffCalculator;
        $diff = $calculator->calculate($baseline, $report);

        $renderer = new DiffRenderer;
        $renderer->render($diff, $this->output);
    }

    private function export(CoverageReport $report): void
    {
        $format = $this->parser->getExportFormat();

        if ($format === null) {
            return;
        }

        $outputPath = $this->parser->getExportOutput();

        match ($format) {
            'json' => (new JsonExporter)->export($report, $outputPath ?? 'coverage-annotator.json'),
            'md' => (new MarkdownExporter)->export($report, $outputPath ?? 'coverage-annotator.md'),
            'html' => (new HtmlExporter)->export($report, $outputPath ?? 'coverage-annotator.html'),
            default => $this->output->writeln(sprintf('  <fg=red>Unknown export format: %s</>', $format)),
        };

        if (in_array($format, ['json', 'md', 'html'], true)) {
            $this->output->writeln(sprintf('  <fg=green>Coverage report exported to %s</>', $outputPath ?? 'coverage-annotator.'.$format));
            $this->output->writeln('');
        }
    }

    private function enforceThreshold(CoverageReport $report, int $exitCode): int
    {
        $min = $this->parser->getMinCoverage();

        if ($min === null) {
            return $exitCode;
        }

        $failing = $report->classesBelowThreshold((float) $min);

        if ($failing === []) {
            return $exitCode;
        }

        $this->output->writeln(sprintf(
            '  <fg=red;options=bold>%d class(es) below minimum coverage threshold of %d%%:</>',
            count($failing),
            $min,
        ));

        foreach ($failing as $class) {
            $this->output->writeln(sprintf(
                '    <fg=red>%s</> <fg=gray>(%s%%)</>',
                $class->className,
                number_format($class->coveragePercentage(), 1),
            ));
        }

        $this->output->writeln('');

        return 1;
    }
}
