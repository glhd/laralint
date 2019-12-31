<?php

namespace Glhd\LaraLint\Contracts;

use Microsoft\PhpParser\Node;

interface ConditionalLinter
{
	public function shouldWalkNode(Node $node) : bool;
}
