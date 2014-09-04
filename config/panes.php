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
	'order' => 100
]);
Panes::register('access', [
	'title' => $t('Access'),
	'order' => 20
]);
Panes::register('external', [
	'title' => $t('External'),
	'order' => 15
]);
Panes::register('authoring', [
	'title' => $t('Authoring'),
	'order' => 90
]);
Panes::register('viewSite', [
	'title' => $t('Site'),
	'order' => 5,
	'url' => '/',
	'actions' => false
]);

$base = ['controller' => 'users', 'action' => 'index', 'library' => 'base_core', 'admin' => true];
Panes::register('access.users', [
	'title' => $t('Users'),
	'url' => $base
]);
Panes::register('access.virtualUsers', [
	'title' => $t('Virtual Users'),
	'url' => ['controller' => 'VirtualUsers'] + $base
]);
Panes::register('access.addresses', [
	'title' => $t('Addresses'),
	'url' => ['controller' => 'Addresses'] + $base
]);

Panes::register('external.support', [
	'title' => $t('Contact Support'),
	'url' => ['controller' => 'Pages', 'action' => 'support', 'library' => 'base_core']
]);
Panes::register('external.ga', [
	'title' => $t('Google Analytics'),
	'url' => function() {
		return 'https://www.google.com/analytics/web/#report/visitors-overview/' . Settings::read('googleAnalytics.default.propertyId');
	}
]);

?>