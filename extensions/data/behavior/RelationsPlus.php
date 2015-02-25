<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\data\behavior;

use lithium\util\Inflector;

class RelationsPlus extends \li3_behaviors\data\model\Behavior {

	protected static function _methods($model, $behavior) {
		$methods = [];

		$object = $model::invokeMethod('_object');
		$key = $model::key();

		if (isset($object->hasObe)) {
			$methods += static::_methodsForHasOne($key, $object->hasOne);
		}
		if (isset($object->hasMany)) {
			$methods += static::_methodsForHasMany($key, $object->hasMany);
		}
		if (isset($object->belongsTo)) {
			$methods += static::_methodsForBelongsTo($key, $object->belongsTo);
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
	public function order($entity, array $query = []) {
		if ($query) {
			trigger_error('Carts::order with query has been deprecated.', E_USER_DEPRECATED);
		}
		if ($entity->order && !$query) {
			return $entity->order;
		}
		return Orders::find('first', [
			'conditions' => [
				'ecommerce_cart_id' => $entity->id
			]
		] + $query);
	}


	protected static function _methodsForHasOne($key, $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$query += ['force' => false];

				$force = $query['force'];
				unset($query['force']);

				if (!$query && !$force && $entity->{$lower}) {
					return $entity->{$lower};
				}
				$query['conditions'][$relation['key']] = $entity->{$key};

				return $relation['to']::find('first', $query);
			};
		}
		return $methods;
	}

	protected static function _methodsForHasMany($key, $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$query += ['force' => false];

				$force = $query['force'];
				unset($query['force']);

				if (!$query && !$force && $entity->{$lower}) {
					return $entity->{$lower};
				}
				$query['conditions'][$relation['key']] = $entity->{$key};

				return $relation['to']::find('all', $query);
			};
		}
		return $methods;
	}

	protected static function _methodsForBelongsTo($key, $relations) {
		$methods = [];

		foreach ($relations as $name => $relation) {
			$lower = lcfirst($name);

			$methods[$lower] = function($entity, array $query = []) use ($lower, $relation, $key) {
				$query += ['force' => false];

				$force = $query['force'];
				unset($query['force']);

				if (!$query && !$force && $entity->{$lower}) {
					return $entity->{$lower};
				}
				$query['conditions'][$key] = $entity->{$relation['key']};

				return $relation['to']::find('first', $query);
			};
		}
		return $methods;
	}
}

?>