<?php

namespace Glhd\LaraLint\Linters\Strategies\Concerns;

use Glhd\LaraLint\Linters\Matchers\ClassMemberMatcher;
use Glhd\LaraLint\Linters\Matchers\OrderedNodeMatcher;

trait CreatesMatchers
{
	protected function orderedMatcher() : OrderedNodeMatcher
	{
		return new OrderedNodeMatcher();
	}
	
	protected function classMatcher() : ClassMemberMatcher
	{
		return new ClassMemberMatcher();
	}
}
