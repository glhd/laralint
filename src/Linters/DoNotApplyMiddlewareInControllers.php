<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;

class DoNotApplyMiddlewareInControllers extends MatchingLinter
{
	protected function matcher() : Matcher
	{
		return $this->classMatcher()
			->withChild(ClassDeclaration::class)
			->withBaseClass(function(ClassBaseClause $node) {
				// FIXME: Let's use a filename-aware strategy instead
				return preg_match('/Controller$/', $node->baseClass->getText());
			})
			->withChildMethod('__construct')
			->withChild(MemberAccessExpression::class, '$this->middleware');
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$method_call = $nodes->first(function(Node $node) {
			return $node instanceof MemberAccessExpression;
		});
		
		return new Result(
			$this,
			$method_call,
			'Do not apply middleware in a Controller\'s constructor (use route middleware instead).'
		);
	}
}
