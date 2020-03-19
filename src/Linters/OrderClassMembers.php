<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\OrderingLinter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;

class OrderClassMembers extends OrderingLinter
{
	use EvaluatesNodes;
	
	public function enterNode(Node $node) : void
	{
		// Start a new context whenever we encounter a new class (even anonymous ones)
		if ($this->isClassDeclarationOrAnonymousClass($node)) {
			$this->createNewContext();
		}
		
		parent::enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		parent::exitNode($node);
		
		// If we're exiting a class node, step out of the current context
		if ($this->isClassDeclarationOrAnonymousClass($node)) {
			$this->exitCurrentContext();
		}
	}
	
	protected function matchers() : Collection
	{
		return new Collection([
			'a trait' => $this->treeMatcher()
				->withChild(TraitUseClause::class),
			
			'a public constant' => $this->treeMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isPublic($node);
				}),
			
			'a protected constant' => $this->treeMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isProtected($node);
				}),
			
			'a private constant' => $this->treeMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isPrivate($node);
				}),
			
			'a public static property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPublic($node)
						&& $this->isStatic($node);
				}),
			
			'a protected static property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isProtected($node)
						&& $this->isStatic($node);
				}),
			
			'a private static property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPrivate($node)
						&& $this->isStatic($node);
				}),
			
			'a public property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPublic($node)
						&& false === $this->isStatic($node);
				}),
			
			'a protected property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isProtected($node)
						&& false === $this->isStatic($node);
				}),
			
			'a private property' => $this->treeMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPrivate($node)
						&& false === $this->isStatic($node);
				}),
			
			'an abstract method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isAbstract($node);
				}),
			
			'a public static method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPublic($node)
						&& $this->isStatic($node);
				}),
			
			'a protected static method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isProtected($node)
						&& $this->isStatic($node);
				}),
			
			'a private static method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPrivate($node)
						&& $this->isStatic($node);
				}),
			
			'the constructor' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return '__construct' === $node->getName();
				}),
			
			'the setUp method' => $this->treeMatcher()
				->withChild(function(ClassDeclaration $node) {
					return Str::endsWith($node->getNamespacedName(), 'Test');
				})
				->withChild(function(MethodDeclaration $node) {
					return 'setUp' === $node->getName();
				}),
			
			'the tearDown method' => $this->treeMatcher()
				->withChild(function(ClassDeclaration $node) {
					return Str::endsWith($node->getNamespacedName(), 'Test');
				})
				->withChild(function(MethodDeclaration $node) {
					return 'tearDown' === $node->getName();
				}),
			
			'a public method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPublic($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			'a protected method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isProtected($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			'a private method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPrivate($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			// TODO: Not so sure about this rule
			// 'a magic method' => $this->treeMatcher()
			// 	->withChild(function(MethodDeclaration $node) {
			// 		return 0 === strpos($node->getName(), '__');
			// 	}),
		]);
	}
}
