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

use base_core\models\Users;
use lithium\aop\Filters;
use lithium\security\Auth;
use lithium\storage\Session;

Auth::config([
	'default' => [
		'adapter' => 'Form',
		'model' => 'Users',
		'fields' => ['email', 'password'],
		'scope' => [
			'is_active' => true,
			'is_locked' => false
		],
		'session' => [
			'name' => 'cookie'
		]
	],
	'token' => [
		'adapter' => 'Query',
		'model' => 'Users',
		'fields' => ['uuid', 'auth_token'],
		'scope' => [
			'is_active' => true,
			'is_locked' => false
		]
	]
]);

// Sync session_key for user in database when a session is created.
Filters::apply(Auth::class, 'set', function($params, $next) {
	$result = $next($params);
	$key = Session::key('default');

	if (isset($params['data']['original'])) {
		$id = $params['data']['original']['id'];
	} else {
		$id = $params['data']['id'];
	}

	$user = Users::find('first', [
		'conditions' => [
			'id' => $id
		],
		'fields' => [
			'id', 'session_key'
		]
	]);
	$user->save(['session_key' => $key], [
		'whitelist' => ['session_key'],
		'validate' => false
	]);
	return $result;
});
Filters::apply(Auth::class, 'clear', function($params, $next) {
	$key = Session::key('default');

	$result = $next($params);

	$user = Users::find('first', [
		'conditions' => [
			'session_key' => $key
		],
		'fields' => [
			'id'
		]
	]);
	// This feature may no been enabled on previous
	// installs gracefully degrade.
	if (!$user) {
		return $result;
	}
	$user->save(['session_key' => null], [
		'whitelist' => ['session_key'],
		'validate' => false
	]);
	return $result;
});

?>