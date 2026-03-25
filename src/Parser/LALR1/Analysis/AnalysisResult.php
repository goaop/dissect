<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

use Dissect\Parser\Rule;

/**
 * The result of a grammar analysis.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 *
 * @phpstan-type ResolvedConflict array{state: int, lookahead: string, rule?: Rule, rules?: array<Rule>, resolution: int}
 */
class AnalysisResult
{
    /**
     * @param array{action: array<int, array<string, int>>, goto: array<int, array<string, int>>} $parseTable The parse table.
     * @param list<ResolvedConflict> $resolvedConflicts An array of conflicts resolved during parse table construction.
     */
    public function __construct(
        protected readonly array $parseTable,
        protected readonly Automaton $automaton,
        protected readonly array $resolvedConflicts
    ) {}

    /**
     * Returns the handle-finding FSA.
     */
    public function getAutomaton(): Automaton
    {
        return $this->automaton;
    }

    /**
     * Returns the resulting parse table.
     *
     * @return array{action: array<int, array<string, int>>, goto: array<int, array<string, int>>}
     */
    public function getParseTable(): array
    {
        return $this->parseTable;
    }

    /**
     * Returns an array of resolved parse table conflicts.
     *
     * @return list<array{state: int, lookahead: string, rule?: Rule, rules?: array<Rule>, resolution: int}>
     */
    public function getResolvedConflicts(): array
    {
        return $this->resolvedConflicts;
    }
}
