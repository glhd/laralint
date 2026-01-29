<?php

namespace Glhd\LaraLint\Tests\Linters;

use Glhd\LaraLint\Linters\NoGuardedOrFillable;
use Glhd\LaraLint\Tests\TestCase;

class NoGuardedOrFillableTest extends TestCase
{
	public function test_it_flags_guarded_property(): void
	{
		$source = <<<'END_SOURCE'
		class User extends \App\Model
		{
			protected $guarded = [];
		}
		END_SOURCE;

		$this->withLinter(NoGuardedOrFillable::class)
			->lintSource($source)
			->assertLintingResult('Models should not use the $guarded property');
	}

	public function test_it_flags_fillable_property(): void
	{
		$source = <<<'END_SOURCE'
		class User extends \App\Model
		{
			protected $fillable = ['name', 'email'];
		}
		END_SOURCE;

		$this->withLinter(NoGuardedOrFillable::class)
			->lintSource($source)
			->assertLintingResult('Models should not use the $fillable property');
	}

	public function test_it_flags_both_properties(): void
	{
		$source = <<<'END_SOURCE'
		class User extends \App\Model
		{
			protected $guarded = [];
			protected $fillable = ['name'];
		}
		END_SOURCE;

		$this->withLinter(NoGuardedOrFillable::class)
			->lintSource($source)
			->assertLintingResultCount(2);
	}

	public function test_it_ignores_non_model_classes(): void
	{
		$source = <<<'END_SOURCE'
		class SomeService extends BaseService
		{
			protected $guarded = [];
			protected $fillable = ['name'];
		}
		END_SOURCE;

		$this->withLinter(NoGuardedOrFillable::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_ignores_other_properties(): void
	{
		$source = <<<'END_SOURCE'
		class User extends \App\Model
		{
			protected $table = 'users';
			protected $casts = [];
		}
		END_SOURCE;

		$this->withLinter(NoGuardedOrFillable::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
