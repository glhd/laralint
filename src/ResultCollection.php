<?php

namespace Glhd\LaraLint;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class ResultCollection extends Collection
{
	public function __construct($items = [])
	{
		parent::__construct($items);
		
		$this->each(function($item) {
			if (!($item instanceof Result)) {
				throw new InvalidArgumentException(__CLASS__.' can only contain '.Result::class.' objects.');
			}
		});
	}
}
