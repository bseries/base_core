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
use base_core\models\Users;
use lithium\storage\Session;

Auth::config([
	'default' => [
		'adapter' => 'Form',
		'model' => 'Users',
		'fields' => ['email', 'password'],
		'scope' => [
			'password' => ['!=' => ''],
			'is_active' => true,
			'is_locked' => false
		],
		'session' => [
			'name' => 'cookie'
		]
	]
]);

// Sync session_key for user in database when a session is created.
// Note only real users get authenticated.
Auth::applyFilter('set', function($self, $params, $chain) {
	$result = $chain->next($self, $params, $chain);
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
Auth::applyFilter('clear', function($self, $params, $chain) {
	$key = Session::key('default');

	$result = $chain->next($self, $params, $chain);

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