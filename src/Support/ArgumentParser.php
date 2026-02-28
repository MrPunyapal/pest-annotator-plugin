<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use Closure;

final class ArgumentParser
{
    private bool $coverageEnabled = false;

    private bool $annotateEnabled = false;

    private bool $showMethods = false;

    private bool $showCovered = false;

    private bool $showTypes = false;

    private bool $showComplexity = false;

    private bool $showMutations = false;

    private bool $showDiff = false;

    private bool $saveBaseline = false;

    private ?int $minCoverage = null;

    private ?string $namespaceFilter = null;

    private ?string $namespaceExclude = null;

    private ?string $exportFormat = null;

    private ?string $exportOutput = null;

    /**
     * Parses CLI arguments and returns the filtered argument list.
     *
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    public function parse(array $arguments): array
    {
        $arguments = $this->detectFlag('--coverage', $arguments, consumed: false, callback: fn (): true => $this->coverageEnabled = true);
        $arguments = $this->detectFlag('--annotate', $arguments, consumed: true, callback: fn (): true => $this->annotateEnabled = true);
        $arguments = $this->detectFlag('--annotate-methods', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showMethods = true;
        });
        $arguments = $this->detectFlag('--annotate-covered', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showCovered = true;
        });
        $arguments = $this->detectFlag('--annotate-types', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showTypes = true;
        });
        $arguments = $this->detectFlag('--annotate-complexity', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showComplexity = true;
        });
        $arguments = $this->detectFlag('--annotate-mutations', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showMutations = true;
        });
        $arguments = $this->detectFlag('--annotate-diff', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->showDiff = true;
        });
        $arguments = $this->detectFlag('--annotate-save-baseline', $arguments, consumed: true, callback: function (): void {
            $this->annotateEnabled = true;
            $this->saveBaseline = true;
        });

        $arguments = $this->extractValueFlag('--annotate-min', $arguments, callback: function (string $value): void {
            $this->annotateEnabled = true;
            $this->minCoverage = (int) $value;
        });
        $arguments = $this->extractValueFlag('--annotate-namespace', $arguments, callback: function (string $value): void {
            $this->annotateEnabled = true;
            $this->namespaceFilter = $value;
        });
        $arguments = $this->extractValueFlag('--annotate-exclude', $arguments, callback: function (string $value): void {
            $this->annotateEnabled = true;
            $this->namespaceExclude = $value;
        });
        $arguments = $this->extractValueFlag('--annotate-format', $arguments, callback: function (string $value): void {
            $this->annotateEnabled = true;
            $this->exportFormat = $value;
        });
        $arguments = $this->extractValueFlag('--annotate-output', $arguments, callback: function (string $value): void {
            $this->exportOutput = $value;
        });

        return array_values($arguments);
    }

    public function isCoverageEnabled(): bool
    {
        return $this->coverageEnabled;
    }

    public function isAnnotateEnabled(): bool
    {
        return $this->annotateEnabled;
    }

    public function shouldShowMethods(): bool
    {
        return $this->showMethods;
    }

    public function shouldShowCovered(): bool
    {
        return $this->showCovered;
    }

    public function shouldShowTypes(): bool
    {
        return $this->showTypes;
    }

    public function shouldShowComplexity(): bool
    {
        return $this->showComplexity;
    }

    public function shouldShowMutations(): bool
    {
        return $this->showMutations;
    }

    public function shouldShowDiff(): bool
    {
        return $this->showDiff;
    }

    public function shouldSaveBaseline(): bool
    {
        return $this->saveBaseline;
    }

    public function getMinCoverage(): ?int
    {
        return $this->minCoverage;
    }

    public function getNamespaceFilter(): ?string
    {
        return $this->namespaceFilter;
    }

    public function getNamespaceExclude(): ?string
    {
        return $this->namespaceExclude;
    }

    public function getExportFormat(): ?string
    {
        return $this->exportFormat;
    }

    public function getExportOutput(): ?string
    {
        return $this->exportOutput;
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    private function detectFlag(string $flag, array $arguments, bool $consumed, Closure $callback): array
    {
        if (! in_array($flag, $arguments, true)) {
            return $arguments;
        }

        $callback();

        if ($consumed) {
            return array_values(array_filter(
                $arguments,
                static fn (string $arg): bool => $arg !== $flag,
            ));
        }

        return $arguments;
    }

    /**
     * Extracts a value-based flag (e.g., --annotate-min=80).
     *
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    private function extractValueFlag(string $prefix, array $arguments, Closure $callback): array
    {
        $filtered = [];

        foreach ($arguments as $arg) {
            if (str_starts_with($arg, $prefix.'=')) {
                $value = substr($arg, strlen($prefix) + 1);
                $callback($value);

                continue;
            }

            $filtered[] = $arg;
        }

        return $filtered;
    }
}
