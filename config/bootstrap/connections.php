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

// We define a single "default" connection, under tests, this will point to the test
// database. This ensures all database request go the the test database, and we cannot
// forget to point the models' connections to test. For safety reasons do not even define
// the non-test connection when running tests.

$default = [
	'type' => 'database',
	'adapter' => 'MySql',
	'encoding' => 'utf8mb4',
	'host' => 'localhost',
	'persistent' => false,
];
if (!defined('TEST_EXECUTION')) {
	Connections::add('default', $default + [
		'host' => PROJECT_DB_HOST,
		'database' => PROJECT_DB_DATABASE,
		'login' => PROJECT_DB_USER,
		'password' => PROJECT_DB_PASSWORD
	]);
} elseif (PROJECT_DB_TEST) {
	Connections::add('default', $default + [
		'host' => PROJECT_DB_TEST_HOST,
		'database' => PROJECT_DB_TEST_DATABASE,
		'login' => PROJECT_DB_TEST_USER,
		'password' => PROJECT_DB_TEST_PASSWORD
	]);
}

?>