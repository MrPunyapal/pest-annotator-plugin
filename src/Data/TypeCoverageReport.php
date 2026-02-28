<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class TypeCoverageReport
{
    /**
     * @param  array<string, ClassTypeCoverage>  $classes keyed by FQCN
     */
    public function __construct(
        public array $classes,
    ) {}

    /** @return array<string, ClassTypeCoverage> */
    public function fullyTypedClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassTypeCoverage $c): bool => $c->isFullyTyped(),
        );
    }

    /** @return array<string, ClassTypeCoverage> */
    public function partiallyTypedClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassTypeCoverage $c): bool => ! $c->isFullyTyped() && $c->typedDeclarations > 0,
        );
    }

    /** @return array<string, ClassTypeCoverage> */
    public function untypedClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassTypeCoverage $c): bool => $c->typedDeclarations === 0 && $c->totalDeclarations > 0,
        );
    }

    public function totalClasses(): int
    {
        return count($this->classes);
    }

    public function totalFullyTyped(): int
    {
        return count($this->fullyTypedClasses());
    }

    public function totalPartiallyTyped(): int
    {
        return count($this->partiallyTypedClasses());
    }

    public function totalUntyped(): int
    {
        return count($this->untypedClasses());
    }

    public function overallPercentage(): float
    {
        $totalDeclarations = 0;
        $typedDeclarations = 0;

        foreach ($this->classes as $class) {
            $totalDeclarations += $class->totalDeclarations;
            $typedDeclarations += $class->typedDeclarations;
        }

        if ($totalDeclarations === 0) {
            return 100.0;
        }

        return round(($typedDeclarations / $totalDeclarations) * 100, 1);
    }
}
