<?php

namespace Glhd\LaraLint\Tests\Constraints;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Result;
use PHPUnit\Framework\Constraint\Constraint;

class LintingResult extends Constraint
{
	/**
	 * @var string
	 */
	protected $linter;
	
	/**
	 * @var string
	 */
	protected $message;
	
	/**
	 * @var bool
	 */
	protected $match_substring;
	
	public function __construct($linter, string $message = null, bool $match_substring = false)
	{
		$this->linter = $linter instanceof Linter
			? get_class($linter)
			: $linter;
		$this->message = $message;
		$this->match_substring = $match_substring;
	}
	
	/**
	 * @param \Glhd\LaraLint\ResultCollection $other
	 * @return bool
	 */
	protected function matches($other): bool
	{
		return $other->contains(function(Result $result) {
			if (get_class($result->getLinter()) !== $this->linter) {
				return false;
			}
			
			if ($this->match_substring && $this->message) {
				return false !== strpos($result->getMessage(), $this->message);
			}
			
			if ($this->message) {
				return $result->getMessage() === $this->message;
			}
			
			return true;
		});
	}
	
	public function toString() : string
	{
		$linter = class_basename($this->linter);
		
		if ($this->message) {
			return "{$linter} triggered '{$this->message}'";
		}
		
		return "{$linter} triggered a result";
	}
	
	protected function failureDescription($other): string
	{
		return $this->toString();
	}
}
