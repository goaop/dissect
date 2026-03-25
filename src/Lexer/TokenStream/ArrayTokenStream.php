<?php

declare(strict_types=1);

namespace Dissect\Lexer\TokenStream;

use ArrayIterator;
use Dissect\Lexer\Token;
use OutOfBoundsException;

/**
 * A simple array based implementation of a token stream.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Lexer\TokenStream\ArrayTokenStreamTest
 */
class ArrayTokenStream implements TokenStream
{
    /**
     * @var Token[]
     */
    protected array $tokens;

    protected int $position = 0;

    /**
     * Constructor.
     */
    public function __construct(Token ...$tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentToken(): Token
    {
        return $this->tokens[$this->position];
    }

    /**
     * {@inheritDoc}
     */
    public function lookAhead(int $n): Token
    {
        if (isset($this->tokens[$this->position + $n])) {
            return $this->tokens[$this->position + $n];
        }

        throw new OutOfBoundsException('Invalid look-ahead.');
    }

    /**
     * {@inheritDoc}
     */
    public function get(int $n): Token
    {
        if (isset($this->tokens[$n])) {
            return $this->tokens[$n];
        }

        throw new OutOfBoundsException('Invalid index.');
    }

    /**
     * {@inheritDoc}
     */
    public function move(int $n): void
    {
        if (!isset($this->tokens[$n])) {
            throw new OutOfBoundsException('Invalid index to move to.');
        }

        $this->position = $n;
    }

    /**
     * {@inheritDoc}
     */
    public function seek(int $n): void
    {
        if (!isset($this->tokens[$this->position + $n])) {
            throw new OutOfBoundsException('Invalid seek.');
        }

        $this->position += $n;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        if (!isset($this->tokens[$this->position + 1])) {
            throw new OutOfBoundsException('Attempting to move beyond the end of the stream.');
        }

        $this->position++;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    /**
     * @return ArrayIterator<int, Token>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->tokens);
    }
}
