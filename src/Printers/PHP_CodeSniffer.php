<?php /** @noinspection PhpHierarchyChecksInspection */

namespace Glhd\LaraLint\Printers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Console\OutputStyle;

class PHP_CodeSniffer extends IlluminatePrinter
{
	protected const PHPCS_VERSION = '3.5.3';
	
	// FIXME:
	// if ($this->option('phpcs') && $this->option('version')) {
	// 	$this->line('PHP_CodeSniffer version '.static::PHPCS_VERSION.' (stable) by Squiz. (http://www.squiz.net)');
	// 	return;
	// }
	
	public function opening() : void
	{
		$this->writeln('<?xml version="1.0" encoding="UTF-8"?>');
		$this->writeln('<phpcs version="'.static::PHPCS_VERSION.'">');
	}
	
	public function closing() : void
	{
		$this->writeln('</phpcs>');
	}
	
	public function startFile(string $filename) : void
	{
		// Do nothing
	}
	
	public function fileResults(string $filename, ResultCollection $results) : void
	{
		$this->writeln(sprintf(
			'  <file name="%s" errors="%d" warnings="0" fixable="0">',
			$filename,
			$results->count()
		));
		
		$results->each(function(Result $result) {
			$this->writeln(sprintf(
				'    <error line="%d" column="%d" source="%s" severity="5" fixable="0">',
				$result->getLine(),
				$result->getCharacter(),
				'LaraLint'
			));
			$this->writeln("      {$result->getMessage()}");
			$this->writeln('    </error>');
		});
		
		$this->writeln('  </file>');
	}
	
	protected function write($messages, bool $newline = false, int $type = OutputStyle::OUTPUT_RAW) : void
	{
		$this->output->write($messages, $newline, $type);
	}
	
	protected function writeln($messages, int $type = OutputStyle::OUTPUT_RAW) : void
	{
		$this->output->writeln($messages, $type);
	}
}
