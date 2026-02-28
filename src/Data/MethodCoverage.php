<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class MethodCoverage
{
    public function __construct(
        public string $name,
        public int $startLine,
        public int $endLine,
        public int $executableLines,
        public int $executedLines,
        public string $visibility,
    ) {}

    public function isCovered(): bool
    {
        return $this->executedLines > 0;
    }

    public function coveragePercentage(): float
    {
        if ($this->executableLines === 0) {
            return 100.0;
        }

        return round(($this->executedLines / $this->executableLines) * 100, 1);
    }

    /** Formats as "methodName():L10-25" */
    public function label(): string
    {
        return sprintf('%s():L%d-%d', $this->name, $this->startLine, $this->endLine);
    }
}
