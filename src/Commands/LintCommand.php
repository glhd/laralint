<?php

namespace Glhd\LaraLint\Commands;

use Glhd\LaraLint\FileProcessor;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Result;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\DiagnosticsProvider;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\PositionUtilities;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class LintCommand extends Command
{
	protected $signature = 'laralint:lint {--path=} {--diff}';
	
	protected $description = 'Run LaraLint on your project';
	
	public function handle()
	{
		// TODO: Load configuration/etc
		$linters = (new LaraLint())->linters()
			->map(function(string $class_name) {
				return new $class_name();
			});
		
		$this->files()
			->each(function(SplFileInfo $file) use ($linters) {
				$this->getOutput()->newLine();
				$this->info($file->getRealPath());
				
				$results = FileProcessor::make($file)->lint($linters);
				
				if ($results->isNotEmpty()) {
					$results->each(function(Result $result) {
						$this->warn($result->getMessage());
					});
				} else {
					$this->info('No issues.');
				}
			});
	}
	
	protected function files() : Collection
	{
		if ($this->option('diff')) {
			return $this->gitDiffFiles();
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
}
