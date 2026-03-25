<?php

declare(strict_types=1);

namespace Dissect\Lexer\TokenStream;

use Dissect\Lexer\CommonToken;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class ArrayTokenStreamTest extends TestCase
{
    protected ArrayTokenStream $stream;

    protected function setUp(): void
    {
        $this->stream = new ArrayTokenStream(
            new CommonToken('INT', '6', 1),
            new CommonToken('PLUS', '+', 1),
            new CommonToken('INT', '5', 1),
            new CommonToken('MINUS', '-', 1),
            new CommonToken('INT', '3', 1),
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function theCursorShouldBeOnFirstTokenByDefault(): void
    {
        $this->assertSame('6', $this->stream->getCurrentToken()->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getPositionShouldReturnCurrentPosition(): void
    {
        $this->stream->seek(2);
        $this->stream->next();

        $this->assertSame(3, $this->stream->getPosition());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lookAheadShouldReturnTheCorrectToken(): void
    {
        $this->assertSame('5', $this->stream->lookAhead(2)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lookAheadShouldThrowAnExceptionWhenInvalid(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->stream->lookAhead(15);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getShouldReturnATokenByAbsolutePosition(): void
    {
        $this->assertSame('3', $this->stream->get(4)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getShouldThrowAnExceptionWhenInvalid(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->stream->get(15);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function moveShouldMoveTheCursorByToAnAbsolutePosition(): void
    {
        $this->stream->move(2);
        $this->assertSame('5', $this->stream->getCurrentToken()->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function moveShouldThrowAnExceptionWhenInvalid(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->stream->move(15);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function seekShouldMoveTheCursorByRelativeOffset(): void
    {
        $this->stream->seek(4);
        $this->assertSame('3', $this->stream->getCurrentToken()->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function seekShouldThrowAnExceptionWhenInvalid(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->stream->seek(15);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nextShouldMoveTheCursorOneTokenAhead(): void
    {
        $this->stream->next();
        $this->assertSame('PLUS', $this->stream->getCurrentToken()->getType());

        $this->stream->next();
        $this->assertSame('5', $this->stream->getCurrentToken()->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nextShouldThrowAnExceptionWhenAtTheEndOfTheStream(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->stream->seek(4);
        $this->stream->next();
    }
}
