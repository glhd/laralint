<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Nodes\MethodDeclaration;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration as BaseMethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

abstract class ClassMethodLinter implements Linter
{
	protected $classDeclaration;
	
	public function enterNode(Node $node) : void
	{
		if ($node instanceof ClassDeclaration && $this->shouldWalkClass($node)) {
			$this->classDeclaration = $node;
		}
		
		if (null !== $this->classDeclaration && $node instanceof BaseMethodDeclaration) {
			$this->enterMethod(new MethodDeclaration($node));
		}
	}
	
	public function leaveNode(Node $node) : void
	{
		if (null !== $this->classDeclaration && $node instanceof BaseMethodDeclaration) {
			$this->leaveMethod(new MethodDeclaration($node));
		}
		
		if ($node === $this->classDeclaration) {
			$this->classDeclaration = null;
		}
	}
	
	protected function shouldWalkClass(ClassDeclaration $node) : bool 
	{
		return true;
	}
	
	abstract protected function enterMethod(MethodDeclaration $node) : void;
	
	protected function leaveMethod(MethodDeclaration $node) : void
	{
		
	}
}
