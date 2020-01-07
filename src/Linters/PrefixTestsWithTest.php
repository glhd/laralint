<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

class PrefixTestsWithTest extends MatchingLinter implements FilenameAwareLinter
{
	protected $active = true;
	
	public function setFilename(string $filename) : void
	{
		$this->active = false !== strpos($filename, 'tests/');
	}
	
	protected function matcher() : Matcher
	{
		return $this->classMatcher()
			->withChild(function(ClassDeclaration $node) {
				return $this->active && Str::endsWith($node->getNamespacedName(), 'Test');
			})
			->withChild(function(MethodDeclaration $node) {
				return false === $node->isStatic()
					&& false === $node->hasModifier(TokenKind::ProtectedKeyword)
					&& false === $node->hasModifier(TokenKind::PrivateKeyword)
					&& 0 !== strpos($node->getName(), 'test_');
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		return new Result(
			$this,
			$nodes->last(),
			'Test methods must start with test_'
		);
	}
}
