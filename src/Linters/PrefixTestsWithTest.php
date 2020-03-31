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
	
	protected $data_providers = [];
	
	public function setFilename(string $filename) : void
	{
		$this->active = false !== strpos($filename, 'tests/');
	}
	
	protected function matcher() : Matcher
	{
		return $this->classMatcher()
			->withChild(function(ClassDeclaration $node) {
				if ($this->active && Str::endsWith($node->getNamespacedName(), 'Test')) {
					// Parse all @dataProvider annotations to whitelist as allowed public methods
					preg_match_all('/@data[Pp]rovider\s*([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/m', $node->getFullText(), $matches);
					$this->data_providers = $matches[1] ?? [];
					return true;
				}
				
				return false;
			})
			->withChild(function(MethodDeclaration $node) {
				return false === $node->isStatic()
					&& false === $node->hasModifier(TokenKind::ProtectedKeyword)
					&& false === $node->hasModifier(TokenKind::PrivateKeyword)
					&& 0 !== strpos($node->getName(), 'test_')
					&& !in_array($node->getName(), $this->data_providers);
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
