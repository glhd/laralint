<?php

namespace Glhd\LaraLint\Printers;

use Glhd\LaraLint\Printers\Concerns\NormalizesFilenames;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Str;

class ConsolePrinter extends IlluminatePrinter
{
	use NormalizesFilenames;
	
	protected $file_count = 0;
	
	protected $result_count = 0;
	
	public function opening() : void
	{
		$this->newLine();
		$this->writeln('<info>   _                        _</info>');
		$this->writeln('<info> _|_)                    \_|_)  o</info>');
		$this->writeln('<info>  |     __,   ,_    __,    |        _  _  _|_</info>');
		$this->writeln('<info> _|    /  |  /  |  /  |   _|    |  / |/ |  |</info>');
		$this->writeln('<info> /\___/\_/|_/   |_/\_/|_/(/\___/|_/  |  |_/|_/</info>');
		$this->newLine();
	}
	
	public function closing() : void
	{
		if ($this->result_count) {
			$warnings = Str::plural('warning', $this->result_count);
			$files = Str::plural('file', $this->file_count);
			
			$this->writeln(" <error> {$this->result_count} total {$warnings} in {$this->file_count} {$files} </error>");
			$this->newLine();
		}
	}
	
	public function startFile(string $filename) : void
	{
		$this->newLine();
		$this->section($this->normalizeFilename($filename));
		
		$this->file_count++;
	}
	
	public function fileResults(string $filename, ResultCollection $results) : void
	{
		if ($results->isEmpty()) {
			$this->writeln("<info>✓ No issues found.\n</info>");
			return;
		}
		
		$this->result_count += $results->count();
		
		$this->table(
			['Line', 'Char', 'Message'],
			$results->toBase()->map(function(Result $result) use ($filename) {
				return [
					"<href=phpstorm://open?file={$filename}&line={$result->getLine()}>{$result->getLine()}</>",
					$result->getCharacter(),
					"<href=phpstorm://open?file={$filename}&line={$result->getLine()}>{$result->getMessage()}</>",
				];
			})->toArray()
		);
		
		$this->newLine();
	}
	
}
