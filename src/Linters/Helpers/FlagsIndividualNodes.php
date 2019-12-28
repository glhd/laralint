<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Microsoft\PhpParser\Node;

trait FlagsIndividualNodes
{
	protected $individual_results = [];
	
	public function lint() : ResultCollection
	{
		return new ResultCollection($this->individual_results);
	}
	
	protected function flagNode(Node $node, string $message) : void
	{
		$this->individual_results[] = new Result($node, $message);
	}
}
