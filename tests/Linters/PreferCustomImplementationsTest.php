<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\PreferCustomImplementations;

class PreferCustomImplementationsTest extends TestCase
{
	public function test_it_flags_direct_usage_of_laravel_controller() : void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Routing\Controller;
		class TestController extends Controller
		{
		}
		END_SOURCE;
		
		$this->withLinter(PreferCustomImplementations::class)
			->lintSource($source, 'TestController.php')
			->assertLintingResult("use 'App\Http\Controllers\Controller'", true);
	}
	
	public function test_it_does_not_flag_app_controller() : void
	{
		$source = <<<'END_SOURCE'
		use App\Http\Controllers\Controller;
		class TestController extends Controller
		{
		}
		END_SOURCE;
		
		$this->withLinter(PreferCustomImplementations::class)
			->lintSource($source, 'TestController.php')
			->assertNoLintingResult();
	}
	
	public function test_it_does_not_flag_base_controller() : void
	{
		$source = <<<'END_SOURCE'
		namespace App\Http\Controllers;
		use Illuminate\Routing\Controller as BaseController;
		class Controller extends BaseController
		{
		}
		END_SOURCE;
		
		$this->withLinter(PreferCustomImplementations::class)
			->lintSource($source, 'Controller.php')
			->assertNoLintingResult();
	}
}
