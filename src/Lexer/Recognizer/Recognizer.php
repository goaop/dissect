<?php

declare(strict_types=1);

namespace Dissect\Lexer\Recognizer;

/**
 * Recognizers are used by the lexer to process
 * the input string.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
interface Recognizer
{
    /**
     * Attempts to match the beginning of the string.
     * Returns the matched value on success, or null on failure.
     *
     * @param string $string The string to match.
     *
     * @return string|null The matched value, or null if no match.
     */
    public function match(string $string): ?string;
}
