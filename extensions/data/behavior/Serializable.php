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
use lithium\util\Set;
use lithium\util\Inflector;

class Serializable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => []
	];

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		$config += $defaults;
		$config['fields'] = Set::normalize($config['fields']);

		foreach ($config['fields'] as $field => &$pass) {
			if (!$pass) {
				$pass = 'json';
			}
		}
		return $config;
	}

	protected static function _filters($model, Behavior $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			foreach ($behavior->config('fields') as $field => $type) {
				if (isset($params['data'][$field])) {
					$data = $params['data'][$field];
				} elseif (isset($params['entity']->{$field})) {
					$data = $params['entity']->{$field};
				} else {
					continue;
				}

				$params['data'][$field] = static::_normalize($data, $type);
				$params['data'][$field] = static::_serialize($data, $type);
			}
			$result = $next($params);

			// After save unserialize again, so we can work.

			foreach ($behavior->config('fields') as $field => $type) {
				if (!isset($params['entity']->{$field})) {
					continue;
				}
				$params['entity']->{$field} = static::_unserialize($params['entity']->{$field}, $type);
			}

			return $result;
		});
		Filters::apply($model, 'find', function($params, $next) use ($behavior) {
			$result = $next($params);

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
			$methods[Inflector::camelize($field, false)] = function($entity, array $options = []) use ($behavior, $field, $pass) {
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