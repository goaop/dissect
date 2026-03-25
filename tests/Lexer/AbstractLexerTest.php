<?php

declare(strict_types=1);

namespace Dissect\Lexer;

use Dissect\Lexer\Exception\RecognitionException;
use Dissect\Parser\Parser;
use PHPUnit\Framework\TestCase;

class AbstractLexerTest extends TestCase
{
    protected StubLexer $lexer;

    public function setUp(): void
    {
        $this->lexer = new StubLexer();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lexShouldDelegateToExtractTokenUpdatingTheLineAndOffsetAccordingly(): void
    {
        $stream = $this->lexer->lex("ab\nc");

        $this->assertSame('a', $stream->getCurrentToken()->getValue());
        $this->assertSame(1, $stream->getCurrentToken()->getLine());
        $stream->next();

        $this->assertSame('b', $stream->getCurrentToken()->getValue());
        $this->assertSame(1, $stream->getCurrentToken()->getLine());
        $stream->next();

        $this->assertSame("\n", $stream->getCurrentToken()->getValue());
        $this->assertSame(1, $stream->getCurrentToken()->getLine());
        $stream->next();

        $this->assertSame('c', $stream->getCurrentToken()->getValue());
        $this->assertSame(2, $stream->getCurrentToken()->getLine());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lexShouldAppendAnEofTokenAutomatically(): void
    {
        $stream = $this->lexer->lex("abc");
        $stream->seek(3);

        $this->assertSame(Parser::EOF_TOKEN_TYPE, $stream->getCurrentToken()->getType());
        $this->assertSame(1, $stream->getCurrentToken()->getLine());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lexShouldThrowAnExceptionOnAnUnrecognizableToken(): void
    {
        try {
            $this->lexer->lex("abcd");
            $this->fail('Expected a RecognitionException.');
        } catch (RecognitionException $e) {
            $this->assertSame(1, $e->getSourceLine());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lexShouldNormalizeLineEndingsBeforeLexing(): void
    {
        $stream = $this->lexer->lex("a\r\nb");
        $this->assertSame("\n", $stream->get(1)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lexShouldSkipTokensIfToldToDoSo(): void
    {
        $stream = $this->lexer->lex('aeb');
        $this->assertNotSame('e', $stream->get(1)->getType());
    }
}
