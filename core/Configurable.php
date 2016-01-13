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

use OutOfRangeException;
use base_core\core\Configuration;
use lithium\util\Collection;

trait Configurable {

	protected static $_configurations = null;

	public static function config($name, $config = null) {
		if (!static::$_configurations) {
			static::$_configurations = new Collection();
		}
		if ($config === null) {
			if ($name === true) {
				return static::$_configurations;
			}
			if (!isset(static::$_configurations[$name])) {
				throw new OutOfRangeException("No configuration `{$name}` available.");
			}
			return static::$_configurations[$name];
		}
		if (is_object($config)) {
			static::$_configurations[$name] = $config;
		} else {
			static::$_configurations[$name] = new Configuration(['data' => $config]);
		}
	}
}

?>