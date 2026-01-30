<?php

namespace Glhd\LaraLint;

use Glhd\LaraLint\Contracts\Linter;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\PositionUtilities;

class Result
{
	public int $line;
	
	public int $character;
	
	public function __construct(
		public Linter $linter,
		public Node $node,
		public string $message
	) {
		$position = PositionUtilities::getLineCharacterPositionFromPosition(
			$node->getStartPosition(),
			$node->getFileContents()
		);
		
		$this->line = $position->line + 1;
		$this->character = $position->character;
	}
}
