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

namespace base_core\models;

use Collator;
use Exception;
use lithium\core\Environment;
use lithium\g11n\Catalog;
use lithium\storage\Cache;
use lithium\util\Collection;

class BaseG11n extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function find($type, array $options = []) {
		$options += [
			'translate' => static::_translate(),
			'available' => static::_available()
		];

		if (!$data = Cache::read('default', $cacheKey = static::_cacheKey($options))) {
			$data = static::_data($options);

			Cache::write('default', $cacheKey, $data, Cache::PERSIST);
		}

		if ($type == 'all') {
			return static::_formatAll($data);
		} elseif ($type == 'list') {
			return static::_formatList($data, 'id', 'name', $options['translate']);
		} elseif ($type == 'first') {
			return static::_formatFirst($data, $options['conditions']['id']);
		}
		throw new Exception("Invalid find type `{$type}` for g11n data.");
	}

	protected static function _translate() {
		return Environment::get('locale');
	}

	// Implement
	protected static function _available() {
		throw new Exception('Not implemented, must override in subclass.');
	}

	// Implement
	protected static function _data(array $options) {
		throw new Exception('Not implemented, must override in subclass.');
	}

	protected static function _cacheKey(array $options) {
		$prefix = explode('\\', get_called_class());
		$prefix = strtolower(array_pop($prefix));

		return $prefix . '_' . md5(serialize($options));
	}

	protected static function _formatAll(array $data) {
		foreach ($data as &$item) {
			$item = static::create($item);
		}
		return new Collection(['data' => $data]);
	}

	protected static function _formatFirst(array $data, $id) {
		if (!isset($data[$id])) {
			return false;
		}
		return static::create($data[$id]);
	}

	protected static function _formatList(array $data, $key, $value, $translate = null) {
		$list = [];

		foreach ($data as $item) {
			$list[$item['id']] = $item['name'];
		}
		if ($translate) {
			$collator = new Collator($translate);
			$collator->asort($list);
		} else {
			asort($list);
		}
		return $list;
	}
}

?>