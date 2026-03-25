<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

/**
 * A finite-state automaton for recognizing
 * grammar productions.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\LALR1\Analysis\AutomatonTest
 */
class Automaton
{
    /**
     * @var array<int, State>
     */
    protected array $states = [];

    /**
     * @var array<int, array<string, int>>
     */
    protected array $transitionTable = [];

    /**
     * Adds a new automaton state.
     */
    public function addState(State $state): void
    {
        $this->states[$state->getNumber()] = $state;
    }

    /**
     * Adds a new transition in the FSA.
     *
     * @param int $origin The number of the origin state.
     * @param string $label The symbol that triggers this transition.
     * @param int $dest The destination state number.
     */
    public function addTransition(int $origin, string $label, int $dest): void
    {
        $this->transitionTable[$origin][$label] = $dest;
    }

    /**
     * Returns a state by its number.
     */
    public function getState(int $number): State
    {
        return $this->states[$number];
    }

    /**
     * Does this automaton have a state identified by $number?
     */
    public function hasState(int $number): bool
    {
        return isset($this->states[$number]);
    }

    /**
     * Returns all states in this FSA.
     *
     * @return array<int, State>
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * Returns the transition table for this automaton.
     *
     * @return array<int, array<string, int>>
     */
    public function getTransitionTable(): array
    {
        return $this->transitionTable;
    }
}
