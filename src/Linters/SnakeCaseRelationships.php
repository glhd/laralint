<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ResolvedName;
use Throwable;

class SnakeCaseRelationships extends MatchingLinter implements ConditionalLinter
{
	use EvaluatesNodes;
	
	protected bool $active = false;
	
	public function shouldWalkNode(Node $node): bool
	{
		if ($node instanceof ClassBaseClause && $node->baseClass) {
			$resolved = $node->baseClass->getResolvedName();
			$extends = $resolved instanceof ResolvedName
				? $resolved->getFullyQualifiedNameText()
				: (string) $resolved;
			
			$this->active = in_array($extends, Config::get('laralint.models', []));
		}
		
		return $this->active;
	}
	
	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(fn(MethodDeclaration $node) => $this->isPublic($node)
				&& $this->isRelationship($node)
				&& ! $this->isSnakeCase($node->getName()));
	}
	
	/** @param Collection<int, MethodDeclaration> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->last();
		$name = $node->getName();
		$suggested = $this->toSnakeCase($name);
		
		return new Result(
			$this,
			$node,
			"Relationship method {$name}() should be {$suggested}()"
		);
	}
	
	protected function isRelationship(MethodDeclaration $node): bool
	{
		if ($node->returnTypeList) {
			$relationships = Config::get('laralint.relationships', []);
			
			foreach ($node->returnTypeList->children as $returnType) {
				foreach ($relationships as $class_name) {
					try {
						$qualified_return_type = $returnType->getResolvedName()->getFullyQualifiedNameText();
						
						if ($class_name === $qualified_return_type) {
							return true;
						}
					} catch (Throwable $exception) {
						// Ignore and use fallback
					}
				}
			}
		}
		
		return Str::contains($node->getText(), Config::get('laralint.relationship_heuristics', []));
	}
	
	protected function isSnakeCase(string $name): bool
	{
		return (bool) preg_match('/^[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/', $name);
	}
	
	protected function toSnakeCase(string $name): string
	{
		$name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);
		
		return strtolower($name);
	}
}
