<?php

namespace Glhd\LaraLint\Tests\Constraints;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Result;
use PHPUnit\Framework\Constraint\Constraint;

class NoLintingResult extends LintingResult
{
	protected function matches($other): bool
	{
		return false === parent::matches($other);
	}
	
	public function toString() : string
	{
		$linter = class_basename($this->linter);
		
		if ($this->message) {
			return "{$linter} did not trigger '{$this->message}'";
		}
		
		return "{$linter} did not trigger a result";
	}
}
