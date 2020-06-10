<?php

namespace Glhd\LaraLint\Tests\Linters;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Linters\OrderClassMembers;
use Glhd\LaraLint\Linters\PreferFullyRestfulControllers;

class PreferFullyRestfulControllersTest extends TestCase
{
	public function test_it_allows_a_controller_with_only_restful_methods() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
			}
			
			public function callAction()
			{
			}		
			
			public function index()
			{
			}
			
			public function create()
			{
			}
			
			public function store()
			{
			}
			
			public function show()
			{
			}
			
			public function edit()
			{
			}
			
			public function update()
			{
			}
			
			public function destroy()
			{
			}
			
			public function validator()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
	
	public function test_it_expects_restful_methods_in_a_specific_order() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function create()
			{
			}
			
			public function index()
			{
			}
			
			public function store()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertLintingResult();
	}
	
	public function test_it_allows_one_non_restful_method() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
			}
			
			public function create()
			{
			}
			
			public function store()
			{
			}
			
			public function pancakes()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
	
	public function test_it_allows_protected_non_restful_methods() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
			}
			
			public function create()
			{
			}
			
			public function store()
			{
			}
			
			protected function pancakes()
			{
			}
			
			protected function syrup()
			{
			}
			
			protected function butter()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
	
	public function test_it_does_not_allow_more_than_one_non_restful_method() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function __construct()
			{
			}
			
			public function create()
			{
			}
			
			public function store()
			{
			}
			
			public function pancakes()
			{
			}
			
			public function syrup()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertLintingResult();
	}
	
	public function test_it_allows_a_controller_with_all_non_restful_methods() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function pancakes()
			{
			}
			
			public function syrup()
			{
			}
			
			public function butter()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
	
	public function test_it_considers_a_controller_restful_when_two_restful_methods_are_present() : void
	{
		$source = <<<'END_SOURCE'
		class TestController
		{
			public function index()
			{
			}
			
			public function pancakes()
			{
			}
			
			public function syrup()
			{
			}
		}	
		END_SOURCE;
		
		$this->withLinter(PreferFullyRestfulControllers::class)
			->lintSource($source, 'Controllers/TestController.php')
			->assertNoLintingResults();
	}
}
