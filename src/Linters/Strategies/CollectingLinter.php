<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

abstract class CollectingLinter extends MatchingLinter
{
	protected $collected_nodes;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->collected_nodes = new Collection();
	}
	
	public function lint() : ResultCollection
	{
		return $this->lintCollectedNodes($this->collected_nodes);
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$this->collected_nodes->push($this->reduceMatchedNodes($nodes));
		
		return null;
	}
	
	protected function reduceMatchedNodes(Collection $nodes) : Node
	{
		return $nodes->first();
	}
	
	abstract protected function lintCollectedNodes(Collection $nodes) : ResultCollection; 
}
