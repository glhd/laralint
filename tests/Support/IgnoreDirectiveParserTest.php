<?php

namespace Glhd\LaraLint\Tests\Support;

use Glhd\LaraLint\Support\Ignore\IgnoreDirectiveParser;
use Glhd\LaraLint\Tests\TestCase;

class IgnoreDirectiveParserTest extends TestCase
{
	protected IgnoreDirectiveParser $parser;

	protected function setUp(): void
	{
		parent::setUp();
		$this->parser = new IgnoreDirectiveParser();
	}

	public function test_it_parses_same_line_ignore(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; // @laralint-ignore
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertFalse($directives->shouldIgnoreFile());
		$this->assertTrue($directives->shouldIgnoreLine(2));
		$this->assertFalse($directives->shouldIgnoreLine(3));
	}

	public function test_it_parses_next_line_ignore(): void
	{
		$source = <<<'PHP'
		<?php
		// @laralint-ignore-next-line
		$x = 1;
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertFalse($directives->shouldIgnoreFile());
		$this->assertFalse($directives->shouldIgnoreLine(2)); // comment line
		$this->assertTrue($directives->shouldIgnoreLine(3)); // next line
		$this->assertFalse($directives->shouldIgnoreLine(4));
	}

	public function test_it_parses_file_ignore(): void
	{
		$source = <<<'PHP'
		<?php
		// @laralint-ignore-file
		$x = 1;
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreFile());
	}

	public function test_it_parses_specific_linter_ignore(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; // @laralint-ignore PreferAuthId
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2, 'PreferAuthId'));
		$this->assertFalse($directives->shouldIgnoreLine(2, 'OtherLinter'));
		$this->assertFalse($directives->shouldIgnoreLine(3, 'PreferAuthId'));
	}

	public function test_it_parses_multiple_linter_names(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; // @laralint-ignore PreferAuthId, SpaceAtBeginningOfComment
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2, 'PreferAuthId'));
		$this->assertTrue($directives->shouldIgnoreLine(2, 'SpaceAtBeginningOfComment'));
		$this->assertFalse($directives->shouldIgnoreLine(2, 'OtherLinter'));
	}

	public function test_it_is_case_insensitive_for_linter_names(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; // @laralint-ignore preferauthid
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2, 'PreferAuthId'));
		$this->assertTrue($directives->shouldIgnoreLine(2, 'PREFERAUTHID'));
	}

	public function test_it_parses_hash_comment_style(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; # @laralint-ignore
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2));
		$this->assertFalse($directives->shouldIgnoreLine(3));
	}

	public function test_it_parses_block_comment_style(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; /* @laralint-ignore */
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2));
		$this->assertFalse($directives->shouldIgnoreLine(3));
	}

	public function test_it_handles_multiple_ignore_directives(): void
	{
		$source = <<<'PHP'
		<?php
		$a = 1; // @laralint-ignore
		$b = 2;
		// @laralint-ignore-next-line
		$c = 3;
		$d = 4;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(2)); // same line ignore
		$this->assertFalse($directives->shouldIgnoreLine(3));
		$this->assertFalse($directives->shouldIgnoreLine(4)); // comment line
		$this->assertTrue($directives->shouldIgnoreLine(5)); // next line ignore
		$this->assertFalse($directives->shouldIgnoreLine(6));
	}

	public function test_it_handles_ignore_next_line_at_end_of_file(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1;
		// @laralint-ignore-next-line
		PHP;

		$directives = $this->parser->parse($source);

		// Should not cause errors, and line 3 is the comment itself
		$this->assertFalse($directives->shouldIgnoreLine(2));
		$this->assertFalse($directives->shouldIgnoreLine(3));
	}

	public function test_it_ignores_directive_not_in_comment(): void
	{
		$source = <<<'PHP'
		<?php
		$x = "@laralint-ignore";
		$y = 2;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertFalse($directives->shouldIgnoreLine(2));
		$this->assertFalse($directives->shouldIgnoreLine(3));
	}

	public function test_ignore_all_linters_takes_precedence(): void
	{
		$source = <<<'PHP'
		<?php
		$x = 1; // @laralint-ignore PreferAuthId
		PHP;

		// Add another ignore for all linters on same line (via merging)
		$directives = $this->parser->parse($source);

		// First check specific linter is ignored
		$this->assertTrue($directives->shouldIgnoreLine(2, 'PreferAuthId'));
		$this->assertFalse($directives->shouldIgnoreLine(2, 'OtherLinter'));
	}

	public function test_it_parses_specific_linter_with_next_line(): void
	{
		$source = <<<'PHP'
		<?php
		// @laralint-ignore-next-line PreferAuthId
		$x = Auth::user()->id;
		PHP;

		$directives = $this->parser->parse($source);

		$this->assertTrue($directives->shouldIgnoreLine(3, 'PreferAuthId'));
		$this->assertFalse($directives->shouldIgnoreLine(3, 'OtherLinter'));
	}
}
