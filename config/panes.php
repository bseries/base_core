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

namespace base_core\config;

use base_core\security\Gate;
use base_core\extensions\cms\Panes;
use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::register('dashboard', [
	'title' => $t('Dashboard', ['scope' => 'base_core']),
	'url' => ['controller' => 'Pages', 'action' => 'home', 'admin' => true, 'library' => 'base_core'],
	'actions' => false,
	'weight' => 0
]);
Panes::register('external', [
	'title' => $t('External', ['scope' => 'base_core']),
	'weight' => 85
]);
Panes::register('authoring', [
	'title' => $t('Authoring', ['scope' => 'base_core']),
	'weight' => 10
]);
Panes::register('viewSite', [
	'title' => $t('Site', ['scope' => 'base_core']),
	'weight' => 95,
	'url' => '/',
	'actions' => false
]);

if (Gate::check('users')) {
	Panes::register('access', [
		'title' => $t('Access', ['scope' => 'base_core']),
		'weight' => 80
	]);

	$base = ['controller' => 'users', 'action' => 'index', 'library' => 'base_core', 'admin' => true];
	Panes::register('access.users', [
		'title' => $t('Users', ['scope' => 'base_core']),
		'url' => $base,
		'weight' => 0
	]);
	if (Settings::read('user.useVirtualUsers')) {
		Panes::register('access.virtualUsers', [
			'title' => $t('Virtual Users', ['scope' => 'base_core']),
			'url' => ['controller' => 'VirtualUsers'] + $base,
			'weight' => 1
		]);
	}
}

?>