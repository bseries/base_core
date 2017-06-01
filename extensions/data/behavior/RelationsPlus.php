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

use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;
use lithium\util\Collection;

// Nested relation keys may contain array data when saving.
class RelationsPlus extends \li3_behaviors\data\model\Behavior {

	protected static function _methods($model, Behavior $behavior) {
		$methods = [];

		$object = $model::invokeMethod('_object');
		$key = $model::key();

		$normalize = function(array $relations) {
			$normalized = [];

			foreach ($relations as $name => $relation) {
				if (is_numeric($name)) {
					$normalized[$relation] = [];
				} else {
					$normalized[$name] = $relation;
				}
			}
			return $normalized;
		};

		if (isset($object->hasOne)) {
			$methods += static::_methodsForHasOne($key, $normalize($object->hasOne));
		}
		if (isset($object->hasMany)) {
			$methods += static::_methodsForHasMany($key, $normalize($object->hasMany));
		}
		if (isset($object->belongsTo)) {
			$methods += static::_methodsForBelongsTo($key, $normalize($object->belongsTo));
		}

		$results = [];
		foreach ($methods as $name => $value) {
			if ($model::respondsTo($name)) {
				continue;
			}
			$results[$name] = $value;
		}
		return $results;
	}

	protected static function _methodsForHasOne($key, array $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$isGenericQuery = !array_diff_key($query, ['force' => null]);
				$force = !empty($query['force']);
				unset($query['force']);

				if ($isGenericQuery && !$force && $entity->{$lower} && is_object($entity->{$lower})) {
					if ($entity->{$lower}->id === null) {
						return null;
					}
					return $entity->{$lower};
				}
				if (!$entity->{$key}) {
					return null;
				}
				if (!isset($query['conditions'])) {
					$query['conditions'] = [];
				}
				$query['conditions'] += [$relation['key'] => $entity->{$key}];

				return $relation['to']::find('first', $query);
			};
		}
		return $methods;
	}

	protected static function _methodsForHasMany($key, array $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$isGenericQuery = !array_diff_key($query, ['force' => null]);
				$force = !empty($query['force']);
				unset($query['force']);

				if ($isGenericQuery && !$force && $entity->{$lower} && is_object($entity->{$lower})) {
					return $entity->{$lower}->find(function($item) {
						return $item->id !== null;
					});
				}
				if (!$entity->{$key}) {
					return new Collection();
				}
				if (!isset($query['conditions'])) {
					$query['conditions'] = [];
				}
				$query['conditions'] += [$relation['key'] => $entity->{$key}];

				return $relation['to']::find('all', $query);
			};
		}
		return $methods;
	}

	protected static function _methodsForBelongsTo($key, array $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$isGenericQuery = !array_diff_key($query, ['force' => null]);
				$force = !empty($query['force']);
				unset($query['force']);

				if ($isGenericQuery && !$force && $entity->{$lower} && is_object($entity->{$lower})) {
					if ($entity->{$lower}->id === null) {
						return null;
					}
					return $entity->{$lower};
				}
				if (!$entity->{$relation['key']}) {
					return null;
				}
				if (!isset($query['conditions'])) {
					$query['conditions'] = [];
				}
				$query['conditions'] += [$key => $entity->{$relation['key']}];

				return $relation['to']::find('first', $query);
			};
		}
		return $methods;
	}
}

?>