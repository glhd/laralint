<?php

namespace Glhd\LaraLint\Tests\Constraints;

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
	 * @var \Glhd\LaraLint\ResultCollection
	 */
	protected $other;
	
	public function __construct(int $count)
	{
		$this->count = $count;
	}
	
	public function toString() : string
	{
		if (0 === $this->count) {
			return 'no linting results were triggered';
		}
		
		$were = 1 === $this->count
			? 'was'
			: 'were';
		$results = Str::plural('result', $this->count);
		return "{$this->count} linting {$results} {$were} triggered";
	}
	
	/**
	 * @param \Glhd\LaraLint\ResultCollection $other
	 * @return bool
	 */
	protected function matches($other) : bool
	{
		$this->other = $other;
		
		return $this->other->count() === $this->count;
	}
	
	protected function failureDescription($other) : string
	{
		$other_count = $this->other->count();
		
		$were = 1 === $other_count
			? 'was'
			: 'were';
		$results = Str::plural('result', $other_count);
		
		$linting_messages = $this->other
			->toBase()
			->map(function(Result $result) {
				return ' - "'.$result->getMessage().'" on line '.$result->getLine();
			})
			->implode("\n");
		
		return $this->toString()." ({$other_count} {$results} {$were} triggered):\n{$linting_messages}";
	}
}
