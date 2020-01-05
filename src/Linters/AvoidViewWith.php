<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;

class AvoidViewWith extends MatchingLinter
{
	protected function matcher() : Matcher
	{
		return $this->treeMatcher()
			->withChild(function(CallExpression $node) {
				return false !== stripos($node->getText(), '->with(');
			})
			->withChild(function(MemberAccessExpression $node) {
				return 0 === stripos($node->dereferencableExpression->getText(), 'view(');
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		return new Result(
			$nodes->first(),
			'Provide an array to the view rather than using ->with() statements.'
		);
	}
}
