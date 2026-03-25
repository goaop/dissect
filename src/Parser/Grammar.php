<?php

declare(strict_types=1);

namespace Dissect\Parser;

use LogicException;

/**
 * Represents a context-free grammar.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\GrammarTest
 */
class Grammar
{
    /**
     * The name given to the rule the grammar is augmented with
     * when start() is called.
     */
    public const START_RULE_NAME = '$start';

    /**
     * The epsilon symbol signifies an empty production.
     */
    public const EPSILON = '$epsilon';

    /**
     * @var array<int, Rule>
     */
    protected array $rules = [];

    /**
     * @var array<string, list<Rule>>
     */
    protected array $groupedRules = [];

    protected int $nextRuleNumber = 1;

    protected int $conflictsMode = 9; // SHIFT | OPERATORS

    protected ?string $currentNonterminal = null;

    protected ?Rule $currentRule = null;

    /**
     * @var array<string, array{prec: int, assoc: int}>
     */
    protected array $operators = [];

    /**
     * @var list<string>|null
     */
    protected ?array $currentOperators = null;

    /**
     * Signifies that the parser should not resolve any
     * grammar conflicts.
     */
    public const NONE = 0;

    /**
     * Signifies that the parser should resolve
     * shift/reduce conflicts by always shifting.
     */
    public const SHIFT = 1;

    /**
     * Signifies that the parser should resolve
     * reduce/reduce conflicts by reducing with
     * the longer rule.
     */
    public const LONGER_REDUCE = 2;

    /**
     * Signifies that the parser should resolve
     * reduce/reduce conflicts by reducing
     * with the rule that was given earlier in
     * the grammar.
     */
    public const EARLIER_REDUCE = 4;

    /**
     * Signifies that the conflicts should be
     * resolved by taking operator precedence
     * into account.
     */
    public const OPERATORS = 8;

    /**
     * Signifies that the parser should automatically
     * resolve all grammar conflicts.
     */
    public const ALL = 15;

    /**
     * Left operator associativity.
     */
    public const LEFT = 0;

    /**
     * Right operator associativity.
     */
    public const RIGHT = 1;

    /**
     * The operator is nonassociative.
     */
    public const NONASSOC = 2;

    public function __invoke(string $nonterminal): static
    {
        $this->currentNonterminal = $nonterminal;

        return $this;
    }

    /**
     * Defines an alternative for a grammar rule.
     *
     * @param string ...$components The components of the rule.
     */
    public function is(string ...$components): static
    {
        $this->currentOperators = null;

        if ($this->currentNonterminal === null) {
            throw new LogicException(
                'You must specify a name of the rule first.'
            );
        }

        $num = $this->nextRuleNumber++;

        $rule = new Rule($num, $this->currentNonterminal, $components);

        $this->rules[$num] = $this->currentRule = $rule;
        $this->groupedRules[$this->currentNonterminal][] = $rule;

        return $this;
    }

    /**
     * Sets the callback for the current rule.
     *
     * @param callable $callback The callback.
     */
    public function call(callable $callback): static
    {
        if ($this->currentRule === null) {
            throw new LogicException(
                'You must specify a rule first.'
            );
        }

        $this->currentRule->setCallback($callback);

        return $this;
    }

    /**
     * Returns the set of rules of this grammar.
     *
     * @return array<int, Rule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getRule(int $number): Rule
    {
        return $this->rules[$number];
    }

    /**
     * Returns rules grouped by nonterminal name.
     *
     * @return array<string, list<Rule>>
     */
    public function getGroupedRules(): array
    {
        return $this->groupedRules;
    }

    /**
     * Sets a start rule for this grammar.
     *
     * @param string $name The name of the start rule.
     */
    public function start(string $name): void
    {
        $this->rules[0] = new Rule(0, self::START_RULE_NAME, [$name]);
    }

    /**
     * Returns the augmented start rule. For internal use only.
     */
    public function getStartRule(): Rule
    {
        if (!isset($this->rules[0])) {
            throw new LogicException("No start rule specified.");
        }

        return $this->rules[0];
    }

    /**
     * Sets the mode of conflict resolution.
     *
     * @param int $mode The bitmask for the mode.
     */
    public function resolve(int $mode): void
    {
        $this->conflictsMode = $mode;
    }

    /**
     * Returns the conflict resolution mode for this grammar.
     *
     * @return int The bitmask of the resolution mode.
     */
    public function getConflictsMode(): int
    {
        return $this->conflictsMode;
    }

    /**
     * Does a nonterminal $name exist in the grammar?
     */
    public function hasNonterminal(string $name): bool
    {
        return array_key_exists($name, $this->groupedRules);
    }

    /**
     * Defines a group of operators.
     *
     * @param string ...$ops Any number of tokens that serve as the operators.
     */
    public function operators(string ...$ops): static
    {
        $this->currentRule = null;
        $this->currentOperators = array_values($ops);

        foreach ($ops as $op) {
            $this->operators[$op] = [
                'prec' => 1,
                'assoc' => self::LEFT,
            ];
        }

        return $this;
    }

    /**
     * Marks the current group of operators as left-associative.
     */
    public function left(): static
    {
        return $this->assoc(self::LEFT);
    }

    /**
     * Marks the current group of operators as right-associative.
     */
    public function right(): static
    {
        return $this->assoc(self::RIGHT);
    }

    /**
     * Marks the current group of operators as nonassociative.
     */
    public function nonassoc(): static
    {
        return $this->assoc(self::NONASSOC);
    }

    /**
     * Explicitly sets the associativity of the current group of operators.
     *
     * @param int $a One of Grammar::LEFT, Grammar::RIGHT, Grammar::NONASSOC
     */
    public function assoc(int $a): static
    {
        if ($this->currentOperators === null) {
            throw new LogicException('Define a group of operators first.');
        }

        foreach ($this->currentOperators as $op) {
            $this->operators[$op]['assoc'] = $a;
        }

        return $this;
    }

    /**
     * Sets the precedence (as an integer) of the current group of operators.
     * If no group of operators is being specified, sets the precedence
     * of the currently described rule.
     *
     * @param int $i The precedence as an integer.
     */
    public function prec(int $i): static
    {
        if ($this->currentOperators === null) {
            if ($this->currentRule === null) {
                throw new LogicException('Define a group of operators or a rule first.');
            } else {
                $this->currentRule->setPrecedence($i);
            }
        } else {
            foreach ($this->currentOperators as $op) {
                $this->operators[$op]['prec'] = $i;
            }
        }

        return $this;
    }

    /**
     * Is the passed token an operator?
     */
    public function hasOperator(string $token): bool
    {
        return array_key_exists($token, $this->operators);
    }

    /**
     * @return array{prec: int, assoc: int}
     */
    public function getOperatorInfo(string $token): array
    {
        return $this->operators[$token];
    }
}
