<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use lithium\aop\Filters;
use lithium\console\Dispatcher;

/**
 * This filter will convert {:heading} to the specified color codes. This is useful for colorizing
 * output and creating different sections.
 */
Filters::apply(Dispatcher::class, '_call', function($params, $next) {
	$params['callable']->response->styles(array(
		'heading' => '\033[1;30;46m'
	));
	return $next($params);
});

?>