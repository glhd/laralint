<?php

namespace Glhd\LaraLint\Linters;

use Closure;
use Glhd\LaraLint\Contracts\ConditionalLinter;
use Glhd\LaraLint\Contracts\FilenameAwareLinter;
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
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\TokenKind;

class PreferCustomImplementations extends MatchingLinter
{
	use EvaluatesNodes;
	
	protected $custom_implementations = [];
	
	public function __construct()
	{
		parent::__construct();
		
		$this->custom_implementations = Config::get('laralint.custom_implementations', []);
	}
	
	protected function matcher() : Matcher
	{
		return $this->classMatcher()
			->withChild(function(ClassBaseClause $node) {
				foreach ($this->custom_implementations as $framework_name => $custom_name) {
					if ($this->isFullyQualifiedName($node->baseClass, $framework_name)) {
						return true;
					}
				}
				
				return false;
			});
	}
	
	protected function onMatch(Collection $nodes) : ?Result
	{
		$node = $nodes->first(function(Node $node) {
			return $node instanceof ClassBaseClause;
		});
		
		$framework_implementation = $this->getFullyQualifiedName($node->baseClass);
		$custom_implementation = $this->custom_implementations[$framework_implementation];
		
		return new Result(
			$this,
			$node,
			"Please use '{$custom_implementation}' rather than '{$framework_implementation}'"
		);
	}
}
