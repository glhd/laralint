<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;

class OrderUseStatementsAlphabetically extends MatchingLinter
{
	/**
	 * @var NamespaceUseDeclaration[]
	 */
	protected $collected_nodes = [];
	
	public function lint() : ResultCollection
	{
		$all_nodes = new Collection($this->collected_nodes);
		$all_statements = $all_nodes->map(function(Node $node) {
			return strtolower($node->getText());
		});
		$ordered_statements = $all_statements->sort();
		
		if ($all_statements->implode(' ') !== $ordered_statements->implode(' ')) {
			return new ResultCollection(
				$all_nodes->map(function(NamespaceUseDeclaration $node) {
					return new Result($node, 'Use statements should be ordered alphabetically.');
				})
			);
		}
		
		return new ResultCollection([]);
	}
	
	protected function matcher() : Matcher
	{
		return $this->orderedMatcher()->withChild(NamespaceUseDeclaration::class);
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$this->collected_nodes[] = $nodes->first();
		
		return null;
	}
}
