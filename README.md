# LaraLint

![GitHub Workflow Status](https://github.com/glhd/laralint/workflows/Run%20PHPUnit%20Tests/badge.svg)

This is a **very early** work-in-progress linter for Laravel projects.
It’s different from other PHP linters in that it focuses on building
custom rules for your specific needs.

The goal is to make **writing custom linters** easy and fluent, just like
writing Laravel-backed code. While the project uses an AST/tokenizers/etc
under-the-hood, most common use-cases shouldn’t need a deep understanding
of how that works.

## Getting Started

Install this into your project with:

```bash
composer require glhd/laralint --dev
```

Once installed, you can lint your project using LaraLint’s default rules 
by running:

```bash
php artisan laralint:lint
```

If you only want to lint uncommitted Git changes, you can pass the `--diff` 
flat to the command

```bash
php artisan laralint:lint --diff
```

Or, ff you only want to lint specific files, pass their filenames as the 
first argument:

```bash
php artisan laralint:lint app/Http/Controllers/HomeController.php
```

## Configuration and Presets

LaraLint comes with a **very opinionated** preset installed. If this works 
for you, great, but you’ll probably want to customize things to match 
your team’s code standards.

To start, publish the LaraLint config file:

```bash
php artisan vendor:publish --tag=laralint-config
```

This will install a `laralint.php` file into your project’s `config/` 
directory with the default LaraLint config.

In some cases, you may be able to simply tweak the config to meet your 
needs. But it’s more likely that you’ll want to create your own preset.

Take a look at the [Preset](src/Contracts/Preset.php) contract, or the 
default [LaraLint Preset](src/Presets/LaraLint.php) to get started. 
From there you can create a custom preset for your project that uses 
linters that make sense for your team. Mix and match the pre-built 
linters with your own [custom linters](#custom-linters) to your 
heart’s content!

## Custom Linters

If you’re interested in LaraLint, you’re probably interested in custom 
linters. Each team’s needs are different, and LaraLint tries to make it 
as easy as possible to quickly add logic that enforces your agreed upon 
conventions.

That said, LaraLint _does_ rely on concepts like Abstract Syntax Trees, 
which can be intimidating at first. But with a few key concepts under 
your belt, you should be traversing that tree like a champ in no time!

### Real Quick: How LaraLint Works

In as few words as possible:

LaraLint uses [Microsoft’s Tolerant PHP Parser](https://github.com/microsoft/tolerant-php-parser) 
to parse your PHP code into an Abstract Syntax Tree, or AST. We use 
Microsoft’s parser because it’s fast and works well with partially-written 
code (like code you’re typing in your IDE). It’s also the basis for 
VS Code’s IntelliSense, so it’s got a good track record.

Think of this tree as a structured view of your code. Given the following 
PHP code:

```php
class Foo
{
  public function bar()
  {
    return 'baz';
  }
}
```

The AST will look something like this (simplified for clarity’s sake):

  - ClassDeclaration (`class Foo`)
      - MethodDeclaration (`public function bar()`)
          - CompoundStatementNode (`return 'baz';`)
              - ReturnStatement (`return`)
                  - StringLiteral (`'baz'`)

LaraLint walks that tree from top to bottom, and passes each node to 
each applicable Linter for inspection. Linters are simply objects that 
receive AST nodes and optionally return linting results once the tree 
is fully walked.

This means you can write incredibly complex and custom linters, but 
hopefully you won't have to.

### How to Write a LaraLint Linter

The core of most LaraLint Linters is a `Matcher` object. These objects 
are designed to easily match a general AST “shape” and flag part of the 
code if the entire “shape” is found. for example, if you wanted to flag 
any method named `bar` that returns the string `'baz'`, you could use 
the following matcher:

```php
(new TreeMatcher())
    // (1) Find a method declaration where the method name is "bar"
    ->withChild(function(MethodDeclaration $node) {
        return 'bar' === $node->getName();
    })
    // (2) Find any return statement
    ->withChild(ReturnStatement::class)
    // (3) Find a string literal that matches the string "baz"
    ->withChild(function(StringLiteral $node) {
        return 'baz' === $node->getStringContentsText();
    })
    ->onMatch(function(Collection $all_matched_nodes) {
        // Create a linting result using the matched nodes.
        // LaraLint will automatically map the AST nodes to line
        // numbers when printing the results.
    });
```

As soon as LaraLint finds a node that matches the first rule, it will 
begin looking for a child that matches the second rule. If it finds a 
child that matches the second rule, it will move on to the third rule.
When it's matched all the rules, the matcher will trigger the `onMatch` 
callback, where you can perform any additional logic you choose.

LaraLint comes with several “strategies” that apply to common use-cases. 
These strategies abstract away even more of the logic, and are the best 
place to start. Look at some of the existing linters to understand how 
best to use each strategy.

### But Wait… How Do I Write a LaraLint Linter?

OK, you’re probably looking at `ReturnStatement` and `StringLiteral` and 
thinking, “I don’t speak Abstract Syntax Tree.”

Neither does anyone else. 

That's where the `laralint:dump` command comes into play. Say you’re 
trying to write the silly bar/baz linter from the example above. Simply 
create a PHP file that _should_ fail, and dump its tree:

```bash
php artisan laralint:dump barbaz_source.php
```

Which will output something like:

```
┏━━ ClassDeclaration ━━━━━━┓
┃                          ┃
┃ class Foo                ┃
┃ {                        ┃
┃   public function bar()  ┃
┃   {                      ┃
┃     return 'baz';        ┃
┃   }                      ┃
┃ }                        ┃
┃                          ┃
┃   ┏━━ ClassMembersNode ━━━━━━┓
┃   ┃                          ┃
┃   ┃ {                        ┃
┃   ┃   public function bar()  ┃
┃   ┃   {                      ┃
┃   ┃     return 'baz';        ┃
┃   ┃   }                      ┃
┃   ┃ }                        ┃
┃   ┃                          ┃
┃   ┃   ┏━━ MethodDeclaration ━━━┓
┃   ┃   ┃                        ┃
┃   ┃   ┃ public function bar()  ┃
┃   ┃   ┃   {                    ┃
┃   ┃   ┃     return 'baz';      ┃
┃   ┃   ┃   }                    ┃
┃   ┃   ┃                        ┃
┃   ┃   ┃   ┏━━ CompoundStatementNode ━━┓
┃   ┃   ┃   ┃                           ┃
┃   ┃   ┃   ┃ {                         ┃
┃   ┃   ┃   ┃     return 'baz';         ┃
┃   ┃   ┃   ┃   }                       ┃
┃   ┃   ┃   ┃                           ┃
┃   ┃   ┃   ┃   ┏━━ ReturnStatement ━━┓
┃   ┃   ┃   ┃   ┃                     ┃
┃   ┃   ┃   ┃   ┃ return 'baz';       ┃
┃   ┃   ┃   ┃   ┃                     ┃
┃   ┃   ┃   ┃   ┃   ┏━━ StringLiteral ━━┓
┃   ┃   ┃   ┃   ┃   ┃                   ┃
┃   ┃   ┃   ┃   ┃   ┃ 'baz'             ┃
┃   ┃   ┃   ┃   ┃   ┃                   ┃
┃   ┃   ┃   ┃   ┃   ┗━━━━━━━━━━━━━━━━━━━┛
┃   ┃   ┃   ┃   ┗━━━━━━━━━━━━━━━━━━━━━┛
┃   ┃   ┃   ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
┃   ┃   ┗━━━━━━━━━━━━━━━━━━━━━━━━━┛
┃   ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

Between the output of the `dump` command, and the example of existing Linters, 
you’d be surprised how easy it is to start writing your own rules.

## IDE Integration

The best place for linting results is right in your IDE. Rather than publishing 
our own IDE plugins, LaraLint can simply pretend to be `PHP_CodeSniffer`:

```bash
php artisan laralint:lint --printer=phpcs
```

This will give you XML output that is compatible with any plugin that can 
parse `PHP_CodeSniffer`’s XML output (such as PhpStorm).

Running `php artisan laralint:install` will install a nice helper file that
makes this a little easier. Just configure your IDE to point to that
file, and your LaraLint lints should get flagged in your IDE.

