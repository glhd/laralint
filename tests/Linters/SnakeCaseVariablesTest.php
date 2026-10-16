<?php

namespace Glhd\LaraLint\Tests\Linters;

use Glhd\LaraLint\Linters\SnakeCaseVariables;
use Glhd\LaraLint\Tests\TestCase;

class SnakeCaseVariablesTest extends TestCase
{
	public function test_it_allows_snake_case_variables(): void
	{
		$source = <<<'END_SOURCE'
		$snake_case = 1;
		$simple = 2;
		$with_numbers_123 = 3;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_camel_case_variables(): void
	{
		$source = <<<'END_SOURCE'
		$camelCase = 1;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Variable $camelCase should be $camel_case');
	}

	public function test_it_flags_pascal_case_variables(): void
	{
		$source = <<<'END_SOURCE'
		$PascalCase = 1;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Variable $PascalCase should be $pascal_case');
	}

	public function test_it_flags_multiple_violations(): void
	{
		$source = <<<'END_SOURCE'
		$camelCase = 1;
		$PascalCase = 2;
		$alsoWrong = 3;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResultCount(3);
	}

	public function test_it_allows_this(): void
	{
		$source = <<<'END_SOURCE'
		class Foo {
			public function bar() {
				return $this->value;
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_allows_superglobals(): void
	{
		$source = <<<'END_SOURCE'
		$get = $_GET['key'];
		$post = $_POST['key'];
		$server = $_SERVER['key'];
		$request = $_REQUEST['key'];
		$session = $_SESSION['key'];
		$env = $_ENV['key'];
		$cookie = $_COOKIE['key'];
		$files = $_FILES['key'];
		$globals = $GLOBALS['key'];
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_allows_underscore_placeholder(): void
	{
		$source = <<<'END_SOURCE'
		[$value, $_] = [1, 2];
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_camel_case_function_parameters(): void
	{
		$source = <<<'END_SOURCE'
		function test($camelCase) {
			return $camelCase;
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Parameter $camelCase should be $camel_case');
	}

	public function test_it_allows_snake_case_function_parameters(): void
	{
		$source = <<<'END_SOURCE'
		function test($snake_case, $simple) {
			return $snake_case + $simple;
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_camel_case_method_parameters(): void
	{
		$source = <<<'END_SOURCE'
		class Foo {
			public function bar($camelParam) {
				return $camelParam;
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Parameter $camelParam should be $camel_param');
	}

	public function test_it_flags_camel_case_class_properties(): void
	{
		$source = <<<'END_SOURCE'
		class Foo {
			public $camelCase;
			protected $anotherOne;
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResultCount(2);
	}

	public function test_it_allows_snake_case_class_properties(): void
	{
		$source = <<<'END_SOURCE'
		class Foo {
			public $snake_case;
			protected $another_one;
			private $simple;
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_allows_leading_underscore(): void
	{
		$source = <<<'END_SOURCE'
		$_private = 1;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_flags_mixed_case_after_underscore(): void
	{
		$source = <<<'END_SOURCE'
		$_camelCase = 1;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Variable $_camelCase should be $_camel_case');
	}

	public function test_it_flags_closure_parameters(): void
	{
		$source = <<<'END_SOURCE'
		$fn = function($camelCase) {
			return $camelCase;
		};
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Parameter $camelCase should be $camel_case');
	}

	public function test_it_flags_arrow_function_parameters(): void
	{
		$source = <<<'END_SOURCE'
		$fn = fn($camelCase) => $camelCase;
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Parameter $camelCase should be $camel_case');
	}

	public function test_it_allows_camel_case_constructor_params_in_view_components(): void
	{
		$source = <<<'END_SOURCE'
		class Alert extends Component {
			public function __construct(
				public $alertType,
				public $dismissible,
			) {}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source, 'app/View/Components/Alert.php')
			->assertNoLintingResults();
	}

	public function test_it_flags_snake_case_constructor_params_in_view_components(): void
	{
		$source = <<<'END_SOURCE'
		class Alert extends Component {
			public function __construct(
				public $alert_type,
			) {}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source, 'app/View/Components/Alert.php')
			->assertLintingResult('Parameter $alert_type should be $alertType');
	}

	public function test_it_still_flags_snake_case_violations_in_view_component_variables(): void
	{
		$source = <<<'END_SOURCE'
		class Alert extends Component {
			public function __construct(
				public $alertType,
			) {}

			public function render() {
				$badVariable = 'test';
				return view('components.alert');
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source, 'app/View/Components/Alert.php')
			->assertLintingResult('Variable $badVariable should be $bad_variable');
	}

	public function test_it_still_flags_non_constructor_params_as_snake_case_in_view_components(): void
	{
		$source = <<<'END_SOURCE'
		class Alert extends Component {
			public function __construct(
				public $alertType,
			) {}

			public function render($badParam) {
				return view('components.alert');
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source, 'app/View/Components/Alert.php')
			->assertLintingResult('Parameter $badParam should be $bad_param');
	}

	public function test_it_ignores_parameters_of_overridden_methods(): void
	{
		$source = <<<'END_SOURCE'
		class Child extends ParentClass
		{
			#[\Override]
			public function handle(string $someValue, int $otherValue): void
			{
				echo $someValue;
				$callback = fn() => $otherValue;
				$other = function() use ($someValue) {
					return $someValue;
				};
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_ignores_parameters_of_overridden_methods_with_imported_attribute(): void
	{
		$source = <<<'END_SOURCE'
		namespace App;

		use Override;

		$handler = new class implements ParentInterface {
			#[Override]
			public function process(array $inputData): array
			{
				return $inputData;
			}
		};
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertNoLintingResults();
	}

	public function test_it_still_flags_local_variables_in_overridden_methods(): void
	{
		$source = <<<'END_SOURCE'
		class Child extends ParentClass
		{
			#[\Override]
			public function handle(string $someValue): void
			{
				$localValue = $someValue;
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResultCount(1)
			->assertLintingResult('Variable $localValue should be $local_value');
	}

	public function test_it_still_flags_parameters_of_methods_without_override(): void
	{
		$source = <<<'END_SOURCE'
		class Child extends ParentClass
		{
			public function handle(string $someValue): void
			{
				echo $someValue;
			}
		}
		END_SOURCE;

		$this->withLinter(SnakeCaseVariables::class)
			->lintSource($source)
			->assertLintingResult('Parameter $someValue should be $some_value')
			->assertLintingResult('Variable $someValue should be $some_value');
	}
}
