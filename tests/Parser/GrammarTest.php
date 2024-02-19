<?php

declare(strict_types=1);

namespace Dissect\Parser;

use PHPUnit\Framework\TestCase;

class GrammarTest extends TestCase
{
    protected ExampleGrammar $grammar;

    protected function setUp(): void
    {
        $this->grammar = new ExampleGrammar();
    }

    /**
     * @test
     */
    public function ruleAlternativesShouldHaveTheSameName(): void
    {
        $rules = $this->grammar->getRules();

        $this->assertEquals('Foo', $rules[1]->getName());
        $this->assertEquals('Foo', $rules[2]->getName());
    }

    /**
     * @test
     */
    public function theGrammarShouldBeAugmentedWithAStartRule(): void
    {
        $this->assertEquals(
            Grammar::START_RULE_NAME,
            $this->grammar->getStartRule()->getName()
        );

        $this->assertEquals(
            array('Foo'),
            $this->grammar->getStartRule()->getComponents()
        );
    }

    /**
     * @test
     */
    public function shouldReturnAlternativesGroupedByName(): void
    {
        $rules = $this->grammar->getGroupedRules();
        $this->assertCount(2, $rules['Foo']);
    }

    /**
     * @test
     */
    public function nonterminalsShouldBeDetectedFromRuleNames(): void
    {
        $this->assertTrue($this->grammar->hasNonterminal('Foo'));
    }
}
