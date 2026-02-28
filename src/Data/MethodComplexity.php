<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class MethodComplexity
{
    public function __construct(
        public string $name,
        public int $cyclomaticComplexity,
        public float $coveragePercentage,
    ) {}

    public function riskScore(): float
    {
        if ($this->coveragePercentage >= 100.0) {
            return 0.0;
        }

        return round($this->cyclomaticComplexity * (1 - ($this->coveragePercentage / 100)), 1);
    }

    public function isHighRisk(): bool
    {
        return $this->cyclomaticComplexity >= 10 && $this->coveragePercentage < 50.0;
    }
}
