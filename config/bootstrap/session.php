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

namespace base_core\config\bootstrap;

use Exception;
use lithium\storage\Session;

if (strlen(PROJECT_SECRET_BASE) < 20) {
	throw new Exception('PROJECT_SECRET_BASE is less than 20 chars.');
}
$secret = substr(PROJECT_SECRET_BASE, 0, 20);

Session::config([
	'default' => [
		'adapter' => 'Php',
		'session.name' => PROJECT_NAME . '_session',
		'session.cache_limiter' => false,
		// http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
		'session.gc_maxlifetime' => 3600
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