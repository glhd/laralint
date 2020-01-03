<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\MatchOrderingLinter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Throwable;

class OrderModelMembers extends MatchOrderingLinter
{
	use EvaluatesNodes;
	
	protected const RELATIONSHIP_HELPERS = [
		'return $this->hasOne(',
		'return $this->hasOneThrough(',
		'return $this->morphOne(',
		'return $this->belongsTo(',
		'return $this->morphTo(',
		'return $this->hasMany(',
		'return $this->hasManyThrough(',
		'return $this->morphMany(',
		'return $this->belongsToMany(',
		'return $this->morphToMany(',
		'return $this->morphedByMany(',
	];
	
	protected const RELATIONSHIP_CLASSES = [
		'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
		'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasManyThrough',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOneOrMany',
		'Illuminate\\Database\\Eloquent\\Relations\\HasOneThrough',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphMany',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphOne',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphOneOrMany',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphPivot',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
		'Illuminate\\Database\\Eloquent\\Relations\\MorphToMany',
		'Illuminate\\Database\\Eloquent\\Relations\\Pivot',
	];
	
	// FIXME: NEEDS TO BE SCOPED TO CONTROLLERS
	
	protected function matchers() : Collection
	{
		return new Collection([
			'boot method' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return 'boot' === $node->getName()
						&& $this->isStatic($node);
				}),
			
			'mutator' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return Str::startsWith($node->getName(), ['get', 'set'])
						&& Str::endsWith($node->getName(), 'Attribute');
				}),
			
			'relationship' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					if (!$this->isPublic($node)) {
						return false;
					}
					
					// First check if a relationship return type has been declared
					if ($node->returnType) {
						foreach (static::RELATIONSHIP_CLASSES as $class_name) {
							try {
								$qualified_return_type = $node->returnType->getResolvedName()->getFullyQualifiedNameText();
								
								if ($class_name === $qualified_return_type) {
									return true;
								}
							} catch (Throwable $exception) {
								// Ignore and use fallback
							}
						}
					}
					
					// If not, check to see if a relationship method was called
					// inside of the method body
					return Str::contains($node->getText(), static::RELATIONSHIP_HELPERS);
				}),
			
			'scope' => $this->orderedMatcher()
				->withChild(function(MethodDeclaration $node) {
					return 0 === strpos($node->getName(), 'scope');
				}),
		]);
	}
	
}
