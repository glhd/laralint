<?php

namespace Glhd\LaraLint\Contracts;

use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;

interface Runner
{
	public function run(Collection $linters) : ResultCollection;
}
