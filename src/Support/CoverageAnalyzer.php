<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\ClassCoverage;
use PestAnnotator\Data\CoverageReport;
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
            $fqcn = $this->resolveClassName((string) $className, $classData['namespace']);
            $methods = $this->extractMethods($classData['methods']);

            if ($methods === []) {
                continue;
            }

            $classes[$fqcn] = new ClassCoverage(
                className: $fqcn,
                filePath: $file->pathAsString(),
                methods: $methods,
            );
        }
    }

    /** @param array<string, ClassCoverage> $classes */
    private function extractTraits(File $file, array &$classes): void
    {
        foreach ($file->traits() as $traitName => $traitData) {
            $fqcn = $this->resolveClassName((string) $traitName, $traitData['namespace']);
            $methods = $this->extractMethods($traitData['methods']);

            if ($methods === []) {
                continue;
            }

            $classes[$fqcn] = new ClassCoverage(
                className: $fqcn,
                filePath: $file->pathAsString(),
                methods: $methods,
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
     * @param  array<string, array{executableLines: int, executedLines: int, methodName: string, coverage: int|float, visibility: string, signature: string, startLine: int, endLine: int, executableBranches: int, executedBranches: int, executablePaths: int, executedPaths: int, ccn: int, crap: int|string, link: string}>  $methods
     * @return array<string, bool>
     */
    private function extractMethods(array $methods): array
    {
        $result = [];

        foreach ($methods as $methodName => $methodData) {
            if ($methodData['executableLines'] === 0) {
                continue;
            }

            $result[$methodName] = $methodData['executedLines'] > 0;
        }

        return $result;
    }
}
