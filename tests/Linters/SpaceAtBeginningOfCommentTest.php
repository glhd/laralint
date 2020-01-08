<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\AvoidGlobalFacadeAliases;
use Glhd\LaraLint\Linters\AvoidViewCompact;
use Glhd\LaraLint\Linters\AvoidViewWith;
use Glhd\LaraLint\Linters\SpaceAtBeginningOfComment;
use Illuminate\Support\Facades\Auth;

class SpaceAtBeginningOfCommentTest extends TestCase
{
	public function test_it_flags_single_line_comments() : void
	{
		$source = <<<'END_SOURCE'
		//hello world
		foo();
		END_SOURCE;
		
		$this->withLinter(SpaceAtBeginningOfComment::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_flags_multi_line_comments_with_missing_starting_space() : void
	{
		$source = <<<'END_SOURCE'
		/*hello world
		*/
		foo();
		END_SOURCE;
		
		$this->withLinter(SpaceAtBeginningOfComment::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_flags_multi_line_comments_with_missing_ending_space() : void
	{
		$source = <<<'END_SOURCE'
		/*
		hello world*/
		foo();
		END_SOURCE;
		
		$this->withLinter(SpaceAtBeginningOfComment::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_does_not_flag_single_line_comments_with_spaces() : void
	{
		$source = <<<'END_SOURCE'
		// hello world
		foo();
		END_SOURCE;
		
		$this->withLinter(SpaceAtBeginningOfComment::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_multi_line_comments_with_spaces() : void
	{
		$source = <<<'END_SOURCE'
		/*
		 * hello world
		 */
		END_SOURCE;
		
		$this->withLinter(SpaceAtBeginningOfComment::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
