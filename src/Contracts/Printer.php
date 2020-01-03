<?php

namespace Glhd\LaraLint\Contracts;

use Glhd\LaraLint\ResultCollection;

interface Printer
{
	public function opening() : void;
	
	public function closing() : void;
	
	public function startFile(string $filename) : void;
	
	public function fileResults(string $filename, ResultCollection $results) : void;
}
