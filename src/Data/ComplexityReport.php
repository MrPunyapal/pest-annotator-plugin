<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class ComplexityReport
{
    /**
     * @param  array<string, ClassComplexity>  $classes  keyed by FQCN
     */
    public function __construct(
        public array $classes,
    ) {}

    /**
     * @return array<string, MethodComplexity>
     */
    public function highRiskMethods(): array
    {
        $result = [];

        foreach ($this->classes as $className => $class) {
            foreach ($class->highRiskMethods() as $methodName => $method) {
                $result[$className.'::'.$methodName] = $method;
            }
        }

        return $result;
    }

    public function averageComplexity(): float
    {
        if ($this->classes === []) {
            return 0.0;
        }

        $totalMethods = 0;
        $totalComplexity = 0;

        foreach ($this->classes as $class) {
            $totalMethods += count($class->methods);
            $totalComplexity += $class->totalComplexity();
        }

        if ($totalMethods === 0) {
            return 0.0;
        }

        return round($totalComplexity / $totalMethods, 1);
    }

    public function totalClasses(): int
    {
        return count($this->classes);
    }

    public function maxComplexity(): int
    {
        if ($this->classes === []) {
            return 0;
        }

        return max(array_map(
            static fn (ClassComplexity $c): int => $c->maxComplexity(),
            $this->classes,
        ));
    }
}
