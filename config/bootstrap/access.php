<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use base_core\security\Gate;
use li3_access\security\Access;
use li3_access\security\AccessDeniedException;
use lithium\action\Dispatcher;
use lithium\analysis\Logger;
use lithium\aop\Filters;
use lithium\security\Auth;

// Role/Level and Rights Definitions
//
// @link http://stackoverflow.com/questions/1193309/common-cms-roles-and-access-levels

// Allows accessing the admin panel. Further actions
// may require further rights.
Gate::registerRight('panel');

// Allows managing users.
Gate::registerRight('users');

// Allows to become **any** other user (usually only good for admins).
Gate::registerRight('become');

// Allows changing the ownership of entities.
Gate::registerRight('owner');

// Allows accessing the Jobs HTTP API to trigger job execution. This
// makes most sense when using HTTP with scheduled jobs.
Gate::registerRight('api.jobs');

// Protects actions that can be potentially dangerous, when used wrong i.e removing all
// unused media or tags while certain media was waiting to be used.
Gate::registerRight('clean');

// Admins can do anything and have all rights.
Gate::registerRole('admin', ['panel', 'users', 'owner', 'api.jobs', 'become', 'clean']);

// Technical users can only access the (protected) API but
// not the admin panel itself.
Gate::registerRole('technical', ['api.jobs']);

// Normal users are unprivileged users who cannot access
// anything by default.
Gate::registerRole('user');

//
// Access Realm Configuration
//
Access::config([
	'app' => [
		'adapter' => 'Resources',
	],
	'admin' => [
		'adapter' => 'Resources',
	],
	'entity' => [
		'adapter' => 'Rules',
		'iterator' => Access::OK_ANY_OK
	]
]);

//
// Setup access for admin panel (nearly anything below /admin).
//

// Routes related to session handling must be public, in
// order to perform login.
Access::add('admin', 'users.auth', [
	'resource' => '#^/admin/(session|login|logout)$#i',
	'rule' => true,
]);

Access::add('admin', 'users', [
	'resource' => ['admin' => true, 'controller' => 'Users'],
	'rule' => function($user) {
		return $user && Gate::checkRight(['panel', 'users'], compact('user'));
	},
	'message' => 'Admin users panel access not permitted.'
]);

// Scheduled jobs API routes have more lax requirements on what
// auth method can be used.
if (PROJECT_SCHEDULED_JOBS === 'http') {
	Access::add('admin', 'api.jobs', [
		'resource' => ['admin' => true, 'api' => true, 'controller' => 'Jobs'],
		'rule' => function($user) {
			return $user && Gate::checkRight('api.jobs', compact('user'));
		},
		'message' => 'Admin Job API access not permitted.'
	]);
}

// All other admin routes are protected fully.
Access::add('admin', 'admin', [
	'resource' => '#^/admin#',
	'rule' => function($user) {
		if (!$user) {
			return false;
		}
		// Users which have the `'become'` right might have become another user,
		// use the original role to check if access is OK.
		if (isset($user['original']['role']) && Gate::checkRight('become')) {
			$user = $user['original'];
		}
		return Gate::checkRight(['panel'], compact('user'));
	},
	'message' => 'Admin panel access not permitted.'
]);

Access::add('admin', 'fallthrough', [
	'resource' => '*',
	'rule' => true
]);

//
// Setup access for entities.
//
Access::add('entity', 'user.role:admin', function($user, $entity) {
	if (!$user) {
		return false;
	}
	if (is_array($user)) {
		return isset($user['role']) && $user['role'] === 'admin';
	}
	return $user->role === 'admin';
});
Access::add('entity', 'any', function($user, $entity) {
	return true;
});
Access::add('entity', 'nobody', function($user, $entity) {
	return false;
});

//
// Actually run the checks on each and every request.
//
Filters::apply(Dispatcher::class, '_callable', function($params, $next) {
	$url = $params['request']->url;

	// Try to login via token. Precheck to prevent overhead.
	if (isset($params['request']->query['auth_token'])) {
		$auth = Auth::check('token', $params['request'], [
			'writeSession' => false,
			'checkSession' => false
		]);
		if ($auth) {
			$message  = "Security: Authenticated using token for `{$url}` with query: ";
			$message .= var_export($params['request']->query, true);
		} else {
			$message  = "Security: Failed to auth using token for `{$url}` with query: ";
			$message .= var_export($params['request']->query, true);
		}
		Logger::debug($message);
	} else {
		$auth = Auth::check('default');
	}

	if (!Access::check($realm = INSIDE_ADMIN ? 'admin' : 'app', $auth, $params)) {
		$errors = Access::errors($realm);

		$message = "Security: Access denied for realm `{$realm}` and URL `{$url}` with: ";
		$message .= var_export($errors, true);
		Logger::debug($message);

		throw new AccessDeniedException(reset($errors)['message']);
	}

	return $next($params);
});

?>