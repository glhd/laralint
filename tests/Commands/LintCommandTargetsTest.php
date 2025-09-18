<?php

namespace Glhd\LaraLint\Tests\Commands;

use Galahad\LaraLint\Tests\TestCase;
use Glhd\LaraLint\Commands\LintCommand;
use Glhd\LaraLint\Printers\TestPrinter;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\App;

class LintCommandTargetsTest extends TestCase
{
	/**
	 * @var \Glhd\LaraLint\Printers\TestPrinter
	 */
	protected $printer;
	
	/**
	 * @var \Glhd\LaraLint\Commands\LintCommand
	 */
	protected $command;
	
	protected function setUp() : void
	{
		parent::setUp();
		
		$this->printer = new TestPrinter();
		$this->command = (new LintCommand())->setPrinter($this->printer);
		
		Application::starting(function(Application $application) {
			$application->add($this->command);
		});
		
		App::setBasePath(realpath(__DIR__.'/../fixtures/finder'));
		
		chdir(realpath(__DIR__.'/../fixtures/finder'));
	}
	
	public function test_it_should_apply_default_paths_without_arguments() : void
	{
		$this->artisan(LintCommand::class)
			->assertExitCode(0);
		
		$this->printer->assertStarted('a.php')
			->assertStarted('b.php')
			->assertStarted('dir1/1a.php')
			->assertStarted('dir1/1b.php')
			->assertStarted('dir2/2a.php')
			->assertStarted('dir2/2b.php')
			->assertDidNotStart('_ide_helper.php')
			->assertDidNotStart('vendor/vendor.php');
	}
	
	public function test_it_should_only_use_provided_files_if_only_filenames_are_provided() : void
	{
		$this->artisan(LintCommand::class, ['targets' => ['a.php', 'dir1/1a.php']])
			->assertExitCode(0);
		
		$this->printer->assertStarted('a.php')
			->assertDidNotStart('b.php')
			->assertStarted('dir1/1a.php')
			->assertDidNotStart('dir1/1b.php')
			->assertDidNotStart('dir2/2a.php')
			->assertDidNotStart('dir2/2b.php')
			->assertDidNotStart('_ide_helper.php')
			->assertDidNotStart('vendor/vendor.php');
	}
	
	public function test_files_and_dirs_can_be_passed_as_targets() : void
	{
		$this->artisan(LintCommand::class, ['targets' => ['a.php', 'dir2']])
			->assertExitCode(0);
		
		$this->printer->assertStarted('a.php')
			->assertDidNotStart('b.php')
			->assertDidNotStart('dir1/1a.php')
			->assertDidNotStart('dir1/1b.php')
			->assertStarted('dir2/2a.php')
			->assertStarted('dir2/2b.php')
			->assertDidNotStart('_ide_helper.php')
			->assertDidNotStart('vendor/vendor.php');
	}
	
	public function test_excluded_directories_can_be_explicitly_set_as_a_target() : void
	{
		$this->artisan(LintCommand::class, ['targets' => ['vendor']])
			->assertExitCode(0);
		
		$this->printer->assertDidNotStart('a.php')
			->assertDidNotStart('b.php')
			->assertDidNotStart('dir1/1a.php')
			->assertDidNotStart('dir1/1b.php')
			->assertDidNotStart('dir2/2a.php')
			->assertDidNotStart('dir2/2b.php')
			->assertDidNotStart('_ide_helper.php')
			->assertStarted('vendor/vendor.php');
	}
	
	public function test_files_in_excluded_directories_can_be_explicitly_set_as_a_target() : void
	{
		$this->artisan(LintCommand::class, ['targets' => ['vendor/vendor.php']])
			->assertExitCode(0);
		
		$this->printer->assertDidNotStart('a.php')
			->assertDidNotStart('b.php')
			->assertDidNotStart('dir1/1a.php')
			->assertDidNotStart('dir1/1b.php')
			->assertDidNotStart('dir2/2a.php')
			->assertDidNotStart('dir2/2b.php')
			->assertDidNotStart('_ide_helper.php')
			->assertStarted('vendor/vendor.php');
	}

    public function test_files_in_excluded_files_can_be_explicitly_set_as_a_target() : void
	{
		$this->artisan(LintCommand::class, ['targets' => ['_ide_helper.php']])
			->assertExitCode(0);

		$this->printer->assertDidNotStart('a.php')
			->assertDidNotStart('b.php')
			->assertDidNotStart('dir1/1a.php')
			->assertDidNotStart('dir1/1b.php')
			->assertDidNotStart('dir2/2a.php')
			->assertDidNotStart('dir2/2b.php')
			->assertDidNotStart('vendor/vendor.php')
			->assertStarted('_ide_helper.php');
	}
}
