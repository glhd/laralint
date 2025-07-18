<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\OrderClassMembers;
use Glhd\LaraLint\Linters\OrderModelMembers;

class OrderModelMembersTest extends TestCase
{
	public function test_it_allows_model_members_in_the_expected_order() : void
	{
		$source = <<<'END_SOURCE'
		class Foo extends \App\Model
		{
			protected function casts(): array
			{
				return [];
			}
		    
			public static function boot()
			{
			}
			
			public function getFooAttribute()
			{
			}
			
			public function setFooAttribute()
			{
			}
			
			public function bar()
			{
				return $this->hasOne(Bar::class);
			}
			
			public function scopeBar($query)
			{
			}
			
			#[\Illuminate\Database\Eloquent\Attributes\Scope]
			public function baz($query)
			{
			}
		}
		END_SOURCE;
		
		$this->withLinter(OrderModelMembers::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_flags_a_model_in_with_unexpected_ordering() : void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Database\Eloquent\Relations\HasOne;

		class Foo extends \App\Model
		{
					
			#[\Illuminate\Database\Eloquent\Attributes\Scope]
			public function baz($query)
			{
			}
			
			public static function boot()
			{
			}
			
			public function scopeBar($query)
			{
			}
			
			public function bar(): HasOne
			{
				return $this->hasOne(Bar::class);
			}
			
			public function getFooAttribute()
			{
			}
			
			public function setFooAttribute()
			{
			}
			
			protected function casts(): array
			{
				return [];
			}    
		}
		END_SOURCE;
		
		$this->withLinter(OrderModelMembers::class)
			->lintSource($source)
			->assertLintingResult()
			->assertLintingResultCount(5);
	}
}
