<?php

namespace Glhd\LaraLint\Commands;

use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\FileProcessor;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Printers\ConsolePrinter;
use Glhd\LaraLint\Printers\PHP_CodeSniffer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class LintCommand extends Command
{
	protected $signature = 'laralint:lint {filename?} {--diff} {--printer=} {--step}';
	
	protected $description = 'Run LaraLint on your project';
	
	public function handle()
	{
		// TODO: Load configuration/etc
		$linters = (new LaraLint())->linters()
			->map(function(string $class_name) {
				return new $class_name();
			});
		
		$printer = $this->getPrinter();
		$printer->opening();
		
		$this->files()
			->each(function(SplFileInfo $file) use ($printer, $linters) {
				$printer->startFile($file->getRealPath());
				
				$printer->fileResults(
					$file->getRealPath(),
					FileProcessor::make($file, $linters)->lint()
				);
				
				if ($this->option('step') && false === $this->confirm('Continue?', true)) {
					return false;
				}
			});
		
		$printer->closing();
	}
	
	protected function files() : Collection
	{
		if ($this->option('diff')) {
			return $this->gitDiffFiles();
		}
		
		if ($filename = $this->argument('filename')) {
			return new Collection([new SplFileInfo($filename)]);
		}
		
		return Collection::make(
			Finder::create()
				->files()
				->name('*.php')
				->in(base_path())
		);
	}
	
	protected function gitDiffFiles() : Collection
	{
		$proc = new Process(['git', 'diff', '--name-only'], base_path());
		$proc->run();
		
		if (!$proc->isSuccessful()) {
			throw new RuntimeException($proc->getErrorOutput());
		}
		
		return Collection::make(explode("\n", trim($proc->getOutput())))
			->filter(function($filename) {
				return preg_match('/\.php$/i', $filename);
			})
			->map(function($filename) {
				return new SplFileInfo(base_path($filename));
			});
	}
	
	protected function getPrinter() : Printer
	{
		if ('phpcs' === $this->option('printer')) {
			return new PHP_CodeSniffer($this->getOutput());
		}
		
		return new ConsolePrinter($this->getOutput());
	}
}
