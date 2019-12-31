<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Microsoft\PhpParser\Node;

trait WalksNodeTypes
{
	public function shouldWalkNode(Node $node) : bool
	{
		if (!isset($this->walk_node_types)) {
			return false;
		}
		
		foreach ($this->walk_node_types as $node_type) {
			if ($node instanceof $node_type) {
				return true;
			}
		}
		
		return false;
	}
}
