<?php

namespace Glhd\LaraLint\Linters\Matchers;

use Closure;
use Glhd\LaraLint\Contracts\Matcher;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Microsoft\PhpParser\Node;
use ReflectionFunction;
use stdClass;
use Throwable;

class AggregateMatcher implements Matcher
{
	protected $matchers;
	
	public function __construct(Matcher ...$matcher)
	{
		$this->matchers = Collection::make($matcher);
	}
	
	public function enterNode(Node $node) : void
	{
		$this->matchers->each->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->matchers->each->exitNode($node);
	}
}
