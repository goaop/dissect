<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

use PHPUnit\Framework\TestCase;

class AutomatonTest extends TestCase
{
    protected Automaton $automaton;

    protected function setUp(): void
    {
        $this->automaton = new Automaton();
        $this->automaton->addState(new State(0, []));
        $this->automaton->addState(new State(1, []));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addingATransitionShouldBeVisibleInTheTransitionTable(): void
    {
        $this->automaton->addTransition(0, 'a', 1);
        $table = $this->automaton->getTransitionTable();

        $this->assertEquals(1, $table[0]['a']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function aNewStateShouldBeIdentifiedByItsNumber(): void
    {
        $state = new State(2, []);
        $this->automaton->addState($state);

        $this->assertSame($state, $this->automaton->getState(2));
    }
}
