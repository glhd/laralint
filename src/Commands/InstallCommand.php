<?php

namespace Glhd\LaraLint\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
	protected $signature = 'laralint:install {ide?}';
	
	protected $description = 'Install LaraLint for your IDE';
	
	public function handle()
	{
		$filename = base_path('laralint-phpcs');
		
		$template = "#!/usr/bin/env bash\n\n"
			. "php artisan laralint:lint \"$1\" --printer=phpcs\n";
		
		file_put_contents($filename, $template);
		chmod($filename, 0777);
		
		$this->info("Installed phpcs-compatible helper to {$filename}. Please configure your IDE to call that file.");
	}
}
