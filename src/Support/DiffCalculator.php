<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\CoverageDiff;
use PestAnnotator\Data\CoverageReport;

final class DiffCalculator
{
    /**
     * Compares baseline coverage percentages with a current coverage report.
     *
     * @param  array<string, float>  $baseline
     */
    public function calculate(array $baseline, CoverageReport $current): CoverageDiff
    {
        $regressed = [];
        $improved = [];
        $new = [];
        $removed = [];

        foreach ($current->classes as $fqcn => $class) {
            $currentPercentage = $class->coveragePercentage();

            if (! isset($baseline[$fqcn])) {
                $new[$fqcn] = $currentPercentage;

                continue;
            }

            $baselinePercentage = $baseline[$fqcn];

            if ($currentPercentage < $baselinePercentage) {
                $regressed[$fqcn] = ['from' => $baselinePercentage, 'to' => $currentPercentage];
            } elseif ($currentPercentage > $baselinePercentage) {
                $improved[$fqcn] = ['from' => $baselinePercentage, 'to' => $currentPercentage];
            }
        }

        foreach ($baseline as $fqcn => $percentage) {
            if (! isset($current->classes[$fqcn])) {
                $removed[$fqcn] = $percentage;
            }
        }

        return new CoverageDiff(
            regressedClasses: $regressed,
            improvedClasses: $improved,
            newClasses: $new,
            removedClasses: $removed,
        );
    }
}
