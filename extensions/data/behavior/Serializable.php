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

use Exception;
use lithium\util\Set;

class Serializable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => []
	];

	protected static function _config($model, $behavior, $config, $defaults) {
		$config += $defaults;
		$config['fields'] = Set::normalize($config['fields']);

		foreach ($config['fields'] as $field => &$pass) {
			if (!$pass) {
				$pass = 'json';
			}
		}
		return $config;
	}

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			foreach ($behavior->config('fields') as $field => $type) {
				if (!isset($params['data'][$field])) {
					continue;
				}
				$params['data'][$field] = static::_normalize($params['data'][$field], $type);
				$params['data'][$field] = static::_serialize($params['data'][$field], $type);
			}
			$result = $chain->next($self, $params, $chain);

			foreach ($behavior->config('fields') as $field => $type) {
				if (!isset($params['entity']->{$field})) {
					continue;
				}
				$params['entity']->{$field} = static::_unserialize($params['entity']->{$field}, $type);
			}

			return $result;
		});
		$model::applyFilter('find', function($self, $params, $chain) use ($behavior) {
			$result = $chain->next($self, $params, $chain);

			if (is_a($result, '\lithium\data\Collection')) {
				foreach ($result as $r) {
					foreach ($behavior->config('fields') as $field => $type) {
						if (!isset($r->$field)) {
							continue;
						}
						$r->$field = static::_unserialize($r->$field, $type);
					}
				}
			} elseif (is_a($result, '\lithium\data\Entity')) {
				foreach ($behavior->config('fields') as $field => $type) {
					if (!isset($result->$field)) {
						continue;
					}
					$result->$field = static::_unserialize($result->$field, $type);
				}
			}
			return $result;
		});


		$methods = [];
		foreach ($behavior->config('fields') as $field => $pass) {
			$methods[$field] = function($entity, array $options = []) use ($behavior, $field, $pass) {
				$options += ['serialized' => true];

				$result = $entity->{$field};
				$result = static::_normalize($result, $pass);

				if (!$options['serialized']) {
					return $result;
				}
				return static::_serialize($result, $pass);
			};
		}
		$model::instanceMethods($methods);
	}

	protected static function _normalize($value, $pass) {
		$normalize = function($values) {
			if (is_numeric(key($values))) {
				$values = array_filter(array_map('trim', $values));
				sort($values);
			}
			return $values;
		};

		if (is_object($value)) {
			return $normalize($value->data());
		} elseif ($value === null) {
			return [];
		} elseif (is_string($value)) {
			return $normalize(static::_unserialize($value, $pass));
		} elseif (is_array($value)) {
			return $normalize($value);
		}
		throw new Exception('Field value is in unsupported format.');
	}

	protected static function _serialize($value, $type) {
		switch ($type) {
			case 'php':
				return serialize($value);
			case 'json':
				return json_encode($value);
			default:
				return is_array($value) ? implode($type, $value) : $value;
		}
	}

	protected static function _unserialize($value, $type) {
		if ($value === null || $value === '') {
			return [];
		}
		switch ($type) {
			case 'php':
				return unserialize($value);
			case 'json':
				return json_decode($value, true);
			default:
				if (!is_string($value)) {
					return $value;
				}
				return explode($type, $value);
		}
	}
}

?>