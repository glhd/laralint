<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Glhd\LaraLint\Linters\Matchers\ClassMemberMatcher;
use Glhd\LaraLint\Linters\Matchers\TreeMatcher;

trait CreatesMatchers
{
	protected function orderedMatcher() : TreeMatcher
	{
		return new TreeMatcher();
	}
	
	protected function classMatcher() : ClassMemberMatcher
	{
		return new ClassMemberMatcher();
	}
}
