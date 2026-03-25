<?php

declare(strict_types=1);

namespace Dissect\Lexer\TokenStream;

use Countable;
use Dissect\Lexer\Token;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * A common contract for all token stream classes.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 *
 * @extends IteratorAggregate<int, Token>
 */
interface TokenStream extends Countable, IteratorAggregate
{
    /**
     * Returns the current position in the stream.
     */
    public function getPosition(): int;

    /**
     * Retrieves the current token.
     *
     * @return Token The current token.
     */
    public function getCurrentToken(): Token;

    /**
     * Returns a look-ahead token. Negative values are allowed
     * and serve as look-behind.
     *
     * @throws OutOfBoundsException If current position + $n is out of range.
     */
    public function lookAhead(int $n): Token;

    /**
     * Returns the token at absolute position $n.
     *
     * @throws OutOfBoundsException If $n is out of range.
     */
    public function get(int $n): Token;

    /**
     * Moves the cursor to the absolute position $n.
     *
     * @throws OutOfBoundsException If $n is out of range.
     */
    public function move(int $n): void;

    /**
     * Moves the cursor by $n, relative to the current position.
     *
     * @throws OutOfBoundsException If current position + $n is out of range.
     */
    public function seek(int $n): void;

    /**
     * Moves the cursor to the next token.
     *
     * @throws OutOfBoundsException If at the end of the stream.
     */
    public function next(): void;
}
