<?php

namespace Glhd\LaraLint\Commands;

use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\FileProcessor;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Printers\CompactPrinter;
use Glhd\LaraLint\Printers\ConsolePrinter;
use Glhd\LaraLint\Printers\PHP_CodeSniffer;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class LintCommand extends Command
{
	protected $signature = 'laralint:lint {filename?*} {--diff} {--printer=} {--step} {--step-all}';
	
	protected $description = 'Run LaraLint on your project';
	
	public function handle()
	{
		// TODO: Load configuration/etc
		$linters = (new LaraLint())->linters();
		
		$printer = $this->getPrinter();
		$printer->opening();
		
		$flag_count = 0;
		
		$this->files()
			->each(function(SplFileInfo $file) use ($printer, $linters, &$flag_count) {
				$printer->startFile($file->getRealPath());
				
				$results = FileProcessor::make($file, $linters)->lint();
				$flag_count += $results->count();
				
				$printer->fileResults($file->getRealPath(), $results);
				
				if (
					(
						($this->option('step') && $results->isNotEmpty())
						|| $this->option('step-all')
					)
					&& false === $this->confirm('Continue?', true)
				) {
					return false;
				}
			});
		
		$printer->closing();
		
		return $flag_count > 0
			? 1
			: 0;
	}
	
	protected function files() : LazyCollection
	{
		if ($this->option('diff')) {
			return $this->gitDiffFiles();
		}
		
		if ($filename = $this->argument('filename')) {
			// TODO: Still confirm the extension/etc
			return LazyCollection::make($filename)
				->map(function($filename){
					return new SplFileInfo($filename);
				});
		}
		
		return LazyCollection::make(
			Finder::create()
				->files()
				->name('*.php')
				->exclude([ // FIXME: Make configurable
					base_path('bootstrap/'),
					base_path('vendor/'),
					base_path('public/'),
					base_path('storage/'),
				])
				->in(base_path('app'))
		);
	}
	
	protected function gitDiffFiles() : LazyCollection
	{
		$proc = new Process(['git', 'diff', '--name-only'], base_path());
		$proc->run();
		
		if (!$proc->isSuccessful()) {
			throw new RuntimeException($proc->getErrorOutput());
		}
		
		return LazyCollection::make(explode("\n", trim($proc->getOutput())))
			->filter(function($filename) {
				return preg_match('/\.php$/i', $filename);
			})
			->map(function($filename) {
				return new SplFileInfo(base_path($filename));
			});
	}
	
	protected function getPrinter() : Printer
	{
		switch ($this->option('printer')) {
			case 'phpcs':
				return new PHP_CodeSniffer($this->getOutput());
			
			case 'compact':
				return new CompactPrinter($this->getOutput());
				
			default:
				return new ConsolePrinter($this->getOutput());
		}
	}
}
