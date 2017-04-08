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

// Continuous, sequential, unique.
// Cannot mixed different style numbers in a column.
// If you can infer the number from row data (implement it using a model
// instance method) do that. Otherwise use this behavior.
class ReferenceNumber extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'number',

		'extract' => '/^([0-9]{4})$/',
		'sort' => '/^(.*)$/',

		// When string passed through strftime and sprintf.
		'generate' => '%%04.d',

		// Set to true when your sort pattern spreads
		// over the whole string. Then optimizations can
		// happen at source/database level. Automatically enabled
		// when `sort` equals the default setting.
		'useSourceSort' => false,

		// Models to use when calculating the next reference number. If empty
		// will use the current model only.
		//
		// Models must all have the ReferenceNumber behavior attached and
		// should have the same settings for `extract`, `generate`, `sort`
		// and `models`.
		'models' => []
	];

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		$config += $defaults;

		if (!$config['models']) {
			$config['models'] = array_merge($config['models'], [$model]);
		}
		if ($config['sort'] === '/^(.*)$/') {
			$config['useSourceSort'] = true;
		}
		return $config;
	}

	// Will assign a new reference number only if the entity doesn't already exist and
	// a number wasn't manually provided.
	protected static function _filters($model, Behavior $behavior) {
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

	protected static function _nextReferenceNumber($model, Behavior $behavior, array $entity) {
		$numbers = [];
		$useSourceSort = $behavior->config('useSourceSort');

		foreach ($behavior->config('models') as $model) {
			$behavior = $model::behavior(__CLASS__);

			$field = $behavior->config('field');

			if (!$useSourceSort) {
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
		if ($useSourceSort) {
			sort($numbers);
		} else {
			uasort($numbers, static::_sorter($model, $behavior));
		}
		return $generator($entity, $extractor($entity, array_pop($numbers)) + 1);
	}

	protected static function _sorter($model, Behavior $behavior) {
		$config = $behavior->config('sort');

		return function($a, $b) use ($config) {
			$extract = function($value) use ($config) {
				if (!preg_match($config, $value, $matches)) {
					// Cannot throw exception here as this modifies the value in sort.
					$message = "Cannot extract number for sorting from value `{$value}`.`";
					trigger_error($message, E_USER_NOTICE);

					return false;
				}
				return $matches[1];
			};
			$a = $extract($a);
			$b = $extract($b);

			if (!$a) {
				return -1;
			}
			if (!$b) {
				return 1;
			}
			return strcmp($a, $b);
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