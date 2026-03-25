<?php

declare(strict_types=1);

namespace Dissect\Lexer;

use Dissect\Lexer\Recognizer\Recognizer;
use Dissect\Lexer\Recognizer\RegexRecognizer;
use Dissect\Lexer\Recognizer\SimpleRecognizer;
use Dissect\Util\Util;

/**
 * SimpleLexer uses specified recognizers
 * without keeping track of state.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Lexer\SimpleLexerTest
 */
class SimpleLexer extends AbstractLexer
{
    /**
     * @var list<mixed>
     */
    protected array $skipTokens = [];

    /**
     * @var array<string, Recognizer>
     */
    protected array $recognizers = [];

    /**
     * Adds a new token definition. If given only one argument,
     * it assumes that the token type and recognized value are
     * identical.
     */
    public function token(string $type, ?string $value = null): static
    {
        if ($value !== null) {
            $this->recognizers[$type] = new SimpleRecognizer($value);
        } else {
            $this->recognizers[$type] = new SimpleRecognizer($type);
        }

        return $this;
    }

    /**
     * Adds a new regex token definition.
     */
    public function regex(string $type, string $regex): static
    {
        $this->recognizers[$type] = new RegexRecognizer($regex);

        return $this;
    }

    /**
     * Marks the token types given as arguments to be skipped.
     *
     * @param mixed $types Unlimited number of token types.
     */
    public function skip(mixed ...$types): static
    {
        $this->skipTokens = array_values($types);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldSkipToken(Token $token): bool
    {
        return in_array($token->getType(), $this->skipTokens);
    }

    /**
     * {@inheritDoc}
     */
    protected function extractToken(string $string): ?Token
    {
        $value = null;
        $type = null;

        foreach ($this->recognizers as $t => $recognizer) {
            $v = $recognizer->match($string);
            if ($v !== null) {
                if ($value === null || Util::stringLength($v) > Util::stringLength($value)) {
                    $value = $v;
                    $type = $t;
                }
            }
        }

        if ($type !== null && $value !== null) {
            return new CommonToken($type, $value, $this->getCurrentLine());
        }

        return null;
    }
}
