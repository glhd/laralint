<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Microsoft\PhpParser\Node;

trait LintsControllers
{
	protected $current_file_is_controller = false;
	
	public function setFilename(string $filename) : void
	{
		$this->current_file_is_controller = false !== strpos($filename, 'Controllers/');
	}
	
	public function shouldWalkNode(Node $node) : bool
	{
		return $this->current_file_is_controller;
	}
}
