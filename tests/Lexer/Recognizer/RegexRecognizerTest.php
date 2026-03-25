<?php

declare(strict_types=1);

namespace Dissect\Lexer\Recognizer;

use PHPUnit\Framework\TestCase;

class RegexRecognizerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizerShouldMatchAndReturnTheMatchedValue(): void
    {
        $recognizer = new RegexRecognizer('/[a-z]+/');
        $value = $recognizer->match('lorem ipsum');

        $this->assertNotNull($value);
        $this->assertSame('lorem', $value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizerShouldFailAndReturnNull(): void
    {
        $recognizer = new RegexRecognizer('/[a-z]+/');
        $value = $recognizer->match('123 456');

        $this->assertNull($value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizerShouldFailIfTheMatchIsNotAtTheBeginningOfTheString(): void
    {
        $recognizer = new RegexRecognizer('/[a-z]+/');
        $value = $recognizer->match('234 class');

        $this->assertNull($value);
    }
}
