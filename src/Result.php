<?php

namespace Glhd\LaraLint;

use Microsoft\PhpParser\Node;

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
	
	public function __construct(Node $node, string $message)
	{
		$this->node = $node;
		$this->message = $message;
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
