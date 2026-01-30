<?php

namespace Glhd\LaraLint\Support\Ignore;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IgnoreDirectiveParser
{
	protected const string PATTERN = '/@laralint-ignore(?:-(?<type>next-line|file))?(?:\s+(?<linters>[A-Za-z0-9_,\s]+))?/';
	
	public function parse(string $source): FileIgnoreDirectives
	{
		$directives = new FileIgnoreDirectives();
		$lines = explode("\n", $source);
		
		foreach ($lines as $index => $line) {
			$line_number = $index + 1;
			
			if (! $this->lineContainsComment($line)) {
				continue;
			}
			
			if (preg_match(self::PATTERN, $line, $matches)) {
				$type = $matches['type'] ?? '';
				
				// If we're ignoring the whole file, we don't have to parse anything else
				if ('file' === $type) {
					$directives->ignoreFile();
					continue;
				}
				
				$linters = $this->parseLinterNames($matches['linters'] ?? null);
				$target_line = 'next-line' === $type ? $line_number + 1 : $line_number;
				
				if ($linters->isEmpty()) {
					$directives->ignore($target_line)->all();
				} else {
					$directives->ignore($target_line)->linters($linters);
				}
			}
		}
		
		return $directives;
	}
	
	protected function lineContainsComment(string $line): bool
	{
		return preg_match('/\/\/|#|\/\*/', $line);
	}
	
	protected function parseLinterNames(?string $names): Collection
	{
		if (null === $names) {
			return new Collection();
		}
		
		return Str::of($names)
			->explode(',')
			->map(static fn($name) => trim($name))
			->filter()
			->values();
	}
}
