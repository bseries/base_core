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

use lithium\util\Set;
use lithium\analysis\Logger;
use Cz\Dependency as Resolver;
use ff\Features;
use Exception;
use RuntimeException;

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
			'frequency' => null,
			'depends' => []
		];
		if (isset($options['frequency'])) {
			static::$_recurring[$options['frequency']][$name] = compact('name', 'run') + [
				'depends' => Set::normalize($options['depends'])
			];
		}
	}

	public static function runName($name) {
		if (!Features::enabled('scheduledJobs')) {
			return;
		}
		foreach (static::$_recurring as $frequency => $data) {
			if (!isset($data[$name])) {
				continue;
			}
			return static::_run($data[$name]);
		}
		throw new Exception("Job `{$name}` not found.`");
	}

	protected static function _run($item) {
		Logger::write('debug', "Running job `{$item['name']}`.");
		$start = microtime(true);

		$item['run']();

		$took = round((microtime(true) - $start) / 1000, 2);
		Logger::write('debug', "Finished running job `{$item['name']}`; took {$took}s.");

		return true;
	}

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

	public static function runFrequency($frequency) {
		if (!Features::enabled('scheduledJobs')) {
			return;
		}
		if (!static::$_recurring[$frequency]) {
			Logger::write('debug', "No jobs to run for frequency `{$frequency}`.");
			return true;
		}
		Logger::write('debug', "Running all jobs with frequency `{$frequency}`.");

		// Resolve depdendencies and get order first
		$resolver = new Resolver();
		$deps = [];

		foreach (static::$_recurring[$frequency] as $item) {
			foreach ($item['depends'] as $name => $type) {
				if (!$result = static::read($name)) { // Will find in any freq.
					if ($type != 'optional') {
						$message  = "Job `{$name}` not available but required dependency by ";
						$message .= "`{$item['name']}`.";
						throw new RuntimeException($message);
					}
					continue;
				}
				$deps[] = $name;
			}
			$resolver->add($item['name'], $deps);
		}
		if (!$order = $resolver->getResolved()) {
			throw new RuntimeException("Failed to resolve run order dependencies.");
		}
		Logger::write('debug', "Resolved dependencies into run order: " . implode(' -> ', $order));

		foreach ($order as $name) {
			static::_run(static::read($name));
		}
		Logger::write('debug', "Finished running all jobs with frequency `{$frequency}`.");
	}
}

?>