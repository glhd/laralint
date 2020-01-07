<?php

namespace Glhd\LaraLint\Runners;

class StringRunner extends SourceCodeRunner
{
	protected $filename;
	
	protected $source;
	
	public function __construct(string $filename, string $source = null)
	{
		$this->filename = $filename;
		$this->source = $source;
	}
	
	protected function source() : string
	{
		return $this->source;
	}
	
	protected function filename() : string
	{
		return $this->filename;
	}
}
