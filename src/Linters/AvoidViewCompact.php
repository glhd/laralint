<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Linters\Helpers\FlagsIndividualNodes;
use Glhd\LaraLint\Linters\Helpers\WalksNodeTypes;
use Glhd\LaraLint\Linters\Strategies\SimpleNodeLinter;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;

class AvoidViewCompact extends SimpleNodeLinter implements ConditionalLinter
{
	use FlagsIndividualNodes, WalksNodeTypes;
	
	/**
	 * @param \Microsoft\PhpParser\Node\Expression\CallExpression $node
	 */
	public function enterNode(Node $node) : void
	{
		if (
			0 === strcasecmp($node->callableExpression->getText(), 'view')
			&& false !== stripos($node->getText(), 'compact(')
		) {
			$this->flagNode($node, 'Provide an array to the view rather than using compact().');
		}
	}
	
	protected function walkNodeTypes() : array
	{
		return [
			CallExpression::class,
		];
	}
}
