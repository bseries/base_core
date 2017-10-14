<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\core;

use Exception;
use lithium\util\Set;
use base_core\util\TopologicalSorter;

/**
 * Manages the bootstrap process of an application.
 */
class Boot {

	protected static $_data = array();

	// $needs may be string or an array
	public static function add($provides, $needs, $unit) {
		static::$_data[$provides] = compact('unit') + [
			'needs' => Set::normalize($needs) ?: []
		];
	}

	public static function run() {
		$sorter = new TopologicalSorter();

		foreach (static::$_data as $key => $item) {
			$sorter->add($key, static::_dependencies($key, $item['needs']));
		}
		foreach ($sorter->resolve() as $key) {
			$unit = static::$_data[$key]['unit'];
			$unit();
		}
	}

	protected static function _dependencies($for, array $dependencies) {
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
				throw new Exception("No provider for `{$dep}` found (wanted by `{$for}`).");
			}
			$results = array_merge($results, $result);
		}
		return $results;
	}

	// Parses an environment file and re-defines contained configuration
	// as constants.
	public static function environment($file, $prefix = null) {
		$fh = fopen($file, 'r');
		$results = [];

		while (($line = fgets($fh)) !== false) {
			if ($line['0'] === '#') {
				continue;
			}
			if (!preg_match('/(?:export )?([a-zA-Z_][a-zA-Z0-9_]*)=(.*)/', $line, $matches)) {
				continue;
			}
			$key = $matches[1];
			$value = trim($matches[2], '"\'');

			switch ($value) {
				case 'y':
				case 'yes':
				case 'true':
					$value = true;
					break;
				case 'n':
				case 'no':
				case 'false':
					$value = false;
					break;
			}
			define(($prefix ? $prefix . '_' : '') . $key, $value);
		}

		fclose($fh);
		return $results;
	}
}

?>