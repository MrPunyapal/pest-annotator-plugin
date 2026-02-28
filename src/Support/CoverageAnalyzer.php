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
            $namespace = is_array($classData) ? $classData['namespace'] : $classData->namespace;
            $methods = is_array($classData) ? $classData['methods'] : $classData->methods;

            $fqcn = $this->resolveClassName((string) $className, $namespace);
            $extractedMethods = $this->extractMethods($methods);

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
            $namespace = is_array($traitData) ? $traitData['namespace'] : $traitData->namespace;
            $methods = is_array($traitData) ? $traitData['methods'] : $traitData->methods;

            $fqcn = $this->resolveClassName((string) $traitName, $namespace);
            $extractedMethods = $this->extractMethods($methods);

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
     * @param  array<string, array{executableLines: int, executedLines: int}|object{executableLines: int, executedLines: int}>  $methods
     * @return array<string, bool>
     */
    private function extractMethods(array $methods): array
    {
        $result = [];

        foreach ($methods as $methodName => $methodData) {
            $executableLines = is_array($methodData) ? $methodData['executableLines'] : $methodData->executableLines;

            if ($executableLines === 0) {
                continue;
            }

            $executedLines = is_array($methodData) ? $methodData['executedLines'] : $methodData->executedLines;
            $result[$methodName] = $executedLines > 0;
        }

        return $result;
    }
}
