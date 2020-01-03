<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\MatchOrderingLinter;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;

class OrderClassMembers extends MatchOrderingLinter
{
	use EvaluatesNodes;
	
	protected function matchers() : Collection
	{
		return new Collection([
			'trait use' => $this->orderedMatcher()
				->withChild(TraitUseClause::class),
			
			'public constant' => $this->orderedMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isPublic($node);
				}),
			
			'protected constant' => $this->orderedMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isProtected($node);
				}),
			
			'private constant' => $this->orderedMatcher()
				->withChild(function(ClassConstDeclaration $node) {
					return $this->isPrivate($node);
				}),
			
			'public static property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPublic($node)
						&& $this->isStatic($node);
				}),
			
			'protected static property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isProtected($node)
						&& $this->isStatic($node);
				}),
			
			'private static property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPrivate($node)
						&& $this->isStatic($node);
				}),
			
			'public property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPublic($node)
						&& false === $this->isStatic($node);
				}),
			
			'protected property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isProtected($node)
						&& false === $this->isStatic($node);
				}),
			
			'private property' => $this->orderedMatcher()
				->withChild(function(PropertyDeclaration $node) {
					return $this->isPrivate($node)
						&& false === $this->isStatic($node);
				}),
			
			'constructor' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return '__construct' === $node->getName();
				}),
			
			'public static method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPublic($node)
						&& $this->isStatic($node);
				}),
			
			'protected static method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isProtected($node)
						&& $this->isStatic($node);
				}),
			
			'private static method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPrivate($node)
						&& $this->isStatic($node);
				}),
			
			'public method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPublic($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			'protected method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isProtected($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			'private method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPrivate($node)
						&& false === $this->isStatic($node)
						&& 0 !== strpos($node->getName(), '__');
				}),
			
			'magic method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					$name = $node->getName();
					return false === $this->isStatic($node)
						&& '__construct' !== $name
						&& 0 === strpos($name, '__');
				}),
		]);
	}
}
