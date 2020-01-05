<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\CollectingLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;

class OrderUseStatementsAlphabetically extends CollectingLinter
{
	public function lintCollectedNodes(Collection $all_nodes) : ResultCollection
	{
		$results = new ResultCollection();
		
		$last_statement = '';
		foreach ($all_nodes as $i => $node) {
			$statement = strtolower($node->getText());
			
			if ($i > 0 && $last_statement > $statement) {
				$results->push(new Result($node, 'Use statements should be ordered alphabetically.'));
				break;
			}
			
			$last_statement = $statement;
		}
		
		return $results;
	}
	
	protected function matcher() : Matcher
	{
		return $this->treeMatcher()->withChild(NamespaceUseDeclaration::class);
	}
}
