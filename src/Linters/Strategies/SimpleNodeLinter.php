<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Microsoft\PhpParser\Node;

abstract class SimpleNodeLinter implements Linter
{
	public function exitNode(Node $node) : void
	{
		// Nothing is necessary when exiting a node with the simple strategy
	}
}
