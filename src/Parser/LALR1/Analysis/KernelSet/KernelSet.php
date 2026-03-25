<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis\KernelSet;

/**
 * A BST implementation for more efficient lookup
 * of states by their kernel items.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Parser\LALR1\Analysis\KernelSet\KernelSetTest
 */
class KernelSet
{
    protected int $nextNumber = 0;
    protected ?Node $root = null;

    /**
     * Inserts a new node in the BST and returns
     * the number of the new state if no such state
     * exists. Otherwise, returns the number of the
     * existing state.
     *
     * @param array<array{int, int}> $kernel The state kernel.
     *
     * @return int The state number.
     */
    public function insert(array $kernel): int
    {
        $kernel = KernelSet::hashKernel($kernel);

        if ($this->root === null) {
            $this->root = new Node($kernel, $n = $this->nextNumber++);

            return $n;
        }

        $node = $this->root;

        while (true) {
            if ($kernel < $node->kernel) {
                if ($node->left === null) {
                    $node->left = new Node($kernel, $n = $this->nextNumber++);

                    return $n;
                } else {
                    $node = $node->left;
                }
            } elseif ($kernel > $node->kernel) {
                if ($node->right === null) {
                    $node->right = new Node($kernel, $n = $this->nextNumber++);

                    return $n;
                } else {
                    $node = $node->right;
                }
            } else {
                return $node->number;
            }
        }
    }

    /**
     * Hashes a state kernel using a pairing function.
     *
     * @param array<array{int, int}> $kernel The kernel.
     *
     * @return int[] The hashed kernel.
     */
    public static function hashKernel(array $kernel): array
    {
        $kernel = array_map(static function (array $tuple): int {
            [$car, $cdr] = $tuple;

            return (int) (($car + $cdr) * ($car + $cdr + 1) / 2 + $cdr);
        }, $kernel);

        sort($kernel);

        return $kernel;
    }
}
