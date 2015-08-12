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
 * License. If not, see http://atelierdisko.de/licenses.
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

if (Gate::checkRight('users')) {
	Panes::register('access', [
		'title' => $t('Access', ['scope' => 'base_core']),
		'weight' => 80
	]);

	Panes::register('access.users', [
		'title' => $t('Users', ['scope' => 'base_core']),
		'url' => [
			'library' => 'base_core',
			'controller' => 'users', 'action' => 'index',
			'admin' => true
		],
		'weight' => 0
	]);
}

?>