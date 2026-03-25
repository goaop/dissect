<?php

declare(strict_types=1);

namespace Dissect\Lexer;

use Dissect\Lexer\Recognizer\Recognizer;
use Dissect\Lexer\Recognizer\RegexRecognizer;
use Dissect\Lexer\Recognizer\SimpleRecognizer;
use Dissect\Util\Util;
use LogicException;

/**
 * The StatefulLexer works like SimpleLexer,
 * but internally keeps notion of current lexer state.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 * @see \Dissect\Lexer\StatefulLexerTest
 */
class StatefulLexer extends AbstractLexer
{
    /**
     * @var array<string, array<string, Recognizer>>
     */
    protected array $stateRecognizers = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $stateActions = [];

    /**
     * @var array<string, list<mixed>>
     */
    protected array $stateSkipTokens = [];

    /**
     * @var list<string>
     */
    protected array $stateStack = [];

    protected ?string $stateBeingBuilt = null;

    protected ?string $typeBeingBuilt = null;

    /**
     * Signifies that no action should be taken on encountering a token.
     */
    public const NO_ACTION = 0;

    /**
     * Indicates that a state should be popped of the state stack on
     * encountering a token.
     */
    public const POP_STATE = 1;

    /**
     * Adds a new token definition. If given only one argument,
     * it assumes that the token type and recognized value are
     * identical.
     */
    public function token(string $type, ?string $value = null): static
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        if ($value === null) {
            $value = $type;
        }

        $this->stateRecognizers[$this->stateBeingBuilt][$type] = new SimpleRecognizer($value);
        $this->stateActions[$this->stateBeingBuilt][$type] = self::NO_ACTION;

        $this->typeBeingBuilt = $type;

        return $this;
    }

    /**
     * Adds a new regex token definition.
     */
    public function regex(string $type, string $regex): static
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        $this->stateRecognizers[$this->stateBeingBuilt][$type] = new RegexRecognizer($regex);
        $this->stateActions[$this->stateBeingBuilt][$type] = self::NO_ACTION;

        $this->typeBeingBuilt = $type;

        return $this;
    }

    /**
     * Marks the token types given as arguments to be skipped.
     *
     * @param mixed $types Unlimited number of token types.
     */
    public function skip(mixed ...$types): static
    {
        if ($this->stateBeingBuilt === null) {
            throw new LogicException("Define a lexer state first.");
        }

        $this->stateSkipTokens[$this->stateBeingBuilt] = array_values($types);

        return $this;
    }

    /**
     * Registers a new lexer state.
     */
    public function state(string $state): static
    {
        $this->stateBeingBuilt = $state;

        $this->stateRecognizers[$state] = [];
        $this->stateActions[$state] = [];
        $this->stateSkipTokens[$state] = [];

        return $this;
    }

    /**
     * Sets the starting state for the lexer.
     */
    public function start(string $state): static
    {
        $this->stateStack[] = $state;

        return $this;
    }

    /**
     * Sets an action for the token type that is currently being built.
     *
     * @param mixed $action The action to take.
     */
    public function action(mixed $action): static
    {
        if ($this->stateBeingBuilt === null || $this->typeBeingBuilt === null) {
            throw new LogicException("Define a lexer state and type first.");
        }

        $this->stateActions[$this->stateBeingBuilt][$this->typeBeingBuilt] = $action;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldSkipToken(Token $token): bool
    {
        $currentState = $this->stateStack[count($this->stateStack) - 1];

        return in_array($token->getType(), $this->stateSkipTokens[$currentState]);
    }

    /**
     * {@inheritDoc}
     */
    protected function extractToken(string $string): ?Token
    {
        if (empty($this->stateStack)) {
            throw new LogicException("You must set a starting state before lexing.");
        }

        $currentState = $this->stateStack[count($this->stateStack) - 1];
        $recognizers = $this->stateRecognizers[$currentState];
        $actions = $this->stateActions[$currentState];

        $value = null;
        $type = null;
        $action = null;

        foreach ($recognizers as $t => $recognizer) {
            $v = $recognizer->match($string);
            if ($v !== null) {
                if ($value === null || Util::stringLength($v) > Util::stringLength($value)) {
                    $value = $v;
                    $type = $t;
                    $action = $actions[$type];
                }
            }
        }

        if ($type !== null && $value !== null) {
            if (is_string($action)) { // enter new state
                $this->stateStack[] = $action;
            } elseif ($action === self::POP_STATE) {
                array_pop($this->stateStack);
            }

            return new CommonToken($type, $value, $this->getCurrentLine());
        }

        return null;
    }
}
