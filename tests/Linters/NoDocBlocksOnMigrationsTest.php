<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\NoDocBlocksOnMigrations;

class NoDocBlocksOnMigrationsTest extends TestCase
{
	public function test_it_flags_default_docblocks() : void
	{
		$source = <<<'END_SOURCE'
		class TestMigration extends Migration
		{
			/**
			 * Run migrations
			 */
			public function up()
			{
			}
			
			/**
			 * Reverse the migrations
			 */
			public function down()
			{
			}
		}
		END_SOURCE;
		
		$this->withLinter(NoDocBlocksOnMigrations::class)
			->lintSource($source)
			->assertLintingResult();
	}
	
	public function test_it_does_not_flag_migrations_without_docblocks() : void
	{
		$source = <<<'END_SOURCE'
		class TestMigration extends Migration
		{
			public function up()
			{
			}
			
			public function down()
			{
			}
		}
		END_SOURCE;
		
		$this->withLinter(NoDocBlocksOnMigrations::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_flag_custom_docblocks() : void
	{
		$source = <<<'END_SOURCE'
		class TestMigration extends Migration
		{
			/**
			 * This needs a comment for some reason
			 */
			public function up()
			{
			}
			
			/**
			 * And so does this!
			 */
			public function down()
			{
			}
		}
		END_SOURCE;
		
		$this->withLinter(NoDocBlocksOnMigrations::class)
			->lintSource($source)
			->assertNoLintingResults();
	}
}
