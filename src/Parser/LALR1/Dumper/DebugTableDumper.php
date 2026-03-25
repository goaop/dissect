<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Dumper;

use Dissect\Parser\Grammar;

/**
 * Dumps a parse table using the debug format,
 * with comments explaining the actions of the
 * parser.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\LALR1\Dumper\DebugTableDumperTest
 */
class DebugTableDumper implements TableDumper
{
    protected StringWriter $writer;

    protected bool $written = false;

    public function __construct(protected readonly Grammar $grammar)
    {
        $this->writer = new StringWriter();
    }

    /**
     * {@inheritDoc}
     *
     * @param array{action: array<int, array<string, int>>, goto: array<int, array<string, int>>} $table
     */
    public function dump(array $table): string
    {
        // for readability
        ksort($table['action']);
        ksort($table['goto']);

        // the grammar dictates the parse table,
        // therefore the result is always the same
        if (!$this->written) {
            $this->writeHeader();
            $this->writer->indent();

            foreach ($table['action'] as $n => $state) {
                $this->writeState($n, $state);
                $this->writer->writeLine();
            }

            $this->writer->outdent();
            $this->writeMiddle();
            $this->writer->indent();

            foreach ($table['goto'] as $n => $map) {
                $this->writeGoto($n, $map);
                $this->writer->writeLine();
            }

            $this->writer->outdent();
            $this->writeFooter();

            $this->written = true;
        }

        return $this->writer->get();
    }

    protected function writeHeader(): void
    {
        $this->writer->writeLine('<?php');
        $this->writer->writeLine();
        $this->writer->writeLine('return [');
        $this->writer->indent();
        $this->writer->writeLine("'action' => [");
    }

    /**
     * @param array<string, int> $state
     */
    protected function writeState(int $n, array $state): void
    {
        $this->writer->writeLine($n . ' => [');
        $this->writer->indent();

        foreach ($state as $trigger => $action) {
            $this->writeAction($trigger, $action);
            $this->writer->writeLine();
        }

        $this->writer->outdent();
        $this->writer->writeLine('],');
    }

    protected function writeAction(string $trigger, int $action): void
    {
        if ($action > 0) {
            $this->writer->writeLine(sprintf(
                '// on %s shift and go to state %d',
                $trigger,
                $action
            ));
        } elseif ($action < 0) {
            $rule = $this->grammar->getRule(-$action);
            $components = $rule->getComponents();

            if (empty($components)) {
                $rhs = '/* empty */';
            } else {
                $rhs = implode(' ', $components);
            }

            $this->writer->writeLine(sprintf(
                '// on %s reduce by rule %s -> %s',
                $trigger,
                $rule->getName(),
                $rhs
            ));
        } else {
            $this->writer->writeLine(sprintf(
                '// on %s accept the input',
                $trigger
            ));
        }

        $this->writer->writeLine(sprintf(
            "'%s' => %d,",
            $trigger,
            $action
        ));
    }

    protected function writeMiddle(): void
    {
        $this->writer->writeLine('],');
        $this->writer->writeLine();
        $this->writer->writeLine("'goto' => [");
    }

    /**
     * @param array<string, int> $map
     */
    protected function writeGoto(int $n, array $map): void
    {
        $this->writer->writeLine($n . ' => [');
        $this->writer->indent();

        foreach ($map as $sym => $dest) {
            $this->writer->writeLine(sprintf(
                '// on %s go to state %d',
                $sym,
                $dest
            ));

            $this->writer->writeLine(sprintf(
                "'%s' => %d,",
                $sym,
                $dest
            ));

            $this->writer->writeLine();
        }

        $this->writer->outdent();
        $this->writer->writeLine('],');
    }

    protected function writeFooter(): void
    {
        $this->writer->writeLine('],');
        $this->writer->outdent();
        $this->writer->writeLine('];');
    }
}
