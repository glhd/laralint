<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\ResolvedName;

class NoDocBlocksOnMigrations extends MatchingLinter
{
	protected function matcher() : Matcher
	{
		return $this->treeMatcher()
			->withChild(function(ClassDeclaration $node) {
				if (!$node->classBaseClause || !$node->classBaseClause->baseClass) {
					return false;
				}
				
				$resolved = $node->classBaseClause->baseClass->getResolvedName();
				$extends = $resolved instanceof ResolvedName
					? $resolved->getFullyQualifiedNameText()
					: (string) $resolved;
				
				return Str::endsWith($extends, 'Migration');
			})
			->withChild(function(MethodDeclaration $node) {
				$comment = $node->getLeadingCommentAndWhitespaceText();
				
				return in_array($node->getName(), ['up', 'down'])
					&& preg_match('/(run|reverse) the migrations/i', $comment);
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		return new Result($this, $nodes->first(), 'Please avoid useless docblocks in migrations.');
	}
}
