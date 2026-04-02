<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\LintsStringCase;
use Glhd\LaraLint\Linters\Matchers\AggregateMatcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;

class SnakeCaseVariables extends MatchingLinter implements FilenameAwareLinter
{
	use LintsStringCase;

	protected bool $current_file_is_view_component = false;

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

	public function setFilename(string $filename): void
	{
		$this->current_file_is_view_component = str_contains($filename, '/View/Components/');
	}

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

				if (null === $name) {
					return false;
				}

				if ($this->current_file_is_view_component && $this->isConstructorParameter($node)) {
					return !$this->isCamelCase($name);
				}

				return !$this->isSnakeCase($name);
			})
		);
	}

	/** @param Collection<Variable|Parameter> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->last();
		$name = $node->getName();
		$type = $node instanceof Parameter ? 'Parameter' : 'Variable';
		$suggested = $this->toSnakeCase($name);

		if ($node instanceof Parameter && $this->current_file_is_view_component && $this->isConstructorParameter($node)) {
			$suggested = $this->toCamelCase($name);
		}

		return new Result(
			$this,
			$node,
			"{$type} \${$name} should be \${$suggested}"
		);
	}

	protected function isConstructorParameter(Parameter $node): bool
	{
		$parent = $node->parent;

		while (null !== $parent) {
			if ($parent instanceof MethodDeclaration && '__construct' === $parent->getName()) {
				return true;
			}

			$parent = $parent->parent;
		}

		return false;
	}
}
