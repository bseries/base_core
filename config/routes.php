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

$modifiers = [
	'library' => function($v) {
		return str_replace('-', '_', $v);
	},
	'action' => function($v) {
		return str_replace('-', '_', $v);
	}
];
$persist = ['admin', 'controller'];

//
// Explicit routes for this module.
//

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

//
// Generic Routes
//

// Generic index route.
// /admin/ecommerce-core/orders
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-]+}", [
	'action' => 'index',
	'admin' => true
], compact('modifiers', 'persist'));

// Generic action route.
// /admin/ecommerce-core/orders/delete/23
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}", [
	'admin' => true
], compact('modifiers', 'persist'));

// Generic view route.
// /admin/ecommerce-core/orders/23
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-]+}/{:id:[0-9]+}", [
	'action' => 'view',
	'admin' => true
], compact('modifiers', 'persist'));

// Generic single action route.
// /admin/ecommerce-core/orders/add
// /admin/base-media/media/transfer
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}", [
	'admin' => true
], compact('modifiers', 'persist'));

// Generic action route with value.
// /admin/ecommerce-core/orders/update-status/23/checked-out

// FIXME: Turn the rules below this commented route into a generic rules.
// Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}/{:value}", [
//	'admin' => true
// ], compact('modifiers', 'persist'));

Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/update-status/{:id:[0-9]+}/{:status}", [
	'admin' => true,
	'action' => 'update_status'
], compact('modifiers', 'persist'));

Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:id:[0-9]+}/change-role/{:role}", [
	'admin' => true,
	'action' => 'change_role'
], compact('modifiers', 'persist'));

?>