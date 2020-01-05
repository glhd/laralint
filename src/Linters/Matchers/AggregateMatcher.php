<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Matchers\Concerns\HasOnMatchCallbacks;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

class AggregateMatcher implements Matcher
{
	use HasOnMatchCallbacks;
	
	protected $matchers;
	
	public function __construct(Matcher ...$matcher)
	{
		$this->matchers = Collection::make($matcher)
			->each(function(Matcher $matcher) {
				$matcher->onMatch(function(Collection $nodes) {
					$this->triggerMatch($nodes);
				});
			});
	}
	
	public function enterNode(Node $node) : void
	{
		$this->matchers->each->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->matchers->each->exitNode($node);
	}
}
