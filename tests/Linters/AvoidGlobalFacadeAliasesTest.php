<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\AvoidGlobalFacadeAliases;
use Illuminate\Support\Facades\Auth;

class AvoidGlobalFacadeAliasesTest extends TestCase
{
	public function test_it_flags_an_alias_when_inside_a_namespace() : void
	{
		$source = <<<'END_SOURCE'
		<?php
		namespace Foo;
		$id = \Auth::id();
		END_SOURCE;
		
		$fqcn = Auth::class;
		
		$this->withLinter(AvoidGlobalFacadeAliases::class)
			->lintSource($source)
			->assertLintingResult("Please use '$fqcn' rather than the Auth alias.");
	}
	
	public function test_it_does_not_flag_an_alias_in_a_global_namespace() : void
	{
		$source = <<<'END_SOURCE'
		<?php
		$id = \Auth::id();
		END_SOURCE;
		
		$this->withLinter(AvoidGlobalFacadeAliases::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
