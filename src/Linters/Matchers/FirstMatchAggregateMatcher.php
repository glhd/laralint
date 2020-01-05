<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Closure;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Matchers\Concerns\HasOnMatchCallbacks;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use IteratorAggregate;
use Microsoft\PhpParser\Node;
use PHPUnit\Framework\MockObject\Builder\Match;
use ReflectionFunction;
use stdClass;
use Throwable;

/**
 * Unlike the AggregateMatcher, this will stop matching when it
 * finds the first match per node.
 */
class FirstMatchAggregateMatcher implements Matcher
{
	use HasOnMatchCallbacks;
	
	protected $matchers;
	
	protected $last_matched_matcher;
	
	public function __construct(Matcher ...$matcher)
	{
		$this->matchers = Collection::make($matcher)
			->each(function(Matcher $matcher) {
				$matcher->onMatch(function(Collection $nodes) use ($matcher) {
					if (null === $this->last_matched_matcher) {
						$this->last_matched_matcher = $matcher;
						$this->triggerMatch($nodes);
					}
				});
			});
	}
	
	public function enterNode(Node $node) : void
	{
		// Before entering a node, we'll reset the aggregate state
		$this->last_matched_matcher = null;
		
		$this->matchers->each(function(Matcher $matcher) use ($node) {
			// If we've already found a match, stop iterating
			if ($this->last_matched_matcher) {
				return false;
			}
			
			// Otherwise, enter the node
			$matcher->enterNode($node);
		});
	}
	
	public function exitNode(Node $node) : void
	{
		$this->matchers->each(function(Matcher $matcher) use ($node) {
			// If this is the matcher that triggered the onMatch callback
			// for this iteration, run its exitNode() just in case that performs
			// some kind of clean up.
			// Alternatively, if no matcher has hit yet, run the exitNode() call
			// on all our matchers (until we hit one or have looped thru them all).
			if (null === $this->last_matched_matcher || $matcher === $this->last_matched_matcher) {
				$matcher->exitNode($node);
			}
		});
	}
}
