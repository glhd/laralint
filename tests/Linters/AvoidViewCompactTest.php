<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\AvoidGlobalFacadeAliases;
use Glhd\LaraLint\Linters\AvoidViewCompact;
use Illuminate\Support\Facades\Auth;

class AvoidViewCompactTest extends TestCase
{
	public function test_it_flags_compacted_variables_passed_to_view_helper() : void
	{
		$source = <<<'END_SOURCE'
		class FooController
		{
			public function index()
			{
				$bar = 'baz';			
				return view('foo.index', compact('bar'));
			}
		}
		END_SOURCE;
		
		$this->withLinter(AvoidViewCompact::class)
			->lintSource($source)
			->assertLintingResult('Provide an array to the view rather than using compact().');
	}
	
	public function test_it_does_not_flag_an_array_passed_to_view_helper() : void
	{
		$source = <<<'END_SOURCE'
		class FooController
		{
			public function index()
			{
				return view('foo.index', ['bar' => 'baz']);
			}
		}
		END_SOURCE;
		
		$this->withLinter(AvoidViewCompact::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_the_view_helper_with_no_second_parameter() : void
	{
		$source = <<<'END_SOURCE'
		class FooController
		{
			public function index()
			{
				return view('foo.index');
			}
		}
		END_SOURCE;
		
		$this->withLinter(AvoidViewCompact::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
