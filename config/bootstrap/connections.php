<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use lithium\data\Connections;

Connections::add('default', [
	'type' => 'database',
	'adapter' => 'MySql',
	'encoding' => 'utf8mb4',
	'host' => 'localhost',
	'persistent' => false,
	'host' => PROJECT_DB_HOST,
	'database' => PROJECT_DB_DATABASE,
	'login' => PROJECT_DB_USER,
	'password' => PROJECT_DB_PASSWORD
]);

if (PROJECT_DB_TEST) {
	Connections::add('test', [
		'type' => 'database',
		'adapter' => 'MySql',
		'encoding' => 'utf8mb4',
		'host' => 'localhost',
		'persistent' => false,
		'host' => PROJECT_DB_TEST_HOST,
		'database' => PROJECT_DB_TEST_DATABASE,
		'login' => PROJECT_DB_TEST_USER,
		'password' => PROJECT_DB_TEST_PASSWORD
	]);
}

?>