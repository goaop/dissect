<?php

declare(strict_types=1);

namespace Dissect\Lexer\Recognizer;

/**
 * SimpleRecognizer matches a string by a simple
 * strncmp match.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Lexer\Recognizer\SimpleRecognizerTest
 */
class SimpleRecognizer implements Recognizer
{
    public function __construct(protected readonly string $string) {}

    /**
     * {@inheritDoc}
     */
    public function match(string $string): ?string
    {
        if (strncmp($string, $this->string, strlen($this->string)) === 0) {
            return $this->string;
        }

        return null;
    }
}
