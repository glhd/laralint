<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Illuminate\Support\Facades\Config;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\ResolvedName;

trait WalksModels
{
	protected bool $active = false;

	public function shouldWalkNode(Node $node): bool
	{
		if ($node instanceof ClassBaseClause && $node->baseClass) {
			$resolved = $node->baseClass->getResolvedName();
			$extends = $resolved instanceof ResolvedName
				? $resolved->getFullyQualifiedNameText()
				: (string) $resolved;

			$this->active = in_array($extends, Config::get('laralint.models', []));
		}

		return $this->active;
	}
}
