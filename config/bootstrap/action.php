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

namespace base_core\config\bootstrap;

use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use lithium\net\http\Router;
use base_core\extensions\net\http\ClientRouter;
use lithium\net\http\Media;
use lithium\security\Auth;
use Mobile_Detect as MobileDetect;
use lithium\storage\Cache;
use lithium\analysis\Logger;
use li3_flash_message\extensions\storage\FlashMessage;

//
// Admin routing. Order matters.
//
Dispatcher::config([
	'rules' => [
		'api' => ['action' => 'api_{:action}'],
		'admin' => ['action' => 'admin_{:action}']
	]
]);

//
// Admin layout.
//
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$parsed = Router::parse($params['request']);

	if (isset($parsed->params['admin'])) {
		$params['options']['render']['layout'] = 'admin';
	}
	return $chain->next($self, $params, $chain);
});

//
// Layout variable injection.
//
// Inject environment variables into templates; remember variables are only
// injected into the original template, for elements variables must be passed
// manually.
//
Media::applyFilter('_handle', function($self, $params, $chain) {
	if ($params['handler']['type'] == 'html') {
		$request = $params['handler']['request'];

		// Inject $authedUser as an object.
		// Uses application Users model if available.
		if ($user = Auth::check('default')) {
			$model = Libraries::locate('models', 'Users');
			$params['data']['authedUser'] = $model::create($user);
		} else {
			$params['data']['authedUser'] = null;
		}

		// Inject current effective locale as $locale.
		$params['data']['locale'] = Environment::get('locale');

		// Inject client routes as $routes.
		$params['data']['routes'] = [];
		foreach (ClientRouter::get() as $name => $ps) {
			if (!empty($request->params['admin']) && empty($ps['admin'])) {
				continue;
			} elseif (empty($request->params['admin']) && !empty($ps['admin'])) {
				continue;
			}
			// In client router context no params should be persisted.
			$clientRequest = clone $request;
			$clientRequest->persist = [];
			$params['data']['routes'][$name] = Router::match($ps, $clientRequest);
		}

		$params['data']['flash'] = FlashMessage::read();
		FlashMessage::clear();
	}
	return $chain->next($self, $params, $chain);
});

//
// Request logging.
//
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

//
// Maintenance page handling.
//
if (PROJECT_MAINTENANCE) {
	Dispatcher::applyFilter('run', function($self, $params, $chain) {
		// if (($user = Auth::check('default')) && $user->role === 'admin') {
		//	return $chain->next($self, $params, $chain);
		// }
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
}

//
// Device detection.
//
if (PROJECT_FEATURE_DEVICE_DETECTION) {
	$detectDevice = function($request) {
		$detect = new MobileDetect();
		$headers = array_merge(
			$detect->getUaHttpHeaders(),
			array_keys($detect->getMobileHeaders()
		));

		$cacheKey = '';

		foreach ($headers as $header) {
			if ($value = $request->env($header)) {
				$cacheKey .= $header . $value;
			}
		}
		$cacheKey = 'deviceDetection_' . md5($cacheKey);

		if (!PROJECT_DEBUG && ($ua = Cache::read('default', $cacheKey))) {
			return $ua;
		}
		$ua = [
			'isMobile' => $detect->isMobile(),
			'isTablet' => $detect->isTablet(),
			// 'mobileGrade' => $detect->mobileGrade(),
			'isIos' => $detect->isiOS()
		];

		Cache::write('default', $cacheKey, $ua, '+1 week');
		return $ua;
	};

	// Wrapped in dispatcher as we need the request object.
	Dispatcher::applyFilter('run', function($self, $params, $chain) use ($detectDevice) {
		$device = $detectDevice($params['request']);

		Media::applyFilter('_handle', function($self, $params, $chain) use ($device) {
			if ($params['handler']['type'] == 'html') {
				$params['data']['device'] = $device;
			}
			return $chain->next($self, $params, $chain);
		});
		return $chain->next($self, $params, $chain);
	});
}

?>