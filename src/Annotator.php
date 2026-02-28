<?php

declare(strict_types=1);

namespace PestCoverageAnnotator;

use PestCoverageAnnotator\Data\ClassCoverage;
use PestCoverageAnnotator\Data\CoverageReport;
use PestCoverageAnnotator\Parsers\CoverageParser;
use Symfony\Component\Console\Output\OutputInterface;

final class Annotator
{
    public function __construct(
        private readonly CoverageParser $parser = new CoverageParser(),
    ) {}

    /**
     * Parses the coverage XML and writes the annotated report to the output.
     *
     * @param array<int, string> $includePrefixes
     */
    public function annotate(
        string $coveragePath,
        OutputInterface $output,
        array $includePrefixes = ['app/', 'src/'],
        bool $showCovered = false,
    ): CoverageReport {
        $report = $this->parser->parse($coveragePath, $includePrefixes);

        if ($report->totalClasses() === 0) {
            $output->writeln('<comment>No classes found in coverage report matching the given filters.</comment>');

            return $report;
        }

        $this->renderUncoveredClasses($report, $output);
        $this->renderPartiallyCoveredClasses($report, $output);

        if ($showCovered) {
            $this->renderFullyCoveredClasses($report, $output);
        }

        $this->renderSummary($report, $output);

        return $report;
    }

    private function renderUncoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $uncovered = $report->fullyUncoveredClasses();

        if ($uncovered === []) {
            return;
        }

        $output->writeln('');
        $output->writeln('<fg=red;options=bold>━━━ Fully Uncovered Classes ━━━</>');
        $output->writeln('');

        foreach ($uncovered as $class) {
            $this->renderClassBlock($class, $output);
        }
    }

    private function renderPartiallyCoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $partial = $report->partiallyCoveredClasses();

        if ($partial === []) {
            return;
        }

        $output->writeln('');
        $output->writeln('<fg=yellow;options=bold>━━━ Partially Covered Classes ━━━</>');
        $output->writeln('');

        foreach ($partial as $class) {
            $this->renderClassBlock($class, $output);
        }
    }

    private function renderFullyCoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $covered = $report->fullyCoveredClasses();

        if ($covered === []) {
            return;
        }

        $output->writeln('');
        $output->writeln('<fg=green;options=bold>━━━ Fully Covered Classes ━━━</>');
        $output->writeln('');

        foreach ($covered as $class) {
            $output->writeln("  📄 <fg=green>Class: {$class->className}</>");
            $output->writeln('     ✅ <fg=green>Fully Covered</>');
            $output->writeln('');
        }
    }

    private function renderClassBlock(ClassCoverage $class, OutputInterface $output): void
    {
        $percentage = $class->coveragePercentage();
        $color = $this->percentageColor($percentage);

        $output->writeln("  📄 <fg=white;options=bold>Class: {$class->className}</>");
        $output->writeln("     Coverage: <fg={$color}>{$percentage}%</>");

        $uncoveredMethods = $class->uncoveredMethods();

        if ($uncoveredMethods !== []) {
            $methodList = implode('(), ', $uncoveredMethods) . '()';
            $output->writeln("     ❌ <fg=red>Uncovered: {$methodList}</>");
        }

        $coveredMethods = $class->coveredMethods();

        if ($coveredMethods !== []) {
            $methodList = implode('(), ', $coveredMethods) . '()';
            $output->writeln("     ✅ <fg=green>Covered: {$methodList}</>");
        }

        $output->writeln('');
    }

    private function renderSummary(CoverageReport $report, OutputInterface $output): void
    {
        $output->writeln('<fg=white;options=bold>━━━ Summary ━━━</>');
        $output->writeln('');
        $output->writeln("  Total Classes:      <fg=white>{$report->totalClasses()}</>");
        $output->writeln("  Fully Covered:      <fg=green>{$report->totalFullyCovered()}</>");
        $output->writeln("  Partially Covered:  <fg=yellow>{$report->totalPartiallyCovered()}</>");
        $output->writeln("  Fully Uncovered:    <fg=red>{$report->totalUncovered()}</>");
        $output->writeln('');
    }

    private function percentageColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 80.0 => 'green',
            $percentage >= 50.0 => 'yellow',
            default => 'red',
        };
    }
}
