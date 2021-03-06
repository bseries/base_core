<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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

	// Accepts two options:
	//
	// - `translate` _boolean|string_: When `false` will disable translating results. When
	//   a locale string will translate into this locale. By default results are translated
	//   into the current effective locale.
	//
	// - `available` _boolean|array_: When an array of i.e. locales will restrict results to just
	//  these. If `true` will no limit results in any way. By default uses a set of i.e. available
	//  locales from configuration.
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
			if (isset($options['conditions']['name'])) {
				return static::_formatFirstByName($data, $options['conditions']['name']);
			}
			return static::_formatFirstById($data, $options['conditions']['id']);
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

	protected static function _formatFirstById(array $data, $id) {
		return isset($data[$id]) ? static::create($data[$id]) : false;
	}

	protected static function _formatFirstByName(array $data, $name) {
		foreach ($data as $item) {
			if ($item['name'] === $name) {
				return static::create($item);
			}
		}
		return false;
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