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

use lithium\security\Auth;
use lithium\action\Dispatcher;
use li3_access\security\Access;
use li3_access\security\AccessDeniedException;
use base_core\security\Gate;

// Role/Level and Rights Definitions
//
// @link http://stackoverflow.com/questions/1193309/common-cms-roles-and-access-levels
Gate::registerRole('admin', ['panel', 'users']);
Gate::registerRole('member', ['panel']);
Gate::registerRole('user');
Gate::registerRole('customer');
Gate::registerRole('merchant');

//
// Basic Access Configuration
//
Access::config([
	'app' => [
		'adapter' => 'Rules'
	],
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
	return $user && $user->role == 'admin';
});
$rules->add('any', function($user, $entity, $options) {
	return true;
});

//
// Setup access for admin panel.
//
Access::adapter('admin')->add('role', function($user, $request, $options) {
	// Protect all resources below admin exception session, login, logout.
	if (strpos($request->url, '/admin') === false) {
		return true;
	}
	if (preg_match('#^/admin/(session|login|logout)$#', $request->url)) {
		return true;
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
	$allNewlyDefinedRules = function($name) {
		$current = array_keys(Access::adapter($name)->get());
		$builtin = ['allowAll', 'denyAll', 'allowAnyUser', 'allowIp'];

		return array_diff($current, $builtin);
	};

	if (strpos($params['request']->url, '/admin') === 0) {
		$access = Access::check('admin', Auth::check('default'), $params['request'], [
			'rules' => $allNewlyDefinedRules('admin')
		]);
	} else {
		$access = Access::check('app', Auth::check('default'), $params['request'], [
			'rules' => $allNewlyDefinedRules('app')
		]);
	}

	// Caution: $access is empty when access is _granted_.
	if ($access) {
		throw new AccessDeniedException('FORBIDDEN');
	}
	return $chain->next($self, $params, $chain);
});

?>