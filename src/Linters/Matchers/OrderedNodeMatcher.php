<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Closure;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;
use ReflectionFunction;
use Throwable;

class OrderedNodeMatcher
{
	/**
	 * This holds all the parsed rules that we're matching against
	 * 
	 * @var \Illuminate\Support\Collection
	 */
	protected $rules;
	
	/**
	 * This tracks the current rule that we're matching against
	 * 
	 * @var int 
	 */
	protected $current_rule = 0;
	
	/**
	 * This holds all the nodes that we've matched during this iteration
	 * 
	 * @var \Illuminate\Support\Collection 
	 */
	protected $matched_nodes;
	
	/**
	 * This is the callback that gets triggered when all rules have been matched
	 * 
	 * @var callable
	 */
	protected $on_match_callback;
	
	public function __construct()
	{
		$this->rules = new Collection();
		$this->matched_nodes = new Collection();
	}
	
	public function withChild($matcher) : self
	{
		$this->parseAndAddRule(func_get_args());
		
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
			if ($matcher instanceof Closure && $matcher($node)) {
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
			if ($matcher instanceof Closure && $matcher($node)) {
				return true;
			}
			return false;
		});
	}
	
	public function onMatch(callable $callback) : self
	{
		$this->on_match_callback = $callback;
		
		return $this;
	}
	
	public function enterNode(Node $node) : void
	{
		if (!$this->nodeMatchesCurrentRule($node)) {
			return;
		}
		
		$this->current_rule++;
		$this->matched_nodes->push($node);
		
		// If we've matched all rules, call the callback
		if ($this->current_rule > $this->rules->count()) {
			call_user_func_array($this->on_match_callback, $this->matched_nodes->toArray());
			
			// Once we've called the "on match" callback, reset the matcher
			$this->current_rule = 0;
			$this->matched_nodes = new Collection();
		}
	}
	
	protected function nodeMatchesCurrentRule(Node $node) : bool
	{
		return $this->rules->has($this->current_rule)
			&& call_user_func($this->rules[$this->current_rule], $node);
	}
	
	protected function parseAndAddRule(array $rule) : self
	{
		$this->rules->push($this->parseRule($rule));
		
		return $this;
	}
	
	protected function parseRule(array $rule) : Closure
	{
		// Parse the rule arguments into a string representation of the signature.
		// This lets us declaratively "overload" the rule definitions based on
		// the argument types that were provided.
		$signature = Collection::make($rule)
			->map(function($argument) {
				$type = gettype($argument);
				return 'object' === $type
					? class_basename($argument)
					: $type;
			})
			->implode(', ');
		
		// Generate a node-matching closure based on the rule signature
		// and arguments provided.
		switch ($signature) {
			case 'string':
				return function(Node $node) use ($rule) {
					return get_class($node) === $rule[0];
				};
			
			case 'Closure':
				return function(Node $node) use ($rule) {
					return get_class($node) === $this->getExpectedNodeType($rule[0])
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
	
	/**
	 * Use reflection to determine the type of node the closure has
	 * type-hinted as its input.
	 *
	 * @param \Closure $matcher
	 * @return string
	 */
	protected function getExpectedNodeType(Closure $matcher) : string
	{
		try {
			$reflect = new ReflectionFunction($matcher);
			if ($reflect->getNumberOfParameters() > 0) {
				$parameter = $reflect->getParameters()[0];
				if ($parameter->hasType()) {
					return (string) $parameter->getType();
				}
			}
		} catch (Throwable $exception) {
		}
		
		return Node::class;
	}
}
