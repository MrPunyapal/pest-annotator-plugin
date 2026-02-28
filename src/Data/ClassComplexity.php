<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class ClassComplexity
{
    /**
     * @param  array<string, MethodComplexity>  $methods
     */
    public function __construct(
        public string $className,
        public string $filePath,
        public array $methods,
    ) {}

    public function maxComplexity(): int
    {
        if ($this->methods === []) {
            return 0;
        }

        return max(array_map(
            static fn (MethodComplexity $m): int => $m->cyclomaticComplexity,
            $this->methods,
        ));
    }

    public function averageComplexity(): float
    {
        if ($this->methods === []) {
            return 0.0;
        }

        $total = array_sum(array_map(
            static fn (MethodComplexity $m): int => $m->cyclomaticComplexity,
            $this->methods,
        ));

        return round($total / count($this->methods), 1);
    }

    /**
     * @return array<string, MethodComplexity>
     */
    public function highRiskMethods(): array
    {
        return array_filter(
            $this->methods,
            static fn (MethodComplexity $m): bool => $m->isHighRisk(),
        );
    }

    public function totalComplexity(): int
    {
        return array_sum(array_map(
            static fn (MethodComplexity $m): int => $m->cyclomaticComplexity,
            $this->methods,
        ));
    }
}
