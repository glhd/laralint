<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Linters\Helpers\CollectsIndividualNodes;
use Glhd\LaraLint\ResultCollection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;

class PreferFacadesOverHelpers implements Linter
{
	use CollectsIndividualNodes;
	
	public function enterNode(Node $node) : void
	{
		if (!$node instanceof CallExpression) {
			return;
		}
		
		$name = ltrim($node->callableExpression->getText(), '\\');
		if ('auth' === $name) {
			$this->collectNode($node, 'Use the Auth facade rather than the auth() helper.');
		}
	}
	
	public function exitNode(Node $node) : void
	{
		
	}
}
