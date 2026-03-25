<?php

declare(strict_types=1);

namespace Dissect\Parser;

/**
 * Represents a rule in a context-free grammar.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\RuleTest
 */
class Rule
{
    /**
     * @var callable|null
     */
    protected $callback = null;

    protected ?int $precedence = null;

    /**
     * @param string[] $components The components of this rule.
     */
    public function __construct(
        protected readonly int $number,
        protected readonly string $name,
        protected readonly array $components
    ) {}

    /**
     * Returns the number of this rule.
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Returns the name of this rule.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the components of this rule.
     *
     * @return string[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Returns a component at index $index or null if index is out of range.
     */
    public function getComponent(int $index): ?string
    {
        return $this->components[$index] ?? null;
    }

    /**
     * Sets the callback (the semantic value) of the rule.
     *
     * @param callable $callback The callback.
     */
    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    public function getPrecedence(): ?int
    {
        return $this->precedence;
    }

    public function setPrecedence(int $i): void
    {
        $this->precedence = $i;
    }
}
