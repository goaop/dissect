<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis\Exception;

use Dissect\Parser\LALR1\Analysis\Automaton;
use Dissect\Parser\Rule;

/**
 * Thrown when a grammar is not LALR(1) and exhibits
 * a shift/reduce conflict.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class ShiftReduceConflictException extends ConflictException
{
    /**
     * The exception message template.
     */
    public const MESSAGE = <<<EOT
The grammar exhibits a shift/reduce conflict on rule:

  %d. %s -> %s

(on lookahead "%s" in state %d). Restructure your grammar or choose a conflict resolution mode.
EOT;

    public function __construct(
        int $state,
        protected readonly Rule $rule,
        protected readonly string $lookahead,
        Automaton $automaton
    ) {
        $components = $rule->getComponents();

        parent::__construct(
            sprintf(
                self::MESSAGE,
                $rule->getNumber(),
                $rule->getName(),
                empty($components) ? '/* empty */' : implode(' ', $components),
                $lookahead,
                $state
            ),
            $state,
            $automaton
        );
    }

    /**
     * Returns the conflicting rule.
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * Returns the conflicting lookahead.
     */
    public function getLookahead(): string
    {
        return $this->lookahead;
    }
}
