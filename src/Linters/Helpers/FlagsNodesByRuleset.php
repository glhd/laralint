<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

trait FlagsNodesByRuleset
{
	use FlagsIndividualNodes;
	
	protected function flagNodeIfAllRulesMatch(Node $node, string $message, bool ...$rules) : void
	{
		if (!Collection::make($rules)->contains(false)) {
			$this->flagNode($node, $message);
		}
	}
}
