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
	'persistent' => false,
	// Using defined for BC, as _HOST was introduced later.
	'host' => defined('PROJECT_DB_HOST') ? PROJECT_DB_HOST : 'localhost',
	'database' => PROJECT_DB_DATABASE,
	'login' => PROJECT_DB_USER,
	'password' => PROJECT_DB_PASSWORD
]);

?>