<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
use PestAnnotator\Data\MethodCoverage;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\File;

final class CoverageAnalyzer
{
    public function analyze(CodeCoverage $codeCoverage): CoverageReport
    {
        $report = $codeCoverage->getReport();
        $classes = [];

        foreach ($report->getIterator() as $node) {
            if (! $node instanceof File) {
                continue;
            }

            $this->extractClasses($node, $classes);
            $this->extractTraits($node, $classes);
        }

        ksort($classes);

        return new CoverageReport($classes);
    }

    /** @param array<string, ClassCoverage> $classes */
    private function extractClasses(File $file, array &$classes): void
    {
        foreach ($file->classes() as $className => $classData) {
            $fqcn = $this->resolveClassName((string) $className, $classData->namespace);
            $extractedMethods = $this->extractMethods($classData->methods);

            if ($extractedMethods === []) {
                continue;
            }

            $classes[$fqcn] = new ClassCoverage(
                className: $fqcn,
                filePath: $file->pathAsString(),
                methods: $extractedMethods,
            );
        }
    }

    /** @param array<string, ClassCoverage> $classes */
    private function extractTraits(File $file, array &$classes): void
    {
        foreach ($file->traits() as $traitName => $traitData) {
            $fqcn = $this->resolveClassName((string) $traitName, $traitData->namespace);
            $extractedMethods = $this->extractMethods($traitData->methods);

            if ($extractedMethods === []) {
                continue;
            }

            $classes[$fqcn] = new ClassCoverage(
                className: $fqcn,
                filePath: $file->pathAsString(),
                methods: $extractedMethods,
            );
        }
    }

    private function resolveClassName(string $className, string $namespace): string
    {
        if (str_contains($className, '\\')) {
            return $className;
        }

        if ($namespace !== '' && $namespace !== 'global') {
            return $namespace.'\\'.$className;
        }

        return $className;
    }

    /**
     * Extracts method coverage data from processed method types.
     *
     * @param  array<string, object{executableLines: int, executedLines: int, startLine: int, endLine: int, visibility: string}>  $methods
     * @return array<string, MethodCoverage>
     */
    private function extractMethods(array $methods): array
    {
        $result = [];

        foreach ($methods as $methodName => $methodData) {
            if ($methodData->executableLines === 0) {
                continue;
            }

            $result[$methodName] = new MethodCoverage(
                name: $methodName,
                startLine: $methodData->startLine,
                endLine: $methodData->endLine,
                executableLines: $methodData->executableLines,
                executedLines: $methodData->executedLines,
                visibility: $methodData->visibility,
            );
        }

        return $result;
    }
}
