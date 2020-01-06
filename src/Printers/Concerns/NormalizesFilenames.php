<?php

namespace Glhd\LaraLint\Printers\Concerns;

use Illuminate\Support\Facades\App;

trait NormalizesFilenames
{
	protected $base_path;
	
	protected function normalizeFilename(string $filename) : string
	{
		if (null === $this->base_path) {
			$this->base_path = App::basePath();
		}
		
		if (0 === strpos($filename, $this->base_path)) {
			$filename = substr($filename, strlen($this->base_path));
		}
		
		return $filename;
	}
}
