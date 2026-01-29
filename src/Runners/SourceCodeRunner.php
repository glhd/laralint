<?php

namespace Glhd\LaraLint\Runners;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Contracts\Runner;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Glhd\LaraLint\Support\IgnoreDirectiveParser;
use Glhd\LaraLint\Support\IgnoreDirectives;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;

abstract class SourceCodeRunner implements Runner
{
	protected int $depth = -1;

	protected Collection $linters;

	protected ?IgnoreDirectives $ignoreDirectives = null;

	public function run(Collection $linters): ResultCollection
	{
		$source = $this->source();

		// Parse ignore directives before linting
		$this->ignoreDirectives = (new IgnoreDirectiveParser())->parse($source);

		// Early bail-out for file-level ignore
		if ($this->ignoreDirectives->shouldIgnoreFile()) {
			return new ResultCollection();
		}

		$this->setLinters($linters);

		// TODO: Allow for blade compilation
		$ast = (new Parser())->parseSourceFile($source);

		// We'll walk the entire tree and perform any filtering
		// or collection logic on all the nodes and tokens
		$this->walk($ast->getChildNodes());

		// Then we'll run the "lint" stage on all the linters to collect all our results
		$results = new ResultCollection(
			$this->linters->flatMap(fn(Linter $linter) => $linter->lint())
		);

		// Filter out ignored results
		return $this->filterIgnoredResults($results);
	}
	
	protected function setLinters(Collection $linters): self
	{
		$this->linters = $linters->map(function(string $class_name) {
			$linter = new $class_name();
			
			if ($linter instanceof FilenameAwareLinter) {
				$linter->setFilename($this->filename());
			}
			
			return $linter;
		});
		
		return $this;
	}
	
	protected function walk($nodes): void
	{
		$this->depth++;
		
		foreach ($nodes as $node) {
			// TODO: Try/catch
			
			$this->linters->each(function(Linter $linter) use ($node) {
				if ($this->shouldWalkNode($node, $linter)) {
					$linter->enterNode($node);
				}
			});
			
			$this->walk($node->getChildNodes());
			
			$this->linters->each(function(Linter $linter) use ($node) {
				if ($this->shouldWalkNode($node, $linter)) {
					$linter->exitNode($node);
				}
			});
		}
		
		$this->depth--;
	}
	
	protected function shouldWalkNode(Node $node, Linter $linter): bool
	{
		return ($linter instanceof ConditionalLinter)
			? $linter->shouldWalkNode($node)
			: true;
	}
	
	protected function filterIgnoredResults(ResultCollection $results): ResultCollection
	{
		if ($this->ignoreDirectives === null) {
			return $results;
		}

		$filtered = $results->reject(function (Result $result) {
			$linterClass = get_class($result->getLinter());
			$shortName = class_basename($linterClass);

			return $this->ignoreDirectives->shouldIgnoreLine(
				$result->getLine(),
				$shortName
			);
		});

		return new ResultCollection($filtered->values()->all());
	}

	abstract protected function source(): string;

	abstract protected function filename(): string;
}
