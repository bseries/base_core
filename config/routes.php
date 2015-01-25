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

use lithium\net\http\Router;

$persist = ['admin', 'controller'];

// Route for dashboard / home.
Router::connect('/admin', [
	'controller' => 'Pages',
	'action' => 'home',
	'library' => 'base_core',
	'admin' => true
], compact('modifiers', 'persist'));

Router::connect('/admin/session', [
	'controller' => 'Users',
	'action' => 'session',
	'library' => 'base_core',
	'admin' => true
], compact('modifiers', 'persist'));

Router::connect('/admin/logout', [
	'controller' => 'Users',
	'action' => 'logout',
	'library' => 'base_core',
	'admin' => true
], compact('modifiers', 'persist'));

// Routes for service discovery, JS routing and general API.
// /admin/api/base-core/app/discover
// /admin/api/base-core/widgets/total-revenue
Router::connect('/admin/api/discover', [
	'controller' => 'app',
	'action' => 'discover',
	'library' => 'base_core',
	'admin' => true,
	'api' => true
], compact('modifiers', 'persist'));

Router::connect('/admin/api/widgets/{:name}', [
	'controller' => 'widgets',
	'action' => 'api_view',
	'library' => 'base_core',
	'admin' => true,
	'api' => true
], compact('modifiers', 'persist'));


// Error routes for showcasing and developing error pages. Normally those aren't
// viewed directly. Commonly an exception inside the app will be handled and
// then the error controller be called.
Router::connect('/403', [
	'controller' => 'Errors', 'action' => 'fourohthree', 'library' => 'base_core'
]);
Router::connect('/404', [
	'controller' => 'Errors', 'action' => 'fourohfour', 'library' => 'base_core'
]);
Router::connect('/500', [
	'controller' => 'Errors', 'action' => 'fiveohoh', 'library' => 'base_core'
]);
Router::connect('/503', [
	'controller' => 'Errors', 'action' => 'fiveohthree', 'library' => 'base_core'
]);
Router::connect('/maintenance', [
	'controller' => 'Errors', 'action' => 'maintenance', 'library' => 'base_core'
]);
Router::connect('/browser', [
	'controller' => 'Errors', 'action' => 'browser', 'library' => 'base_core'
]);

?>