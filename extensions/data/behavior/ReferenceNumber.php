<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_core\extensions\data\behavior;

use Exception;

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

	protected static function _config($model, $behavior, $config, $defaults) {
		$config += $defaults;

		if (!$config['models']) {
			$config['models'] = array_merge($config['models'], [$model]);
		}
		if ($config['sort'] === '/^(.*)$/') {
			$config['useSourceSort'] = true;
		}
		return $config;
	}

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($model, $behavior) {
			$field = $behavior->config('field');

			if (!$params['entity']->exists() && empty($params['data'][$field])) {
				$params['data'][$field] = static::_nextReferenceNumber($model, $behavior, $params['entity']->data());
			}
			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _nextReferenceNumber($model, $behavior, $entity) {
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
		return $generator($entity, $extractor(array_pop($numbers)) + 1);
	}

	protected static function _sorter($model, $behavior) {
		$config = $behavior->config('sort');

		return function($a, $b) use ($config) {
			$extract = function($value) use ($config) {
				if (!preg_match($config, $value, $matches)) {
					// Cannot throw exception here as this modifies the value in sort.
					$message = "Cannot extract number for sorting from value `{$value}`.`";
					trigger_error(E_USER_NOTICE, $message);

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

	protected static function _extractor($model, $behavior) {
		$config = $behavior->config('extract');

		return function($value) use ($config) {
			if (!preg_match($config, $value, $matches)) {
				$message = "Cannot extract number from value `{$value}`.`";
				trigger_error(E_USER_NOTICE, $message);

				return $value;
			}
			return (integer) $matches[1];
		};
	}

	protected static function _generator($model, $behavior) {
		$config = $behavior->config('generate');

		if (is_string($config)) {
			return function($data, $value) use ($config) {
				return sprintf(strftime($config), $value);
			};
		}
		return $config;
	}
}

?>