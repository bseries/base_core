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

namespace base_core\config\bootstrap;

use lithium\storage\Session;
use lithium\core\Environment;

// Use a pseudo number generator seeded with project
// name to generate cookie secret. Simple md5'ing wont work as
// there the alphabet would be too limited for a password style string.
$secret = substr(PROJECT_SECRET_BASE, 0, 20);

Session::config([
	'default' => [
		'adapter' => 'Php',
		'session.name' => PROJECT_NAME . '_session',
		'session.cache_limiter' => false
	],
	'cookie' => [
		'adapter' => 'Cookie',
		'name' => PROJECT_NAME . '_cookie',
		'strategies' => [
			// 'Hmac' => ['secret' => $secret],
			'Encrypt' => ['secret' => $secret],
		]
	]
]);

?>