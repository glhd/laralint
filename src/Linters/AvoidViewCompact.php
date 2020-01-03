<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;

class AvoidViewCompact extends MatchingLinter
{
	protected function matcher() : Matcher
	{
		return $this->orderedMatcher()
			->withChild(function(CallExpression $node) {
				return 0 === strcasecmp($node->callableExpression->getText(), 'view')
					&& false !== stripos($node->getText(), 'compact(');
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		return new Result(
			$nodes->first(),
			'Provide an array to the view rather than using compact().'
		);
	}
}
