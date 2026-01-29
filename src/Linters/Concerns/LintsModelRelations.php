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

			foreach ($node->returnTypeList->children as $return_type) {
				try {
					$qualified_return_type = $return_type->getResolvedName()->getFullyQualifiedNameText();
				} catch (Throwable) {
					continue;
				}

				if (in_array($qualified_return_type, $relationships)) {
					return true;
				}
			}
		}

		return Str::contains($node->getText(), Config::get('laralint.relationship_heuristics', []));
	}
}
