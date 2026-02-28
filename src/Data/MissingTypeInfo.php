<?php

declare(strict_types=1);

namespace PestAnnotator\Data;

final readonly class MissingTypeInfo
{
    public function __construct(
        public string $kind,
        public string $name,
        public int $line,
        public string $context,
    ) {}

    public function label(): string
    {
        return sprintf('%s %s in %s() L%d', $this->kind, $this->name, $this->context, $this->line);
    }
}
