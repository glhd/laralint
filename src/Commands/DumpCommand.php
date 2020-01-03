<?php

namespace Glhd\LaraLint\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Parser;

class DumpCommand extends Command
{
	protected $signature = 'laralint:dump {filename}';
	
	protected $description = 'Dump the AST for a file for debugging purposes';
	
	protected $depth = -1;
	
	public function handle()
	{
		$filename = $this->argument('filename');
		$ast = (new Parser())->parseSourceFile(file_get_contents($filename));
		
		$this->getOutput()->newLine();
		$this->walk($ast->getChildNodes());
		$this->getOutput()->newLine();
	}
	
	protected function walk($nodes) : void
	{
		$this->depth++;
		
		$indent = str_repeat('┃   ', $this->depth);
		
		foreach ($nodes as $i => $node) {
			$node_kind_length = strlen($node->getNodeKindName());
			
			$code_lines = Collection::make(explode("\n", $node->getText()))
				->map(function($line) {
					return str_replace("\t", '  ', rtrim($line)).'  ';
				});
			$longest_line = $code_lines->reduce(function($longest_line, $line) {
				$length = strlen($line);
				return $length > $longest_line
					? $length
					: $longest_line;
			}, 0);
			
			$dashes = $longest_line > ($node_kind_length + 5)
				? str_repeat('━', $longest_line - $node_kind_length - 5)
				: '';
			$this->writeln("{$indent}┏━━ <info>{$node->getNodeKindName()}</info> ━━{$dashes}┓");
			
			// $this->writeln("{$indent}┃");
			// $this->writeln("{$indent}┃ longest: {$longest_line}, len: {$node_kind_length}");
			
			$this->writeln(
				$code_lines->map(function($line) use ($indent, $longest_line, $node_kind_length) {
					$line = str_pad($line, max($longest_line, $node_kind_length + 5));
					return "{$indent}┃ {$line}┃";
				})
			);
			
			// $this->writeln("{$indent}┃");
			
			$this->walk($node->getChildNodes());
			
			$dashes = str_repeat('━', max($node_kind_length + 4, $longest_line));
			$this->writeln("{$indent}┗━━{$dashes}┛");
		}
		
		$this->depth--;
	}
	
	protected function writeln($messages, $type = OutputStyle::OUTPUT_NORMAL)
	{
		return $this->getOutput()->writeln($messages, $type);
	}
	
	protected function indent() : string
	{
		return str_repeat(static::TAB, $this->depth);
	}
	
	protected function writeWithDashes($text) : void
	{
		$indent = str_repeat(' ', $this->depth);
		
		$remainder = static::COLUMNS - (strlen(strip_tags($text)) + strlen($indent));
		$dashes = $remainder > 0
			? str_repeat('━', $remainder)
			: '';
		
		$this->getOutput()->writeln("{$indent}{$text}{$dashes}");
	}
}
