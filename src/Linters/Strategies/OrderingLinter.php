<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\CreatesMatchers;
use Glhd\LaraLint\Linters\Matchers\FirstMatchAggregateMatcher;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use stdClass;

abstract class OrderingLinter implements Linter
{
	use CreatesMatchers;
	
	/**
	 * This holds a stack of matching contexts. Depending on the implementation,
	 * we may need to push new contexts to the stack to handle nested matching.
	 *
	 * @var array
	 */
	protected $contexts = [];
	
	/**
	 * This is the index of our current context in the stack
	 *
	 * @var int
	 */
	protected $current_context;
	
	/**
	 * This is the order that the matchers are expected to fire in
	 *
	 * @var \Illuminate\Support\Collection
	 */
	protected $expected_order;
	
	public function __construct()
	{
		$this->expected_order = $this->matchers()->keys()->flip();
	}
	
	public function lint() : ResultCollection
	{
		$flags = Collection::make($this->contexts)
			->flatMap(function(stdClass $context) {
				return $this->evaluateAndFlagResults($context->results);
			});
		
		return new ResultCollection($flags);
	}
	
	public function enterNode(Node $node) : void
	{
		$this->currentMatcher()->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->currentMatcher()->exitNode($node);
	}
	
	protected function evaluateAndFlagResults(Collection $results) : Collection
	{
		$current_index = 0;
		
		return $results
			->map(function($result) use (&$current_index) {
				// Determine where we expect this result to be in the order
				// and flag it if it's not in the correct place
				$expected_index = $this->expected_order[$result->name];
				$flagged = $expected_index < $current_index;
				
				// Now compute what we *did* expect to be next
				$result->expected = $this->expected_order->search($current_index);
				
				// If the ordering has changed, update our current index
				if ($expected_index > $current_index) {
					$current_index = $expected_index;
				}
				
				// Only return the result if it was flagged, so that we can filter
				// out non-issues in the next process
				return $flagged
					? $result
					: null;
			})
			->filter()
			->map(function($result) {
				$message = ucfirst(trim("{$result->name} should not come after {$result->expected}."));
				return new Result($this, $result->node, $message);
			});
	}
	
	protected function currentContext() : stdClass
	{
		if (empty($this->contexts)) {
			$this->createNewContext();
		}
		
		return $this->contexts[$this->current_context];
	}
	
	protected function currentMatcher() : Matcher
	{
		return $this->currentContext()->matcher;
	}
	
	protected function createNewContext() : stdClass
	{
		$context = (object) [
			'results' => new Collection(),
		];
		
		$matchers = $this->matchers()
			->each(function(Matcher $matcher, $name) use (&$context) {
				$matcher->onMatch(function(Collection $nodes) use ($name, &$context) {
					$context->results->push((object) [
						'name' => $name,
						'node' => $nodes->last(),
						'all_nodes' => $nodes,
					]);
				});
			});
		
		$context->matcher = new FirstMatchAggregateMatcher(...$matchers->values()->toArray());
		
		$this->contexts[] = $context;
		$this->current_context = array_key_last($this->contexts);
		
		return $context;
	}
	
	protected function exitCurrentContext() : void
	{
		// TODO: This may cause issues if the indexes get out of sync. Probably
		// won't be an issue, but worth considering.
		$this->current_context--;
	}
	
	abstract protected function matchers() : Collection;
}
