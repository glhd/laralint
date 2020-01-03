<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Linters\Helpers\FlagsIndividualNodes;
use Glhd\LaraLint\Linters\Helpers\WalksNodeTypes;
use Glhd\LaraLint\Linters\Strategies\SimpleNodeLinter;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;

class PreferFacadesOverHelpers extends SimpleNodeLinter implements ConditionalLinter
{
	use FlagsIndividualNodes, WalksNodeTypes;
	
	protected const FACADE_MAP = [
		'auth' => 'Auth', // TODO
	];
	
	public function enterNode(Node $node) : void
	{
		$name = ltrim($node->callableExpression->getText(), '\\');
		
		foreach (static::FACADE_MAP as $helper => $facade) {
			if ($name === $helper) {
				$this->flagNode($node, "Use the {$facade} facade rather than the {$helper}() helper.");
				return;
			}
		}
	}
	
	protected function walkNodeTypes() : array
	{
		return [
			CallExpression::class,
		];
	}
}
