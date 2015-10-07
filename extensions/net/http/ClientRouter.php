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

namespace base_core\extensions\net\http;

use lithium\net\http\Router;
use Exception;

class ClientRouter extends \lithium\core\StaticObject {

	protected static $_routes = [];

	public static function provide($name, array $params, array $options = []) {
		$options += ['scope' => Router::scope()];

		static::$_routes[$name] = compact('params', 'options');
	}

	public static function get($name = null) {
		if (!$name) {
			return static::$_routes;
		}
		if (isset(static::$_routes[$name])) {
			return static::$_routes[$name];
		}
		throw new Exception("No client route provided with name `{$name}`.");
	}
}

?>