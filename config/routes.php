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

use lithium\net\http\Router;
use base_core\extensions\net\http\ClientRouter;
use lithium\util\Inflector;

Router::attach('admin', [
	'prefix' => 'admin',
	'library' => false
]);

Router::scope('admin', function() {
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
	Router::connect('/', [
		'controller' => 'Pages',
		'action' => 'home',
		'library' => 'base_core',
		'admin' => true
	], compact('modifiers', 'persist'));

	Router::connect('/{:action:session|login|logout}', [
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
	// /api/base-core/widgets/total-revenue
	Router::connect("/api/{$library}/{$controller}:{:id:([\w\d\-]+|__ID__)}", [
		'action' => 'view',
		'admin' => true,
		'api' => true
	], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);

	// Generic API single action/add route.
	// /api/base-media/media/transfer
	Router::connect("/api/{$library}/{$controller}/{$action}", [
		'admin' => true,
		'api' => true
	], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);

	// Generic API index route.
	// /api/base-media/media
	Router::connect("/api/{$library}/{$controller}", [
		'action' => 'index',
		'admin' => true,
		'api' => true
	], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);

	if (PROJECT_FEATURE_SCHEDULED_JOBS === 'http') {
		Router::connect("/api/base-core/jobs/run/{:frequency:(high|medium|low)}", [
			'library' => 'base_core',
			'controller' => 'Jobs',
			'action' => 'run',
			'admin' => true,
			'api' => true
		], compact('modifiers', 'formatters', 'persist') + ['defaults' => ['type' => 'json']]);
	}

	// Generic index route.
	// /ecommerce-core/orders
	Router::connect("/{$library}/{$controller}", [
		'action' => 'index',
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	// Generic page and sorted index route.
	Router::connect("/{$library}/{$controller}/page:{:page:(\d+|__PAGE__)}", [
		'action' => 'index',
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	$template  = "/{$library}/{$controller}";
	$template .= "/page:{:page:(\d+|__PAGE__)}";
	$template .= ",order:{:orderField:([\w\-\.\|]+|__ORDER_FIELD__)}";
	$template .= "--{:orderDirection:(desc|asc|__ORDER_DIRECTION__)}";
	$template .= ",filter:{:filter:(.*|__FILTER__)}";
	Router::connect($template, [
		'action' => 'index',
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	// Generic action route.
	// /ecommerce-core/orders/delete/23
	Router::connect("/{$library}/{$controller}/{$action}/{$id}", [
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	// Generic view route.
	// /ecommerce-core/orders/23
	Router::connect("/{$library}/{$controller}/{$id}", [
		'action' => 'view',
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	// Generic single action route.
	// /ecommerce-core/orders/add
	// /base-media/media/transfer
	Router::connect("/{$library}/{$controller}/{$action}", [
		'admin' => true
	], compact('modifiers', 'formatters', 'persist'));

	// Generic action route with value.
	// /ecommerce-core/orders/update-status/23/checked-out

	// FIXME: Turn the rules below this commented route into a generic rules.
	// Router::connect("/{:library:[a-z\-_]+}/{:controller:[a-z\-_]+}/{:action:[a-z\-_]+}/{:id:[0-9]+}/{:value}", [
	//	'admin' => true
	// ], compact('modifiers', 'formatters', 'persist'));

	Router::connect("/{$library}/{$controller}/update-status/{$id}/{:status}", [
		'admin' => true,
		'action' => 'update_status'
	], compact('modifiers', 'formatters', 'persist'));

	Router::connect("/{$library}/{$controller}/change-role/{$id}/{:role}", [
		'admin' => true,
		'action' => 'change_role'
	], compact('modifiers', 'formatters', 'persist'));
});

?>