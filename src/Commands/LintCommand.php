<?php

namespace Glhd\LaraLint\Commands;

use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\FileProcessor;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Printers\CompactPrinter;
use Glhd\LaraLint\Printers\ConsolePrinter;
use Glhd\LaraLint\Printers\PHP_CodeSniffer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class LintCommand extends Command
{
	protected $signature = '
		laralint:lint
		
		{ filename?*  : List of filename(s) to lint }
		{ --diff      : Only lint uncommitted git changes }
		{ --only=     : Only apply these linters (comma separated) }
		{ --except=   : Do not apply these linters (comma separated) }
		{ --printer=  : Use a custom printer }
		{ --step      : Step through results (stopping after each issue) }
		{ --step-all  : Step through results (stopping after each file, regardless of whether an issue was found) }
	';
	
	protected $description = 'Run LaraLint on your project';
	
	public function handle()
	{
		$linters = (new LaraLint())->linters();
		
		$linters = $this->applyOnly($linters);
		$linters = $this->applyExcept($linters);
		
		$printer = $this->getPrinter();
		$printer->opening();
		
		$flag_count = 0;
		
		$this->files()
			->each(function(SplFileInfo $file) use ($printer, $linters, &$flag_count) {
				$printer->startFile($file->getRealPath());
				
				$results = FileProcessor::make($file, $linters)->lint();
				$flag_count += $results->count();
				
				$printer->fileResults($file->getRealPath(), $results);
				
				if ($this->shouldStopIterating($results->isNotEmpty())) {
					return false;
				}
			});
		
		$printer->closing();
		
		return $flag_count > 0 ? 1 : 0;
	}
	
	protected function applyOnly(Collection $linters) : Collection
	{
		if ($only = $this->optionArray('only')) {
			return $linters->filter(function($linter) use ($only) {
				foreach ($only as $class_name) {
					if (Str::endsWith($linter, $class_name)) {
						return true;
					}
				}
				
				return false;
			});
		}
		
		return $linters;
	}
	
	protected function applyExcept(Collection $linters) : Collection
	{
		if ($except = $this->optionArray('except')) {
			return $linters->filter(function($linter) use ($except) {
				foreach ($except as $class_name) {
					if (Str::endsWith($linter, $class_name)) {
						return false;
					}
				}
				
				return true;
			});
		}
		
		return $linters;
	}
	
	protected function shouldStopIterating(bool $last_file_had_results) : bool
	{
		if ($last_file_had_results && $this->option('step')) {
			return false === $this->confirm('Continue to next result?', true);
		}
		
		if ($this->option('step-all')) {
			return false === $this->confirm('Continue to next file?', true);
		}
		
		return false;
	}
	
	protected function files() : LazyCollection
	{
		if ($this->option('diff')) {
			return $this->gitDiffFiles();
		}
		
		if ($filename = $this->argument('filename')) {
			// FIXME: We still want to pass this to the finder to apply
			// FIXME: exclusions and pattern matching/etc. 
			return LazyCollection::make($filename)
				->map(function($filename) {
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
	
	protected function optionArray(string $option) : ?Collection
	{
		$options = Collection::make(explode(',', $this->option($option)))
			->map(function($value) {
				return trim($value);
			})
			->filter();
		
		return $options->isEmpty()
			? null
			: $options;
	}
}
