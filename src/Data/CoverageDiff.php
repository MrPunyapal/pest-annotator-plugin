<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class CoverageDiff
{
    /**
     * @param  array<string, array{from: float, to: float}>  $regressedClasses
     * @param  array<string, array{from: float, to: float}>  $improvedClasses
     * @param  array<string, float>  $newClasses
     * @param  array<string, float>  $removedClasses
     */
    public function __construct(
        public array $regressedClasses,
        public array $improvedClasses,
        public array $newClasses,
        public array $removedClasses,
    ) {}

    public function hasChanges(): bool
    {
        return $this->regressedClasses !== []
            || $this->improvedClasses !== []
            || $this->newClasses !== []
            || $this->removedClasses !== [];
    }

    public function totalRegressions(): int
    {
        return count($this->regressedClasses);
    }

    public function totalImprovements(): int
    {
        return count($this->improvedClasses);
    }

    public function totalNew(): int
    {
        return count($this->newClasses);
    }

    public function totalRemoved(): int
    {
        return count($this->removedClasses);
    }
}
