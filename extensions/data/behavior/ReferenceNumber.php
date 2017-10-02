<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use li3_behaviors\data\model\Behavior;
use lithium\aop\Filters;
use lithium\data\Entity;
use lithium\util\Validator;

// Deals with reference numbers as used on invoices or as customer numbers. When
// configured infers the next available number from the last present one. Allows
// to provide custom sorting to correctly sort numbers.
class ReferenceNumber extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		// The field where the reference number is stored.
		'field' => 'number',

		// Either a regular expression containing a single capture group or a data source fragment
		// containing a field placeholder for - more performant and feature rich - source sorting.
		//
		// By default and when `true` sorts over the whole field using natural source sorting.
		//
		// When a regular expression the part matched by the capture group will be extracted and
		// then passed to the `'compare'` function. Without source sorting, data retrieved through
		// find operations and ordered by `'field'` will most likely not have the correct order,
		// especially when differing number formats are used that do not sort naturally.
		//
		// When a data source fragment, the string must contain exactly one placeholder into
		// which the field name will be inserted, i.e.
		// `REGEXP_REPLACE(%s, "([0-9]{4})(0*)([0-9]*)", "\\1\\3")`
		'sort' => true,

		// Comparison function to use for sorting i.e. `function($a, $b) { return /* ... */; }`,
		// defaults to`strcmp`. Will only be used if `'sort'` is a regular expression.
		'compare' => 'strcmp',

		// When string passed through strftime and sprintf. When `false`, disables generation
		// of next reference numbers.
		'generate' => '%%04.d',

		// An initial number that should be considered present when no generated numbers
		// are. Useful when migrating from previous systems. When `null` will not be
		// considered.
		'initial' => null,

		// Regular expression containing a single capture group, to extract the part of
		// the number to bump, when calculating the next available number. Not used when
		// `'generate'` is `false`.
		'extract' => '/^([0-9]{4})$/',

		// Models to use when calculating the next reference number. If empty will use the
		// current model only. Unused when `'generate'` is `false`.
		//
		// Models must all have the ReferenceNumber behavior attached and should have the
		// same settings for `extract`, `generate`, `sort`, `compare`, and `models`.
		'models' => []
	];

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		$config += $defaults;

		if (!$config['models']) {
			$config['models'] = array_merge($config['models'], [$model]);
		}
		return $config;
	}

	// Returns sort SQL fragment with inserted escaped field name.
	protected static function _sourceSort($model, Behavior $behavior) {
		return sprintf(
			$behavior->config('sort'),
			$model::connection()->name($behavior->config('field'))
		);
	}

	// Will assign a new reference number only if the entity doesn't already exist and
	// a number wasn't manually provided.
	protected static function _filters($model, Behavior $behavior) {
		Filters::apply($model, 'find', function($params, $next) use ($model, $behavior) {
			$field = $behavior->config('field');
			$sort = $behavior->config('sort');

			if ($sort === true) { // simply pass through, using natural sorting
				return $next($params);
			}
			if (!is_string($sort) || $sort[0] === '/') { // not supported
				return $next($params);
			}
			$order = $params['options']['order'];

			if (!$order || !isset($order[$field]) && !in_array($field, $order)) {
				return $chain->next($self, $params, $chain);
			}

			// $order may be normalized [field => direction] or simply [field] or a mix thereof.
			if (isset($order[$field])) {
				$dir = $order[$field];
				unset($order[$field]);
				$order[static::_sourceSort($model, $behavior)] = $dir;
			} elseif (in_array($field, $order)) {
				unset($order[array_search($field, $order)]);
				$order[] = static::_sourceSort($model, $behavior);
			}
			$params['options']['order'] = $order;

			return $next($params);
		});

		if ($behavior->config('generate')) {
			Filters::apply($model, 'save', function($params, $next) use ($model, $behavior) {
				$field = $behavior->config('field');

				if (!$params['entity']->exists() && empty($params['data'][$field])) {
					if (isset($params['options']['whitelist'])) {
						$params['options']['whitelist'][] = $field;
					}
					$params['data'][$field] = static::_nextReferenceNumber(
						$model, $behavior, $params['data'] + $params['entity']->data()
					);
				}
				return $next($params);
			});
		}
	}

	public static function nextReferenceNumber($model, Behavior $behavior, Entity $item = null) {
		if ($behavior->config('generate')) {
			return static::_nextReferenceNumber(
				$model, $behavior, $item ? $item->data() : []
			);
		}
	}

	protected static function _nextReferenceNumber($model, Behavior $behavior, array $entity) {
		if (!$behavior->config('generate')) {
			return false;
		}
		$numbers = [];
		$sourceSort = static::_sourceSort($model, $behavior);

		$field = $behavior->config('field');
		$sort = $behavior->config('sort');
		$useSourceSort = $sort === true || is_string($sort) && $sort[0] !== '/';

		foreach ($behavior->config('models') as $model) {
			$behavior = $model::behavior(__CLASS__);

			if ($useSourceSort) {
				$results = $model::find('all', [
					'fields' => [$field],
					'order' => [
						($sort === true ? $field : static::_sourceSort($model, $behavior)) => 'DESC'
					],
					'limit' => 1
				]);
			} else {
				$results = $model::find('all', [
					'fields' => [$field]
				]);
			}
			foreach ($results as $result) {
				if ($result->$field) {
					$numbers[] = $result->$field;
				}
			}
		}
		$generator = static::_generator($model, $behavior);
		$extractor = static::_extractor($model, $behavior);

		if (!$numbers) {
			if ($initial = $behavior->config('initial')) {
				$numbers[] = $initial;
			} else {
				return $generator($entity, 1);
			}
		}
		if (!$useSourceSort) {
			uasort($numbers, static::_sorter($model, $behavior));
		}
		return $generator($entity, $extractor($entity, array_pop($numbers)) + 1);
	}

	protected static function _sorter($model, Behavior $behavior) {
		$sort = $behavior->config('sort');
		$compare = $behavior->config('compare');

		$extract = function($value) use ($sort) {
			if (!preg_match($sort, $value, $matches)) {
				// Cannot throw exception here as this modifies the value in sort.
				$message = "Cannot extract number for sorting from value `{$value}`.`";
				trigger_error($message, E_USER_NOTICE);

				return false;
			}
			return $matches[1];
		};

		return function($a, $b) use ($extract, $compare) {
			$a = $extract($a);
			$b = $extract($b);

			if (!$a) {
				return -1;
			}
			if (!$b) {
				return 1;
			}
			return $compare($a, $b);
		};
	}

	protected static function _extractor($model, Behavior $behavior) {
		$config = $behavior->config('extract');

		if (is_callable($config)) {
			return $config;
		}
		return function($data, $value) use ($config) {
			if (!preg_match($config, $value, $matches)) {
				$message = "Cannot extract number from value `{$value}`.`";
				trigger_error($message, E_USER_NOTICE);

				return $value;
			}
			return (integer) $matches[1];
		};
	}

	protected static function _generator($model, Behavior $behavior) {
		$config = $behavior->config('generate');

		if (is_callable($config)) {
			return $config;
		}
		return function($data, $value) use ($config) {
			return sprintf(strftime($config), $value);
		};
	}
}

Validator::add('isUniqueReferenceNumber', function($value, $format, $options) {
	$conditions = [
		$options['field'] => $value
	];
	if (!empty($options['values']['id'])) {
		$conditions['id'] = ['!=' => $options['values']['id']];
	}
	return !$options['model']::find('count', compact('conditions'));
});

?>