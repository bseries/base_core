<?php
/**
 * Base Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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

use base_core\core\Configuration;

// Double lazy loadable configurations.
trait Configurable {

	protected static $_configurations = [];

	public static function config($name, $config = null) {
		if ($config === null) {
			if ($name === true) {
				foreach (static::$_configurations as $name => &$c) {
					if (!is_a($c, 'Configuration')) {
						$c = static::_initializeConfiguration($c);
					}
				}
				return static::$_configurations;
			}
			if (!isset(static::$_configurations[$name])) {
				throw new OutOfRangeException("No configuration `{$name}` available.");
			}
			if (!is_a($c = static::$_configurations[$name], 'Configuration')) {
				static::$_configurations[$name] = static::_initializeConfiguration($c);
			}
			static::$_configurations[$name];
		}
		static::$_configurations[$name] = $config;
	}

	static function _initializeConfiguration($config) {
		return new Configuration(is_callable($config) ? $config() : $config);
	}
}

?>