<?php

namespace Glhd\LaraLint\Presets;

use Glhd\LaraLint\Contracts\Preset;
use Glhd\LaraLint\Linters\OrderUseStatementsAlphabetically;
use Glhd\LaraLint\Linters\PreferFacadesOverHelpers;
use Glhd\LaraLint\Linters\PrefixTestsWithTest;
use Illuminate\Support\Collection;

class LaraLint implements Preset
{
	public function linters() : Collection
	{
		return new Collection([
			PrefixTestsWithTest::class,
			PreferFacadesOverHelpers::class,
			OrderUseStatementsAlphabetically::class,
		]);
	}
}
