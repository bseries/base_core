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

use base_core\util\TopologicalSorter;

/**
 * Manages the bootstrap process of an application.
 */
class Boot {

	protected static $_data = array();

	public static function add($unit, array $options = array()) {
		$options += array(
			'needs' => array(),
			'provides' => null
		);
		if ($item['provides']) {
			static::$_data[$item['provides']] = compact('unit') + $options;
		} else {
			static::$_data[] = compact('unit') + $options;
		}
	}

	public static function run() {
		$sorter = new TopologicalSorter();

		foreach (static::$_data as $key => $item) {
			$sorter->add($key, $item['needs']);
		}
		foreach ($sorter->resolve() as $key) {
			static::$_data[$key]['unit']();
		}
	}
}

?>