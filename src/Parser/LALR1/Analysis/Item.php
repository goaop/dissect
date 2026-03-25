<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

use Dissect\Parser\Rule;

/**
 * A LALR(1) item.
 *
 * An item represents a state where a part of
 * a grammar rule has been recognized. The current
 * position is marked by a dot:
 *
 * <pre>
 * A -> a . b c
 * </pre>
 *
 * This means that within this item, a has been recognized
 * and b is expected. If the dot is at the very end of the
 * rule:
 *
 * <pre>
 * A -> a b c .
 * </pre>
 *
 * it means that the whole rule has been recognized and
 * can be reduced.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\LALR1\Analysis\ItemTest
 */
class Item
{
    /** @var string[] */
    protected array $lookahead = [];

    /** @var Item[] */
    protected array $connected = [];

    public function __construct(
        protected readonly Rule $rule,
        protected readonly int $dotIndex
    ) {}

    /**
     * Returns the dot index of this item.
     */
    public function getDotIndex(): int
    {
        return $this->dotIndex;
    }

    /**
     * Returns the currently expected component.
     *
     * If the item is:
     *
     * <pre>
     * A -> a . b c
     * </pre>
     *
     * then this method returns the component "b".
     * Only valid when !isReduceItem().
     */
    public function getActiveComponent(): string
    {
        return $this->rule->getComponent($this->dotIndex)
            ?? throw new \LogicException('No active component: item is a reduce item.');
    }

    /**
     * Returns the rule of this item.
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * Determines whether this item is a reduce item.
     *
     * An item is a reduce item if the dot is at the very end:
     *
     * <pre>
     * A -> a b c .
     * </pre>
     */
    public function isReduceItem(): bool
    {
        return $this->dotIndex === count($this->rule->getComponents());
    }

    /**
     * Connects two items with a lookahead pumping channel.
     */
    public function connect(Item $i): void
    {
        $this->connected[] = $i;
    }

    /**
     * Pumps a lookahead token to this item and all items connected to it.
     */
    public function pump(string $lookahead): void
    {
        if (!in_array($lookahead, $this->lookahead)) {
            $this->lookahead[] = $lookahead;

            foreach ($this->connected as $item) {
                $item->pump($lookahead);
            }
        }
    }

    /**
     * Pumps several lookahead tokens.
     *
     * @param string[] $lookahead The lookahead tokens.
     */
    public function pumpAll(array $lookahead): void
    {
        foreach ($lookahead as $l) {
            $this->pump($l);
        }
    }

    /**
     * Returns the computed lookahead for this item.
     *
     * @return string[] The lookahead symbols.
     */
    public function getLookahead(): array
    {
        return $this->lookahead;
    }

    /**
     * Returns all components that haven't been recognized so far.
     *
     * @return string[] The unrecognized components.
     */
    public function getUnrecognizedComponents(): array
    {
        return array_slice($this->rule->getComponents(), $this->dotIndex + 1);
    }
}
