<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Strategies\RuleBuilderLinter;
use Glhd\LaraLint\Result;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

class DoNotApplyMiddlewareInControllers extends RuleBuilderLinter
{
	protected function buildRules() : RuleBuilder
	{
		return RuleBuilder::startingWithNode(ClassDeclaration::class)
			->withChild(ClassBaseClause::class, function(ClassBaseClause $node) {
				return false !== stripos($node->getText(), 'controller');
			})
			->withChild(MethodDeclaration::class, function(MethodDeclaration $node) {
				// TODO: We want a better way to do this
				return false !== stripos($node->getText(), '__construct');
			})
			->withChild(MemberAccessExpression::class, '$this->middleware')
			->onMatch(function($class_declaration, $base_clause, $constructor, $middleware_call) {
				return new Result(
					$middleware_call,
					'Do not apply middleware in the controller\'s constructor method.'
				);
			});
	}
}
