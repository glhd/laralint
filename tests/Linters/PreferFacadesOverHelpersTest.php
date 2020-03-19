<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\PreferAuthId;
use Glhd\LaraLint\Linters\PreferFacadesOverHelpers;
use Glhd\LaraLint\Linters\SpaceAtBeginningOfComment;

class PreferFacadesOverHelpersTest extends TestCase
{
	public function test_it_flags_a_helper_function() : void
	{
		$source = <<<'END_SOURCE'
		$user = auth()->user();
		END_SOURCE;
		
		$this->withLinter(PreferFacadesOverHelpers::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_does_not_flag_a_facade() : void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Support\Facades\Auth;
		$user = Auth::user();
		END_SOURCE;
		
		$this->withLinter(PreferFacadesOverHelpers::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
