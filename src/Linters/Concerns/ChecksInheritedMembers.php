<?php

namespace Glhd\LaraLint\Linters\Concerns;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\ResolvedName;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

/**
 * Some names are dictated by a parent class or interface (overridden methods and
 * their parameter names). LaraLint only sees one file at a time, so we resolve the
 * parent/interface names from the AST and use the autoloader + reflection to look
 * them up. If a parent can't be loaded, we fall back to treating the name as local.
 */
trait ChecksInheritedMembers
{
	protected array $inherited_method_cache = [];

	protected function isInheritedMethod(MethodDeclaration $node): bool
	{
		return null !== $this->findInheritedMethod($node);
	}

	protected function isInheritedParameterName(MethodDeclaration $node, string $name): bool
	{
		$method = $this->findInheritedMethod($node);

		if (null === $method) {
			return false;
		}

		foreach ($method->getParameters() as $parameter) {
			/** @var ReflectionParameter $parameter */
			if ($parameter->getName() === $name) {
				return true;
			}
		}

		return false;
	}

	protected function findInheritedMethod(MethodDeclaration $node): ?ReflectionMethod
	{
		$name = $node->getName();

		if (! is_string($name) || '' === $name) {
			return null;
		}

		$parents = $this->parentTypeNames($node->parent?->parent);
		$key = implode('|', $parents).'::'.strtolower($name);

		if (! array_key_exists($key, $this->inherited_method_cache)) {
			$this->inherited_method_cache[$key] = $this->reflectInheritedMethod($parents, $name);
		}

		return $this->inherited_method_cache[$key];
	}

	protected function reflectInheritedMethod(array $parents, string $name): ?ReflectionMethod
	{
		foreach ($parents as $parent) {
			try {
				if (! class_exists($parent) && ! interface_exists($parent)) {
					continue;
				}

				$reflection = new ReflectionClass($parent);

				if (! $reflection->hasMethod($name)) {
					continue;
				}

				$method = $reflection->getMethod($name);

				if (! $method->isPrivate()) {
					return $method;
				}
			} catch (Throwable) {
				continue;
			}
		}

		return null;
	}

	/** @return string[] */
	protected function parentTypeNames(?Node $class_like): array
	{
		$names = [];

		if ($class_like instanceof ClassDeclaration || $class_like instanceof ObjectCreationExpression) {
			if ($class_like->classBaseClause instanceof ClassBaseClause && $class_like->classBaseClause->baseClass instanceof QualifiedName) {
				$names[] = $this->resolveQualifiedName($class_like->classBaseClause->baseClass);
			}
			$names = array_merge($names, $this->qualifiedNameListToNames($class_like->classInterfaceClause?->interfaceNameList));
		} elseif ($class_like instanceof InterfaceDeclaration) {
			$names = $this->qualifiedNameListToNames($class_like->interfaceBaseClause?->interfaceNameList);
		} elseif ($class_like instanceof EnumDeclaration) {
			$names = $this->qualifiedNameListToNames($class_like->enumInterfaceClause?->interfaceNameList);
		}

		return array_values(array_filter($names));
	}

	/** @return string[] */
	protected function qualifiedNameListToNames($list): array
	{
		if (! $list instanceof QualifiedNameList) {
			return [];
		}

		$names = [];

		foreach ($list->getElements() as $element) {
			if ($element instanceof QualifiedName) {
				$names[] = $this->resolveQualifiedName($element);
			}
		}

		return $names;
	}

	protected function resolveQualifiedName(QualifiedName $name): ?string
	{
		try {
			$resolved = $name->getResolvedName();

			$resolved = $resolved instanceof ResolvedName
				? $resolved->getFullyQualifiedNameText()
				: (string) $resolved;

			return '' === $resolved ? null : ltrim($resolved, '\\');
		} catch (Throwable) {
			return null;
		}
	}
}
