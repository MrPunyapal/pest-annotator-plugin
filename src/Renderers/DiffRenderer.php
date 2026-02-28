<?php

declare(strict_types=1);

namespace PestAnnotator\Renderers;

use PestAnnotator\Data\CoverageDiff;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class DiffRenderer
{
    public function render(CoverageDiff $diff, OutputInterface $output): void
    {
        $output->writeln('  <fg=cyan;options=bold>Coverage Diff (vs Baseline)</>');
        $output->writeln('');

        if (! $diff->hasChanges()) {
            $output->writeln('  <fg=green>No changes detected.</>');
            $output->writeln('');

            return;
        }

        $this->renderRegressions($diff, $output);
        $this->renderImprovements($diff, $output);
        $this->renderNewClasses($diff, $output);
        $this->renderRemovedClasses($diff, $output);
        $this->renderSummary($diff, $output);
    }

    private function renderRegressions(CoverageDiff $diff, OutputInterface $output): void
    {
        if ($diff->regressedClasses === []) {
            return;
        }

        $output->writeln('  <fg=red;options=bold>Regressions</>');
        $output->writeln('');

        foreach ($diff->regressedClasses as $fqcn => $change) {
            $output->writeln(sprintf(
                '  <fg=red>↓</> %s <fg=gray>%s%% → %s%%</>',
                $fqcn,
                number_format($change['from'], 1),
                number_format($change['to'], 1),
            ));
        }

        $output->writeln('');
    }

    private function renderImprovements(CoverageDiff $diff, OutputInterface $output): void
    {
        if ($diff->improvedClasses === []) {
            return;
        }

        $output->writeln('  <fg=green;options=bold>Improvements</>');
        $output->writeln('');

        foreach ($diff->improvedClasses as $fqcn => $change) {
            $output->writeln(sprintf(
                '  <fg=green>↑</> %s <fg=gray>%s%% → %s%%</>',
                $fqcn,
                number_format($change['from'], 1),
                number_format($change['to'], 1),
            ));
        }

        $output->writeln('');
    }

    private function renderNewClasses(CoverageDiff $diff, OutputInterface $output): void
    {
        if ($diff->newClasses === []) {
            return;
        }

        $output->writeln('  <fg=cyan;options=bold>New Classes</>');
        $output->writeln('');

        foreach ($diff->newClasses as $fqcn => $percentage) {
            $output->writeln(sprintf(
                '  <fg=cyan>+</> %s <fg=gray>%s%%</>',
                $fqcn,
                number_format($percentage, 1),
            ));
        }

        $output->writeln('');
    }

    private function renderRemovedClasses(CoverageDiff $diff, OutputInterface $output): void
    {
        if ($diff->removedClasses === []) {
            return;
        }

        $output->writeln('  <fg=gray;options=bold>Removed Classes</>');
        $output->writeln('');

        foreach ($diff->removedClasses as $fqcn => $percentage) {
            $output->writeln(sprintf(
                '  <fg=gray>-</> %s <fg=gray>(%s%%)</>',
                $fqcn,
                number_format($percentage, 1),
            ));
        }

        $output->writeln('');
    }

    private function renderSummary(CoverageDiff $diff, OutputInterface $output): void
    {
        $output->writeln('  <fg=white;options=bold>Diff Summary</>');
        $output->writeln('');
        $output->writeln(sprintf('  Regressions:  <fg=red>%d</>', $diff->totalRegressions()));
        $output->writeln(sprintf('  Improvements: <fg=green>%d</>', $diff->totalImprovements()));
        $output->writeln(sprintf('  New:          <fg=cyan>%d</>', $diff->totalNew()));
        $output->writeln(sprintf('  Removed:      <fg=gray>%d</>', $diff->totalRemoved()));
        $output->writeln('');
    }
}
