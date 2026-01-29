<?php

namespace Glhd\LaraLint\Tests\Support;

use Glhd\LaraLint\Linters\PreferAuthId;
use Glhd\LaraLint\Linters\SpaceAtBeginningOfComment;
use Glhd\LaraLint\Tests\TestCase;

class IgnoreDirectiveIntegrationTest extends TestCase
{
	public function test_same_line_ignore_suppresses_linting_result(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_same_line_ignore_only_affects_that_line(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore
$id2 = Auth::user()->id;
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResultCount(1);
	}

	public function test_next_line_ignore_suppresses_following_line(): void
	{
		$source = <<<'PHP'
<?php
// @laralint-ignore-next-line
$id = Auth::user()->id;
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_next_line_ignore_only_affects_next_line(): void
	{
		$source = <<<'PHP'
<?php
// @laralint-ignore-next-line
$id = Auth::user()->id;
$id2 = Auth::user()->id;
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResultCount(1);
	}

	public function test_file_ignore_suppresses_entire_file(): void
	{
		$source = <<<'PHP'
<?php
// @laralint-ignore-file
$id = Auth::user()->id;
$id2 = Auth::user()->id;
$id3 = Auth::user()->id;
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_specific_linter_ignore_only_affects_that_linter(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore PreferAuthId
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_specific_linter_ignore_does_not_affect_other_linters(): void
	{
		// When ignoring SpaceAtBeginningOfComment, PreferAuthId should still trigger
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore SpaceAtBeginningOfComment
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResultCount(1);
	}

	public function test_ignore_all_affects_all_linters(): void
	{
		// A generic @laralint-ignore should suppress all linters
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_hash_comment_style_works(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; # @laralint-ignore
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_block_comment_style_works(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; /* @laralint-ignore */
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_multiple_ignores_in_file(): void
	{
		$source = <<<'PHP'
<?php
$id1 = Auth::user()->id; // @laralint-ignore
$id2 = Auth::user()->id;
// @laralint-ignore-next-line
$id3 = Auth::user()->id;
$id4 = Auth::user()->id;
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResultCount(2); // lines 3 and 6
	}

	public function test_case_insensitive_linter_names(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore preferauthid
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_multiple_linter_names(): void
	{
		$source = <<<'PHP'
<?php
$id = Auth::user()->id; // @laralint-ignore PreferAuthId, SpaceAtBeginningOfComment
PHP;

		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
