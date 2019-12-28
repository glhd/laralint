<?php

namespace Glhd\LaraLint\Support;

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
	    $this->publishes([
		    __DIR__.'/../../config/laralint.php' => config_path('laralint.php'),
	    ]);
    	
        if ($this->app->runningInConsole()) {
            $this->commands([
                LintCommand::class,
                InstallCommand::class,
            ]);
        }
    }
}
