<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Microsoft\PhpParser\Node;

trait SkipsViewComponents
{
	protected bool $current_file_is_view_component = false;

	public function setFilename(string $filename): void
	{
		$this->current_file_is_view_component = str_contains($filename, '/View/Components/');
	}

	public function shouldWalkNode(Node $node): bool
	{
		return !$this->current_file_is_view_component;
	}
}
