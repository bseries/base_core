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

namespace base_core\extensions\cms;

use lithium\util\Collection;

class Widgets extends \lithium\core\StaticObject {

	const GROUP_NONE = 'none';
	const GROUP_DASHBOARD = 'dashboard';

	const TYPE_COUNTER = 'counter';
	const TYPE_TABLE = 'table';
	const TYPE_QUICKDIAL = 'quickdial';

	const WEIGHT_HIGH = 100;
	const WEIGHT_NORMAL = 50;
	const WEIGHT_LOW = 0;

	protected static $_data = [];

	public static function register($name, $inner, array $options = []) {
		if (func_num_args() > 3) {
			trigger_error("Library parameter is deprecated (widget $name).", E_USER_DEPRECATED);
			return;
		}
		$options += [
			'weight' => static::WEIGHT_NORMAL,
			'type' => null,
			'group' => static::GROUP_NONE
		];
		$id = hash('md5', $name . $options['type'] . $options['group']);

		static::$_data[$id][] = compact('name', 'inner') +  $options;
	}

	// Returns all items wrapped in a collection object which can be
	// filtered and sorted. When an item has been registered multiple
	// times its inner data is wrapped in a single closure.
	//
	// Alternatively returns a single widget by name.
	public static function read($name = null) {
		$data = [];

		foreach (static::$_data as $id => $items) {
			$item = current($items);

			if ($name && $item['name'] !== $name) {
				continue;
			}

			$widget = [
				// group and type will be the same for all items as we grouped earlier.
				// We need both to be available "outside" as we need to filter on those
				// properities or need them to determine the widget element to load.
				'name' => $item['name'],
				'group' => $item['group'],
				'type' => $item['type'],
				'weight' => $item['weight'],
				// Aggregates multiple registered widgets into one.
				'inner' => function() use ($item, $items) {
					$result = [
						'class' => null,
						'url' => null,
						'title' => false,
						'data' => []
					];
					foreach ($items as $i) {
						$inner = $i['inner']();

						if (isset($inner['data'])) {
							if (is_array($inner['data'])) {
								$result['data'] += $inner['data'];
							} else {
								$result['data'] = $inner['data'];
							}
							unset($inner['data']);
						}
						$result = $inner + $result;
					}
					return $result;
				}
			];

			if ($name && $item['name'] === $name) {
				return $widget;
			}
			$data[$id] = $widget;
		}
		if ($name) {
			throw new Exception("No widget with name `{$name}` found.");
		}
		$data = new Collection(compact('data'));
		$data->sort(function($a, $b) {
			if ($a['weight'] === $b['weight']) {
				return 0;
			}
			return $a['weight'] < $b['weight'] ? -1 : 1;
		});
		return $data;
	}
}

?>