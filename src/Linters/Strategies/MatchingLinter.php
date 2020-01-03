<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\CreatesMatchers;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

abstract class MatchingLinter implements Linter
{
	use CreatesMatchers;
	
	/**
	 * The Matcher object to check nodes against
	 *
	 * @var \Glhd\LaraLint\Contracts\Matcher
	 */
	protected $matcher;
	
	/**
	 * A collection of matched node results
	 *
	 * @var \Glhd\LaraLint\ResultCollection
	 */
	protected $results;
	
	public function __construct()
	{
		$this->results = new ResultCollection();
		
		$this->matcher = $this->matcher();
		
		if (method_exists($this->matcher, 'onMatch')) {
			$this->matcher->onMatch(function(Collection $nodes) {
				$result = $this->onMatch($nodes);
				if ($result instanceof Result) {
					$this->results->push($result);
				}
			});
		}
	}
	
	public function enterNode(Node $node) : void
	{
		$this->matcher->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->matcher->exitNode($node);
	}
	
	public function lint() : ResultCollection
	{
		return $this->results;
	}
	
	abstract protected function matcher() : Matcher;
	
	abstract protected function onMatch(Collection $nodes) : ?Result;
}
