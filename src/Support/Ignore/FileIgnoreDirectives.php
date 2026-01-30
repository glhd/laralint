<?php

namespace Glhd\LaraLint\Support\Ignore;

use Glhd\LaraLint\Result;
use Illuminate\Support\Traits\Conditionable;

class FileIgnoreDirectives
{
	use Conditionable;
	
	protected bool $ignore_file = false;

	/** @var array<int, IgnoreDirective> */
	protected array $directives = [];

	public function ignoreFile(): self
	{
		$this->ignore_file = true;
		
		return $this;
	}
	
	public function ignore(int $line): IgnoreDirective
	{
		return $this->directives[$line] ??= new IgnoreDirective();
	}

	public function shouldIgnoreFile(): bool
	{
		return $this->ignore_file;
	}
	
	public function shouldIgnoreResult(Result $result): bool
	{
		$line = $result->getLine();
		$linter = class_basename($result->getLinter());
		
		return $this->shouldIgnoreLine($line, $linter);
	}
	
	public function shouldIgnoreLine(int $line, ?string $linter = null): bool
	{
		if (! isset($this->directives[$line])) {
			return false;
		}

		return $this->directives[$line]->shouldIgnore($linter);
	}
}
