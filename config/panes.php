<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config;

use base_core\security\Gate;
use base_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::register('dashboard', [
	'title' => $t('Dashboard', ['scope' => 'base_core']),
	'url' => [
		'library' => 'base_core',
		'controller' => 'Pages', 'action' => 'home',
		'admin' => true,
	],
	'actions' => false,
	'weight' => 0
]);
Panes::register('base', [
	'title' => $t('Bento', ['scope' => 'base_core']),
	'weight' => 85
]);

if (Gate::checkRight('users')) {
	Panes::register('user', [
		'title' => $t('User', ['scope' => 'base_core']),
		'weight' => 80
	]);
	Panes::register('user.users', [
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