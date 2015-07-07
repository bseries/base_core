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

use lithium\net\http\Router;
use base_core\extensions\net\http\ClientRouter;
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
		// Controller may come in as library.Controller.
		if (strpos($v, '.') !== false) {
			list($library, $controller) = explode('.', $v);
		} else {
			$controller = $v;
		}
		return str_replace('_', '-', Inflector::underscore($controller));
	},
	'action' => function($v) {
		return str_replace('_', '-', $v);
	}
];
$persist = ['admin', 'controller', 'library'];

$library = '{:library:[a-z\-]+}';
$controller = '{:controller:[a-z\-]+}';
$action = '{:action:[a-z\-]+}';
$id = '{:id:\d+}';

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

// Generic API view route.
// /admin/api/base-core/widgets/total-revenue
Router::connect("/admin/api/{$library}/{$controller}:{:id:([\w\d\-]+|__ID__)}", [
	'action' => 'view',
	'admin' => true,
	'api' => true
], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);

// Generic API single action/add route.
// /admin/api/base-media/media/transfer
Router::connect("/admin/api/{$library}/{$controller}/{$action}", [
	'admin' => true,
	'api' => true
], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);

// Generic API index route.
// /admin/api/base-media/media
Router::connect("/admin/api/{$library}/{$controller}", [
	'action' => 'index',
	'admin' => true,
	'api' => true
], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);


// Generic index route.
// /admin/ecommerce-core/orders
Router::connect("/admin/{$library}/{$controller}", [
	'action' => 'index',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic page and sorted index route.
Router::connect("/admin/{$library}/{$controller}/page:{:page:(\d+|__PAGE__)}", [
	'action' => 'index',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

$template  = "/admin/{$library}/{$controller}";
$template .= "/page:{:page:(\d+|__PAGE__)}";
$template .= ",order:{:orderField:([\w\-\.\|]+|__ORDER_FIELD__)}";
$template .= "--{:orderDirection:(desc|asc|__ORDER_DIRECTION__)}";
$template .= ",filter:{:filter:(.*|__FILTER__)}";
Router::connect($template, [
	'action' => 'index',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic action route.
// /admin/ecommerce-core/orders/delete/23
Router::connect("/admin/{$library}/{$controller}/{$action}/{$id}", [
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic view route.
// /admin/ecommerce-core/orders/23
Router::connect("/admin/{$library}/{$controller}/{$id}", [
	'action' => 'view',
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic single action route.
// /admin/ecommerce-core/orders/add
// /admin/base-media/media/transfer
Router::connect("/admin/{$library}/{$controller}/{$action}", [
	'admin' => true
], compact('modifiers', 'formatters', 'persist'));

// Generic action route with value.
// /admin/ecommerce-core/orders/update-status/23/checked-out

// FIXME: Turn the rules below this commented route into a generic rules.
// Router::connect("/admin/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}/{:value}", [
//	'admin' => true
// ], compact('modifiers', 'formatters', 'persist'));

Router::connect("/admin/{$library}/{$controller}/update-status/{$id}/{:status}", [
	'admin' => true,
	'action' => 'update_status'
], compact('modifiers', 'formatters', 'persist'));

Router::connect("/admin/{$library}/{$controller}/change-role/{$id}/{:role}", [
	'admin' => true,
	'action' => 'change_role'
], compact('modifiers', 'formatters', 'persist'));

?>