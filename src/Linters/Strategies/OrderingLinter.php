<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\CreatesMatchers;
use Glhd\LaraLint\Linters\Matchers\AggregateMatcher;
use Glhd\LaraLint\Linters\Matchers\FirstMatchAggregateMatcher;
use Glhd\LaraLint\Linters\Matchers\TreeMatcher;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;

abstract class OrderingLinter implements Linter
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
			->each(function(Matcher $matcher, $name) {
				$matcher->onMatch(function(Collection $nodes) use ($name) {
					$this->results->push((object) [
						'name' => $name,
						'node' => $nodes->last(),
						'all_nodes' => $nodes,
					]);
				});
			});
		
		$this->expected_order = $matchers->keys()->flip();
		
		$this->matcher = new FirstMatchAggregateMatcher(...$matchers->values()->toArray());
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
				$node_article = $this->getArticle($result->name);
				$expected_article = $this->getArticle($result->expected);
				
				return new Result(
					$result->node,
					ucfirst(trim("{$node_article} {$result->name} should not come after {$expected_article} {$result->expected}."))
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
	
	protected function getArticle($word) : string 
	{
		if (0 === stripos($word, 'the ')) {
			return '';
		}
		
		// This is imperfect but better than nothing
		$vowels = ['a', 'e', 'i', 'o', 'u'];
		if (in_array(strtolower(substr($word, 0, 1)), $vowels)) {
			return 'an';
		}
		
		return 'a';
	}
	
	abstract protected function matchers() : Collection;
}
