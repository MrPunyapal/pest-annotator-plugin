<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\ClassComplexity;
use PestAnnotator\Data\ComplexityReport;
use PestAnnotator\Data\MethodComplexity;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ComplexityRenderer
{
    public function render(ComplexityReport $report, OutputInterface $output): void
    {
        if ($report->totalClasses() === 0) {
            return;
        }

        $output->writeln('  <fg=cyan;options=bold>Complexity Analysis</>');
        $output->writeln('');

        $sorted = $report->classes;
        uasort($sorted, static fn (ClassComplexity $a, ClassComplexity $b): int => $b->maxComplexity() <=> $a->maxComplexity());

        foreach ($sorted as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
        $this->renderSummary($report, $output);
    }

    private function renderClassRow(ClassComplexity $class, OutputInterface $output): void
    {
        $max = $class->maxComplexity();
        $avg = $class->averageComplexity();
        $color = $this->complexityColor($max);
        $highRisk = $class->highRiskMethods();

        $warning = $highRisk !== [] ? ' <fg=red>⚠</>' : '';

        $output->writeln(sprintf(
            '  <fg=%s>%3d</> <fg=gray>max</> <fg=%s>%4s</> <fg=gray>avg</> %s%s',
            $color,
            $max,
            $this->complexityColor((int) $avg),
            number_format($avg, 1),
            $class->className,
            $warning,
        ));

        if ($highRisk !== []) {
            foreach ($highRisk as $method) {
                $this->renderMethodRow($method, $output);
            }
        }
    }

    private function renderMethodRow(MethodComplexity $method, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            '     <fg=red>⚠ %s()</> <fg=gray>complexity: %d, coverage: %s%%, risk: %s</>',
            $method->name,
            $method->cyclomaticComplexity,
            number_format($method->coveragePercentage, 1),
            number_format($method->riskScore(), 1),
        ));
    }

    private function renderSummary(ComplexityReport $report, OutputInterface $output): void
    {
        $highRisk = $report->highRiskMethods();

        $output->writeln('  <fg=white;options=bold>Complexity Summary</>');
        $output->writeln('');
        $output->writeln(sprintf('  Average Complexity: <fg=white>%s</>', number_format($report->averageComplexity(), 1)));
        $output->writeln(sprintf('  Max Complexity:     <fg=%s>%d</>', $this->complexityColor($report->maxComplexity()), $report->maxComplexity()));
        $output->writeln(sprintf('  High Risk Methods:  <fg=%s>%d</>', $highRisk !== [] ? 'red' : 'green', count($highRisk)));
        $output->writeln('');
    }

    private function complexityColor(int $complexity): string
    {
        return match (true) {
            $complexity >= 10 => 'red',
            $complexity >= 5 => 'yellow',
            default => 'green',
        };
    }
}
