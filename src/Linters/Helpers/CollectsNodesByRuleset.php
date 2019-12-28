<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

trait CollectsNodesByRuleset
{
	use CollectsIndividualNodes;
	
	protected function collectNodeIfAllRulesMatch(Node $node, string $message, bool ...$rules) : void
	{
		if (!Collection::make($rules)->contains(false)) {
			$this->collectNode($node, $message);
		}
	}
}
