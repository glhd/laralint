<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Illuminate\Support\Collection;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;

trait EvaluatesNodes
{
	
	protected function isPublic($node) : bool
	{
		return false === $this->hasModifier($node, TokenKind::ProtectedKeyword)
			&& false === $this->hasModifier($node, TokenKind::PrivateKeyword);
	}
	
	protected function isPrivate($node) : bool
	{
		return $this->hasModifier($node, TokenKind::PrivateKeyword);
	}
	
	protected function isProtected($node) : bool
	{
		return $this->hasModifier($node, TokenKind::ProtectedKeyword);
	}
	
	protected function isStatic($node) : bool
	{
		return $this->hasModifier($node, TokenKind::StaticKeyword);
	}
	
	protected function isAbstract($node) : bool
	{
		return $this->hasModifier($node, TokenKind::AbstractKeyword);
	}
	
	protected function hasModifier($node, $kind) : bool
	{
		return Collection::make($node->modifiers)
			->contains(function(Token $modifier) use ($kind) {
				return $modifier->kind === $kind;
			});
	}
}
