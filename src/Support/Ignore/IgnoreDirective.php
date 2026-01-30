<?php

namespace Glhd\LaraLint\Support\Ignore;

use Illuminate\Support\Collection;

class IgnoreDirective
{
	protected array $linter_basenames = [];
	
	protected bool $wildcard = false;
	
	public function shouldIgnore(?string $linter): bool
	{
		if ($this->wildcard) {
			return true;
		}
		
		// If no specific linter was provided, but we're not a wildcard, then don't ignore
		if (null === $linter) {
			return false;
		}
		
		// We only require the basename in the comment
		$linter = class_basename($linter);
		
		// Check if specific linter is in the ignored list (case-insensitive)
		foreach ($this->linter_basenames as $ignored_basename) {
			if (0 === strcasecmp($ignored_basename, $linter)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function all(): self
	{
		$this->wildcard = true;
		
		return $this;
	}
	
	public function linters(Collection $linters): self
	{
		if ($linters->isEmpty()) {
			return $this->all();
		}
		
		$this->linter_basenames = array_merge($this->linter_basenames, $linters->all());
		
		return $this;
	}
}
