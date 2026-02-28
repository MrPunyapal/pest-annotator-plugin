<?php

declare(strict_types=1);

namespace PestCoverageAnnotator\Data;

final readonly class ClassCoverage
{
    /** @param array<string, bool> $methods method name => covered flag */
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

        return ! in_array(false, $this->methods, true);
    }

    public function isFullyUncovered(): bool
    {
        if ($this->methods === []) {
            return false;
        }

        return ! in_array(true, $this->methods, true);
    }

    /** @return array<int, string> */
    public function uncoveredMethods(): array
    {
        return array_keys(array_filter(
            $this->methods,
            static fn (bool $covered): bool => ! $covered,
        ));
    }

    /** @return array<int, string> */
    public function coveredMethods(): array
    {
        return array_keys(array_filter(
            $this->methods,
            static fn (bool $covered): bool => $covered,
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
}
