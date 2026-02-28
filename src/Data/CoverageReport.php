<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class CoverageReport
{
    /** @param array<string, ClassCoverage> $classes keyed by fully-qualified class name */
    public function __construct(
        public array $classes,
    ) {}

    /** @return array<string, ClassCoverage> */
    public function fullyCoveredClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => $c->isFullyCovered(),
        );
    }

    /** @return array<string, ClassCoverage> */
    public function fullyUncoveredClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => $c->isFullyUncovered(),
        );
    }

    /** @return array<string, ClassCoverage> */
    public function partiallyCoveredClasses(): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => ! $c->isFullyCovered() && ! $c->isFullyUncovered(),
        );
    }

    public function totalClasses(): int
    {
        return count($this->classes);
    }

    public function totalFullyCovered(): int
    {
        return count($this->fullyCoveredClasses());
    }

    public function totalUncovered(): int
    {
        return count($this->fullyUncoveredClasses());
    }

    public function totalPartiallyCovered(): int
    {
        return count($this->partiallyCoveredClasses());
    }

    /** Filters to classes whose FQCN starts with the given namespace prefix. */
    public function filterByNamespace(string $prefix): self
    {
        return new self(array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => str_starts_with($c->className, $prefix),
        ));
    }

    /** Excludes classes whose FQCN starts with the given namespace prefix. */
    public function excludeNamespace(string $prefix): self
    {
        return new self(array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => ! str_starts_with($c->className, $prefix),
        ));
    }

    /**
     * Returns classes that fall below the given coverage threshold.
     *
     * @return array<string, ClassCoverage>
     */
    public function classesBelowThreshold(float $threshold): array
    {
        return array_filter(
            $this->classes,
            static fn (ClassCoverage $c): bool => $c->coveragePercentage() < $threshold,
        );
    }
}
