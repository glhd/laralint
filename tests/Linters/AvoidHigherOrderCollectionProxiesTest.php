<?php

namespace Glhd\LaraLint\Tests\Linters;

use Glhd\LaraLint\Linters\AvoidHigherOrderCollectionProxies;
use Glhd\LaraLint\Tests\TestCase;

class AvoidHigherOrderCollectionProxiesTest extends TestCase
{
	public function test_it_flags_a_proxied_method_call(): void
	{
		$source = <<<'END_SOURCE'
		$post->comments()
			->where('user_id', $post->user_id)
			->get()
			->each
			->delete();
		END_SOURCE;

		$this->withLinter(AvoidHigherOrderCollectionProxies::class)
			->lintSource($source)
			->assertLintingResult('Call ->each() with a closure rather than using the magic higher-order collection proxy.');
	}

	public function test_it_flags_a_proxied_property_access(): void
	{
		$source = <<<'END_SOURCE'
		$names = $users->map->name;
		END_SOURCE;

		$this->withLinter(AvoidHigherOrderCollectionProxies::class)
			->lintSource($source)
			->assertLintingResult('Call ->map() with a closure rather than using the magic higher-order collection proxy.');
	}

	public function test_it_does_not_flag_a_normal_method_call(): void
	{
		$source = <<<'END_SOURCE'
		$users->each(fn ($user) => $user->delete());
		END_SOURCE;

		$this->withLinter(AvoidHigherOrderCollectionProxies::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_does_not_flag_unrelated_nested_property_access(): void
	{
		$source = <<<'END_SOURCE'
		$value = $config->database->host;
		END_SOURCE;

		$this->withLinter(AvoidHigherOrderCollectionProxies::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_does_not_flag_a_dynamic_member_name(): void
	{
		$source = <<<'END_SOURCE'
		$result = $collection->$method->value;
		END_SOURCE;

		$this->withLinter(AvoidHigherOrderCollectionProxies::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
