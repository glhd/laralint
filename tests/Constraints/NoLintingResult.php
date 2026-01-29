<?php

namespace Glhd\LaraLint\Tests\Constraints;

class NoLintingResult extends LintingResult
{
	public function toString(): string
	{
		$linter = class_basename($this->linter);
		
		if ($this->message) {
			return "{$linter} did not trigger '{$this->message}'";
		}
		
		return "{$linter} did not trigger a result";
	}
	
	protected function matches($other): bool
	{
		return false === parent::matches($other);
	}
}
