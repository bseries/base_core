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

namespace base_core\extensions\cms;

use lithium\analysis\Logger;

class Jobs extends \lithium\core\StaticObject {

	const FREQUENCY_HIGH = 'high';
	const FREQUENCY_MEDIUM = 'medium';
	const FREQUENCY_LOW = 'low';

	protected static $_recurring = [
		'high' => [],
		'medium' => [],
		'low' => []
	];

	public static function recur($name, $run, array $options = []) {
		$options += [
			'frequency' => null
		];
		if (isset($options['frequency'])) {
			static::$_recurring[$options['frequency']][$name] = compact('name', 'run');
		}
	}

	public static function runName($name) {
		if (!USE_SCHEDULED_JOBS) {
			return;
		}
		foreach (static::$_recurring as $frequency => $data) {
			if (!isset($data[$name])) {
				continue;
			}
			static::_run($data[$name]);
		}
	}

	protected static function _run($item) {
		Logger::write('debug', "Running job `{$item['name']}`.");
		$start = microtime(true);

		$item['run']();

		$took = round((microtime(true) - $start) / 1000, 2);
		Logger::write('debug', "Finished running job `{$item['name']}`; took {$took}s.");
	}

	public static function runFrequency($frequency) {
		if (!USE_SCHEDULED_JOBS) {
			return;
		}
		Logger::write('debug', "Running all jobs with frequency `{$frequency}`.");

		foreach (static::$_recurring[$frequency] as $item) {
			static::_run($item);
		}
		Logger::write('debug', "Finished running all jobs with frequency `{$frequency}`.");
	}

	public static function read() {
		return ['recurring' => static::$_recurring];
	}
}

?>