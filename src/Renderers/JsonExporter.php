<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\CoverageReport;

final readonly class JsonExporter
{
    public function export(CoverageReport $report, string $outputPath): void
    {
        $data = [
            'summary' => [
                'totalClasses' => $report->totalClasses(),
                'fullyCovered' => $report->totalFullyCovered(),
                'partiallyCovered' => $report->totalPartiallyCovered(),
                'fullyUncovered' => $report->totalUncovered(),
            ],
            'classes' => [],
        ];

        foreach ($report->classes as $fqcn => $class) {
            $methods = [];

            foreach ($class->methods as $name => $method) {
                $methods[$name] = [
                    'startLine' => $method->startLine,
                    'endLine' => $method->endLine,
                    'executableLines' => $method->executableLines,
                    'executedLines' => $method->executedLines,
                    'coverage' => $method->coveragePercentage(),
                    'visibility' => $method->visibility,
                    'isCovered' => $method->isCovered(),
                ];
            }

            $data['classes'][$fqcn] = [
                'filePath' => $class->filePath,
                'coverage' => $class->coveragePercentage(),
                'lineCoverage' => $class->lineCoveragePercentage(),
                'isFullyCovered' => $class->isFullyCovered(),
                'isFullyUncovered' => $class->isFullyUncovered(),
                'methods' => $methods,
            ];
        }

        file_put_contents(
            $outputPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}
