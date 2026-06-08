<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Token;

class AvoidHigherOrderCollectionProxies extends MatchingLinter
{
	/**
	 * The methods Laravel exposes as higher-order collection proxies. Accessing
	 * any of these as a property (rather than calling them) returns a proxy that
	 * forwards a subsequent member access to every item in the collection.
	 *
	 * @see \Illuminate\Collections\Traits\EnumeratesValues::$proxies
	 */
	protected const array PROXIES = [
		'average', 'avg', 'contains', 'doesntContain', 'each', 'every', 'filter',
		'first', 'flatMap', 'groupBy', 'hasMany', 'hasSole', 'keyBy', 'last', 'map',
		'max', 'min', 'partition', 'percentage', 'reject', 'skipUntil', 'skipWhile',
		'some', 'sortBy', 'sortByDesc', 'sum', 'takeUntil', 'takeWhile', 'unique',
		'unless', 'until', 'when',
	];

	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(function(MemberAccessExpression $node) {
				// We only care about a proxy method accessed as a property, e.g.
				// the `->each` in `$collection->each->delete()`. A real method
				// call (`$collection->each(...)`) parses with this node as the
				// callable of a CallExpression, so requiring the parent to chain
				// another member access off of us excludes it cleanly.
				$member_name = $node->memberName;

				if (! $member_name instanceof Token) {
					return false;
				}

				if (! in_array($member_name->getText($node->getFileContents()), static::PROXIES, true)) {
					return false;
				}

				return $node->parent instanceof MemberAccessExpression
					&& $node->parent->dereferencableExpression === $node;
			});
	}

	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->first();
		$method = $node->memberName->getText($node->getFileContents());

		return new Result(
			$this,
			$node,
			"Call ->{$method}() with a closure rather than using the magic higher-order collection proxy."
		);
	}
}
