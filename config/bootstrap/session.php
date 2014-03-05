<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\storage\Session;
use lithium\core\Environment;

Session::config([
	'default' => [
		'adapter' => 'Php',
		'session.name' => PROJECT_NAME . '_session',
		'session.cache_limiter' => 'private'
	],
	'cookie' => [
		'adapter' => 'Cookie',
		'name' => PROJECT_NAME . '_cookie',
		'strategies' => [
			// 'Hmac' => ['secret' => Environment::get('security.cookieSecret')],
			'Encrypt' => ['secret' => Environment::get('security.cookieSecret')],
		]
	]
]);

?>