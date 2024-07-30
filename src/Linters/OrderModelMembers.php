<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\OrderingLinter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ResolvedName;
use Throwable;

class OrderModelMembers extends OrderingLinter implements ConditionalLinter
{
	use EvaluatesNodes;
	
	protected $active = false;
	
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
	
	protected function matchers(): Collection
	{
		return new Collection([
			'boot methods' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return in_array($node->getName(), ['booting', 'boot', 'booted'])
						&& $this->isStatic($node);
				}),
			
			'a mutator' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return Str::startsWith($node->getName(), ['get', 'set'])
						&& Str::endsWith($node->getName(), 'Attribute');
				}),
			
			'a relationship' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					if (!$this->isPublic($node)) {
						return false;
					}
					
					// First check if a relationship return type has been declared
					if ($node->returnTypeList) {
						$relationships = Config::get('laralint.relationships', []);
						
						/** @var \Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList $returnType */
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
					
					// If not, check to see if a relationship method was called
					// inside of the method body
					return Str::contains($node->getText(), Config::get('laralint.relationship_heuristics', []));
				}),
			
			'a scope' => $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return 0 === strpos($node->getName(), 'scope');
				}),
		]);
	}
}
