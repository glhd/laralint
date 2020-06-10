<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\PreferFullyRestfulControllers;
use Glhd\LaraLint\Linters\PrefixTestsWithTest;
use Glhd\LaraLint\Linters\SpaceAtBeginningOfComment;

class PrefixTestsWithTestTest extends TestCase
{
	public function test_it_flags_a_test_annotation() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			/** @test */
			public function it_does_something()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertLintingResult();
	}
	
	public function test_it_flags_a_camel_case_test_name() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			public function testItDoesSomething()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertLintingResult();
	}
	
	public function test_it_does_not_flag_a_prefixed_method() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			public function test_it_does_something()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_a_protected_method() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			public function test_it_does_something()
			{
			}
			
			protected function helperMethod()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_a_data_provider() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			/** @dataProvider foo */
			public function test_it_does_something()
			{
			}
			
			public function foo()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_a_function_inside_an_anonymous_class_within_a_test() : void
	{
		$source = <<<'END_SOURCE'
		class LinterTest
		{
			public function test_it_does_something()
			{
				$foo = new class {
					public function bar()
					{
						return 'ok';
					}
				};
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PrefixTestsWithTest::class)
			->lintSource($source, 'tests/LinterTest.php')
			->assertNoLintingResults();
	}
}
