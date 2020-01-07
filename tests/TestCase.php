<?php

namespace Galahad\LaraLint\Tests;

use Glhd\LaraLint\Runners\StringRunner;
use Glhd\LaraLint\Support\LaraLintServiceProvider;
use Glhd\LaraLint\Tests\Constraints\LintingResult;
use Glhd\LaraLint\Tests\Constraints\LintingResultCount;
use Glhd\LaraLint\Tests\Constraints\NoLintingResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	/**
	 * @var string
	 */
	protected $linter;
	
	/**
	 * @var \Glhd\LaraLint\ResultCollection
	 */
	protected $results;
	
	protected function setUp() : void
	{
		parent::setUp();
		
		$this->linter = null;
		$this->results = null;
	}
	
	protected function getPackageProviders($app) : array
	{
		return [
			LaraLintServiceProvider::class,
		];
	}
	
	protected function withLinter(string $linter) : self
	{
		$this->linter = $linter;
		
		return $this;
	}
	
	protected function lintSource(string $source, string $filename = null) : self
	{
		if (null === $filename) {
			$filename = Str::slug(class_basename($this->linter)).'_test.php';
		}
		
		$this->results = (new StringRunner($filename, $source))
			->run(new Collection([$this->linter]));
		
		return $this;
	}
	
	protected function assertLintingResult(string $message = null, bool $match_substring = false) : self 
	{
		$this->assertThat(
			$this->results,
			new LintingResult($this->linter, $message, $match_substring)
		);
		
		return $this;
	}
	
	protected function assertNoLintingResult(string $message = null, bool $match_substring = false) : self
	{
		$this->assertThat(
			$this->results,
			new NoLintingResult($this->linter, $message, $match_substring)
		);
		
		return $this;
	}
	
	protected function assertLintingResultCount(int $count) : self
	{
		$this->assertThat(
			$this->results,
			new LintingResultCount($count)
		);
		
		return $this;
	}
	
	protected function assertNoLintingResults() : self
	{
		$this->assertThat(
			$this->results,
			new LintingResultCount(0)
		);
		
		return $this;
	}
}
