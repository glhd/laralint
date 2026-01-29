<?php

namespace Glhd\LaraLint\Support;

class IgnoreDirectiveParser
{
	/**
	 * Matches LaraLint ignore directives in comments.
	 *
	 * Supports:
	 * - // @laralint-ignore
	 * - // @laralint-ignore-next-line
	 * - // @laralint-ignore-file
	 * - /* @laralint-ignore * /
	 * - # @laralint-ignore
	 *
	 * Optional linter names: @laralint-ignore LinterA, LinterB
	 */
	protected const PATTERN = '/@laralint-ignore(-next-line|-file)?(?:\s+([A-Za-z0-9_,\s]+))?/';

	public function parse(string $source): IgnoreDirectives
	{
		$directives = new IgnoreDirectives();
		$lines = explode("\n", $source);
		$totalLines = count($lines);

		foreach ($lines as $index => $line) {
			$lineNumber = $index + 1; // 1-indexed

			// Only match if the directive appears in a comment
			if (!$this->lineContainsComment($line)) {
				continue;
			}

			if (preg_match(self::PATTERN, $line, $matches)) {
				$type = $matches[1] ?? '';
				$linterNames = $this->parseLinterNames($matches[2] ?? null);

				switch ($type) {
					case '-file':
						$directives->ignoreFile();
						break;

					case '-next-line':
						// Only apply if there is a next line
						if ($lineNumber < $totalLines) {
							$directives->ignoreLine($lineNumber + 1, $linterNames);
						}
						break;

					default: // same line ignore
						$directives->ignoreLine($lineNumber, $linterNames);
						break;
				}
			}
		}

		return $directives;
	}

	/**
	 * Check if a line contains a comment (where our directive would be valid).
	 */
	protected function lineContainsComment(string $line): bool
	{
		// Check for //, #, or /* style comments
		// This is a simple check - it doesn't handle string literals containing these
		return preg_match('/\/\/|#|\/\*/', $line) === 1;
	}

	/**
	 * Parse comma-separated linter names from the directive.
	 *
	 * @return array<string>|null Null means ignore all linters
	 */
	protected function parseLinterNames(?string $names): ?array
	{
		if ($names === null || trim($names) === '') {
			return null; // Ignore all linters
		}

		$linters = array_map('trim', explode(',', $names));

		// Filter out empty strings
		$linters = array_filter($linters, fn($name) => $name !== '');

		return empty($linters) ? null : array_values($linters);
	}
}
