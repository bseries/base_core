<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\core\Environment;

Environment::set($map[PROJECT_CONTEXT]);

// For BC
$map = [
	'test' => 'test',
	'dev' => 'development',
	'stage' => 'staging',
	'prod' => 'production'
];
Environment::is(function($request) use ($map) {
	return $map[PROJECT_CONTEXT];
});

?>