<?php

namespace Glhd\LaraLint\Tests\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

class LintingDidNotStart extends Constraint
{
	public function __construct(
		protected string $filename
	)
	{
	}
	
	public function toString(): string
	{
		return "linting did not start on '{$this->filename}'";
	}
	
	/**
	 * @param \Glhd\LaraLint\Printers\TestPrinter $other
	 * @return bool
	 */
	protected function matches($other): bool
	{
		return false === $other->isStarted($this->filename);
	}
	
	protected function failureDescription($other): string
	{
		return $this->toString();
	}
}
