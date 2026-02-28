<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class ClassCoverage
{
    /** @param array<string, MethodCoverage> $methods method name => coverage data */
    public function __construct(
        public string $className,
        public string $filePath,
        public array $methods,
    ) {}

    public function isFullyCovered(): bool
    {
        if ($this->methods === []) {
            return true;
        }

        foreach ($this->methods as $method) {
            if (! $method->isCovered()) {
                return false;
            }
        }

        return true;
    }

    public function isFullyUncovered(): bool
    {
        if ($this->methods === []) {
            return false;
        }

        foreach ($this->methods as $method) {
            if ($method->isCovered()) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, MethodCoverage> */
    public function uncoveredMethods(): array
    {
        return array_values(array_filter(
            $this->methods,
            static fn (MethodCoverage $m): bool => ! $m->isCovered(),
        ));
    }

    /** @return array<int, MethodCoverage> */
    public function coveredMethods(): array
    {
        return array_values(array_filter(
            $this->methods,
            static fn (MethodCoverage $m): bool => $m->isCovered(),
        ));
    }

    public function coveragePercentage(): float
    {
        if ($this->methods === []) {
            return 100.0;
        }

        $covered = count($this->coveredMethods());
        $total = count($this->methods);

        return round(($covered / $total) * 100, 1);
    }

    public function lineCoveragePercentage(): float
    {
        $totalExecutable = 0;
        $totalExecuted = 0;

        foreach ($this->methods as $method) {
            $totalExecutable += $method->executableLines;
            $totalExecuted += $method->executedLines;
        }

        if ($totalExecutable === 0) {
            return 100.0;
        }

        return round(($totalExecuted / $totalExecutable) * 100, 1);
    }
}
