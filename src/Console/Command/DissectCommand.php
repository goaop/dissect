<?php

declare(strict_types=1);

namespace Dissect\Console\Command;

use Dissect\Parser\LALR1\Analysis\Exception\ConflictException;
use Dissect\Parser\LALR1\Analysis\Analyzer;
use Dissect\Parser\LALR1\Dumper\AutomatonDumper;
use Dissect\Parser\LALR1\Dumper\DebugTableDumper;
use Dissect\Parser\LALR1\Dumper\ProductionTableDumper;
use Dissect\Parser\Grammar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ReflectionClass;

class DissectCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('dissect')
            ->addArgument('grammar-class', InputArgument::REQUIRED, 'The grammar class.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Writes the parse table in the debug format.')
            ->addOption('dfa', 'D', InputOption::VALUE_NONE, 'Exports the LALR(1) DFA as a Graphviz graph.')
            ->addOption('state', 's', InputOption::VALUE_REQUIRED, 'Exports only the specified state instead of the entire DFA.')
            ->addOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'Overrides the default output directory.')
            ->setHelp(<<<EOT
                Analyzes the given grammar and, if successful, exports the parse table to a PHP
                file.

                By default, the output directory is taken to be the one in which the grammar is
                defined. You can change that with the <info>--output-dir</info> option:

                 <info>--output-dir=../some/other/dir</info>

                The parse table is by default written with minimal whitespace to make it compact.
                If you wish to inspect the table manually, you can export it in a readable and
                well-commented way with the <info>--debug</info> option.

                If you wish to inspect the handle-finding automaton for your grammar (perhaps
                to aid with grammar debugging), use the <info>--dfa</info> option. When in use, Dissect
                will create a file with the automaton exported as a Graphviz graph
                in the output directory.

                Additionally, you can use the <info>--state</info> option to export only the specified
                state and any relevant transitions:

                 <info>--dfa --state=5</info>
                EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $grammarClass = $input->getArgument('grammar-class');
        if (!is_string($grammarClass)) {
            $output->writeln('<error>grammar-class argument must be a string</error>');
            return 1;
        }

        $class = strtr($grammarClass, '/', '\\');

        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');

        $output->writeln('<info>Analyzing...</info>');
        $output->writeln('');

        if (!class_exists($class)) {
            $output->writeln([
                $formatter->formatBlock(
                    sprintf('The class "%s" could not be found.', $class),
                    'error',
                    true
                ),
            ]);

            return 1;
        }

        $grammar = new $class();

        if (!$grammar instanceof Grammar) {
            $output->writeln('<error>The specified class must extend Grammar</error>');
            return 1;
        }

        $outputDirOption = $input->getOption('output-dir');
        if (is_string($outputDirOption)) {
            $cwd = getcwd();
            if ($cwd === false) {
                $output->writeln('<error>Cannot determine current working directory</error>');
                return 1;
            }
            $outputDir = rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $outputDirOption;
        } else {
            $refl = new ReflectionClass($class);
            $fileName = $refl->getFileName();
            if ($fileName === false) {
                $output->writeln('<error>Cannot determine grammar file location</error>');
                return 1;
            }
            $outputDir = pathinfo($fileName, PATHINFO_DIRNAME);
        }

        $analyzer = new Analyzer();

        try {
            $result = $analyzer->analyze($grammar);
            $conflicts = $result->getResolvedConflicts();
            $automaton = $result->getAutomaton();
            $table = $result->getParseTable();

            if ($conflicts) {
                foreach ($conflicts as $conflict) {
                    $output->writeln($this->formatConflict($conflict));
                }

                $output->writeln(sprintf(
                    "<info><comment>%d</comment> conflicts in total",
                    count($conflicts)
                ));

                $output->writeln('');
            }

            $output->writeln('<info>Writing the parse table...</info>');

            $fileName = $outputDir . DIRECTORY_SEPARATOR . 'parse_table.php';

            if ($input->getOption('debug')) {
                $tableDumper = new DebugTableDumper($grammar);
            } else {
                $tableDumper = new ProductionTableDumper();
            }

            $code = $tableDumper->dump($table);

            $ret = @file_put_contents($fileName, $code);
            if ($ret === false) {
                $output->writeln('<error>Error writing the parse table</error>');
            } else {
                $output->writeln('<info>Parse table written</info>');
            }
        } catch (ConflictException $e) {
            $output->writeln([
                $formatter->formatBlock(
                    explode("\n", $e->getMessage()),
                    'error',
                    true
                ),
            ]);

            $automaton = $e->getAutomaton();
        }

        if ($input->getOption('dfa')) {
            $output->writeln('');

            $automatonDumper = new AutomatonDumper($automaton);

            $stateOption = $input->getOption('state');
            if ($stateOption === null) {
                $output->writeln('<info>Exporting the DFA...</info>');

                $dot = $automatonDumper->dump();
                $file = 'automaton.dot';
            } else {
                $state = is_string($stateOption) ? (int) $stateOption : 0;

                if (!$automaton->hasState($state)) {
                    $output->writeln([
                        $formatter->formatBlock(
                            sprintf('The automaton has no state #%d', $state),
                            'error',
                            true
                        ),
                    ]);

                    return 1;
                }

                $output->writeln(sprintf(
                    '<info>Exporting the DFA state <comment>%d</comment>...',
                    $state
                ));

                $dot = $automatonDumper->dumpState($state);
                $file = sprintf('state_%d.dot', $state);
            }

            $fileName = $outputDir . DIRECTORY_SEPARATOR . $file;
            $ret = @file_put_contents($fileName, $dot);

            if ($ret === false) {
                $output->writeln('<error>Error writing to the file</error>');
            } else {
                $output->writeln('<info>Successfully exported</info>');
            }
        }

        return 0;
    }

    /**
     * @param array{state: int, lookahead: string, rule?: \Dissect\Parser\Rule, rules?: \Dissect\Parser\Rule[], resolution: int} $conflict
     */
    protected function formatConflict(array $conflict): string
    {
        $type = $conflict['resolution'] === Grammar::SHIFT
            ? 'shift/reduce'
            : 'reduce/reduce';

        return sprintf(
            "<info>Resolved a <comment>%s</comment> conflict in state <comment>%d</comment> on lookahead <comment>%s</comment></info>",
            $type,
            $conflict['state'],
            $conflict['lookahead']
        );
    }
}
