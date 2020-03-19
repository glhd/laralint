<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Linters\Concerns\EvaluatesNodes;
use Glhd\LaraLint\Linters\Strategies\OrderingLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;

class PreferFullyRestfulControllers extends OrderingLinter implements ConditionalLinter, FilenameAwareLinter
{
	use EvaluatesNodes;
	
	protected const RESTFUL_METHOD_NAMES = [
		'index',
		'create',
		'store',
		'show',
		'edit',
		'update',
		'destroy',
	];
	
	protected const IGNORED_METHOD_NAMES = [
		'__construct',
		'callAction',
		'validator',
	];
	
	protected const NON_RESTFUL_NAME = 'non-RESTful method';
	
	protected $max_non_restful_methods = 1;
	
	protected $active = false;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->max_non_restful_methods = Config::get(
			'laralint.max_non_restful_methods',
			$this->max_non_restful_methods
		);
	}
	
	public function shouldWalkNode(Node $node) : bool
	{
		return $this->active;
	}
	
	public function setFilename(string $filename) : void
	{
		$this->active = false !== strpos($filename, 'Controllers/');
	}
	
	public function lint() : ResultCollection
	{
		$results = parent::lint();
		
		$grouped_nodes = $this->currentContext()->results->groupBy(function($result) {
			return static::NON_RESTFUL_NAME === $result->name
				? 'non_restful'
				: 'restful';
		});
		
		if (
			isset($grouped_nodes['restful'], $grouped_nodes['non_restful'])
			&& $grouped_nodes['non_restful']->count() > $this->max_non_restful_methods
		) {
			$results = $results->merge(
				$grouped_nodes['non_restful']->splice(1)
					->map(function($result) {
						return new Result(
							$this,
							$result->node,
							"A RESTful controller should not contain more than {$this->max_non_restful_methods} non-RESTful ".Str::plural('method', $this->max_non_restful_methods).'.'
						);
					})
			);
		}
		
		return $results;
	}
	
	protected function matchers() : Collection
	{
		return Collection::make(static::RESTFUL_METHOD_NAMES)
			->mapWithKeys(function($method_name) {
				return [
					"the {$method_name} method" => $this->treeMatcher()
						->withChild(function(MethodDeclaration $node) use ($method_name) {
							return $method_name === $node->getName();
						}),
				];
			})
			->put(static::NON_RESTFUL_NAME, $this->treeMatcher()
				->withChild(function(MethodDeclaration $node) {
					return $this->isPublic($node)
						&& !in_array($node->getName(), array_merge(
							static::RESTFUL_METHOD_NAMES,
							static::IGNORED_METHOD_NAMES
						));
				}));
	}
}
