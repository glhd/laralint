<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Linters\Helpers\FlagsIndividualNodes;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;

class PreferFacadesOverHelpers implements Linter
{
	use FlagsIndividualNodes;
	
	public function enterNode(Node $node) : void
	{
		if (!$node instanceof CallExpression) {
			return;
		}
		
		$name = ltrim($node->callableExpression->getText(), '\\');
		if ('auth' === $name) {
			$this->flagNode($node, 'Use the Auth facade rather than the auth() helper.');
		}
	}
	
	public function exitNode(Node $node) : void
	{
		
	}
}
