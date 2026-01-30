<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\CollectingLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;

class OverlappingFactoryStates extends CollectingLinter
{
	protected const int MIN_SHARED_ATTRIBUTES = 3;
	
	protected const int MIN_OCCURRENCES = 4;
	
	protected array $factory_calls = [];
	
	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(fn(CallExpression $node) => $this->isFactoryCreateOrMakeCall($node));
	}
	
	/** @param Collection<int, CallExpression> $nodes */
	protected function onMatch(Collection $nodes): ?Result
	{
		if ($extracted = $this->extractFactoryCallData($nodes->first())) {
			$this->factory_calls[] = $extracted;
		}
		
		return null;
	}
	
	protected function lintCollectedNodes(Collection $nodes): ResultCollection
	{
		$results = new ResultCollection();
		
		$grouped = collect($this->factory_calls)->groupBy('model');
		
		foreach ($grouped as $model => $calls) {
			if ($calls->count() < self::MIN_OCCURRENCES) {
				continue;
			}
			
			$overlaps = $this->findMaximalOverlaps($calls->all());
			
			foreach ($overlaps as $overlap) {
				$attributeList = collect($overlap['attributes'])
					->map(fn($v, $k) => "{$k} => ".var_export($v, true))
					->implode(', ');
				
				$results->push(new Result(
					$this,
					$overlap['node'],
					"Consider extracting a factory state for {$model} — {$overlap['count']} calls share attributes: [{$attributeList}]"
				));
			}
		}
		
		return $results;
	}
	
	protected function isFactoryCreateOrMakeCall(CallExpression $node): bool
	{
		$callable = $node->callableExpression ?? null;
		
		if (! $callable instanceof MemberAccessExpression) {
			return false;
		}
		
		$method_name = $callable->memberName->getText($node->getFileContents());
		
		if (! in_array($method_name, ['create', 'make'], true)) {
			return false;
		}
		
		$dereferencable = $callable->dereferencableExpression ?? null;
		
		if (! $dereferencable instanceof CallExpression) {
			return false;
		}
		
		$inner_callable = $dereferencable->callableExpression ?? null;
		
		if ($inner_callable instanceof ScopedPropertyAccessExpression || $inner_callable instanceof MemberAccessExpression) {
			return 'factory' === $inner_callable->memberName->getText($node->getFileContents());
		}
		
		return false;
	}
	
	protected function extractFactoryCallData(CallExpression $node): ?array
	{
		$callable = $node->callableExpression;
		$dereferencable = $callable->dereferencableExpression;
		$inner_callable = $dereferencable->callableExpression;
		
		$model = null;
		
		if ($inner_callable instanceof ScopedPropertyAccessExpression) {
			$scope_resolution = $inner_callable->scopeResolutionQualifier ?? null;
			if ($scope_resolution instanceof QualifiedName) {
				$model = $scope_resolution->getText();
			}
		}
		
		if (! $model) {
			return null;
		}
		
		$attributes = $this->extractAttributes($node->argumentExpressionList);
		
		if (empty($attributes)) {
			return null;
		}
		
		return [
			'model' => $model,
			'attributes' => $attributes,
			'node' => $node,
		];
	}
	
	protected function extractAttributes(?ArgumentExpressionList $args): array
	{
		if (! $args) {
			return [];
		}
		
		$attributes = [];
		
		foreach ($args->getChildNodes() as $child) {
			if (! $child instanceof ArgumentExpression) {
				continue;
			}
			
			$expression = $child->expression ?? null;
			
			if (! $expression instanceof ArrayCreationExpression) {
				continue;
			}
			
			$elements = $expression->arrayElements ?? null;
			
			if (! $elements instanceof ArrayElementList) {
				continue;
			}
			
			foreach ($elements->getChildNodes() as $element) {
				if (
					$element instanceof ArrayElement
					&& $element->elementKey
					&& $element->elementValue
				) {
					$attributes[$element->elementKey->getText()] = $element->elementValue->getText();
				}
			}
		}
		
		return $attributes;
	}
	
	protected function findMaximalOverlaps(array $calls): array
	{
		if (count($calls) < self::MIN_OCCURRENCES) {
			return [];
		}
		
		$attrToCalls = [];
		
		foreach ($calls as $index => $call) {
			foreach ($call['attributes'] as $key => $value) {
				$attr = json_encode([$key => $value]);
				$attrToCalls[$attr][] = $index;
			}
		}
		
		$frequent = array_filter($attrToCalls, fn($indices) => count($indices) >= self::MIN_OCCURRENCES);
		
		if (count($frequent) < self::MIN_SHARED_ATTRIBUTES) {
			return [];
		}
		
		$candidates = [];
		
		foreach ($this->combinations(array_keys($frequent), self::MIN_SHARED_ATTRIBUTES) as $attrCombo) {
			$sharedCallIndices = null;
			
			foreach ($attrCombo as $attr) {
				if ($sharedCallIndices === null) {
					$sharedCallIndices = $frequent[$attr];
				} else {
					$sharedCallIndices = array_values(array_intersect($sharedCallIndices, $frequent[$attr]));
				}
			}
			
			if (count($sharedCallIndices) < self::MIN_OCCURRENCES) {
				continue;
			}
			
			$fullIntersection = $this->intersectAttributes(
				array_map(fn($i) => $calls[$i]['attributes'], $sharedCallIndices)
			);
			
			if (count($fullIntersection) < self::MIN_SHARED_ATTRIBUTES) {
				continue;
			}
			
			ksort($fullIntersection);
			$key = json_encode($fullIntersection);
			
			if (! isset($candidates[$key]) || count($sharedCallIndices) > $candidates[$key]['count']) {
				$candidates[$key] = [
					'attributes' => $fullIntersection,
					'count' => count($sharedCallIndices),
					'node' => $calls[$sharedCallIndices[0]]['node'],
				];
			}
		}
		
		return array_values($candidates);
	}
	
	protected function intersectAttributes(array $attributeSets): array
	{
		if (empty($attributeSets)) {
			return [];
		}
		
		$result = array_shift($attributeSets);
		
		foreach ($attributeSets as $attrs) {
			$newResult = [];
			foreach ($result as $key => $value) {
				if (array_key_exists($key, $attrs) && $attrs[$key] === $value) {
					$newResult[$key] = $value;
				}
			}
			$result = $newResult;
		}
		
		return $result;
	}
	
	protected function combinations(array $items, int $size): \Generator
	{
		$items = array_values($items);
		$n = count($items);
		
		if ($size > $n) {
			return;
		}
		
		$indices = range(0, $size - 1);
		
		yield array_map(fn($i) => $items[$i], $indices);
		
		while (true) {
			$i = $size - 1;
			
			while ($i >= 0 && $indices[$i] === $n - $size + $i) {
				$i--;
			}
			
			if ($i < 0) {
				return;
			}
			
			$indices[$i]++;
			
			for ($j = $i + 1; $j < $size; $j++) {
				$indices[$j] = $indices[$j - 1] + 1;
			}
			
			yield array_map(fn($i) => $items[$i], $indices);
		}
	}
}
