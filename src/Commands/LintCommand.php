<?php

namespace Glhd\LaraLint\Commands;

use AppendIterator;
use Glhd\LaraLint\Contracts\Preset;
use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Printers\CompactPrinter;
use Glhd\LaraLint\Printers\ConsolePrinter;
use Glhd\LaraLint\Printers\PHP_CodeSniffer;
use Glhd\LaraLint\Runners\SplFileInfoRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

// TODO: This command needs major work. Right now it's just a quick-and-dirty tool
// to get LaraLint running. Eventually it'll need to refactoring and improved configuration/etc.
class LintCommand extends Command
{
	protected $signature = '
		laralint:lint
		
		{ targets?*              : Filenames and/or directories to lint }
		{ --diff                 : Only lint uncommitted git changes }
		{ --only=*               : Only apply these linter(s) }
		{ --except=*             : Do not apply these linter(s) }
		{ --printer=             : Use a custom printer }
		{ --step                 : Step through results (stopping after each issue) }
		{ --step-all             : Step through results (stopping after each file, regardless of whether an issue was found) }
		{ --ignore-parse-errors  : Fail gracefully if syntax errors are encountered }
	';
	
	protected $description = 'Run LaraLint on your project';
	
	/**
	 * @var \Glhd\LaraLint\Contracts\Printer
	 */
	protected $printer;
	
	public function handle(): int
	{
		try {
			$linters = $this->linters();
			$printer = $this->printer();
			
			$printer->opening();
			$flag_count = 0;
			$target_files = $this->targets()[0];
			$excluded_files = Collection::make(Config::get('laralint.excluded_files', []))
				->reject(function($file) use ($target_files) {
					foreach ($target_files as $target_file) {
						if ($target_file->getFilename() === $file) {
							return true;
						}
					}
					return false;
                });
			
			$this->files()
				->each(function(SplFileInfo $file) use ($printer, $linters, &$flag_count, $excluded_files) {
					if ($excluded_files->contains(function($excluded) use ($file) {
						return $file->getRealPath() === $excluded
							|| $file->getFilename() === $excluded;
					})) {
						return;
					}

					$printer->startFile($file->getRealPath());
					
					$results = SplFileInfoRunner::file($file)->run($linters);
					$flag_count += $results->count();
					
					$printer->fileResults($file->getRealPath(), $results);
					
					if ($this->shouldStopIterating($results->isNotEmpty())) {
						return false;
					}
				});
			
			$printer->closing();
			
			return $flag_count > 0
				? 2
				: 0;
		} catch (\Throwable $e) {
			return $this->option('ignore-parse-errors')
				? 0
				: 3;
		}
	}
	
	public function setPrinter(Printer $printer) : self
	{
		$this->printer = $printer;
		
		return $this;
	}
	
	protected function preset() : Preset
	{
		$preset_contract = Preset::class;
		$preset = Config::get('laralint.preset', LaraLint::class);
		
		if (!is_subclass_of($preset, $preset_contract, true)) {
			throw new RuntimeException("'{$preset}' should implement the '{$preset_contract}' interface.");
		}
		
		return new $preset();
	}
	
	protected function linters() : Collection
	{
		$linters = $this->preset()->linters();
		
		if (!empty($only = $this->option('only'))) {
			$linters = $linters->filter(function(string $linter) use ($only) {
				return Str::endsWith($linter, $only);
			});
		}
		
		if (!empty($except = $this->option('except'))) {
			$linters = $linters->reject(function(string $linter) use ($except) {
				return Str::endsWith($linter, $except);
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
		[$files, $directories] = $this->targets();
		
		// If we were provided a list of target files, just return that list 
		if ($files->isNotEmpty() && $directories->isEmpty()) {
			return LazyCollection::make($files);
		}
		
		// If no directories were provided, then just use the base path
		if ($directories->isEmpty()) {
			$directories = new Collection([new SplFileInfo(App::basePath())]);
		}

		// Get excluded directories, overriding if they're
		// explicitly whitelisted in the targets argument
		$exclude = Collection::make(Config::get('laralint.excluded_directories', []))
			->reject(function($path) use ($directories) {
				foreach ($directories as $directory_path) {
					if ($directory_path === $path) {
						return true;
					}
				}
				return false;
			})
			->toArray();
		
		// Set up base finder
		$finder = Finder::create()
			->files()
			->exclude($exclude)
			->name('*.php')
			->in($directories->map->getRealPath()->toArray());
		
		// If we have filenames + directories to lint, then iterate over both, starting with files
		if ($files->isNotEmpty()) {
			return LazyCollection::make(
				tap(new AppendIterator(), function(AppendIterator $iterator) use ($finder, $files) {
					$iterator->append($files->getIterator());
					$iterator->append($finder->getIterator());
				})
			);
		}
		
		// Otherwise, just iterate over our finder
		return LazyCollection::make($finder);
	}
	
	/**
	 * @return Collection[]
	 */
	protected function targets() : array
	{
		// If we were asked for git changes, just return those
		if ($this->option('diff')) {
			return [$this->gitDiffFiles(), new Collection()];
		}
		
		// Otherwise, split the targets into files and directories
		$targets = Collection::make($this->argument('targets'))
			->map(function($target) {
				if (!file_exists($target)) {
					$target = getcwd().DIRECTORY_SEPARATOR.ltrim($target, DIRECTORY_SEPARATOR);
				}
				
				return new SplFileInfo($target);
			});
		
		$files = $targets->filter(function(SplFileInfo $file) {
			return $file->isFile();
		});
		
		$directories = $targets->filter(function(SplFileInfo $file) {
			return $file->isDir();
		});
		
		return [$files, $directories];
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
	
	protected function printer() : Printer
	{
		if (null === $this->printer) {
			switch ($this->option('printer')) {
				case 'phpcs':
					$this->printer = new PHP_CodeSniffer($this->getOutput());
					break;
				
				case 'compact':
					$this->printer = new CompactPrinter($this->getOutput());
					break;
				
				default:
					$this->printer = new ConsolePrinter($this->getOutput());
					break;
			}
		}
		
		return $this->printer;
	}
}
