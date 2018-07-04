<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\data\behavior;

use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;
use lithium\util\Collection;

// Nested relation keys may contain array data when saving.
class RelationsPlus extends \li3_behaviors\data\model\Behavior {

	protected static function _methods($model, Behavior $behavior) {
		$methods = [];

		$object = $model::object();
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
			if (method_exists($object, $name)) {
				continue;
			}
			$results[$name] = $value;
		}
		return $results;
	}

	protected static function _methodsForHasOne($key, array $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			// TODO: Remove once we support short relation definition syntax, whre
			//       $relation is empty.
			if (!$relation) {
				continue;
			}
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
			// TODO: Remove once we support short relation definition syntax, whre
			//       $relation is empty.
			if (!$relation) {
				continue;
			}
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$isGenericQuery = !array_diff_key($query, ['force' => null]);
				$force = !empty($query['force']);
				unset($query['force']);

				if ($isGenericQuery && !$force && $entity->{$lower}) {
					if (is_object($entity->{$lower})) {
						return $entity->{$lower}->find(function($item) {
							return $item->id !== null;
						});
					} elseif (is_array($entity->{$lower})) {
						$data = [];
						foreach ($entity->{$lower} as $key => $value) {
							if ($key === 'new') {
								continue;
							}
							$data[$key] = $relation['to']::create(['id' => $key] + $value);
						}
						return new Collection(compact('data'));
					}
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
			// TODO: Remove once we support short relation definition syntax, whre
			//       $relation is empty.
			if (!$relation) {
				continue;
			}
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