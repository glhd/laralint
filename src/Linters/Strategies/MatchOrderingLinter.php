<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Linters\Matchers\AggregateMatcher;
use Glhd\LaraLint\Linters\Matchers\OrderedNodeMatcher;
use Glhd\LaraLint\Linters\Strategies\Concerns\CreatesMatchers;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

abstract class MatchOrderingLinter implements Linter
{
	use CreatesMatchers;
	
	/**
	 * The Matcher object to check nodes against
	 *
	 * @var \Glhd\LaraLint\Contracts\Matcher
	 */
	protected $matcher;
	
	protected $expected_order;
	
	protected $results;
	
	public function __construct()
	{
		$this->results = new Collection();
		
		$matchers = $this->matchers()
			->map(function(OrderedNodeMatcher $matcher, $name) {
				return $matcher->onMatch(function(Collection $nodes) use ($name) {
					$this->results->push((object) [
						'name' => $name,
						'node' => $nodes->first(),
						'all_nodes' => $nodes,
					]);
				});
			});
		
		$this->expected_order = $matchers->keys()->flip();
		
		$this->matcher = new AggregateMatcher(...$matchers->values()->toArray());
	}
	
	public function lint() : ResultCollection
	{
		$current_index = 0;
		
		$flags = $this->results
			->map(function($result) use (&$current_index) {
				$result->expected_index = $this->expected_order[$result->name];
				$result->flagged = $result->expected_index < $current_index;
				$result->expected = $this->expected_order->search($current_index);
				
				if ($result->expected_index > $current_index) {
					$current_index = $result->expected_index;
				}
				
				return $result;
			})
			->filter(function($result) {
				return $result->flagged;
			})
			->map(function($result) {
				return new Result(
					$result->node,
					"A {$result->name} should not come after a {$result->expected}."
				);
			});
		
		return new ResultCollection($flags);
	}
	
	public function enterNode(Node $node) : void
	{
		$this->matcher->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->matcher->exitNode($node);
	}
	
	abstract protected function matchers() : Collection;
}
