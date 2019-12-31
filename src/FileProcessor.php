<?php

namespace Glhd\LaraLint;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\Linter;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use SplFileInfo;

class FileProcessor
{
	/**
	 * @var \SplFileInfo
	 */
	protected $file;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $linters;
	
	public function __construct(SplFileInfo $file, Collection $linters)
	{
		$this->file = $file;
		$this->linters = $linters;
	}
	
	public static function make(SplFileInfo $file, Collection $linters) : self
	{
		return new static($file, $linters);
	}
	
	public function lint() : ResultCollection
	{
		// TODO: Allow for blade compilation
		$ast = (new Parser())->parseSourceFile(file_get_contents($this->file->getRealPath()));
		
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
	
	protected function walk($nodes) : void
	{
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
	}
	
	protected function shouldWalkNode(Node $node, Linter $linter) : bool
	{
		return ($linter instanceof ConditionalLinter)
			? $linter->shouldWalkNode($node)
			: true;
	}
}
