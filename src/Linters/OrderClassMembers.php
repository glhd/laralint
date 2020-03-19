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
	
	protected $class_members_count = 0;
	
	public function enterNode(Node $node) : void
	{
		// This is an ugly hack because the parser doesn't identify anonymous
		// classes as a specific node type, but does identify a new "class members"
		// node when an anonymous class is instantiated. That means that if we're inside
		// an existing class and instantiate a new anonymous class, we'll have encountered
		// *two* "class members" nodes. For the time-being I'm going to just track the count
		// of these nodes and handle that case specially, but it feels like there's got
		// to be a better way to determine if we're inside a 'class definition' context
		// regardless of whether it's anonymous or not.
		if ($node instanceof Node\ClassMembersNode) {
			$this->class_members_count++;
			if ($this->class_members_count > 1) {
				$this->createNewContext();
			}
		}
		
		parent::enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		parent::exitNode($node);
		
		// See note in enterNode for explanation of why this is necessary
		if ($node instanceof Node\ClassMembersNode) {
			if ($this->class_members_count > 1) {
				$this->class_members_count--;
				$this->exitCurrentContext();
			}
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
