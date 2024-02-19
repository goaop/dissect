<?php

namespace Dissect\Lexer\Recognizer;

use PHPUnit\Framework\TestCase;

class SimpleRecognizerTest extends TestCase
{
    /**
     * @test
     */
    public function recognizerShouldMatchAndPassTheValueByReference(): void
    {
        $recognizer = new SimpleRecognizer('class');
        $result = $recognizer->match('class lorem ipsum', $value);

        $this->assertTrue($result);
        $this->assertNotNull($value);
        $this->assertEquals('class', $value);
    }

    /**
     * @test
     */
    public function recognizerShouldFailAndTheValueShouldStayNull(): void
    {
        $recognizer = new SimpleRecognizer('class');
        $result = $recognizer->match('lorem ipsum', $value);

        $this->assertFalse($result);
        $this->assertNull($value);
    }
}
