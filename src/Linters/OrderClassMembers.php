<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Strategies\MatchOrderingLinter;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;

class OrderClassMembers extends MatchOrderingLinter
{
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
				->withChild(function(PMethodDeclaration $node) {
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
						&& 0 !== stripos($node->getName(), '__');
				}),
			
			'protected method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isProtected($node)
						&& false === $this->isStatic($node)
						&& 0 !== stripos($node->getName(), '__');
				}),
			
			'private method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPrivate($node)
						&& false === $this->isStatic($node)
						&& 0 !== stripos($node->getName(), '__');
				}),
			
			'magic method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return false === $this->isStatic($node)
						&& 0 === stripos($node->getName(), '__');
				}),
		]);
	}
	
	protected function isStatic($node) : bool
	{
		return $this->hasModifier($node, TokenKind::StaticKeyword);
	}
	
	protected function isPublic($node) : bool
	{
		return false === $this->hasModifier($node, TokenKind::ProtectedKeyword)
			&& false === $this->hasModifier($node, TokenKind::PrivateKeyword);
	}
	
	protected function isProtected($node) : bool
	{
		return $this->hasModifier($node, TokenKind::ProtectedKeyword);
	}
	
	protected function isPrivate($node) : bool
	{
		return $this->hasModifier($node, TokenKind::PrivateKeyword);
	}
	
	protected function hasModifier($node, $kind) : bool
	{
		return Collection::make($node->modifiers)
			->contains(function(Token $modifier) use ($kind) {
				return $modifier->kind === $kind;
			});
	}
}
