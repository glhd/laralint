<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Concerns\LintsModelRelations;
use Glhd\LaraLint\Linters\Concerns\WalksModels;
use Glhd\LaraLint\Linters\Strategies\OrderingLinter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node\MethodDeclaration;

class OrderModelMembers extends OrderingLinter implements ConditionalLinter
{
	use EvaluatesNodes;
	use LintsModelRelations;
	use WalksModels;

	protected function matchers(): Collection
	{
		return new Collection([
			'boot methods' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return in_array($node->getName(), ['booting', 'boot', 'booted'])
						&& $this->isStatic($node);
				}),

			'cast method' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return 'casts' === $node->getName()
						&& $this->isProtected($node);
				}),

			'a mutator' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return Str::startsWith($node->getName(), ['get', 'set'])
						&& Str::endsWith($node->getName(), 'Attribute');
				}),

			'a relationship' => $this->treeMatcher()
				->withChild(fn(MethodDeclaration $node) => $this->isPublic($node)
					&& $this->isRelationship($node)),

			'a scope' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					if (0 === strpos($node->getName(), 'scope')) {
						return true;
					}

					return $this->hasAttribute($node, 'Illuminate\\Database\\Eloquent\\Attributes\\Scope');
				}),
		]);
	}
}
