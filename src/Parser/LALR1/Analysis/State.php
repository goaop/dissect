<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

/**
 * A state in a handle-finding FSA.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\LALR1\Analysis\StateTest
 */
class State
{
    /** @var Item[] */
    protected array $items = [];

    /** @var array<int, array<int, Item>> */
    protected array $itemMap = [];

    /**
     * @param Item[] $items
     */
    public function __construct(protected readonly int $number, array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Adds a new item to this state.
     */
    public function add(Item $item): void
    {
        $this->items[] = $item;

        $this->itemMap[$item->getRule()->getNumber()][$item->getDotIndex()] = $item;
    }

    /**
     * Returns an item by its rule number and dot index.
     */
    public function get(int $ruleNumber, int $dotIndex): Item
    {
        return $this->itemMap[$ruleNumber][$dotIndex];
    }

    /**
     * Returns the number identifying this state.
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Returns an array of items constituting this state.
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
