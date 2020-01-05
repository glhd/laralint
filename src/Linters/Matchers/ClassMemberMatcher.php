<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Closure;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;

class ClassMemberMatcher extends TreeMatcher
{
	public function withChildMethod($matcher = null) : self
	{
		return $this->withChild(function(MethodDeclaration $node) use ($matcher) {
			if (null === $matcher) {
				return true;
			}
			if (is_string($matcher) && $matcher === $node->getName()) {
				return true;
			}
			if ($matcher instanceof Closure && $matcher($node)) {
				return true;
			}
			return false;
		});
	}
	
	public function withBaseClass($matcher = null) : self
	{
		return $this->withChild(function(ClassBaseClause $node) use ($matcher) {
			if (null === $matcher) {
				return true;
			}
			if (is_string($matcher) && $matcher === $node->baseClass->getText()) {
				return true;
			}
			if ($matcher instanceof Closure && $matcher($node)) {
				return true;
			}
			return false;
		});
	}
}
