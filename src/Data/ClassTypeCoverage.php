<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class ClassTypeCoverage
{
    /**
     * @param  array<int, MissingTypeInfo>  $missingTypes
     */
    public function __construct(
        public string $className,
        public string $filePath,
        public int $totalDeclarations,
        public int $typedDeclarations,
        public array $missingTypes,
    ) {}

    public function isFullyTyped(): bool
    {
        return $this->missingTypes === [];
    }

    public function coveragePercentage(): float
    {
        if ($this->totalDeclarations === 0) {
            return 100.0;
        }

        return round(($this->typedDeclarations / $this->totalDeclarations) * 100, 1);
    }

    public function missingCount(): int
    {
        return count($this->missingTypes);
    }

    /**
     * @return array<int, MissingTypeInfo>
     */
    public function missingReturnTypes(): array
    {
        return array_values(array_filter(
            $this->missingTypes,
            static fn (MissingTypeInfo $info): bool => $info->kind === 'return',
        ));
    }

    /**
     * @return array<int, MissingTypeInfo>
     */
    public function missingParamTypes(): array
    {
        return array_values(array_filter(
            $this->missingTypes,
            static fn (MissingTypeInfo $info): bool => $info->kind === 'param',
        ));
    }

    /**
     * @return array<int, MissingTypeInfo>
     */
    public function missingPropertyTypes(): array
    {
        return array_values(array_filter(
            $this->missingTypes,
            static fn (MissingTypeInfo $info): bool => $info->kind === 'property',
        ));
    }
}
