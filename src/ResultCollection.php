<?php

namespace Glhd\LaraLint;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class ResultCollection extends Collection
{
	public function __construct($items = [])
	{
		parent::__construct($items);
		
		$this->ensure(Result::class);
	}
}
