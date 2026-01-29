# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

LaraLint is an opinionated PHP linting framework for Laravel projects that uses Microsoft's Tolerant PHP Parser to analyze code via Abstract Syntax Trees (AST). Unlike traditional linters, it focuses
on making custom linting rules easy to write.

## Architecture

### How LaraLint Works

1. PHP code is parsed into an AST using Microsoft's Tolerant PHP Parser
2. LaraLint walks the tree top-to-bottom, passing each node to applicable linters
3. Linters return `Result` objects with line/character positions
4. Results are formatted via Printers (console, compact, or PHPCS XML)

### Key Directories

- `src/Linters/` - Built-in linters (14 total)
- `src/Linters/Strategies/` - Base linter strategies to extend
- `src/Contracts/` - Core interfaces (Linter, Matcher, Preset, Printer)
- `src/Presets/` - Linter presets (collections of linters)

### Linter Strategies

Extend one of these when writing a new linter:

- **MatchingLinter** - Detects specific AST patterns using a Matcher, fires callback on match. Most common strategy.
- **CollectingLinter** - Gathers all matching nodes, analyzes them collectively (e.g., checking use statement ordering)
- **OrderingLinter** - Specialized for member/property ordering rules

### Writing a Custom Linter

1. Extend a strategy from `src/Linters/Strategies/`
2. Define a `matcher()` method returning a `Matcher` (usually `TreeMatcher`)
3. Implement `onMatch()` to return a `Result` or null
4. Use `php artisan laralint:dump file.php` to visualize AST structure of sample code

### Testing Pattern

```php
public function test_it_flags_something(): void
{
    $this->withLinter(SomeLinter::class)
        ->lintSource($phpSource)
        ->assertLintingResult('Expected message');
}
```

Available assertions: `assertLintingResult()`, `assertNoLintingResult()`, `assertLintingResultCount()`, `assertNoLintingResults()`
