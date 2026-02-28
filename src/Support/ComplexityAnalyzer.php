<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\ClassComplexity;
use PestAnnotator\Data\ComplexityReport;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodComplexity;
use SebastianBergmann\Complexity\Calculator;

final class ComplexityAnalyzer
{
    /**
     * Analyzes file complexity and cross-references with coverage data.
     *
     * @param  array<int, string>  $filePaths
     */
    public function analyze(array $filePaths, CoverageReport $coverageReport): ComplexityReport
    {
        $calculator = new Calculator;
        $classes = [];

        foreach ($coverageReport->classes as $fqcn => $classCoverage) {
            if (! in_array($classCoverage->filePath, $filePaths, true)) {
                continue;
            }

            if (! file_exists($classCoverage->filePath)) {
                continue;
            }

            $complexities = $calculator->calculateForSourceFile($classCoverage->filePath);
            $methods = [];

            foreach ($complexities as $complexity) {
                if (! $complexity->isMethod()) {
                    continue;
                }

                $methodName = $this->extractMethodName($complexity->name());
                $coveragePercentage = 0.0;

                if (isset($classCoverage->methods[$methodName])) {
                    $coveragePercentage = $classCoverage->methods[$methodName]->coveragePercentage();
                }

                $methods[$methodName] = new MethodComplexity(
                    name: $methodName,
                    cyclomaticComplexity: $complexity->cyclomaticComplexity(),
                    coveragePercentage: $coveragePercentage,
                );
            }

            if ($methods !== []) {
                $classes[$fqcn] = new ClassComplexity(
                    className: $fqcn,
                    filePath: $classCoverage->filePath,
                    methods: $methods,
                );
            }
        }

        ksort($classes);

        return new ComplexityReport($classes);
    }

    private function extractMethodName(string $fullName): string
    {
        if (str_contains($fullName, '::')) {
            return substr($fullName, strrpos($fullName, '::') + 2);
        }

        return $fullName;
    }
}
