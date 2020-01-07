<?php

namespace Glhd\LaraLint\Tests\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

class LintingDidNotStart extends Constraint
{
	protected $filename;
	
	public function __construct($filename)
	{
		$this->filename = $filename;
	}
	
	/**
	 * @param \Glhd\LaraLint\Printers\TestPrinter $other
	 * @return bool
	 */
	protected function matches($other): bool
	{
		return false === $other->isStarted($this->filename);
	}
	
	public function toString() : string
	{
		return "linting did not start on '{$this->filename}'";
	}
	
	protected function failureDescription($other): string
	{
		return $this->toString();
	}
}
