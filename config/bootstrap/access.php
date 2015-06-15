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
use base_core\models\Users;

//
// Role/Level and Rights Definitions
//
// @link http://stackoverflow.com/questions/1193309/common-cms-roles-and-access-levels
$roles = [
	'admin' => [
		'panel',
		'become'
	],
	'member' => [
		'panel'
	],
	'user' => [

	],
	'customer' => [

	],
	'merchant' => [

	]
];
foreach ($roles as $role => $rights) {
	Users::$enum['roles'][] = $role;
}

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
Access::adapter('admin')->add('role', function($user, $request, $options) use ($roles) {
	// Protect all resources below admin exception session, login, logout.
	if (strpos($request->url, '/admin') === false) {
		return true;
	}
	if (preg_match('#^/admin/(session|login|logout)$#', $request->url)) {
		return true;
	}

	if (!isset($roles[$user['role']])) {
		return false;
	}

	// Allow all users access to the admin panel that have the `'panel'` right.
	if (in_array('panel', $roles[$user['role']])) {
		return true;
	}

	// Users which have the `'become'` right might have become another user,
	// use the original role to check if access is OK.
	if (isset($user['original']['role'])) {
		if (in_array('become', $roles[$user['role']])) { // First check if become is/was OK.
			if (in_array('panel', $roles[$user['original']['role']])) { // Then check original role for access.
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