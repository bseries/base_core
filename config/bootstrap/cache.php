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

use lithium\storage\Cache;
use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use lithium\storage\cache\adapter\Memcache;
use lithium\storage\Session;
use lithium\data\Connections;
use lithium\data\source\Database;
use ff\Features;

if (Features::enabled('memcached')) {
	if (!Memcache::enabled()) {
		throw new Exception("Memcached not available.");
	}
	Cache::config([
		'default' => [
			'scope' => PROJECT_NAME . ':' . PROJECT_VERSION,
			'adapter' => 'Memcache',
			'host' => '127.0.0.1:11211'
		]
	]);
} else {
	if (!is_writable($path = Libraries::get(true, 'resources') . '/tmp/cache')) {
		throw new Exception("Cache path `{$path}` is not writable.");
	}
	Cache::config([
		'default' => [
			'adapter' => 'File',
			'strategies' => ['Serializer']
		]
	]);
}

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if (Environment::is('development')) {
		return $chain->next($self, $params, $chain);
	}
	$cacheKey = 'core.libraries';

	if ($cached = Cache::read('default', $cacheKey)) {
		$cached = (array) $cached + Libraries::cache();
		Libraries::cache($cached);
	}
	$result = $chain->next($self, $params, $chain);

	if ($cached != ($data = Libraries::cache())) {
		Cache::write('default', $cacheKey, $data, '+1 day');
	}
	return $result;
});

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if (Environment::is('development')) {
		return $chain->next($self, $params, $chain);
	}
	foreach (Connections::get() as $name) {
		if (!(($connection = Connections::get($name)) instanceof Database)) {
			continue;
		}
		$connection->applyFilter('describe', function($self, $params, $chain) use ($name) {
			if ($params['fields']) {
				return $chain->next($self, $params, $chain);
			}
			$cacheKey = "data.connections.{$name}.sources.{$params['entity']}.schema";

			return Cache::read('default', $cacheKey, [
				'write' => function() use ($self, $params, $chain) {
					return ['+1 day' => $chain->next($self, $params, $chain)];
				}
			]);
		});
	}
	return $chain->next($self, $params, $chain);
});

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if (Environment::is('development')) {
		return $chain->next($self, $params, $chain);
	}
	$request  = $params['request'];
	$response = $chain->next($self, $params, $chain);

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

?>