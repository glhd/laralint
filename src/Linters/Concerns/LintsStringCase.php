<?php

namespace Glhd\LaraLint\Linters\Concerns;

trait LintsStringCase
{
	protected function isSnakeCase(string $name): bool
	{
		return (bool) preg_match('/^_?[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/', $name);
	}

	protected function toSnakeCase(string $name): string
	{
		$name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);

		return strtolower($name);
	}

	protected function isCamelCase(string $name): bool
	{
		return (bool) preg_match('/^[a-z][a-zA-Z0-9]*$/', $name);
	}

	protected function toCamelCase(string $name): string
	{
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
	}
}
