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
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

// Continuous, sequential, unique.
// Cannot mixed different style numbers in a column.
// If you can infer the number from row data (implement it using a model
// instance method) do that. Otherwise use this behavior.
class ReferenceNumber extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		// The field where the reference number is stored.
		'field' => 'number',

		// Regular expression containing a single capture group, to extract the part of
		// the number to bump, when calculating the next available number.
		'extract' => '/^([0-9]{4})$/',

		// Regular expression containing a single capture group, to extract the string to
		// sort on. This may equal the `'extract'` configuration setting.
		'sort' => '/^(.*)$/',

		// Comparison function to use for sorting.
		'compare' => 'strcmp',

		// When string passed through strftime and sprintf.
		'generate' => '%%04.d',

		// When `true` will use - more performant - native data source sorting. The
		// `'sort'` and `'compare'` settings are than ignored. Source sorting will always
		// sort over the whole number and use string comparison. Will be automatically
		// enabled when the two settings are unmodified at their defaults. Set to `false`
		// to ensure source sorting is never used.
		'useSourceSort' => null,

		// Models to use when calculating the next reference number. If empty
		// will use the current model only.
		//
		// Models must all have the ReferenceNumber behavior attached and
		// should have the same settings for `extract`, `generate`, `sort`, `compare`,
		// and `models`.
		'models' => []
	];

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		$config += $defaults;

		if (!$config['models']) {
			$config['models'] = array_merge($config['models'], [$model]);
		}
		return $config;
	}

	// Will assign a new reference number only if the entity doesn't already exist and
	// a number wasn't manually provided.
	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($model, $behavior) {
			$field = $behavior->config('field');

			if (!$params['entity']->exists() && empty($params['data'][$field])) {
				if (isset($params['options']['whitelist'])) {
					$params['options']['whitelist'][] = $field;
				}
				$params['data'][$field] = static::_nextReferenceNumber(
					$model, $behavior, $params['data'] + $params['entity']->data()
				);
			}
			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _nextReferenceNumber($model, Behavior $behavior, array $entity) {
		$numbers = [];
		$useSourceSort = $behavior->config('useSourceSort');

		if ($useSourceSort === null) {
			$sortDefault    = $behavior->config('sort') === static::$_defaults['sort'];
			$compareDefault = $behavior->config('compare') === static::$_defaults['compare'];

			$useSourceSort = $sortDefault && $compareDefault;
		}

		foreach ($behavior->config('models') as $model) {
			$behavior = $model::behavior(__CLASS__);

			$field = $behavior->config('field');

			if ($useSourceSort) {
				$results = $model::find('all', [
					'fields' => [$field],
					'order' => [$field => 'DESC'],
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
			return $generator($entity, 1);
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

?>