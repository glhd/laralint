<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Microsoft\PhpParser\Node;

trait CollectsIndividualNodes
{
	protected $individual_results = [];
	
	public function lint() : ResultCollection
	{
		return new ResultCollection($this->individual_results);
	}
	
	protected function collectNode(Node $node, string $message)
	{
		$this->individual_results[] = new Result($node, $message);
		
		return $this;
	}
}
