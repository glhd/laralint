<?php

namespace Glhd\LaraLint\Commands;

use Glhd\LaraLint\Contracts\Printer;
use Glhd\LaraLint\FileProcessor;
use Glhd\LaraLint\Presets\LaraLint;
use Glhd\LaraLint\Printers\PHP_CodeSniffer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
	protected $signature = 'laralint:install {ide?}';
	
	protected $description = 'Install LaraLint for your IDE';
	
	public function handle()
	{
		$filename = base_path('laralint-phpcs');
		
		$template = "#!/usr/bin/env bash\n\n"
			. "php artisan laralint:lint \"$@\" --formatter=phpcs\n";
		
		file_put_contents($filename, $template);
		chmod($filename, 0777);
		
		$this->info("Installed phpcs-compatible helper to {$filename}. Please configure your IDE to call that file.");
	}
}
