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

use Exception;
use lithium\net\http\Router;
use lithium\net\http\Request;

// The sister of `Router`, allows us to register named routes, then provide them to
// clients (browsers). It basically allows us to expose certain routes publicly. This
// class and its output from `matched()` is often used together with `router.js`.
class ClientRouter {

	protected static $_routes = [];

	public static function provide($name, array $params, array $options = []) {
		$options += ['scope' => Router::scope()];
		static::$_routes[] = compact('name', 'params', 'options');
	}

	// Returns an array mapping client route names to their matched URLs.
	public static function matched(Request $request, $scope) {
		$routes = array_filter(static::$_routes, function($route) use ($scope) {
			return $route['options']['scope'] === $scope;
		});

		// In client router context no params should be persisted.
		$clientRequest = clone $request;
		$clientRequest->persist = [];

		$results = [];
		foreach ($routes as $route) {
			if (isset($results[$route['name']])) {
				throw new Exception("Possible duplicate client route `'{$route['name']}'`.");
			}
			$results[$route['name']] = Router::match(
				$route['params'], $clientRequest, $route['options']
			);
		}
		return $results;
	}
}

?>