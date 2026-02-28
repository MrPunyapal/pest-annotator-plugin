<?php

declare(strict_types=1);

namespace PestCoverageAnnotator\Commands;

use InvalidArgumentException;
use PestCoverageAnnotator\Annotator;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'annotate',
    description: 'Annotate uncovered classes and methods from a Clover coverage XML report.',
)]
final class AnnotateCommand extends Command
{
    public function __construct(
        private readonly Annotator $annotator = new Annotator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'coverage-file',
                InputArgument::REQUIRED,
                'Path to the Clover coverage XML file (e.g. coverage.xml)',
            )
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Directory prefixes to include (default: app/, src/)',
            )
            ->addOption(
                'show-covered',
                null,
                InputOption::VALUE_NONE,
                'Also display fully covered classes in the output',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $coverageFile */
        $coverageFile = $input->getArgument('coverage-file');

        /** @var array<int, string> $includes */
        $includes = $input->getOption('include');

        $includePrefixes = $includes !== [] ? $includes : ['app/', 'src/'];

        $showCovered = (bool) $input->getOption('show-covered');

        $output->writeln('');
        $output->writeln('<fg=cyan;options=bold>🔍 Pest Coverage Annotator</>');
        $output->writeln(sprintf('<fg=gray>   Parsing: %s</>', $coverageFile));

        try {
            $report = $this->annotator->annotate(
                coveragePath: $coverageFile,
                output: $output,
                includePrefixes: $includePrefixes,
                showCovered: $showCovered,
            );
        } catch (InvalidArgumentException|RuntimeException $e) {
            $output->writeln('');
            $output->writeln(sprintf('<error> ERROR </error> <fg=red>%s</>', $e->getMessage()));
            $output->writeln('');

            return Command::FAILURE;
        }

        if ($report->totalUncovered() > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
