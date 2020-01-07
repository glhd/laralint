<?php

namespace Glhd\LaraLint\Runners;

use SplFileInfo;

class SplFileInfoRunner extends SourceCodeRunner
{
	/**
	 * @var \SplFileInfo
	 */
	protected $file;
	
	public static function file(SplFileInfo $file) : self
	{
		return new static($file);
	}
	
	public function __construct(SplFileInfo $file)
	{
		$this->file = $file;
	}
	
	protected function source() : string
	{
		return file_get_contents($this->file->getRealPath());
	}
	
	protected function filename() : string
	{
		return $this->file->getRealPath();
	}
}
