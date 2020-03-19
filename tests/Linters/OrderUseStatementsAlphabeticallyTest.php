<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\OrderUseStatementsAlphabetically;

class OrderUseStatementsAlphabeticallyTest extends TestCase
{
	public function test_it_does_not_flag_use_statements_in_alpha_order() : void
	{
		$source = <<<'END_SOURCE'
		use A;
		use Aa;
		use B;
		END_SOURCE;
		
		$this->withLinter(OrderUseStatementsAlphabetically::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_flags_use_statements_in_non_alpha_order() : void
	{
		$source = <<<'END_SOURCE'
		use A;
		use B;
		use Aa;
		END_SOURCE;
		
		$this->withLinter(OrderUseStatementsAlphabetically::class)
			->lintSource($source)
			->assertLintingResult();
	}
}
