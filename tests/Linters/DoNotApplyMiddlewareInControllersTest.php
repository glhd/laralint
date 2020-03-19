<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\DoNotApplyMiddlewareInControllers;
use Glhd\LaraLint\Linters\OrderClassMembers;
use Glhd\LaraLint\Linters\PreferFullyRestfulControllers;

class DoNotApplyMiddlewareInControllersTest extends TestCase
{
	public function test_it_does_not_flag_a_controller_with_no_middleware_calls_in_its_constructor() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(DoNotApplyMiddlewareInControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
	
	public function test_it_flags_a_controller_with_middleware_calls_in_its_constructor() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
				$this->middleware('auth');
			}
		}	
		END_SOURCE;
		
		$this->withLinter(DoNotApplyMiddlewareInControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertLintingResult();
	}
}
