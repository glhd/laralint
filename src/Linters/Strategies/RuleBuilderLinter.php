<?php

namespace Glhd\LaraLint\Linters\Strategies;

use Glhd\LaraLint\Contracts\Linter;
use Glhd\LaraLint\Linters\RuleBuilder;
use Glhd\LaraLint\ResultCollection;
use Microsoft\PhpParser\Node;

abstract class RuleBuilderLinter implements Linter
{
	protected $builder;
	
	public function __construct()
	{
		// TODO: Maybe extend the RuleBasedLinter instead?
		$this->builder = $this->buildRules();
	}
	
	public function enterNode(Node $node) : void
	{
		$this->builder->getLinter()->enterNode($node);
	}
	
	public function exitNode(Node $node) : void
	{
		$this->builder->getLinter()->exitNode($node);
	}
	
	public function lint() : ResultCollection
	{
		return $this->builder->getLinter()->lint();
	}
	
	abstract protected function buildRules() : RuleBuilder;
}
