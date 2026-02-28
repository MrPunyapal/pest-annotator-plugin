<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use Symfony\Component\Console\Output\OutputInterface;

final class CoverageRenderer
{
    public function render(CoverageReport $report, OutputInterface $output): void
    {
        $this->renderUncoveredClasses($report, $output);
        $this->renderPartiallyCoveredClasses($report, $output);
        $this->renderSummary($report, $output);
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

    private function renderClassBlock(ClassCoverage $class, OutputInterface $output): void
    {
        $percentage = $class->coveragePercentage();
        $color = $this->percentageColor($percentage);

        $output->writeln(sprintf('  📄 <fg=white;options=bold>Class: %s</>', $class->className));
        $output->writeln(sprintf('     Coverage: <fg=%s>%s%%</>', $color, $percentage));

        $uncoveredMethods = $class->uncoveredMethods();

        if ($uncoveredMethods !== []) {
            $methodList = implode('(), ', $uncoveredMethods).'()';
            $output->writeln(sprintf('     ❌ <fg=red>Uncovered: %s</>', $methodList));
        }

        $coveredMethods = $class->coveredMethods();

        if ($coveredMethods !== []) {
            $methodList = implode('(), ', $coveredMethods).'()';
            $output->writeln(sprintf('     ✅ <fg=green>Covered: %s</>', $methodList));
        }

        $output->writeln('');
    }

    private function renderSummary(CoverageReport $report, OutputInterface $output): void
    {
        $output->writeln('<fg=white;options=bold>━━━ Summary ━━━</>');
        $output->writeln('');
        $output->writeln(sprintf('  Total Classes:      <fg=white>%d</>', $report->totalClasses()));
        $output->writeln(sprintf('  Fully Covered:      <fg=green>%d</>', $report->totalFullyCovered()));
        $output->writeln(sprintf('  Partially Covered:  <fg=yellow>%d</>', $report->totalPartiallyCovered()));
        $output->writeln(sprintf('  Fully Uncovered:    <fg=red>%d</>', $report->totalUncovered()));
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
