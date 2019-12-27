<?php

namespace Glhd\LaraLint\Nodes;

use Microsoft\PhpParser\Node\MethodDeclaration as BaseDeclaration;
use Microsoft\PhpParser\TokenKind;

/**
 * @mixin \Microsoft\PhpParser\Node\MethodDeclaration
 */
class MethodDeclaration 
{
	/**
	 * @var \Microsoft\PhpParser\Node\MethodDeclaration
	 */
	public $node;
	
	public function __construct(BaseDeclaration $node)
	{
		$this->node = $node;
	}
	
	public function __get($name)
	{
		return $this->node->$name;
	}
	
	public function __call($name, $arguments)
	{
		$result = $this->node->$name(...$arguments);
		
		if ($result === $this->node) {
			return $this;
		}
		
		return $result;
	}
	
	public function isPublic() : bool 
	{
		return $this->hasModifier(TokenKind::PublicKeyword);
	}
	
	protected function hasModifier(int $modifier) : bool {
		if (null === $this->modifiers) {
			return false;
		}
		
		foreach ($this->node->modifiers as $node_modifier) {
			if ($node_modifier->kind === $modifier) {
				return true;
			}
		}
		
		return false;
	}
}
