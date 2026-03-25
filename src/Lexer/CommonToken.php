<?php

declare(strict_types=1);

namespace Dissect\Lexer;

/**
 * A simple token representation.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class CommonToken implements Token
{
    public function __construct(
        protected readonly string $type,
        protected readonly int|string $value,
        protected readonly int $line
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): int|string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getLine(): int
    {
        return $this->line;
    }
}
