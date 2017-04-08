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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\config\bootstrap;

use lithium\core\Environment;

$map = [
	'test' => 'test',
	'stage' => 'staging',
	'prod' => 'production'
];

Environment::is(function() use ($map) {
	if (isset($map[PROJECT_CONTEXT])) {
		return $map[PROJECT_CONTEXT];
	}
	return 'development';
});

// Trigger detector and even init unknown envs.
Environment::set([]);

$config = [
	'locale' => PROJECT_LOCALE,
];
Environment::set('development', $config);
Environment::set('staging', $config);
Environment::set('production', $config);
Environment::set('test', ['locale' => 'en']);

?>