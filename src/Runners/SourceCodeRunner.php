<?php

namespace Glhd\LaraLint\Runners;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Contracts\Runner;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;

abstract class SourceCodeRunner implements Runner
{
	protected $depth = -1;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $linters;
	
	public function run(Collection $linters) : ResultCollection
	{
		$this->setLinters($linters);
		
		// TODO: Allow for blade compilation
		$ast = (new Parser())->parseSourceFile($this->source());
		
		// We'll walk the entire tree and perform any filtering
		// or collection logic on all the nodes and tokens
		$this->walk($ast->getChildNodes());
		
		// Then we'll run the "lint" stage on all the linters to collect
		// all our results
		return new ResultCollection(
			$this->linters->flatMap(function(Linter $linter) {
				return $linter->lint();
			})
		);
	}
	
	protected function setLinters(Collection $linters) : self
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
	
	protected function walk($nodes) : void
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
	
	protected function shouldWalkNode(Node $node, Linter $linter) : bool
	{
		return ($linter instanceof ConditionalLinter)
			? $linter->shouldWalkNode($node)
			: true;
	}
	
	abstract protected function source() : string;
	
	abstract protected function filename() : string;
}
