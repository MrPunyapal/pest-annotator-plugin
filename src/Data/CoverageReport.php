<?php

declare(strict_types=1);

namespace PestCoverageAnnotator\Data;

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
}
