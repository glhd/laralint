<?php

namespace Glhd\LaraLint\Tests\Linters;

use Glhd\LaraLint\Linters\SnakeCaseRelationships;
use Glhd\LaraLint\Tests\TestCase;

class SnakeCaseRelationshipsTest extends TestCase
{
	public function test_it_allows_snake_case_relationships(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			public function related_items()
			{
				return $this->hasMany(Item::class);
			}

			public function owner()
			{
				return $this->belongsTo(User::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_camel_case_relationships_using_heuristics(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			public function relatedItems()
			{
				return $this->hasMany(Item::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertLintingResult('Relationship method relatedItems() should be related_items()');
	}

	public function test_it_flags_camel_case_relationships_using_return_type(): void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Database\Eloquent\Relations\HasMany;

		class Foo extends \App\Model
		{
			public function relatedItems(): HasMany
			{
				return $this->hasMany(Item::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertLintingResult('Relationship method relatedItems() should be related_items()');
	}

	public function test_it_ignores_non_model_classes(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends SomeOtherClass
		{
			public function relatedItems()
			{
				return $this->hasMany(Item::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_ignores_non_relationship_methods(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			public function doSomething()
			{
				return 'hello';
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_ignores_protected_methods(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			protected function relatedItems()
			{
				return $this->hasMany(Item::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_ignores_private_methods(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			private function relatedItems()
			{
				return $this->hasMany(Item::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_multiple_violations(): void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			public function relatedItems()
			{
				return $this->hasMany(Item::class);
			}

			public function primaryOwner()
			{
				return $this->belongsTo(User::class);
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseRelationships::class)
			->lintSource($source)
			->assertLintingResultCount(2);
	}
}
