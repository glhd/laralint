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
	
	/**
	 * @var int
	 */
	protected $other_count;
	
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
		$this->other_count = $other->count();
		
		return $this->other_count === $this->count;
	}
	
	public function toString() : string
	{
		if (0 === $this->count) {
			return 'no linting results were triggered';
		}
		
		$were = 1 === $this->count ? 'was' : 'were';
		$results = Str::plural('result', $this->count);
		return "{$this->count} linting {$results} {$were} triggered";
	}
	
	protected function failureDescription($other): string
	{
		$were = 1 === $this->other_count ? 'was' : 'were';
		$results = Str::plural('result', $this->other_count);
		
		return $this->toString()." ({$this->other_count} {$results} {$were} triggered)";
	}
}
