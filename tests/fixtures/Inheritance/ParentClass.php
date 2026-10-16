<?php

namespace Glhd\LaraLint\Tests\fixtures\Inheritance;

abstract class ParentClass
{
	abstract public function handle(string $someValue, int $other_value): void;
	
	private function hidden(string $privateValue): void
	{
	}
}
