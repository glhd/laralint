<?php

namespace Glhd\LaraLint\Support;

use Glhd\LaraLint\ResultCollection;

class PhpCsFormatter
{
	/**
	 * @var \Glhd\LaraLint\ResultCollection
	 */
	protected $results;
	
	public function __construct(ResultCollection $results)
	{
		$this->results = $results;
	}
}
