<?php

namespace Glhd\LaraLint\Tests\Constraints;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Str;
use PHPUnit\Framework\Constraint\Constraint;

class LintingResultCount extends Constraint
{
	/**
	 * @var int
	 */
	protected $count;
	
	public function __construct(int $count)
	{
		$this->count = $count;
	}
	
	/**
	 * @param \Glhd\LaraLint\ResultCollection $other
	 * @return bool
	 */
	protected function matches($other): bool
	{
		return $other->count() === $this->count;
	}
	
	public function toString() : string
	{
		if (0 === $this->count) {
			return 'no linting results were triggered';
		}
		
		$results = Str::plural('result', $this->count);
		return "{$this->count} linting {$results} were triggered";
	}
	
	protected function failureDescription($other): string
	{
		return $this->toString();
	}
}
