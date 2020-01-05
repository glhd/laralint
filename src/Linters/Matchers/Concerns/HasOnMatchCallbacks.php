<?php

namespace Glhd\LaraLint\Linters\Matchers\Concerns;

use Glhd\LaraLint\Linters\Matchers\TreeMatcher;
use Illuminate\Support\Collection;

trait HasOnMatchCallbacks
{
	
	/**
	 * Callbacks that gets triggered when all rules have been matched
	 *
	 * @var Collection|callable[]
	 */
	protected $on_match_callbacks;
	
	/**
	 * @return $this
	 */
	public function onMatch(callable $callback)
	{
		if (null === $this->on_match_callbacks) {
			$this->on_match_callbacks = new Collection();
		}
		
		$this->on_match_callbacks->push($callback);
		
		return $this;
	}
	
	protected function triggerMatch(Collection $nodes) : void
	{
		if (null === $this->on_match_callbacks) {
			return;
		}
		
		foreach($this->on_match_callbacks as $callback) {
			$callback($nodes);
		}
	}
}
