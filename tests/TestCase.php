<?php

namespace Galahad\LaraLint\Tests;

use Glhd\LaraLint\Support\LaraLintServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
	protected function getPackageProviders($app)
	{
		return [
			LaraLintServiceProvider::class,
		];
	}
}
