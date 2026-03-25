<?php

declare(strict_types=1);

namespace Dissect\Node;

use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * A basic contract for a node in an AST.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 *
 * @template-extends IteratorAggregate<int|string, Node>
 */
interface Node extends Countable, IteratorAggregate
{
    /**
     * Returns the children of this node.
     *
     * @return array<int|string, Node>
     */
    public function getNodes(): array;

    /**
     * Checks for existence of child node named $name.
     */
    public function hasNode(string $name): bool;

    /**
     * Returns a child node specified by $name.
     *
     * @throws RuntimeException When no child node named $name exists.
     */
    public function getNode(int|string $name): Node;

    /**
     * Sets a child node.
     */
    public function setNode(string $name, Node $child): void;

    /**
     * Removes a child node by name.
     */
    public function removeNode(string $name): void;

    /**
     * Returns all attributes of this node.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * Determines whether this node has an attribute under $key.
     */
    public function hasAttribute(string $key): bool;

    /**
     * Gets an attribute by key.
     *
     * @throws RuntimeException When no attribute exists under $key.
     */
    public function getAttribute(string $key): mixed;

    /**
     * Sets an attribute by key.
     */
    public function setAttribute(string $key, mixed $value): void;

    /**
     * Removes an attribute by key.
     */
    public function removeAttribute(string $key): void;
}
