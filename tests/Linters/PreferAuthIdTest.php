<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\PreferAuthId;
use Glhd\LaraLint\Linters\SpaceAtBeginningOfComment;

class PreferAuthIdTest extends TestCase
{
	public function test_it_flags_global_use() : void
	{
		$source = <<<'END_SOURCE'
		$id = Auth::user()->id;
		END_SOURCE;
		
		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_flags_fully_qualified_global_use() : void
	{
		$source = <<<'END_SOURCE'
		$id = \Auth::user()->id;
		END_SOURCE;
		
		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_flags_imported_use() : void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Support\Facades\Auth;
		$id = Auth::user()->id;
		END_SOURCE;
		
		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_flags_fully_qualified_use() : void
	{
		$source = <<<'END_SOURCE'
		$id = Illuminate\Support\Facades\Auth::user()->id;
		END_SOURCE;
		
		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_does_not_flag_similar_looking_signatures() : void
	{
		$source = <<<'END_SOURCE'
		$id = API::user()->id;
		END_SOURCE;
		
		$this->withLinter(PreferAuthId::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
