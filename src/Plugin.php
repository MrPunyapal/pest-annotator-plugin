<?php

declare(strict_types=1);

namespace PestAnnotator;

use Pest\Contracts\Plugins\AddsOutput;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Support\Coverage;
use PestAnnotator\Renderers\CoverageRenderer;
use PestAnnotator\Support\CoverageAnalyzer;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Console\Output\OutputInterface;

final class Plugin implements AddsOutput, HandlesArguments
{
    use HandleArguments;

    private bool $coverageEnabled = false;

    public function __construct(
        private readonly OutputInterface $output,
    ) {}

    /** @param array<int, string> $arguments */
    public function handleArguments(array $arguments): array
    {
        if ($this->hasArgument('--coverage', $arguments)) {
            $this->coverageEnabled = true;
        }

        return $arguments;
    }

    public function addOutput(int $exitCode): int
    {
        if (! $this->coverageEnabled || $exitCode !== 0) {
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

        if ($report->totalClasses() === 0) {
            return $exitCode;
        }

        $renderer = new CoverageRenderer;
        $renderer->render($report, $this->output);

        return $exitCode;
    }
}
