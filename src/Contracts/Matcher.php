<?php

namespace Glhd\LaraLint\Contracts;

use Microsoft\PhpParser\Node;

interface Matcher
{
	public function enterNode(Node $node) : void;
	
	public function exitNode(Node $node) : void;
	
	public function onMatch(callable $callback);
}
