<?php

namespace Glhd\LaraLint\Tests\Constraints;

class NoLintingResult extends LintingResult
{
	public function toString(): string
	{
		$linter = class_basename($this->linter);
		
		$message = $this->message
			? "{$linter} did not trigger '{$this->message}'"
			: "{$linter} did not trigger a result";
		
		if ($this->line) {
			$message .= " (on line {$this->line})";
		}
		
		return $message;
	}
	
	protected function matches($other): bool
	{
		return false === parent::matches($other);
	}
}
