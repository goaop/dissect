<?php

declare(strict_types=1);

namespace Dissect\Lexer;

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
    protected array $skipTokens = [];

    /**
     * @var SimpleRecognizer[]
     */
    protected array $recognizers = [];

    /**
     * Adds a new token definition. If given only one argument,
     * it assumes that the token type and recognized value are
     * identical.
     *
     * @param string $type The token type.
     * @param string|null $value The value to be recognized.
     */
    public function token(string $type, ?string $value = null): self
    {
        if ($value) {
            $this->recognizers[$type] = new SimpleRecognizer($value);
        } else {
            $this->recognizers[$type] = new SimpleRecognizer($type);
        }

        return $this;
    }

    /**
     * Adds a new regex token definition.
     *
     * @param string $type The token type.
     * @param string $regex The regular expression used to match the token.
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
        $this->skipTokens = $types;

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
        $value = $type = null;

        foreach ($this->recognizers as $t => $recognizer) {
            if ($recognizer->match($string, $v)) {
                if ($value === null || Util::stringLength($v) > Util::stringLength($value)) {
                    $value = $v;
                    $type = $t;
                }
            }
        }

        if ($type !== null) {
            return new CommonToken($type, $value, $this->getCurrentLine());
        }

        return null;
    }
}
