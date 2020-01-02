<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Strategies\RuleBasedLinter;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;

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
	
	public static function make() : self 
	{
		return new static([]);
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
	
	public function withChildMethod($matcher = null) : self
	{
		return $this->withChild(function(MethodDeclaration $node) use ($matcher) {
			if (null === $matcher) {
				return true;
			}
			if (is_string($matcher) && $matcher === $node->getName()) {
				return true;
			}
			if ($matcher instanceof \Closure && $matcher($node)) {
				return true;
			}
			return false;
		});
	}
	
	public function withBaseClass($matcher = null) : self
	{
		return $this->withChild(function(ClassBaseClause $node) use ($matcher) {
			if (null === $matcher) {
				return true;
			}
			if (is_string($matcher) && $matcher === $node->baseClass->getText()) {
				return true;
			}
			if ($matcher instanceof \Closure && $matcher($node)) {
				return true;
			}
			return false;
		});
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
