<?php

namespace Glhd\LaraLint\Printers;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\ResultCollection;
use Glhd\LaraLint\Tests\Constraints\LintingDidNotStart;
use Glhd\LaraLint\Tests\Constraints\LintingStarted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class TestPrinter implements Printer
{
	protected $opened = false;
	
	protected $closed = false;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $started;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $results;
	
	public function __construct()
	{
		$this->started = new Collection();
		$this->results = new Collection();
	}
	
	public function isStarted(string $filename) : bool
	{
		$filepath = App::basePath($filename);
		
		return $this->started->contains($filepath);
	}
	
	public function assertStarted(string $filename) : self
	{
		TestCase::assertThat(
			$this,
			new LintingStarted($filename)
		);
		
		return $this;
	}
	
	public function assertDidNotStart(string $filename) : self
	{
		TestCase::assertThat(
			$this,
			new LintingDidNotStart($filename)
		);
		
		return $this;
	}
	
	public function opening() : void
	{
		$this->opened = true;
	}
	
	public function closing() : void
	{
		$this->closed = true;
	}
	
	public function startFile(string $filename) : void
	{
		$this->started->push($filename);
	}
	
	public function fileResults(string $filename, ResultCollection $results) : void
	{
		$this->results->put($filename, $results);
	}
}
