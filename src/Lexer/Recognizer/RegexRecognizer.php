<?php

declare(strict_types=1);

namespace Dissect\Lexer\Recognizer;

/**
 * The RegexRecognizer matches a string using a
 * regular expression.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Lexer\Recognizer\RegexRecognizerTest
 */
class RegexRecognizer implements Recognizer
{
    public function __construct(protected readonly string $regex) {}

    /**
     * {@inheritDoc}
     */
    public function match(string $string): ?string
    {
        $r = preg_match($this->regex, $string, $match, PREG_OFFSET_CAPTURE);

        if ($r === 1 && $match[0][1] === 0) {
            return $match[0][0];
        }

        return null;
    }
}
