# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run tests
vendor/bin/phpunit

# Run tests with coverage
composer run-script test-coverage

# Apply Rector refactoring rules
vendor/bin/rector process

# CLI tool
php bin/dissect.php
```

To run a single test file: `vendor/bin/phpunit tests/Path/To/SomeTest.php`

## Architecture

Dissect is a pure PHP library for lexical and syntactical analysis (building custom lexers and LALR(1) parsers). It is used by the GoAOP framework to parse pointcut DSL expressions.

### Data Flow

```
Input String → Lexer → TokenStream → Parser → AST / semantic result
                                        ↑
                                   Grammar (rules + callbacks)
```

### Core Modules

**`src/Lexer/`** — Converts strings into token streams.
- `AbstractLexer` — Base class handling line tracking, token filtering, EOF insertion. Subclasses implement `extractToken()` and `shouldSkipToken()`.
- `SimpleLexer` / `StatefulLexer` — Fluent builders (`token()`, `regex()`, `skip()`, `state()`). `StatefulLexer` supports context-dependent tokenization via explicit state transitions.
- `RegexLexer` — Abstract base for custom regex-based lexers.
- Recognizers: `SimpleRecognizer` (string match) and `RegexRecognizer` (regex match) are tried in sequence until one succeeds.

**`src/Parser/`** — Converts token streams into results using LALR(1) parsing.
- `Grammar` — Defines rules with a fluent API: `$grammar('NonTerminal')->is('A', 'B')->call(fn(...) => ...)`. Also handles operator precedence and associativity.
- `Rule` — A single grammar production with optional semantic action callback.
- `src/Parser/LALR1/` — Core LALR(1) engine:
  - `Analyzer` — Computes FIRST/FOLLOW sets and builds the parse table (shift/reduce/accept actions) from a `Grammar`.
  - `Automaton` / `State` / `Item` — LR(0) items and states forming the state machine.
  - `Parser` — Drives the parse loop using the generated table; executes rule callbacks on reduction.
  - Dumpers — Debug utilities to print parse tables and automaton graphs.

**`src/Node/`** — AST representation.
- `Node` interface — Countable and IteratorAggregate for tree traversal.
- `CommonNode` — Default implementation with parent/child relationships and arbitrary attribute storage.

**`src/Console/`** — CLI via Symfony Console (`bin/dissect`).

**`src/Util/`** — Unicode-aware string utilities.

### Key Patterns

- Grammar rules are defined as callbacks: reductions transform matched symbols into AST nodes or computed values.
- The `Analyzer` is run once per grammar to build the parse table; the resulting `Automaton` is passed to `Parser` for repeated use.
- Conflict resolution (shift/reduce, reduce/reduce) is handled during grammar analysis using operator precedence declarations.
