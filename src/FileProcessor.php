<?php

namespace Glhd\LaraLint;

use Glhd\LaraLint\Contracts\Linter;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Parser;
use SplFileInfo;

class FileProcessor
{
	/**
	 * @var \SplFileInfo
	 */
	protected $file;
	
	public function __construct(SplFileInfo $file)
	{
		$this->file = $file;
	}
	
	public static function make(SplFileInfo $file) : self
	{
		return new static($file);
	}
	
	public function lint(Collection $linters) : ResultCollection
	{
		$parser = new Parser();
		
		// TODO: Allow for blade compilation
		$ast = $parser->parseSourceFile(file_get_contents($this->file->getRealPath()));
		
		$walk = function($nodes, $walk) use ($linters) {
			foreach ($nodes as $node) {
				// TODO: Try/catch
				$linters->each(function(Linter $linter) use ($node) {
					$linter->enterNode($node);
				});
				
				$walk($node->getChildNodes(), $walk);
				
				$linters->each(function(Linter $linter) use ($node) {
					$linter->leaveNode($node);
				});
			}
		};
		
		$walk($ast->getChildNodes(), $walk);
		
		// $position = PositionUtilities::getLineCharacterPositionFromPosition(
		// 	$node->getStart(),
		// 	$node->getFileContents()
		// );
		//
		// dump($node->getNodeKindName(), $position);
		
		return new ResultCollection(
			$linters->flatMap(function(Linter $linter) {
				return $linter->lint();
			})
		);
	}
}
