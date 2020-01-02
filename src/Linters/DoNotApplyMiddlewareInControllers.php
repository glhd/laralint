<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Strategies\RuleBuilderLinter;
use Glhd\LaraLint\Result;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

class DoNotApplyMiddlewareInControllers extends RuleBuilderLinter
{
	protected function buildRules() : RuleBuilder
	{
		return RuleBuilder::startingWithNode(ClassDeclaration::class)
			->withBaseClass(function(ClassBaseClause $node) {
				return preg_match('/Controller$/', $node->baseClass->getText());
			})
			->withChildMethod('__construct')
			->withChild(MemberAccessExpression::class, '$this->middleware')
			->onMatch(function($class_declaration, $base_clause, $constructor, $middleware_call) {
				return new Result(
					$middleware_call,
					'Do not apply middleware in the controller\'s constructor method.'
				);
			});
	}
}
