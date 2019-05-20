<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use Exception;
use lithium\storage\Session;

if (strlen(PROJECT_SECRET_BASE) < 20) {
	throw new Exception('PROJECT_SECRET_BASE is less than 20 chars.');
}
$secret = substr(PROJECT_SECRET_BASE, 0, 20);

$base = [];

if (PROJECT_HAS_MEMCACHED) {
	$base += [
		'session.save_handler' => 'memcached',
		'session.save_path' => PROJECT_MEMCACHED_HOST . ':11211',
	];
}

Session::config([
	'default' => $base + [
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