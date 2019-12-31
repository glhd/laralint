<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Linters\Helpers\WalksNodeTypes;
use Glhd\LaraLint\Linters\Strategies\SimpleNodeLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;

class OrderUseStatementsAlphabetically extends SimpleNodeLinter implements ConditionalLinter
{
	use WalksNodeTypes;
	
	/**
	 * @var NamespaceUseDeclaration[]
	 */
	protected $collected_nodes = [];
	
	protected $walk_node_types = [
		NamespaceUseDeclaration::class,
	];
	
	public function enterNode(Node $node) : void
	{
		$this->collected_nodes[] = $node;
	}
	
	public function lint() : ResultCollection
	{
		$all_nodes = new Collection($this->collected_nodes);
		$all_statements = $all_nodes->map->getText();
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
}
