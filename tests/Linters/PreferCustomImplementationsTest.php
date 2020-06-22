<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\PreferCustomImplementations;

class PreferCustomImplementationsTest extends TestCase
{
	public function test_it_flags_direct_usage_of_formrequest() : void
	{
		$source = <<<'END_SOURCE'
		use Illuminate\Foundation\Http\FormRequest;
		class TestRequest extends FormRequest
		{
		}
		END_SOURCE;
		
		$this->withLinter(PreferCustomImplementations::class)
			->lintSource($source, 'TestRequest.php')
			->assertLintingResult("use 'App\Http\Requests\Request'", true);
	}
	
	public function test_it_does_not_flag_app_request() : void
	{
		$source = <<<'END_SOURCE'
		use App\Http\Requests\Request;
		class TestRequest extends Request
		{
		}
		END_SOURCE;
		
		$this->withLinter(PreferCustomImplementations::class)
			->lintSource($source, 'TestRequest.php')
			->assertNoLintingResult();
	}
	
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
			->lintSource($source, 'TestRequest.php')
			->assertNoLintingResult();
	}
}
