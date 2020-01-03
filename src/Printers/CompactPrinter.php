<?php

namespace Glhd\LaraLint\Printers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;

class CompactPrinter extends IlluminatePrinter
{
	public function opening() : void
	{
		$this->writeln(' ');
		$this->writeln(str_repeat('*', 80));
		
		$this->writeln('LaraLint Results:');
	}
	
	public function closing() : void
	{
		$this->writeln(' ');
		$this->writeln(str_repeat('*', 80));
		$this->writeln(' ');
	}
	
	public function startFile(string $filename) : void
	{
		// 
	}
	
	public function fileResults(string $filename, ResultCollection $results) : void
	{
		if ($results->isEmpty()) {
			return;
		}
		
		$this->newLine();
		
		$this->writeln($filename);
		
		$results->each(function(Result $result) {
			$this->writeln("Line {$result->getLine()}: {$result->getMessage()}");
		});
	}
}
