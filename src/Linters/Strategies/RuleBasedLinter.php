<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Closure;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Microsoft\PhpParser\Node;
use ReflectionFunction;
use RuntimeException;

class RuleBasedLinter extends SimpleNodeLinter
{
	
	
	/**
	 * @var callable
	 */
	protected $on_match_callback;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $matched_nodes;
	
	public function __construct(array $rules = [])
	{
		$this->rules = new Collection(array_map(function($rule) {
			return $this->parseRule($rule);
		}, $rules));
		
		
	}
	
	
	
	public function onMatch(callable $callback) : self
	{
		$this->on_match_callback = $callback;
		
		return $this;
	}
	
	
	
	public function lint() : ResultCollection
	{
		// If we still have rules to be matched, don't return anything
		if ($this->current_rule < count($this->rules)) {
			return new ResultCollection([]);
		}
		
		if (!is_callable($this->on_match_callback)) {
			throw new RuntimeException('You must provide a closure to RuleBasedLinter::onMatch().');
		}
		
		// Otherwise, call our callback and return the results
		$results = call_user_func_array($this->on_match_callback, $this->matched_nodes->toArray());
		
		if ($results instanceof Result) {
			return new ResultCollection([$results]);
		}
		
		if ($results instanceof ResultCollection) {
			return $results;
		}
		
		throw new RuntimeException('The closure provided to RuleBasedLinter::onMatch must return a Result or ResultCollection.');
	}
	
	
	
	
}
