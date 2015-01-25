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
use lithium\util\Inflector;
$modifiers = [
	'library' => function($v) {
		return str_replace('-', '_', $v);
	},
	'controller' => function($v) {
		return Inflector::camelize($v);
	},
	'action' => function($v) {
		return str_replace('-', '_', $v);
	}
];
$formatters = [
	'library' => function($v) {
		return str_replace('_', '-', $v);
	},
	'controller' => function($v) {
		return str_replace('_', '-', Inflector::underscore($v));
	},
	'action' => function($v) {
		return str_replace('_', '-', $v);
	}
];
$persist = ['admin', 'controller'];

//
// Generic Routes
//

// Generic index route.
// /admin/ecommerce-core/orders
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-]+}", [
	'action' => 'index',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic action route.
// /admin/ecommerce-core/orders/delete/23
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}", [
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic view route.
// /admin/ecommerce-core/orders/23
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-]+}/{:id:[0-9]+}", [
	'action' => 'view',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic single action route.
// /admin/ecommerce-core/orders/add
// /admin/base-media/media/transfer
Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}", [
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic action route with value.
// /admin/ecommerce-core/orders/update-status/23/checked-out

// FIXME: Turn the rules below this commented route into a generic rules.
// Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}/{:value}", [
//	'admin' => true
// ], compact('modifiers', 'formatters', 'persist'));

Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/update-status/{:id:[0-9]+}/{:status}", [
	'admin' => true,
	'action' => 'update_status'
], compact('modifiers', 'formatters', 'persist'));

Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/change-role/{:id:[0-9]+}/{:role}", [
	'admin' => true,
	'action' => 'change_role'
], compact('modifiers', 'formatters', 'persist'));

// Generic API view route.
// /admin/api/base-core/widgets/total-revenue
Router::connect('/admin/api/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}:{:id:([a-z0-9\-_]+|__ID__)}', [
	'action' => 'view',
	'admin' => true,
	'api' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic API single action/add route.
// /admin/api/base-media/media/transfer
Router::connect('/admin/api/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-]+}', [
	'admin' => true,
	'api' => true
], compact('modifiers', 'formatters', 'persist'));

?>