<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;

final readonly class HtmlExporter
{
    public function export(CoverageReport $report, string $outputPath): void
    {
        $html = $this->buildHtml($report);
        file_put_contents($outputPath, $html);
    }

    private function buildHtml(CoverageReport $report): string
    {
        $rows = '';

        foreach ($report->classes as $class) {
            $rows .= $this->buildClassRow($class);
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Coverage Annotator Report</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 2rem; background: #1a1a2e; color: #e0e0e0; }
                h1 { color: #00d4ff; }
                h2 { color: #a0a0a0; margin-top: 2rem; }
                table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
                th, td { border: 1px solid #333; padding: 0.5rem 1rem; text-align: left; }
                th { background: #16213e; color: #00d4ff; }
                tr:nth-child(even) { background: #1a1a2e; }
                tr:nth-child(odd) { background: #16213e; }
                .progress { width: 120px; height: 10px; background: #333; border-radius: 5px; overflow: hidden; display: inline-block; }
                .progress-fill { height: 100%; border-radius: 5px; }
                .green { background: #4caf50; color: #4caf50; }
                .yellow { background: #ff9800; color: #ff9800; }
                .red { background: #f44336; color: #f44336; }
                .summary { display: flex; gap: 2rem; margin: 1rem 0; }
                .summary-card { padding: 1rem 2rem; border-radius: 8px; background: #16213e; border: 1px solid #333; }
                .summary-card h3 { margin: 0; color: #a0a0a0; font-size: 0.9rem; }
                .summary-card .value { font-size: 2rem; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Coverage Annotator Report</h1>

            <div class="summary">
                <div class="summary-card"><h3>Total</h3><div class="value" style="color:#00d4ff">{$report->totalClasses()}</div></div>
                <div class="summary-card"><h3>Covered</h3><div class="value green">{$report->totalFullyCovered()}</div></div>
                <div class="summary-card"><h3>Partial</h3><div class="value yellow">{$report->totalPartiallyCovered()}</div></div>
                <div class="summary-card"><h3>Uncovered</h3><div class="value red">{$report->totalUncovered()}</div></div>
            </div>

            <h2>All Classes</h2>
            <table>
                <thead>
                    <tr><th>Class</th><th>Coverage</th><th>Methods</th><th>Line Coverage</th><th>Progress</th></tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </body>
        </html>
        HTML;
    }

    private function buildClassRow(ClassCoverage $class): string
    {
        $percentage = $class->coveragePercentage();
        $linePercentage = $class->lineCoveragePercentage();
        $color = $this->percentageColorClass($percentage);
        $coveredCount = count($class->coveredMethods());
        $totalCount = count($class->methods);

        return sprintf(
            '<tr><td>%s</td><td class="%s">%s%%</td><td>%d/%d</td><td>%s%%</td><td><div class="progress"><div class="progress-fill %s" style="width:%s%%"></div></div></td></tr>',
            htmlspecialchars($class->className),
            $color,
            number_format($percentage, 1),
            $coveredCount,
            $totalCount,
            number_format($linePercentage, 1),
            $color,
            number_format($percentage, 1),
        );
    }

    private function percentageColorClass(float $percentage): string
    {
        return match (true) {
            $percentage >= 80.0 => 'green',
            $percentage >= 50.0 => 'yellow',
            default => 'red',
        };
    }
}
