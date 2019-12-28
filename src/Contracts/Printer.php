<?php

namespace Glhd\LaraLint\Contracts;

use Glhd\LaraLint\ResultCollection;

interface Printer
{
	public function opening() : void;
	
	public function closing() : void;
	
	public function results(string $filname, ResultCollection $results) : void;
}
