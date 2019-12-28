<?php

namespace Glhd\LaraLint;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\PositionUtilities;

class Result
{
	/**
	 * @var Node
	 */
	public $node;
	
	/**
	 * @var string
	 */
	public $message;
	
	/**
	 * @var int
	 */
	public $line;
	
	/**
	 * @var int 
	 */
	public $character;
	
	/**
	 * @var int 
	 */
	public $end;
	
	public function __construct(Node $node, string $message)
	{
		$this->node = $node;
		$this->message = $message;
		
		$position = PositionUtilities::getLineCharacterPositionFromPosition(
			$node->getStart(),
			$node->getFileContents()
		);
		
		$this->line = $position->line + 1;
		$this->character = $position->character;
	}
	
	public function getLine() : int 
	{
		return $this->line;
	}
	
	public function getCharacter() : int 
	{
		return $this->character;
	}
	
	public function getNode() : Node
	{
		return $this->node;
	}
	
	public function getMessage() : string
	{
		return $this->message;
	}
}
