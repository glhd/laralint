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
	 * @var \Illuminate\Support\Collection
	 */
	protected $rules;
	
	protected $current_rule = 0;
	
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
		
		$this->matched_nodes = new Collection();
	}
	
	public function addRule($rule) : self
	{
		$this->rules->push($this->parseRule($rule));
		
		return $this;
	}
	
	public function onMatch(callable $callback) : self
	{
		$this->on_match_callback = $callback;
		
		return $this;
	}
	
	public function enterNode(Node $node) : void
	{
		if ($this->nodeMatchesCurrentRule($node)) {
			$this->current_rule++;
			$this->matched_nodes[] = $node;
		}
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
	
	protected function nodeMatchesCurrentRule(Node $node) : bool 
	{
		return isset($this->rules[$this->current_rule])
			&& call_user_func($this->rules[$this->current_rule], $node);
	}
	
	protected function parseRule(array $rule) : Closure
	{
		// Parse the rule arguments into a signature for easier matching
		$signature = Collection::make($rule)
			->map(function($argument) {
				$type = gettype($argument);
				return 'object' === $type
					? class_basename($argument)
					: $type;
			})
			->implode(', ');
		
		// Match based on the signature
		switch ($signature) {
			case 'string':
				return function(Node $node) use ($rule) {
					return get_class($node) === $rule[0];
				};
			
			case 'Closure':
				// FIXME: Better error handling
				$reflect = new ReflectionFunction($rule[0]);
				$type_hint = (string) $reflect->getParameters()[0]->getType();
				return function(Node $node) use ($rule, $type_hint) {
					return get_class($node) === $type_hint
						&& (bool) $rule[0]($node);
				};
			
			case 'string, string':
				return function(Node $node) use ($rule) {
					return get_class($node) === $rule[0]
						&& $node->getText() === $rule[1];
				};
			
			case 'string, Closure':
				return function(Node $node) use ($rule) {
					return get_class($node) === $rule[0]
						&& $rule[1]($node);
				};
				
			default:
				throw new InvalidArgumentException("Unknown rule signature: '$signature'");
		}
	}
}
