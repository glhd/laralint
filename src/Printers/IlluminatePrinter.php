<?php

namespace Glhd\LaraLint\Printers;

use Glhd\LaraLint\Contracts\Printer;
use Illuminate\Console\OutputStyle;

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
	
	protected function write($messages, bool $newline = false, int $type = OutputStyle::OUTPUT_RAW) : void 
	{
		$this->output->write($messages, $newline, $type);
	}
	
	protected function writeln($messages, int $type = OutputStyle::OUTPUT_RAW) : void
	{
		$this->output->writeln($messages, $type);
	}
}
