<?php

namespace Glhd\LaraLint\Printers;

use Glhd\LaraLint\Contracts\Printer;
use Illuminate\Console\OutputStyle;

/**
 * @mixin \Illuminate\Console\OutputStyle
 */
abstract class IlluminatePrinter implements Printer
{
	/**
	 * @var \Illuminate\Console\OutputStyle
	 */
	protected $output;
	
	public function __construct(OutputStyle $output)
	{
		$this->output = $output;
	}
	
	public function __call($name, $arguments)
	{
		return $this->output->$name(...$arguments);
	}
}
