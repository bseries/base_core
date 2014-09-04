<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\models;

use base_core\extensions\cms\Settings;
use lithium\g11n\Catalog;
use Collator;
use lithium\util\Collection;
use lithium\core\Environment;
use lithium\storage\Cache;

class Countries extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function find($type, array $options = []) {
		$options += ['locale' => Environment::get('locale')];
		$available = Settings::read('availableCountries');

		$cacheKey = 'countries_' . md5(serialize([
			$available,
			$options['locale']
		]));
		if (!$data = Cache::read('default', $cacheKey)) {
			$data = [];
			$results = Catalog::read(true, 'territory', $options['locale']);

			foreach ($results as $key => $value) {
				if (is_numeric($key)) {
					continue;
				}
				$data[$key] = $value;
			}
			if ($available) {
				$all = $data;
				$data = [];

				foreach ($available as $currency) {
					$data[$currency] = $all[$currency];
				}
			}
			$collator = new Collator($options['locale']);
			$collator->asort($data);

			Cache::write('default', $cacheKey, $data, Cache::PERSIST);
		}

		if ($type == 'all') {
			foreach ($data as $key => &$item) {
				$item = static::create([
					'id' => $key,
					'name' => $item
				]);
			}
			return new Collection(['data' => $data]);
		} elseif ($type == 'list') {
			return $data;
		} elseif ($type == 'first') {
			$item = $data[$key = $options['conditions']['id']];
			return static::create(['id' => $key, 'name' => $item]);
		}
	}
}

?>