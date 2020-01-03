<?php

namespace Glhd\LaraLint\Contracts;

interface FilenameAwareLinter
{
	public function setFilename(string $filename) : void;
}
