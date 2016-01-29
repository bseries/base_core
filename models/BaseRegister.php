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
use InvalidArgumentException;
use OutOfBoundsException;
use lithium\util\Collection;

class BaseRegister extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Access'
	];

	protected $_data = [];

	public static function register($name, array $data = []) {
		trigger_error('BaseRegister is deprecated.', E_USER_DEPRECATED);

		static::_object()->_data[$name] = static::create(
			static::_register(compact('name') + $data)
		);
	}

	// Re-implement to customize behavior.
	protected static function _register(array $data) {
		trigger_error('BaseRegister is deprecated.', E_USER_DEPRECATED);

		return $data;
	}

	public static function find($type, array $options = []) {
		trigger_error('BaseRegister is deprecated.', E_USER_DEPRECATED);

		if (isset($options['conditions']['id'])) {
			trigger_error('The `id` condition is deprecated for `name`.', E_USER_DEPRECATED);

			$options['conditions']['name'] = $options['conditions']['id'];
			unset($options['conditions']['id']);
		}
		$data = static::_object()->_data;

		if ($type == 'all') {
			return new Collection(compact('data'));
		} elseif ($type == 'list') {
			$results = [];
			foreach ($data as $item) {
				$results[$item->name] = $item->title();
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
		if (!isset($data[$name])) {
			$message  = "Item `{$name}` not registered. Only have keys: ";
			$message .= implode(', ', array_keys($data)) ?: '<none>';
			throw new OutOfBoundsException($message);
		}
		return $data[$name];
	}

	public function title($entity) {
		trigger_error('BaseRegister is deprecated.', E_USER_DEPRECATED);

		return $entity->title;
	}
}

?>