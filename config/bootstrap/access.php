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

use lithium\security\Auth;
use lithium\action\Dispatcher;
use li3_access\security\Access;
use li3_access\security\AccessDeniedException;
use base_core\security\Gate;
use lithium\analysis\Logger;

// Role/Level and Rights Definitions
//
// @link http://stackoverflow.com/questions/1193309/common-cms-roles-and-access-levels
Gate::registerRole('admin', ['panel', 'users', 'api.jobs']);
Gate::registerRole('member', ['panel']);
Gate::registerRole('technical', ['api.jobs']);
Gate::registerRole('user');

//
// Basic Access Configuration
//
Access::config([
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
		return Gate::check(['panel', 'users'], compact('user'));
	},
	'message' => 'Admin users panel access not permitted.'
]);

// Scheduled jobs API routes have more lax requirements on what
// auth method can be used.
if (PROJECT_FEATURE_SCHEDULED_JOBS === 'http') {
	Access::add('admin', 'api.jobs', [
		'resource' => ['admin' => true, 'api' => true, 'controller' => 'Jobs'],
		'rule' => function($user) {
			if (!($user = $user ?: Auth::check('token'))) {
				return false;
			}
			return Gate::check(['api.jobs'], compact('user'));
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
		if (isset($user['original']['role']) && Gate::check('become')) {
			$user = $user['original'];
		}
		return Gate::check(['panel'], compact('user'));
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
	return $user && $user['role'] === 'admin';
});
Access::add('entity', 'any', function($user, $entity) {
	return true;
});

//
// Actually run the checks on each and every request.
//
Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	$url = $params['request']->url;

	if (!Access::check('admin', Auth::check('default'), $params)) {
		$errors = Access::errors('admin');
		Logger::debug("Security: Access denied for `{$url}` with: " . var_export($errors, true));

		throw new AccessDeniedException(reset($errors)['message']);
	}
	return $chain->next($self, $params, $chain);
});

?>