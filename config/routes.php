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
use base_core\extensions\cms\Features;

// Errors
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

// Administration
$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin', [
	'controller' => 'pages', 'action' => 'home', 'library' => 'base_core', 'admin' => true
], $persist);

Router::connect('/admin/session', [
	'controller' => 'users', 'action' => 'session', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/login', [
	'controller' => 'users', 'action' => 'login', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/logout', [
	'controller' => 'users', 'action' => 'logout', 'library' => 'base_core', 'admin' => true
], $persist);

// Users
Router::connect('/admin/users/{:id:[0-9]+}/change-role/{:role}', [
	'controller' => 'users', 'action' => 'change_role', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/users/{:action}/{:id:[0-9]+}', [
	'controller' => 'users', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/users/{:action}/{:args}', [
	'controller' => 'users', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/virtual-users/{:id:[0-9]+}/change-role/{:role}', [
	'controller' => 'VirtualUsers', 'action' => 'change_role', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/virtual-users/{:action}/{:id:[0-9]+}', [
	'controller' => 'VirtualUsers', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/virtual-users/{:action}/{:args}', [
	'controller' => 'VirtualUsers', 'library' => 'base_core', 'admin' => true
], $persist);

// Addresses
Router::connect('/admin/addresses/{:action}/{:id:[0-9]+}', [
	'controller' => 'addresses', 'library' => 'base_core', 'admin' => true
], $persist);
Router::connect('/admin/addresses/{:action}/{:args}', [
	'controller' => 'addresses', 'library' => 'base_core', 'admin' => true
], $persist);

// Misc
Router::connect('/admin/support', [
	'controller' => 'pages', 'action' => 'support', 'library' => 'base_core', 'admin' => true
], $persist);

// Administration JavaScript Environment
Router::connect('/admin/api/discover', [
	'controller' => 'app', 'action' => 'api_discover', 'library' => 'base_core', 'admin' => true
], $persist);

Router::connect('/admin/api/widgets/{:name}', [
	'controller' => 'widgets', 'action' => 'api_view', 'library' => 'base_core', 'admin' => true
], $persist);

?>