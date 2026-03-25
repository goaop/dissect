<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Analysis;

use Dissect\Parser\LALR1\Analysis\Exception\ReduceReduceConflictException;
use Dissect\Parser\Grammar;
use Dissect\Parser\Parser;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    protected ?Analyzer $analyzer = null;

    #[\PHPUnit\Framework\Attributes\Test]
    public function automatonShouldBeCorrectlyBuilt(): void
    {
        $grammar = new Grammar();

        $grammar('S')
            ->is('a', 'S', 'b')
            ->is();

        $grammar->start('S');

        $result = $this->getAnalysisResult($grammar);
        $table = $result->getAutomaton()->getTransitionTable();

        $this->assertSame(1, $table[0]['S']);
        $this->assertSame(2, $table[0]['a']);
        $this->assertSame(2, $table[2]['a']);
        $this->assertSame(3, $table[2]['S']);
        $this->assertSame(4, $table[3]['b']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lookaheadShouldBeCorrectlyPumped(): void
    {
        $grammar = new Grammar();

        $grammar('S')
            ->is('A', 'B', 'C', 'D');

        $grammar('A')
            ->is('a');

        $grammar('B')
            ->is('b');

        $grammar('C')
            ->is(/* empty */);

        $grammar('D')
            ->is('d');

        $grammar->start('S');

        $automaton = $this->getAnalysisResult($grammar)->getAutomaton();

        $this->assertSame(
            array(Parser::EOF_TOKEN_TYPE),
            $automaton->getState(1)->get(0, 1)->getLookahead()
        );

        $this->assertSame(
            array('b'),
            $automaton->getState(3)->get(2, 1)->getLookahead()
        );

        $this->assertSame(
            array('d'),
            $automaton->getState(4)->get(4, 0)->getLookahead()
        );

        $this->assertSame(
            array('d'),
            $automaton->getState(5)->get(3, 1)->getLookahead()
        );

        $this->assertSame(
            array(Parser::EOF_TOKEN_TYPE),
            $automaton->getState(7)->get(1, 4)->getLookahead()
        );

        $this->assertSame(
            array(Parser::EOF_TOKEN_TYPE),
            $automaton->getState(8)->get(5, 1)->getLookahead()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function parseTableShouldBeCorrectlyBuilt(): void
    {
        $grammar = new Grammar();

        $grammar('S')
            ->is('a', 'S', 'b')
            ->is(/* empty */);

        $grammar->start('S');

        $table = $this->getAnalysisResult($grammar)->getParseTable();

        // shift(2)
        $this->assertSame(2, $table['action'][0]['a']);

        // reduce(S -> )
        $this->assertSame(-2, $table['action'][0][Parser::EOF_TOKEN_TYPE]);

        // accept
        $this->assertSame(0, $table['action'][1][Parser::EOF_TOKEN_TYPE]);

        // shift(2)
        $this->assertSame(2, $table['action'][2]['a']);

        // reduce(S -> )
        $this->assertSame(-2, $table['action'][2]['b']);

        // shift(4)
        $this->assertSame(4, $table['action'][3]['b']);

        // reduce(S -> a S b)
        $this->assertSame(-1, $table['action'][4]['b']);
        $this->assertSame(-1, $table['action'][4][Parser::EOF_TOKEN_TYPE]);

        $this->assertSame(1, $table['goto'][0]['S']);
        $this->assertSame(3, $table['goto'][2]['S']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unexpectedConflictsShouldThrowAnException(): void
    {
        $grammar = new Grammar();

        $grammar('S')
            ->is('a', 'b', 'C', 'd')
            ->is('a', 'b', 'E', 'd');

        $grammar('C')
            ->is(/* empty */);

        $grammar('E')
            ->is(/* empty */);

        $grammar->start('S');

        try {
            $this->getAnalysisResult($grammar);
            $this->fail('Expected an exception warning of a reduce/reduce conflict.');
        } catch(ReduceReduceConflictException $e) {
            $this->assertSame(3, $e->getStateNumber());
            $this->assertSame('d', $e->getLookahead());
            $this->assertSame(3, $e->getFirstRule()->getNumber());
            $this->assertSame(4, $e->getSecondRule()->getNumber());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function expectedConflictsShouldBeRecorded(): void
    {
        $grammar = new Grammar();

        $grammar('S')
            ->is('S', 'S', 'S')
            ->is('S', 'S')
            ->is('b');

        $grammar->resolve(Grammar::ALL);
        $grammar->start('S');

        $conflicts = $this->getAnalysisResult($grammar)->getResolvedConflicts();

        $this->assertCount(4, $conflicts);

        $conflict = $conflicts[0];

        $this->assertSame(3, $conflict['state']);
        $this->assertSame('b', $conflict['lookahead']);
        $this->assertArrayHasKey('rule', $conflict);
        $this->assertSame(2, $conflict['rule']->getNumber());
        $this->assertSame(Grammar::SHIFT, $conflict['resolution']);

        $conflict = $conflicts[1];

        $this->assertSame(4, $conflict['state']);
        $this->assertSame('b', $conflict['lookahead']);
        $this->assertArrayHasKey('rule', $conflict);
        $this->assertSame(1, $conflict['rule']->getNumber());
        $this->assertSame(Grammar::SHIFT, $conflict['resolution']);

        $conflict = $conflicts[2];

        $this->assertSame(4, $conflict['state']);
        $this->assertSame(Parser::EOF_TOKEN_TYPE, $conflict['lookahead']);
        $this->assertArrayHasKey('rules', $conflict);
        $this->assertSame(1, $conflict['rules'][0]->getNumber());
        $this->assertSame(2, $conflict['rules'][1]->getNumber());
        $this->assertSame(Grammar::LONGER_REDUCE, $conflict['resolution']);

        $conflict = $conflicts[3];

        $this->assertSame(4, $conflict['state']);
        $this->assertSame('b', $conflict['lookahead']);
        $this->assertArrayHasKey('rule', $conflict);
        $this->assertSame(2, $conflict['rule']->getNumber());
        $this->assertSame(Grammar::SHIFT, $conflict['resolution']);
    }

    protected function getAnalysisResult(Grammar $grammar): AnalysisResult
    {
        return $this->getAnalyzer()->analyze($grammar);
    }

    protected function getAnalyzer(): Analyzer
    {
        if ($this->analyzer === null) {
            $this->analyzer = new Analyzer();
        }

        return $this->analyzer;
    }
}
