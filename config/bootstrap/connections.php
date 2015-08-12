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

use lithium\data\Connections;

Connections::add('default', [
	'type' => 'database',
	'adapter' => 'MySql',
	'encoding' => 'UTF-8',
	'host' => 'localhost',
	'persistent' => false,
	'database' => PROJECT_DB_DATABASE,
	'login' => PROJECT_DB_USER,
	'password' => PROJECT_DB_PASSWORD
]);

?>