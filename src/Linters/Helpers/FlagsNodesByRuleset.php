<?php

namespace Glhd\LaraLint\Linters\Helpers;

use Closure;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;

trait FlagsNodesByRuleset
{
	use FlagsIndividualNodes;
	
	protected function flagNodeIfAllRulesMatch(Node $node, string $message, array $rules) : void
	{
		if ($this->allRulesMatch($node, $rules)) {
			$this->flagNode($node, $message);
		}
	}
	
	protected function allRulesMatch(Node $node, array $rules) : bool
	{
		return Collection::make($rules)
			->map(function($rule) use ($node) {
				if ($rule instanceof Closure) {
					$rule = $rule($node);
				}
				
				return (bool) $rule;
			})
			->filter(function(bool $result) {
				return false === $result;
			})
			->isEmpty();
	}
}
