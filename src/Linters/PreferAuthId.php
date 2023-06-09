<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;

class PreferAuthId extends MatchingLinter
{
	protected function matcher() : Matcher
	{
		return $this->treeMatcher()
			->withChild(function(MemberAccessExpression $node) {
				return Str::endsWith($node->getText(), '::user()->id');
			})
			->withChild(function(CallExpression $node) {
				return Str::endsWith($node->callableExpression->getText(), '::user');
			})
			->withChild(function(QualifiedName $node) {
				return ($resolved_name = $node->getResolvedName())
					&& ($qualified_name = $resolved_name->getFullyQualifiedNameText())
					&& (Auth::class === $qualified_name || 'Auth' === $qualified_name);
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		return new Result(
			$this,
			$nodes->first(),
			'Please use Auth::id() rather than Auth::user()->id.'
		);
	}
}
