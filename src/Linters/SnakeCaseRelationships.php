<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Concerns\LintsModelRelations;
use Glhd\LaraLint\Linters\Concerns\LintsStringCase;
use Glhd\LaraLint\Linters\Concerns\WalksModels;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\MethodDeclaration;

class SnakeCaseRelationships extends MatchingLinter implements ConditionalLinter
{
	use EvaluatesNodes;
	use LintsModelRelations;
	use LintsStringCase;
	use WalksModels;

	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(fn(MethodDeclaration $node) => $this->isPublic($node)
				&& $this->isRelationship($node)
				&& ! $this->isSnakeCase($node->getName()));
	}

	/** @param Collection<int, MethodDeclaration> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->last();
		$name = $node->getName();
		$suggested = $this->toSnakeCase($name);

		return new Result(
			$this,
			$node,
			"Relationship method {$name}() should be {$suggested}()"
		);
	}
}
