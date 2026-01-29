<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\WalksModels;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;

class NoGuardedOrFillable extends MatchingLinter implements ConditionalLinter
{
	use WalksModels;
	
	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(fn(PropertyDeclaration $node) => $this->hasProhibitedProperty($node));
	}
	
	/** @param Collection<int, PropertyDeclaration> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->last();
		$name = $this->getProhibitedPropertyName($node);
		
		return new Result(
			$this,
			$node,
			"Models should not use the \${$name} property"
		);
	}
	
	protected function hasProhibitedProperty(PropertyDeclaration $node): bool
	{
		return null !== $this->getProhibitedPropertyName($node);
	}
	
	protected function getProhibitedPropertyName(PropertyDeclaration $node): ?string
	{
		foreach ($node->propertyElements->getChildNodes() as $element) {
			$variable = $element instanceof AssignmentExpression
				? $element->leftOperand
				: $element;
			
			if ($variable instanceof Variable) {
				$name = $variable->getName();
				if (in_array($name, ['guarded', 'fillable'], true)) {
					return $name;
				}
			}
		}
		
		return null;
	}
}
