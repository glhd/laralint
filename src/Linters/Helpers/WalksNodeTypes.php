<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Microsoft\PhpParser\Node;

trait WalksNodeTypes
{
	public function shouldWalkNode(Node $node) : bool
	{
		foreach ($this->walkNodeTypes() as $node_type) {
			if ($node instanceof $node_type) {
				return true;
			}
		}
		
		return false;
	}
	
	abstract protected function walkNodeTypes() : array;
}
