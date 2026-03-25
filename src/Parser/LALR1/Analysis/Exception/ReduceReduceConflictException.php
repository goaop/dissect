<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis\Exception;

use Dissect\Parser\LALR1\Analysis\Automaton;
use Dissect\Parser\Rule;

/**
 * Thrown when a grammar is not LALR(1) and exhibits
 * a reduce/reduce conflict.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class ReduceReduceConflictException extends ConflictException
{
    /**
     * The exception message template.
     */
    public const MESSAGE = <<<EOT
The grammar exhibits a reduce/reduce conflict on rules:

  %d. %s -> %s

vs:

  %d. %s -> %s

(on lookahead "%s" in state %d). Restructure your grammar or choose a conflict resolution mode.
EOT;

    public function __construct(
        int $state,
        protected readonly Rule $firstRule,
        protected readonly Rule $secondRule,
        protected readonly string $lookahead,
        Automaton $automaton
    ) {
        $components1 = $firstRule->getComponents();
        $components2 = $secondRule->getComponents();

        parent::__construct(
            sprintf(
                self::MESSAGE,
                $firstRule->getNumber(),
                $firstRule->getName(),
                empty($components1) ? '/* empty */' : implode(' ', $components1),
                $secondRule->getNumber(),
                $secondRule->getName(),
                empty($components2) ? '/* empty */' : implode(' ', $components2),
                $lookahead,
                $state
            ),
            $state,
            $automaton
        );
    }

    /**
     * Returns the first conflicting rule.
     */
    public function getFirstRule(): Rule
    {
        return $this->firstRule;
    }

    /**
     * Returns the second conflicting rule.
     */
    public function getSecondRule(): Rule
    {
        return $this->secondRule;
    }

    /**
     * Returns the conflicting lookahead.
     */
    public function getLookahead(): string
    {
        return $this->lookahead;
    }
}
