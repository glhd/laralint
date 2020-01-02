<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Strategies\RuleBasedLinter;

class RuleBuilder
{
	protected $linter;
	
	public function __construct(array $rules)
	{
		$this->linter = new RuleBasedLinter($rules);
	}
	
	/**
	 * @param string|callable $node_type
	 * @param callable|string $matcher
	 * @return static
	 */
	public static function startingWithNode($node_type, $matcher = null) : self
	{
		return new static([func_get_args()]);
	}
	
	/**
	 * @param string|callable $node_type
	 * @param callable|string $matcher
	 * @return static
	 */
	public function withChild($node_type, $matcher = null) : self
	{
		$this->linter->addRule(func_get_args());
		
		return $this;
	}
	
	public function onMatch(callable $callback) : self
	{
		$this->linter->onMatch($callback);
		
		return $this;
	}
	
	public function getLinter() : RuleBasedLinter
	{
		return $this->linter;
	}
}
