<?php

namespace Glhd\LaraLint;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Linter;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use SplFileInfo;

class FileProcessor
{
	public static $debugging = false;
	
	/**
	 * @var \SplFileInfo
	 */
	protected $file;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $linters;
	
	protected $depth = -1;
	
	public function __construct(SplFileInfo $file, Collection $linters)
	{
		$this->file = $file;
		$this->linters = $linters->map(function(string $class_name) {
			$linter = new $class_name();
			
			if ($linter instanceof FilenameAwareLinter) {
				$linter->setFilename($this->file->getRealPath());
			}
			
			return $linter;
		});
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
		$this->depth++;
		
		foreach ($nodes as $node) {
			// TODO: Try/catch
			
			if (static::$debugging) {
				$indent = str_repeat('  ', $this->depth);
				
				echo $indent.'>>> ['.get_class($node).'] '.PHP_EOL;
				$node_text = trim($node->getText());
				echo $indent.str_replace("\n", "\n{$indent}", $node_text).PHP_EOL;
			}
			
			$this->linters->each(function(Linter $linter) use ($node) {
				if ($this->shouldWalkNode($node, $linter)) {
					$linter->enterNode($node);
				}
			});
			
			$this->walk($node->getChildNodes());
			
			if (static::$debugging) {
				echo $indent.'<<< ['.get_class($node).']'.PHP_EOL;
			}
			
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
}
