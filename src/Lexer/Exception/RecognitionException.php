<?php

declare(strict_types=1);

namespace Dissect\Lexer\Exception;

use RuntimeException;

/**
 * Thrown when a lexer is unable to extract another token.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
class RecognitionException extends RuntimeException
{
    public function __construct(protected readonly int $sourceLine)
    {
        parent::__construct(sprintf("Cannot extract another token at line %d.", $sourceLine));
    }

    /**
     * Returns the source line number where the exception occurred.
     */
    public function getSourceLine(): int
    {
        return $this->sourceLine;
    }
}
