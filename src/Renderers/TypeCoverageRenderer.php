<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\ClassTypeCoverage;
use PestAnnotator\Data\TypeCoverageReport;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class TypeCoverageRenderer
{
    public function __construct(
        private bool $showMethods = false,
    ) {}

    public function render(TypeCoverageReport $report, OutputInterface $output): void
    {
        if ($report->totalClasses() === 0) {
            return;
        }

        $output->writeln('  <fg=cyan;options=bold>Type Coverage</>');
        $output->writeln('');

        $this->renderUntypedClasses($report, $output);
        $this->renderPartiallyTypedClasses($report, $output);
        $this->renderFullyTypedClasses($report, $output);
        $this->renderSummary($report, $output);
    }

    private function renderUntypedClasses(TypeCoverageReport $report, OutputInterface $output): void
    {
        $untyped = $report->untypedClasses();

        if ($untyped === []) {
            return;
        }

        $output->writeln('  <fg=red;options=bold>Untyped Classes</>');
        $output->writeln('');

        foreach ($untyped as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderPartiallyTypedClasses(TypeCoverageReport $report, OutputInterface $output): void
    {
        $partial = $report->partiallyTypedClasses();

        if ($partial === []) {
            return;
        }

        $output->writeln('  <fg=yellow;options=bold>Partially Typed Classes</>');
        $output->writeln('');

        foreach ($partial as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderFullyTypedClasses(TypeCoverageReport $report, OutputInterface $output): void
    {
        $typed = $report->fullyTypedClasses();

        if ($typed === []) {
            return;
        }

        $output->writeln('  <fg=green;options=bold>Fully Typed Classes</>');
        $output->writeln('');

        foreach ($typed as $class) {
            $this->renderClassRow($class, $output);
        }

        $output->writeln('');
    }

    private function renderClassRow(ClassTypeCoverage $class, OutputInterface $output): void
    {
        $percentage = $class->coveragePercentage();
        $color = $this->percentageColor($percentage);
        $bar = $this->progressBar($percentage);

        $output->writeln(sprintf(
            '  %s <fg=%s>%5s%%</> <fg=gray>(%d/%d declarations)</> %s',
            $bar,
            $color,
            number_format($percentage, 1),
            $class->typedDeclarations,
            $class->totalDeclarations,
            $class->className,
        ));

        if ($this->showMethods && $class->missingTypes !== []) {
            foreach ($class->missingTypes as $missing) {
                $icon = match ($missing->kind) {
                    'return' => '↩',
                    'param' => '→',
                    'property' => '◆',
                    default => '?',
                };

                $output->writeln(sprintf(
                    '     <fg=red>%s %s</> <fg=gray>%s in %s() L%d</>',
                    $icon,
                    $missing->kind,
                    $missing->name,
                    $missing->context,
                    $missing->line,
                ));
            }
        }
    }

    private function renderSummary(TypeCoverageReport $report, OutputInterface $output): void
    {
        $output->writeln('  <fg=white;options=bold>Type Coverage Summary</>');
        $output->writeln('');
        $output->writeln(sprintf('  Overall:            <fg=%s>%s%%</>', $this->percentageColor($report->overallPercentage()), number_format($report->overallPercentage(), 1)));
        $output->writeln(sprintf('  Fully Typed:        <fg=green>%d</>', $report->totalFullyTyped()));
        $output->writeln(sprintf('  Partially Typed:    <fg=yellow>%d</>', $report->totalPartiallyTyped()));
        $output->writeln(sprintf('  Untyped:            <fg=red>%d</>', $report->totalUntyped()));
        $output->writeln('');
    }

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
