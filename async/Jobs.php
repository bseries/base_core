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

namespace base_core\async;

use Exception;
use base_core\util\TopologicalSorter;
use lithium\analysis\Logger;
use lithium\util\Set;

/**
 * Manages and executes recurring jobs. Jobs can have (optional) cross-frequency dependencies.
 */
class Jobs {

	const FREQUENCY_HIGH   = 'high';
	const FREQUENCY_MEDIUM = 'medium';
	const FREQUENCY_LOW    = 'low';

	protected static $_recurring = [
		'high' => [],
		'medium' => [],
		'low' => []
	];

	public static function read($name = null) {
		if (!$name) {
			return ['recurring' => static::$_recurring];
		}
		foreach (static::$_recurring as $frequency => $data) {
			if (isset($data[$name])) {
				return $data[$name];
			}
		}
	}

	public static function recur($name, $unit, array $options = []) {
		$options += [
			'frequency' => static::FREQUENCY_MEDIUM,
			'needs' => []
		];

		if (isset($options['depends'])) {
			trigger_error('The `depends` job option is deprecated in favor of `needs`.', E_USER_DEPRECATED);
			$options['needs'] = $options['depends'];
			unset($options['depends']);
		}
		static::$_recurring[$options['frequency']][$name] = compact('name', 'unit') + [
			'needs' => Set::normalize($options['needs']) ?: []
		];
	}

	public static function runName($name) {
		foreach (static::$_recurring as $frequency => $data) {
			if (!isset($data[$name])) {
				continue;
			}
			return static::_run($data[$name]);
		}
		throw new Exception("Job `{$name}` not found.`");
	}

	public static function runFrequency($frequency) {
		if (!static::$_recurring[$frequency]) {
			Logger::write('debug', "No jobs to run for frequency `{$frequency}`.");
			return true;
		}
		Logger::write('debug', "Running all jobs with frequency `{$frequency}`.");

		// Allow cross frequency dependencies. Build a list of all names.
		$available = [];
		foreach (static::$_recurring as $data) {
			$available = array_merge($available, array_keys($data));
		}
		$sorter = new TopologicalSorter();

		foreach (static::$_recurring[$frequency] as $name => $item) {
			$sorter->add($name, static::_dependencies($name, $available, $item['needs']));
		}
		$order = $sorter->resolve();
		Logger::write('debug', "Resolved dependencies into run order: " . implode(' -> ', $order));

		foreach ($order as $name) {
			static::_run(static::read($name)); // read() finds name in any freq.
		}
		Logger::write('debug', "Finished running all jobs with frequency `{$frequency}`.");
		return true;
	}

	protected static function _run($item) {
		Logger::write('debug', "Running job `{$item['name']}`.");
		$start = microtime(true);

		$item['unit']();

		$took = round((microtime(true) - $start) / 1000, 2);
		Logger::write('debug', "Finished running job `{$item['name']}`; took {$took}s.");

		return true;
	}

	// FIXME Equal to Boot::_dependencies()
	protected static function _dependencies($for, array $available, array $dependencies) {
		$results = [];

		foreach ($dependencies as $dep => $type) {
			$result = [];

			if (strpos($dep, '*') === false) {
				if (isset($available[$dep])) {
					$result[] = $dep;
				}
			} else {
				foreach ($available as $key) {
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
}

?>