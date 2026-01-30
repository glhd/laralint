<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\LintsStringCase;
use Glhd\LaraLint\Linters\Concerns\SkipsViewComponents;
use Glhd\LaraLint\Linters\Matchers\AggregateMatcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Parameter;

class SnakeCaseVariables extends MatchingLinter implements ConditionalLinter, FilenameAwareLinter
{
	use LintsStringCase;
	use SkipsViewComponents;

	protected array $excluded = [
		'this',
		'_',
		'_GET',
		'_POST',
		'_SERVER',
		'_REQUEST',
		'_SESSION',
		'_ENV',
		'_COOKIE',
		'_FILES',
		'GLOBALS',
		'argc',
		'argv',
		'http_response_header',
	];

	protected function matcher(): Matcher
	{
		return new AggregateMatcher(
			$this->treeMatcher()->withChild(function(Variable $node) {
				$name = $node->getName();

				return null !== $name
					&& !in_array($name, $this->excluded, true)
					&& !$this->isSnakeCase($name);
			}),
			$this->treeMatcher()->withChild(function(Parameter $node) {
				$name = $node->getName();

				return null !== $name && !$this->isSnakeCase($name);
			})
		);
	}

	/** @param Collection<Variable|Parameter> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->last();
		$name = $node->getName();
		$suggested = $this->toSnakeCase($name);
		$type = $node instanceof Parameter ? 'Parameter' : 'Variable';

		return new Result(
			$this,
			$node,
			"{$type} \${$name} should be \${$suggested}"
		);
	}
}
