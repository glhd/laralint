<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use SplObjectStorage;

class PreferFacadesOverHelpers extends MatchingLinter
{
	protected const FACADE_MAP = [
		'auth' => 'Auth', // TODO
	];
	
	/**
	 * @var \SplObjectStorage
	 */
	protected $node_map;
	
	protected function matcher() : Matcher
	{
		$this->node_map = new SplObjectStorage();
		
		return $this->treeMatcher()
			->withChild(function(CallExpression $node) {
				$name = $node->callableExpression->getText();
				
				foreach (static::FACADE_MAP as $helper => $facade) {
					if ($name === $helper) {
						$this->node_map[$node] = $helper;
						return true;
					}
				}
				
				return false;
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$node = $nodes->first();
		$helper = $this->node_map[$node];
		$facade = static::FACADE_MAP[$helper];
		
		return new Result(
			$this,
			$node, 
			"Use the {$facade} facade rather than the {$helper}() helper."
		);
	}
}
