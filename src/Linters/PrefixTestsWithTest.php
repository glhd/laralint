<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Helpers\CollectsIndividualNodes;
use Glhd\LaraLint\Linters\Strategies\ClassMethodLinter;
use Glhd\LaraLint\Nodes\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

class PrefixTestsWithTest extends ClassMethodLinter
{
	use CollectsIndividualNodes;
	
	protected function shouldWalkClass(ClassDeclaration $node) : bool
	{
		return preg_match('/Test$/', $node->getNamespacedName()->getFullyQualifiedNameText());
	}
	
	protected function enterMethod(MethodDeclaration $node) : void
	{
		if (
			false === $node->isStatic() 
			&& $node->isPublic()
			&& 0 !== strpos($node->getName(), 'test_')
		) {
			$this->collectNode($node->node, 'Test methods must start with test_');
		}
	}
}
