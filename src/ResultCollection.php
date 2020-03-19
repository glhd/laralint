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
			$this->validateItemType($item);
		});
	}
	
	// public function push($item)
	// {
	// 	$this->validateItemType($item);
	//	
	// 	return parent::push($item);
	// }
	
	protected function validateItemType($item) : void
	{
		if (!($item instanceof Result)) {
			throw new InvalidArgumentException(__CLASS__.' can only contain '.Result::class.' objects.');
		}
	}
}
