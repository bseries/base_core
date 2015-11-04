<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

namespace base_core\models;

use Exception;
use OutOfBoundsException;
use InvalidArgumentException;
use lithium\util\Collection;

class BaseRegister extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Access'
	];

	protected static $_data = [];

	public static function register($name, array $data = []) {
		throw new Exception('Must re-implement.');
	}

	public static function find($type, array $options = []) {
		if (isset($options['conditions']['id'])) {
			trigger_error('The `id` condition is deprecated for `name`.', E_USER_DEPRECATED);

			$options['conditions']['name'] = $options['conditions']['id'];
			unset($options['conditions']['id']);
		}

		if ($type == 'all') {
			return new Collection(['data' => static::$_data]);
		} elseif ($type == 'list') {
			$useTitle = method_exists($item, 'title');

			$results = [];
			foreach (static::$_data as $item) {
				$results[$item->name] = $useTitle ? $item->title() : $item->name;
			}
			return $results;
		} elseif ($type == 'first') {
			if (!isset($options['conditions']['name'])) {
				throw new InvalidArgumentException('No `name` condition given.');
			}
			$name = $options['conditions']['name'];
		} else {
			$name = $type;
		}
		if (!isset(static::$_data[$name])) {
			throw new OutOfBoundsException("Item `{$name}` not registered.");
		}
		return static::$_data[$name];
	}
}

?>