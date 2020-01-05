<?php

namespace Glhd\LaraLint\Support;

use Glhd\LaraLint\Commands\DumpCommand;
use Glhd\LaraLint\Commands\InstallCommand;
use Glhd\LaraLint\Commands\LintCommand;
use Illuminate\Support\ServiceProvider;

class LaraLintServiceProvider extends ServiceProvider
{
    public function register()
    {
	    $this->mergeConfigFrom(__DIR__.'/../../config/laralint.php', 'laralint');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
	        if (method_exists($this->app, 'configPath')) {
		        $this->publishes([
			        __DIR__.'/../../config/laralint.php' => config_path('laralint.php'),
		        ], ['laralint', 'laralint-config']);
	        }
        	
            $this->commands([
                LintCommand::class,
                InstallCommand::class,
	            DumpCommand::class,
            ]);
        }
    }
}
