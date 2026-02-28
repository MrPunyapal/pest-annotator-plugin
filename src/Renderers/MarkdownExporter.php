<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\CoverageReport;

final readonly class MarkdownExporter
{
    public function export(CoverageReport $report, string $outputPath): void
    {
        $lines = [];
        $lines[] = '# Coverage Annotator Report';
        $lines[] = '';
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = '| Metric | Count |';
        $lines[] = '|--------|-------|';
        $lines[] = sprintf('| Total Classes | %d |', $report->totalClasses());
        $lines[] = sprintf('| Fully Covered | %d |', $report->totalFullyCovered());
        $lines[] = sprintf('| Partially Covered | %d |', $report->totalPartiallyCovered());
        $lines[] = sprintf('| Fully Uncovered | %d |', $report->totalUncovered());
        $lines[] = '';

        $uncovered = $report->fullyUncoveredClasses();

        if ($uncovered !== []) {
            $lines[] = '## Fully Uncovered Classes';
            $lines[] = '';
            $lines[] = '| Class | Methods | Line Coverage |';
            $lines[] = '|-------|---------|---------------|';

            foreach ($uncovered as $class) {
                $lines[] = sprintf(
                    '| %s | 0/%d | %s%% |',
                    $class->className,
                    count($class->methods),
                    number_format($class->lineCoveragePercentage(), 1),
                );
            }

            $lines[] = '';
        }

        $partial = $report->partiallyCoveredClasses();

        if ($partial !== []) {
            $lines[] = '## Partially Covered Classes';
            $lines[] = '';
            $lines[] = '| Class | Coverage | Methods | Line Coverage |';
            $lines[] = '|-------|----------|---------|---------------|';

            foreach ($partial as $class) {
                $lines[] = sprintf(
                    '| %s | %s%% | %d/%d | %s%% |',
                    $class->className,
                    number_format($class->coveragePercentage(), 1),
                    count($class->coveredMethods()),
                    count($class->methods),
                    number_format($class->lineCoveragePercentage(), 1),
                );
            }

            $lines[] = '';
        }

        $covered = $report->fullyCoveredClasses();

        if ($covered !== []) {
            $lines[] = '## Fully Covered Classes';
            $lines[] = '';
            $lines[] = '| Class | Methods | Line Coverage |';
            $lines[] = '|-------|---------|---------------|';

            foreach ($covered as $class) {
                $lines[] = sprintf(
                    '| %s | %d/%d | %s%% |',
                    $class->className,
                    count($class->coveredMethods()),
                    count($class->methods),
                    number_format($class->lineCoveragePercentage(), 1),
                );
            }

            $lines[] = '';
        }

        file_put_contents($outputPath, implode("\n", $lines));
    }
}
