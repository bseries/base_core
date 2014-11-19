<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use base_core\extensions\cms\Panes;
use lithium\g11n\Message;
use base_core\extensions\cms\Settings;

extract(Message::aliases());

Panes::register('dashboard', [
	'title' => $t('Dashboard'),
	'url' => ['controller' => 'Pages', 'action' => 'home', 'admin' => true, 'library' => 'base_core'],
	'actions' => false,
	'weight' => 0
]);
Panes::register('access', [
	'title' => $t('Access'),
	'weight' => 80
]);
Panes::register('external', [
	'title' => $t('External'),
	'weight' => 85
]);
Panes::register('authoring', [
	'title' => $t('Authoring'),
	'weight' => 10
]);
Panes::register('viewSite', [
	'title' => $t('Site'),
	'weight' => 95,
	'url' => '/',
	'actions' => false
]);

$base = ['controller' => 'users', 'action' => 'index', 'library' => 'base_core', 'admin' => true];
Panes::register('access.users', [
	'title' => $t('Users'),
	'url' => $base,
	'weight' => 0
]);
Panes::register('access.virtualUsers', [
	'title' => $t('Virtual Users'),
	'url' => ['controller' => 'VirtualUsers'] + $base,
	'weight' => 1
]);

Panes::register('external.support', [
	'title' => $t('Contact Support'),
	'url' => ['controller' => 'Pages', 'action' => 'support', 'library' => 'base_core'],
	'weight' => 0
]);

?>