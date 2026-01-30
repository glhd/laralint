<?php

namespace Glhd\LaraLint\Linters;

use Glhd\LaraLint\Contracts\Matcher;
use Glhd\LaraLint\Linters\Strategies\CollectingLinter;
use Glhd\LaraLint\Result;
use Glhd\LaraLint\ResultCollection;
use Illuminate\Support\Collection;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\StringLiteral;

class OverlappingFactoryStates extends CollectingLinter
{
	protected int $minSharedAttributes = 3;

	protected int $minOccurrences = 4;

	protected array $factoryCalls = [];

	protected function matcher(): Matcher
	{
		return $this->treeMatcher()
			->withChild(fn(CallExpression $node) => $this->isFactoryCreateOrMakeCall($node));
	}

	protected function onMatch(Collection $nodes): ?Result
	{
		$node = $nodes->first();
		$extracted = $this->extractFactoryCallData($node);

		if ($extracted) {
			$this->factoryCalls[] = $extracted;
		}

		return null;
	}

	protected function lintCollectedNodes(Collection $nodes): ResultCollection
	{
		$results = new ResultCollection();

		$grouped = collect($this->factoryCalls)->groupBy('model');

		foreach ($grouped as $model => $calls) {
			if ($calls->count() < $this->minOccurrences) {
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

		if (!$callable instanceof MemberAccessExpression) {
			return false;
		}

		$methodName = $callable->memberName->getText($node->getFileContents());

		if (!in_array($methodName, ['create', 'make'], true)) {
			return false;
		}

		$dereferencable = $callable->dereferencableExpression ?? null;

		if (!$dereferencable instanceof CallExpression) {
			return false;
		}

		$innerCallable = $dereferencable->callableExpression ?? null;

		if ($innerCallable instanceof ScopedPropertyAccessExpression) {
			$memberName = $innerCallable->memberName->getText($node->getFileContents());
			return $memberName === 'factory';
		}

		if ($innerCallable instanceof MemberAccessExpression) {
			$memberName = $innerCallable->memberName->getText($node->getFileContents());
			return $memberName === 'factory';
		}

		return false;
	}

	protected function extractFactoryCallData(CallExpression $node): ?array
	{
		$callable = $node->callableExpression;
		$dereferencable = $callable->dereferencableExpression;
		$innerCallable = $dereferencable->callableExpression;

		$model = null;

		if ($innerCallable instanceof ScopedPropertyAccessExpression) {
			$scopeResolution = $innerCallable->scopeResolutionQualifier ?? null;
			if ($scopeResolution instanceof QualifiedName) {
				$model = $scopeResolution->getText();
			}
		}

		if (!$model) {
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

	protected function extractAttributes(?ArgumentExpressionList $argList): array
	{
		if (!$argList) {
			return [];
		}

		$attributes = [];

		foreach ($argList->getChildNodes() as $child) {
			if (!$child instanceof ArgumentExpression) {
				continue;
			}

			$expression = $child->expression ?? null;

			if (!$expression instanceof ArrayCreationExpression) {
				continue;
			}

			$elementList = $expression->arrayElements ?? null;

			if (!$elementList instanceof ArrayElementList) {
				continue;
			}

			foreach ($elementList->getChildNodes() as $element) {
				if (!$element instanceof ArrayElement) {
					continue;
				}

				$key = $this->extractLiteralValue($element->elementKey);
				$value = $this->extractLiteralValue($element->elementValue);

				if ($key !== null && $value !== null) {
					$attributes[$key] = $value;
				}
			}
		}

		return $attributes;
	}

	protected function extractLiteralValue(?Node $node): mixed
	{
		if (!$node) {
			return null;
		}

		if ($node instanceof StringLiteral) {
			$text = $node->getText();
			return trim($text, "\"'");
		}

		if ($node instanceof Node\NumericLiteral) {
			$text = $node->getText();
			return is_numeric($text) && str_contains($text, '.') ? (float) $text : (int) $text;
		}

		if ($node instanceof Node\ReservedWord) {
			$text = strtolower($node->getText());
			if ($text === 'true') {
				return true;
			}
			if ($text === 'false') {
				return false;
			}
			if ($text === 'null') {
				return null;
			}
		}

		return null;
	}

	protected function findMaximalOverlaps(array $calls): array
	{
		if (count($calls) < $this->minOccurrences) {
			return [];
		}

		$attrToCalls = [];

		foreach ($calls as $index => $call) {
			foreach ($call['attributes'] as $key => $value) {
				$attr = json_encode([$key => $value]);
				$attrToCalls[$attr][] = $index;
			}
		}

		$frequent = array_filter($attrToCalls, fn($indices) => count($indices) >= $this->minOccurrences);

		if (count($frequent) < $this->minSharedAttributes) {
			return [];
		}

		$candidates = [];

		foreach ($this->combinations(array_keys($frequent), $this->minSharedAttributes) as $attrCombo) {
			$sharedCallIndices = null;

			foreach ($attrCombo as $attr) {
				if ($sharedCallIndices === null) {
					$sharedCallIndices = $frequent[$attr];
				} else {
					$sharedCallIndices = array_values(array_intersect($sharedCallIndices, $frequent[$attr]));
				}
			}

			if (count($sharedCallIndices) < $this->minOccurrences) {
				continue;
			}

			$fullIntersection = $this->intersectAttributes(
				array_map(fn($i) => $calls[$i]['attributes'], $sharedCallIndices)
			);

			if (count($fullIntersection) < $this->minSharedAttributes) {
				continue;
			}

			ksort($fullIntersection);
			$key = json_encode($fullIntersection);

			if (!isset($candidates[$key]) || count($sharedCallIndices) > $candidates[$key]['count']) {
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
