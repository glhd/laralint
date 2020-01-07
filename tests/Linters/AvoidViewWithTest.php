<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\AvoidGlobalFacadeAliases;
use Glhd\LaraLint\Linters\AvoidViewCompact;
use Glhd\LaraLint\Linters\AvoidViewWith;
use Illuminate\Support\Facades\Auth;

class AvoidViewWithTest extends TestCase
{
	public function test_it_flags_with_chained_after_view_helper() : void
	{
		$source = <<<'END_SOURCE'
		class FooController
		{
			public function index()
			{			
				return view('foo.index')
					->with('bar', 'baz');
			}
		}
		END_SOURCE;
		
		$this->withLinter(AvoidViewWith::class)
			->lintSource($source)
			->assertLintingResult();
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
		
		$this->withLinter(AvoidViewWith::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_compacted_variables_passed_to_view_helper() : void
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
		
		$this->withLinter(AvoidViewWith::class)
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
		
		$this->withLinter(AvoidViewWith::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
