<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Linters\Helpers\FlagsNodesByRuleset;
use Glhd\LaraLint\Linters\Strategies\ClassMethodLinter;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

class PrefixTestsWithTest extends ClassMethodLinter implements FilenameAwareLinter
{
	use FlagsNodesByRuleset;
	
	protected $active = true;
	
	public function setFilename(string $filename) : void
	{
		$this->active = false !== strpos($filename, base_path('tests'));
	}
	
	protected function shouldWalkClass(ClassDeclaration $node) : bool
	{
		return $this->active
			&& preg_match('/Test$/', $node->getNamespacedName()->getFullyQualifiedNameText());
	}
	
	protected function enterMethod(MethodDeclaration $node) : void
	{
		$this->flagNodeIfAllRulesMatch($node, 'Test methods must start with test_', [
			false === $node->isStatic(),
			false === $node->hasModifier(TokenKind::ProtectedKeyword),
			false === $node->hasModifier(TokenKind::PrivateKeyword),
			0 !== strpos($node->getName(), 'test_'),
		]);
	}
}
