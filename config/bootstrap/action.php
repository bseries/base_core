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

use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use lithium\net\http\Router;
use lithium\net\http\Media;
use lithium\security\Auth;
use Mobile_Detect as MobileDetect;
use lithium\storage\Cache;
use lithium\analysis\Logger;
use ff\Features;

Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$libraries = Libraries::get();

	require_once $libraries['base_core']['path'] . '/config/routes.php';
	require_once $libraries['app']['path'] . '/config/routes.php';

	// Load other libraries.
	unset($libraries['app']);
	unset($libraries['lithium']);
	unset($libraries['base_core']);
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
// manually. Uses application Users model if available.
Media::applyFilter('_handle', function($self, $params, $chain) {
	if ($params['handler']['type'] == 'html') {
		if ($user = Auth::check('default')) {
			$model = Libraries::locate('models', 'Users');
			$params['data']['authedUser'] = $model::create($user);
		} else {
			$params['data']['authedUser'] = null;
		}

		$params['data']['locale'] = Environment::get('locale');

		// $params['data']['site'] = Environment::get('site');
		// $params['data']['service'] = Environment::get('service');
	}
	return $chain->next($self, $params, $chain);
});

// Request logging.
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$request = $params['request'];

	$message = sprintf('%s %s', $request->method, $request->url);

	if (in_array($request->method, ['POST', 'PUT'])) {
		$clean = $request->data;

		if (is_array($clean)) {
			foreach ($clean as $k => &$v) {
				if (is_string($v) && strlen($v) > 500) {
					$v = '[too large - '. strlen($v) . ' bytes suppressed]';
				}
			}
		} elseif (is_string($clean) && strlen($clean) > 500) {
			$clean = '[too large - '. strlen($clean) . ' bytes suppressed]';
		}
		$scrubFields = ['password', 'password_repeat'];

		foreach ($scrubFields as $field) {
			if (isset($clean[$field])) {
				$clean[$field] = '[protected]';
			}
		}
		$message .= " with:\n" . var_export($clean, true);
	}
	Logger::debug($message);

	return $chain->next($self, $params, $chain);
});

// Mainteance page handling.
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	if (!Environment::get('maintenance')) {
		return $chain->next($self, $params, $chain);
	}
	$message  = 'Showing maintenance page.';
	Logger::debug($message);

	$controller = Libraries::instance('controllers', 'base_core.Errors', [
		'request' => $params['request']
	]);

	return $controller(
		$params['request'],
		['action' => 'maintenance']
	);
});

$detectDevice = function($request) {
	$detect = new MobileDetect();
	$headers = array_merge($detect->getUaHttpHeaders(), array_keys($detect->getMobileHeaders()));

	$cacheKey = '';

	foreach ($headers as $header) {
		if ($value = $request->env($header)) {
			$cacheKey = $header . $value;
		}
	}
	$cacheKey = 'deviceDetection_' . md5($cacheKey);

	if (!Environment::is('development') && ($ua = Cache::read('default', $cacheKey))) {
		return $ua;
	}
	$ua = [
		// 'isMobile' => $detect->isMobile(),
		// 'isTablet' => $detect->isTablet(),
		// 'mobileGrade' => $detect->mobileGrade(),
		'isIos' => $detect->isiOS()
	];

	Cache::write('default', $cacheKey, $ua, '+1 week');
	return $ua;
};
// Wrapped in dispatcher as we need the request object.
Dispatcher::applyFilter('run', function($self, $params, $chain) use ($detectDevice) {
	if (!Features::enabled('deviceDetection')) {
		return $chain->next($self, $params, $chain);
	}
	$device = $detectDevice($params['request']);

	Media::applyFilter('_handle', function($self, $params, $chain) use ($device) {
		if ($params['handler']['type'] == 'html') {
			$params['data']['device'] = $device;
		}
		return $chain->next($self, $params, $chain);
	});
	return $chain->next($self, $params, $chain);
});

?>