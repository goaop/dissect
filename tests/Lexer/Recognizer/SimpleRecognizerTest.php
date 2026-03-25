<?php

declare(strict_types=1);

namespace Dissect\Lexer\Recognizer;

use PHPUnit\Framework\TestCase;

class SimpleRecognizerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizerShouldMatchAndReturnTheMatchedValue(): void
    {
        $recognizer = new SimpleRecognizer('class');
        $value = $recognizer->match('class lorem ipsum');

        $this->assertNotNull($value);
        $this->assertSame('class', $value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizerShouldFailAndReturnNull(): void
    {
        $recognizer = new SimpleRecognizer('class');
        $value = $recognizer->match('lorem ipsum');

        $this->assertNull($value);
    }
}
