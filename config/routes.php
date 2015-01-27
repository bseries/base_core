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
use base_core\extensions\net\http\ClientRouter;

$persist = ['admin', 'controller', 'library'];

// Route for dashboard / home.
Router::connect('/admin', [
	'controller' => 'Pages',
	'action' => 'home',
	'library' => 'base_core',
	'admin' => true
], compact('modifiers', 'persist'));

Router::connect('/admin/{:action:session|login|logout}', [
	'controller' => 'Users',
	'library' => 'base_core',
	'admin' => true
], compact('modifiers', 'persist'));


ClientRouter::provide('widgets:view', [
	'controller' => 'widgets', 'library' => 'base_core',
	'action' => 'view', 'admin' => true, 'api' => true,
	'id' => '__ID__'
]);

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