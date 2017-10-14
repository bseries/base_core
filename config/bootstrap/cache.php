<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use Exception;
use lithium\action\Dispatcher;
use lithium\analysis\Logger;
use lithium\aop\Filters;
use lithium\core\Environment;
use lithium\core\Libraries;
use lithium\data\Connections;
use lithium\data\source\Database;
use lithium\net\http\Router;
use lithium\storage\Cache;
use lithium\storage\Session;
use lithium\storage\cache\adapter\Memcache;

if (!is_writable($path = PROJECT_PATH . '/tmp/cache')) {
	throw new Exception("Cache path `{$path}` is not writable.");
}
if (PROJECT_HAS_MEMCACHED) {
	if (!Memcache::enabled()) {
		throw new Exception("Memcached not available.");
	}
	$default = [
		'scope' => PROJECT_NAME . ':' . PROJECT_CONTEXT . ':' . PROJECT_VERSION,
		'adapter' => 'Memcache',
		'host' => '127.0.0.1:11211'
	];
} else {
	$default = [
		'adapter' => 'File',
		'strategies' => ['Serializer'],
		'path' => $path
	];
}
Cache::config([
	'default' => $default,
	'blob' => [
		'adapter' => 'File',
		'streams' => true,
		'path' => $path
	]
]);

if (!PROJECT_DEBUG) {
	Filters::apply(Dispatcher::class, 'run', function($params, $next) {
		$cacheKey = 'core.libraries';

		if ($cached = Cache::read('default', $cacheKey)) {
			$cached = (array) $cached + Libraries::cache();
			Libraries::cache($cached);
		}
		$result = $next($params);

		if ($cached != ($data = Libraries::cache())) {
			Cache::write('default', $cacheKey, $data, '+1 day');
		}
		return $result;
	});

	Filters::apply(Dispatcher::class, 'run', function($params, $next) {
		foreach (Connections::get() as $name) {
			if (!(($connection = Connections::get($name)) instanceof Database)) {
				continue;
			}
			Filters::apply($connection, 'describe', function($params, $next) use ($name) {
				if ($params['fields']) {
					return $next($params);
				}
				$cacheKey = "data.connections.{$name}.sources.{$params['entity']}.schema";

				return Cache::read('default', $cacheKey, [
					'write' => function() use ($params, $next) {
						return ['+1 day' => $next($params)];
					}
				]);
			});
		}
		return $next($params);
	});
}


if (PROJECT_FPC) {
	// Will ignore any existing session and dynamic data.
	// Doesn't work with redirects.
	Filters::apply(Dispatcher::class, 'run', function($params, $next) {
		$request = $params['request'];
		$response = $next($params);

		$cacheKey = 'fpc_' . $request->url;

		$skip = !$request->is('get') || $response->type() !== 'html';
		$skip = $skip || strpos($request->url, '/admin') === 0;

		if ($skip) {
			Logger::debug("FPC skipped URL `{$request->url}`.");
			return $response;
		}

		// Effectivly disable compression as this cannot be handled by webservers.
		$backup = ini_get('memcached.compression_threshold');
		ini_set('memcached.compression_threshold', 10000000);

		Logger::debug("FPC caching URL `{$request->url}` und key `{$cacheKey}`.");
		Cache::write('default', $cacheKey, $response->body(), '+1 hour');

		ini_set('memcached.compression_threshold', $backup);

		return $response;
	});
}

if (!PROJECT_DEBUG) {
	Filters::apply(Dispatcher::class, 'run', function($params, $next) {
		$request  = $params['request'];
		$response = $next($params);

		// Cache only HTML responses, JSON responses come from
		// APIs and are most often highly dynamic.
		if ($response->type() !== 'html' || strpos($request->url, '/admin') === 0 || Session::read('default')) {
			$response->headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
			$response->headers['Pragma'] = 'no-cache';
			$response->headers['Expires'] = '0';
			return $response;
		}

		$hash = 'W/' . md5(serialize([
			$response->body,
			$response->headers,
			PROJECT_VERSION
		]));
		$condition = trim($request->get('http:if_none_match'), '"');

		if ($condition === $hash) {
			$response->status(304);
			$response->body = [];
		}
		$response->headers['ETag'] = "\"{$hash}\"";
		return $response;
	});
}

//
// Cache Router::match calls.
//
if (!PROJECT_DEBUG) {
	$cachedUrls = Cache::read('default', 'template_view_urls');

	// Workaround as we currently cannot filter Dispatcher::run. This results into max nesting.
	register_shutdown_function(function() use (&$cachedUrls) {
		Cache::write('default', 'template_view_urls', $cachedUrls);
	});

	Filters::apply(Libraries::class, 'instance', function($params, $next) use (&$cachedUrls) {
		if ($params['name'] !== 'File' || $params['type'] !== 'adapter.template.view') {
			return $next($params);
		}
		$req = $params['options']['request'];
		$h = $params['options']['view']->outputFilters['h'];

		$params['options']['handlers'] = [
			'url' => function($url, $ref, array $options = array()) use (&$req, $h, &$cachedUrls) {
				$key = md5(serialize([
					$url,
					$options,
					$req->url
				]));
				if (isset($cachedUrls[$key])) {
					return $cachedUrls[$key];
				}
				$url = Router::match($url ?: '', $req, $options);

				$result = $h ? str_replace('&amp;', '&', $h($url)) : $url;

				$cachedUrls[$key] = $result;
				return $result;
			}
		];
		return $next($params);
	});
}

?>