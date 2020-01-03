<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\MatchingLinter;
use Glhd\LaraLint\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use SplObjectStorage;

class AvoidGlobalFacadeAliases extends MatchingLinter implements FilenameAwareLinter, ConditionalLinter
{
	protected const DEFAULT_ALIASES = [
		'App' => 'Illuminate\\Support\\Facades\\App',
		'Arr' => 'Illuminate\\Support\\Arr',
		'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
		'Auth' => 'Illuminate\\Support\\Facades\\Auth',
		'Blade' => 'Illuminate\\Support\\Facades\\Blade',
		'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
		'Bus' => 'Illuminate\\Support\\Facades\\Bus',
		'Cache' => 'Illuminate\\Support\\Facades\\Cache',
		'Config' => 'Illuminate\\Support\\Facades\\Config',
		'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
		'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
		'DB' => 'Illuminate\\Support\\Facades\\DB',
		'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
		'Event' => 'Illuminate\\Support\\Facades\\Event',
		'File' => 'Illuminate\\Support\\Facades\\File',
		'Gate' => 'Illuminate\\Support\\Facades\\Gate',
		'Hash' => 'Illuminate\\Support\\Facades\\Hash',
		'Lang' => 'Illuminate\\Support\\Facades\\Lang',
		'Log' => 'Illuminate\\Support\\Facades\\Log',
		'Mail' => 'Illuminate\\Support\\Facades\\Mail',
		'Notification' => 'Illuminate\\Support\\Facades\\Notification',
		'Password' => 'Illuminate\\Support\\Facades\\Password',
		'Queue' => 'Illuminate\\Support\\Facades\\Queue',
		'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
		'Redis' => 'Illuminate\\Support\\Facades\\Redis',
		'Request' => 'Illuminate\\Support\\Facades\\Request',
		'Response' => 'Illuminate\\Support\\Facades\\Response',
		'Route' => 'Illuminate\\Support\\Facades\\Route',
		'Schema' => 'Illuminate\\Support\\Facades\\Schema',
		'Session' => 'Illuminate\\Support\\Facades\\Session',
		'Storage' => 'Illuminate\\Support\\Facades\\Storage',
		'Str' => 'Illuminate\\Support\\Str',
		'URL' => 'Illuminate\\Support\\Facades\\URL',
		'Validator' => 'Illuminate\\Support\\Facades\\Validator',
		'View' => 'Illuminate\\Support\\Facades\\View',
	];
	
	protected $aliases;
	
	protected $node_map;
	
	protected $filename;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->aliases = Config::get('app.aliases', static::DEFAULT_ALIASES);
		$this->node_map = new SplObjectStorage();
	}
	
	public function setFilename(string $filename) : void
	{
		$this->filename = $filename;
	}
	
	public function shouldWalkNode(Node $node) : bool
	{
		return false === Str::endsWith($this->filename, '.blade.php');
	}
	
	protected function matcher() : Matcher
	{
		return $this->orderedMatcher()
			->withChild(ScopedPropertyAccessExpression::class)
			->withChild(function(QualifiedName $node) {
				if (!$resolved_name = $node->getResolvedName()) {
					return false;
				}
				
				$qualified_name = is_string($resolved_name)
					? $resolved_name
					: $resolved_name->getFullyQualifiedNameText();
				
				foreach ($this->aliases as $alias => $fqcn) {
					if ($alias === $qualified_name) {
						$this->node_map[$node] = $alias;
						return true;
					}
				}
				
				return false;
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$node = $nodes->first(function($node) {
			return $this->node_map->contains($node);
		});
		
		$alias = $this->node_map[$node];
		$qualified = $this->aliases[$alias];
		
		return new Result(
			$node,
			"Please use '$qualified' rather than the $alias alias."
		);
	}
}
