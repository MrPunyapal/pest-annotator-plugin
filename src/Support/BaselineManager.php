<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\CoverageReport;

final class BaselineManager
{
    private const string BASELINE_FILE = '.pest-annotator-baseline.json';

    public function save(CoverageReport $report): void
    {
        $data = [];

        foreach ($report->classes as $fqcn => $class) {
            $methods = [];

            foreach ($class->methods as $name => $method) {
                $methods[$name] = $method->coveragePercentage();
            }

            $data[$fqcn] = [
                'coverage' => $class->coveragePercentage(),
                'lineCoverage' => $class->lineCoveragePercentage(),
                'methods' => $methods,
            ];
        }

        file_put_contents(
            $this->getPath(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }

    public function exists(): bool
    {
        return file_exists($this->getPath());
    }

    /**
     * Loads baseline data as a simple percentage map.
     *
     * @return array<string, float>
     */
    public function load(): array
    {
        if (! $this->exists()) {
            return [];
        }

        $content = file_get_contents($this->getPath());

        if ($content === false) {
            return [];
        }

        /** @var array<string, array{coverage: float, lineCoverage: float, methods: array<string, float>}> $data */
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return [];
        }

        $result = [];

        foreach ($data as $fqcn => $classData) {
            $result[$fqcn] = $classData['coverage'] ?? 0.0;
        }

        return $result;
    }

    private function getPath(): string
    {
        return getcwd().'/'.self::BASELINE_FILE;
    }
}
