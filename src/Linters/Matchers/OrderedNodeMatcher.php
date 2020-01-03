<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Closure;
use Glhd\LaraLint\Contracts\Matcher;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Microsoft\PhpParser\Node;
use ReflectionFunction;
use stdClass;
use Throwable;

class OrderedNodeMatcher implements Matcher
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
	protected $current_rule_index = 0;
	
	/**
	 * This is the callback that gets triggered when all rules have been matched
	 *
	 * @var callable
	 */
	protected $on_match_callback;
	
	public function __construct()
	{
		$this->rules = new Collection();
	}
	
	public function withChild($matcher) : self
	{
		$this->parseAndAddRule(func_get_args());
		
		return $this;
	}
	
	public function onMatch(callable $callback) : void
	{
		$this->on_match_callback = $callback;
	}
	
	public function enterNode(Node $node) : void
	{
		if (!$this->nodeMatchesCurrentRule($node)) {
			return;
		}
		
		// Update current rule before incrementing
		$this->currentRule()->node = $node;
		
		// Then increment current rule
		$this->current_rule_index++;
		
		// If we've matched all rules, call the callback
		if ($this->current_rule_index >= $this->rules->count()) {
			call_user_func($this->on_match_callback, $this->rules->map->node);
			
			// Once we've called the "on match" callback, reset the matcher
			$this->current_rule_index = 0;
			
			// Reset all our rules
			$this->rules = $this->rules->map(function(stdClass $rule) {
				$rule->node = null;
				return $rule;
			});
		}
	}
	
	public function exitNode(Node $node) : void
	{
		$exiting_index = $this->rules->search(function($rule) use ($node) {
			return $node === $rule->node;
		});
		
		foreach ($this->rules as $index => $rule) {
			if ($index >= $exiting_index) {
				$rule->node = null;
			}
		}
		
		$this->current_rule_index = max(0, $exiting_index - 1);
	}
	
	protected function currentRule() : ?stdClass
	{
		return $this->rules->get($this->current_rule_index);
	}
	
	protected function nodeMatchesCurrentRule(Node $node) : bool
	{
		return ($current_rule = $this->currentRule())
			&& call_user_func($current_rule->callback, $node);
	}
	
	protected function parseAndAddRule(array $rule) : self
	{
		$this->rules->push((object) [
			'node' => null,
			'depth' => 0,
			'callback' => $this->parseRule($rule),
		]);
		
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
