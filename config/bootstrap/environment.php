<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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