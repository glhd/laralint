<?php

namespace Glhd\LaraLint\Tests\Constraints;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Result;
use PHPUnit\Framework\Constraint\Constraint;

class LintingResult extends Constraint
{
	protected string $linter;
	
	public function __construct(
		Linter|string $linter,
		protected ?string $message = null,
		protected bool $match_substring = false,
		protected ?int $line = null,
	) {
		$this->linter = $linter instanceof Linter ? $linter::class : $linter;
	}
	
	public function toString(): string
	{
		$linter = class_basename($this->linter);
		
		$message = $this->message
			? "{$linter} triggered '{$this->message}'"
			: "{$linter} triggered a result";
		
		if ($this->line) {
			$message .= " (on line {$this->line})";
		}
		
		return $message;
	}
	
	/** @param \Glhd\LaraLint\ResultCollection $other */
	protected function matches($other): bool
	{
		return $other->contains(function(Result $result) {
			if ($result->getLinter()::class !== $this->linter) {
				return false;
			}
			
			if ($this->line && $result->line !== $this->line) {
				return false;
			}
			
			if ($this->match_substring && $this->message) {
				return str_contains($result->getMessage(), $this->message);
			}
			
			if ($this->message) {
				return $result->getMessage() === $this->message;
			}
			
			return true;
		});
	}
	
	protected function failureDescription($other): string
	{
		return $this->toString();
	}
}
