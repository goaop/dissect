<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1;

use Dissect\Lexer\Token;
use Dissect\Parser\Grammar;

class ArithGrammar extends Grammar
{
    /** @noinspection PhpUnusedParameterInspection */
    public function __construct()
    {
        $this('Expr')
            ->is('Expr', '+', 'Expr')
            ->call(function(int|float $l, mixed $_, int|float $r): int|float { return $l + $r; })

            ->is('Expr', '-', 'Expr')
            ->call(function(int|float $l, mixed $_, int|float $r): int|float { return $l - $r; })

            ->is('Expr', '*', 'Expr')
            ->call(function(int|float $l, mixed $_, int|float $r): int|float { return $l * $r; })

            ->is('Expr', '/', 'Expr')
            ->call(function(int|float $l, mixed $_, int|float $r): int|float { return $l / $r; })

            ->is('Expr', '**', 'Expr')
            ->call(function(int|float $l, mixed $_, int|float $r): int|float { return pow($l, $r); })

            ->is('(', 'Expr', ')')
            ->call(function(mixed $r, int|float $e, mixed $_): int|float { return $e; })

            ->is('-', 'Expr')->prec(4)
            ->call(function(mixed $_, int|float $e): int|float { return -$e; })

            ->is('INT')
            ->call(function(Token $i): int { return (int) $i->getValue(); });

        $this->operators('+', '-')->left()->prec(1);
        $this->operators('*', '/')->left()->prec(2);
        $this->operators('**')->right()->prec(3);

        $this->start('Expr');
    }
}
