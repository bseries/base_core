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

namespace base_core\core;

use lithium\util\Set;
use base_core\util\TopologicalSorter;

/**
 * Manages the bootstrap process of an application.
 */
class Boot {

	protected static $_data = array();

	// $needs may be string or an array
	public static function add($provides, $needs, $unit) {
		static::$_data[$provides] = compact('unit') + ['needs' => Set::normalize($needs) ?: []];
	}

	public static function run() {
		$sorter = new TopologicalSorter();

		foreach (static::$_data as $key => $item) {
			$sorter->add($key, static::_dependencies($item['needs']));
		}
		foreach ($sorter->resolve() as $key) {
			$unit = static::$_data[$key]['unit'];
			$unit();
		}
	}

	protected static function _dependencies(array $dependencies) {
		$results = [];

		foreach ($dependencies as $dep => $type) {
			$result = [];

			if (strpos($dep, '*') === false) {
				if (isset(static::$_data[$dep])) {
					$result[] = $dep;
				}
			} else {
				foreach (static::$_data as $key => $_) {
					if (fnmatch($dep, $key)) {
						$result[] = $key;
					}
				}
			}
			if (!$result && $type !== 'optional') {
				throw new Exception("No provider for `{$dep}` found.");
			}
			$results = array_merge($results, $result);
		}
		return $results;
	}
}

?>