<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

abstract class ClassMethodLinter implements Linter
{
	protected $classDeclaration;
	
	public function enterNode(Node $node) : void
	{
		if ($node instanceof ClassDeclaration && $this->shouldWalkClass($node)) {
			$this->classDeclaration = $node;
		}
		
		if (null !== $this->classDeclaration && $node instanceof MethodDeclaration) {
			$this->enterMethod($node);
		}
	}
	
	public function exitNode(Node $node) : void
	{
		if (null !== $this->classDeclaration && $node instanceof MethodDeclaration) {
			$this->exitMethod($node);
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
	
	protected function exitMethod(MethodDeclaration $node) : void
	{
		
	}
}
