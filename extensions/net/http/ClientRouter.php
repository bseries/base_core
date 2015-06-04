<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\net\http;

use Exception;

class ClientRouter extends \lithium\core\StaticObject {

	protected static $_routes = [];

	public static function provide($name, array $params) {
		static::$_routes[$name] = $params;
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