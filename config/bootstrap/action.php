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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\config\bootstrap;


use base_core\extensions\net\http\ClientRouter;
use base_core\extensions\net\http\ServiceUnavailableException;
use base_core\models\Assets;
use base_core\security\Gate;
use base_media\models\MediaVersions;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\action\Dispatcher;
use lithium\analysis\Logger;
use lithium\aop\Filters;
use lithium\core\Environment;
use lithium\core\Libraries;
use lithium\net\http\Media;
use lithium\net\http\Router;
use lithium\security\Auth;
use lithium\util\Set;
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
Filters::apply(Dispatcher::class, 'run', function($params, $next) {
	$parsed = Router::parse($params['request']);

	if (isset($parsed->params['admin'])) {
		$params['options']['render']['layout'] = 'admin';
	}
	return $next($params);
});

//
// Layout variable injection.
//
// Inject environment variables into templates; remember variables are only
// injected into the original template, for elements variables must be passed
// manually.
//
// The following variables are made available to views and layouts:
// $authedUser    - The current authenticated user as an User entity.
// $locale        - The current effective locale.
// $app           - The application definition for use with JavaScript.
// $flash         - The last message.
//
Filters::apply(Media::class, '_handle', function($params, $next) {
	if ($params['handler']['type'] == 'html') {
		$request = $params['handler']['request'];

		// Inject $authedUser as an object.
		// Uses application Users model if available.
		if ($user = Auth::check('default')) {
			$model = Libraries::locate('models', 'Users');
			$authedUser = $model::create($user);
		} else {
			$authedUser = null;
		}

		// Inject current effective locale as $locale.
		$locale = Environment::get('locale');

		// Build global application definition for JavaScript.
		$app = [
			'debug' => PROJECT_DEBUG,
			'assets' => [
				'base' => Assets::base($request) . '/v:' . PROJECT_VERSION
			],
			'media' => [
				'base' => MediaVersions::base($request)
			],
			'routes' => ClientRouter::matched($request, Router::scope())
		];

		// Pass and clear last flash message.
		$flash = FlashMessage::read();
		FlashMessage::clear();

		// Security: Do not disclose route information in higher security administration contexts.
		if (!empty($request->params['admin']) && !Gate::checkRight('panel')) {
			$app['routes'] = [];
		}

		$params['data'] += compact(
			'authedUser',
			'app',
			'flash',
			'locale'
		);
	}
	return $next($params);
});

//
// Request logging.
//
// This is enclosed in this condition in order to optimize performance. We
// know that when Logger does not log debug messages, we do not have
// to generate them here. Request logging is pretty expensive.
if (PROJECT_DEBUG_LOGGING) {
	$scrubber = function($data) {
		$clean = $data;

		// Limit request data to display size.
		$maxLength = 500;
		if (is_array($clean)) {
			foreach ($clean as $k => &$v) {
				if (is_string($v) && strlen($v) > $maxLength) {
					$v = '[too large - '. strlen($v) . ' bytes suppressed]';
				}
			}
		} elseif (is_string($clean) && strlen($clean) > $maxLength) {
			$clean = '[too large - '. strlen($clean) . ' bytes suppressed]';
		}

		// Remove sensitive data.
		$scrubFields = [
			'password',
			'password_repeat',
			'user.password',
			'user.password_repeat'
		];
		foreach ($scrubFields as $field) {
			if (Set::check($clean, $field)) {
				$clean = Set::insert($clean, $field, '[protected]');
			}
		}

		return $clean;
	};

	Filters::apply(Dispatcher::class, 'run', function($params, $next) use ($scrubber) {
		$request = $params['request'];
		$message = sprintf('%s %s', $request->method, $request->url);

		if (in_array($request->method, ['POST', 'PUT'])) {
			$message .= " with:\n" . var_export($scrubber($request->data), true);
		}
		Logger::debug($message);

		return $next($params);
	});
}

//
// Maintenance page handling.
//
if (PROJECT_MAINTENANCE) {
	Filters::apply(Dispatcher::class, 'run', function($params, $next) {
		throw new ServiceUnavailableException('Maintenance');
	});
}

?>