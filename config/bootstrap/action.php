<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use lithium\net\http\Router;
use lithium\net\http\Media;
use lithium\security\Auth;
use lithium\storage\Cache;
use lithium\analysis\Logger;

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$libraries = Libraries::get();

	require_once $libraries['cms_core']['path'] . '/config/routes.php';
	require_once $libraries['app']['path'] . '/config/routes.php';

	// Load other libraries.
	unset($libraries['app']);
	unset($libraries['lithium']);
	unset($libraries['cms_core']);
	foreach (array_reverse($libraries) as $name => $config) {
		$file = "{$config['path']}/config/routes.php";
		file_exists($file) ? call_user_func(function() use ($file) { include $file; }) : null;
	}
	return $chain->next($self, $params, $chain);
});

// Admin routing
Dispatcher::config([
	'rules' => ['admin' => ['action' => 'admin_{:action}']]
]);

// Admin layout.
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$parsed = Router::parse($params['request']);

	if (isset($parsed->params['admin'])) {
		$params['options']['render']['layout'] = 'admin';
	}
	return $chain->next($self, $params, $chain);
});
// Inject environment variables into templates; remember variables are only
// injected into the original template, for elements variables must be passed
// manually.
Media::applyFilter('_handle', function($self, $params, $chain) {
	if ($params['handler']['type'] == 'html') {
		$params['data']['authedUser'] = Auth::check('default');

		$params['data']['locale'] = Environment::get('locale');

		// $params['data']['site'] = Environment::get('site');
		// $params['data']['service'] = Environment::get('service');
	}
	return $chain->next($self, $params, $chain);
});

// Request logging.
Dispatcher::applyFilter('run', function($self, $params, $chain) use ($errorResponse){
	$request = $params['request'];

	$message = sprintf('%s %s', $request->method, $request->url);
	if ($request->method == 'POST') {
		$message .= " with:\n" . var_export($request->data, true);
	}
	Logger::debug($message);

	return $chain->next($self, $params, $chain);
});

// Mainteance page handling.
Dispatcher::applyFilter('run', function($self, $params, $chain) use ($errorResponse){
	if (!Environment::get('maintenance')) {
		return $chain->next($self, $params, $chain);
	}
	$message  = 'Showing maintenance page.';
	Logger::debug($message);

	$controller = Libraries::instance('controllers', 'cms_core.Errors', ['request' => $params['request']]);

	return $controller(
		$params['request'],
		['action' => 'maintenance']
	);
});

?>