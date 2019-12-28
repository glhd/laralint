# LaraLint

This is a **very early** work-in-progress linter for Laravel projects.
It's different from other PHP linters in that it focuses on building
custom rules for your specific needs.

The goal is to make **writing custom linters** easy and fluent, just like
writing Laravel-backed code. While the project uses an AST/tokenizers/etc
under-the-hood, most common use-cases shouldn't need a deep understanding
of how that works.

## Getting Started

Documentation will arrive once the basic API is more complete. For right now,
you'll have to install the project on your own and mess around with it :)

Running `php artisan laralint:install` will install a nice helper file that
you can use to set up LaraLint to work with any IDE that has support for
`PHP_CodeSniffer` (like PHPStorm). Just configure your IDE to point to that
file, and your LaraLint lints will get flagged in your IDE.

Running `php artisan laralint:lint` will lint your files.

