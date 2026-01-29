<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Throwable;

trait LintsModelRelations
{
	protected function isRelationship(MethodDeclaration $node): bool
	{
		if ($node->returnTypeList) {
			$relationships = Config::get('laralint.relationships', []);

			foreach ($node->returnTypeList->children as $returnType) {
				foreach ($relationships as $class_name) {
					try {
						$qualified_return_type = $returnType->getResolvedName()->getFullyQualifiedNameText();

						if ($class_name === $qualified_return_type) {
							return true;
						}
					} catch (Throwable $exception) {
						// Ignore and use fallback
					}
				}
			}
		}

		return Str::contains($node->getText(), Config::get('laralint.relationship_heuristics', []));
	}
}
