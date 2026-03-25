# 🔬 Dissect

> A pure-PHP toolkit for building custom **lexers** and **LALR(1) parsers** — fast, type-safe, and dependency-free.

![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/goaop/dissect/php-tests.yml?branch=master)
![PHPStan Badge](https://img.shields.io/badge/PHPStan-level%2010-brightgreen.svg?style=flat&link=https%3A%2F%2Fphpstan.org%2Fuser-guide%2Frule-levels)
[![Total Downloads](https://img.shields.io/packagist/dt/goaop/dissect.svg)](https://packagist.org/packages/goaop/dissect)
[![Daily Downloads](https://img.shields.io/packagist/dd/goaop/dissect.svg)](https://packagist.org/packages/goaop/dissect)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.4-8892BF.svg)](https://php.net/)
![GitHub License](https://img.shields.io/github/license/goaop/dissect)
[![Sponsor](https://img.shields.io/badge/Sponsor-❤️-lightgray?style=flat&logo=github)](https://github.com/sponsors/lisachenko)

---

## ✨ What is Dissect?

Dissect is a pure-PHP library for **lexical and syntactical analysis** — the foundational building blocks for any language tooling: expression evaluators, template engines, DSL interpreters, query parsers, and more.

It powers the [GoAOP framework](https://github.com/goaop/framework), where it parses pointcut DSL expressions into an AST for aspect-oriented programming.

### Data flow

```
Input String
    │
    ▼
┌─────────┐      ┌──────────────┐      ┌──────────────────┐
│  Lexer  │ ───▶ │ TokenStream  │ ───▶ │  LALR(1) Parser  │ ───▶ Result / AST
└─────────┘      └──────────────┘      └──────────────────┘
                                               ▲
                                         Grammar (rules
                                          + callbacks)
```

---

## 🚀 Key Features

### 🔤 Flexible Lexers
| Lexer               | Description                                                                                |
|---------------------|--------------------------------------------------------------------------------------------|
| **`SimpleLexer`**   | Fluent builder API — define tokens with strings or regex, mark skippable tokens            |
| **`StatefulLexer`** | Context-aware tokenization with explicit state transitions (e.g. for string interpolation) |
| **`RegexLexer`**    | Abstract base class adapted from Doctrine — ultra-fast single-pass regex lexing            |

### 📐 LALR(1) Parser

- **Full LALR(1) grammar support** — handles the vast majority of real-world grammars
- **Fluent grammar API** — define productions and semantic actions with readable PHP closures
- **Operator precedence & associativity** — built-in `left()`, `right()`, `nonassoc()` declarations
- **Conflict resolution** — configurable strategies: shift-wins, longer-reduce, earlier-reduce
- **Precomputed parse tables** — analyze once, serialize to PHP file, load instantly in production

### 🌳 AST Construction

- **`CommonNode`** — ready-to-use tree node with named children and arbitrary attributes
- **Countable & iterable** — traverse subtrees with standard PHP constructs

### 🛠 Developer Experience

- **Zero runtime dependencies** — only Symfony Console as an optional CLI dep
- **PHPStan level 10** — fully typed with generics, array shapes, and readonly properties
- **CLI tool** — dump parse tables and visualize automaton states as Graphviz graphs

---

## 📦 Installation

```bash
composer require goaop/dissect
```

---

## ⚡ Quick Example

```php
use Dissect\Lexer\SimpleLexer;
use Dissect\Parser\Grammar;
use Dissect\Parser\LALR1\Parser;

// 1. Define a lexer
$lexer = new SimpleLexer();
$lexer->token('INT',   '/[0-9]+/')
      ->token('PLUS',  '+')
      ->token('MINUS', '-')
      ->skip('WS',     '/\s+/');

// 2. Define a grammar
$grammar = new Grammar();
$grammar('Expr')
    ->is('Expr', 'PLUS', 'Expr')
    ->call(fn($l, $_, $r) => $l + $r)

    ->is('Expr', 'MINUS', 'Expr')
    ->call(fn($l, $_, $r) => $l - $r)

    ->is('INT')
    ->call(fn($t) => (int) $t->getValue());

$grammar->operators('PLUS', 'MINUS')->left()->prec(1);
$grammar->start('Expr');

// 3. Parse!
$parser = new Parser($grammar);
$result = $parser->parse($lexer->lex('3 + 5 - 2')); // → 6
```

---

## 📖 Documentation

| Topic                                | Description                                                      |
|--------------------------------------|------------------------------------------------------------------|
| [Lexical analysis](docs/lexing.md)   | `SimpleLexer`, `StatefulLexer`, `RegexLexer`, performance tips   |
| [Writing a grammar](docs/parsing.md) | Productions, callbacks, operator precedence, conflict resolution |
| [Building an AST](docs/ast.md)       | `CommonNode`, tree traversal                                     |
| [Common patterns](docs/common.md)    | Lists, comma-separated sequences, expression grammars            |
| [CLI tool](docs/cli.md)              | Precomputing parse tables, exporting automaton graphs            |

---

## 🧪 Testing & Quality

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Static analysis (PHPStan level 10)
composer phpstan
```

---

## 🙏 Credits

Originally created by [@jakubledl](https://github.com/jakubledl), extended by [@WalterWoshid](https://github.com/WalterWoshid), maintained by the [GoAOP](https://github.com/goaop) team.

Give a ⭐ if Dissect saved you from writing a parser by hand!
