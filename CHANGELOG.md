Changelog
=========

3.0.0 (2026-03-25)
------------------

- Modernized entire codebase for PHP 8.4+: readonly constructor-promoted properties, union types, named arguments, first-class callable syntax, and modern array destructuring throughout
- Refactored `Recognizer::match()` API from by-reference output parameter to clean `?string` return value
- Replaced `static $regex` local variable in `RegexLexer` with a typed class property; added explicit `preg_split` false-return guard
- Decomposed `StatefulLexer` nested array shape into three separately-typed arrays (`stateRecognizers`, `stateActions`, `stateSkipTokens`) for correctness and clarity
- Narrowed `Token::getType()` return type from `mixed` to `string`
- Added precise generic type annotations throughout: `array{action:..., goto:...}` parse table shapes, `ArrayIterator<int, Token>`, `IteratorAggregate<int, Token>`, `SplQueue<State>`, and `@template T of string` constraints on `Util`
- Fixed undefined variable bug in `Analyzer::buildParseTable()` (`$resolvedRules` used before assignment)
- Added `phpstan/phpstan` (level 10) and `phpstan/phpstan-phpunit` as dev dependencies; all 57 tests pass with zero static analysis errors

1.0.1 (2013-01-29)
------------------

- 2b40f94: Fixed an invalid format in the CLI

1.0.0 (2013-01-15)
------------------

- First release.
