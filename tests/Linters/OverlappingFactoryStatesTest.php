<?php

namespace Glhd\LaraLint\Tests\Linters;

use Glhd\LaraLint\Linters\OverlappingFactoryStates;
use Glhd\LaraLint\Tests\TestCase;

class OverlappingFactoryStatesTest extends TestCase
{
	public function test_it_does_not_flag_factory_calls_with_different_attributes(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['name' => 'Alice']);
		User::factory()->create(['email' => 'bob@test.com']);
		User::factory()->create(['role' => 'admin']);
		User::factory()->create(['status' => 'active']);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_does_not_flag_less_than_four_overlapping_calls(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_does_not_flag_four_calls_with_only_two_overlapping_attributes(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'name' => 'Alice']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'name' => 'Bob']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'name' => 'Chris']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'name' => 'Dana']);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_four_calls_with_three_overlapping_attributes(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertLintingResult('Consider extracting a factory state', match_substring: true);
	}

	public function test_it_flags_calls_with_additional_non_overlapping_attributes(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0, 'name' => 'Alice']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0, 'email' => 'bob@test.com']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0, 'foo' => 'bar']);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertLintingResult('Consider extracting a factory state for User', match_substring: true);
	}

	public function test_it_does_not_mix_different_models(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		Post::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		Post::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_also_detects_make_calls(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->make(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->make(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->make(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->make(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertLintingResult('Consider extracting a factory state', match_substring: true);
	}

	public function test_it_detects_overlap_with_different_value_types(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'is_active' => true, 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'is_active' => true, 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'is_active' => true, 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'is_active' => true, 'credits' => 0]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertLintingResult('Consider extracting a factory state', match_substring: true);
	}

	public function test_it_reports_only_maximal_shared_subset(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
		User::factory()->create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
		User::factory()->create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
		User::factory()->create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertLintingResultCount(1);
	}

	public function test_it_requires_same_values_not_just_same_keys(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'admin', 'status' => 'active', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'inactive', 'credits' => 0]);
		User::factory()->create(['role' => 'guest', 'status' => 'active', 'credits' => 100]);
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_handles_factory_calls_without_attributes(): void
	{
		$source = <<<'END_SOURCE'
		User::factory()->create();
		User::factory()->create();
		User::factory()->create();
		User::factory()->create();
		END_SOURCE;

		$this->withLinter(OverlappingFactoryStates::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
