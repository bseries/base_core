<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
		// In client router context no params should be persisted.
		$clientRequest = clone $request;
		$clientRequest->persist = [];

		$results = [];
		foreach (static::$_routes as $route) {
			if ($route['options']['scope'] !== $scope) {
				continue;
			}
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