<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis\KernelSet;

class Node
{
    public ?Node $left = null;
    public ?Node $right = null;

    /**
     * @param int[] $kernel
     */
    public function __construct(
        public readonly array $kernel,
        public readonly int $number
    ) {}
}
