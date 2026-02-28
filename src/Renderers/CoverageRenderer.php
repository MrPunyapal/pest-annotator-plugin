<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class CoverageRenderer
{
    public function __construct(
        private bool $showMethods = false,
        private bool $showCovered = false,
    ) {}

    public function render(CoverageReport $report, OutputInterface $output): void
    {
        $output->writeln('');

        $this->renderUncoveredClasses($report, $output);
        $this->renderPartiallyCoveredClasses($report, $output);

        if ($this->showCovered) {
            $this->renderCoveredClasses($report, $output);
        }

        $this->renderSummary($report, $output);
    }

    private function renderUncoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $uncovered = $report->fullyUncoveredClasses();

        if ($uncovered === []) {
            return;
        }

        $output->writeln('  <fg=red;options=bold>Fully Uncovered Classes</>');
        $output->writeln('');

        foreach ($uncovered as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderPartiallyCoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $partial = $report->partiallyCoveredClasses();

        if ($partial === []) {
            return;
        }

        $output->writeln('  <fg=yellow;options=bold>Partially Covered Classes</>');
        $output->writeln('');

        foreach ($partial as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderCoveredClasses(CoverageReport $report, OutputInterface $output): void
    {
        $covered = $report->fullyCoveredClasses();

        if ($covered === []) {
            return;
        }

        $output->writeln('  <fg=green;options=bold>Fully Covered Classes</>');
        $output->writeln('');

        foreach ($covered as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderClassRow(ClassCoverage $class, OutputInterface $output): void
    {
        $percentage = $class->coveragePercentage();
        $linePercentage = $class->lineCoveragePercentage();
        $color = $this->percentageColor($percentage);
        $bar = $this->progressBar($percentage);

        $methodCount = count($class->methods);
        $coveredCount = count($class->coveredMethods());

        $output->writeln(sprintf(
            '  %s <fg=%s>%5s%%</> <fg=gray>(%d/%d methods, %s%% lines)</> %s',
            $bar,
            $color,
            number_format($percentage, 1),
            $coveredCount,
            $methodCount,
            number_format($linePercentage, 1),
            $class->className,
        ));

        if ($this->showMethods) {
            $this->renderMethodDetails($class, $output);
        }
    }

    private function renderMethodDetails(ClassCoverage $class, OutputInterface $output): void
    {
        $uncovered = $class->uncoveredMethods();
        $covered = $class->coveredMethods();

        foreach ($uncovered as $method) {
            $output->writeln(sprintf(
                '     <fg=red>✕ %s</> <fg=gray>L%d-%d (%d/%d lines)</>',
                $method->name.'()',
                $method->startLine,
                $method->endLine,
                $method->executedLines,
                $method->executableLines,
            ));
        }

        if ($this->showCovered) {
            foreach ($covered as $method) {
                $output->writeln(sprintf(
                    '     <fg=green>✓ %s</> <fg=gray>L%d-%d</>',
                    $method->name.'()',
                    $method->startLine,
                    $method->endLine,
                ));
            }
        }
    }

    private function renderSummary(CoverageReport $report, OutputInterface $output): void
    {
        $output->writeln('  <fg=white;options=bold>Summary</>');
        $output->writeln('');
        $output->writeln(sprintf('  Total Classes:      <fg=white>%d</>', $report->totalClasses()));
        $output->writeln(sprintf('  Fully Covered:      <fg=green>%d</>', $report->totalFullyCovered()));
        $output->writeln(sprintf('  Partially Covered:  <fg=yellow>%d</>', $report->totalPartiallyCovered()));
        $output->writeln(sprintf('  Fully Uncovered:    <fg=red>%d</>', $report->totalUncovered()));
        $output->writeln('');
    }

    /** Generates a colored progress bar (10 segments). */
    private function progressBar(float $percentage): string
    {
        $filled = (int) round($percentage / 10);
        $empty = 10 - $filled;
        $color = $this->percentageColor($percentage);

        return sprintf(
            '<fg=%s>%s</><fg=gray>%s</>',
            $color,
            str_repeat('█', $filled),
            str_repeat('░', $empty),
        );
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
