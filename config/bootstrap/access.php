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
Gate::registerRole('admin', ['panel', 'users']);
Gate::registerRole('member', ['panel']);
Gate::registerRole('user');

//
// Basic Access Configuration
//
Access::config([
	'admin' => [
		'adapter' => 'Rules'
	],
	'entity' => [
		'adapter' => 'Rules',
		'allowAny' => true // When at least one rule matches succeed.
	]
]);

//
// Setup access for entities.
//
$rules = Access::adapter('entity');
$rules->add('user.role:admin', function($user, $entity, $options) {
	return $user && $user['role'] === 'admin';
});
$rules->add('any', function($user, $entity, $options) {
	return true;
});

//
// Setup access for admin panel.
//
Access::adapter('admin')->add('panel', function($user, $request, $options) {
	// Protect all resources below admin exception session, login, logout.
	if (strpos($request->url, '/admin') === false) {
		return true;
	}
	if (preg_match('#^/admin/(session|login|logout)$#', $request->url)) {
		return true;
	}
	if (!$user) {
		return false;
	}
	$rights = ['panel'];

	if (preg_match('#^/admin/base-core/users#', $request->url)) {
		$rights[] = 'users';
	}

	// Allow all users access to the admin panel that have the `'panel'` right.
	if (Gate::check($rights, compact('user'))) {
		return true;
	}

	// Users which have the `'become'` right might have become another user,
	// use the original role to check if access is OK.
	if (isset($user['original']['role'])) {
		if (Gate::check('become', compact('user'))) { // First check if become is/was OK.
			if (Gate::check($rights, ['user' => $user['original']])) { // Then check original role for access.
				return true;
			}
		}
	}
	return false;
});

//
// Actually run the checks on each and every request.
//
Dispatcher::applyFilter('run', function($self, $params, $chain) {
	$access = Access::check('admin', Auth::check('default'), $params['request'], [
		'rules' => ['panel']
	]);

	// Caution: $access is empty when access is _granted_.
	if ($access) {
		$url = $params['request']->url;
		Logger::debug("Security: Access denied for `{$url}` with: " . var_export($access, true));

		throw new AccessDeniedException($access['message']);
	}
	return $chain->next($self, $params, $chain);
});

?>