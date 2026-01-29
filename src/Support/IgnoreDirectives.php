<?php

namespace Glhd\LaraLint\Support;

class IgnoreDirectives
{
	protected bool $ignoreFile = false;

	/**
	 * Line number => array of linter short names (null means all linters)
	 * @var array<int, array<string>|null>
	 */
	protected array $ignoredLines = [];

	public function ignoreFile(): self
	{
		$this->ignoreFile = true;
		return $this;
	}

	/**
	 * Mark a line as ignored for specific linters or all linters.
	 *
	 * @param int $line The line number to ignore
	 * @param array<string>|null $linters Linter names to ignore, or null for all linters
	 */
	public function ignoreLine(int $line, ?array $linters = null): self
	{
		// null means ignore all linters on this line
		// If already set, merge (null takes precedence)
		if (array_key_exists($line, $this->ignoredLines)) {
			if ($this->ignoredLines[$line] === null || $linters === null) {
				$this->ignoredLines[$line] = null;
			} else {
				$this->ignoredLines[$line] = array_unique(
					array_merge($this->ignoredLines[$line], $linters)
				);
			}
		} else {
			$this->ignoredLines[$line] = $linters;
		}

		return $this;
	}

	public function shouldIgnoreFile(): bool
	{
		return $this->ignoreFile;
	}

	/**
	 * Check if a line should be ignored for a specific linter.
	 *
	 * @param int $line The line number to check
	 * @param string|null $linterShortName The linter's short class name (e.g., "PreferAuthId")
	 */
	public function shouldIgnoreLine(int $line, ?string $linterShortName = null): bool
	{
		if (!array_key_exists($line, $this->ignoredLines)) {
			return false;
		}

		$ignoredLinters = $this->ignoredLines[$line];

		// null means all linters ignored on this line
		if ($ignoredLinters === null) {
			return true;
		}

		// No specific linter provided, but specific linters are listed - don't ignore
		if ($linterShortName === null) {
			return false;
		}

		// Check if specific linter is in the ignored list (case-insensitive)
		foreach ($ignoredLinters as $ignoredLinter) {
			if (strcasecmp($ignoredLinter, $linterShortName) === 0) {
				return true;
			}
		}

		return false;
	}
}
